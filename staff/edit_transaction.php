<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff']) || $_SESSION['staff'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_transactions.php");
    exit();
}

$transaction_id = intval($_GET['id']);

// Database connection
$conn = new mysqli("localhost", "root", "", "vehicle_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if driver assignment is included in the form
    if (isset($_POST['driver_id']) && isset($_POST['customer_id'])) {
        $driver_id = $_POST['driver_id'];
        $customer_id = $_POST['customer_id'];
        
        // Update customer with assigned driver
        $update_customer_query = "UPDATE customer SET ASSIGNED_DRIVER_ID = ? WHERE CUSTOMER_ID = ?";
        $stmt = $conn->prepare($update_customer_query);
        $stmt->bind_param("ii", $driver_id, $customer_id);
        
        if ($stmt->execute()) {
            // Also update the driver status to "Assigned"
            $update_driver_query = "UPDATE driver SET STATUS = 'Assigned' WHERE DRIVER_ID = ?";
            $stmt = $conn->prepare($update_driver_query);
            $stmt->bind_param("i", $driver_id);
            $stmt->execute();
            
            $_SESSION['toast_message'] = "Driver assigned successfully.";
            $_SESSION['toast_type'] = "success";
            header("Location: view_transactions.php");
            exit();
        } else {
            $error_message = "Error assigning driver: " . $conn->error;
        }
    }
}

// Fetch transaction details
$query = "
    SELECT 
        rh.RENTAL_HDR_ID,
        rh.CUSTOMER_ID,
        CONCAT(c.FIRST_NAME, ' ', c.LAST_NAME) as customer_name,
        c.DRIVER_TYPE,
        c.ASSIGNED_DRIVER_ID,
        CONCAT(v.VEHICLE_BRAND, ' ', v.MODEL) as vehicle,
        rd.DURATION as duration_hours,
        rd.LINE_TOTAL as amount,
        p.PAYMENT_ID,
        p.PAYMENT_METHOD,
        p.STATUS as payment_status,
        DATE(rh.DATE_CREATED) as transaction_date,
        d.DRIVER_NAME as assigned_driver_name
    FROM 
        rental_hdr rh
    JOIN 
        customer c ON rh.CUSTOMER_ID = c.CUSTOMER_ID
    JOIN 
        vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
    JOIN 
        rental_dtl rd ON rh.RENTAL_DTL_ID = rd.RENTAL_DTL_ID
    JOIN 
        payment p ON rh.PAYMENT_ID = p.PAYMENT_ID
    LEFT JOIN
        driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    WHERE 
        rh.RENTAL_HDR_ID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Transaction not found
    header("Location: view_transactions.php");
    exit();
}

$transaction = $result->fetch_assoc();

// Get available drivers only if this is a "with_driver" rental
if ($transaction['DRIVER_TYPE'] === 'with_driver') {
    $drivers_query = "SELECT * FROM driver WHERE STATUS != 'Assigned' OR DRIVER_ID = ? ORDER BY DRIVER_NAME";
    $stmt = $conn->prepare($drivers_query);
    $assigned_driver_id = $transaction['ASSIGNED_DRIVER_ID'] ?? 0;
    $stmt->bind_param("i", $assigned_driver_id);
    $stmt->execute();
    $drivers_result = $stmt->get_result();
    $available_drivers = [];

    while ($driver = $drivers_result->fetch_assoc()) {
        $available_drivers[] = $driver;
    }
}

// Format duration for display
$hours = $transaction['duration_hours'];
$days = floor($hours / 24);
$remaining_hours = $hours % 24;
$duration_text = '';

if ($days > 0) {
    $duration_text .= $days . ' ' . ($days == 1 ? 'Day' : 'Days');
    if ($remaining_hours > 0) {
        $duration_text .= ', ' . $remaining_hours . ' ' . ($remaining_hours == 1 ? 'Hour' : 'Hours');
    }
} else {
    $duration_text = $hours . ' ' . ($hours == 1 ? 'Hour' : 'Hours');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Edit Transaction</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
    <!-- Modern Header Section -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 fixed w-full top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-20">
                <!-- Logo Section -->
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl blur opacity-60 group-hover:opacity-100 transition duration-200"></div>
                        <div class="relative w-12 h-12 bg-black rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17h8M8 17v-4m8 4v-4m-8 4h8m-8-4h8M4 11l2-6h12l2 6M4 11h16M4 11v6h16v-6" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">RentWheels</h1>
                        <p class="text-xs font-medium text-gray-500">Premium Car Rental</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Edit Transaction Page -->
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Transaction</h1>
                <p class="text-gray-600">Transaction #<?php echo $transaction_id; ?></p>
            </div>
            <div>
                <a href="view_transactions.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 flex items-center space-x-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Transactions</span>
                </a>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Transaction Information - Read Only -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Transaction Information
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Customer</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['customer_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Vehicle</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['vehicle']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Duration</p>
                        <p class="font-medium"><?php echo $duration_text; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Amount</p>
                        <p class="font-medium">₱<?php echo number_format($transaction['amount'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Method</p>
                        <p class="font-medium"><?php echo ucfirst($transaction['PAYMENT_METHOD']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Status</p>
                        <p class="font-medium"><?php echo $transaction['payment_status']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Transaction Date</p>
                        <p class="font-medium"><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Driver Assignment Section (if customer chose "with_driver") -->
            <?php if ($transaction['DRIVER_TYPE'] === 'with_driver'): ?>
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6 lg:col-span-2">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Assign Driver
                </h2>

                <form action="" method="POST" class="space-y-6">
                    <input type="hidden" name="customer_id" value="<?php echo $transaction['CUSTOMER_ID']; ?>">
                    
                    <div>
                        <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-1">Select Driver</label>
                        <select name="driver_id" id="driver_id" class="w-full rounded-lg border-gray-200 p-2.5" required>
                            <option value="">-- Select a driver --</option>
                            <?php foreach ($available_drivers as $driver): ?>
                                <option value="<?php echo $driver['DRIVER_ID']; ?>" <?php echo ($transaction['ASSIGNED_DRIVER_ID'] == $driver['DRIVER_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($driver['DRIVER_NAME']); ?> 
                                    (<?php echo htmlspecialchars($driver['LICENSE_NUMBER']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($transaction['assigned_driver_name'])): ?>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-800 font-medium">Currently assigned driver: <?php echo htmlspecialchars($transaction['assigned_driver_name']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <?php echo !empty($transaction['assigned_driver_name']) ? 'Update Driver Assignment' : 'Assign Driver'; ?>
                        </button>
                        <a href="view_transactions.php" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6 lg:col-span-2">
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="bg-yellow-50 p-4 rounded-lg inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-yellow-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-700 mt-4">This customer selected <span class="font-bold">self-drive</span> option.</p>
                            <p class="text-gray-500 mt-2">No driver assignment is needed.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Transaction View Button -->
        <div class="mt-6 flex justify-center">
            <a href="transaction_detail.php?id=<?php echo $transaction_id; ?>" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>View Complete Transaction Details</span>
            </a>
        </div>
    </div>
</body>
</html>
