<?php
require __DIR__ . "/config.php";
$payload = json_decode(file_get_contents("php://input"), true) ?? $_POST;

$user_id = (int)($payload["user_id"] ?? 0);
$difficulty = $payload["difficulty"] ?? "";
$result = $payload["result"] ?? "";
$time = (int)($payload["time_seconds"] ?? 0);

$validDifficulties = ["easy","medium","hard","extreme"];
if (!$user_id || !in_array($difficulty,$validDifficulties,true) || !in_array($result,["win","lose"],true) || $time < 0) {
  http_response_code(400);
  echo json_encode(["error"=>"Invalid or missing fields"]); exit;
}

$pdo->prepare("INSERT INTO games (user_id,difficulty,result,time_seconds) VALUES (?,?,?,?)")
    ->execute([$user_id,$difficulty,$result,$time]);

// Fetch stats row
$stmt = $pdo->prepare("SELECT * FROM stats WHERE user_id=?");
$stmt->execute([$user_id]);
$st = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$st) { $pdo->prepare("INSERT INTO stats (user_id) VALUES (?)")->execute([$user_id]); $st = [
  "total_games"=>0,"wins"=>0,"losses"=>0,"best_time_easy"=>null,"best_time_medium"=>null,"best_time_hard"=>null,"best_time_extreme"=>null,"current_streak"=>0,"best_streak"=>0,"rating_points"=>0
]; }

$total = (int)$st["total_games"] + 1;
$wins = (int)$st["wins"];
$losses = (int)$st["losses"];
$streak = (int)$st["current_streak"];
$best_streak = (int)$st["best_streak"];
$rating = (int)$st["rating_points"];

$points = 0;
if ($result === "win") {
  $wins++;
  $streak++;
  if ($streak > $best_streak) $best_streak = $streak;

  // base by difficulty
  $base = ["easy"=>1,"medium"=>3,"hard"=>8,"extreme"=>15][$difficulty];
  $points += $base;

  // streak bonus
  if ($streak >= 2 && $streak < 4) $points += 5;
  else if ($streak >= 4) $points += 10;

  // time bonus
  if ($time < 60) $points += 5;
  if ($time < 30) $points += 10;

  $rating += $points;

  // best time update
  $col = "best_time_" . $difficulty;
  $currentBest = $st[$col];
  if ($currentBest === null || $currentBest === "" || (int)$currentBest === 0 || $time < (int)$currentBest) {
    $pdo->prepare("UPDATE stats SET $col=? WHERE user_id=?")->execute([$time,$user_id]);
  }
} else {
  $losses++;
  $streak = 0;
}

$pdo->prepare("UPDATE stats SET total_games=?, wins=?, losses=?, current_streak=?, best_streak=?, rating_points=? WHERE user_id=?")
    ->execute([$total,$wins,$losses,$streak,$best_streak,$rating,$user_id]);

echo json_encode(["success"=>true,"awarded_points"=>$points,"new_rating"=>$rating,"current_streak"=>$streak]);
