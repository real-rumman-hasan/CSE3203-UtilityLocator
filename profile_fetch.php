 <?php
session_start();
include("db_connect.php");

$userID = $_SESSION['userID'];

$sql = "SELECT * FROM User WHERE userID='$userID'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$servicesQuery = "SELECT s.name AS service_name, b.date, b.comment, b.rating FROM bookings b 
    INNER JOIN services s ON b.service_id = s.id 
    WHERE b.customer_id = '$userID'";
$servicesResult = $conn->query($servicesQuery);
$services = [];

if ($servicesResult->num_rows > 0) {
    while ($row = $servicesResult->fetch_assoc()) {
        $services[] = $row;
    }
}

$response = [
    'user' => $user,
    'services' => $services
];

echo json_encode($response);
?>