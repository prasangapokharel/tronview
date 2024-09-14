<?php
header('Content-Type: application/json');

$tron_address = $_GET['address'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'tron_lottery');

// Fetch latest 10 transactions
$api_url_transactions = "https://apilist.tronscan.org/api/transaction?address=$tron_address&limit=10";
$tron_response_transactions = file_get_contents($api_url_transactions);
$tron_data_transactions = json_decode($tron_response_transactions, true);
$transactions = isset($tron_data_transactions['data']) ? $tron_data_transactions['data'] : [];

// Output JSON
echo json_encode($transactions);
?>
