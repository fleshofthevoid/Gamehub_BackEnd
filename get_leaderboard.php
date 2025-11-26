<?php
require __DIR__ . "/config.php";
$by = $_GET["by"] ?? "rating"; // rating | wins | best_time_easy|medium|hard|extreme
$limit = max(1, min(100, (int)($_GET["limit"] ?? 20)));

$allowed = ["rating","wins","best_time_easy","best_time_medium","best_time_hard","best_time_extreme"];
if (!in_array($by,$allowed,true)) $by = "rating";

if ($by === "rating" || $by === "wins") {
  $order = $by === "rating" ? "rating_points DESC" : "wins DESC";
  $stmt = $pdo->prepare("SELECT u.username, s.rating_points, s.wins, s.best_time_easy, s.best_time_medium, s.best_time_hard, s.best_time_extreme
                         FROM stats s JOIN users u ON u.id=s.user_id
                         ORDER BY $order, u.username ASC LIMIT ?");
  $stmt->bindValue(1, $limit, PDO::PARAM_INT);
} else {
  $stmt = $pdo->prepare("SELECT u.username, s.$by AS best_time, s.rating_points, s.wins
                         FROM stats s JOIN users u ON u.id=s.user_id
                         WHERE s.$by IS NOT NULL
                         ORDER BY s.$by ASC LIMIT ?");
  $stmt->bindValue(1, $limit, PDO::PARAM_INT);
}
$stmt->execute();
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
