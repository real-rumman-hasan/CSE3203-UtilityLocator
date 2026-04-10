<?php
include 'db_connect.php';

// Check if the request is coming via fetch (JSON)
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if ($contentType === "application/json") {
    // Receive the JSON data
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);

    // Map your JS data to your SQL columns
    $jobID   = $conn->real_escape_string($decoded['jobRequestID']);
    $rating  = $conn->real_escape_string($decoded['rating']);
    $comment = $conn->real_escape_string($decoded['comment']);

    $sql = "INSERT INTO ReviewRating (jobRequestID, rating, comment)
            VALUES ('$jobID', '$rating', '$comment')";

    if ($conn->query($sql) === TRUE) {
        echo "Review submitted successfully!";
    } else {
        echo "Database Error: " . $conn->error;
    }
} else {
    echo "Invalid Request Type";
}

$conn->close();
?>