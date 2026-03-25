 <?php
$conn = new mysqli("localhost", "root", "", "db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = $_POST['firstName'];
    $lastName  = $_POST['lastName'];
    $name = $firstName . " " . $lastName;

    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $roleText = $_POST['role'];

    // Convert role text → roleID
    if ($roleText == "Customer") {
        $roleID = 1;
    } else {
        $roleID = 2;
    }

    $district = $_POST['district'];
    $area = $_POST['area'];
    $postal = $_POST['postal'];

    // Check email
    $check = $conn->prepare("SELECT userID FROM User WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists'); window.location='register.php';</script>";
        exit();
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO User (roleID, userName, email, phone, passHash, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("issss", $roleID, $name, $email, $phone, $password);
    $stmt->execute();

    $userID = $stmt->insert_id;

    // Insert role table relation
    if ($roleID == 1) {
        $conn->query("INSERT INTO Customer (customerID) VALUES ($userID)");
    } else {
        $conn->query("INSERT INTO Provider (providerID) VALUES ($userID)");
    }

    // Insert location
    $loc = $conn->prepare("INSERT INTO UserLocation (userID, district, area, postalCode) VALUES (?, ?, ?, ?)");
    $loc->bind_param("isss", $userID, $district, $area, $postal);
    $loc->execute();

    echo "<script>alert('Registration Successful'); window.location='login.php';</script>";
}
?>
