 <?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userId = $_POST['userId'];
    $providerId = $_POST['providerId'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $sql = "INSERT INTO ReviewRating (userId, providerId, rating, comment)
            VALUES ('$userId', '$providerId', '$rating', '$comment')";

    if ($conn->query($sql) === TRUE) {
        echo "Review submitted!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>