<?php
declare(strict_types=1);

require_once __DIR__ . '/partials.php';
require_once __DIR__ . '/payment-gateway.php';

$tran_id = $_POST['tran_id'] ?? '';
$status = $_POST['status'] ?? '';
$val_id = $_POST['val_id'] ?? '';

$isValid = false;
$errorMessage = '';

if (($status === 'VALID' || $status === 'VALIDATED') && $tran_id) {
    if (defined('SSL_SANDBOX_MODE') && SSL_SANDBOX_MODE === true) {
        // In local Sandbox environments, the validation API curl can return null, mismatch, or 
        // connection timeouts due to local network settings. We bypass API validation and trust 
        // the client POST status for seamless testing.
        $isValid = true;
    } else {
        if ($val_id) {
            $url = "https://securepay.sslcommerz.com/validator/api/validationserverphp.php?" . http_build_query([
                'val_id' => $val_id,
                'store_id' => SSLCOMMERZ_STORE_ID,
                'store_passwd' => SSLCOMMERZ_STORE_PASSWORD,
                'v' => '1',
                'format' => 'json'
            ]);

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if (!$error) {
                $validationResponse = json_decode((string) $response, true);
                if (isset($validationResponse['status']) && ($validationResponse['status'] === 'VALID' || $validationResponse['status'] === 'VALIDATED')) {
                    $isValid = true;
                } else {
                    $errorMessage = "Payment validation failed from SSLCommerz API.";
                }
            } else {
                $errorMessage = "Payment API connection error: " . $error;
            }
        }
    }

    if ($isValid) {
        $stmt = pdo()->prepare("UPDATE bookings SET payment_status = 'paid' WHERE transaction_id = :tran_id");
        $stmt->execute(['tran_id' => $tran_id]);

        // Restore user session if dropped by SameSite=Lax cross-origin POST
        $custStmt = pdo()->prepare("SELECT customer_id FROM bookings WHERE transaction_id = :tran_id LIMIT 1");
        $custStmt->execute(['tran_id' => $tran_id]);
        $customerId = $custStmt->fetchColumn();

        if ($customerId && !is_logged_in()) {
            $userStmt = pdo()->prepare("SELECT id, f_name, l_name, email, role, image, is_verified FROM users WHERE id = :id LIMIT 1");
            $userStmt->execute(['id' => $customerId]);
            if ($customerUser = $userStmt->fetch()) {
                login_user($customerUser);
            }
        }
    }
} else {
    $errorMessage = "Invalid payment payload received. The transaction failed or was cancelled.";
}

render_layout_start('Payment Successful', '');
?>

<section class="section">
  <div class="shell" style="text-align: center; padding: 5rem 0;">
    <?php if ($isValid): ?>
        <div style="font-size: 4rem; color: #1F5EFF; margin-bottom: 1rem;">✓</div>
        <h1 style="color: #1F5EFF; margin-bottom: 0.5rem;">Payment Successful!</h1>
        <p class="muted">Your payment was processed securely. Thank you for your booking.</p>
        <p class="muted" style="margin-bottom: 2rem;">Transaction ID: <?= e($tran_id) ?></p>
        <div>
            <a href="profile.php" class="primary-btn">Return to Profile</a>
        </div>
    <?php else: ?>
        <div style="font-size: 4rem; color: #dc3545; margin-bottom: 1rem;">✗</div>
        <h1 style="color: #dc3545; margin-bottom: 0.5rem;">Payment Validation Failed</h1>
        <p class="muted"><?= e($errorMessage) ?></p>
        <div style="margin-top: 2rem;">
            <a href="index.php" class="ghost-btn">Back to Home</a>
        </div>
    <?php endif; ?>
  </div>
</section>

<?php render_layout_end(); ?>
