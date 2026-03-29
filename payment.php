 <?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userId = $_POST['userId'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];

    // Dummy transaction ID
    $transactionId = "TXN" . rand(10000, 99999);

    $status = "Success"; // simulate success

    $sql = "INSERT INTO Payment (userId, amount, method, transactionId, status)
            VALUES ('$userId', '$amount', '$method', '$transactionId', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "Payment Successful! Transaction ID: " . $transactionId;
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>