<?php
$conn = new mysqli('localhost', 'root', '', 'tron_lottery');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['payment_status']) && $data['payment_status'] === 'finished') {
    $order_id = $data['order_id'];
    $trx_amount = $data['pay_amount'];

    // Update ticket status
    $conn->query("UPDATE tickets SET status='purchased' WHERE user_id=$order_id AND trx_amount=$trx_amount");

    // Update user balance
    $conn->query("UPDATE users SET balance = balance + $trx_amount WHERE id = $order_id");
}
?>
