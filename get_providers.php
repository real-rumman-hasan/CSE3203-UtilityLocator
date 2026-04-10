<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'db_connect.php';

$sql = "SELECT u.userName, l.latitude, l.longitude, s.price, s.serviceName, u.userID
        FROM User u
        JOIN UserLocation l ON u.userID = l.userID
        JOIN ProviderService ps ON u.userID = ps.providerID
        JOIN Service s ON ps.serviceID = s.serviceID
        WHERE u.roleID = 2"; 

$result = $conn->query($sql);
$data = [];
while($row = $result->fetch_assoc()) { $data[] = $row; }
echo json_encode($data);
?>