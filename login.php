 <?php
session_start();
$conn = new mysqli("localhost", "root", "", "db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM User WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['passHash'])) {

            $_SESSION['userID'] = $user['userID'];
            $_SESSION['roleID'] = $user['roleID'];
            $_SESSION['userName'] = $user['userName'];

            header("Location: index.html"); // or dashboard
            exit();

        } else {
            echo "<script>alert('Wrong password');</script>";
        }

    } else {
        echo "<script>alert('User not found');</script>";
    }
}
?>