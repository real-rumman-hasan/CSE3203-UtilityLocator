<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$providerId = isset($_GET['provider_id']) ? (int) $_GET['provider_id'] : (int) ($_GET['providerID'] ?? 0);
$serviceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : (int) ($_GET['serviceID'] ?? 0);

$stmt = pdo()->prepare(
    'SELECT
        u.id,
        u.f_name,
        u.l_name,
        u.email,
        u.phone,
        u.district,
        u.area,
        u.postal_code,
        u.image,
        s.id AS service_id,
        s.name AS service_name,
        s.`desc` AS service_desc,
        COALESCE(ps.custom_price, s.price) AS display_price,
        COALESCE(AVG(r.rating), 0) AS rating,
        COUNT(r.id) AS review_count
     FROM users u
     INNER JOIN provider_services ps ON ps.provider_id = u.id
     INNER JOIN services s ON s.id = ps.service_id
     LEFT JOIN reviews r ON r.provider_id = u.id
     WHERE u.id = :provider_id
       AND s.id = :service_id
       AND u.role = "provider"
       AND u.is_verified = 1
     GROUP BY u.id, s.id, ps.id'
);
$stmt->execute([
    'provider_id' => $providerId,
    'service_id' => $serviceId,
]);
$provider = $stmt->fetch();

if (!$provider) {
    set_flash('error', 'Provider profile was not found.');
    redirect('service.php');
}

$user = current_user();
render_layout_start('Provider Profile', 'services');
?>
<section class="section">
  <div class="shell">
    <div class="panel">
      <div class="profile-header">
        <img class="avatar" src="<?= e($provider['image'] ?: default_avatar('Worker')) ?>" alt="<?= e($provider['f_name']) ?>">
        <div>
          <span class="tag"><?= e($provider['service_name']) ?> specialist</span>
          <h2 class="section-title" style="font-size: 2.4rem; margin-top: 14px;"><?= e($provider['f_name'] . ' ' . $provider['l_name']) ?></h2>
          <p class="section-copy">Serving <?= e($provider['area'] ?: 'Dhaka area') ?>, <?= e($provider['district']) ?> with verified identity and pricing ready for admin-reviewed checkout.</p>
          <div class="cta-row">
            <span class="status-badge status-primary">Rating <?= number_format((float) $provider['rating'], 1) ?>/5</span>
            <span class="status-badge status-success"><?= (int) $provider['review_count'] ?> reviews</span>
            <span class="status-badge status-warning">BDT <?= number_format((float) $provider['display_price'], 0) ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="dashboard-grid" style="margin-top: 24px;">
      <div class="panel">
        <h3>Service overview</h3>
        <p class="muted"><?= e($provider['service_desc']) ?></p>
        <p><strong>Preferred area:</strong> <?= e($provider['area'] ?: 'Dhaka area') ?></p>
        <p><strong>Contact:</strong> <?= e($provider['email']) ?>, <?= e($provider['phone']) ?></p>
        <p><strong>Coverage:</strong> <?= e($provider['district']) ?>, <?= e($provider['postal_code']) ?></p>
        <div class="rating-stars" style="margin-top: 16px;"><?= str_repeat('?', (int) round((float) $provider['rating'])) . str_repeat('?', max(0, 5 - (int) round((float) $provider['rating']))) ?></div>
      </div>

      <div class="panel">
        <h3>Book this provider</h3>
        <p class="muted">Customers can review provider profiles, then place a paid request with special instructions. Final worker assignment still stays under admin control.</p>
        <?php if ($user && $user['role'] === 'customer'): ?>
          <a class="primary-btn" href="request_service.php?provider_id=<?= $providerId ?>&service_id=<?= $serviceId ?>">Place Service</a>
        <?php else: ?>
          <a class="primary-btn" href="login.php">Login as customer</a>
        <?php endif; ?>
        <a class="ghost-btn" href="service.php?service_id=<?= $serviceId ?>" style="margin-top: 12px;">Back to providers</a>
      </div>
    </div>
  </div>
</section>
<?php render_layout_end(); ?>
