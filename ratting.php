<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$user = current_user();
$bookingId = (int) ($_GET['booking_id'] ?? 0);

if (!$user || $user['role'] !== 'customer') {
    render_layout_start('Customer Reviews', 'review');
    ?>
    <section class="section">
      <div class="shell">
        <div class="panel">
          <span class="eyebrow">Customer feedback</span>
          <h2 class="section-title" style="font-size: 2.6rem;">Reviews are available after paid customer bookings.</h2>
          <p class="section-copy">Log in as a customer to review confirmed or completed jobs. This keeps feedback tied to real paid service requests only.</p>
          <div class="inline-actions">
            <a class="primary-btn" href="login.php">Login</a>
            <a class="ghost-btn" href="index.php">Back Home</a>
          </div>
        </div>
      </div>
    </section>
    <?php
    render_layout_end();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $reviewText = trim($_POST['review_text'] ?? '');

    $eligibleStmt = pdo()->prepare(
        'SELECT b.id, b.provider_id
         FROM bookings b
         WHERE b.id = :booking_id
           AND b.customer_id = :customer_id
           AND b.payment_status = "paid"
           AND b.status IN ("confirmed", "completed")
         LIMIT 1'
    );
    $eligibleStmt->execute([
        'booking_id' => $bookingId,
        'customer_id' => (int) $user['id'],
    ]);
    $eligible = $eligibleStmt->fetch();

    if (!$eligible) {
        set_flash('error', 'This booking is not eligible for review yet.');
        redirect('rating.php');
    }

    if ($rating < 1 || $rating > 5) {
        set_flash('error', 'Please choose a rating between 1 and 5.');
        redirect('rating.php?booking_id=' . $bookingId);
    }

    $saveStmt = pdo()->prepare(
        'INSERT INTO reviews (booking_id, provider_id, customer_id, rating, review_text)
         VALUES (:booking_id, :provider_id, :customer_id, :rating, :review_text)
         ON DUPLICATE KEY UPDATE
           rating = VALUES(rating),
           review_text = VALUES(review_text)'
    );
    $saveStmt->execute([
        'booking_id' => $bookingId,
        'provider_id' => (int) $eligible['provider_id'],
        'customer_id' => (int) $user['id'],
        'rating' => $rating,
        'review_text' => $reviewText !== '' ? $reviewText : null,
    ]);

    set_flash('success', 'Your review has been saved successfully.');
    redirect('rating.php?booking_id=' . $bookingId);
}

$reviewableStmt = pdo()->prepare(
    'SELECT
        b.id,
        b.status,
        b.payment_status,
        b.created_at,
        s.name AS service_name,
        CONCAT(u.f_name, " ", u.l_name) AS provider_name,
        r.rating,
        r.review_text
     FROM bookings b
     INNER JOIN services s ON s.id = b.service_id
     INNER JOIN users u ON u.id = b.provider_id
     LEFT JOIN reviews r ON r.booking_id = b.id
     WHERE b.customer_id = :customer_id
       AND b.payment_status = "paid"
       AND b.status IN ("confirmed", "completed")
     ORDER BY b.created_at DESC'
);
$reviewableStmt->execute(['customer_id' => (int) $user['id']]);
$reviewableBookings = $reviewableStmt->fetchAll();

$selectedBooking = null;
if ($bookingId > 0) {
    foreach ($reviewableBookings as $booking) {
        if ((int) $booking['id'] === $bookingId) {
            $selectedBooking = $booking;
            break;
        }
    }
}

if (!$selectedBooking && $reviewableBookings) {
    $selectedBooking = $reviewableBookings[0];
}

$recentReviews = pdo()->query(
    'SELECT
        r.rating,
        r.review_text,
        r.created_at,
        s.name AS service_name,
        CONCAT(c.f_name, " ", c.l_name) AS customer_name,
        CONCAT(p.f_name, " ", p.l_name) AS provider_name
     FROM reviews r
     INNER JOIN bookings b ON b.id = r.booking_id
     INNER JOIN services s ON s.id = b.service_id
     INNER JOIN users c ON c.id = r.customer_id
     INNER JOIN users p ON p.id = r.provider_id
     ORDER BY r.created_at DESC
     LIMIT 6'
)->fetchAll();

