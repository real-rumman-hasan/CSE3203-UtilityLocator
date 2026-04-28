<?php
header("Content-Type: application/json");
require_once "db_connect.php";

try {
  $conn->beginTransaction();
  $stmt = $conn->prepare(
            'INSERT INTO users (f_name, l_name, email, phone, password, role, postal_code, district, area, lat, lng, image, is_verified)
             VALUES (:f_name, :l_name, :email, :phone, :password, :role, :postal_code, :district, :area, NULL, NULL, :image, :is_verified)'
        );
  $stmt->execute([
            'f_name' => $_POST['f_name'],
            'l_name' => $_POST['l_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'password' => $_POST['password'],
            'role' => $_POST['role'],
            'postal_code' => $_POST['postal_code'],
            'district' => $_POST['district'],
            'area' => $_POST['area'],
            'image' => $_POST['image'],
            'is_verified' => $_POST['is_verified'],
        ]);
  
  $userId = (int) $conn->lastInsertId();

  if ($_POST['role'] === 'provider') {
    $psStmt = $conn->prepare('INSERT INTO provider_services (provider_id, service_id) VALUES (:provider_id, :service_id)');
    foreach ($_POST['service_ids'] as $serviceId) {
        $psStmt->execute([
            'provider_id' => $userId,
            'service_id' => $serviceId,
        ]);
    }
  }

  $conn->commit();

  echo json_encode(['success' => true]);
}
catch (PDOException $e) {
    echo json_encode(["success" => null, "message" => $e->getMessage()]);
}

?>