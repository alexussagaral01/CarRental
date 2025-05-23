<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "vehicle_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get transaction ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['toast_message'] = "Invalid transaction ID";
    $_SESSION['toast_type'] = "error";
    header("Location: view_all_transactions.php");
    exit();
}

$transaction_id = intval($_GET['id']);

// Fetch transaction details with a comprehensive JOIN query
$query = "
    SELECT 
        rh.RENTAL_HDR_ID,
        rh.DATE_CREATED as rental_date,
        c.CUSTOMER_ID,
        c.FIRST_NAME,
        c.LAST_NAME,
        c.EMAIL,
        c.CONTACT_NUM,
        c.CUSTOMER_ADDRESS as ADDRESS,
        c.DRIVER_TYPE,
        c.DRIVERS_LICENSE as LICENSE_NUMBER,
        v.VEHICLE_ID,
        v.VEHICLE_BRAND,
        v.MODEL,
        v.VEHICLE_TYPE,
        v.LICENSE_PLATE as PLATE_NUMBER,
        v.AMOUNT as HOURLY_RATE,
        rd.RENTAL_DTL_ID,
        rd.START_DATE,
        rd.END_DATE,
        rd.DURATION,
        rd.LINE_TOTAL as payment_amount,
        p.PAYMENT_ID,
        p.PAYMENT_METHOD,
        p.STATUS as payment_status,
        d.DRIVER_ID,
        d.DRIVER_NAME,
        d.CONTACT_NUMBER as DRIVER_CONTACT,
        d.LICENSE_NUMBER as DRIVER_LICENSE_NUMBER
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
    $_SESSION['toast_message'] = "Transaction not found";
    $_SESSION['toast_type'] = "error";
    header("Location: view_all_transactions.php");
    exit();
}

$transaction = $result->fetch_assoc();

