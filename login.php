<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'tron_lottery'); // Update DB credentials

// TRON Address Validator
function isValidTronAddress($address) {
    return preg_match('/^T[a-zA-Z0-9]{33}$/', $address);
}

// User Login/Signup
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tron_address = $_POST['tron_address'];

    if (!isValidTronAddress($tron_address)) {
        echo "Invalid TRON Address.";
        exit;
    }

    // Check if user already exists
    $result = $conn->query("SELECT * FROM users WHERE tron_address = '$tron_address'");
    
    if ($result->num_rows > 0) {
        // User exists, log in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
    } else {
        // New user, sign up
        $conn->query("INSERT INTO users (tron_address) VALUES ('$tron_address')");
        $_SESSION['user_id'] = $conn->insert_id;
    }

    header('Location: index.php');
    exit;
}
?>
