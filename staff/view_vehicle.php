<?php
session_start();
require_once('../connect.php');

if(isset($_GET['id'])) {
    $vehicle_id = $_GET['id'];
    // Use stored procedure instead of direct query
    $sql = "CALL sp_GetVehicleDetails(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicle = $result->fetch_assoc();
    
    if(!$vehicle) {
        header("Location: manage_vehicle.php");
        exit();
    }
    
    // Debug output to check the data
    // var_dump($vehicle); // Uncomment this to check the data structure
} else {
    header("Location: manage_vehicle.php");
    exit();
}

function displayImage($imagePath) {
    if ($imagePath) {
        return "../" . $imagePath;
    }
    return "../assets/img/default-vehicle.jpg"; // Fallback image
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Vehicle - RentWheels</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <button onclick="window.location.href='manage_vehicle.php'" 
                    class="mb-6 inline-flex items-center px-4 py-2.5 bg-white text-gray-700 rounded-xl shadow-sm hover:bg-gray-50 border border-gray-200 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Vehicle List
            </button>

            <!-- Vehicle Details Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                <!-- Vehicle Image -->
                <div class="w-full h-[500px] bg-gray-100 relative group">
                    <img src="<?php echo displayImage($vehicle['IMAGES']); ?>" 
                         alt="Vehicle Image" 
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>

                <!-- Vehicle Information -->
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($vehicle['VEHICLE_BRAND'] . ' ' . $vehicle['MODEL']); ?>
                            </h1>
                            <div class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Type: <?php echo htmlspecialchars($vehicle['VEHICLE_TYPE']); ?>
                            </div>
                        </div>
                        <span class="px-4 py-2 rounded-xl text-sm font-semibold <?php 
                            echo $vehicle['STATUS'] == 'available' ? 'bg-green-100 text-green-800' : 
                                ($vehicle['STATUS'] == 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                        ?>">
                            <?php echo ucfirst($vehicle['STATUS']); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <!-- Vehicle Details -->
                        <div class="bg-gray-50 p-6 rounded-xl">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Details</h3>
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Year</dt>
                                    <dd class="text-lg text-gray-900"><?php echo htmlspecialchars($vehicle['YEAR']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Color</dt>
                                    <dd class="text-lg text-gray-900"><?php echo htmlspecialchars($vehicle['COLOR']); ?></dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">License Plate</dt>
                                    <dd class="text-lg text-gray-900"><?php echo htmlspecialchars($vehicle['LICENSE_PLATE']); ?></dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Specifications -->
                        <div class="bg-gray-50 p-6 rounded-xl">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Specifications</h3>
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                    <dd class="text-lg text-gray-900"><?php echo htmlspecialchars($vehicle['CAPACITY']); ?> persons</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Transmission</dt>
                                    <dd class="text-lg text-gray-900"><?php echo htmlspecialchars($vehicle['TRANSMISSION']); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                        <p class="text-gray-700 leading-relaxed"><?php 
                            echo !empty($vehicle['VEHICLE_DESCRIPTION']) ? 
                                htmlspecialchars($vehicle['VEHICLE_DESCRIPTION']) : 
                                'No description available'; 
                        ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
