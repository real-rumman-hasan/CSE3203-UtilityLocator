<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$providerId = (int) ($_POST['provider_id'] ?? 0);
$adminId = (int) ($_POST['admin_id'] ?? 0);
$bookingId = (int) ($_POST['booking_id'] ?? 0);
$assignedProviderId = (int) ($_POST['assigned_provider_id'] ?? 0);


try {
  $check = pdo()->prepare(
            'SELECT b.id
             FROM bookings b
             INNER JOIN provider_services ps ON ps.provider_id = :provider_id AND ps.service_id = b.service_id
             INNER JOIN users u ON u.id = ps.provider_id
             WHERE b.id = :booking_id
               AND b.status = "awaiting_assignment"
               AND b.payment_status = "paid"
               AND u.is_verified = 1
               AND u.role = "provider"
             LIMIT 1'
        );
        $check->execute([
            'provider_id' => $assignedProviderId,
            'booking_id' => $bookingId,
        ]);

        if ($check->fetch()) {
            $assign = pdo()->prepare(
                'UPDATE bookings
                 SET provider_id = :provider_id,
                     status = "pending",
                     assigned_by_admin_id = :admin_id,
                     assigned_at = NOW(),
                     expires_at = DATE_ADD(NOW(), INTERVAL 2 MINUTE)
                 WHERE id = :booking_id
                   AND status = "awaiting_assignment"
                   AND payment_status = "paid"'
            );
            $assign->execute([
                'provider_id' => $assignedProviderId,
                'admin_id' => (int) $adminId,
                'booking_id' => $bookingId,
            ]);

            echo json_encode(["success" => true, "message" => 'Booking assigned to the selected worker and pushed to the provider queue.']);
        } else {
          echo json_encode(["success" => false, "message" => 'Selected provider is not eligible for this service.']);
        }
}
catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}


?>