<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$email = trim($_POST['email'] ?? '');

try {
  $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
  $stmt->execute(['email' => $email]);

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