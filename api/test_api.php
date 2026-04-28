<?php
// 1. Tell the browser/client to expect JSON data
header("Content-Type: application/json");

// 2. Include your existing connection
require_once 'db_connect.php';

try {
    // 3. Fetch your 5 users
    $stmt = $conn->prepare("SELECT f_name, email FROM users LIMIT 5");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Turn the PHP array into a JSON string and send it
    echo json_encode($users);
    
} catch (PDOException $e) {
    // If something fails, send an error message in JSON format
    echo json_encode(["error" => $e->getMessage()]);
}