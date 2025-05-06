<?php
session_start();
require_once('connect.php');

// Initialize variables
$customer = null;
$payment = null;
$vehicle = null;
$rentalDetail = null;
$rentalHeader = null;

// Check if we have rental header ID in session
if(isset($_SESSION['rental_hdr_id'])) {
    $rentalHdrId = $_SESSION['rental_hdr_id'];
    
    // Fetch rental header data
    $sql = "SELECT * FROM rental_hdr WHERE RENTAL_HDR_ID = $rentalHdrId";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $rentalHeader = $result->fetch_assoc();
        
        // Fetch related data
        $customerId = $rentalHeader['CUSTOMER_ID'];
        $paymentId = $rentalHeader['PAYMENT_ID'];
        $vehicleId = $rentalHeader['VEHICLE_ID'];
        $rentalDtlId = $rentalHeader['RENTAL_DTL_ID'];
        
        // Fetch customer data
        $customerSql = "SELECT * FROM customer WHERE CUSTOMER_ID = $customerId";
        $customerResult = $conn->query($customerSql);
        if ($customerResult->num_rows > 0) {
            $customer = $customerResult->fetch_assoc();
        }
        
        // Fetch payment data
        $paymentSql = "SELECT * FROM payment WHERE PAYMENT_ID = $paymentId";
        $paymentResult = $conn->query($paymentSql);
        if ($paymentResult->num_rows > 0) {
            $payment = $paymentResult->fetch_assoc();
        }
        
        // Fetch vehicle data
        $vehicleSql = "SELECT * FROM vehicle WHERE VEHICLE_ID = $vehicleId";
        $vehicleResult = $conn->query($vehicleSql);
        if ($vehicleResult->num_rows > 0) {
            $vehicle = $vehicleResult->fetch_assoc();
        }
        
        // Fetch rental detail data
        $rentalDtlSql = "SELECT * FROM rental_dtl WHERE RENTAL_DTL_ID = $rentalDtlId";
        $rentalDtlResult = $conn->query($rentalDtlSql);
        if ($rentalDtlResult->num_rows > 0) {
            $rentalDetail = $rentalDtlResult->fetch_assoc();
        }
    }
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
        @media print {
            .no-print { display: none; }
            body { background: white !important; }
            .glass-effect {
                background-color: white !important;
                backdrop-filter: none;
            }
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

                <!-- Navigation -->
                <nav class="hidden md:block mx-auto">
                    <ul class="flex space-x-4 justify-center">
                        <li>
                            <a href="dashboard.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="rent.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Rent</span>
                            </a>
                        </li>
                        <li>
                            <a href="details.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>About</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                Transaction Details
            </h1>
            <p class="text-gray-600">Order Summary and Customer Information</p>
        </div>

        <?php if($customer && $vehicle && $rentalDetail): ?>
        <!-- Content Card -->
        <div class="glass-effect rounded-2xl shadow-2xl p-6 md:p-8">
            <!-- Transaction Information -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Left Column - Rented Vehicles -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-800 pb-2 border-b">Rented Vehicle</h2>
                    
                    <!-- Vehicle Info -->
                    <div class="bg-white/50 rounded-lg p-4 space-y-2">
                        <h3 class="font-semibold text-gray-800"><?php echo $vehicle['VEHICLE_BRAND']; ?>, <?php echo $vehicle['MODEL']; ?></h3>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p><?php echo $vehicle['YEAR']; ?>, <?php echo $vehicle['COLOR']; ?></p>
                            <p>License Plate: <span class="font-medium"><?php echo $vehicle['LICENSE_PLATE']; ?></span></p>
                            <p>Description: <?php echo $vehicle['VEHICLE_DESCRIPTION']; ?></p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-blue-600 font-semibold">₱<?php echo number_format($rentalDetail['HOURLY_RATE'], 2); ?>/hour</span>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">x1</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rental Period Information -->
                    <div class="bg-white/50 rounded-lg p-4 space-y-2">
                        <h3 class="font-semibold text-gray-800">Rental Period</h3>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p>From: <span class="font-medium"><?php echo date('F j, Y g:i A', strtotime($rentalDetail['START_DATE'])); ?></span></p>
                            <p>To: <span class="font-medium"><?php echo date('F j, Y g:i A', strtotime($rentalDetail['END_DATE'])); ?></span></p>
                            <p>Duration: <span class="font-medium"><?php echo $rentalDetail['DURATION']; ?> hours</span></p>
                            <p>Pickup Location: <span class="font-medium"><?php echo $rentalDetail['PICKUP_LOCATION']; ?></span></p>
                        </div>
                    </div>

                    <!-- Cost Breakdown -->
                    <div class="bg-white/50 rounded-lg p-4 space-y-2">
                        <h3 class="font-semibold text-gray-800">Cost Breakdown</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Base Rate (<?php echo $rentalDetail['DURATION']; ?> hours × ₱<?php echo number_format($rentalDetail['HOURLY_RATE'], 2); ?>)</span>
                                <span>₱<?php echo number_format($rentalDetail['TOTAL_AMOUNT'], 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>VAT (12%)</span>
                                <span>₱<?php echo number_format($rentalDetail['VAT_AMOUNT'], 2); ?></span>
                            </div>
                            <div class="flex justify-between font-semibold pt-2 border-t">
                                <span>Total</span>
                                <span>₱<?php echo number_format($rentalDetail['LINE_TOTAL'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Customer Details -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-800 pb-2 border-b">Customer Details</h2>
                    <div class="bg-white/50 rounded-lg p-4">
                        <div class="grid gap-3 text-sm">
                            <div>
                                <p class="text-gray-500">Customer Name</p>
                                <p class="font-medium text-gray-800"><?php echo $customer['LAST_NAME']; ?>, <?php echo $customer['FIRST_NAME']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Email</p>
                                <p class="font-medium text-gray-800"><?php echo $customer['EMAIL']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Contact Number</p>
                                <p class="font-medium text-gray-800"><?php echo $customer['CONTACT_NUM']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Customer Address</p>
                                <p class="font-medium text-gray-800"><?php echo $customer['CUSTOMER_ADDRESS']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Company Name</p>
                                <p class="font-medium text-gray-800"><?php echo empty($customer['COMPANY_NAME']) ? 'None' : $customer['COMPANY_NAME']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Job Title</p>
                                <p class="font-medium text-gray-800"><?php echo empty($customer['JOB_TITLE']) ? 'None' : $customer['JOB_TITLE']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Transaction Date</p>
                                <p class="font-medium text-gray-800"><?php echo date('Y-m-d', strtotime($rentalHeader['DATE_CREATED'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Driver Type</p>
                                <p class="font-medium text-gray-800"><?php echo $customer['DRIVER_TYPE'] === 'self' ? 'Self-Drive' : 'With Driver'; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Payment Method</p>
                                <p class="font-medium text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $payment['PAYMENT_METHOD'])); ?></p>
                            </div>
                        </div>

                        <!-- Total Amount -->
                        <div class="mt-6 pt-4 border-t">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-800">Total Amount</span>
                                <span class="text-2xl font-bold text-blue-600">₱<?php echo number_format($rentalDetail['LINE_TOTAL'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t">
                <div class="flex space-x-4">
                    <button onclick="window.location.href='rent.php'" class="px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-900 text-white rounded-lg hover:opacity-90 transition-all duration-200 no-print">
                        Book Again
                    </button>
                    <button onclick="window.location.href='dashboard.php'" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:opacity-90 transition-all duration-200 no-print">
                        Back to Home
                    </button>
                </div>
                
                <button onclick="window.print()" class="no-print flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:opacity-90 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Receipt</span>
                </button>
            </div>
        </div>
        <?php else: ?>
        <!-- No data available -->
        <div class="glass-effect rounded-2xl shadow-2xl p-6 md:p-8 text-center">
            <p class="text-xl text-gray-700 mb-4">No transaction details found.</p>
            <button onclick="window.location.href='rent.php'" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:opacity-90 transition-all duration-200">
                Book a Vehicle
            </button>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>