<?php
header("Content-Type: application/json");
require_once "db_connect.php";

try {
  $stmt = $conn->query(
    'SELECT
        u.id,
        u.f_name,
        u.l_name,
        u.email,
        u.phone,
        u.district,
        u.area,
        u.postal_code,
        u.image,
        u.created_at,
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ", ") AS services
     FROM users u
     LEFT JOIN provider_services ps ON ps.provider_id = u.id
     LEFT JOIN services s ON s.id = ps.service_id
     WHERE u.role = "provider" AND u.is_verified = 0
     GROUP BY u.id
     ORDER BY u.created_at ASC'
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