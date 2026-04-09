<?php
session_start();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link
      rel="shortcut icon"
      href="images/logos/logo.svg"
      type="image/x-icon"
    />
    <link rel="stylesheet" href="styles/about.css">
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
      crossorigin="anonymous"
    />
  </head>
  <body>
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
              <a class="nav-link active" aria-current="page" href="index.php"
                >Home</a
              >
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
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="service.php">Gas</a></li>
                <li>
                  <a class="dropdown-item" href="service.php">Sanitary</a>
                </li>
                <li>
                  <a class="dropdown-item" href="service.php">Electrical</a>
                </li>
                <li>
                  <a class="dropdown-item" href="service.php">Shifting</a>
                </li>
                <li>
                  <a class="dropdown-item" href="service.php">Lock Smith</a>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                  <a class="dropdown-item" href="service.php">Miscellaneous</a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="about.php">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="help.php">Help</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="faq.php">FAQ</a>
            </li>
          </ul>
          
<!-- Php to hide login and register button -->

          <?php if (isset($_SESSION['userID'])): ?>
            <div class="d-grid gap-2 d-md-block">
              <a
                href="customerprofile.php"
                class="btn btn-success fw-bold"
                type="button"
              >
               <?php echo htmlspecialchars($_SESSION['userName']); ?> 
              </a>
              <a href="logout.php" class="btn btn-secondary fw-bold" type="button"
                >Logout</a
              >
            </div>
          <?php else: ?>
            <div class="d-grid gap-2 d-md-block">
              <a
                href="register.html"
                class="btn btn-success fw-bold"
                type="button"
                >Register</a
              >
              <a href="login.html" class="btn btn-secondary fw-bold" type="button"
                >Login</a
              >
            </div>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- about section -->
    <div class="container">
      <h1 class="fs-1 mt-5">About Us</h1>
      <p class="fs-5">
        Utility Locator is a platform designed to make finding reliable
        household service providers quick and convenient. Our goal is to connect
        customers with trusted professionals who can handle everyday utility
        needs such as electrical work, gas services, sanitary repairs, house
        shifting, locksmith assistance, and other essential services.
      </p>
      <p class="fs-5">
        Many people struggle to find dependable service providers when they
        urgently need help. Utility Locator solves this problem by bringing
        multiple service providers together in one place, allowing users to
        easily search for nearby professionals and choose the one that best fits
        their needs.
      </p>
      <p class="fs-5">
        Our platform allows individuals to register either as customers looking
        for services or as providers offering their skills. Customers can browse
        available providers, view their details, and hire them for specific
        tasks. At the same time, service providers gain an opportunity to reach
        more customers and grow their business.
      </p>
      <p class="fs-5">
        Utility Locator aims to simplify the process of finding utility services
        by making it faster, more transparent, and accessible for everyone.
        Whether you need a quick repair, help moving to a new home, or
        assistance with essential home maintenance, our platform is here to help
        you find the right professional.
      </p>
    </div>

    <!-- footer -->
    <footer
      class="container-fluid p-5 text-center fw-bold bg-primary text-light"
    >
      &copy; TeamOne
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
