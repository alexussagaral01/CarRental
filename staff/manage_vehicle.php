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
    $status = $_POST['STATUS'];
    $amount = $_POST['AMOUNT'];
    $quantity = $_POST['QUANTITY'];  // Add this line

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
                $stmt = $conn->prepare("CALL sp_AddVehicle(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssisssssssdi", 
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
                    $amount,            // DECIMAL
                    $quantity           // INT (new parameter)
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
                                    window.location = 'manage_vehicle.php';
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
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2v3M9 5h6"/></svg>
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
                                        <select name="VEHICLE_TYPE" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
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
                                        <input type="text" 
                                            name="VEHICLE_BRAND" 
                                            required 
                                            placeholder="Enter Brand" 
                                            pattern="[A-Za-z\s]+"
                                            title="Please enter only letters and spaces"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                <!-- Model and Year Row -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Model</label>
                                        <input type="text" 
                                            name="MODEL" 
                                            required 
                                            placeholder="Enter Model" 
                                            pattern="[A-Za-z\s]+"
                                            title="Please enter only letters and spaces"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Year</label>
                                        <input type="text" 
                                            name="YEAR" 
                                            required 
                                            placeholder="Enter Year" 
                                            pattern="[0-9]{4}"
                                            maxlength="4"
                                            title="Please enter a valid 4-digit year"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                                        <input type="text" 
                                            name="COLOR" 
                                            required 
                                            placeholder="Enter Color" 
                                            pattern="[A-Za-z\s]+"
                                            title="Please enter only letters and spaces"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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

                            <!-- Status Section -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                                <h4 class="font-medium text-gray-700">Status</h4>
                                <select name="STATUS" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="available">Available</option>
                                    <option value="rented">Rented</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>

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
                                           min="0"
                                           step="0.01"
                                           placeholder="0.00"
                                           class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Quantity Section - Add this new section -->
                            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                                <h4 class="font-medium text-gray-700">Quantity Available</h4>
                                <div class="relative">
                                    <input type="number" 
                                           name="QUANTITY" 
                                           required 
                                           min="1"
                                           value="1"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <input type="search" id="vehicleSearch" placeholder="Search vehicles..." 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                        <div class="divide-y divide-gray-200">
                            <?php
                            $query = "SELECT * FROM vehicle";
                            $result = mysqli_query($conn, $query);

                            if ($result && mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)): 
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
                                        <button onclick="deleteVehicle(<?= $row['VEHICLE_ID'] ?>)" 
                                                class="p-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors"
                                                title="Delete Vehicle">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <div class="p-4 text-center text-gray-500">
                                    No vehicles found
                                </div>
                            <?php endif; ?>
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

        function deleteVehicle(vehicleId) {
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
                    fetch('delete_vehicle.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'vehicle_id=' + vehicleId
                    })
                    .then(response => response.text())
                    .then(result => {
                        if(result === 'success') {
                            Swal.fire(
                                'Deleted!',
                                'Vehicle has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete vehicle.',
                                'error'
                            );
                        }
                    });
                }
            });
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

        // Add this after your existing script
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addVehicleForm');
            const brandInput = form.querySelector('input[name="VEHICLE_BRAND"]');
            const modelInput = form.querySelector('input[name="MODEL"]');
            const colorInput = form.querySelector('input[name="COLOR"]');
            const yearInput = form.querySelector('input[name="YEAR"]');
            const amountInput = form.querySelector('input[name="AMOUNT"]');
            const quantityInput = form.querySelector('input[name="QUANTITY"]');

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
            modelInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            colorInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            yearInput.addEventListener('keypress', preventNonNumbers);

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

            // Add quantity validation
            quantityInput.addEventListener('input', function(e) {
                if (this.value < 1) {
                    this.value = 1;
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: 'Quantity must be at least 1',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });

            // Form validation before submit
            form.addEventListener('submit', function(e) {
                const brandValue = brandInput.value;
                const modelValue = modelInput.value;
                const colorValue = colorInput.value;
                const yearValue = yearInput.value;
                const amountValue = parseFloat(amountInput.value);
                const quantityValue = parseInt(quantityInput.value);

                const pattern = /^[A-Za-z\s]+$/;
                const yearPattern = /^[0-9]{4}$/;
                const currentYear = new Date().getFullYear();

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

                if (!pattern.test(colorValue)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Color can only contain letters and spaces'
                    });
                    return;
                }

                if (!yearPattern.test(yearValue)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Year must be a 4-digit number'
                    });
                    return;
                }

                if (yearValue < 1900 || yearValue > currentYear + 1) {
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

                if (isNaN(quantityValue) || quantityValue < 1) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: 'Please enter a valid quantity (minimum 1)'
                    });
                    return;
                }
            });
        });
    </script>

</body>
</html>