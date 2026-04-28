<?php
declare(strict_types=1);
// ini_set('display_errors', 1); 
// ini_set('display_startup_errors', 1); 
// error_reporting(E_ALL);

require_once __DIR__ . '/partials.php';
require_once "dbapi.php";

require_role('admin');

$providerId = (int) ($_GET['id'] ?? 0);

if ($providerId <= 0) {
    set_flash('error', 'Invalid provider ID.');
    redirect('admin_dashboard.php');
}

$data = ['id' => $providerId];
$provider = post_to_api('get_one_user.php', $data);

if (!$provider) {
    set_flash('error', 'Provider not found.');
    // redirect('admin_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f_name = trim($_POST['f_name'] ?? '');
    $l_name = trim($_POST['l_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');

    $errors = [];
    if ($f_name === '' || $l_name === '') $errors[] = 'First and last name are required.';
    if (!is_alpha_name($f_name) || !is_alpha_name($l_name)) $errors[] = 'First name and last name must contain alphabet letters only.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_gmail_address($email)) $errors[] = 'A valid Gmail address is required.';
    if (!is_bd_mobile($phone)) $errors[] = 'Phone number must be a valid 11-digit Bangladeshi mobile number.';

    if (empty($errors)) {
        $data = [
          'id' => $providerId,
          'email' => $email
        ];

        $emailCheck = post_to_api('get_email_uniqueness.php', $data);

        if ($emailCheck) {
            $errors[] = 'Email is already taken by another user.';
        }
    }

    if (empty($errors)) {
        $_POST['id'] = $providerId;

        $update = post_to_api('update_provider.php', $_POST);
        
        if ($update['success']) {
          set_flash('success', 'Provider details updated successfully.');
        }
        redirect('admin_dashboard.php');
    } else {
        set_flash('error', $errors[0]);
    }
}

render_layout_start('Update Provider', '');
?>
<section class="section">
  <div class="shell" style="max-width: 600px; margin: 0 auto; display: flex; flex-direction: column; align-items: flex-start;">
    <a href="admin_dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; text-decoration: none; color: var(--brand-color); font-weight: 600;">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      Back to Dashboard
    </a>
    
    <div class="panel" style="width: 100%;">
      <h3>Update Worker Information</h3>
      <p class="helper-text" style="margin-bottom: 24px;">Edit the details of worker <strong><?= e($provider['f_name'] . ' ' . $provider['l_name']) ?></strong>.</p>
      
      <form method="post">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div class="form-group">
            <label for="f_name" style="display: block; margin-bottom: 8px; font-weight: 500;">First Name</label>
            <input type="text" id="f_name" name="f_name" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['f_name'] ?? $provider['f_name']) ?>" pattern="[A-Za-z]+" title="Use alphabet letters only" required>
          </div>
          <div class="form-group">
            <label for="l_name" style="display: block; margin-bottom: 8px; font-weight: 500;">Last Name</label>
            <input type="text" id="l_name" name="l_name" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['l_name'] ?? $provider['l_name']) ?>" pattern="[A-Za-z]+" title="Use alphabet letters only" required>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email Address</label>
          <input type="email" id="email" name="email" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['email'] ?? $provider['email']) ?>" pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Use a Gmail address only" required>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 500;">Phone Number</label>
          <input type="text" id="phone" name="phone" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['phone'] ?? $provider['phone']) ?>" inputmode="numeric" maxlength="11" pattern="01[0-9]{9}" title="Enter an 11-digit Bangladeshi mobile number" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
          <div class="form-group">
            <label for="district" style="display: block; margin-bottom: 8px; font-weight: 500;">District</label>
            <input type="text" id="district" name="district" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['district'] ?? $provider['district']) ?>">
          </div>
          <div class="form-group">
            <label for="area" style="display: block; margin-bottom: 8px; font-weight: 500;">Area</label>
            <input type="text" id="area" name="area" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['area'] ?? $provider['area']) ?>">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <label for="postal_code" style="display: block; margin-bottom: 8px; font-weight: 500;">Postal Code</label>
          <input type="text" id="postal_code" name="postal_code" class="input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" value="<?= e($_POST['postal_code'] ?? $provider['postal_code']) ?>">
        </div>

        <button type="submit" class="primary-btn" style="width: 100%; padding: 12px; font-size: 1rem; border-radius: 6px;">Save Changes</button>
      </form>
    </div>
  </div>
</section>
<?php render_layout_end(); ?>
