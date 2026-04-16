<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$services = fetch_services();
render_layout_start(APP_NAME, '');
?>

<section class="hero">
  <div class="shell hero-grid">
    <div>
      <span class="eyebrow">Fast, verified utility booking</span>
      <h1>Get trusted local help for home and office utility work.</h1>
      <p>
        Book verified providers for gas, sanitary, electrical, shifting, and locksmith work with
        professional dashboards, role-based access, and payment-ready booking flow.
      </p>
      <div class="cta-row">
        <a class="primary-btn" href="register.php?role=customer">Get Service</a>
        <a class="ghost-btn" href="register.php?role=provider">Become a Worker</a>
        <a class="secondary-btn" href="service.php">Browse Services</a>
      </div>
    </div>
    <div class="stack">
      <div class="card">
        <span class="tag">Customer flow</span>
        <h3>Get, compare, and book nearby professionals.</h3>
        <p class="muted">Customers can browse providers, see live distance estimates, and place paid service requests.</p>
      </div>
      <div class="card">
        <span class="tag">Provider flow</span>
        <h3>Receive live bookings with a 2-minute action window.</h3>
        <p class="muted">Providers get approval-based onboarding, service assignment, and timed booking confirmation tools.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" id="services">
  <div class="shell">
    <p class="eyebrow">Core services</p>
    <h2 class="section-title">Our Essential Services</h2>
    <p class="section-copy">Browse our comprehensive list of utility professional services to keep your property running securely.</p>

    <div class="service-grid">
      <?php foreach ($services as $service): ?>
        <article class="card">
          <div class="service-card-icon">
            <?= match ($service['name']) {
                'Gas' => 'G',
                'Sanitary' => 'S',
                'Electrical' => 'E',
                'Shifting' => 'M',
                default => 'L',
            } ?>
          </div>
          <h3><?= e($service['name']) ?></h3>
          <p class="muted"><?= e($service['desc']) ?></p>
          <div class="service-card-price">From BDT <?= number_format((float) $service['price'], 0) ?></div>
          <div class="cta-row">
            <a class="primary-btn" href="service.php?service_id=<?= (int) $service['id'] ?>">View</a>
            <a class="ghost-btn" href="register.php?role=customer">Get Service</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="shell stats-grid">
    <div class="metric-card">
      <p class="muted">Public website</p>
      <div class="metric-value">Open access</div>
      <p class="muted">Visitors can browse the homepage and service list before login.</p>
    </div>
    <div class="metric-card">
      <p class="muted">Admin control</p>
      <div class="metric-value">Provider approval</div>
      <p class="muted">Only verified workers appear on the frontend after admin approval.</p>
    </div>
    <div class="metric-card">
      <p class="muted">Booking SLA</p>
      <div class="metric-value">2 minutes</div>
      <p class="muted">Providers must confirm new requests quickly or they expire automatically.</p>
    </div>
  </div>
</section>

<?php render_layout_end(); ?>