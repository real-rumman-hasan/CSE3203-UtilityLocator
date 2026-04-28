<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

try {
  $stmt = $conn->prepare('SELECT id FROM users WHERE email = :email OR phone = :phone LIMIT 1');
  $stmt->execute([
            'email' => $email,
            'phone' => $phone,
        ]);

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