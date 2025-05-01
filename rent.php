<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_rental');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search form submission
if (isset($_POST['search'])) {
    $vehicle_type = $_POST['vehicle_type'];
    $capacity = $_POST['capacity'];
    $transmission = $_POST['transmission'];
    
    $sql = "SELECT * FROM vehicle WHERE 1=1";
    
    if (!empty($vehicle_type)) {
        $sql .= " AND VEHICLE_TYPE = '$vehicle_type'";
    }
    if (!empty($capacity)) {
        $sql .= " AND CAPACITY = '$capacity'";
    }
    if (!empty($transmission)) {
        $sql .= " AND TRANSMISSION = '$transmission'";
    }
    
    $result = $conn->query($sql);
    $vehicles = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['search'])) {
    $customer_id = 1; // This should come from the logged-in user session
    $driver_id = isset($_POST['driver_id']) ? $_POST['driver_id'] : null;
    $payment_id = null; // This will be updated when payment is processed
    $rent_driver_type = $_POST['driver_type'];
    $pickup_location = $_POST['pickup_location'];
    $start_date = $_POST['start_date'];
    $return_date = $_POST['return_date'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $total_amount = 0; // This will be calculated based on vehicle selection

    $stmt = $conn->prepare("CALL sp_InsertRental(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissssdi", 
        $customer_id, 
        $driver_id, 
        $payment_id, 
        $rent_driver_type, 
        $pickup_location, 
        $start_date, 
        $return_date, 
        $total_amount,
        $quantity
    );

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $rental_id = $row['rental_id'];
            // Redirect to payment page or show success message
            header("Location: payment.php?rental_id=" . $rental_id);
            exit();
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Available</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
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
                            <a href="#" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span>Notifications</span>
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

                <!-- Search and Profile Section -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search vehicles..." class="w-64 px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <button class="absolute right-3 top-2.5 text-gray-400 hover:text-blue-600 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl overflow-hidden mb-8">
            <div class="p-8 md:p-12">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Find Your Perfect Ride</h1>
                <p class="text-blue-100 text-lg mb-6">Choose from our premium selection of vehicles for any occasion</p>
                <div class="flex space-x-4">
                    <button class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">View Fleet</button>
                    <button class="border border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors">Learn More</button>
                </div>
            </div>
        </div>

        <!-- Rental Form Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Rental Details</h2>
                    <p class="text-gray-500 mt-1">Find available vehicles for your trip</p>
                </div>
                <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium">Best Rates</span>
            </div>
            
            <!-- Rental Form -->
            <form method="POST" action="" class="space-y-8">
                <!-- Location and Dates -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pickup Location</label>
                        <div class="relative">
                            <select name="pickup_location" required class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                                <option value="" disabled selected>Select Location</option>
                                <option value="Cebu City Downtown">Cebu City Downtown</option>
                                <option value="Mactan Airport">Mactan Airport</option>
                                <option value="Mandaue City">Mandaue City</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date & Time</label>
                        <div class="relative">
                            <input type="datetime-local" 
                                   name="start_date"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Return Date & Time</label>
                        <div class="relative">
                            <input type="datetime-local" 
                                   name="return_date"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Additional Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                        <select name="vehicle_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option value="">All Types</option>
                            <option value="SUV">SUV</option>
                            <option value="HATCHBACK">HATCHBACK</option>
                            <option value="SEDAN">SEDAN</option>
                            <option value="MPV">MPV</option>
                            <option value="VAN">VAN</option>
                            <option value="MINIBUS">MINIBUS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                        <select name="capacity" class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option value="">All Capacities</option>
                            <option value="4-5">4-5 Person</option>
                            <option value="7-8">7-8 Person</option>
                            <option value="10-18">10-18 Person</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transmission</label>
                        <select name="transmission" class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option value="">All Types</option>
                            <option value="automatic">Automatic</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Driver Type</label>
                        <select name="driver_type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option value="" disabled selected>Select Driver Type</option>
                            <option value="self">Self-Drive</option>
                            <option value="with_driver">With Driver</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex justify-end">
                    <button type="submit" name="search" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search Available Vehicles
                    </button>
                </div>
            </form>

            <!-- Dynamic Vehicle Cards -->
            <?php if (isset($_POST['search'])): ?>
            <div class="mt-8">
                <?php if (!empty($vehicles)): ?>
                    <!-- Add hidden input for selected vehicles -->
                    <input type="hidden" id="selectedVehicles" name="selectedVehicles" value="">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[800px] overflow-y-auto p-4">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <!-- Vehicle cards from database -->
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group cursor-pointer vehicle-card"
                             onclick="toggleVehicleSelection(this, <?php echo $vehicle['VEHICLE_ID']; ?>)"
                             data-vehicle-id="<?php echo $vehicle['VEHICLE_ID']; ?>">
                            <!-- Add selected indicator -->
                            <div class="absolute top-4 right-4 z-10 hidden check-indicator">
                                <div class="bg-blue-600 text-white p-2 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Existing vehicle card content -->
                            <div class="relative h-[300px] overflow-hidden">
                                <img src="<?php echo $vehicle['IMAGES']; ?>" 
                                     alt="<?php echo $vehicle['VEHICLE_BRAND']; ?>" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                
                                <!-- Overlay with status and type -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        <?php echo $vehicle['VEHICLE_TYPE']; ?>
                                    </span>
                                    <span class="bg-emerald-600 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        <?php echo $vehicle['STATUS']; ?>
                                    </span>
                                </div>
                                
                                <!-- Price tag -->
                                <div class="absolute top-4 right-4">
                                    <div class="bg-white/90 backdrop-blur-sm text-blue-600 px-4 py-2 rounded-lg font-bold">
                                        ₱<?php echo number_format($vehicle['AMOUNT'], 2); ?>
                                        <span class="text-xs text-gray-500">/day</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Details Section -->
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900"><?php echo $vehicle['VEHICLE_BRAND']; ?></h3>
                                        <p class="text-gray-600 mt-1"><?php echo $vehicle['MODEL']; ?></p>
                                    </div>
                                    <span class="inline-flex items-center justify-center bg-blue-100 text-blue-600 px-2.5 py-0.5 rounded-full text-sm font-medium">
                                        <?php echo $vehicle['LICENSE_PLATE']; ?>
                                    </span>
                                </div>

                                <!-- Features Grid -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-blue-50 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 4H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zm-5 14h2m-6 0h2m-6 0h2"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $vehicle['CAPACITY']; ?> Seats</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-blue-50 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $vehicle['TRANSMISSION']; ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-blue-50 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $vehicle['YEAR']; ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-blue-50 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $vehicle['QUANTITY']; ?> Available</span>
                                    </div>
                                </div>

                                <!-- Quantity Selector -->
                                <div class="flex items-center gap-4 mb-4">
                                    <label class="text-sm text-gray-600">Select Quantity:</label>
                                    <div class="flex items-center">
                                        <button type="button" 
                                                onclick="updateQuantity(this, -1)" 
                                                class="px-3 py-1 border border-gray-300 rounded-l-lg hover:bg-gray-100">
                                            -
                                        </button>
                                        <input type="number" 
                                               name="quantity" 
                                               value="1" 
                                               min="1" 
                                               max="<?php echo $vehicle['QUANTITY']; ?>" 
                                               class="w-16 text-center border-y border-gray-300 py-1"
                                               readonly>
                                        <button type="button" 
                                                onclick="updateQuantity(this, 1)" 
                                                class="px-3 py-1 border border-gray-300 rounded-r-lg hover:bg-gray-100">
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-center">
                                    <button onclick="viewVehicleDetails(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)" 
                                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Single Booking Button -->
                    <div class="mt-6 flex justify-center">
                        <form method="POST" action="cust_info.php" id="bookingForm" class="w-full max-w-md">
                            <input type="hidden" name="rental_info[vehicles]" id="selected_vehicles_form">
                            <input type="hidden" name="rental_info[pickup_location]" id="pickup_location_form">
                            <input type="hidden" name="rental_info[start_date]" id="start_date_form">
                            <input type="hidden" name="rental_info[return_date]" id="return_date_form">
                            <input type="hidden" name="rental_info[driver_type]" id="driver_type_form">
                            <button type="button" 
                                    onclick="proceedToBooking()" 
                                    class="w-full bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                Proceed to Booking
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 bg-white rounded-xl shadow">
                        <p class="text-gray-500">No vehicles found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Vehicle Details Modal -->
            <div id="vehicleDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl max-w-2xl w-full overflow-hidden">
                        <!-- Modal Header -->
                        <div class="flex justify-between items-center p-4 border-b">
                            <h3 class="text-xl font-bold" id="modalVehicleName"></h3>
                            <button onclick="closeVehicleDetails()" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Modal Content -->
                        <div class="p-4">
                            <!-- Vehicle Image -->
                            <div class="relative h-64 mb-4">
                                <img id="modalVehicleImage" class="w-full h-full object-cover rounded-lg" src="" alt="Vehicle">
                            </div>
                            
                            <!-- Vehicle Information Grid -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Vehicle Type</p>
                                    <p id="modalVehicleType" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Brand & Model</p>
                                    <p id="modalBrandModel" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Year</p>
                                    <p id="modalYear" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Color</p>
                                    <p id="modalColor" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">License Plate</p>
                                    <p id="modalPlate" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Transmission</p>
                                    <p id="modalTransmission" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Capacity</p>
                                    <p id="modalCapacity" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Rate</p>
                                    <p id="modalAmount" class="font-semibold text-blue-600"></p>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Description</p>
                                <p id="modalDescription" class="text-gray-700"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function viewVehicleDetails(vehicle) {
                    // Update modal content
                    document.getElementById('modalVehicleName').textContent = vehicle.VEHICLE_BRAND + ' ' + vehicle.MODEL;
                    document.getElementById('modalVehicleImage').src = vehicle.IMAGES;
                    document.getElementById('modalVehicleType').textContent = vehicle.VEHICLE_TYPE;
                    document.getElementById('modalBrandModel').textContent = vehicle.VEHICLE_BRAND + ' ' + vehicle.MODEL;
                    document.getElementById('modalYear').textContent = vehicle.YEAR;
                    document.getElementById('modalColor').textContent = vehicle.COLOR;
                    document.getElementById('modalPlate').textContent = vehicle.LICENSE_PLATE;
                    document.getElementById('modalTransmission').textContent = vehicle.TRANSMISSION;
                    document.getElementById('modalCapacity').textContent = vehicle.CAPACITY;
                    document.getElementById('modalAmount').textContent = '₱' + parseFloat(vehicle.AMOUNT).toLocaleString(undefined, {minimumFractionDigits: 2});
                    document.getElementById('modalDescription').textContent = vehicle.VEHICLE_DESCRIPTION;

                    // Show modal
                    document.getElementById('vehicleDetailsModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden'; // Prevent scrolling
                }

                function closeVehicleDetails() {
                    document.getElementById('vehicleDetailsModal').classList.add('hidden');
                    document.body.style.overflow = ''; // Restore scrolling
                }

                // Close modal when clicking outside
                document.getElementById('vehicleDetailsModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeVehicleDetails();
                    }
                });

                function updateQuantity(button, change) {
                    const input = button.parentElement.querySelector('input');
                    const currentValue = parseInt(input.value);
                    const maxValue = parseInt(input.getAttribute('max'));
                    const newValue = currentValue + change;
                    
                    if (newValue >= 1 && newValue <= maxValue) {
                        input.value = newValue;
                    }
                }

                let selectedVehicles = new Set();

                function toggleVehicleSelection(card, vehicleId) {
                    card.classList.toggle('selected');
                    
                    if (selectedVehicles.has(vehicleId)) {
                        selectedVehicles.delete(vehicleId);
                    } else {
                        selectedVehicles.add(vehicleId);
                    }
                    
                    document.getElementById('selectedVehicles').value = Array.from(selectedVehicles).join(',');
                    document.getElementById('selected_vehicles_form').value = Array.from(selectedVehicles).join(',');
                }

                function proceedToBooking() {
                    if (selectedVehicles.size === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Vehicles Selected',
                            text: 'Please select at least one vehicle to proceed with booking.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    // Get form values
                    const pickupLocation = document.querySelector('select[name="pickup_location"]').value;
                    const startDate = document.querySelector('input[name="start_date"]').value;
                    const returnDate = document.querySelector('input[name="return_date"]').value;
                    const driverType = document.querySelector('select[name="driver_type"]').value;

                    // Set form values
                    document.getElementById('selected_vehicles_form').value = Array.from(selectedVehicles).join(',');
                    document.getElementById('pickup_location_form').value = pickupLocation;
                    document.getElementById('start_date_form').value = startDate;
                    document.getElementById('return_date_form').value = returnDate;
                    document.getElementById('driver_type_form').value = driverType;

                    // Submit form
                    document.getElementById('bookingForm').submit();
                }
            </script>

            <!-- Add styles for selected cards -->
            <style>
                .vehicle-card.selected {
                    border: 2px solid #2563eb;
                    position: relative;
                }
                
                .vehicle-card.selected .check-indicator {
                    display: block;
                }
            </style>
        </div>
    </main>

    <script>
        function showVehicles() {
            const vehicleCards = document.getElementById('vehicleCards');
            vehicleCards.classList.remove('hidden');
            // Smooth scroll to vehicles
            vehicleCards.scrollIntoView({ behavior: 'smooth' });
        }
        
        function showModal(vehicle) {
            const modal = document.getElementById('vehicleModal');
            const data = vehicles[vehicle];

            // Update modal content
            document.getElementById('modalTitle').textContent = data.title;
            document.getElementById('modalImage').src = data.image;
            document.getElementById('modalYear').textContent = data.year;
            document.getElementById('modalColor').textContent = data.color;
            document.getElementById('modalPlate').textContent = data.plate;
            document.getElementById('modalPrice').textContent = data.price;
            document.getElementById('modalDescription').textContent = data.description;

            // Show modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeModal() {
            const modal = document.getElementById('vehicleModal');
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }

        function bookNow() {
            // Redirect to customer information page
            window.location.href = 'cust_info.php';
        }

        // Close modal when clicking outside
        document.getElementById('vehicleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        function updateQuantity(button, change) {
            const input = button.parentElement.querySelector('input');
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.getAttribute('max'));
            const newValue = currentValue + change;
            
            if (newValue >= 1 && newValue <= maxValue) {
                input.value = newValue;
            }
        }
    </script>
    
    <!-- Footer Section -->
    <footer class="bg-gray-900 text-gray-300 mt-24">
        <div class="container mx-auto px-4 py-12">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Company Info -->
                <div>
                    <h3 class="text-white text-lg font-bold mb-4">RentWheels</h3>
                    <p class="text-gray-400 text-sm leading-loose">
                        Your trusted partner in car rentals since 2010. Providing quality vehicles and exceptional service across the Philippines.
                    </p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Our Fleet</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Booking</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQs</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-white text-lg font-bold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Insurance Policy</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-white text-lg font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-400">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            123 Main Street, Cebu City
                        </li>
                        <li class="flex items-center text-gray-400">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            info@rentwheels.com
                        </li>
                        <li class="flex items-center text-gray-400">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            +63 912 345 6789
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-gray-400">© 2024 RentWheels. All rights reserved.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Terms of Service</a>
                        <a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>