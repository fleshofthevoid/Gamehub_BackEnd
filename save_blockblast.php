<?php
// /api/save_blockblast.php
require "config.php";
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$score   = isset($data['score']) ? (int)$data['score'] : 0;
$combo   = isset($data['combo']) ? (int)$data['combo'] : 1;

if ($user_id <= 0 || $score <= 0) {
    echo json_encode([
        "success" => false,
        "error"   => "Missing or invalid data"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO blockblast_scores (user_id, score, max_combo)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $score, $combo]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error"   => "DB error: " . $e->getMessage()
    ]);
}
