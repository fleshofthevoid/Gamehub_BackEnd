<?php
require __DIR__ . "/config.php";
$input = file_get_contents("php://input");
$data = json_decode($input, true);
if (!$data) { $data = $_POST; }
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : (int)($_GET['user_id'] ?? 0);
if (!$user_id) { http_response_code(400); echo json_encode(['error'=>'Invalid or missing user_id']); exit; }

$stmt = $pdo->prepare("SELECT u.username, s.* FROM users u JOIN stats s ON s.user_id=u.id WHERE u.id=?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); echo json_encode(['error'=>'User not found or no stats']); exit; }
echo json_encode(['success'=>true,'data'=>[
  'username'=>$row['username'],
  'total_games'=>(int)$row['total_games'],
  'wins'=>(int)$row['wins'],
  'losses'=>(int)$row['losses'],
  'current_streak'=>(int)$row['current_streak'],
  'best_streak'=>(int)$row['best_streak'],
  'rating_points'=>(int)$row['rating_points'],
  'best_time_easy'=>$row['best_time_easy'] !== null ? (int)$row['best_time_easy'] : null,
  'best_time_medium'=>$row['best_time_medium'] !== null ? (int)$row['best_time_medium'] : null,
  'best_time_hard'=>$row['best_time_hard'] !== null ? (int)$row['best_time_hard'] : null,
  'best_time_extreme'=>$row['best_time_extreme'] !== null ? (int)$row['best_time_extreme'] : null,
]]);
?>