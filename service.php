<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$services = fetch_services();
$selectedServiceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0;
$selectedService = $selectedServiceId > 0 ? fetch_service_by_id($selectedServiceId) : null;
$providers = [];
$user = current_user();

if ($selectedService) {
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
            u.lat,
            u.lng,
            u.image,
            COALESCE(ps.custom_price, s.price) AS display_price,
            COALESCE(AVG(r.rating), 0) AS rating,
            COUNT(r.id) AS review_count
         FROM provider_services ps
         INNER JOIN users u ON u.id = ps.provider_id
         INNER JOIN services s ON s.id = ps.service_id
         LEFT JOIN reviews r ON r.provider_id = u.id
         WHERE ps.service_id = :service_id
           AND u.role = "provider"
           AND u.is_verified = 1
         GROUP BY u.id, ps.id, s.price
         ORDER BY rating DESC, u.updated_at DESC'
    );
    $stmt->execute(['service_id' => $selectedServiceId]);
    $providers = $stmt->fetchAll();

}

render_layout_start('Services', 'services');
?>

<section class="section">
  <div class="shell">
    <?php if ($selectedService): ?>
      <span class="eyebrow"><?= e($selectedService['name']) ?> providers</span>
      <h2 class="section-title" style="font-size: 2.6rem;"><?= e($selectedService['name']) ?> experts near you</h2>
      <p class="section-copy"><?= e($selectedService['desc']) ?></p>

      <div class="providers-grid">
        <?php if ($providers): ?>
          <?php foreach ($providers as $provider): ?>
            <article class="card">
              <div class="profile-header" style="grid-template-columns: 96px 1fr;">
                <img class="avatar" style="width:96px; height:96px;" src="<?= e($provider['image'] ?: default_avatar('Worker')) ?>" alt="<?= e($provider['f_name']) ?>">
                <div>
                  <span class="tag">Verified provider</span>
                  <h3><?= e($provider['f_name'] . ' ' . $provider['l_name']) ?></h3>
                  <p class="muted"><?= e($provider['area'] ?: 'Dhaka area') ?>, <?= e($provider['district']) ?></p>
                  <div class="rating-stars"><?= str_repeat('★', (int) round((float) $provider['rating'])) . str_repeat('☆', max(0, 5 - (int) round((float) $provider['rating']))) ?></div>
                  <p class="helper-text"><?= number_format((float) $provider['rating'], 1) ?> rating from <?= (int) $provider['review_count'] ?> reviews</p>
                </div>
              </div>
              <div class="service-card-price">BDT <?= number_format((float) $provider['display_price'], 0) ?></div>
              <p class="muted">Coverage area: <?= e($provider['area'] ?: 'Dhaka') ?>. Final worker assignment is confirmed by admin after payment.</p>
              <div class="cta-row">
                <a class="primary-btn" href="provider_profile.php?provider_id=<?= (int) $provider['id'] ?>&service_id=<?= (int) $selectedServiceId ?>">View</a>
                <?php if (!$user || $user['role'] !== 'customer'): ?>
                  <a class="ghost-btn" href="login.php">Login to book</a>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="panel empty-state">
            <h3>No verified providers yet</h3>
            <p>This service is ready, but no worker has been approved for it yet.</p>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <span class="eyebrow">Available services</span>
      <h2 class="section-title" style="font-size: 2.6rem;">Browse verified service categories</h2>
      <p class="section-copy">Each category shows approved providers, clear pricing, and a booking flow routed through admin assignment.</p>

      <div class="service-grid">
        <?php foreach ($services as $service): ?>
          <article class="card">
            <div class="service-card-icon"><?= strtoupper($service['name'][0]) ?></div>
            <h3><?= e($service['name']) ?></h3>
            <p class="muted"><?= e($service['desc']) ?></p>
            <div class="service-card-price">From BDT <?= number_format((float) $service['price'], 0) ?></div>
            <a class="primary-btn" href="service.php?service_id=<?= (int) $service['id'] ?>">View Providers</a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php render_layout_end(); ?>
