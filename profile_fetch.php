 <?php
session_start();
include("db_connect.php");

$userID = $_SESSION['userID'];

$sql = "SELECT * FROM User WHERE userID='$userID'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

echo json_encode($user);
?>