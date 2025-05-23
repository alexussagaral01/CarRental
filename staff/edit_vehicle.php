<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "vehicle_rental");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize message variable
$message = '';

// Get vehicle ID from URL
$vehicle_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_type = $_POST['VEHICLE_TYPE'];
    $vehicle_brand = $_POST['VEHICLE_BRAND'];
    $model = $_POST['MODEL'];
    $year = $_POST['YEAR'];
    $color = $_POST['COLOR'];
    $license_plate = $_POST['LICENSE_PLATE'];
    $vehicle_description = $_POST['VEHICLE_DESCRIPTION'];
    $capacity = $_POST['CAPACITY'];
    $transmission = $_POST['TRANSMISSION'];
    // Removed status field
    $amount = $_POST['AMOUNT'];

    // Update query
    $update_query = "UPDATE vehicle SET 
        VEHICLE_TYPE = ?,
        VEHICLE_BRAND = ?,
        MODEL = ?,
        YEAR = ?,
        COLOR = ?,
        LICENSE_PLATE = ?,
        VEHICLE_DESCRIPTION = ?,
        CAPACITY = ?,
        TRANSMISSION = ?,
        AMOUNT = ?
        WHERE VEHICLE_ID = ?";

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssississdi", 
        $vehicle_type,
        $vehicle_brand,
        $model,
        $year,
        $color,
        $license_plate,
        $vehicle_description,
        $capacity,
        $transmission,
        $amount,
        $vehicle_id
    );

    if ($stmt->execute()) {
        // Handle new image upload if provided
        if(isset($_FILES['IMAGES']) && $_FILES['IMAGES']['error'][0] == 0) {
            $upload_dir = '../VEHICLE_IMAGES/';
            $file_extension = pathinfo($_FILES['IMAGES']['name'][0], PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;

            if(move_uploaded_file($_FILES['IMAGES']['tmp_name'][0], $upload_path)) {
                $image_path = 'VEHICLE_IMAGES/' . $unique_filename;
                $update_image = "UPDATE vehicle SET IMAGES = ? WHERE VEHICLE_ID = ?";
                $stmt = $conn->prepare($update_image);
                $stmt->bind_param("si", $image_path, $vehicle_id);
                $stmt->execute();
            }
        }

        $message = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Vehicle has been updated successfully!',
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
                        text: 'Error updating vehicle: " . $stmt->error . "',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                });
            </script>";
    }
}

// Fetch existing vehicle data
$query = "SELECT * FROM vehicle WHERE VEHICLE_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();

