<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$provider = require_role('provider');

pdo()->exec("UPDATE bookings SET status = 'expired' WHERE provider_id = " . (int) $provider['id'] . " AND status = 'pending' AND expires_at IS NOT NULL AND expires_at <= NOW()");

$metricsStmt = pdo()->prepare(
    'SELECT
        SUM(status = "pending") AS pending_count,
        SUM(status = "confirmed") AS confirmed_count,
        SUM(status = "completed") AS completed_count
     FROM bookings
     WHERE provider_id = :provider_id'
);
$metricsStmt->execute(['provider_id' => (int) $provider['id']]);
$metrics = $metricsStmt->fetch() ?: ['pending_count' => 0, 'confirmed_count' => 0, 'completed_count' => 0];

$bookingsStmt = pdo()->prepare(
    'SELECT
        b.id,
        b.status,
        b.payment_status,
        b.message,
        b.created_at,
        b.expires_at,
        s.name AS service_name,
        COALESCE(ps.custom_price, s.price) AS amount,
        u.f_name,
        u.l_name,
        u.phone
     FROM bookings b
     INNER JOIN users u ON u.id = b.customer_id
     INNER JOIN services s ON s.id = b.service_id
     LEFT JOIN provider_services ps ON ps.provider_id = b.provider_id AND ps.service_id = b.service_id
     WHERE b.provider_id = :provider_id
       AND b.status IN ("pending", "confirmed", "completed", "cancelled", "expired")
     ORDER BY b.created_at DESC'
);
$bookingsStmt->execute(['provider_id' => (int) $provider['id']]);
$bookings = $bookingsStmt->fetchAll();

render_layout_start('Provider Dashboard', '');
?>
<section class="section">
  <div class="shell">
    <span class="eyebrow">Worker dashboard</span>
    <h2 class="section-title" style="font-size: 2.5rem;">Live booking queue</h2>
    <p class="section-copy">Only admin-assigned jobs appear here. New assignments stay actionable for 2 minutes, and they expire automatically if not confirmed in time.</p>

    <div class="stats-grid">
      <div class="metric-card">
        <p class="muted">Pending</p>
        <div class="metric-value"><?= (int) $metrics['pending_count'] ?></div>
      </div>
      <div class="metric-card">
        <p class="muted">Confirmed</p>
        <div class="metric-value"><?= (int) $metrics['confirmed_count'] ?></div>
      </div>
      <div class="metric-card">
        <p class="muted">Completed</p>
        <div class="metric-value"><?= (int) $metrics['completed_count'] ?></div>
      </div>
    </div>

    <div class="panel" style="margin-top: 24px;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Booking</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Timer</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($bookings): ?>
              <?php foreach ($bookings as $booking): ?>
                <tr data-booking-id="<?= (int) $booking['id'] ?>" data-status="<?= e($booking['status']) ?>" data-expires-at="<?= e((string) $booking['expires_at']) ?>">
                  <td>
                    <strong><?= e($booking['service_name']) ?></strong><br>
                    <span class="helper-text"><?= e((string) $booking['message']) ?></span>
                  </td>
                  <td><?= e($booking['f_name'] . ' ' . $booking['l_name']) ?><br><span class="helper-text"><?= e($booking['phone']) ?></span></td>
                  <td>BDT <?= number_format((float) $booking['amount'], 2) ?></td>
                  <td><span class="status-badge status-<?= e(booking_status_badge($booking['status'])) ?>"><?= e(strtoupper($booking['status'])) ?></span></td>
                  <td class="countdown-cell">
                    <?php if ($booking['status'] === 'pending' && $booking['expires_at']): ?>
                      <span class="countdown">Loading...</span>
                    <?php else: ?>
                      <span class="helper-text">Closed</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($booking['status'] === 'pending'): ?>
                      <div class="inline-actions">
                        <button class="primary-btn booking-action" data-action="confirm" data-id="<?= (int) $booking['id'] ?>" type="button">Confirm</button>
                        <button class="danger-btn booking-action" data-action="cancel" data-id="<?= (int) $booking['id'] ?>" type="button">Cancel</button>
                      </div>
                    <?php else: ?>
                      <span class="helper-text">No action</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="empty-state">No bookings have reached this provider account yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<script src="worker-dashboard.js"></script>
<?php render_layout_end(); ?>
