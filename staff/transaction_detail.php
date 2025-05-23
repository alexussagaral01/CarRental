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

// Fetch transaction details
$query = "
    SELECT 
        rh.RENTAL_HDR_ID,
        c.CUSTOMER_ID,
        CONCAT(c.FIRST_NAME, ' ', c.LAST_NAME) as customer_name,
        c.EMAIL as customer_email,
        c.CONTACT_NUM as customer_phone,
        c.CUSTOMER_ADDRESS as customer_address,
        c.DRIVERS_LICENSE as license_number,
        v.VEHICLE_ID,
        CONCAT(v.VEHICLE_BRAND, ' ', v.MODEL) as vehicle,
        v.VEHICLE_TYPE,
        v.YEAR as vehicle_year,
        v.COLOR as vehicle_color,
        v.LICENSE_PLATE,
        rd.RENTAL_DTL_ID,
        rd.PICKUP_LOCATION,
        rd.START_DATE,
        rd.END_DATE,
        rd.DURATION as duration_hours,
        rd.HOURLY_RATE,
        rd.TOTAL_AMOUNT,
        rd.VAT_AMOUNT,
        rd.LINE_TOTAL as total_amount,
        p.PAYMENT_ID,
        p.PAYMENT_METHOD,
        p.STATUS as payment_status,
        DATE(rh.DATE_CREATED) as transaction_date
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

// Check if assigned driver exists
$driver_info = null;
$driver_query = "
    SELECT d.* 
    FROM driver d
    JOIN customer c ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    WHERE c.CUSTOMER_ID = ?";

$driver_stmt = $conn->prepare($driver_query);
$driver_stmt->bind_param("i", $transaction['CUSTOMER_ID']);
$driver_stmt->execute();
$driver_result = $driver_stmt->get_result();

if ($driver_result->num_rows > 0) {
    $driver_info = $driver_result->fetch_assoc();
}

// Format dates and times
$start_date = new DateTime($transaction['START_DATE']);
$end_date = new DateTime($transaction['END_DATE']);
$formatted_start = $start_date->format('M d, Y h:i A');
$formatted_end = $end_date->format('M d, Y h:i A');

// Calculate days and hours for display
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
    <title>RentWheels - Transaction Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
    <!-- Header (removing print mode conditions) -->
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

    <!-- Transaction Details Page -->
    <div class="container mx-auto px-4 py-8">
        <!-- Action buttons (removing print condition and print button) -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Transaction Details</h1>
                <p class="text-gray-600">Transaction #<?php echo $transaction_id; ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="view_transactions.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 flex items-center space-x-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Transactions</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Information -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Customer Information
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['customer_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['customer_email']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['customer_phone']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['customer_address']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Driver's License</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['license_number']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Vehicle Information
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Vehicle</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['vehicle']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Type</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['VEHICLE_TYPE']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Year & Color</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['vehicle_year']) . ', ' . htmlspecialchars($transaction['vehicle_color']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">License Plate</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['LICENSE_PLATE']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pickup Location</p>
                        <p class="font-medium"><?php echo htmlspecialchars($transaction['PICKUP_LOCATION']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Rental Details -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Rental Details
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Start Date & Time</p>
                        <p class="font-medium"><?php echo $formatted_start; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">End Date & Time</p>
                        <p class="font-medium"><?php echo $formatted_end; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Duration</p>
                        <p class="font-medium"><?php echo $duration_text; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rate</p>
                        <p class="font-medium">₱<?php echo number_format($transaction['HOURLY_RATE'], 2); ?> per hour</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Method</p>
                        <p class="font-medium flex items-center">
                            <?php if ($transaction['PAYMENT_METHOD'] === 'cash'): ?>
                                <svg class="h-4 w-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            <?php else: ?>
                                <svg class="h-4 w-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            <?php endif; ?>
                            <?php echo ucfirst($transaction['PAYMENT_METHOD']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Status</p>
                        <p class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            <?php 
                            if ($transaction['payment_status'] === 'Paid') {
                                echo 'bg-green-100 text-green-800';
                            } elseif ($transaction['payment_status'] === 'Pending') {
                                echo 'bg-yellow-100 text-yellow-800';
                            } else {
                                echo 'bg-red-100 text-red-800';
                            }
                            ?>">
                            <?php echo $transaction['payment_status']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($driver_info): ?>
        <!-- Driver Information -->
        <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Assigned Driver
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($driver_info['DRIVER_NAME']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">License Number</p>
                        <p class="font-medium"><?php echo htmlspecialchars($driver_info['LICENSE_NUMBER']); ?></p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Contact Number</p>
                        <p class="font-medium"><?php echo htmlspecialchars($driver_info['CONTACT_NUMBER']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Gender</p>
                        <p class="font-medium"><?php echo $driver_info['GENDER'] === '0' ? 'Male' : 'Female'; ?></p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium"><?php echo htmlspecialchars($driver_info['ADDRESS']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <?php echo htmlspecialchars($driver_info['STATUS']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment Summary -->
        <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Payment Summary
            </h2>

            <div class="overflow-hidden mt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Rental Duration</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-900"><?php echo $duration_text; ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Hourly Rate</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-900">₱<?php echo number_format($transaction['HOURLY_RATE'], 2); ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Subtotal</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-900">₱<?php echo number_format($transaction['TOTAL_AMOUNT'], 2); ?></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">VAT (12%)</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-900">₱<?php echo number_format($transaction['VAT_AMOUNT'], 2); ?></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 text-left text-sm font-bold text-gray-900">Total Amount</td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-blue-600">₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
