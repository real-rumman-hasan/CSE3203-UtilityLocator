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
    <link rel="stylesheet" href="styles/faq.css">
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

    <!-- faq section -->
    <div class="container mt-5">
      <h1 class="fs-1">Frequently Asked Questions</h1>

      <div class="accordion" id="accordionFAQ">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button
              class="accordion-button"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseOne"
              aria-expanded="true"
              aria-controls="collapseOne"
            >
              <strong>What is Utility Locator?</strong>
            </button>
          </h2>
          <div
            id="collapseOne"
            class="accordion-collapse collapse show"
            aria-labelledby="headingOne"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              Utility Locator is an online platform that connects customers with service providers for various household utilities such as electrical work, gas services, sanitary repairs, locksmith services, and house shifting. It helps users quickly find and hire nearby professionals.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingTwo">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseTwo"
              aria-expanded="false"
              aria-controls="collapseTwo"
            >
              <strong>How do I create an account?</strong>
            </button>
          </h2>
          <div
            id="collapseTwo"
            class="accordion-collapse collapse"
            aria-labelledby="headingTwo"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              To create an account, click the Register button on the homepage. Fill in the required information and choose whether you want to register as a customer or a service provider. After submitting the form, you can log in using your credentials.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingThree">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseThree"
              aria-expanded="false"
              aria-controls="collapseThree"
            >
              <strong>How can I request a service?</strong>
            </button>
          </h2>
          <div
            id="collapseThree"
            class="accordion-collapse collapse"
            aria-labelledby="headingThree"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              After logging in as a customer, select a service category from the homepage. You will see a list of providers offering that service. Click on a provider to view their profile, where you can chat with them or send a job request.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingFour">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseFour"
              aria-expanded="false"
              aria-controls="collapseFour"
            >
              <strong>How do service providers receive job requests?</strong>
            </button>
          </h2>
          <div
            id="collapseFour"
            class="accordion-collapse collapse"
            aria-labelledby="headingFour"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              When a customer sends a job request, it appears in the provider’s profile page. Providers can review the request, communicate with the customer through chat, and then choose to accept or reject the request.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingFive">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseFive"
              aria-expanded="false"
              aria-controls="collapseFive"
            >
              <strong>Can I communicate with the provider before requesting a job?</strong>
            </button>
          </h2>
          <div
            id="collapseFive"
            class="accordion-collapse collapse"
            aria-labelledby="headingFive"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              Yes. Customers can use the chat feature on the provider's profile page to discuss job details before sending a job request.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingSix">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseSix"
              aria-expanded="false"
              aria-controls="collapseSix"
            >
              <strong>How can I track my job requests?</strong>
            </button>
          </h2>
          <div
            id="collapseSix"
            class="accordion-collapse collapse"
            aria-labelledby="headingSix"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              Customers can view all their pending and accepted job requests by visiting their profile page. This section allows users to keep track of their service requests.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingSeven">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseSeven"
              aria-expanded="false"
              aria-controls="collapseSeven"
            >
              <strong>Can I see my previous jobs?</strong>
            </button>
          </h2>
          <div
            id="collapseSeven"
            class="accordion-collapse collapse"
            aria-labelledby="headingSeven"
            data-bs-parent="#accordionFAQ"
          >
            <div class="accordion-body">
              Yes. Both customers and providers can access their job history from their profile page to review previously completed jobs.
            </div>
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

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
