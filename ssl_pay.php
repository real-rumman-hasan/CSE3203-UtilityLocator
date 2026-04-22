<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('service.php');
}

$serviceId = (int)($_POST['service_id'] ?? 0);
$service = fetch_service_by_id($serviceId);

if (!$service) {
    set_flash('error', 'Invalid service selected.');
    redirect('service.php');
}

$post_data = array();
$post_data['store_id'] = SSLCOMMERZ_STORE_ID;
$post_data['store_passwd'] = SSLCOMMERZ_STORE_PASSWORD;
$post_data['total_amount'] = number_format((float)$service['price'], 2, '.', '');
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "SSLCZ_TEST_" . uniqid();
$post_data['success_url'] = app_url('success.php');
$post_data['fail_url'] = app_url('fail.php');
$post_data['cancel_url'] = app_url('cancel.php');

$post_data['cus_name'] = $user['name'] ?? 'Unknown Customer';
$post_data['cus_email'] = $user['email'] ?? 'unknown@example.com';
$post_data['cus_add1'] = "Dhaka";
$post_data['cus_add2'] = "Dhaka";
$post_data['cus_city'] = "Dhaka";
$post_data['cus_state'] = "Dhaka";
$post_data['cus_postcode'] = "1000";
$post_data['cus_country'] = "Bangladesh";
$post_data['cus_phone'] = "01711111111"; 

$post_data['shipping_method'] = "NO";
$post_data['product_name'] = $service['name'];
$post_data['product_category'] = "Utility Service";
$post_data['product_profile'] = "non-physical-goods";

$api_url = "https://sandbox.sslcommerz.com/gwprocess/v4/api.php";

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $api_url);
curl_setopt($handle, CURLOPT_TIMEOUT, 30);
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($handle, CURLOPT_POST, 1);
curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE); 

$content = curl_exec($handle);
$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if ($code == 200 && !(curl_errno($handle))) {
    curl_close($handle);
    $sslcommerzResponse = json_decode($content, true);

    if (isset($sslcommerzResponse['status']) && $sslcommerzResponse['status'] === 'SUCCESS') {
        if (!empty($sslcommerzResponse['GatewayPageURL'])) {
            redirect($sslcommerzResponse['GatewayPageURL']);
        } else {
            set_flash('error', 'Gateway URL not found.');
            redirect('service.php');
        }
    } else {
        $reason = $sslcommerzResponse['failedreason'] ?? 'SSLCommerz API connection failed.';
        set_flash('error', $reason);
        redirect('service.php');
    }
} else {
    curl_close($handle);
    set_flash('error', 'Unable to connect to the payment gateway.');
    redirect('service.php');
}
