<?php
session_start();
require '../connect.php';

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_driver'])) {
    $staff_id = 1; // You might want to get this from the session
    $driver_name = $_POST['driver_name'];
    $license_number = $_POST['license_number'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $status = $_POST['status']; 
    $image_path = 'driver_images/default.jpg'; // Default image
    
    // Handle image upload
    if(isset($_FILES['driver_image']) && $_FILES['driver_image']['error'] == 0) {
        $upload_dir = '../driver_images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['driver_image']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if(move_uploaded_file($_FILES['driver_image']['tmp_name'], $upload_path)) {
            // Save file path to database (relative path)
            $image_path = 'driver_images/' . $unique_filename;
        } else {
            $message = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Error uploading image!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
                </script>";
        }
    }
    
    // Using the stored procedure to add driver
    $sql = "CALL sp_AddDriver(?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Updated parameter count
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Fix: The type string 'isssssss' has 8 parameters but we're passing 9 parameters
    // Add an extra 's' for the image parameter
    $stmt->bind_param("issssssss", $staff_id, $driver_name, $license_number, 
                    $contact_number, $address, $birthdate, $gender, $status, $image_path); 
    
    if ($stmt->execute()) {
        $message = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Driver has been added successfully!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    }).then(function() {
                        // Redirect to the same page but with driver-list tab active
                        window.location = 'manage_drivers.php?tab=driver-list';
                    });
                });
            </script>";
    } else {
        $message = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error adding driver: " . $stmt->error . "',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                });
            </script>";
    }
    $stmt->close();
}

// Query to display drivers - Update to fetch required fields including image
// Replace the simple query with pagination and search support
$items_per_page = isset($_GET['entries']) ? intval($_GET['entries']) : 5; // Default 5, but can be changed by user
if ($items_per_page <= 0) $items_per_page = 5; // Safety check
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

// Search functionality
$search_condition = "";
$search_params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $search_condition = " WHERE 
        DRIVER_NAME LIKE ? OR 
        LICENSE_NUMBER LIKE ? OR 
        CONTACT_NUMBER LIKE ? OR
        STATUS LIKE ?";
    $search_param = "%{$search}%";
    $search_params = array_fill(0, 4, $search_param);
}

// Get total number of records for pagination with search
$count_query = "SELECT COUNT(*) as total FROM driver" . $search_condition;
$stmt = $conn->prepare($count_query);

if (!empty($search_params)) {
    $stmt->bind_param(str_repeat('s', count($search_params)), ...$search_params);
}

$stmt->execute();
$count_result = $stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Adjust page if it's out of range
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $items_per_page;
}

// Get drivers with pagination and search
$query = "SELECT DRIVER_ID, DRIVER_NAME, LICENSE_NUMBER, CONTACT_NUMBER, STATUS, IMAGE FROM driver" . $search_condition . " LIMIT ?, ?";
$stmt = $conn->prepare($query);

