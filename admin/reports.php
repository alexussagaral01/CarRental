<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "vehicle_rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$driver_type = isset($_GET['driver_type']) ? $_GET['driver_type'] : '';

// Add pagination variables
$items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Build query to fetch report data based on filters
$has_filters = !empty($start_date) || !empty($end_date) || !empty($driver_type);
$report_data = array();
$query_error = '';
$total_records = 0;
$total_pages = 0;

if ($has_filters) {
    // Modified query to match the actual database structure
    $query = "SELECT 
        c.FIRST_NAME AS customer_first_name,
        c.LAST_NAME AS customer_last_name,
        v.VEHICLE_TYPE,
        v.MODEL,
        v.VEHICLE_BRAND,
        v.LICENSE_PLATE,
        v.CAPACITY,
        rd.LINE_TOTAL,
        CASE 
            WHEN c.DRIVER_TYPE = 'with_driver' THEN d.DRIVER_NAME
            ELSE 'Self Drive'
        END AS assigned_driver
    FROM rental_hdr rh
    JOIN customer c ON rh.CUSTOMER_ID = c.CUSTOMER_ID
    JOIN vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
    JOIN rental_dtl rd ON rh.RENTAL_DTL_ID = rd.RENTAL_DTL_ID
    LEFT JOIN driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    WHERE 1=1";
    
    // Apply date filters if provided
    if (!empty($start_date)) {
        $query .= " AND DATE(rd.START_DATE) >= '" . $conn->real_escape_string($start_date) . "'";
    }
    
    if (!empty($end_date)) {
        $query .= " AND DATE(rd.END_DATE) <= '" . $conn->real_escape_string($end_date) . "'";
    }
    
    // Apply driver type filter if provided
    if (!empty($driver_type)) {
        $query .= " AND c.DRIVER_TYPE = '" . $conn->real_escape_string($driver_type) . "'";
    }
    
    // Get total records for pagination
    $count_query = $query;
    $count_result = $conn->query($count_query);
    if ($count_result) {
        $total_records = $count_result->num_rows;
    }
    // Calculate total pages here
    $total_pages = ceil($total_records / $items_per_page);

    $query .= " ORDER BY rd.START_DATE DESC LIMIT $offset, $items_per_page";
    
    $result = $conn->query($query);
    
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
    } else {
        $query_error = "Error executing query: " . $conn->error;
    }
}

