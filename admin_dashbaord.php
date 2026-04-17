<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';

$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providerId = (int) ($_POST['provider_id'] ?? 0);
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $assignedProviderId = (int) ($_POST['assigned_provider_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($providerId > 0 && in_array($action, ['allow', 'reject'], true)) {
        $stmt = pdo()->prepare('UPDATE users SET is_verified = :is_verified WHERE id = :id AND role = "provider"');
        $stmt->execute([
            'is_verified' => $action === 'allow' ? 1 : 0,
            'id' => $providerId,
        ]);

        set_flash('success', $action === 'allow' ? 'Provider approved successfully.' : 'Provider rejected and hidden from the frontend.');
        redirect('admin_dashboard.php');
    }

    if ($providerId > 0 && $action === 'revoke') {
        $stmt = pdo()->prepare('DELETE FROM users WHERE id = :id AND role = "provider"');
        $stmt->execute(['id' => $providerId]);
        set_flash('success', 'Provider and all associated data have been completely removed.');
        redirect('admin_dashboard.php');
    }

    if ($bookingId > 0 && $assignedProviderId > 0 && $action === 'assign_booking') {
        $check = pdo()->prepare(
            'SELECT b.id
             FROM bookings b
             INNER JOIN provider_services ps ON ps.provider_id = :provider_id AND ps.service_id = b.service_id
             INNER JOIN users u ON u.id = ps.provider_id
             WHERE b.id = :booking_id
               AND b.status = "awaiting_assignment"
               AND b.payment_status = "paid"
               AND u.is_verified = 1
               AND u.role = "provider"
             LIMIT 1'
        );
        $check->execute([
            'provider_id' => $assignedProviderId,
            'booking_id' => $bookingId,
        ]);

        if ($check->fetch()) {
            $assign = pdo()->prepare(
                'UPDATE bookings
                 SET provider_id = :provider_id,
                     status = "pending",
                     assigned_by_admin_id = :admin_id,
                     assigned_at = NOW(),
                     expires_at = DATE_ADD(NOW(), INTERVAL 2 MINUTE)
                 WHERE id = :booking_id
                   AND status = "awaiting_assignment"
                   AND payment_status = "paid"'
            );
            $assign->execute([
                'provider_id' => $assignedProviderId,
                'admin_id' => (int) $admin['id'],
                'booking_id' => $bookingId,
            ]);
            set_flash('success', 'Booking assigned to the selected worker and pushed to the provider queue.');
        } else {
            set_flash('error', 'Selected provider is not eligible for this service.');
        }

        redirect('admin_dashboard.php');
    }
}

$pendingProviders = pdo()->query(
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
        u.created_at,
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ", ") AS services
     FROM users u
     LEFT JOIN provider_services ps ON ps.provider_id = u.id
     LEFT JOIN services s ON s.id = ps.service_id
     WHERE u.role = "provider" AND u.is_verified = 0
     GROUP BY u.id
     ORDER BY u.created_at ASC'
)->fetchAll();

$activeProviders = pdo()->query(
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
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ", ") AS services
     FROM users u
     LEFT JOIN provider_services ps ON ps.provider_id = u.id
     LEFT JOIN services s ON s.id = ps.service_id
     WHERE u.role = "provider" AND u.is_verified = 1
     GROUP BY u.id
     ORDER BY u.f_name, u.l_name'
)->fetchAll();

$allServices = pdo()->query('SELECT name FROM services ORDER BY name ASC')->fetchAll(PDO::FETCH_COLUMN);
$activeProvidersByService = [];
foreach ($allServices as $serviceName) {
    $activeProvidersByService[$serviceName] = [];
}
$activeProvidersByService['Unassigned'] = [];

foreach ($activeProviders as $provider) {
    if (!$provider['services']) {
        $activeProvidersByService['Unassigned'][] = $provider;
        continue;
    }
    
    $providerServicesList = array_map('trim', explode(',', $provider['services']));
    foreach ($providerServicesList as $srv) {
        if (isset($activeProvidersByService[$srv])) {
            $activeProvidersByService[$srv][] = $provider;
        }
    }
}

if (empty($activeProvidersByService['Unassigned'])) {
    unset($activeProvidersByService['Unassigned']);
}

$assignmentQueue = pdo()->query(
    'SELECT
        b.id,
        b.message,
        b.payment_status,
        b.created_at,
        b.provider_id,
        s.id AS service_id,
        s.name AS service_name,
        COALESCE(ps_existing.custom_price, s.price) AS amount,
        c.f_name AS customer_first,
        c.l_name AS customer_last,
        c.phone AS customer_phone,
        p.f_name AS suggested_first,
        p.l_name AS suggested_last
     FROM bookings b
     INNER JOIN services s ON s.id = b.service_id
     INNER JOIN users c ON c.id = b.customer_id
     LEFT JOIN users p ON p.id = b.provider_id
     LEFT JOIN provider_services ps_existing ON ps_existing.provider_id = b.provider_id AND ps_existing.service_id = b.service_id
     WHERE b.status = "awaiting_assignment" AND b.payment_status = "paid"
     ORDER BY b.created_at DESC'
)->fetchAll();

