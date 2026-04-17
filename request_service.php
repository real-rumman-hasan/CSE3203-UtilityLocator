<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$customer = require_role('customer');
$providerId = isset($_GET['provider_id']) ? (int) $_GET['provider_id'] : (int) ($_GET['providerID'] ?? 0);
$serviceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : (int) ($_GET['serviceID'] ?? 0);

$stmt = pdo()->prepare(
    'SELECT
        u.id AS provider_id,
        u.f_name,
        u.l_name,
        u.district,
        u.area,
        s.id AS service_id,
        s.name,
        s.`desc`,
        COALESCE(ps.custom_price, s.price) AS price
     FROM users u
     INNER JOIN provider_services ps ON ps.provider_id = u.id
     INNER JOIN services s ON s.id = ps.service_id
     WHERE u.id = :provider_id
       AND s.id = :service_id
       AND u.role = "provider"
       AND u.is_verified = 1
     LIMIT 1'
);
$stmt->execute([
    'provider_id' => $providerId,
    'service_id' => $serviceId,
]);
$selection = $stmt->fetch();

if (!$selection) {
    set_flash('error', 'The selected provider or service is unavailable.');
    redirect('service.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $transactionId = 'BOOK-' . date('YmdHis') . '-' . random_int(1000, 9999);

    $insert = pdo()->prepare(
        'INSERT INTO bookings (customer_id, provider_id, service_id, status, payment_status, message, transaction_id, expires_at)
         VALUES (:customer_id, :provider_id, :service_id, "awaiting_assignment", "pending", :message, :transaction_id, NULL)'
    );
    $insert->execute([
        'customer_id' => (int) $customer['id'],
        'provider_id' => $providerId,
        'service_id' => $serviceId,
        'message' => $message !== '' ? $message : null,
        'transaction_id' => $transactionId,
    ]);

    redirect('payment.php?booking_id=' . (int) pdo()->lastInsertId());
}

render_layout_start('Place Service', 'services');
?>
<section class="section">
  <div class="shell split-card" style="grid-template-columns: 1.05fr 0.95fr;">
    <div class="panel">
      <span class="eyebrow">Booking preview</span>
      <h2 class="section-title" style="font-size: 2.3rem;"><?= e($selection['name']) ?> with <?= e($selection['f_name'] . ' ' . $selection['l_name']) ?></h2>
      <p class="section-copy"><?= e($selection['desc']) ?></p>
      <div class="stack">
        <div class="card">
          <p class="muted">Provider area</p>
          <div class="metric-value" style="font-size:1.4rem;"><?= e(($selection['area'] ?: 'Dhaka area') . ', ' . $selection['district']) ?></div>
        </div>
        <div class="card">
          <p class="muted">Service charge</p>
          <div class="metric-value" style="font-size:1.4rem;">BDT <?= number_format((float) $selection['price'], 2) ?></div>
        </div>
      </div>
    </div>
    <div class="auth-card">
      <form method="post" class="form-grid">
        <div class="field field-full">
          <label for="message">Service details</label>
          <textarea id="message" name="message" placeholder="Describe the issue, preferred timing, access notes, or urgency."></textarea>
        </div>
        <div class="field field-full">
          <p class="helper-text">After payment, the booking enters the admin assignment queue. An admin must assign or confirm the worker before the provider can accept the job.</p>
        </div>
        <div class="field field-full">
          <button class="primary-btn" type="submit">Place Service</button>
        </div>
      </form>
    </div>
  </div>
</section>
<?php render_layout_end(); ?>
