<?php
session_start();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Provider Profile</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <style>
      #main {
        min-height: 100vh;
      }

      .avatar {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
      }

      .rating {
        color: #f6c84c;
      }

      @media (max-width: 575.98px) {
        .avatar {
          width: 110px;
          height: 110px;
        }
      }
    </style>
  </head>

  <body class="bg-light">
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
                <li>
                  <hr class="dropdown-divider" />
                </li>
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

    <div id="main" class="container py-4">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <div class="card shadow-sm">
            <div class="card-body">
              <div
                class="d-flex flex-column flex-md-row align-items-center gap-3"
              >
                <img
                  src="images/placeholder/provider.jpg"
                  alt="Provider photo"
                  class="avatar"
                />

                <div class="w-100">
                  <div
                    class="d-flex align-items-start justify-content-between flex-column flex-sm-row gap-2"
                  >
                    <div>
                      <h4 class="mb-1">Alex Rivera</h4>
                      <p class="text-muted mb-1">
                        Licensed Electrician • 8 years experience
                      </p>
                      <p class="mb-2">
                        Quick, reliable electrical repairs and installations.
                        Available for emergency calls and scheduled jobs.
                      </p>
                    </div>

                    <div class="text-sm-end">
                      <h5 class="mb-1">$85</h5>
                      <div class="text-muted small">per hour</div>
                    </div>
                  </div>

                  <div class="row mt-3 gx-2 gy-2">
                    <div class="col-auto">
                      <div class="badge bg-primary text-white">
                        Service: Electrical Repair
                      </div>
                    </div>

                    <div class="col-auto d-flex align-items-center">
                      <div class="me-2 small text-muted">Rating</div>
                      <div class="rating" aria-label="4.6 out of 5">
                        ★★★★☆
                        <span class="small text-muted ms-2">4.6 (128)</span>
                      </div>
                    </div>

                    <div class="col-auto d-flex align-items-center">
                      <div class="me-2 small text-muted">Distance</div>
                      <div class="fw-semibold">3.2 km</div>
                    </div>
                  </div>

                  <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                    <a href="payment.html" class="btn btn-success btn-lg flex-grow-1"
                      >Order Service</a
                    >
                    <a href="#" class="btn btn-outline-secondary btn-lg"
                      >Chat</a
                    >
                  </div>
                </div>
              </div>

              <hr class="my-4" />

              <div class="row">
                <div class="col-12 col-md-6">
                  <h6>About</h6>
                  <p class="text-muted">
                    Alex provides residential and commercial electrical services
                    including wiring, outlets, lighting installations,
                    troubleshooting, and safety inspections. Licensed and
                    insured.
                  </p>
                </div>

                <div class="col-12 col-md-6">
                  <h6>Details</h6>
                  <ul class="list-unstyled small mb-0">
                    <li>
                      <strong>Response time:</strong> Typically within 1 hour
                    </li>
                    <li><strong>Service area:</strong> Within 10 km</li>
                    <li><strong>Payment:</strong> Card, Cash, Mobile pay</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Optional: similar providers / other actions -->
          <div class="mt-3 text-center small text-muted">
            Verified provider • Background checked
          </div>
        </div>
      </div>
    </div>

    <!-- footer -->
    <footer
      class="container-fluid p-5 text-center fw-bold bg-primary text-light"
    >
      &copy; TeamOne
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    
<script>
fetch("profile_fetch.php")
.then(res => res.json())
.then(data => {
    document.querySelector("#name").innerText = data.userName;
    document.querySelector("#email").innerText = data.email;
    document.querySelector("#phone").innerText = data.phone;
});
</script>

<script>
fetch("orders_fetch.php")
.then(res => res.json())
.then(data => {
    let container = document.querySelector(".list-group");
    container.innerHTML = "";

    data.forEach(order => {
        container.innerHTML += `
        <div class="list-group-item mb-3 shadow-sm rounded">
            <h5>Provider: ${order.userName}</h5>
            <p><strong>Date:</strong> ${order.scheduleDate}</p>
            <p><strong>Status:</strong> ${order.status}</p>
        </div>
        `;
    });
});
</script>
  </body>
</html>
