<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$payment_id = $_POST['razorpay_payment_id'];
$order_id   = $_POST['razorpay_order_id'];
$signature  = $_POST['razorpay_signature'];
$purpose    = $_POST['purpose'];

// Verify signature - security check
$generated_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, RAZORPAY_KEY_SECRET);

if ($generated_signature !== $signature) {
    die('Payment verification failed. Please contact support with your payment ID: ' . $payment_id);
}

// Payment is genuine - save to database
if ($purpose === 'room_booking') {

    $request_id   = (int)$_POST['request_id'];
    $details_json = mysqli_real_escape_string($conn, json_encode([
        'razorpay_order_id'   => $order_id,
        'razorpay_payment_id' => $payment_id
    ]));

    $req = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM room_requests WHERE id = $request_id AND student_id = $student_id"));

    // Insert into payments table
    mysqli_query($conn, "INSERT INTO payments
        (transaction_id, room_request_id, student_id, amount, payment_method, payment_details, status)
        VALUES
        ('$payment_id', $request_id, $student_id, {$req['total_amount']}, 'Razorpay', '$details_json', 'Success')");

    $db_payment_id = mysqli_insert_id($conn);

    // Mark room request as paid
    mysqli_query($conn,
        "UPDATE room_requests SET payment_status='Paid', payment_id='$payment_id' WHERE id = $request_id");

    // Notifications
    $student_name = $_SESSION['student_name'];
    $notification_msg = "New payment received from $student_name for room booking. Amount: Rs." . number_format($req['total_amount'], 2);
    mysqli_query($conn, "INSERT INTO payment_notifications (payment_id, notification_type, message) VALUES ($db_payment_id, 'Admin', '$notification_msg')");
    mysqli_query($conn, "INSERT INTO payment_notifications (payment_id, notification_type, message) VALUES ($db_payment_id, 'Student', 'Payment successful! Transaction ID: $payment_id')");

    header("Location: payment_success.php?transaction_id=$payment_id");
    exit();

} elseif ($purpose === 'rent') {

    $months    = (int)$_POST['months_to_pay'];
    $due_month = $_POST['due_month'];

    $room = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT rr.*, r.rent FROM room_requests rr
         LEFT JOIN rooms r ON rr.room_id = r.id
         WHERE rr.student_id = $student_id
           AND rr.status = 'Approved'
           AND rr.payment_status = 'Paid'
         ORDER BY rr.response_date DESC LIMIT 1"));

    $details_json = mysqli_real_escape_string($conn, json_encode([
        'razorpay_order_id'   => $order_id,
        'razorpay_payment_id' => $payment_id
    ]));

    $conn->begin_transaction();
    try {
        for ($i = 0; $i < $months; $i++) {
            $pay_month = date('Y-m-01', strtotime("$due_month +$i month"));
            $is_adv    = ($pay_month > date('Y-m-01')) ? 1 : 0;

            mysqli_query($conn, "INSERT INTO monthly_rent_payments
                (student_id, room_request_id, payment_month, room_rent, total_amount,
                 payment_method, payment_status, transaction_id, is_advance_payment, payment_details)
                VALUES
                ($student_id, {$room['id']}, '$pay_month', {$room['total_amount']},
                 {$room['total_amount']}, 'Razorpay', 'Success', '$payment_id', $is_adv, '$details_json')");
        }
        $conn->commit();
        header("Location: pay_rent.php?success=1&txn=$payment_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die('Error saving payment. Contact support with ID: ' . $payment_id);
    }
}
