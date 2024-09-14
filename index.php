<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'tron_lottery');
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch TRX balance, staked balance, and account creation date from TronScan API
$tron_address = $user['tron_address'];
$api_url_balance = "https://apilist.tronscan.org/api/account?address=$tron_address";
$tron_response_balance = file_get_contents($api_url_balance);
$tron_data_balance = json_decode($tron_response_balance, true);

$trx_balance = isset($tron_data_balance['balance']) ? $tron_data_balance['balance'] / 1000000 : 0; // Convert from SUN to TRX
$trx_staked_balance = isset($tron_data_balance['frozen']) ? $tron_data_balance['frozen']['total'] / 1000000 : 0; // Staked TRX
$creation_date = isset($tron_data_balance['createTime']) ? date('Y-m-d', $tron_data_balance['createTime'] / 1000) : 'Unknown';

// Placeholder conversion rate for demonstration; in production, use a real API to get the conversion rate
$conversion_rate = 0.075; // Example: 1 TRX = 0.075 USD
$trx_balance_usd = $trx_balance * $conversion_rate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRON Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.1/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: "Exo 2", sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
    <script>
        async function fetchTransactions() {
            try {
                const response = await fetch(`fetch_transactions.php?address=<?php echo htmlspecialchars($tron_address); ?>`);
                const transactions = await response.json();
                
                const tbody = document.querySelector("#transactions-table tbody");
                tbody.innerHTML = ""; // Clear current rows
                
                if (transactions.length > 0) {
                    transactions.forEach(transaction => {
                        const row = document.createElement("tr");
                        row.classList.add("hover:bg-red-900", "transition");

                        row.innerHTML = `
                            <td class="py-4 px-4 truncate">
                                <a href="https://tronscan.org/#/transaction/${transaction.hash}" target="_blank" class="text-blue-400 hover:underline" title="${transaction.hash}">
                                    ${transaction.hash.substring(0, 10)}...${transaction.hash.slice(-10)}
                                </a>
                            </td>
                            <td class="py-4 px-4 truncate">${transaction.toAddress}</td>
                            <td class="py-4 px-4">${(transaction.amount / 1000000).toFixed(2)}</td>
                            <td class="py-4 px-4">${new Date(transaction.timestamp / 1000 * 1000).toLocaleString()}</td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="py-4 px-4 text-center">No transactions found.</td></tr>';
                }
            } catch (error) {
                console.error("Error fetching transactions:", error);
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            fetchTransactions(); // Initial fetch
            setInterval(fetchTransactions, 2000); // Fetch every 2 seconds
        });
    </script>
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-red-600 to-red-800 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-white text-lg font-semibold">TRON Dashboard</a>
            <a href="logout.php" class="text-white bg-red-700 hover:bg-red-600 font-bold py-2 px-4 rounded transition duration-300">Logout</a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container mx-auto px-4 py-8 flex-grow">
        <div class="grid gap-8 md:grid-cols-3">
            <!-- Balance Card -->
            <div class="bg-gradient-to-r from-red-500 to-red-700 p-6 rounded-lg shadow-lg">
                <h1 class="text-2xl font-bold text-white mb-4">Your Balance</h1>
                <p class="text-3xl font-extrabold mt-2"><?php echo number_format($trx_balance, 2); ?> TRX</p>
                <p class="text-lg font-medium mt-2">$<?php echo number_format($trx_balance_usd, 2); ?> USD</p>
                <p class="text-md font-medium mt-2"><?php echo number_format($trx_staked_balance, 2); ?> TRX Staked</p>
                <p class="text-sm mt-2">Account Created: <?php echo $creation_date; ?></p>
            </div>

            <!-- Clipboard Feature -->
            <div class="relative col-span-1 md:col-span-2">
                <label for="tron-address-copy" class="sr-only">TRON Address</label>
                <div class="relative">
                    <input id="tron-address-copy" type="text" class="bg-gray-700 border border-gray-600 text-gray-300 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pr-10 py-2.5" value="<?php echo htmlspecialchars($user['tron_address']); ?>" disabled readonly>
                    <button data-copy-to-clipboard-target="tron-address-copy" data-tooltip-target="tooltip-copy-tron-address" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-300 hover:bg-gray-600 rounded-lg p-2 inline-flex items-center justify-center">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                            <path d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                        </svg>
                    </button>
                </div>
                <div id="tooltip-copy-tron-address" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-700 rounded-lg opacity-0">
                    Copied!
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>
        </div>

        <!-- Latest Transactions -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
            <h2 class="text-xl font-bold text-red-400 mb-4">Latest Transactions</h2>
            <div class="overflow-x-auto">
                <table id="transactions-table" class="min-w-full bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-red-700">
                        <tr>
                            <th class="py-5 px-4 text-left font-semibold">Transaction Hash</th>
                            <th class="py-5 px-4 text-left font-semibold">To</th>
                            <th class="py-5 px-4 text-left font-semibold">Amount (TRX)</th>
                            <th class="py-5 px-4 text-left font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center">Loading transactions...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 p-4 mt-auto">
        <div class="container mx-auto text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> Timalya. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