if (!empty($search_params)) {
    $params = $search_params;
    $params[] = $offset;
    $params[] = $items_per_page;
    $stmt->bind_param(str_repeat('s', count($search_params)) . 'ii', ...$params);
} else {
    $stmt->bind_param('ii', $offset, $items_per_page);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
        
        /* Driver Image Upload Preview */
        #imagePreview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            border: 4px solid #e5e7eb;
            margin: 0 auto 16px auto;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        #imagePreview:hover {
            border-color: #3b82f6;
        }
        
        #imagePreview svg {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        #imagePreview:hover svg {
            opacity: 1;
        }
        
        #imagePreviewOverlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        #imagePreview:hover #imagePreviewOverlay {
            opacity: 1;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
    <?php 
    // Output the message right after body tag
    if (!empty($message)) {
        echo $message;
    }
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2v1" /></svg>
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
        <!-- Page Title -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Manage Drivers</h2>
            <p class="text-gray-600">Add, update, and manage driver information</p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg text-blue-600 border-blue-600 active" 
                            id="add-driver-tab" 
                            data-tabs-target="#add-driver" 
                            type="button" 
                            role="tab" 
                            aria-controls="add-driver" 
                            aria-selected="true">
                        Add Driver
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" 
                            id="driver-list-tab" 
                            data-tabs-target="#driver-list" 
                            type="button" 
                            role="tab" 
                            aria-controls="driver-list" 
                            aria-selected="false">
                        Driver List
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="tab-content">
            <!-- Add Driver Tab -->
            <div class="block" id="add-driver" role="tabpanel" aria-labelledby="add-driver-tab">
                <!-- Driver Information Card -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Driver Information</h3>
                        <p class="text-sm text-gray-500 mt-1">Enter the driver details below</p>
                    </div>
                    
                    <form class="grid grid-cols-1 md:grid-cols-2 gap-8" method="POST" enctype="multipart/form-data">
                        <!-- Left Column - Personal Details -->
                        <div class="space-y-6">
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
                                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Personal Information
                                </h4>
                                
                                <!-- Driver Photo Upload -->
                                <div class="mt-4 text-center">
                                    <input type="file" name="driver_image" id="imageInput" accept="image/*" class="hidden">
                                    <div id="imagePreview" onclick="document.getElementById('imageInput').click();" class="bg-gray-100">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <div id="imagePreviewOverlay">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Click to upload driver photo</p>
                                </div>
                                
                                <!-- Driver Name -->
                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Driver Name</label>
                                    <input type="text" 
                                        name="driver_name" 
                                        required 
                                        placeholder="Full Name" 
                                        pattern="[A-Za-z\s]+"
                                        title="Please enter only letters and spaces"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Contact Number -->
                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Contact Number</label>
                                    <input type="tel" 
                                        name="contact_number" 
                                        required 
                                        placeholder="e.g. 09123456789" 
                                        pattern="[0-9]+"
                                        maxlength="11"
                                        title="Please enter only numbers"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Gender -->
                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Gender</label>
                                    <select name="gender" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled selected>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - License & Additional Info -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg space-y-4">
                                <h4 class="font-medium text-gray-700">Additional Information</h4>
                                
                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">License Number</label>
                                    <input type="text" name="license_number" required placeholder="e.g. DL123456789" 
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Birthdate</label>
                                    <input type="date" name="birthdate" required id="birthdate" max="<?= date('Y-m-d') ?>"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Address</label>
                                    <textarea name="address" required rows="3" placeholder="Enter full address"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>

                                <div class="relative">
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Status</label>
                                    <select name="status" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="Available">Available</option>
                                        <option value="On Duty">On Duty</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" name="add_driver"
                                        class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                                    Add Driver
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Driver List Tab -->
            <div class="hidden" id="driver-list" role="tabpanel" aria-labelledby="driver-list-tab">
                <!-- Driver List Card -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-xl font-semibold">Driver List</h3>
                        </div>
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input type="text" id="driverSearch" placeholder="Search drivers..." 
                                    class="px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drivers list table -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <!-- Table Header -->
                        <div class="grid grid-cols-5 gap-4 p-4 bg-gray-50 border-b border-gray-200 font-medium text-gray-700">
                            <div>Driver Name</div>
                            <div>License Number</div>
                            <div>Contact</div>
                            <div>Status</div>
                            <div>Actions</div>
                        </div>

                        <!-- Driver Entries from Database -->
                        <div class="divide-y divide-gray-200">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <div class="grid grid-cols-5 gap-4 p-4 items-center hover:bg-gray-50 transition-colors driver-row">
                                        <div class="flex items-center space-x-3">
                                            <?php if(!empty($row['IMAGE']) && file_exists('../'.$row['IMAGE'])): ?>
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                    src="../<?= htmlspecialchars($row['IMAGE']) ?>" 
                                                    alt="Driver photo">
                                            <?php else: ?>
                                                <img class="h-10 w-10 rounded-full" 
                                                    src="https://ui-avatars.com/api/?name=<?= urlencode($row['DRIVER_NAME']) ?>&background=random" 
                                                    alt="Driver photo">
                                            <?php endif; ?>
                                            <span class="font-medium"><?= htmlspecialchars($row['DRIVER_NAME']) ?></span>
                                        </div>
                                        <div><?= htmlspecialchars($row['LICENSE_NUMBER']) ?></div>
                                        <div><?= htmlspecialchars($row['CONTACT_NUMBER']) ?></div>
                                        <div>
                                            <span class="px-2 py-1 rounded-full text-sm 
                                                <?= $row['STATUS'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= htmlspecialchars($row['STATUS']) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="view_driver.php?id=<?= $row['DRIVER_ID'] ?>" 
                                               class="p-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors"
                                               title="View Details">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="edit_driver.php?id=<?= $row['DRIVER_ID'] ?>" 
                                               class="p-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors"
                                               title="Edit Driver">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button onclick="deleteDriver(<?= $row['DRIVER_ID'] ?>)" 
                                                    class="p-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors"
                                                    title="Delete Driver">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-8 text-center text-gray-500">
                                    <?= isset($_GET['search']) ? "No drivers found matching '" . htmlspecialchars($_GET['search']) . "'" : "No drivers found" ?>
                                    <div class="mt-4">
                                        <a href="?tab=add-driver" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Add a driver
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination Controls with Entry Count Display -->
                        <?php if ($total_items > 0): ?>
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row items-center justify-between">
                                <!-- Entry Information -->
                                <div class="mb-4 sm:mb-0">
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?= $total_items > 0 ? $offset + 1 : 0 ?></span> to 
                                        <span class="font-medium"><?= min($offset + $items_per_page, $total_items) ?></span> of 
                                        <span class="font-medium"><?= $total_items ?></span> drivers
                                        <?= isset($_GET['search']) ? " matching '" . htmlspecialchars($_GET['search']) . "'" : "" ?>
                                    </p>
                                </div>
                                
                                <!-- Pagination Buttons -->
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <!-- Previous Page Link -->
                                        <?php 
                                        $search_param = isset($_GET['search']) ? "&search=" . urlencode($_GET['search']) : "";
                                        $entries_param = "&entries=" . $items_per_page;
                                        ?>
                                        <?php if ($page > 1): ?>
                                        <a href="?tab=driver-list&page=<?= $page - 1 ?><?= $search_param ?><?= $entries_param ?>" 
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
                                        
                                        <!-- Page Numbers -->
                                        <?php 
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $start_page + 4);
                                        if ($end_page - $start_page < 4 && $start_page > 1) {
                                            $start_page = max(1, $end_page - 4);
                                        }
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                            $is_current = $i == $page;
                                        ?>
                                        <a href="?tab=driver-list&page=<?= $i ?><?= $search_param ?><?= $entries_param ?>" 
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 <?= $is_current ? 'bg-blue-50 text-blue-600 z-10' : 'bg-white text-gray-500 hover:bg-gray-50' ?> text-sm font-medium">
                                            <?= $i ?>
                                        </a>
                                        <?php endfor; ?>
                                        
                                        <!-- Next Page Link -->
                                        <?php if ($page < $total_pages): ?>
                                        <a href="?tab=driver-list&page=<?= $page + 1 ?><?= $search_param ?><?= $entries_param ?>" 
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
            </div>
        </div>
    </div>

    <!-- Add Tab Functionality Script -->
    <script>
    // Tab functionality
    const tabButtons = document.querySelectorAll('[role="tab"]');
    const tabPanels = document.querySelectorAll('[role="tabpanel"]');

    document.addEventListener('DOMContentLoaded', function() {
        // Check URL parameters for tab
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam) {
            // Find the tab button for this tab
            const tabButton = document.querySelector(`[data-tabs-target="#${tabParam}"]`);
            if (tabButton) {
                // Trigger a click on this tab button
                tabButton.click();
            }
        }
        
        // Also check localStorage (used when returning from view_driver.php)
        const storedTab = localStorage.getItem('activeTab');
        if (storedTab && !tabParam) {
            const tabButton = document.querySelector(`[data-tabs-target="#${storedTab}"]`);
            if (tabButton) {
                tabButton.click();
                // Clear the storage after use
                localStorage.removeItem('activeTab');
            }
        }

        // Update URL when changing tabs to preserve search and pagination
        tabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-tabs-target').substring(1);
                const currentUrl = new URL(window.location.href);
                
                // Preserve search parameter if it exists
                const search = currentUrl.searchParams.get('search');
                
                // Update URL with new tab and existing search
                currentUrl.searchParams.set('tab', target);
                
                // Remove page parameter when switching tabs unless going to driver-list
                if (target !== 'driver-list') {
                    currentUrl.searchParams.delete('page');
                    currentUrl.searchParams.delete('search');
                } else if (search) {
                    currentUrl.searchParams.set('search', search);
                }
                
                history.pushState({}, '', currentUrl.toString());
            });
        });
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Deactivate all tabs
            tabButtons.forEach(btn => {
                btn.classList.remove('text-blue-600', 'border-blue-600');
                btn.classList.add('border-transparent');
                btn.setAttribute('aria-selected', 'false');
            });

            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.add('hidden');
            });

            // Activate clicked tab
            button.classList.add('text-blue-600', 'border-blue-600');
            button.classList.remove('border-transparent');
            button.setAttribute('aria-selected', 'true');

            // Show corresponding panel
            const panelId = button.getAttribute('data-tabs-target').substring(1);
            document.getElementById(panelId).classList.remove('hidden');
        });
    });

    // Image preview functionality
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewOverlay = document.getElementById('imagePreviewOverlay');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Update the background image of the preview element
                imagePreview.style.backgroundImage = `url(${e.target.result})`;
                // Remove the placeholder icon and show the camera icon on hover
                imagePreview.innerHTML = '';
                imagePreview.appendChild(imagePreviewOverlay);
                // Add a class to indicate an image is selected
                imagePreview.classList.add('has-image');
                imagePreview.classList.remove('bg-gray-100');
            };
            
            reader.readAsDataURL(file);
        }
    });

    function deleteDriver(driverId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('delete_driver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'driver_id=' + driverId
                })
                .then(response => response.text())
                .then(result => {
                    if(result === 'success') {
                        Swal.fire(
                            'Deleted!',
                            'Driver has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to delete driver.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const driverNameInput = form.querySelector('input[name="driver_name"]');
        const contactNumberInput = form.querySelector('input[name="contact_number"]');
        const birthdateInput = document.getElementById('birthdate');

        function preventSpecialCharsAndNumbers(e) {
            if (!/^[A-Za-z\s]$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Only letters and spaces are allowed',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }

        function preventNonNumbers(e) {
            if (!/^[0-9]$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Only numbers are allowed for contact number',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }

        // Add event listeners for keypress
        driverNameInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
        contactNumberInput.addEventListener('keypress', preventNonNumbers);

        // Set max date to today to prevent future dates in the date picker
        const today = new Date().toISOString().split('T')[0];
        birthdateInput.setAttribute('max', today);
        
        // Add additional validation for browsers that don't support the 'max' attribute
        birthdateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const currentDate = new Date();
            
            if (selectedDate > currentDate) {
                this.value = today;
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Birthdate cannot be in the future',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            
            // Also check if the person is at least 18 years old
            const minAge = 18;
            const minAgeDate = new Date();
            minAgeDate.setFullYear(minAgeDate.getFullYear() - minAge);
            
            if (selectedDate > minAgeDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Age Verification',
                    text: 'Driver must be at least 18 years old',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });

        // Form validation before submit
        form.addEventListener('submit', function(e) {
            const driverNameValue = driverNameInput.value;
            const contactNumberValue = contactNumberInput.value;
            const birthdateValue = birthdateInput.value;

            const namePattern = /^[A-Za-z\s]+$/;
            const contactPattern = /^[0-9]+$/;

            if (!namePattern.test(driverNameValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Driver name can only contain letters and spaces'
                });
                return;
            }

            if (!contactPattern.test(contactNumberValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Contact number can only contain numbers'
                });
                return;
            }

            if (contactNumberValue.length !== 11) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Contact number must be exactly 11 digits'
                });
                return;
            }
            
            // Validate birthdate
            const selectedDate = new Date(birthdateValue);
            const currentDate = new Date();
            
            if (selectedDate > currentDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Birthdate cannot be in the future'
                });
                return;
            }
            
            // Check if the person is at least 18 years old
            const minAge = 18;
            const minAgeDate = new Date();
            minAgeDate.setFullYear(minAgeDate.getFullYear() - minAge);
            
            if (selectedDate > minAgeDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Age Requirement',
                    text: 'Driver must be at least 18 years old to be registered'
                });
                return;
            }
        });
    });

    // Entries per page selector functionality
    document.addEventListener('DOMContentLoaded', function() {
        const entriesPerPageSelect = document.getElementById('entriesPerPage');
        if (entriesPerPageSelect) {
            entriesPerPageSelect.addEventListener('change', function() {
                const entriesValue = this.value;
                
                // Create URL with the new entries parameter
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('tab', 'driver-list');
                currentUrl.searchParams.set('entries', entriesValue);
                
                // Reset to page 1 when changing entries per page
                currentUrl.searchParams.set('page', 1);
                
                // Keep search parameter if it exists
                if (!currentUrl.searchParams.has('search') && document.getElementById('driverSearch').value) {
                    currentUrl.searchParams.set('search', document.getElementById('driverSearch').value);
                }
                
                // Navigate to the new URL
                window.location.href = currentUrl.toString();
            });
        }
    });

    // Real-time search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const driverSearch = document.getElementById('driverSearch');
        
        if (driverSearch) {
            driverSearch.addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const driverRows = document.querySelectorAll('.driver-row, .grid.grid-cols-5.gap-4.p-4.items-center');
                let hasResults = false;
                
                driverRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    if (rowText.includes(searchText)) {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show "no results" message if no matches
                const noResultsMessage = document.querySelector('.p-8.text-center.text-gray-500');
                if (noResultsMessage) {
                    if (hasResults || searchText === '') {
                        noResultsMessage.style.display = 'none';
                    } else {
                        noResultsMessage.style.display = 'block';
                        noResultsMessage.innerHTML = `
                            No drivers found matching '${searchText}'
                            <div class="mt-4">
                                <a href="?tab=add-driver" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add a driver
                                </a>
                            </div>
                        `;
                    }
                }
                
                // Hide pagination controls during search
                const paginationControls = document.querySelector('.px-4.py-3.bg-gray-50.border-t.border-gray-200');
                if (paginationControls) {
                    paginationControls.style.display = searchText ? 'none' : '';
                }
            });
        }
    });
</script>
</body>
</html>