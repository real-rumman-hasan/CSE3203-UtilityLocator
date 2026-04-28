<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

$providerId = (int) ($_POST['provider_id'] ?? 0);
$action = $_POST['action'] ?? '';

try {
    $stmt = $conn->prepare('UPDATE users SET is_verified = :is_verified WHERE id = :id AND role = "provider"');
    $stmt->execute([
        'is_verified' => $action === 'allow' ? 1 : 0,
        'id' => $providerId,
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => ($action === 'allow' ? 'Provider approved successfully.' : 'Provider rejected and hidden from the frontend.')]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made or user not found."]);
    }
}
catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
// if ($providerId && $bookingId && $assignedProviderId && $action) {
// }

?>

