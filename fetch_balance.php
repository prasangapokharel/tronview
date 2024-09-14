<?php
header('Content-Type: application/json');

$tron_address = $_GET['address'];
$api_url_balance = "https://apilist.tronscan.org/api/account?address=$tron_address";
$tron_response_balance = file_get_contents($api_url_balance);
$tron_data_balance = json_decode($tron_response_balance, true);

$trx_balance = isset($tron_data_balance['balance']) ? $tron_data_balance['balance'] / 1000000 : 0; // Convert from SUN to TRX
$trx_staked_balance = isset($tron_data_balance['frozen']) ? $tron_data_balance['frozen']['total'] / 1000000 : 0; // Staked TRX

// Placeholder conversion rate for demonstration; in production, use a real API to get the conversion rate
$conversion_rate = 0.075; // Example: 1 TRX = 0.075 USD
$trx_balance_usd = $trx_balance * $conversion_rate;

$response = array(
    'balance' => number_format($trx_balance, 2),
    'balance_usd' => number_format($trx_balance_usd, 2),
    'staked_balance' => number_format($trx_staked_balance, 2),
);

echo json_encode($response);
?>
