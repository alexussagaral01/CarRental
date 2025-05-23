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

// Pagination variables
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$customer_search = isset($_GET['customer_search']) ? $_GET['customer_search'] : '';

// Build the WHERE clause for filters
$where_clause = "1=1";
if (!empty($start_date)) {
    $where_clause .= " AND DATE(rh.DATE_CREATED) >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_clause .= " AND DATE(rh.DATE_CREATED) <= '" . $conn->real_escape_string($end_date) . "'";
}
if (!empty($customer_search)) {
    $customer_search = $conn->real_escape_string($customer_search);
    $where_clause .= " AND (c.FIRST_NAME LIKE '%$customer_search%' OR c.LAST_NAME LIKE '%$customer_search%' OR CONCAT(c.FIRST_NAME, ' ', c.LAST_NAME) LIKE '%$customer_search%')";
}

// Count total transactions for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM rental_hdr rh
    JOIN customer c ON rh.CUSTOMER_ID = c.CUSTOMER_ID
    JOIN vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
    JOIN rental_dtl rd ON rh.RENTAL_DTL_ID = rd.RENTAL_DTL_ID
    JOIN payment p ON rh.PAYMENT_ID = p.PAYMENT_ID
    WHERE $where_clause";

$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $items_per_page);

// Fetch transactions with pagination and filters
$transactions_query = "
    SELECT 
        rh.RENTAL_HDR_ID,
        CONCAT(c.FIRST_NAME, ' ', c.LAST_NAME) as customer_name,
        CONCAT(v.VEHICLE_BRAND, ' ', v.MODEL) as vehicle,
        rd.DURATION as duration_hours,
        rd.LINE_TOTAL as amount,
        p.PAYMENT_METHOD as payment_method,
        c.DRIVER_TYPE,
        d.DRIVER_NAME,
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
    LEFT JOIN
        driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    WHERE 
        $where_clause
    ORDER BY 
        rh.DATE_CREATED DESC
    LIMIT $offset, $items_per_page";

$transactions_result = $conn->query($transactions_query);
$transactions = [];

if ($transactions_result && $transactions_result->num_rows > 0) {
    while ($row = $transactions_result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

// Get unique payment methods for filter dropdown
$payment_methods_query = "SELECT DISTINCT PAYMENT_METHOD FROM payment";
$payment_methods_result = $conn->query($payment_methods_query);
$payment_methods = [];

if ($payment_methods_result && $payment_methods_result->num_rows > 0) {
    while ($row = $payment_methods_result->fetch_assoc()) {
        $payment_methods[] = $row['PAYMENT_METHOD'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - View Transactions</title>
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
        
        /* Confirmation toast styling */
        .confirmation-toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease-in-out;
            visibility: hidden;
        }
        
        .confirmation-toast.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            visibility: visible;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            z-index: 9998;
            transition: opacity 0.3s ease-in-out;
            visibility: hidden;
        }
        
        .overlay.show {
            opacity: 1;
            visibility: visible;
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

    <!-- Transaction Management Interface -->
    <div class="container mx-auto px-4 py-8">
        <!-- Page Title and Filters -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Transaction Management</h1>
            <div class="glass-effect p-4 rounded-lg shadow-md">
                <form action="" method="GET" id="filtersForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input 
                            type="date" 
                            name="start_date" 
                            value="<?php echo htmlspecialchars($start_date); ?>"
                            class="w-full rounded-lg border-gray-200 p-2.5"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input 
                            type="date" 
                            name="end_date" 
                            value="<?php echo htmlspecialchars($end_date); ?>"
                            class="w-full rounded-lg border-gray-200 p-2.5"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                        <input 
                            type="text" 
                            name="customer_search" 
                            value="<?php echo htmlspecialchars($customer_search); ?>"
                            placeholder="Search by name..."
                            class="w-full rounded-lg border-gray-200 p-2.5"
                        >
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200">
                            Apply Filters
                        </button>
                        <button type="button" id="resetFiltersBtn" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="glass-effect rounded-lg overflow-hidden shadow-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(count($transactions) > 0): ?>
                        <?php foreach($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo $transaction['customer_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $transaction['vehicle']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php 
                                    $hours = $transaction['duration_hours'];
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $transaction['payment_method'] === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($transaction['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($transaction['DRIVER_TYPE'] == 'self'): ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Self Drive
                                        </span>
                                    <?php elseif (!empty($transaction['DRIVER_NAME'])): ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo $transaction['DRIVER_NAME']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Not Assigned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="transaction_detail.php?id=<?php echo $transaction['RENTAL_HDR_ID']; ?>" 
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2 text-sm font-medium">No transactions found</p>
                                <?php if (!empty($start_date) || !empty($end_date) || !empty($customer_search)): ?>
                                    <p class="mt-1 text-sm text-gray-400">Try adjusting your filters</p>
                                    <a href="view_transactions.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                        Clear Filters
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_records > 0): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?php echo min(($current_page - 1) * $items_per_page + 1, $total_records); ?></span> 
                                to <span class="font-medium"><?php echo min($current_page * $items_per_page, $total_records); ?></span> 
                                of <span class="font-medium"><?php echo $total_records; ?></span> transactions
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($start_date) ? '&start_date='.$start_date : ''; ?><?php echo !empty($end_date) ? '&end_date='.$end_date : ''; ?><?php echo !empty($customer_search) ? '&customer_search='.$customer_search : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, min($current_page - 2, $total_pages - 4));
                                $end_page = min($total_pages, max($current_page + 2, 5));
                                
                                // Always show first page button
                                if ($start_page > 1) {
                                    echo '<a href="?page=1'.(!empty($start_date) ? '&start_date='.$start_date : '').(!empty($end_date) ? '&end_date='.$end_date : '').(!empty($customer_search) ? '&customer_search='.$customer_search : '').'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                    
                                    if ($start_page > 2) {
                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                    }
                                }
                                
                                // Page links
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    if ($i == $current_page) {
                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">'.$i.'</span>';
                                    } else {
                                        echo '<a href="?page='.$i.(!empty($start_date) ? '&start_date='.$start_date : '').(!empty($end_date) ? '&end_date='.$end_date : '').(!empty($customer_search) ? '&customer_search='.$customer_search : '').'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">'.$i.'</a>';
                                    }
                                }
                                
                                // Always show last page button
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                    }
                                    
                                    echo '<a href="?page='.$total_pages.(!empty($start_date) ? '&start_date='.$start_date : '').(!empty($end_date) ? '&end_date='.$end_date : '').(!empty($customer_search) ? '&customer_search='.$customer_search : '').'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">'.$total_pages.'</a>';
                                }
                                ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($start_date) ? '&start_date='.$start_date : ''; ?><?php echo !empty($end_date) ? '&end_date='.$end_date : ''; ?><?php echo !empty($customer_search) ? '&customer_search='.$customer_search : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
            
            // Reset filters button functionality
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                // Clear all input fields
                document.querySelector('input[name="start_date"]').value = '';
                document.querySelector('input[name="end_date"]').value = '';
                document.querySelector('input[name="customer_search"]').value = '';
                
                // Redirect to the page without any query parameters
                window.location.href = 'view_all_transactions.php';
            });
            
            // Date validation to ensure end date is not before start date
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            
            if (startDateInput && endDateInput) {
                endDateInput.addEventListener('change', function() {
                    if (startDateInput.value && endDateInput.value) {
                        const startDate = new Date(startDateInput.value);
                        const endDate = new Date(endDateInput.value);
                        
                        if (endDate < startDate) {
                            alert('End date cannot be before start date');
                            endDateInput.value = '';
                        }
                    }
                });
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
