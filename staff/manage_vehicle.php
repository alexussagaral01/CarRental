<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "vehicle_rental");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = 1; // You may want to get this from session
    $vehicle_type = $_POST['VEHICLE_TYPE'];
    $vehicle_brand = $_POST['VEHICLE_BRAND'];
    $model = $_POST['MODEL'];
    $year = $_POST['YEAR'];
    $color = $_POST['COLOR'];
    $license_plate = $_POST['LICENSE_PLATE'];
    $vehicle_description = $_POST['VEHICLE_DESCRIPTION'];
    $capacity = $_POST['CAPACITY'];
    $transmission = $_POST['TRANSMISSION'];
    $status = 'available'; // Override with default value regardless of form input
    $amount = $_POST['AMOUNT'];

    // Handle image upload
    if(isset($_FILES['IMAGES']) && $_FILES['IMAGES']['error'][0] == 0) {
        $upload_dir = '../VEHICLE_IMAGES/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['IMAGES']['name'][0], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if(move_uploaded_file($_FILES['IMAGES']['tmp_name'][0], $upload_path)) {
            // Save file path to database
            $image_path = 'VEHICLE_IMAGES/' . $unique_filename;
            
            // Check if license plate already exists
            $check_plate = $conn->prepare("SELECT COUNT(*) as count FROM vehicle WHERE LICENSE_PLATE = ?");
            $check_plate->bind_param("s", $license_plate);
            $check_plate->execute();
            $result = $check_plate->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $message = "
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'License plate number already exists!',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        });
                    </script>";
            } else {
                // Call stored procedure to add vehicle
                $stmt = $conn->prepare("CALL sp_AddVehicle(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssisssssssd", 
                    $staff_id,           // INT
                    $vehicle_type,       // VARCHAR
                    $vehicle_brand,      // VARCHAR
                    $model,             // VARCHAR
                    $year,              // INT
                    $color,             // VARCHAR
                    $license_plate,     // VARCHAR
                    $vehicle_description, // VARCHAR
                    $image_path,        // VARCHAR (file path)
                    $capacity,          // VARCHAR
                    $transmission,      // VARCHAR
                    $status,            // VARCHAR
                    $amount            // DECIMAL
                );

                if ($stmt->execute()) {
                    $message = "
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Vehicle has been added successfully!',
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
                                    // Redirect to the same page with a fragment identifier for the vehicle list tab
                                    window.location = 'manage_vehicle.php?tab=vehicle-list';
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
                                    text: 'Error adding vehicle: " . $stmt->error . "',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            });
                        </script>";
                }
                $stmt->close();
            }
            $check_plate->close();
        } else {
            $message = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Error uploading image!',
                        });
                    });
                </script>";
        }
    } else {
        $message = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please select an image!',
                    });
                });
            </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/manage_vehicle.css">
    <style>
        /* Year dropdown styles */
        .year-dropdown {
            position: relative;
        }
        
        .year-dropdown-button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 1rem;
            border: 1px solid #e5e7eb;
            background-color: white;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .year-dropdown-button:hover {
            border-color: #93c5fd;
        }
        
        .year-dropdown-button:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        
        .year-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 20;
            margin-top: 0.25rem;
            max-height: 300px;
            overflow-y: auto;
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: none;
            padding: 12px;
        }
        
        .year-dropdown-list.active {
            display: block;
        }
        
        .year-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        
        .year-option {
            padding: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 4px;
        }
        
        .year-option:hover {
            background-color: #f3f4f6;
            color: #3b82f6;
        }
        
        .year-option.selected {
            background-color: #eff6ff;
            color: #3b82f6;
            font-weight: 500;
        }
        
        /* Category header */
        .year-category {
            grid-column: 1 / -1;
            font-weight: 500;
            color: #6b7280;
            font-size: 0.9rem;
            padding: 4px 6px;
            border-bottom: 1px solid #e5e7eb;
            margin: 8px 0 4px 0;
        }
        
        .year-category:first-child {
            margin-top: 0;
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 012-2h2a2 2 0 012 2v3M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2v3M9 5h6"/></svg>
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
            <h2 class="text-3xl font-bold text-gray-800">Manage Vehicles</h2>
            <p class="text-gray-600">Add, update, and manage vehicle fleet</p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg text-blue-600 border-blue-600 active" 
                            id="add-vehicle-tab" 
                            data-tabs-target="#add-vehicle" 
                            type="button" 
                            role="tab" 
                            aria-controls="add-vehicle" 
                            aria-selected="true">
                        Add Vehicle
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" 
                            id="vehicle-list-tab" 
                            data-tabs-target="#vehicle-list" 
                            type="button" 
                            role="tab" 
                            aria-controls="vehicle-list" 
                            aria-selected="false">
                        Vehicle List
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="tab-content">
            <!-- Add Vehicle Tab -->
            <div class="block" id="add-vehicle" role="tabpanel" aria-labelledby="add-vehicle-tab">
                <!-- Add Vehicle Card - Your existing add vehicle form goes here -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Vehicle
                    </h3>

                    <form class="grid grid-cols-1 md:grid-cols-2 gap-8" enctype="multipart/form-data" id="addVehicleForm" method="POST">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-xl space-y-6">
                                <h4 class="font-medium text-gray-700">Basic Information</h4>
                                
                                <!-- Type and Brand Row -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Vehicle Type</label>
                                        <select name="VEHICLE_TYPE" id="vehicleTypeSelect" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="SUV">SUV</option>
                                            <option value="HATCHBACK">HATCHBACK</option>
                                            <option value="SEDAN">SEDAN</option>
                                            <option value="MPV">MPV</option>
                                            <option value="VAN">VAN</option>
                                            <option value="MINIBUS">MINIBUS</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Brand</label>
                                        <select name="VEHICLE_BRAND" id="brandSelect" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="" disabled selected>Select Type First</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Model and Year Row -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Model</label>
                                        <select name="MODEL" id="modelSelect" required 
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="" disabled selected>Select Brand First</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Year</label>
                                        <div class="year-dropdown">
                                            <input type="hidden" name="YEAR" id="yearField" value="" required>
                                            <button type="button" id="yearDropdownButton" class="year-dropdown-button">
                                                <span id="yearDisplay">Select Year</span>
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                            <div id="yearDropdownList" class="year-dropdown-list">
                                                <div id="yearOptions" class="year-grid">
                                                    <!-- Years will be inserted here dynamically, grouped by decades -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features Section -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-6">
                                <h4 class="font-medium text-gray-700">Features & Details</h4>
                                
                                <!-- Color and Plate Row -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Color</label>
                                        <select name="COLOR" required class="hidden">
                                            <option value="" disabled selected>Select Color</option>
                                            <option value="Black" data-color="#000000">Black</option>
                                            <option value="White" data-color="#FFFFFF">White</option>
                                            <option value="Silver" data-color="#C0C0C0">Silver</option>
                                            <option value="Gray" data-color="#808080">Gray</option>
                                            <option value="Red" data-color="#FF0000">Red</option>
                                            <option value="Blue" data-color="#0000FF">Blue</option>
                                            <option value="Dark Blue" data-color="#00008B">Dark Blue</option>
                                            <option value="Sky Blue" data-color="#87CEEB">Sky Blue</option>
                                            <option value="Navy Blue" data-color="#000080">Navy Blue</option>
                                            <option value="Green" data-color="#008000">Green</option>
                                            <option value="Dark Green" data-color="#006400">Dark Green</option>
                                            <option value="Yellow" data-color="#FFFF00">Yellow</option>
                                            <option value="Orange" data-color="#FFA500">Orange</option>
                                            <option value="Brown" data-color="#A52A2A">Brown</option>
                                            <option value="Beige" data-color="#F5F5DC">Beige</option>
                                            <option value="Purple" data-color="#800080">Purple</option>
                                            <option value="Pink" data-color="#FFC0CB">Pink</option>
                                            <option value="Gold" data-color="#FFD700">Gold</option>
                                            <option value="Burgundy" data-color="#800020">Burgundy</option>
                                            <option value="Maroon" data-color="#800000">Maroon</option>
                                            <option value="Teal" data-color="#008080">Teal</option>
                                            <option value="Olive" data-color="#808000">Olive</option>
                                            <option value="Champagne" data-color="#F7E7CE">Champagne</option>
                                            <option value="Bronze" data-color="#CD7F32">Bronze</option>
                                        </select>
                                        <div id="colorPickerContainer" class="relative">
                                            <div id="colorDisplay" class="selected-color-display">
                                                <div id="selectedColorSwatch" class="color-swatch"></div>
                                                <span id="selectedColorText" class="flex-1">Select Color</span>
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                            <div id="colorPalette" class="absolute z-30 w-[320px] mt-2 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden hidden">
                                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                                                    <h4 class="text-sm font-medium text-gray-700">Select a car color</h4>
                                                    <button id="closeColorPalette" type="button" class="text-gray-400 hover:text-gray-500">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div id="colorGrid" class="color-grid"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">License Plate</label>
                                        <input type="text" name="LICENSE_PLATE" required placeholder="e.g. ABC-1234" 
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                <!-- Transmission and Capacity Row -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Transmission</label>
                                        <select name="TRANSMISSION" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="Automatic">Automatic</option>
                                            <option value="Manual">Manual</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Capacity</label>
                                        <select name="CAPACITY" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="" disabled selected>Select Capacity</option>
                                            <option value="4-5">4-5 Person</option>
                                            <option value="7-8">7-8 Person</option>
                                            <option value="10-18">10-18 Person</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Image Upload Section -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                                <h4 class="font-medium text-gray-700 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Vehicle Images
                                </h4>
                                <div id="dropZone" class="relative border-2 border-dashed border-gray-300 rounded-xl transition-all duration-300 ease-in-out hover:border-blue-400">
                                    <input type="file" name="IMAGES[]" accept="image/*" class="hidden" id="imageInput">
                                    <!-- Upload Interface -->
                                    <div id="uploadInterface" class="p-8 text-center">
                                        <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-blue-50 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <p class="text-base text-gray-600 font-medium">Drop your images here</p>
                                        <p class="text-sm text-gray-400 mt-2">or click to browse from your computer</p>
                                        <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                    <!-- Preview Container -->
                                    <div id="imagePreviewContainer" class="p-4 grid grid-cols-1 gap-4"></div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                                <h4 class="font-medium text-gray-700">Description</h4>
                                <textarea name="VEHICLE_DESCRIPTION" required rows="4" placeholder="Enter Vehicle Description" 
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>

                            <!-- Remove the Status Section completely and use a hidden input -->
                            <input type="hidden" name="STATUS" value="available">

                            <!-- Amount Section -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                                <h4 class="font-medium text-gray-700">Rental Amount</h4>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">₱</span>
                                    </div>
                                    <input type="number" 
                                           name="AMOUNT" 
                                           required 
                                           step="0.01"
                                           placeholder="0.00"
                                           class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors">
                                Add Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Vehicle List Tab -->
            <div class="hidden" id="vehicle-list" role="tabpanel" aria-labelledby="vehicle-list-tab">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold">Vehicle List</h3>
                        <div class="flex space-x-2">
                            <form action="" method="GET" class="search-form">
                                <input type="hidden" name="tab" value="vehicle-list">
                                <div class="relative">
                                    <input type="text" name="search" id="vehicleSearch" placeholder="Search vehicles..." 
                                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                        class="px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="?tab=vehicle-list" class="flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Vehicles list table -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <!-- Table Header -->
                        <div class="grid grid-cols-7 gap-4 p-4 bg-gray-50 border-b border-gray-200">
                            <div class="font-semibold text-gray-600">Vehicle</div>
                            <div class="font-semibold text-gray-600">Model</div>
                            <div class="font-semibold text-gray-600">Year</div>
                            <div class="font-semibold text-gray-600">License Plate</div>
                            <div class="font-semibold text-gray-600">Capacity</div>
                            <div class="font-semibold text-gray-600">Status</div>
                            <div class="font-semibold text-gray-600">Actions</div>
                        </div>

                        <!-- Vehicle Entries from Database -->
                        <div class="divide-y divide-gray-200" id="vehicleListContainer">
                            <?php
                            // Pagination settings
                            $items_per_page = 5;
                            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                            if ($page < 1) $page = 1;
                            $offset = ($page - 1) * $items_per_page;
                            
                            // Search functionality
                            $search_condition = "";
                            $search_params = [];
                            
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $search = $_GET['search'];
                                $search_condition = " WHERE 
                                    VEHICLE_BRAND LIKE ? OR 
                                    MODEL LIKE ? OR 
                                    VEHICLE_TYPE LIKE ? OR 
                                    LICENSE_PLATE LIKE ? OR 
                                    COLOR LIKE ? OR
                                    YEAR LIKE ? OR
                                    STATUS LIKE ?";
                                $search_param = "%{$search}%";
                                $search_params = array_fill(0, 7, $search_param);
                            }
                            
                            // Get total number of records for pagination with search
                            $count_query = "SELECT COUNT(*) as total FROM vehicle" . $search_condition;
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
                            
                            // Get vehicles with pagination and search
                            $query = "SELECT * FROM vehicle" . $search_condition . " LIMIT ?, ?";
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

                            if ($result && mysqli_num_rows($result) > 0):
                                while ($row = $result->fetch_assoc()): 
                                    $statusClass = match(strtolower($row['STATUS'])) {
                                        'available' => 'bg-green-100 text-green-800',
                                        'rented' => 'bg-blue-100 text-blue-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                            ?>
                                <div class="grid grid-cols-7 gap-4 p-4 items-center hover:bg-gray-50">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M8 17h8M8 17v-4m8 4v-4m-8 4h8m-8-4h8M4 11l2-6h12l2 6M4 11h16M4 11v6h16v-6"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($row['VEHICLE_BRAND']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($row['VEHICLE_TYPE']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($row['MODEL']) ?></div>
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($row['YEAR']) ?></div>
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars($row['LICENSE_PLATE']) ?></div>
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars($row['CAPACITY']) ?> persons</div>
                                    <div>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                            <?= htmlspecialchars(ucfirst($row['STATUS'])) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="view_vehicle.php?id=<?= $row['VEHICLE_ID'] ?>" 
                                           class="p-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors"
                                           title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="edit_vehicle.php?id=<?= $row['VEHICLE_ID'] ?>" 
                                           class="p-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors"
                                           title="Edit Vehicle">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <div class="p-4 text-center text-gray-500">
                                    <?= isset($_GET['search']) ? "No vehicles found matching '" . htmlspecialchars($_GET['search']) . "'" : "No vehicles found" ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination Controls -->
                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?php echo min(($page - 1) * $items_per_page + 1, $total_items); ?></span> 
                                        to <span class="font-medium"><?php echo min($page * $items_per_page, $total_items); ?></span> 
                                        of <span class="font-medium"><?php echo $total_items; ?></span> vehicles
                                        <?php echo isset($_GET['search']) ? " matching '" . htmlspecialchars($_GET['search']) . "'" : ""; ?>
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <!-- Previous Page Button -->
                                        <?php if ($page > 1): ?>
                                            <a href="?tab=vehicle-list&page=<?php echo ($page - 1) . $search_param; ?>" 
                                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                                <span class="sr-only">Previous</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>

                                        <!-- Page Numbers -->
                                        <?php 
                                        $start_page = max(1, min($page - 2, $total_pages - 4));
                                        $end_page = min($total_pages, $start_page + 4);
                                        
                                        if ($start_page > 1): ?>
                                            <a href="?tab=vehicle-list&page=1<?php echo $search_param; ?>" 
                                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                1
                                            </a>
                                            <?php if ($start_page > 2): ?>
                                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                            <?php endif;
                                        endif;

                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                            $is_current = $i == $page;
                                        ?>
                                            <a href="?tab=vehicle-list&page=<?php echo $i . $search_param; ?>" 
                                               class="relative inline-flex items-center px-4 py-2 border <?php echo $is_current ? 'bg-blue-50 border-blue-500 text-blue-600 z-10' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor;

                                        if ($end_page < $total_pages): 
                                            if ($end_page < $total_pages - 1): ?>
                                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                            <?php endif; ?>
                                            <a href="?tab=vehicle-list&page=<?php echo $total_pages . $search_param; ?>" 
                                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                <?php echo $total_pages; ?>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Next Page Button -->
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?tab=vehicle-list&page=<?php echo ($page + 1) . $search_param; ?>" 
                                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Next</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                                <span class="sr-only">Next</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            </div>
                        </div>
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

        // Check URL parameters for tab switching on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Get the URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            // If there's a tab parameter, activate that tab
            if (tabParam) {
                const tabToActivate = document.getElementById(`${tabParam}-tab`);
                if (tabToActivate) {
                    tabToActivate.click();
                }
            }
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

        // Image handling functionality
        const imageInput = document.getElementById('imageInput');
        const dropZone = document.getElementById('dropZone');
        const uploadInterface = document.getElementById('uploadInterface');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');

        function updateUploadInterface() {
            uploadInterface.style.display = imagePreviewContainer.children.length > 0 ? 'none' : 'block';
        }

        function createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'relative bg-white rounded-xl shadow-sm overflow-hidden';
                preview.innerHTML = `
                    <div class="relative aspect-[16/9]">
                        <img src="${e.target.result}" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                    <div class="p-4 flex justify-between items-center border-t border-gray-100">
                        <span class="text-sm font-medium text-gray-700 truncate max-w-[200px]">${file.name}</span>
                        <button type="button" class="p-2 hover:bg-red-50 rounded-full text-red-500 transition-colors" 
                                onclick="this.closest('.relative').remove(); updateUploadInterface();">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                `;
                imagePreviewContainer.appendChild(preview);
                updateUploadInterface();
            };
            reader.readAsDataURL(file);
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    if (file.size <= 10 * 1024 * 1024) { // 10MB limit
                        createImagePreview(file);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'File too large',
                            text: 'Image size should not exceed 10MB',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
            });
        }

        imageInput.addEventListener('change', (e) => handleFiles(e.target.files));

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            handleFiles(e.dataTransfer.files);
        });

        dropZone.addEventListener('click', () => {
            imageInput.click();
        });

        function editVehicle(vehicleId) {
            if(confirm('Do you want to edit this vehicle?')) {
                // Add your edit logic here
                console.log('Editing vehicle:', vehicleId);
            }
        }

        // Add search functionality
        document.getElementById('vehicleSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Add event listeners and validation for form
        document.addEventListener('DOMContentLoaded', function() {
            // Define vehicle brand and model structure - organized by vehicle type
            const vehicleTypeBrandsModels = {
                'SUV': {
                    'GREELY': ['COOLRAY', 'OKAVANGO'],
                    'TOYOTA': ['RAIZE', 'RUSH'],
                    'CHERY': ['TIGGO 2 PRO']
                },
                'HATCHBACK': {
                    'HONDA': ['BRIO'],
                    'MAZDA': ['MAZDA2'],
                    'MITSUBISHI': ['MIRAGE'],
                    'SUZUKI': ['CELERIO', 'SWIFT'],
                    'TOYOTA': ['WIGO']
                },
                'SEDAN': {
                    'HONDA': ['CITY'],
                    'HYUNDAI': ['ACCENT'],
                    'KIA': ['SOLUTO'],
                    'MG': ['MG5'],
                    'TOYOTA': ['VIOS'],
                    'NISSAN': ['ALMERA']
                },
                'MPV': {
                    'BAIC': ['M5OS'],
                    'FOTON': ['GRATOUR'],
                    'HONDA': ['BR-V'],
                    'HYUNDAI': ['STARGAZER'],
                    'KIA': ['CARNIVAL'],
                    'MAXUS': ['G50'],
                    'NISSAN': ['LIVINA'],
                    'SUZUKI': ['ERTIGA'],
                    'TOYOTA': ['AVANZA', 'INNOVA']
                },
                'VAN': {
                    'HYUNDAI': ['H350'],
                    'FORD': ['TRANSIT'],
                    'ISUZU': ['TRAVIZ'],
                    'JMC': ['TRANSPORTER'],
                    'MAXUS': ['V80'],
                    'NISSAN': ['NV350 URVAN'],
                    'TOYOTA': ['HIACE COMMUTER', 'HIACE GL']
                },
                'MINIBUS': {
                    'FOTON': ['TOANO', 'TRAVELLER'],
                    'HYUNDAI': ['COUNTRY'],
                    'KING LONG': ['UNIVAN'],
                    'TOYOTA': ['COASTER']
                }
            };
            
            // Extract just the brands for each vehicle type for the first dropdown
            const vehicleBrands = {};
            for (const type in vehicleTypeBrandsModels) {
                vehicleBrands[type] = Object.keys(vehicleTypeBrandsModels[type]);
            }
            
            const vehicleTypeSelect = document.getElementById('vehicleTypeSelect');
            const brandSelect = document.getElementById('brandSelect');
            const modelSelect = document.getElementById('modelSelect');
            
            let currentVehicleType = '';
            
            // Make sure elements exist before adding event listeners
            if (vehicleTypeSelect && brandSelect && modelSelect) {
                // Update brand options when vehicle type changes
                vehicleTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    currentVehicleType = selectedType;
                    
                    // Clear existing options
                    brandSelect.innerHTML = '';
                    
                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    defaultOption.textContent = 'Select Brand';
                    brandSelect.appendChild(defaultOption);
                    
                    // Also reset model dropdown
                    modelSelect.innerHTML = '';
                    const modelDefaultOption = document.createElement('option');
                    modelDefaultOption.value = '';
                    modelDefaultOption.disabled = true;
                    modelDefaultOption.selected = true;
                    modelDefaultOption.textContent = 'Select Brand First';
                    modelSelect.appendChild(modelDefaultOption);
                    modelSelect.disabled = true;
                    
                    // Add new options based on selected vehicle type
                    if (selectedType && vehicleBrands[selectedType]) {
                        vehicleBrands[selectedType].forEach(brand => {
                            const option = document.createElement('option');
                            option.value = brand;
                            option.textContent = brand;
                            brandSelect.appendChild(option);
                        });
                        
                        // Enable the brand select
                        brandSelect.disabled = false;
                    } else {
                        // If no type is selected, disable the brand select
                        brandSelect.disabled = true;
                    }
                });
                
                // Update model options when brand changes
                brandSelect.addEventListener('change', function() {
                    const selectedBrand = this.value;
                    
                    // Clear existing options
                    modelSelect.innerHTML = '';
                    
                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    defaultOption.textContent = 'Select Model';
                    modelSelect.appendChild(defaultOption);
                    
                    // Add new options based on selected brand
                    if (selectedBrand && currentVehicleType && vehicleTypeBrandsModels[currentVehicleType][selectedBrand]) {
                        vehicleTypeBrandsModels[currentVehicleType][selectedBrand].forEach(model => {
                            const option = document.createElement('option');
                            option.value = model;
                            option.textContent = model;
                            modelSelect.appendChild(option);
                        });
                        
                        // Enable the model select
                        modelSelect.disabled = false;
                    } else {
                        // If no brand is selected, disable the model select
                        modelSelect.disabled = true;
                    }
                });
                
                // Trigger change event if a value is already selected (for page reloads)
                if (vehicleTypeSelect.value) {
                    vehicleTypeSelect.dispatchEvent(new Event('change'));
                    
                    if (brandSelect.value) {
                        brandSelect.dispatchEvent(new Event('change'));
                    }
                }
            }
            
            // Get form and form controls
            const form = document.getElementById('addVehicleForm');
            const amountInput = form.querySelector('input[name="AMOUNT"]');
            const colorSelect = form.querySelector('select[name="COLOR"]');
            const yearField = document.getElementById('yearField'); // Hidden year field
            
            // Initialize year dropdown
            function initYearDropdown() {
                const yearDropdownButton = document.getElementById('yearDropdownButton');
                const yearDropdownList = document.getElementById('yearDropdownList');
                const yearOptions = document.getElementById('yearOptions');
                const yearDisplay = document.getElementById('yearDisplay');
                const yearField = document.getElementById('yearField');
                
                // Generate years from 1990 to current year + 1
                const currentYear = new Date().getFullYear();
                const startYear = 1990;
                
                // Clear existing options
                yearOptions.innerHTML = '';
                
                // Group years by decades for better organization
                function addYearCategory(label) {
                    const category = document.createElement('div');
                    category.className = 'year-category';
                    category.textContent = label;
                    yearOptions.appendChild(category);
                }
                
                // Add current and next year separately at the top
                addYearCategory('Recent Years');
                
                // Current year and next year
                [currentYear + 1, currentYear].forEach(year => {
                    const option = document.createElement('div');
                    option.className = 'year-option';
                    option.textContent = year;
                    option.dataset.value = year;
                    
                    option.addEventListener('click', () => {
                        // Update hidden field and display
                        yearField.value = year;
                        yearDisplay.textContent = year;
                        
                        // Update selected status
                        const allOptions = yearOptions.querySelectorAll('.year-option');
                        allOptions.forEach(opt => opt.classList.remove('selected'));
                        option.classList.add('selected');
                        
                        // Close dropdown
                        yearDropdownList.classList.remove('active');
                        yearDropdownList.classList.add('hidden');
                    });
                    
                    yearOptions.appendChild(option);
                });
                
                // Add 2020s decade separately to ensure all years in current decade are shown
                addYearCategory('2020s');
                
                // Create current decade years individually (2020-2029)
                const currentDecadeStart = Math.floor(currentYear / 10) * 10;
                
                // Start from the year before current year and go down to the decade start
                for (let year = currentYear - 1; year >= currentDecadeStart; year--) {
                    const option = document.createElement('div');
                    option.className = 'year-option';
                    option.textContent = year;
                    option.dataset.value = year;
                    
                    option.addEventListener('click', () => {
                        yearField.value = year;
                        yearDisplay.textContent = year;
                        
                        const allOptions = yearOptions.querySelectorAll('.year-option');
                        allOptions.forEach(opt => opt.classList.remove('selected'));
                        option.classList.add('selected');
                        
                        yearDropdownList.classList.remove('active');
                        yearDropdownList.classList.add('hidden');
                    });
                    
                    yearOptions.appendChild(option);
                }
                
                // Add older decades (2010s, 2000s, 1990s)
                const oldDecades = [2010, 2000, 1990];
                
                oldDecades.forEach(decadeStart => {
                    const decadeEnd = decadeStart + 9;
                    addYearCategory(`${decadeStart}s`);
                    
                    // Add all years in the decade, in reverse order (newest first)
                    for (let year = decadeEnd; year >= decadeStart; year--) {
                        const option = document.createElement('div');
                        option.className = 'year-option';
                        option.textContent = year;
                        option.dataset.value = year;
                        
                        option.addEventListener('click', () => {
                            yearField.value = year;
                            yearDisplay.textContent = year;
                            
                            const allOptions = yearOptions.querySelectorAll('.year-option');
                            allOptions.forEach(opt => opt.classList.remove('selected'));
                            option.classList.add('selected');
                            
                            yearDropdownList.classList.remove('active');
                            yearDropdownList.classList.add('hidden');
                        });
                        
                        yearOptions.appendChild(option);
                    }
                });
                
                // Initially hide the dropdown
                yearDropdownList.classList.add('hidden');
                
                // Toggle dropdown
                yearDropdownButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    yearDropdownList.classList.toggle('active');
                    yearDropdownList.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!yearDropdownButton.contains(e.target) && !yearDropdownList.contains(e.target)) {
                        yearDropdownList.classList.remove('active');
                        yearDropdownList.classList.add('hidden');
                    }
                });
                
                // Do NOT set a default year value, leave it blank until user selects
                yearDisplay.textContent = "Select Year";
                yearField.value = "";
            }
            
            // Initialize year dropdown
            initYearDropdown();

            // Excel-like color picker functionality
            function initColorPicker() {
                const colorDisplay = document.getElementById('colorDisplay');
                const colorPalette = document.getElementById('colorPalette');
                const colorGrid = document.getElementById('colorGrid');
                const selectedColorSwatch = document.getElementById('selectedColorSwatch');
                const selectedColorText = document.getElementById('selectedColorText');
                const closeColorPalette = document.getElementById('closeColorPalette');
                
                // Get all color options from the hidden select
                const colorOptions = [];
                Array.from(colorSelect.options).forEach(option => {
                    if (option.value && option.dataset.color) {
                        colorOptions.push({
                            name: option.value,
                            hex: option.dataset.color
                        });
                    }
                });
                
                // Build color grid
                colorOptions.forEach(color => {
                    const colorCell = document.createElement('div');
                    colorCell.className = 'color-cell';
                    colorCell.style.backgroundColor = color.hex;
                    colorCell.dataset.colorName = color.name;
                    colorCell.dataset.colorHex = color.hex;
                    
                    // Add white border for white color to make it visible
                    if (color.name === 'White') {
                        colorCell.style.border = '1px solid #e5e7eb';
                    }
                    
                    // Create tooltip with color name
                    const colorName = document.createElement('span');
                    colorName.className = 'color-name';
                    colorName.textContent = color.name;
                    colorCell.appendChild(colorName);
                    
                    // Handle color selection
                    colorCell.addEventListener('click', () => {
                        // Update the hidden select value
                        colorSelect.value = color.name;
                        
                        // Update the display
                        selectedColorSwatch.style.backgroundColor = color.hex;
                        if (color.name === 'White') {
                            selectedColorSwatch.style.border = '1px solid #e5e7eb';
                        } else {
                            selectedColorSwatch.style.border = '1px solid rgba(0,0,0,0.1)';
                        }
                        
                        selectedColorText.textContent = color.name;
                        
                        // Visual feedback for selection
                        const allCells = colorGrid.querySelectorAll('.color-cell');
                        allCells.forEach(cell => cell.classList.remove('selected'));
                        colorCell.classList.add('selected');
                        
                        // Add animation effect
                        selectedColorSwatch.classList.add('scale-110');
                        setTimeout(() => {
                            selectedColorSwatch.classList.remove('scale-110');
                        }, 200);
                        
                        // Hide palette with small delay for visual feedback
                        setTimeout(() => {
                            colorPalette.classList.add('hidden');
                        }, 250);
                        
                        // Trigger change event on select
                        const event = new Event('change');
                        colorSelect.dispatchEvent(event);
                    });
                    
                    colorGrid.appendChild(colorCell);
                });
                
                // Toggle color palette visibility
                colorDisplay.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isHidden = colorPalette.classList.contains('hidden');
                    
                    if (isHidden) {
                        // Position the palette correctly
                        const rect = colorDisplay.getBoundingClientRect();
                        if (window.innerHeight - rect.bottom < 300) {
                            // If not enough space below, show above
                            colorPalette.style.bottom = colorDisplay.offsetHeight + 'px';
                            colorPalette.style.top = 'auto';
                        } else {
                            colorPalette.style.top = colorDisplay.offsetHeight + 8 + 'px';
                            colorPalette.style.bottom = 'auto';
                        }
                    }
                    
                    colorPalette.classList.toggle('hidden');
                });
                
                // Close palette when clicking the close button
                closeColorPalette.addEventListener('click', () => {
                    colorPalette.classList.add('hidden');
                });
                
                // Close palette when clicking outside
                document.addEventListener('click', (e) => {
                    if (!colorDisplay.contains(e.target) && !colorPalette.contains(e.target)) {
                        colorPalette.classList.add('hidden');
                    }
                });
                
                // Handle keyboard navigation and accessibility
                colorDisplay.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        colorPalette.classList.toggle('hidden');
                    }
                });
                
                // Make the color picker accessible
                colorDisplay.setAttribute('tabindex', '0');
                colorDisplay.setAttribute('role', 'combobox');
                colorDisplay.setAttribute('aria-expanded', 'false');
                colorDisplay.setAttribute('aria-haspopup', 'listbox');
                
                // Update ARIA states when opening/closing the palette
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'class') {
                            const isHidden = colorPalette.classList.contains('hidden');
                            colorDisplay.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                        }
                    });
                });
                
                observer.observe(colorPalette, { attributes: true });
            }
            
            // Initialize improved color picker
            initColorPicker();

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
                        text: 'Only numbers are allowed for year',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }

            // Add event listeners for keypress
            brandInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            // We're using select elements for brand and model, so no need for keypress event listeners
            // Add amount validation
            amountInput.addEventListener('input', function(e) {
                if (this.value < 0) {
                    this.value = 0;
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Amount cannot be negative',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });

            // Form validation before submit
            // Pattern for text validation
            const pattern = /^[A-Za-z\s]+$/;
            const yearPattern = /^[0-9]{4}$/;
            const currentYear = new Date().getFullYear();
            
            form.addEventListener('submit', function(e) {
                const brandValue = brandSelect.value;
                const modelValue = modelSelect.value;
                const yearValue = document.getElementById('yearField').value;
                const amountValue = parseFloat(amountInput.value);
                const colorValue = colorSelect.value;

                if (!pattern.test(brandValue)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Brand name can only contain letters and spaces'
                    });
                    return;
                }

                if (!pattern.test(modelValue)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Model can only contain letters and spaces'
                    });
                    return;
                }

                if (!colorValue) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please select a color'
                    });
                    return;
                }

                if (!yearValue) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please select a year'
                    });
                    return;
                }

                if (!yearPattern.test(yearValue) || yearValue < 1900 || yearValue > currentYear + 1) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Year',
                        text: `Year must be between 1900 and ${currentYear + 1}`
                    });
                    return;
                }

                if (isNaN(amountValue) || amountValue <= 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Please enter a valid amount greater than 0'
                    });
                    return;
                }
            });

            // Skip redefining variables and directly update the event listener
            // These variables are already defined earlier in the code
            
            // Update brand options when vehicle type changes
            vehicleTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                
                // Clear existing options except the first one
                brandSelect.innerHTML = '<option value="" disabled selected>Select Brand</option>';
                
                // Add new options based on selected vehicle type
                if (selectedType && vehicleBrands[selectedType]) {
                    vehicleBrands[selectedType].forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand;
                        option.textContent = brand;
                        brandSelect.appendChild(option);
                    });
                    
                    // Enable the brand select
                    brandSelect.disabled = false;
                } else {
                    // If no type is selected, disable the brand select
                    brandSelect.disabled = true;
                }
            });
        });

        // Update search functionality to work with pagination
        document.getElementById('vehicleSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            
            // If search text is empty, reload the page to restore pagination
            if (searchText.trim() === '') {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('tab', 'vehicle-list');
                if (currentUrl.searchParams.has('search')) {
                    currentUrl.searchParams.delete('search');
                }
                window.location.href = currentUrl.toString();
                return;
            }
            
            // Get all vehicle rows
            const vehicleEntries = document.querySelectorAll('#vehicleListContainer > div');
            let hasResults = false;
            
            vehicleEntries.forEach(entry => {
                if (entry.className.includes('grid')) { // Only target actual vehicle rows
                    const text = entry.textContent.toLowerCase();
                    if (text.includes(searchText)) {
                        entry.style.display = '';
                        hasResults = true;
                    } else {
                        entry.style.display = 'none';
                    }
                }
            });
            
            // Show or hide the "No vehicles found" message
            const noResultsMsg = document.querySelector('#vehicleListContainer > .p-4.text-center');
            if (noResultsMsg) {
                noResultsMsg.style.display = hasResults ? 'none' : 'block';
            }
            
            // Hide pagination when searching
            const pagination = document.querySelector('.px-4.py-3.bg-gray-50.border-t');
            if (pagination) {
                pagination.style.display = hasResults ? '' : 'none';
            }
        });

        // Update tab functionality to preserve page number when switching tabs
        function handleTabChange(tabId) {
            // Hide all tab panels
            tabPanels.forEach(panel => {
                panel.classList.add('hidden');
            });
            
            // Deactivate all tab buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('text-blue-600', 'border-blue-600');
                btn.classList.add('border-transparent');
                btn.setAttribute('aria-selected', 'false');
            });
            
            // Activate the clicked tab
            const activeTab = document.getElementById(tabId);
            activeTab.classList.add('text-blue-600', 'border-blue-600');
            activeTab.classList.remove('border-transparent');
            activeTab.setAttribute('aria-selected', 'true');
            
            // Show corresponding tab panel
            const panelId = activeTab.getAttribute('data-tabs-target').substring(1);
            document.getElementById(panelId).classList.remove('hidden');
            
            // Update URL with tab parameter but keep page parameter if exists
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('tab', panelId);
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            history.pushState({path: newUrl}, '', newUrl);
        }

        // Update the tab click event listeners
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                handleTabChange(button.id);
            });
        });

        // Check URL parameters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                const tabToActivate = document.getElementById(`${tabParam}-tab`);
                if (tabToActivate) {
                    handleTabChange(tabToActivate.id);
                }
            }
        });
    </script>
</body>
</html>