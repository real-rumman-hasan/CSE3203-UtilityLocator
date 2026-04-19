<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';
require_once __DIR__ . '/payment-gateway.php';

$customer = require_role('customer');
$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : (int) ($_GET['requestID'] ?? 0);

$stmt = pdo()->prepare(
    'SELECT
        b.id,
        b.customer_id,
        b.provider_id,
        b.service_id,
        b.status,
        b.payment_status,
        b.message,
        b.transaction_id,
        b.expires_at,
        s.name AS service_name,
        COALESCE(ps.custom_price, s.price) AS amount,
        u.f_name,
        u.l_name,
        u.district
     FROM bookings b
     INNER JOIN services s ON s.id = b.service_id
     INNER JOIN users u ON u.id = b.customer_id
     LEFT JOIN provider_services ps ON ps.provider_id = b.provider_id AND ps.service_id = b.service_id
     WHERE b.id = :id AND b.customer_id = :customer_id
     LIMIT 1'
);
$stmt->execute([
    'id' => $bookingId,
    'customer_id' => (int) $customer['id'],
]);
$booking = $stmt->fetch();

if (!$booking) {
    set_flash('error', 'Booking not found.');
    redirect('index.php');
}

$errorMessage = '';
$successMessage = '';

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    if (!empty($_GET['val_id']) && !empty($_GET['tran_id'])) {
        $validation = sslcommerz_validate_transaction((string) $_GET['val_id'], (string) $_GET['tran_id']);
        if ($validation['success']) {
            $update = pdo()->prepare('UPDATE bookings SET payment_status = "paid" WHERE id = :id');
            $update->execute(['id' => $bookingId]);
            $successMessage = 'Payment validated successfully through SSLCommerz sandbox.';
        } else {
            $errorMessage = $validation['error'];
        }
    } else {
        $update = pdo()->prepare('UPDATE bookings SET payment_status = "paid" WHERE id = :id');
        $update->execute(['id' => $bookingId]);
        $successMessage = 'Sandbox success flow completed. Payment marked as paid.';
    }
} elseif (isset($_GET['status']) && in_array($_GET['status'], ['fail', 'cancel'], true)) {
    $errorMessage = 'Payment was not completed. Please try again.';
    $update = pdo()->prepare('UPDATE bookings SET payment_status = "failed" WHERE id = :id');
    $update->execute(['id' => $bookingId]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerStmt = pdo()->prepare('SELECT f_name, l_name, email, phone, district FROM users WHERE id = :id LIMIT 1');
    $customerStmt->execute(['id' => (int) $customer['id']]);
    $customerData = $customerStmt->fetch();

    $self = app_url('payment.php?booking_id=' . $bookingId);
    $result = sslcommerz_init_transaction(
        $booking,
        $customerData,
        app_url('success.php'),
        $self . '&status=fail',
        $self . '&status=cancel'
    );

    if ($result['success']) {
        redirect($result['redirectUrl']);
    }

    $errorMessage = $result['error'] . ' You can use the sandbox fallback button below for local testing.';
}

render_layout_start('Payment', 'services');
?>

<section class="section">
  <div class="shell split-card" style="grid-template-columns: 1fr 1fr;">
    <div class="panel">
      <span class="eyebrow">SSLCommerz sandbox</span>
      <h2 class="section-title" style="font-size: 2.3rem;">Complete your payment</h2>
      <p class="section-copy">This booking is created in BDT, then moves into the admin assignment queue after payment is completed.</p>
      <div class="stack">
        <div class="card">
          <p class="muted">Service</p>
          <div class="metric-value" style="font-size:1.4rem;"><?= e($booking['service_name']) ?></div>
        </div>
        <div class="card">
          <p class="muted">Amount</p>
          <div class="metric-value" style="font-size:1.4rem;">BDT <?= number_format((float) $booking['amount'], 2) ?></div>
        </div>
        <div class="card">
          <p class="muted">Workflow state</p>
          <span class="status-badge status-<?= e(booking_status_badge($booking['status'])) ?>"><?= e(strtoupper(str_replace('_', ' ', $booking['status']))) ?></span>
        </div>
      </div>
    </div>
    <div class="auth-card">
      <?php if ($errorMessage !== ''): ?>
        <div class="flash flash-error" style="margin-bottom: 18px;"><?= e($errorMessage) ?></div>
      <?php endif; ?>
      <?php if ($successMessage !== ''): ?>
        <div class="flash flash-success" style="margin-bottom: 18px;"><?= e($successMessage) ?></div>
      <?php endif; ?>

      <form method="post" class="form-grid">
        <div class="field field-full">
          <button class="primary-btn" type="submit">Pay with SSLCommerz Sandbox</button>
        </div>
      </form>
      <div class="cta-row">
        <a class="ghost-btn" href="payment.php?booking_id=<?= $bookingId ?>&status=success">Sandbox success fallback</a>
        <a class="danger-btn" href="payment.php?booking_id=<?= $bookingId ?>&status=cancel">Cancel payment</a>
      </div>
    </div>
  </div>
</section>

<?php render_layout_end(); ?>
