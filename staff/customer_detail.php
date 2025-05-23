<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff']) || $_SESSION['staff'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_customer.php");
    exit();
}

$customer_id = intval($_GET['id']);

// Database connection
require_once('../connect.php');

// Fetch customer details
$query = "
    SELECT 
        c.*,
        d.DRIVER_ID,
        d.DRIVER_NAME,
        d.LICENSE_NUMBER,
        d.CONTACT_NUMBER as driver_contact,
        d.ADDRESS as driver_address,
        d.BIRTHDATE as driver_birthdate,
        d.GENDER as driver_gender,
        d.STATUS as driver_status
    FROM 
        customer c
    LEFT JOIN 
        driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    WHERE 
        c.CUSTOMER_ID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Customer not found
    header("Location: view_customer.php");
    exit();
}

$customer = $result->fetch_assoc();

// Fetch rental history for this customer
$history_query = "
    SELECT 
        rh.RENTAL_HDR_ID,
        CONCAT(v.VEHICLE_BRAND, ' ', v.MODEL) as vehicle,
        rd.START_DATE,
        rd.END_DATE,
        rd.TOTAL_AMOUNT,
        p.STATUS as payment_status
    FROM 
        rental_hdr rh
    JOIN 
        vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
    JOIN 
        rental_dtl rd ON rh.RENTAL_DTL_ID = rd.RENTAL_DTL_ID
    JOIN 
        payment p ON rh.PAYMENT_ID = p.PAYMENT_ID
    WHERE 
        rh.CUSTOMER_ID = ?
    ORDER BY 
        rd.START_DATE DESC";

$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $customer_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$rental_history = [];

if ($history_result->num_rows > 0) {
    while ($row = $history_result->fetch_assoc()) {
        $rental_history[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Customer Details</title>
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

    <!-- Customer Details Page -->
    <div class="container mx-auto px-4 py-8">
        <!-- Action buttons -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Customer Details</h1>
                <p class="text-gray-600">Customer ID: <?php echo $customer_id; ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="view_customer.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 flex items-center space-x-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Customers</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Customer Information -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Customer Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">First Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['FIRST_NAME']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Last Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['LAST_NAME']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['EMAIL']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['CONTACT_NUM']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['CUSTOMER_ADDRESS']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Customer Type</p>
                        <p class="font-medium"><?php echo ucfirst(htmlspecialchars($customer['CUSTOMER_TYPE'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Driver Type</p>
                        <p class="font-medium capitalize"><?php echo $customer['DRIVER_TYPE']; ?> Driver</p>
                    </div>
                    <?php if ($customer['DRIVER_TYPE'] == 'self'): ?>
                    <div>
                        <p class="text-sm text-gray-500">Driver's License</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['DRIVERS_LICENSE']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($customer['DRIVER_TYPE'] != 'self' && !empty($customer['DRIVER_ID'])): ?>
            <!-- Assigned Driver Information -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Assigned Driver
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Driver Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['DRIVER_NAME']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">License Number</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['LICENSE_NUMBER']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact Number</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['driver_contact']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Gender</p>
                        <p class="font-medium"><?php echo $customer['driver_gender'] == '0' ? 'Male' : 'Female'; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium"><?php echo htmlspecialchars($customer['driver_address']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            <?php echo $customer['driver_status'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                            <?php echo htmlspecialchars($customer['driver_status']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php elseif ($customer['DRIVER_TYPE'] != 'self'): ?>
            <!-- No Driver Assigned Information -->
            <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    No Driver Assigned
                </h2>
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">This customer requires a driver but none has been assigned yet.</p>
                    <a href="view_customer.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all inline-flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>Assign a Driver</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Rental History -->
        <div class="glass-effect rounded-xl overflow-hidden shadow-lg p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Rental History
            </h2>

            <?php if (count($rental_history) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($rental_history as $rental): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $rental['RENTAL_HDR_ID']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($rental['vehicle']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                        $start = new DateTime($rental['START_DATE']); 
                                        echo $start->format('M d, Y h:i A'); 
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                        $end = new DateTime($rental['END_DATE']); 
                                        echo $end->format('M d, Y h:i A'); 
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱<?php echo number_format($rental['TOTAL_AMOUNT'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo ($rental['payment_status'] === 'Paid') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo $rental['payment_status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="transaction_detail.php?id=<?php echo $rental['RENTAL_HDR_ID']; ?>" class="text-blue-600 hover:text-blue-900">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-500">No rental history found for this customer.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