// Even if no filters, show some recent rentals for demonstration
if (!$has_filters) {
    $demo_query = "SELECT 
        c.FIRST_NAME AS customer_first_name,
        c.LAST_NAME AS customer_last_name,
        v.VEHICLE_TYPE,
        v.MODEL,
        v.VEHICLE_BRAND,
        v.LICENSE_PLATE,
        v.CAPACITY,
        rd.LINE_TOTAL,
        CASE 
            WHEN c.DRIVER_TYPE = 'with_driver' THEN d.DRIVER_NAME
            ELSE 'Self Drive'
        END AS assigned_driver
    FROM rental_hdr rh
    JOIN customer c ON rh.CUSTOMER_ID = c.CUSTOMER_ID
    JOIN vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
    JOIN rental_dtl rd ON rh.RENTAL_DTL_ID = rd.RENTAL_DTL_ID
    LEFT JOIN driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    ORDER BY rh.DATE_CREATED DESC
    LIMIT 5";
    
    $demo_result = $conn->query($demo_query);
    
    if ($demo_result && $demo_result->num_rows > 0) {
        $total_records = $demo_result->num_rows;
        $total_pages = ceil($total_records / $items_per_page);
        while ($row = $demo_result->fetch_assoc()) {
            $report_data[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- PDF Generation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <!-- Fallback PDF export script -->
    <script src="js/pdf-export.js"></script>
    <!-- Ensure these libraries are properly loaded -->
    <script>
        window.onload = function() {
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                console.error('jsPDF library failed to load');
            }
        };
    </script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
    <!-- Hidden iframe for PDF download -->
    <iframe id="downloadFrame" style="display:none;"></iframe>
    
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
                            <a href="reports.php" class="px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium transition-all duration-200 flex items-center space-x-1">
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

    <!-- Reports Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Business Reports</h1>
            <p class="text-gray-600 mt-2">Analyze your business performance and make data-driven decisions</p>
        </div>

        <!-- Filter Controls -->
        <div class="glass-effect rounded-xl shadow-md p-6 mb-8">
            <form action="" method="GET" id="reportsFilterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        value="<?php echo htmlspecialchars($start_date); ?>"
                        class="w-full rounded-lg border-gray-300 p-2.5 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        value="<?php echo htmlspecialchars($end_date); ?>"
                        class="w-full rounded-lg border-gray-300 p-2.5 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Driver Type</label>
                    <select 
                        name="driver_type" 
                        class="w-full rounded-lg border-gray-300 p-2.5 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Types</option>
                        <option value="self" <?php echo $driver_type === 'self' ? 'selected' : ''; ?>>Self Drive</option>
                        <option value="with_driver" <?php echo $driver_type === 'with_driver' ? 'selected' : ''; ?>>With Driver</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter Reports</span>
                    </button>
                    <button 
                        type="button" 
                        id="resetFiltersBtn"
                        class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Reports Content -->
        <div class="glass-effect bg-white/75 backdrop-blur-md rounded-xl p-8 shadow-lg">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-6">
                <?php if (!empty($start_date) || !empty($end_date) || !empty($driver_type)): ?>
                    <div class="p-4 bg-blue-50 text-blue-700 rounded-lg">
                        <h3 class="font-semibold mb-2">Applied Filters:</h3>
                        <ul class="text-left list-disc list-inside">
                            <?php if (!empty($start_date)): ?>
                                <li>Start Date: <?php echo date('F d, Y', strtotime($start_date)); ?></li>
                            <?php endif; ?>
                            
                            <?php if (!empty($end_date)): ?>
                                <li>End Date: <?php echo date('F d, Y', strtotime($end_date)); ?></li>
                            <?php endif; ?>
                            
                            <?php if (!empty($driver_type)): ?>
                                <li>Driver Type: <?php echo $driver_type === 'self' ? 'Self Drive' : 'With Driver'; ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-blue-50 text-blue-700 rounded-lg">
                        <h3 class="font-semibold">Showing Recent Rentals</h3>
                        <p class="text-sm">Apply filters for more specific results</p>
                    </div>
                <?php endif; ?>
                
                <!-- Export Report Button - Now using JavaScript -->
                <div class="mt-4 md:mt-0 self-end md:self-start ml-auto">
                    <button 
                        id="exportButton" 
                        type="button" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center space-x-2"
                        onclick="generatePDF('reportTable', {
                            startDate: document.querySelector('input[name=\'start_date\']').value,
                            endDate: document.querySelector('input[name=\'end_date\']').value,
                            driverType: document.querySelector('select[name=\'driver_type\']').value
                        })"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export Report</span>
                    </button>
                </div>
            </div>
            
            <?php if (!empty($query_error)): ?>
                <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg">
                    <p><strong>Error:</strong> <?php echo $query_error; ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Display Length and Search -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Show</span>
                    <select 
                        onchange="changeDisplayLength(this.value)"
                        class="border border-gray-300 rounded-md px-3 py-1 text-gray-600"
                    >
                        <option value="10" <?php echo $items_per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $items_per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $items_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $items_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span class="text-sm text-gray-600">entries</span>
                </div>
            </div>

            <!-- Report Table -->
            <div class="overflow-x-auto">
                <table id="reportTable" class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License Plate</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Driver</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($report_data) > 0): ?>
                            <?php foreach ($report_data as $rental): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['customer_first_name'] . ' ' . $rental['customer_last_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['VEHICLE_TYPE']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['MODEL']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['VEHICLE_BRAND']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['LICENSE_PLATE']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                                    ₱<?php echo number_format($rental['LINE_TOTAL'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['CAPACITY']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($rental['assigned_driver']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    <p>No records found matching your search criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer with Entry Info and Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?php echo min(($current_page - 1) * $items_per_page + 1, $total_records); ?></span> 
                            to <span class="font-medium"><?php echo min($current_page * $items_per_page, $total_records); ?></span> 
                            of <span class="font-medium"><?php echo $total_records; ?></span> entries
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($start_date) ? '&start_date='.$start_date : ''; ?><?php echo !empty($end_date) ? '&end_date='.$end_date : ''; ?><?php echo !empty($driver_type) ? '&driver_type='.$driver_type : ''; ?>" 
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
                                echo '<a href="?page=1'.(!empty($start_date) ? '&start_date='.$start_date : '').(!empty($end_date) ? '&end_date='.$end_date : '').(!empty($driver_type) ? '&driver_type='.$driver_type : '').'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                
                                if ($start_page > 2) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                            }

                            // Page links
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                if ($i == $current_page) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">'.$i.'</span>';
                                } else {
                                    echo '<a href="?page='.$i.(!empty($start_date) ? '&start_date='.$start_date : '').(!empty($end_date) ? '&end_date='.$end_date : '').(!empty($driver_type) ? '&driver_type='.$driver_type : '').'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">'.$i.'</a>';
                                }
                            }

                            // Always show last page button
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                                echo '<a href="?page='.$total_pages.(!empty($start_date) ? '&start_date='.$start_date : '').(!empty($end_date) ? '&end_date='.$end_date : '').(!empty($driver_type) ? '&driver_type='.$driver_type : '').'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">'.$total_pages.'</a>';
                            }
                            ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($start_date) ? '&start_date='.$start_date : ''; ?><?php echo !empty($end_date) ? '&end_date='.$end_date : ''; ?><?php echo !empty($driver_type) ? '&driver_type='.$driver_type : ''; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4-4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4-4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Date validation to ensure end date is not before start date
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            const driverTypeSelect = document.querySelector('select[name="driver_type"]'); // Fixed missing variable
            
            // Function to validate dates
            function validateDates() {
                if (startDateInput.value && endDateInput.value) {
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(endDateInput.value);
                    
                    if (endDate < startDate) {
                        alert('End date cannot be before start date');
                        endDateInput.value = '';
                    }
                }
            }
            
            // Validate when end date changes
            endDateInput.addEventListener('change', validateDates);
            
            // Validate when form is submitted
            document.querySelector('form').addEventListener('submit', function(e) {
                if (startDateInput.value && endDateInput.value) {
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(endDateInput.value);
                    
                    if (endDate < startDate) {
                        e.preventDefault();
                        alert('End date cannot be before start date');
                        endDateInput.value = '';
                    }
                }
            });
            
            // Update hidden form values when filters change
            document.querySelector('form').addEventListener('submit', function() {
                // Update export form values when filters are changed
                document.querySelector('#exportForm input[name="start_date"]').value = startDateInput.value;
                document.querySelector('#exportForm input[name="end_date"]').value = endDateInput.value;
                document.querySelector('#exportForm input[name="driver_type"]').value = 
                    document.querySelector('select[name="driver_type"]').value;
            });

            // Reset filters button functionality
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                // Clear all filter inputs
                startDateInput.value = '';
                endDateInput.value = '';
                driverTypeSelect.value = '';
                
                // Redirect to the page without any query parameters
                window.location.href = 'reports.php';
            });

            // Export PDF functionality
            document.getElementById('exportButton').addEventListener('click', function() {
                try {
                    // Check if jsPDF is available
                    if (typeof window.jspdf === 'undefined') {
                        throw new Error('PDF generation library is not loaded properly');
                    }
                    
                    // Get report data from table
                    const tableData = [];
                    const tableHeaders = [];
                    
                    // Get headers
                    document.querySelectorAll('#reportTable thead th').forEach(header => {
                        tableHeaders.push(header.textContent.trim());
                    });
                    
                    // Get rows
                    let hasData = false;
                    document.querySelectorAll('#reportTable tbody tr').forEach(row => {
                        if (!row.querySelector('td[colspan]')) { // Skip "no records" message rows
                            hasData = true;
                            const rowData = [];
                            row.querySelectorAll('td').forEach(cell => {
                                // Preserve the peso sign in the amount column
                                const text = cell.textContent.trim();
                                rowData.push(text);
                            });
                            tableData.push(rowData);
                        }
                    });
                    
                    // If no data, show a message but still generate PDF with headers
                    if (!hasData) {
                        tableData.push(['No data available', '', '', '', '', '', '', '']);
                    }
                    
                    // Define the PDF document
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF('l', 'mm', 'a4'); // landscape
                    
                    // Add title and date
                    doc.setFontSize(18);
                    doc.text('RentWheels - Vehicle Rental Report', doc.internal.pageSize.getWidth() / 2, 15, { align: 'center' });
                    doc.setFontSize(11);
                    doc.text('Generated on: ' + new Date().toLocaleString(), doc.internal.pageSize.getWidth() / 2, 22, { align: 'center' });
                    
                    // Add filters section
                    doc.setFontSize(12);
                    let yPos = 35;
                    doc.text('Applied Filters:', 14, yPos);
                    
                    if (startDateInput.value) {
                        yPos += 6;
                        doc.text('Start Date: ' + new Date(startDateInput.value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), 14, yPos);
                    }
                    
                    if (endDateInput.value) {
                        yPos += 6;
                        doc.text('End Date: ' + new Date(endDateInput.value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), 14, yPos);
                    }
                    
                    if (driverTypeSelect.value) {
                        yPos += 6;
                        doc.text('Driver Type: ' + (driverTypeSelect.value === 'self' ? 'Self Drive' : 'With Driver'), 14, yPos);
                    }
                    
                    // Add the table
                    doc.autoTable({
                        head: [tableHeaders],
                        body: tableData,
                        startY: yPos + 10,
                        styles: { fontSize: 8, cellPadding: 2 },
                        headStyles: { fillColor: [52, 152, 219], textColor: [255, 255, 255] },
                        alternateRowStyles: { fillColor: [240, 248, 255] },
                        margin: { top: 10 }
                    });
                    
                    // Add summary if data exists and is not the "no data" placeholder
                    if (hasData) {
                        let totalAmount = 0;
                        tableData.forEach(row => {
                            // Find the total amount column (index 5 - zero-based)
                            const amountText = row[5];
                            if (amountText && amountText.includes('₱')) {
                                const amount = parseFloat(amountText.replace('₱', '').replace(/,/g, ''));
                                if (!isNaN(amount)) {
                                    totalAmount += amount;
                                }
                            }
                        });
                        
                        if (doc.lastAutoTable) {
                            const finalY = doc.lastAutoTable.finalY + 10;
                            doc.text('Total Records: ' + tableData.length, 14, finalY);
                            // Use the peso symbol ₱ directly in the text
                            doc.text('Total Revenue: ₱' + totalAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','), 14, finalY + 6);
                        }
                    }
                    
                    // Save the PDF
                    doc.save('RentWheels_Report_' + new Date().toISOString().slice(0, 10) + '.pdf');
                } catch (error) {
                    console.error('PDF Generation Error:', error);
                    alert('Failed to generate PDF report. Please check if all required libraries are loaded.');
                }
            });
        });

        // Change display length functionality
        function changeDisplayLength(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('items_per_page', value);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
