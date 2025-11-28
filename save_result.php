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

$pdo->prepare("INSERT INTO games (user_id,difficulty,result,time_seconds) VALUES (?,?,?,?)")->execute([$user_id,$difficulty,$result,$time]);

$stmt = $pdo->prepare("SELECT * FROM stats WHERE user_id=?");
$stmt->execute([$user_id]);
$st = $stmt->fetch(PDO::FETCH_ASSOC);

$total = (int)$st["total_games"] + 1;
$wins = (int)$st["wins"];
$losses = (int)$st["losses"];
$streak = (int)$st["current_streak"];
$best_streak = (int)$st["best_streak"];
$rating = (int)$st["rating_points"];
$points = 0;

if ($result === "win") {
  $wins++; $streak++; if ($streak > $best_streak) $best_streak = $streak;
  $base = ["easy"=>1,"medium"=>3,"hard"=>8,"extreme"=>15][$difficulty];
  $points += $base;
  if ($streak >= 2 && $streak < 4) $points += 5; else if ($streak >= 4) $points += 10;
  if ($time < 60) $points += 5; if ($time < 30) $points += 10;
  $rating += $points;
  $col = "best_time_" . $difficulty;
  $currentBest = $st[$col];
  if ($currentBest === null || (int)$currentBest === 0 || $time < (int)$currentBest) {
    $pdo->prepare("UPDATE stats SET $col=? WHERE user_id=?")->execute([$time,$user_id]);
  }
} else {
  $losses++; $streak = 0;
}

$pdo->prepare("UPDATE stats SET total_games=?, wins=?, losses=?, current_streak=?, best_streak=?, rating_points=? WHERE user_id=?")
    ->execute([$total,$wins,$losses,$streak,$best_streak,$rating,$user_id]);

echo json_encode(["success"=>true,"awarded_points"=>$points,"new_rating"=>$rating,"current_streak"=>$streak]);
?>