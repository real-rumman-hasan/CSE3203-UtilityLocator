<?php
session_start();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile Page</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
      crossorigin="anonymous"
    />

    <style>
      main {
        min-height: 100vh;
      }
    </style>
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

    <main class="container p-5">
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button
            class="nav-link active"
            id="profile-tab"
            data-bs-toggle="tab"
            data-bs-target="#profile"
            type="button"
            role="tab"
            aria-controls="profile"
            aria-selected="true"
          >
            Profile
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button
            class="nav-link"
            id="orders-tab"
            data-bs-toggle="tab"
            data-bs-target="#orders"
            type="button"
            role="tab"
            aria-controls="orders"
            aria-selected="false"
          >
            Orders
          </button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div
          class="tab-pane fade show active"
          id="profile"
          role="tabpanel"
          aria-labelledby="profile-tab"
        >
          <!-- profile content -->
          <img
            src="images/placeholder/provider.jpg"
            class="img-fluid p-3"
            alt="customer image"
            width="250px"
          />
          <hr />

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <h6 class="mb-1">Full name</h6>
              <p class="mb-0">Jane Doe</p>
            </div>

            <div class="col-12 col-md-6">
              <h6 class="mb-1">Email</h6>
              <p class="mb-0">
                <a href="mailto:jane.doe@example.com">jane.doe@example.com</a>
              </p>
            </div>

            <div class="col-12 col-md-6">
              <h6 class="mb-1">Phone</h6>
              <p class="mb-0"><a href="tel:+1234567890">+880123345345</a></p>
            </div>

            <div class="col-12 col-md-6">
              <h6 class="mb-1">User type</h6>
              <p class="mb-0">
                <span class="badge bg-info text-dark">Customer</span>
              </p>
            </div>

            <div class="col-12">
              <h6 class="mb-1">User status</h6>
              <p class="mb-0">
                <span class="fw-semibold">Active</span> — Email verified, phone
                verified
              </p>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <a href="/profile/edit" class="btn btn-primary">Edit Profile</a>
            <a href="/settings" class="btn btn-outline-secondary">Settings</a>
          </div>
        </div>
        <div
          class="tab-pane fade"
          id="orders"
          role="tabpanel"
          aria-labelledby="orders-tab"
        >
          <!-- orders content -->
          <div class="container mt-4">
            <div class="list-group">
              <!-- Order Item -->
              <div
                class="list-group-item list-group-item-action mb-3 shadow-sm rounded"
              >
                <div
                  class="d-flex w-100 justify-content-between align-items-center"
                >
                  <!-- Left side: Order Info -->
                  <div>
                    <h5 class="mb-1">Provider: John Smith</h5>
                    <p class="mb-1">
                      <strong>Service:</strong> Electrical Repair <br />
                      <strong>Scheduled Date:</strong> 10 April 2026
                    </p>
                  </div>

                  <!-- Right side: Chat Button -->
                  <div>
                    <a href="#" class="btn btn-primary"> Chat </a>
                  </div>
                </div>
              </div>

              <!-- Order Item -->
              <div
                class="list-group-item list-group-item-action mb-3 shadow-sm rounded"
              >
                <div
                  class="d-flex w-100 justify-content-between align-items-center"
                >
                  <div>
                    <h5 class="mb-1">Provider: Mr. Abdur Rahman</h5>
                    <p class="mb-1">
                      <strong>Service:</strong> Gas Line Fix <br />
                      <strong>Scheduled Date:</strong> 5 April 2026
                    </p>
                  </div>

                  <div>
                    <a href="#" class="btn btn-primary"> Chat </a>
                  </div>
                </div>
              </div>

              <!-- Add more orders dynamically -->
            </div>
          </div>
        </div>
      </div>
    </main>

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
