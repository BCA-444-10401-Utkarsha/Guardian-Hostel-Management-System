<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$amount  = (int)$_POST['amount'];
$purpose = $_POST['purpose'] ?? 'payment';

$api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

$order = $api->order->create([
    'receipt'         => 'RCPT_' . time(),
    'amount'          => $amount,
    'currency'        => 'INR',
    'payment_capture' => 1
]);

header('Content-Type: application/json');
echo json_encode([
    'order_id' => $order['id'],
    'amount'   => $order['amount'],
    'currency' => $order['currency'],
    'key_id'   => RAZORPAY_KEY_ID,
]);
