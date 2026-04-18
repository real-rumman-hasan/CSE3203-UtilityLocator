<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function render_layout_start(string $title, string $active = ''): void
{
    $user = current_user();
    $flash = get_flash();
    $navItems = [
        'services' => 'Services',
        'about' => 'About',
        'review' => 'Review',
        'help' => 'Help',
        'faq' => 'FAQ',
    ];
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/app.css">
</head>
<body>
  <header class="site-header">
    <nav class="navbar shell">
      <a class="brand" href="index.php">
          <a class="navbar-brand mx-auto fw-bold" href="index.php">
            <img src="images/logos/logo.svg" width="60" alt="Logo" />
          </a>
        <span><?= e(APP_NAME) ?></span>
      </a>
      <div class="nav-links">
        <?php foreach ($navItems as $key => $label): ?>
          <a class="<?= $active === $key ? 'is-active' : '' ?>" href="<?= match ($key) {
              'services' => 'service.php',
              'about' => 'about.php',
              'review' => 'rating.php',
              'help' => 'help.php',
              default => 'faq.php',
          } ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="nav-actions">
        <?php if ($user): ?>
          <span class="user-pill"><?= e($user['name']) ?></span>
          <?php if ($user['role'] === 'provider'): ?>
            <a class="ghost-btn" href="provider_dashboard.php">Dashboard</a>
          <?php elseif ($user['role'] === 'admin'): ?>
            <a class="ghost-btn" href="admin_dashboard.php">Admin</a>
          <?php elseif ($user['role'] === 'customer'): ?>
            <a class="ghost-btn" href="profile.php">Profile</a>
          <?php endif; ?>
          <a class="primary-btn" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="ghost-btn" href="login.php">Login</a>
          <a class="primary-btn" href="register.php">Register</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>
  <?php if ($flash): ?>
    <div class="shell flash-wrap">
      <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
  <?php endif; ?>
  <main>
<?php
}

function render_layout_end(): void
{
    ?>
  </main>
  <footer class="site-footer">
    <div class="shell footer-grid">
      <div>
        <h3><?= e(APP_NAME) ?></h3>
        <p>Professional utility booking software with admin approval, assignment control, and secure payment-ready workflow.</p>
      </div>
      <div>
        <p>Support: <?= e(SUPPORT_EMAIL) ?></p>
        <p>WCAG-conscious blue and white interface for clear readability and presentation quality.</p>
      </div>
    </div>
  </footer>
</body>
</html>
<?php
}
