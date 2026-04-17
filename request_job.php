 <?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userId = $_POST['userId'];
    $providerId = $_POST['providerId'];
    $serviceId = $_POST['serviceId'];
    $date = $_POST['date'];

    $status = "Pending";

    $sql = "INSERT INTO JobRequest (userId, providerId, serviceId, date, status)
            VALUES ('$userId', '$providerId', '$serviceId', '$date', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "Job requested successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
