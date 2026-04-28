<?php
header("Content-Type: application/json");
require_once "db_connect.php";

try {
  $stmt = $conn->query(
    'SELECT
        ps.service_id,
        u.id AS provider_id,
        CONCAT(u.f_name, " ", u.l_name) AS provider_name,
        COALESCE(ps.custom_price, s.price) AS amount
     FROM provider_services ps
     INNER JOIN users u ON u.id = ps.provider_id
     INNER JOIN services s ON s.id = ps.service_id
     WHERE u.role = "provider" AND u.is_verified = 1
     ORDER BY provider_name'
);
  $response = $stmt->fetchAll();

  if ($stmt->rowCount() > 0) {
    echo json_encode($response);
  } else {
    echo json_encode(null);
  }
}
catch (PDOException $e) {
    echo json_encode(["success" => null, "message" => $e->getMessage()]);
}

?>