render_layout_start('Customer Reviews', 'review');
?>
<section class="page-hero">
  <div class="shell lead-card-grid">
    <div>
      <span class="eyebrow">Verified feedback</span>
      <h1 class="section-title" style="font-size: 3rem;">Paid customers can review assigned utility work.</h1>
      <p class="section-copy">Reviews only unlock for paid bookings that reached a live provider stage. That keeps the feedback section credible for your demo and for real usage.</p>
    </div>
    <div class="panel">
      <div class="kpi-strip">
        <div class="kpi">
          <span class="muted">Eligible jobs</span>
          <strong><?= count($reviewableBookings) ?></strong>
        </div>
        <div class="kpi">
          <span class="muted">Review policy</span>
          <strong>Paid only</strong>
        </div>
        <div class="kpi">
          <span class="muted">Dispatch model</span>
          <strong>Admin assigned</strong>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="shell split-card" style="grid-template-columns: 1fr 0.95fr;">
    <div class="panel">
      <h3>My reviewable bookings</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Booking</th>
              <th>Provider</th>
              <th>Status</th>
              <th>Review</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($reviewableBookings): ?>
              <?php foreach ($reviewableBookings as $booking): ?>
                <tr>
                  <td>
                    <strong>#<?= (int) $booking['id'] ?> - <?= e($booking['service_name']) ?></strong><br>
                    <span class="helper-text"><?= e((string) $booking['created_at']) ?></span>
                  </td>
                  <td><?= e($booking['provider_name']) ?></td>
                  <td><span class="status-badge status-<?= e(booking_status_badge((string) $booking['status'])) ?>"><?= e(strtoupper((string) $booking['status'])) ?></span></td>
                  <td>
                    <a class="ghost-btn" href="rating.php?booking_id=<?= (int) $booking['id'] ?>">
                      <?= $booking['rating'] ? 'Edit Review' : 'Write Review' ?>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="empty-state">No paid and confirmed jobs are ready for review yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel">
      <h3><?= $selectedBooking ? 'Review booking #' . (int) $selectedBooking['id'] : 'Review instructions' ?></h3>
      <?php if ($selectedBooking): ?>
        <p class="helper-text">Service: <?= e($selectedBooking['service_name']) ?> with <?= e($selectedBooking['provider_name']) ?></p>
        <form method="post" class="form-grid">
          <input type="hidden" name="booking_id" value="<?= (int) $selectedBooking['id'] ?>">
          <div class="field field-full">
            <label for="rating">Rating</label>
            <select id="rating" name="rating" required>
              <option value="">Choose rating</option>
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= (int) ($selectedBooking['rating'] ?? 0) === $i ? 'selected' : '' ?>>
                  <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="field field-full">
            <label for="review_text">Comments</label>
            <textarea id="review_text" name="review_text" placeholder="Share service quality, punctuality, professionalism, or any issue for the admin and future customers."><?= e((string) ($selectedBooking['review_text'] ?? '')) ?></textarea>
          </div>
          <div class="field field-full">
            <button class="primary-btn" type="submit">Save Review</button>
          </div>
        </form>
      <?php else: ?>
        <p class="section-copy">Once a booking is paid and reaches confirmed or completed status, it will appear here automatically for review.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="shell">
    <div class="section-heading">
      <span class="eyebrow">Recent reviews</span>
      <h2 class="section-title">Real customer feedback snapshot</h2>
    </div>
    <div class="testimonial-grid">
      <?php if ($recentReviews): ?>
        <?php foreach ($recentReviews as $item): ?>
          <article class="panel">
            <div class="rating-stars"><?= str_repeat('?', (int) $item['rating']) . str_repeat('?', 5 - (int) $item['rating']) ?></div>
            <p class="quote"><?= e((string) ($item['review_text'] ?: 'Customer completed the booking and left a positive service confirmation.')) ?></p>
            <p><strong><?= e($item['customer_name']) ?></strong></p>
            <p class="helper-text"><?= e($item['service_name']) ?> with <?= e($item['provider_name']) ?></p>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="panel empty-state">
          <h3>No reviews yet</h3>
          <p>The review wall will fill automatically after paid customer jobs receive feedback.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php render_layout_end(); ?>
