<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Providers</title>
    <link
      rel="shortcut icon"
      href="images/logos/logo.svg"
      type="image/x-icon"
    />
    <link rel="stylesheet" href="styles/service.css" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
      crossorigin="anonymous"
    />
  </head>
  <body>
    <?php
    session_start();
    include 'db_connect.php';

    $isLoggedIn = isset($_SESSION['userID']);
    $userName = $isLoggedIn ? $_SESSION['userName'] : '';
    $roleID = $isLoggedIn ? $_SESSION['roleID'] : 0;

    $serviceID = isset($_GET['serviceID']) && is_numeric($_GET['serviceID']) ? (int) $_GET['serviceID'] : 0;
    $service = $conn->prepare("SELECT * FROM Service WHERE serviceID = ?");
    $service->bind_param("i", $serviceID);
    $service->execute();
    $serviceResult = $service->get_result();
    $serviceData = $serviceResult->fetch_assoc();

    $providersResult = null;
    if ($serviceData) {
      $providers = $conn->prepare("
        SELECT u.userID, u.userName, u.email, u.phone, u.profilePicture, ul.district, ul.area, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.rating) as review_count
        FROM Provider p
        JOIN User u ON p.providerID = u.userID
        JOIN UserLocation ul ON u.userID = ul.userID
        JOIN ProviderService ps ON p.providerID = ps.providerID AND ps.serviceID = ?
        LEFT JOIN Rating r ON r.providerID = p.providerID AND r.serviceID = ?
        WHERE u.status = 'active'
        GROUP BY u.userID, u.userName, u.email, u.phone, u.profilePicture, ul.district, ul.area
      ");
      $providers->bind_param("ii", $serviceID, $serviceID);
      $providers->execute();
      $providersResult = $providers->get_result();
    }
    ?>

    <!-- navigation bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container-fluid">
        <a class="navbar-brand mx-auto fw-bold" href="index.php">
          <img src="images/logos/logo.svg" width="60" alt="Logo" />
          UtilityLocator
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-bold">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                id="navbarDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                Services
              </a>
              <ul class="dropdown-menu">
                <?php
                $allCats = $conn->query("SELECT categoryName FROM ServiceCategory");
                while ($cat = $allCats->fetch_assoc()) {
                  echo "<li><a class='dropdown-item' href='service.php?category=" . urlencode($cat['categoryName']) . "'>" . $cat['categoryName'] . "</a></li>";
                }
                ?>
                <li><hr class="dropdown-divider" /></li>
                <li>
                  <a class="dropdown-item" href="service.php">All Services</a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="about.html">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="help.html">Help</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="faq.html">FAQ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="rating.html">Reviews</a>
            </li>
          </ul>
          <div class="d-grid gap-2 d-md-block">
            <?php if ($isLoggedIn): ?>
              <span class="text-light">Welcome, <?php echo $userName; ?>!</span>
              <a href="logout.php" class="btn btn-secondary fw-bold" type="button">Logout</a>
              <?php if ($roleID == 2): ?>
                <a href="provider_dashboard.php" class="btn btn-info fw-bold" type="button">Dashboard</a>
              <?php elseif ($roleID == 3): ?>
                <a href="admin_dashboard.php" class="btn btn-warning fw-bold" type="button">Admin</a>
              <?php endif; ?>
            <?php else: ?>
              <a href="register.html" class="btn btn-success fw-bold" type="button">Register</a>
              <a href="login.html" class="btn btn-secondary fw-bold" type="button">Login</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>

    <!-- available providers -->
    <main class="container m-md-5">
      <h1 class="fs-1 text-center"><?php echo htmlspecialchars($serviceData['serviceName'] ?? 'Service'); ?></h1>
      <h2 class="fs-3 text-center text-secondary">Available Providers</h2>

      <div class="container mt-5">
        <div class="row g-2">
          <?php if (!$serviceData): ?>
            <div class="col-12">
              <div class="alert alert-warning text-center" role="alert">
                Service not found. Please return to <a href="service.php" class="alert-link">services</a> and choose again.
              </div>
            </div>
          <?php elseif (!$providersResult || $providersResult->num_rows === 0): ?>
            <div class="col-12">
              <div class="alert alert-info text-center" role="alert">
                No providers are available for this service right now.
              </div>
            </div>
          <?php else: ?>
            <?php while ($provider = $providersResult->fetch_assoc()): ?>
              <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100" style="width: auto">
                  <img src="<?php echo htmlspecialchars($provider['profilePicture'] ?: 'images/placeholder/provider.jpg'); ?>" class="card-img-top" alt="Provider photo" />
                  <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($provider['userName']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($provider['area'] . ', ' . $provider['district']); ?></p>
                  </div>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item"><?php echo htmlspecialchars($serviceData['serviceName']); ?></li>
                    <li class="list-group-item">Price: BDT <?php echo number_format($serviceData['price'], 2); ?></li>
                    <li class="list-group-item">Rating: <?php echo number_format($provider['avg_rating'] ?? 0, 1); ?> (<?php echo $provider['review_count']; ?>)</li>
                    <li class="list-group-item">Distance: N/A</li>
                  </ul>
                  <div class="card-body">
                    <a href="provider_profile.php?providerID=<?php echo htmlspecialchars($provider['userID']); ?>&serviceID=<?php echo $serviceID; ?>" class="btn btn-primary card-link">View Profile</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>
    </main>

    <!-- footer -->
    <footer class="bg-primary text-light py-4">
      <div class="container text-center">
        <p>&copy; 2024 UtilityLocator. All rights reserved.</p>
      </div>
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
