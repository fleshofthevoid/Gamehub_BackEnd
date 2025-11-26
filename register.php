<?php
require __DIR__ . "/config.php";
$payload = json_decode(file_get_contents("php://input"), true) ?? $_POST;

$username = trim($payload["username"] ?? "");
$email = trim($payload["email"] ?? "");
$password = $payload["password"] ?? "";

if (!$username || !$email || !$password) {
  http_response_code(400);
  echo json_encode(["error"=>"Missing fields"]);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["error"=>"Invalid email"]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$stmt->execute([$username,$email]);
if ($stmt->fetch()) {
  http_response_code(409);
  echo json_encode(["error"=>"Username or email already exists"]);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (username,email,password_hash) VALUES (?,?,?)")->execute([$username,$email,$hash]);
$uid = (int)$pdo->lastInsertId();
$pdo->prepare("INSERT INTO stats (user_id) VALUES (?)")->execute([$uid]);

echo json_encode(["success"=>true,"user"=>["id"=>$uid,"username"=>$username,"email"=>$email]]);
