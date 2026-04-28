<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

$providerId = (int) ($_POST['provider_id'] ?? 0);

try {
    $stmt = pdo()->prepare('DELETE FROM users WHERE id = :id AND role = "provider"');
    $stmt->execute(['id' => $providerId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Provider and all associated data have been completely removed."]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made or user not found."]);
    }
}
catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>