 <?php
session_start();
include("db_connect.php");

$customerID = $_SESSION['userID'];

$sql = "SELECT j.*, u.userName 
        FROM JobRequest j
        JOIN User u ON j.providerID = u.userID
        WHERE j.customerID = '$customerID'";

$result = $conn->query($sql);

$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);
?>