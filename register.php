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

    $roleInput = strtolower(trim($_POST['role']));
    $roleMap = [
        '1' => [1, 'Customer'],
        'customer' => [1, 'Customer'],
        '2' => [2, 'Provider'],
        'provider' => [2, 'Provider'],
    ];

    if (!isset($roleMap[$roleInput])) {
        die("Invalid role selected");
    }

    $roleID = $roleMap[$roleInput][0];
    $roleName = $roleMap[$roleInput][1];

    $roleUpsert = $conn->prepare("INSERT INTO Role (roleID, roleName) VALUES (?, ?) ON DUPLICATE KEY UPDATE roleName=VALUES(roleName)");
    $roleUpsert->bind_param("is", $roleID, $roleName);
    $roleUpsert->execute();

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
    if (strcasecmp($roleName, "Customer") === 0) {
        $stmt2 = $conn->prepare("INSERT INTO Customer (customerID) VALUES (?)");
    } else {
        $stmt2 = $conn->prepare("INSERT INTO Provider (providerID) VALUES (?)");
    }

    $stmt2->bind_param("i", $userID);
    $stmt2->execute();

    // Insert location
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $loc = $conn->prepare("INSERT INTO UserLocation (userID, district, area, postalCode, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
    $loc->bind_param("isssdd", $userID, $district, $area, $postal, $latitude, $longitude);
    $loc->execute();

    echo "<script>alert('Registration Successful'); window.location='login.php';</script>";
}
?>
