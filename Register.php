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
        echo "<script>alert('Invalid TRON Address. Please try again.');</script>";
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TRX Lottery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #ff416c, #ff4b2b); /* Red gradient */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Registration Form Container -->
    <div class="w-full max-w-sm mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-center text-2xl font-bold text-red-600 mb-6">Register with TRON Address</h2>
        
        <!-- Registration Form -->
        <form action="register.php" method="POST" class="space-y-4">
            <!-- TRON Address Input -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tron_address">
                    TRON Address
                </label>
                <input name="tron_address" id="tron_address" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" type="text" placeholder="Txxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                    Register
                </button>
            </div>
        </form>
    </div>

</body>
</html>
