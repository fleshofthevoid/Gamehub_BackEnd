<?php
// /api/get_blockblast_leaderboard.php
require "config.php";
header('Content-Type: application/json; charset=utf-8');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0 || $limit > 200) $limit = 50;

try {
    $stmt = $pdo->prepare("
        SELECT u.username, b.score, b.max_combo, b.played_at
        FROM blockblast_scores b
        JOIN users u ON u.id = b.user_id
        ORDER BY b.score DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data"    => $rows
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error"   => "DB error: " . $e->getMessage()
    ]);
}
