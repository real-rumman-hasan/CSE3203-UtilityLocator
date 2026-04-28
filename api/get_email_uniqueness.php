<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$providerId = (int) ($_POST['id'] ?? 0);
$email = $_POST['email'] ?? null;

try {
  $stmt = $conn->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
  $stmt->execute(['email' => $email, 'id' => $providerId]);

  $response = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($stmt->rowCount() > 0) {
    echo json_encode($response);
  }
  else {
    echo json_encode(null);
  }
}
catch (PDOException $e) {
    echo json_encode(["success" => null, "message" => $e->getMessage()]);
}

?>