// Format dates for display
$rental_date = new DateTime($transaction['rental_date']);
$start_date = new DateTime($transaction['START_DATE']);
$end_date = new DateTime($transaction['END_DATE']);
// Use rental date as payment date if payment is marked as paid
$payment_date = ($transaction['payment_status'] === 'Paid') ? $rental_date : null;
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
        
        /* Toast notification styling */
        .toast {
            position: fixed;
            top: 85px;
            right: 20px;
            z-index: 9999;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.5s ease-in-out;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .toast-progress-bar {
            height: 3px;
            width: 100%;
            transition: width linear 1.5s;
        }
        
        .toast-progress-bar.animate {
            width: 0 !important;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
    <!-- Toast Notification -->
    <?php if (isset($_SESSION['toast_message'])): ?>
        <div id="toast" class="toast shadow-xl rounded-lg overflow-hidden">
            <div class="<?php echo $_SESSION['toast_type'] === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-4 py-3 relative flex items-center">
                <div class="mr-3">
                    <?php if ($_SESSION['toast_type'] === 'success'): ?>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="font-medium">
                        <?php echo $_SESSION['toast_message']; ?>
                    </p>
                </div>
                <button onclick="closeToast()" class="absolute top-0 right-0 p-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="toast-progress">
                    <div class="toast-progress-bar <?php echo $_SESSION['toast_type'] === 'success' ? 'bg-green-400' : 'bg-red-400'; ?>"></div>
                </div>
            </div>
        </div>
    <?php 
        // Clear toast message after displaying
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
    endif; 
    ?>
    
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

                <nav class="hidden md:block">
                    <ul class="flex space-x-1">
                        <li>
                            <a href="admin_dashboard.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="create_account.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                <span>Create Staff Accounts</span>
                            </a>
                        </li>
                        <li>
                            <a href="view_all_transactions.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>View All Transactions</span>
                            </a>
                        </li>
                        <li>
                            <a href="reports.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Reports</span>
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-red-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Back button -->
    <div class="container mx-auto px-4 py-6">
        <a href="view_all_transactions.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Transactions
        </a>
    </div>

    <!-- Transaction Detail Content -->
    <div class="container mx-auto px-4 pb-12">
        <div class="max-w-5xl mx-auto">
            <!-- Transaction Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Transaction #<?php echo $transaction_id; ?></h1>
                    <p class="text-gray-600">
                        <span class="font-medium">Date:</span> <?php echo $rental_date->format('F d, Y - h:i A'); ?>
                    </p>
                </div>
                
                <!-- Payment Status Badge -->
                <div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold 
                        <?php 
                            switch($transaction['payment_status']) {
                                case 'Paid': 
                                    echo 'bg-green-100 text-green-800'; 
                                    break;
                                case 'Pending': 
                                    echo 'bg-yellow-100 text-yellow-800'; 
                                    break;
                                default: 
                                    echo 'bg-red-100 text-red-800'; 
                                    break;
                            }
                        ?>">
                        <?php echo $transaction['payment_status']; ?>
                    </span>
                </div>
            </div>
            
            <!-- Transaction Details Card -->
            <div class="glass-effect rounded-xl shadow-xl overflow-hidden">
                <!-- Customer Information Section -->
                <div class="border-b border-gray-200">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Customer Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Name</p>
                                <p class="text-base font-semibold text-gray-800">
                                    <?php echo $transaction['FIRST_NAME'] . ' ' . $transaction['LAST_NAME']; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['EMAIL']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Contact Number</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['CONTACT_NUM']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Address</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['ADDRESS']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Driver Type</p>
                                <p class="text-base font-medium text-gray-800">
                                    <span class="px-3 py-1 rounded-full text-xs 
                                        <?php echo $transaction['DRIVER_TYPE'] === 'self' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo $transaction['DRIVER_TYPE'] === 'self' ? 'Self Drive' : 'With Driver'; ?>
                                    </span>
                                </p>
                            </div>
                            <?php if ($transaction['DRIVER_TYPE'] === 'self'): ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500">License Number</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['LICENSE_NUMBER']; ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Driver Information Section (only shown if customer is not self-driving) -->
                <?php if ($transaction['DRIVER_TYPE'] !== 'self' && !empty($transaction['DRIVER_ID'])): ?>
                <div class="border-b border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-3">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Driver Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Driver Name</p>
                                <p class="text-base font-semibold text-gray-800"><?php echo $transaction['DRIVER_NAME']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Contact Number</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['DRIVER_CONTACT']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">License Number</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['DRIVER_LICENSE_NUMBER']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Vehicle Information Section -->
                <div class="border-b border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16v-4m0 0h8m-8 0v-4m8 4v4m0 0h-8m0 0v4"></path>
                            </svg>
                            Vehicle Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Vehicle</p>
                                <p class="text-base font-semibold text-gray-800"><?php echo $transaction['VEHICLE_BRAND'] . ' ' . $transaction['MODEL']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Vehicle Type</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['VEHICLE_TYPE']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Plate Number</p>
                                <p class="text-base text-gray-800"><?php echo $transaction['PLATE_NUMBER']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Hourly Rate</p>
                                <p class="text-base font-semibold text-blue-600">₱<?php echo number_format($transaction['HOURLY_RATE'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rental Information Section -->
                <div class="border-b border-gray-200">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Rental Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Start Date & Time</p>
                                <p class="text-base text-gray-800"><?php echo $start_date->format('F d, Y - h:i A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">End Date & Time</p>
                                <p class="text-base text-gray-800"><?php echo $end_date->format('F d, Y - h:i A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Duration</p>
                                <p class="text-base text-gray-800">
                                    <?php 
                                    $hours = $transaction['DURATION'];
                                    if ($hours >= 24) {
                                        $days = floor($hours / 24);
                                        $remaining_hours = $hours % 24;
                                        echo $days . ' ' . ($days == 1 ? 'Day' : 'Days');
                                        if ($remaining_hours > 0) {
                                            echo ', ' . $remaining_hours . ' ' . ($remaining_hours == 1 ? 'Hour' : 'Hours');
                                        }
                                    } else {
                                        echo $hours . ' ' . ($hours == 1 ? 'Hour' : 'Hours');
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information Section -->
                <div>
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Payment Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Method</p>
                                <p class="text-base font-medium">
                                    <span class="px-3 py-1 rounded-full text-xs 
                                        <?php echo strtolower($transaction['PAYMENT_METHOD']) === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($transaction['PAYMENT_METHOD']); ?>
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Date</p>
                                <p class="text-base text-gray-800">
                                    <?php 
                                        echo ($payment_date && $transaction['payment_status'] === 'Paid') ? 
                                            $payment_date->format('F d, Y - h:i A') : 
                                            'Not yet paid';
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Amount</p>
                                <p class="text-xl font-bold text-blue-600">₱<?php echo number_format($transaction['payment_amount'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toast notification functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            if (toast) {
                const progressBar = toast.querySelector('.toast-progress-bar');
                
                // Show toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
                
                // Start progress bar animation
                setTimeout(() => {
                    progressBar.classList.add('animate');
                }, 200);
                
                // Hide toast after 1.5 seconds
                setTimeout(() => {
                    closeToast();
                }, 1500);
            }
        });
        
        function closeToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('show');
                // Remove toast from DOM after animation completes
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }
        }
    </script>
</body>
</html>
