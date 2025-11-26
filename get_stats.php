<?php
require __DIR__ . "/config.php";
$user_id = (int)($_GET["user_id"] ?? 0);
if (!$user_id) { http_response_code(400); echo json_encode(["error"=>"Missing user_id"]); exit; }

$stmt = $pdo->prepare("SELECT u.username, s.* FROM users u JOIN stats s ON s.user_id=u.id WHERE u.id=?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); echo json_encode(["error"=>"User not found"]); exit; }
echo json_encode(["success"=>true,"data"=>$row]);
