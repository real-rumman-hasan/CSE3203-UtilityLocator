<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';
require_once "dbapi.php";

if (is_logged_in()) {
    redirect(login_redirect_path(current_user()['role']));
}

$email = $_COOKIE['remember_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        set_flash('error', 'Email and password are required.');
        redirect('login.php');
    }

    $user = post_to_api('get_user_by_email.php', $_POST);

    if (!$user || !password_verify($password, $user['password'])) {
        set_flash('error', 'Invalid email or password.');
        redirect('login.php');
    }

    if ($user['role'] === 'provider' && (int) $user['is_verified'] !== 1) {
        set_flash('error', 'Your provider account is pending admin verification.');
        redirect('login.php');
    }

    login_user($user);

    if ($remember) {
        setcookie('remember_email', $email, [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'samesite' => 'Lax',
        ]);
        set_remember_token((int) $user['id']);
    } else {
        clear_remember_token((int) $user['id']);
        setcookie('remember_email', '', time() - 3600, '/');
    }

    redirect(login_redirect_path($user['role']));
}

render_layout_start('Login', '');
?>

<section class="section">
  <div class="shell two-col" style="grid-template-columns: 1fr 1fr;">
    <div class="panel">
      <span class="eyebrow">Secure login</span>
      <h2 class="section-title" style="font-size: 2.4rem;">Welcome back</h2>
      <p class="section-copy">
        Login with your email and password. Admins are redirected to the admin dashboard,
        providers to the worker dashboard, and customers back to the booking experience.
      </p>
    </div>
    <div class="auth-card">
      <form method="post" class="form-grid">
        <div class="field field-full">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="<?= e($email) ?>" required>
        </div>
        <div class="field field-full">
          <label for="password">Password</label>
          <div class="password-field">
            <input id="password" name="password" type="password" required>
            <button class="password-toggle" type="button" data-password-toggle data-target="password" aria-pressed="false">Show</button>
          </div>
        </div>
        <div class="field field-full">
          <label style="display:flex; align-items:center; gap:10px; font-weight:600;">
            <input type="checkbox" name="remember" value="1" style="width:auto;">
            Remember me for 30 days
          </label>
        </div>
        <div class="field field-full">
          <button class="primary-btn" type="submit">Login</button>
        </div>
        <div class="field field-full">
          <p class="helper-text">Need an account? <a href="register.php" style="color: var(--brand); font-weight: 700;">Register here</a>.</p>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.dataset.target;
      const input = document.getElementById(targetId);
      if (!input) {
        return;
      }

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      button.textContent = isPassword ? 'Hide' : 'Show';
      button.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
    });
  });
});
</script>

<?php render_layout_end(); ?>
