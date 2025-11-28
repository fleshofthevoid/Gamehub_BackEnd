<?php
require __DIR__ . "/config.php";

header("Content-Type: application/json; charset=utf-8");

$by = $_GET["by"] ?? "rating";
$limit = max(1, min(100, (int)($_GET["limit"] ?? 20)));

$allowed = ["rating", "wins", "best_time_easy", "best_time_medium", "best_time_hard", "best_time_extreme"];
if (!in_array($by, $allowed, true)) $by = "rating";

$order = "s.rating_points DESC";
$select_field = "s.rating_points";
$label = "Рейтинг";

switch ($by) {
  case "wins":
    $order = "s.wins DESC";
    $select_field = "s.wins";
    $label = "Перемоги";
    break;
  case "best_time_easy":
  case "best_time_medium":
  case "best_time_hard":
  case "best_time_extreme":
    $order = "s.$by ASC";
    $select_field = "s.$by";
    $label = "Кращий час";
    break;
}

$sql = "SELECT 
          COALESCE(u.username, CONCAT('User #', s.user_id)) AS username,
          s.wins, s.rating_points,
          s.best_time_easy, s.best_time_medium, s.best_time_hard, s.best_time_extreme
        FROM stats s
        LEFT JOIN users u ON u.id = s.user_id
        ORDER BY $order
        LIMIT ?";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $data, "sort_by" => $label]);
?>
