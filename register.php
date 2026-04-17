<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$selectedRole = $_GET['role'] ?? ($_POST['role'] ?? 'customer');
$selectedRole = $selectedRole === 'provider' ? 'provider' : 'customer';
$districtOptions = ['Dhaka'];
$areaOptions = dhaka_area_options();

$formData = [
    'f_name' => '',
    'l_name' => '',
    'email' => '',
    'phone' => '',
    'postal_code' => '',
    'district' => 'Dhaka',
    'area' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedRole = ($_POST['role'] ?? '') === 'provider' ? 'provider' : 'customer';
    $formData = [
        'f_name' => trim($_POST['f_name'] ?? ''),
        'l_name' => trim($_POST['l_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'district' => trim($_POST['district'] ?? 'Dhaka'),
        'area' => trim($_POST['area'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $serviceIds = array_map('intval', $_POST['service_ids'] ?? []);

    try {
        if ($formData['f_name'] === '' || $formData['l_name'] === '' || $formData['email'] === '' || $formData['phone'] === '' || $password === '' || $formData['postal_code'] === '' || $formData['district'] === '' || $formData['area'] === '') {
            throw new RuntimeException('Please complete all required fields.');
        }

        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please enter a valid email address.');
        }

        if (strlen($password) < 8) {
            throw new RuntimeException('Password must be at least 8 characters long.');
        }

        if (!in_array($formData['district'], $districtOptions, true)) {
            throw new RuntimeException('Please select a valid district.');
        }

        if (!in_array($formData['area'], $areaOptions, true)) {
            throw new RuntimeException('Please select a valid Dhaka area.');
        }

        if ($selectedRole === 'provider' && $serviceIds === []) {
            throw new RuntimeException('Please select at least one service you provide.');
        }

        $exists = pdo()->prepare('SELECT id FROM users WHERE email = :email OR phone = :phone LIMIT 1');
        $exists->execute([
            'email' => $formData['email'],
            'phone' => $formData['phone'],
        ]);

        if ($exists->fetch()) {
            throw new RuntimeException('Email or phone already exists.');
        }

        $imagePath = $selectedRole === 'provider' ? save_uploaded_image($_FILES['image'] ?? []) : null;
        $pdo = pdo();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO users (f_name, l_name, email, phone, password, role, postal_code, district, area, lat, lng, image, is_verified)
             VALUES (:f_name, :l_name, :email, :phone, :password, :role, :postal_code, :district, :area, NULL, NULL, :image, :is_verified)'
        );
        $stmt->execute([
            'f_name' => $formData['f_name'],
            'l_name' => $formData['l_name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $selectedRole,
            'postal_code' => $formData['postal_code'],
            'district' => $formData['district'],
            'area' => $formData['area'],
            'image' => $imagePath,
            'is_verified' => $selectedRole === 'customer' ? 1 : 0,
        ]);

        $userId = (int) $pdo->lastInsertId();

        if ($selectedRole === 'provider') {
            $psStmt = $pdo->prepare('INSERT INTO provider_services (provider_id, service_id) VALUES (:provider_id, :service_id)');
            foreach ($serviceIds as $serviceId) {
                $psStmt->execute([
                    'provider_id' => $userId,
                    'service_id' => $serviceId,
                ]);
            }
        }

        $pdo->commit();
        set_flash('success', $selectedRole === 'provider'
            ? 'Provider registration submitted. Admin approval is required before login.'
            : 'Customer registration completed. Please login.'
        );
        redirect('login.php');
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        set_flash('error', $e->getMessage());
    }
}

$services = fetch_services();
render_layout_start('Register', '');
?>
<section class="section auth-shell">
  <div class="shell">
    <div class="register-stage">
      <div class="register-panel register-panel--intro">
        <span class="eyebrow"><?= $selectedRole === 'provider' ? 'Provider onboarding' : 'Customer onboarding' ?></span>
        <h1 class="register-title"><?= $selectedRole === 'provider' ? 'Join as a verified worker for Dhaka-based service delivery.' : 'Create a customer account and request utility help with confidence.' ?></h1>
        <p class="section-copy">The registration flow is now based on district and preferred area instead of map coordinates. Providers apply with services and location preference, and the admin approves and assigns work manually.</p>

        <div class="register-toggle">
          <a class="<?= $selectedRole === 'customer' ? 'register-chip register-chip--active' : 'register-chip' ?>" href="register.php?role=customer">Get Service</a>
          <a class="<?= $selectedRole === 'provider' ? 'register-chip register-chip--active' : 'register-chip' ?>" href="register.php?role=provider">Become a Worker</a>
        </div>

        <div class="register-highlights">
          <div class="mini-card">
            <strong>District model</strong>
            <p>Keep all accounts under Dhaka for now, then choose a preferred service area.</p>
          </div>
          <div class="mini-card">
            <strong>Admin controlled</strong>
            <p>Workers cannot start instantly. Admin approval and assignment keep the workflow organized.</p>
          </div>
          <div class="mini-card">
            <strong>Professional setup</strong>
            <p>Blue-first, balanced presentation with clearer hierarchy for your project demo.</p>
          </div>
        </div>
      </div>

      <div class="register-panel register-panel--form">
        <div class="register-form-head">
          <h2><?= $selectedRole === 'provider' ? 'Provider Application' : 'Create Account' ?></h2>
          <p><?= $selectedRole === 'provider' ? 'Upload your profile image, choose your services, and set your preferred Dhaka area.' : 'Customers register faster and can add their preferred area for easier admin dispatch.' ?></p>
        </div>

        <form method="post" enctype="multipart/form-data" class="form-grid register-form-grid">
          <input type="hidden" name="role" value="<?= e($selectedRole) ?>">

          <div class="field">
            <label for="f_name">First name</label>
            <input id="f_name" name="f_name" type="text" value="<?= e($formData['f_name']) ?>" required>
          </div>
          <div class="field">
            <label for="l_name">Last name</label>
            <input id="l_name" name="l_name" type="text" value="<?= e($formData['l_name']) ?>" required>
          </div>
          <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="<?= e($formData['email']) ?>" required>
          </div>
          <div class="field">
            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="text" value="<?= e($formData['phone']) ?>" required>
          </div>
          <div class="field">
            <label for="postal_code">Postal code</label>
            <input id="postal_code" name="postal_code" type="text" value="<?= e($formData['postal_code']) ?>" required>
          </div>
          <div class="field">
            <label for="district">District</label>
            <select id="district" name="district" required>
              <?php foreach ($districtOptions as $districtOption): ?>
                <option value="<?= e($districtOption) ?>" <?= $formData['district'] === $districtOption ? 'selected' : '' ?>><?= e($districtOption) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field field-full">
            <label for="area">Preferred area in Dhaka</label>
            <select id="area" name="area" required>
              <option value="">Select area</option>
              <?php foreach ($areaOptions as $areaOption): ?>
                <option value="<?= e($areaOption) ?>" <?= $formData['area'] === $areaOption ? 'selected' : '' ?>><?= e($areaOption) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field field-full">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="8" required>
          </div>

          <?php if ($selectedRole === 'provider'): ?>
            <div class="field field-full">
              <label for="service_ids">Services you provide</label>
              <select id="service_ids" name="service_ids[]" multiple size="5" required>
                <?php foreach ($services as $service): ?>
                  <option value="<?= (int) $service['id'] ?>"><?= e($service['name']) ?> - BDT <?= number_format((float) $service['price'], 0) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="helper-text">Hold Ctrl or Command to select multiple services.</p>
            </div>
            <div class="field field-full">
              <label for="image">Profile image</label>
              <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" required>
              <p class="helper-text">You can replace demo images later from code or by uploading a new provider photo.</p>
            </div>
          <?php endif; ?>

          <div class="field field-full">
            <button class="primary-btn register-submit" type="submit">
              <?= $selectedRole === 'provider' ? 'Submit Provider Application' : 'Create Customer Account' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php render_layout_end(); ?>