if (!$vehicle) {
    header("Location: manage_vehicle.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - RentWheels</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/manage_vehicle.css">
    <style>
        /* Adding specific styles for consistent appearance */
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
    if (!empty($message)) {
        echo $message;
    }
    ?>
    <!-- Header Section -->
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
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
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Edit Vehicle</h2>
                <p class="text-gray-600">Update vehicle information</p>
            </div>
            <div class="mt-8 flex justify-between">
                <a href="manage_vehicle.php?tab=vehicle-list" class="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Vehicle List
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-6 rounded-xl space-y-6">
                        <h4 class="font-medium text-gray-700">Basic Information</h4>
                        
                        <!-- Type and Brand Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Vehicle Type</label>
                                <select name="VEHICLE_TYPE" id="vehicleTypeSelect" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                                    <option value="SUV" <?php echo $vehicle['VEHICLE_TYPE'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                                    <option value="HATCHBACK" <?php echo $vehicle['VEHICLE_TYPE'] == 'HATCHBACK' ? 'selected' : ''; ?>>HATCHBACK</option>
                                    <option value="SEDAN" <?php echo $vehicle['VEHICLE_TYPE'] == 'SEDAN' ? 'selected' : ''; ?>>SEDAN</option>
                                    <option value="MPV" <?php echo $vehicle['VEHICLE_TYPE'] == 'MPV' ? 'selected' : ''; ?>>MPV</option>
                                    <option value="VAN" <?php echo $vehicle['VEHICLE_TYPE'] == 'VAN' ? 'selected' : ''; ?>>VAN</option>
                                    <option value="MINIBUS" <?php echo $vehicle['VEHICLE_TYPE'] == 'MINIBUS' ? 'selected' : ''; ?>>MINIBUS</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Brand</label>
                                <select name="VEHICLE_BRAND" id="brandSelect" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                                    <option value="<?php echo htmlspecialchars($vehicle['VEHICLE_BRAND']); ?>" selected><?php echo htmlspecialchars($vehicle['VEHICLE_BRAND']); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Model and Year Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Model</label>
                                <select name="MODEL" id="modelSelect" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                                    <option value="<?php echo htmlspecialchars($vehicle['MODEL']); ?>" selected><?php echo htmlspecialchars($vehicle['MODEL']); ?></option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Year</label>
                                <div class="year-dropdown">
                                    <input type="hidden" name="YEAR" id="yearField" value="<?php echo htmlspecialchars($vehicle['YEAR']); ?>" required>
                                    <button type="button" id="yearDropdownButton" class="year-dropdown-button">
                                        <span id="yearDisplay"><?php echo htmlspecialchars($vehicle['YEAR']); ?></span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <div id="yearDropdownList" class="year-dropdown-list">
                                        <div id="yearOptions" class="year-grid">
                                            <!-- Years will be inserted here dynamically -->
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
                                <select name="COLOR" id="colorSelect" required class="hidden">
                                    <option value="" disabled>Select Color</option>
                                    <option value="Black" data-color="#000000" <?php echo $vehicle['COLOR'] == 'Black' ? 'selected' : ''; ?>>Black</option>
                                    <option value="White" data-color="#FFFFFF" <?php echo $vehicle['COLOR'] == 'White' ? 'selected' : ''; ?>>White</option>
                                    <option value="Silver" data-color="#C0C0C0" <?php echo $vehicle['COLOR'] == 'Silver' ? 'selected' : ''; ?>>Silver</option>
                                    <option value="Gray" data-color="#808080" <?php echo $vehicle['COLOR'] == 'Gray' ? 'selected' : ''; ?>>Gray</option>
                                    <option value="Red" data-color="#FF0000" <?php echo $vehicle['COLOR'] == 'Red' ? 'selected' : ''; ?>>Red</option>
                                    <option value="Blue" data-color="#0000FF" <?php echo $vehicle['COLOR'] == 'Blue' ? 'selected' : ''; ?>>Blue</option>
                                    <option value="Dark Blue" data-color="#00008B" <?php echo $vehicle['COLOR'] == 'Dark Blue' ? 'selected' : ''; ?>>Dark Blue</option>
                                    <option value="Sky Blue" data-color="#87CEEB" <?php echo $vehicle['COLOR'] == 'Sky Blue' ? 'selected' : ''; ?>>Sky Blue</option>
                                    <option value="Navy Blue" data-color="#000080" <?php echo $vehicle['COLOR'] == 'Navy Blue' ? 'selected' : ''; ?>>Navy Blue</option>
                                    <option value="Green" data-color="#008000" <?php echo $vehicle['COLOR'] == 'Green' ? 'selected' : ''; ?>>Green</option>
                                    <option value="Dark Green" data-color="#006400" <?php echo $vehicle['COLOR'] == 'Dark Green' ? 'selected' : ''; ?>>Dark Green</option>
                                    <option value="Yellow" data-color="#FFFF00" <?php echo $vehicle['COLOR'] == 'Yellow' ? 'selected' : ''; ?>>Yellow</option>
                                    <option value="Orange" data-color="#FFA500" <?php echo $vehicle['COLOR'] == 'Orange' ? 'selected' : ''; ?>>Orange</option>
                                    <option value="Brown" data-color="#A52A2A" <?php echo $vehicle['COLOR'] == 'Brown' ? 'selected' : ''; ?>>Brown</option>
                                    <option value="Beige" data-color="#F5F5DC" <?php echo $vehicle['COLOR'] == 'Beige' ? 'selected' : ''; ?>>Beige</option>
                                    <option value="Purple" data-color="#800080" <?php echo $vehicle['COLOR'] == 'Purple' ? 'selected' : ''; ?>>Purple</option>
                                    <option value="Pink" data-color="#FFC0CB" <?php echo $vehicle['COLOR'] == 'Pink' ? 'selected' : ''; ?>>Pink</option>
                                    <option value="Gold" data-color="#FFD700" <?php echo $vehicle['COLOR'] == 'Gold' ? 'selected' : ''; ?>>Gold</option>
                                    <option value="Burgundy" data-color="#800020" <?php echo $vehicle['COLOR'] == 'Burgundy' ? 'selected' : ''; ?>>Burgundy</option>
                                    <option value="Maroon" data-color="#800000" <?php echo $vehicle['COLOR'] == 'Maroon' ? 'selected' : ''; ?>>Maroon</option>
                                    <option value="Teal" data-color="#008080" <?php echo $vehicle['COLOR'] == 'Teal' ? 'selected' : ''; ?>>Teal</option>
                                    <option value="Olive" data-color="#808000" <?php echo $vehicle['COLOR'] == 'Olive' ? 'selected' : ''; ?>>Olive</option>
                                    <option value="Champagne" data-color="#F7E7CE" <?php echo $vehicle['COLOR'] == 'Champagne' ? 'selected' : ''; ?>>Champagne</option>
                                    <option value="Bronze" data-color="#CD7F32" <?php echo $vehicle['COLOR'] == 'Bronze' ? 'selected' : ''; ?>>Bronze</option>
                                </select>
                                <div id="colorPickerContainer" class="relative">
                                    <div id="colorDisplay" class="selected-color-display">
                                        <div id="selectedColorSwatch" class="color-swatch"></div>
                                        <span id="selectedColorText" class="flex-1"><?php echo htmlspecialchars($vehicle['COLOR']); ?></span>
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
                                <input type="text" name="LICENSE_PLATE" required value="<?php echo htmlspecialchars($vehicle['LICENSE_PLATE']); ?>" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <!-- Transmission and Capacity Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Transmission</label>
                                <select name="TRANSMISSION" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                                    <option value="Automatic" <?php echo $vehicle['TRANSMISSION'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                                    <option value="Manual" <?php echo $vehicle['TRANSMISSION'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Capacity</label>
                                <select name="CAPACITY" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                                    <option value="4-5" <?php echo $vehicle['CAPACITY'] == '4-5' ? 'selected' : ''; ?>>4-5 Person</option>
                                    <option value="7-8" <?php echo $vehicle['CAPACITY'] == '7-8' ? 'selected' : ''; ?>>7-8 Person</option>
                                    <option value="10-18" <?php echo $vehicle['CAPACITY'] == '10-18' ? 'selected' : ''; ?>>10-18 Person</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Current Image -->
                    <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                        <h4 class="font-medium text-gray-700">Current Image</h4>
                        <div class="aspect-[16/9] rounded-lg overflow-hidden">
                            <img src="../<?php echo htmlspecialchars($vehicle['IMAGES']); ?>" 
                                 class="w-full h-full object-cover" 
                                 alt="Current vehicle image">
                        </div>
                    </div>

                    <!-- New Image Upload -->
                    <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                        <h4 class="font-medium text-gray-700">Upload New Image (Optional)</h4>
                        <input type="file" name="IMAGES[]" accept="image/*" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Description Section -->
                    <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                        <h4 class="font-medium text-gray-700">Description</h4>
                        <textarea name="VEHICLE_DESCRIPTION" required rows="4" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($vehicle['VEHICLE_DESCRIPTION']); ?></textarea>
                    </div>

                    <!-- Amount Section -->
                    <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                        <h4 class="font-medium text-gray-700">Amount</h4>
                        <input type="number" name="AMOUNT" value="<?php echo $vehicle['AMOUNT']; ?>" required min="0" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Update Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const brandInput = form.querySelector('input[name="VEHICLE_BRAND"]');
        const modelInput = form.querySelector('input[name="MODEL"]');
        const colorInput = form.querySelector('input[name="COLOR"]');
        const yearInput = form.querySelector('input[name="YEAR"]');

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

        // Form validation before submit
        form.addEventListener('submit', function(e) {
            const brandValue = brandInput.value;
            const modelValue = modelInput.value;
            const colorValue = colorInput.value;
            const yearValue = yearInput.value;

            const pattern = /^[A-Za-z\s]+$/;
            const yearPattern = /^[0-9]{4}$/;
            const currentYear = new Date().getFullYear();

            if (!pattern.test(brandValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Brand name can only contain letters and spaces',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            if (!pattern.test(modelValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Model can only contain letters and spaces',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            if (!pattern.test(colorValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Color can only contain letters and spaces',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            if (!yearPattern.test(yearValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Year must be a 4-digit number',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            if (yearValue < 1900 || yearValue > currentYear + 1) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Year',
                    text: `Year must be between 1900 and ${currentYear + 1}`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }
        });
    });

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
    
    // Extract just the brands for each vehicle type
    const vehicleBrands = {};
    for (const type in vehicleTypeBrandsModels) {
        vehicleBrands[type] = Object.keys(vehicleTypeBrandsModels[type]);
    }
    
    const vehicleTypeSelect = document.getElementById('vehicleTypeSelect');
    const brandSelect = document.getElementById('brandSelect');
    const modelSelect = document.getElementById('modelSelect');
    const currentBrand = "<?php echo $vehicle['VEHICLE_BRAND']; ?>";
    const currentModel = "<?php echo $vehicle['MODEL']; ?>";
    
    let currentVehicleType = vehicleTypeSelect.value;
    
    // Initialize year dropdown
    function initYearDropdown() {
        const yearField = document.getElementById('yearField');
        const yearDisplay = document.getElementById('yearDisplay');
        const yearDropdownButton = document.getElementById('yearDropdownButton');
        const yearDropdownList = document.getElementById('yearDropdownList');
        const yearOptions = document.getElementById('yearOptions');
        const currentYear = new Date().getFullYear();
        const startYear = 1990;
        const currentVehicleYear = "<?php echo $vehicle['YEAR']; ?>";
        
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
            if (year.toString() === currentVehicleYear) {
                option.className += ' selected';
            }
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
        
        // Add 2020s decade separately
        addYearCategory('2020s');
        
        // Create current decade years starting from the year before current year
        const currentDecadeStart = Math.floor(currentYear / 10) * 10;
        
        // Start from the year before current year and go down to the decade start
        for (let year = currentYear - 1; year >= currentDecadeStart; year--) {
            const option = document.createElement('div');
            option.className = 'year-option';
            if (year.toString() === currentVehicleYear) {
                option.className += ' selected';
            }
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
                if (year.toString() === currentVehicleYear) {
                    option.className += ' selected';
                }
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
        
        // Pre-fill with current vehicle year
        yearDisplay.textContent = currentVehicleYear;
        yearField.value = currentVehicleYear;
    }
    
    // Initialize color picker
    function initColorPicker() {
        const colorSelect = document.getElementById('colorSelect');
        const colorDisplay = document.getElementById('colorDisplay');
        const colorPalette = document.getElementById('colorPalette');
        const colorGrid = document.getElementById('colorGrid');
        const closeColorPalette = document.getElementById('closeColorPalette');
        const selectedColorSwatch = document.getElementById('selectedColorSwatch');
        const selectedColorText = document.getElementById('selectedColorText');
        
        // Clear existing content
        colorGrid.innerHTML = '';
        
        // Get all color options from the hidden select
        const colorOptions = [];
        Array.from(colorSelect.options).forEach(option => {
            if (option.value && option.dataset.color) {
                colorOptions.push({
                    name: option.value,
                    hex: option.dataset.color,
                    selected: option.selected
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
            
            // Mark as selected if this is the current color
            if (color.selected) {
                colorCell.classList.add('selected');
                selectedColorSwatch.style.backgroundColor = color.hex;
                selectedColorText.textContent = color.name;
                
                if (color.name === 'White') {
                    selectedColorSwatch.style.border = '1px solid #e5e7eb';
                } else {
                    selectedColorSwatch.style.border = '1px solid rgba(0,0,0,0.1)';
                }
            }
            
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
    }
    
    // Populate brand dropdown based on vehicle type
    function populateBrandDropdown() {
        const selectedType = vehicleTypeSelect.value;
        currentVehicleType = selectedType;
        
        // Clear existing options
        brandSelect.innerHTML = '';
        
        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.textContent = 'Select Brand';
        brandSelect.appendChild(defaultOption);
        
        // Add brands for selected vehicle type
        if (selectedType && vehicleBrands[selectedType]) {
            vehicleBrands[selectedType].forEach(brand => {
                const option = document.createElement('option');
                option.value = brand;
                option.textContent = brand;
                if (brand === currentBrand) {
                    option.selected = true;
                }
                brandSelect.appendChild(option);
            });
        }
        
        // Trigger change to populate models
        const event = new Event('change');
        brandSelect.dispatchEvent(event);
    }
    
    // Populate model dropdown based on selected brand
    function populateModelDropdown() {
        const selectedBrand = brandSelect.value;
        
        // Clear existing options
        modelSelect.innerHTML = '';
        
        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.textContent = 'Select Model';
        modelSelect.appendChild(defaultOption);
        
        // Add models for selected brand
        if (selectedBrand && currentVehicleType && vehicleTypeBrandsModels[currentVehicleType][selectedBrand]) {
            vehicleTypeBrandsModels[currentVehicleType][selectedBrand].forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                if (model === currentModel) {
                    option.selected = true;
                }
                modelSelect.appendChild(option);
            });
        }
    }
    
    // Set up event listeners
    vehicleTypeSelect.addEventListener('change', populateBrandDropdown);
    brandSelect.addEventListener('change', populateModelDropdown);
    
    // Initialize dropdowns
    populateBrandDropdown();
    
    // Initialize year dropdown
    initYearDropdown();
    
    // Initialize color picker
    initColorPicker();
    
    // Form validation
    // ...existing code...
});
</script>
</body>
</html>