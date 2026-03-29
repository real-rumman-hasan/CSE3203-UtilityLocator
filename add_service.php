 <?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $serviceName = $_POST['serviceName'];
    $categoryId = $_POST['categoryId'];

    if (empty($serviceName) || empty($categoryId)) {
        echo "All fields required!";
        exit();
    }

    $sql = "INSERT INTO Service (serviceName, categoryId)
            VALUES ('$serviceName', '$categoryId')";

    if ($conn->query($sql) === TRUE) {
        echo "Service added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
