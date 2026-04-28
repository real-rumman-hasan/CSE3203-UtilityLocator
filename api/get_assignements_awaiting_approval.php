<?php
header("Content-Type: application/json");
require_once "db_connect.php";

try {
  $stmt = $conn->query(
    'SELECT
        b.id,
        b.message,
        b.payment_status,
        b.created_at,
        b.provider_id,
        s.id AS service_id,
        s.name AS service_name,
        COALESCE(ps_existing.custom_price, s.price) AS amount,
        c.f_name AS customer_first,
        c.l_name AS customer_last,
        c.phone AS customer_phone,
        p.f_name AS suggested_first,
        p.l_name AS suggested_last
     FROM bookings b
     INNER JOIN services s ON s.id = b.service_id
     INNER JOIN users c ON c.id = b.customer_id
     LEFT JOIN users p ON p.id = b.provider_id
     LEFT JOIN provider_services ps_existing ON ps_existing.provider_id = b.provider_id AND ps_existing.service_id = b.service_id
     WHERE b.status = "awaiting_assignment" AND b.payment_status = "paid"
     ORDER BY b.created_at DESC'
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