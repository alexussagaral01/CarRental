<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff']) || $_SESSION['staff'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "vehicle_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get staff info from session
$staff_id = $_SESSION['staff_id'];
$username = $_SESSION['username'];

// Handle search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch statistics
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM customer")->fetch_assoc()['total'];
$available_cars = $conn->query("SELECT COUNT(*) as total FROM vehicle WHERE STATUS = 'Available'")->fetch_assoc()['total'];
$active_drivers = $conn->query("SELECT COUNT(*) as total FROM driver")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(line_total) as total FROM rental_dtl")->fetch_assoc()['total'];

// Format total revenue
$formatted_revenue = number_format($total_revenue ?? 0, 2);

// Pagination for recent bookings
$entries_per_page = 5; // Number of bookings to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Build the search condition if search query is provided
$search_condition = '';
if (!empty($search_query)) {
    $search_query_escaped = $conn->real_escape_string($search_query);
    $search_condition = " AND (
        c.FIRST_NAME LIKE '%$search_query_escaped%' OR 
        c.LAST_NAME LIKE '%$search_query_escaped%' OR
        v.VEHICLE_TYPE LIKE '%$search_query_escaped%' OR
        v.MODEL LIKE '%$search_query_escaped%' OR
        v.LICENSE_PLATE LIKE '%$search_query_escaped%'
    )";
}

// Count total bookings for pagination
$total_bookings_query = "SELECT COUNT(*) as total 
                         FROM rental_hdr rh
                         JOIN customer c ON rh.CUSTOMER_ID = c.CUSTOMER_ID
                         JOIN vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
                         WHERE 1=1 $search_condition";
$total_bookings_result = $conn->query($total_bookings_query);
$total_records = $total_bookings_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $entries_per_page);

// Fetch recent bookings data with pagination and search
$recent_bookings_query = "
    SELECT 
        c.FIRST_NAME, 
        c.LAST_NAME, 
        c.CONTACT_NUM, 
        c.EMAIL,
        c.DRIVER_TYPE,
        c.ASSIGNED_DRIVER_ID,
        v.VEHICLE_TYPE, 
        v.MODEL, 
        v.LICENSE_PLATE,
        d.DRIVER_NAME
    FROM 
        rental_hdr rh
    JOIN 
        customer c ON rh.CUSTOMER_ID = c.CUSTOMER_ID
    JOIN 
        vehicle v ON rh.VEHICLE_ID = v.VEHICLE_ID
    LEFT JOIN 
        driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID
    WHERE 1=1 $search_condition
    ORDER BY 
        rh.DATE_CREATED DESC
    LIMIT $offset, $entries_per_page";

$recent_bookings_result = $conn->query($recent_bookings_query);
$recent_bookings = [];

