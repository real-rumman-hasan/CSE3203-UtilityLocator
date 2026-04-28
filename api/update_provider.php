<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$providerId = (int) ($_POST['id'] ?? 0);
$f_name = trim($_POST['f_name'] ?? '');
$l_name = trim($_POST['l_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$district = trim($_POST['district'] ?? '');
$area = trim($_POST['area'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');

try {
  $stmt = $conn->prepare(
            'UPDATE users 
             SET f_name = :f_name, 
                 l_name = :l_name, 
                 email = :email, 
                 phone = :phone, 
                 district = :district, 
                 area = :area, 
                 postal_code = :postal_code 
             WHERE id = :id'
        );
  $stmt->execute([
            'f_name' => $f_name,
            'l_name' => $l_name,
            'email' => $email,
            'phone' => $phone,
            'district' => $district,
            'area' => $area,
            'postal_code' => $postal_code,
            'id' => $providerId,
        ]);
  echo json_encode(['success' => true]);
}
catch (PDOException $e) {
    echo json_encode(["success" => null, "message" => $e->getMessage()]);
}

?>