<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const SSLCOMMERZ_INIT_URL = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
const SSLCOMMERZ_VALIDATE_URL = 'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php';

function sslcommerz_init_transaction(array $booking, array $customer, string $successUrl, string $failUrl, string $cancelUrl): array
{
    $postData = [
        'store_id' => SSLCOMMERZ_STORE_ID,
        'store_passwd' => SSLCOMMERZ_STORE_PASSWORD,
        'total_amount' => number_format((float) $booking['amount'], 2, '.', ''),
        'currency' => 'BDT',
        'tran_id' => $booking['transaction_id'],
        'success_url' => $successUrl,
        'fail_url' => $failUrl,
        'cancel_url' => $cancelUrl,
        'emi_option' => 0,
        'cus_name' => trim($customer['f_name'] . ' ' . $customer['l_name']),
        'cus_email' => $customer['email'],
        'cus_add1' => $customer['district'] ?: 'Dhaka',
        'cus_phone' => $customer['phone'],
        'product_name' => $booking['service_name'],
        'value_a' => $booking['id'],
    ];

    $curl = curl_init(SSLCOMMERZ_INIT_URL);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded) || empty($decoded['GatewayPageURL'])) {
        return ['success' => false, 'error' => 'Unable to initialise SSLCommerz sandbox session.'];
    }

    return ['success' => true, 'redirectUrl' => $decoded['GatewayPageURL']];
}

function sslcommerz_validate_transaction(string $valId, string $tranId): array
{
    $url = SSLCOMMERZ_VALIDATE_URL . '?' . http_build_query([
        'val_id' => $valId,
        'tran_id' => $tranId,
        'store_id' => SSLCOMMERZ_STORE_ID,
        'store_passwd' => SSLCOMMERZ_STORE_PASSWORD,
    ]);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded) || ($decoded['status'] ?? '') !== 'VALID') {
        return ['success' => false, 'error' => 'Payment validation failed.'];
    }

    return ['success' => true, 'data' => $decoded];
}
