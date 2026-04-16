<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$user = require_login();

if ($user['role'] === 'customer') {
    $stmt = pdo()->prepare(
        'SELECT
            b.id,
            b.status,
            b.payment_status,
            b.created_at,
            s.name AS service_name,
            CONCAT(u.f_name, " ", u.l_name) AS provider_name,
            r.id AS review_id
         FROM bookings b
         INNER JOIN services s ON s.id = b.service_id
         INNER JOIN users u ON u.id = b.provider_id
         LEFT JOIN reviews r ON r.booking_id = b.id
         WHERE b.customer_id = :user_id
         ORDER BY b.created_at DESC'
    );
} elseif ($user['role'] === 'provider') {
    $stmt = pdo()->prepare(
        'SELECT
            b.id,
            b.status,
            b.payment_status,
            b.created_at,
            s.name AS service_name,
            CONCAT(u.f_name, " ", u.l_name) AS customer_name
         FROM bookings b
         INNER JOIN services s ON s.id = b.service_id
         INNER JOIN users u ON u.id = b.customer_id
         WHERE b.provider_id = :user_id
         ORDER BY b.created_at DESC'
    );
} else {
    $stmt = pdo()->prepare(
        'SELECT
            id,
            status,
            payment_status,
            created_at,
            transaction_id AS service_name,
            "System" AS customer_name
         FROM bookings
         WHERE assigned_by_admin_id = :user_id
         ORDER BY created_at DESC'
    );
}

$stmt->execute(['user_id' => (int) $user['id']]);
$items = $stmt->fetchAll();
$location = fetch_user_location((int) $user['id']);

render_layout_start('Profile', '');
?>
<section class="section">
  <div class="shell lead-card-grid">
    <div class="panel">
      <span class="eyebrow">My account</span>
      <h2 class="section-title" style="font-size: 2.7rem;"><?= e($user['name']) ?></h2>
      <p class="section-copy">Role: <?= e(ucfirst($user['role'])) ?>. This page gives a quick view of your account details and recent booking history.</p>
      <div class="kpi-strip">
        <div class="kpi"><span class="muted">Email</span><strong style="font-size:1rem;"><?= e($user['email']) ?></strong></div>
        <div class="kpi"><span class="muted">Verification</span><strong style="font-size:1rem;"><?= (int) $user['is_verified'] === 1 ? 'Verified' : 'Pending' ?></strong></div>
        <div class="kpi"><span class="muted">District</span><strong style="font-size:1rem;"><?= e((string) ($location['district'] ?? 'Not set')) ?></strong></div>
        <div class="kpi"><span class="muted">Area</span><strong style="font-size:1rem;"><?= e((string) ($location['area'] ?? 'Not set')) ?></strong></div>
      </div>
    </div>

    <div class="panel">
      <h3>Recent activity</h3>
      <?php if ($items): ?>
        <div class="timeline">
          <?php foreach (array_slice($items, 0, 5) as $item): ?>
            <div class="timeline-step" data-step="#<?= (int) $item['id'] ?>">
              <strong><?= e($item['service_name']) ?></strong>
              <p class="helper-text">Status: <?= e(strtoupper((string) $item['status'])) ?> | Payment: <?= e(strtoupper((string) $item['payment_status'])) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="helper-text">No recent items found for this account.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="shell panel">
    <h3>Booking history</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Service</th>
            <th><?= $user['role'] === 'customer' ? 'Provider' : ($user['role'] === 'provider' ? 'Customer' : 'Reference') ?></th>
            <th>Status</th>
            <th>Payment</th>
            <th><?= $user['role'] === 'customer' ? 'Review' : 'Details' ?></th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($items): ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td>#<?= (int) $item['id'] ?></td>
                <td><?= e($item['service_name']) ?></td>
                <td><?= e((string) ($item['provider_name'] ?? $item['customer_name'] ?? 'System')) ?></td>
                <td><span class="status-badge status-<?= e(booking_status_badge((string) $item['status'])) ?>"><?= e(strtoupper((string) $item['status'])) ?></span></td>
                <td><?= e(strtoupper((string) $item['payment_status'])) ?></td>
                <td>
                  <?php if ($user['role'] === 'customer' && (string) $item['payment_status'] === 'paid' && in_array((string) $item['status'], ['confirmed', 'completed'], true)): ?>
                    <a class="ghost-btn" href="rating.php?booking_id=<?= (int) $item['id'] ?>"><?= !empty($item['review_id']) ? 'Edit Review' : 'Give Review' ?></a>
                  <?php else: ?>
                    <span class="helper-text"><?= $user['role'] === 'customer' ? 'Available after paid confirmation' : 'Managed by role workflow' ?></span>
                  <?php endif; ?>
                </td>
                <td><?= e((string) $item['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="empty-state">No booking history available.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php render_layout_end(); ?>
