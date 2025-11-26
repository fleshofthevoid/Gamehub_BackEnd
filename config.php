<?php
// api/config.php
header("Content-Type: application/json; charset=utf-8");
// Uncomment for dev CORS if you open HTML from file:// or different host
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$DB_HOST = '127.0.0.1';
$DB_NAME = 'saper_db';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default empty

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error'=>'DB connection failed','detail'=>$e->getMessage()]);
  exit;
}
?>