if ($recent_bookings_result && $recent_bookings_result->num_rows > 0) {
    while ($row = $recent_bookings_result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

// Fetch vehicle type data for pie chart
$vehicle_types_query = "SELECT v.VEHICLE_TYPE, COUNT(*) as count 
                      FROM rental_dtl rd 
                      JOIN vehicle v ON rd.VEHICLE_ID = v.VEHICLE_ID 
                      GROUP BY v.VEHICLE_TYPE";
$vehicle_types_result = $conn->query($vehicle_types_query);

$vehicle_types_data = [];
if ($vehicle_types_result && $vehicle_types_result->num_rows > 0) {
    while ($row = $vehicle_types_result->fetch_assoc()) {
        $vehicle_types_data[] = [
            'value' => (int)$row['count'],
            'name' => $row['VEHICLE_TYPE']
        ];
    }
} else {
    // If no data, add dummy data
    $vehicle_types_data = [
        ['value' => 0, 'name' => 'No rentals yet']
    ];
}

// Convert to JSON for JavaScript
$vehicle_types_json = json_encode($vehicle_types_data);

// After the other queries, add this to fetch available vehicles
$available_vehicles_query = "SELECT VEHICLE_ID, VEHICLE_TYPE, VEHICLE_BRAND, MODEL, CAPACITY 
                           FROM vehicle 
                           WHERE STATUS = 'Available' 
                           ORDER BY VEHICLE_BRAND, MODEL 
                           LIMIT 5";
$available_vehicles_result = $conn->query($available_vehicles_query);
$available_vehicles = [];

if ($available_vehicles_result && $available_vehicles_result->num_rows > 0) {
    while ($row = $available_vehicles_result->fetch_assoc()) {
        $available_vehicles[] = $row;
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add ECharts JavaScript library -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
        
        /* Modern Card Styles */
        .stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 50%);
            z-index: 1;
        }
        
        .stat-card-inner {
            position: relative;
            z-index: 2;
        }
        
        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
            background-clip: text;
            -webkit-background-clip: text;
        }
        
        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0.8;
        }
        
        /* Card Specific Colors */
        .card-blue {
            background: linear-gradient(135deg, #EEF7FF 0%, #F6FAFF 100%);
            border: 1px solid #E1EEFF;
        }
        
        .card-blue .stat-icon {
            background: linear-gradient(135deg, #0EA5E9 0%, #3B82F6 100%);
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }
        
        .card-blue .stat-value {
            color: #1E40AF;
        }
        
        .card-green {
            background: linear-gradient(135deg, #ECFDF5 0%, #F8FDF9 100%);
            border: 1px solid #D1FAE5;
        }
        
        .card-green .stat-icon {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        
        .card-green .stat-value {
            color: #065F46;
        }
        
        .card-yellow {
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF9F0 100%);
            border: 1px solid #FEF3C7;
        }
        
        .card-yellow .stat-icon {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }
        
        .card-yellow .stat-value {
            color: #92400E;
        }
        
        .card-purple {
            background: linear-gradient(135deg, #F5F3FF 0%, #FAF5FF 100%);
            border: 1px solid #E9D5FF;
        }
        
        .card-purple .stat-icon {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
        }
        
        .card-purple .stat-value {
            color: #5B21B6;
        }
        
        /* Dropdown Menu Styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            min-width: 180px;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 10;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            color: #4b5563;
            font-size: 0.875rem;
            transition: all 0.15s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f3f4f6;
            color: #1e40af;
        }
        
        .dropdown-item.active {
            background-color: #e0e7ff;
            color: #4f46e5;
            font-weight: 500;
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
                <nav class="hidden md:block">
                    <ul class="flex space-x-1">
                        <li>
                            <a href="staff_dashboard.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="manage_vehicle.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Manage Vehicle</span>
                            </a>
                        </li>
                        <li>
                            <a href="manage_drivers.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Manage Drivers</span>
                            </a>
                        </li>
                        <li>
                            <a href="view_customer.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>View Customer</span>
                            </a>
                        </li>
                        <li>
                            <a href="view_transactions.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                                <span>View Transactions</span>
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

    <!-- Dashboard Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Bookings Card -->
            <div class="stat-card card-blue shadow-lg p-6">
                <div class="stat-card-inner flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="stat-icon text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="mt-4 md:mt-0 md:text-right">
                        <div class="stat-value"><?php echo $total_bookings; ?></div>
                        <div class="stat-label text-blue-600">Total Bookings</div>
                    </div>
                </div>
            </div>

            <!-- Available Cars Card -->
            <div class="stat-card card-green shadow-lg p-6">
                <div class="stat-card-inner flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="stat-icon text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-4 md:mt-0 md:text-right">
                        <div class="stat-value"><?php echo $available_cars; ?></div>
                        <div class="stat-label text-green-600">Available Cars</div>
                    </div>
                </div>
            </div>

            <!-- Active Drivers Card -->
            <div class="stat-card card-yellow shadow-lg p-6">
                <div class="stat-card-inner flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="stat-icon text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="mt-4 md:mt-0 md:text-right">
                        <div class="stat-value"><?php echo $active_drivers; ?></div>
                        <div class="stat-label text-yellow-600">Active Drivers</div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="stat-card card-purple shadow-lg p-6">
                <div class="stat-card-inner flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="stat-icon text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="mt-4 md:mt-0 md:text-right">
                        <div class="stat-value">₱<?php echo $formatted_revenue; ?></div>
                        <div class="stat-label text-purple-600">Total Revenue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Recent Bookings</h2>
                    <p class="text-sm text-gray-500 mt-1">Monitor your latest customer reservations</p>
                </div>
                
                <!-- Search Form -->
                <div class="relative">
                    <form action="" method="GET" class="flex items-center">
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Search bookings..." 
                                value="<?php echo htmlspecialchars($search_query); ?>"
                                class="pl-10 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm py-2 pr-10"
                            >
                            <?php if (!empty($search_query)): ?>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <a href="staff_dashboard.php" class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (!empty($search_query)): ?>
            <div class="px-6 py-3 bg-blue-50">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-blue-700">
                            Showing results for: <span class="font-medium">"<?php echo htmlspecialchars($search_query); ?>"</span>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</span>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License Plate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php if(count($recent_bookings) > 0): ?>
                            <?php foreach($recent_bookings as $booking): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold">
                                                <?php echo substr($booking['FIRST_NAME'], 0, 1) . substr($booking['LAST_NAME'], 0, 1); ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $booking['FIRST_NAME'] . ' ' . $booking['LAST_NAME']; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo $booking['EMAIL']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900"><?php echo $booking['MODEL']; ?></span>
                                            <span class="text-sm text-gray-500"><?php echo $booking['VEHICLE_TYPE']; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            <?php echo $booking['LICENSE_PLATE']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <?php echo $booking['CONTACT_NUM']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($booking['DRIVER_TYPE'] == 'self'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Self-Drive
                                            </span>
                                        <?php elseif (!empty($booking['DRIVER_NAME'])): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Driver: <?php echo $booking['DRIVER_NAME']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Driver Not Assigned
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-sm text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <?php if (!empty($search_query)): ?>
                                            <p>No results found for "<?php echo htmlspecialchars($search_query); ?>"</p>
                                            <a href="staff_dashboard.php" class="mt-2 text-blue-600 hover:text-blue-800 font-medium">Clear search</a>
                                        <?php else: ?>
                                            <p>No recent bookings found</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Controls -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?php echo min(($current_page - 1) * $entries_per_page + 1, $total_records); ?></span> 
                    to <span class="font-medium"><?php echo min($current_page * $entries_per_page, $total_records); ?></span> 
                    of <span class="font-medium"><?php echo $total_records; ?></span> bookings
                </div>
                <nav class="flex items-center justify-center space-x-1">
                    <?php if($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>" 
                           class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>
                    <?php else: ?>
                        <button disabled class="px-3 py-1 bg-white text-gray-400 border border-gray-200 rounded flex items-center cursor-not-allowed">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                    <?php endif; ?>
                    
                    <!-- Page Indicator -->
                    <span class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded">
                        <?php echo $current_page; ?> / <?php echo max(1, $total_pages); ?>
                    </span>
                    
                    <?php if($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_query) ? '&search='.urlencode($search_query) : ''; ?>" 
                           class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors flex items-center">
                            Next
                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <button disabled class="px-3 py-1 bg-white text-gray-400 border border-gray-200 rounded flex items-center cursor-not-allowed">
                            Next
                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <!-- Vehicle Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Available Vehicles -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Available Vehicles</h2>
                        <p class="text-sm text-gray-500 mt-1">Vehicles ready for rental</p>
                    </div>
                    
                    <!-- Capacity Filter Dropdown -->
                    <div class="dropdown">
                        <button id="capacityFilterBtn" class="flex items-center space-x-1 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span id="selectedCapacity">Filter by Capacity</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="capacityDropdown" class="dropdown-menu mt-1">
                            <a href="#" class="dropdown-item capacity-option" data-capacity="all">All Capacities</a>
                            <a href="#" class="dropdown-item capacity-option" data-capacity="4-5">4-5 Persons</a>
                            <a href="#" class="dropdown-item capacity-option" data-capacity="7-8">7-8 Persons</a>
                            <a href="#" class="dropdown-item capacity-option" data-capacity="10-18">10-18 Persons</a>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if(count($available_vehicles) > 0): ?>
                                <?php foreach($available_vehicles as $vehicle): ?>
                                    <tr class="hover:bg-gray-50 transition-colors vehicle-row" data-capacity="<?php echo $vehicle['CAPACITY']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo $vehicle['VEHICLE_TYPE']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $vehicle['VEHICLE_BRAND']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                            <?php echo $vehicle['MODEL']; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-sm text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 17h8M8 17v-4m8 4v-4m-8 4h8m-8-4h8M4 11l2-6h12l2 6M4 11h16M4 11v6h16v-6" />
                                            </svg>
                                            <p>No available vehicles found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vehicle Type Distribution Chart -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Vehicle Type Distribution</h2>
                </div>
                <div class="p-6">
                    <!-- Chart container -->
                    <div id="vehicleTypeChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Initialize the chart -->
    <script>
        // Initialize ECharts instance
        var chartDom = document.getElementById('vehicleTypeChart');
        var myChart = echarts.init(chartDom);
        
        // Chart configuration
        var option = {
            tooltip: {
                trigger: 'item',
                formatter: '{b}: {c} ({d}%)'
            },
            series: [
                {
                    name: 'Vehicle Types',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}: {c}',
                        fontSize: 14
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: true
                    },
                    data: <?php echo $vehicle_types_json; ?>
                }
            ]
        };
        
        // Set the chart option
        myChart.setOption(option);
        
        // Make chart responsive
        window.addEventListener('resize', function() {
            myChart.resize();
        });

        // Vehicle Capacity Filtering
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtn = document.getElementById('capacityFilterBtn');
            const dropdown = document.getElementById('capacityDropdown');
            const capacityOptions = document.querySelectorAll('.capacity-option');
            const selectedCapacityText = document.getElementById('selectedCapacity');
            const vehicleRows = document.querySelectorAll('.vehicle-row');
            
            // Toggle dropdown
            filterBtn.addEventListener('click', function() {
                dropdown.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            window.addEventListener('click', function(event) {
                if (!event.target.matches('#capacityFilterBtn') && 
                    !event.target.closest('#capacityFilterBtn') && 
                    !event.target.matches('#capacityDropdown') && 
                    !event.target.closest('#capacityDropdown')) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Handle filter selection
            capacityOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Update active state
                    capacityOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    
                    const capacityFilter = this.getAttribute('data-capacity');
                    selectedCapacityText.textContent = this.textContent;
                    
                    // Filter the table rows
                    filterVehiclesByCapacity(capacityFilter);
                    
                    // Close dropdown
                    dropdown.classList.remove('show');
                });
            });
            
            // Filter function
            function filterVehiclesByCapacity(capacityRange) {
                if (capacityRange === 'all') {
                    // Show all vehicles
                    vehicleRows.forEach(row => {
                        row.style.display = '';
                    });
                    return;
                }
                
                // Get capacity range
                const [min, max] = capacityRange.split('-').map(Number);
                
                // Filter rows
                vehicleRows.forEach(row => {
                    const capacity = parseInt(row.getAttribute('data-capacity'));
                    
                    if ((min <= capacity && capacity <= max)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>