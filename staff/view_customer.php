<?php
session_start();
require_once('../connect.php');

// Check if customer table has ASSIGNED_DRIVER_ID column, add if needed
$checkColumnSql = "SHOW COLUMNS FROM customer LIKE 'ASSIGNED_DRIVER_ID'";
$checkColumnResult = $conn->query($checkColumnSql);
if ($checkColumnResult->num_rows === 0) {
    $addColumnSql = "ALTER TABLE customer ADD COLUMN ASSIGNED_DRIVER_ID INT DEFAULT NULL";
    $conn->query($addColumnSql);
}

// Fetch all customers from the database with driver information if assigned
$sql = "SELECT c.*, d.DRIVER_NAME, d.DRIVER_ID 
        FROM customer c 
        LEFT JOIN driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID 
        ORDER BY c.LAST_NAME, c.FIRST_NAME";
$result = $conn->query($sql);

// Initialize arrays to store customers by driver type
$selfDriveCustomers = [];
$withDriverCustomers = [];

// Organize customers by driver type
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['DRIVER_TYPE'] == 'self') {
            $selfDriveCustomers[] = $row;
        } else {
            $withDriverCustomers[] = $row;
        }
    }
}

// Handle search functionality
$searchQuery = '';
if (isset($_POST['search']) && !empty($_POST['search_query'])) {
    $searchQuery = $_POST['search_query'];
    $sql = "SELECT c.*, d.DRIVER_NAME, d.DRIVER_ID 
            FROM customer c 
            LEFT JOIN driver d ON c.ASSIGNED_DRIVER_ID = d.DRIVER_ID 
            WHERE 
            c.FIRST_NAME LIKE '%$searchQuery%' OR 
            c.LAST_NAME LIKE '%$searchQuery%' OR 
            c.EMAIL LIKE '%$searchQuery%' OR 
            c.CONTACT_NUM LIKE '%$searchQuery%' OR 
            c.DRIVERS_LICENSE LIKE '%$searchQuery%'
            ORDER BY c.LAST_NAME, c.FIRST_NAME";
    $result = $conn->query($sql);
    
    // Clear previous arrays
    $selfDriveCustomers = [];
    $withDriverCustomers = [];
    
    // Repopulate arrays based on search results
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['DRIVER_TYPE'] == 'self') {
                $selfDriveCustomers[] = $row;
            } else {
                $withDriverCustomers[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Customer Management</title>
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Search Section -->
        <div class="mb-8">
            <div class="glass-effect rounded-xl p-6 shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Customer Management</h2>
                <form method="POST" action="" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" name="search_query" value="<?php echo $searchQuery; ?>" placeholder="Search by name, email, or license number..." 
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <button type="submit" name="search" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                        Search
                    </button>
                    <?php if (!empty($searchQuery)): ?>
                    <a href="view_customer.php" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all">
                        Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <button class="py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 whitespace-nowrap focus:outline-none" id="self-drive-tab">
                        Individual (<?php echo count($selfDriveCustomers); ?>)
                    </button>
                    <button class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap focus:outline-none" id="with-driver-tab">
                        Company (<?php echo count($withDriverCustomers); ?>)
                    </button>
                </nav>
            </div>
        </div>

        <!-- Self Drive Customers Table -->
        <div id="self-drive-content" class="glass-effect rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Individual Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">License Number</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($selfDriveCustomers) > 0): ?>
                            <?php foreach ($selfDriveCustomers as $customer): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($customer['FIRST_NAME'] . '+' . $customer['LAST_NAME']); ?>" alt="">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $customer['FIRST_NAME'] . ' ' . $customer['LAST_NAME']; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo ucfirst($customer['CUSTOMER_TYPE']); ?> Customer</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $customer['EMAIL']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $customer['CONTACT_NUM']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $customer['DRIVERS_LICENSE']; ?></td>
                                    <td class="px-6 py-4 text-sm flex space-x-2">
                                        <a href="customer_detail.php?id=<?php echo $customer['CUSTOMER_ID']; ?>" 
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 hover:bg-blue-200 transition-colors" 
                                           title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No self-drive customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Showing <?php echo count($selfDriveCustomers); ?> individual customers
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Previous</button>
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next</button>
                </div>
            </div>
        </div>

        <!-- With Driver Customers Table -->
        <div id="with-driver-content" class="glass-effect rounded-xl shadow-lg overflow-hidden hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Company Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Address</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Assigned Driver</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($withDriverCustomers) > 0): ?>
                            <?php foreach ($withDriverCustomers as $customer): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($customer['FIRST_NAME'] . '+' . $customer['LAST_NAME']); ?>" alt="">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $customer['FIRST_NAME'] . ' ' . $customer['LAST_NAME']; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo ucfirst($customer['CUSTOMER_TYPE']); ?> Customer</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $customer['EMAIL']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $customer['CONTACT_NUM']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $customer['CUSTOMER_ADDRESS']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php if (!empty($customer['DRIVER_NAME'])): ?>
                                            <span class="text-green-600"><?php echo $customer['DRIVER_NAME']; ?></span>
                                        <?php else: ?>
                                            <span class="text-yellow-600">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="customer_detail.php?id=<?php echo $customer['CUSTOMER_ID']; ?>" 
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 hover:bg-blue-200 transition-colors" 
                                           title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No customers with drivers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Showing <?php echo count($withDriverCustomers); ?> company customers
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Previous</button>
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next</button>
                </div>
            </div>
        </div>

        <!-- Tab Switching Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selfDriveTab = document.getElementById('self-drive-tab');
                const withDriverTab = document.getElementById('with-driver-tab');
                const selfDriveContent = document.getElementById('self-drive-content');
                const withDriverContent = document.getElementById('with-driver-content');

                selfDriveTab.addEventListener('click', function() {
                    // Update tab styles
                    selfDriveTab.classList.add('border-blue-500', 'text-blue-600');
                    selfDriveTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    withDriverTab.classList.remove('border-blue-500', 'text-blue-600');
                    withDriverTab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    
                    // Show/hide content
                    selfDriveContent.classList.remove('hidden');
                    withDriverContent.classList.add('hidden');
                });

                withDriverTab.addEventListener('click', function() {
                    // Update tab styles
                    withDriverTab.classList.add('border-blue-500', 'text-blue-600');
                    withDriverTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    selfDriveTab.classList.remove('border-blue-500', 'text-blue-600');
                    selfDriveTab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    
                    // Show/hide content
                    withDriverContent.classList.remove('hidden');
                    selfDriveContent.classList.add('hidden');
                });
            });
        </script>
    </div>
</body>
</html>