$providerOptionsStmt = pdo()->query(
    'SELECT
        ps.service_id,
        u.id AS provider_id,
        CONCAT(u.f_name, " ", u.l_name) AS provider_name,
        COALESCE(ps.custom_price, s.price) AS amount
     FROM provider_services ps
     INNER JOIN users u ON u.id = ps.provider_id
     INNER JOIN services s ON s.id = ps.service_id
     WHERE u.role = "provider" AND u.is_verified = 1
     ORDER BY provider_name'
);
$providerOptions = [];
foreach ($providerOptionsStmt->fetchAll() as $row) {
    $providerOptions[(int) $row['service_id']][] = $row;
}

render_layout_start('Admin Dashboard', '');
?>
<section class="section">
  <div class="shell">
    <span class="eyebrow">Admin control center</span>
    <h2 class="section-title" style="font-size: 2.5rem;">Approval, assignment, and provider control</h2>
    <p class="section-copy">Admins are inserted only from the database. From this dashboard they approve provider applications, remove live providers, and assign paid customer jobs to the correct verified worker.</p>

    <div class="stats-grid" style="margin-bottom: 24px;">
      <div class="metric-card">
        <p class="muted">Pending approvals</p>
        <div class="metric-value"><?= count($pendingProviders) ?></div>
      </div>
      <div class="metric-card">
        <p class="muted">Active providers</p>
        <div class="metric-value"><?= count($activeProviders) ?></div>
      </div>
      <div class="metric-card">
        <p class="muted">Jobs awaiting assignment</p>
        <div class="metric-value"><?= count($assignmentQueue) ?></div>
      </div>
    </div>

    <div class="panel" style="margin-bottom: 24px;">
      <h3>Paid booking assignment queue</h3>
      <p class="helper-text">Customers can request a service and pay first. The admin then assigns the booking to one verified provider who offers that service. Customer comments are shown here so you can dispatch the right worker.</p>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Booking</th>
              <th>Customer</th>
              <th>Customer note</th>
              <th>Assign worker</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($assignmentQueue): ?>
              <?php foreach ($assignmentQueue as $booking): ?>
                <tr>
                  <td>
                    <strong>#<?= (int) $booking['id'] ?> - <?= e($booking['service_name']) ?></strong><br>
                    <span class="helper-text">BDT <?= number_format((float) $booking['amount'], 2) ?> | Paid booking</span>
                  </td>
                  <td>
                    <?= e($booking['customer_first'] . ' ' . $booking['customer_last']) ?><br>
                    <span class="helper-text"><?= e($booking['customer_phone']) ?></span>
                  </td>
                  <td>
                    <span class="helper-text"><?= e((string) ($booking['message'] ?: 'No additional request provided.')) ?></span><br>
                    <span class="helper-text">Suggested: <?= e(trim((string) ($booking['suggested_first'] . ' ' . $booking['suggested_last'])) ?: 'Admin decides') ?></span>
                  </td>
                  <td>
                    <form method="post" class="inline-actions">
                      <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                      <select name="assigned_provider_id" required style="min-width: 240px;">
                        <option value="">Select provider</option>
                        <?php foreach ($providerOptions[(int) $booking['service_id']] ?? [] as $option): ?>
                          <option value="<?= (int) $option['provider_id'] ?>" <?= (int) $option['provider_id'] === (int) $booking['provider_id'] ? 'selected' : '' ?>>
                            <?= e($option['provider_name']) ?> - BDT <?= number_format((float) $option['amount'], 2) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <button class="primary-btn" type="submit" name="action" value="assign_booking">Assign</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="empty-state">No paid bookings are waiting for admin assignment right now.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <h3 style="margin: 0 0 16px;">Pending provider approvals</h3>
    <div class="providers-grid">
      <?php if ($pendingProviders): ?>
        <?php foreach ($pendingProviders as $provider): ?>
          <article class="card">
            <div class="profile-header" style="grid-template-columns: 90px 1fr;">
              <img class="avatar" style="width:90px; height:90px;" src="<?= e($provider['image'] ?: default_avatar('Provider')) ?>" alt="<?= e($provider['f_name']) ?>">
              <div>
                <h3><?= e($provider['f_name'] . ' ' . $provider['l_name']) ?></h3>
                <p class="muted"><?= e($provider['email']) ?></p>
                <p class="helper-text"><?= e($provider['phone']) ?> | <?= e($provider['area'] ?: 'Dhaka area') ?>, <?= e($provider['district']) ?></p>
              </div>
            </div>
            <p><strong>Services:</strong> <?= e($provider['services'] ?: 'Not assigned') ?></p>
            <div class="inline-actions">
              <form method="post">
                <input type="hidden" name="provider_id" value="<?= (int) $provider['id'] ?>">
                <button class="primary-btn" type="submit" name="action" value="allow">Allow</button>
              </form>
              <form method="post">
                <input type="hidden" name="provider_id" value="<?= (int) $provider['id'] ?>">
                <button class="danger-btn" type="submit" name="action" value="reject">Reject</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="panel empty-state">
          <h3>No pending providers</h3>
          <p>The verification queue is currently empty.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel" style="margin-top: 28px;">
      <h3>Active providers</h3>
      <p class="helper-text">Only verified providers appear in the frontend service pages. If needed, admin can remove them from the live list here without deleting the account.</p>
      
      <div class="tabs-container" style="margin-top: 16px;">
        <div style="display: flex; gap: 8px; border-bottom: 2px solid #EAF2F2; padding-bottom: 8px; margin-bottom: 24px; overflow-x: auto;">
          <?php $idx = 0; foreach (array_keys($activeProvidersByService) as $serviceName): ?>
              <button class="tab-btn <?= $idx === 0 ? 'active' : '' ?>" data-target="service-<?= e(strtolower(str_replace(' ', '-', $serviceName))) ?>" style="padding: 10px 20px; border: none; background: <?= $idx === 0 ? '#1F5EFF' : '#f4f4f4' ?>; color: <?= $idx === 0 ? '#fff' : '#333' ?>; border-radius: 6px; cursor: pointer; white-space: nowrap; font-weight: 600; font-family: inherit; font-size: 0.95rem; transition: background 0.2s, color 0.2s;">
                  <?= e($serviceName) ?> Section (<?= count($activeProvidersByService[$serviceName]) ?>)
              </button>
          <?php $idx++; endforeach; ?>
        </div>

        <div class="tabs-content">
          <?php $idx = 0; foreach ($activeProvidersByService as $serviceName => $serviceProviders): ?>
            <div id="service-<?= e(strtolower(str_replace(' ', '-', $serviceName))) ?>" class="tab-pane" style="display: <?= $idx === 0 ? 'block' : 'none' ?>;">
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Provider</th>
                      <th>Services</th>
                      <th>Location</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($serviceProviders): ?>
                      <?php foreach ($serviceProviders as $provider): ?>
                        <tr>
                          <td style="display: flex; align-items: center; gap: 12px; padding: 12px;">
                            <img src="<?= e($provider['image'] ?: default_avatar(substr($provider['f_name'], 0, 1))) ?>" alt="<?= e($provider['f_name']) ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: #EAF2F2;">
                            <div>
                              <strong style="font-size: 1.05rem;"><?= e($provider['f_name'] . ' ' . $provider['l_name']) ?></strong><br>
                              <span class="helper-text" style="font-size: 0.85rem; color: #666;"><?= e($provider['email']) ?> | <?= e($provider['phone']) ?></span>
                            </div>
                          </td>
                          <td style="vertical-align: middle; padding: 12px;"><?= e($provider['services'] ?: 'Not assigned') ?></td>
                          <td style="vertical-align: middle; padding: 12px;"><?= e($provider['area'] ?: 'Dhaka area') ?>, <?= e($provider['district']) ?></td>
                          <td style="vertical-align: middle; padding: 12px;">
                            <div class="inline-actions">
                              <form method="post" style="margin: 0;">
                                <input type="hidden" name="provider_id" value="<?= (int) $provider['id'] ?>">
                                <button class="danger-btn" type="submit" name="action" value="revoke" onclick="return confirm('Are you sure you want to completely delete this provider?');">Delete</button>
                              </form>
                              <a href="admin_edit_provider.php?id=<?= (int) $provider['id'] ?>" class="primary-btn" style="text-decoration: none; padding: 6px 12px; height: 38px; display: inline-flex; align-items: center;">Update</a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="4" class="empty-state">No active providers found in the <?= e($serviceName) ?> section.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php $idx++; endforeach; ?>
        </div>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.tab-btn');
        const panes = document.querySelectorAll('.tab-pane');

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                tabs.forEach(t => {
                    t.style.background = '#f4f4f4';
                    t.style.color = '#333';
                });
                panes.forEach(p => p.style.display = 'none');

                tab.style.background = '#1F5EFF';
                tab.style.color = '#fff';
                document.getElementById(tab.dataset.target).style.display = 'block';
            });
        });
    });
    </script>
  </div>
</section>
<?php render_layout_end(); ?>
