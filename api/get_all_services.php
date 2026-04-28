<?php
header("Content-Type: application/json");
require_once "db_connect.php";

try {
  $stmt = $conn->query('SELECT name FROM services ORDER BY name ASC');
  $response = $stmt->fetchAll(PDO::FETCH_COLUMN);

  if ($stmt->rowCount() > 0) {
    echo json_encode($response);
  } else {
    echo json_encode(false);
  }
}
catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

?>