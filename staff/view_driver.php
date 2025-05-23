<?php
session_start();
require_once('../connect.php');

if (!isset($_GET['id'])) {
    header('Location: manage_drivers.php');
    exit();
}

$driver_id = $_GET['id'];
$sql = "CALL sp_GetDriverById(?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    header('Location: manage_drivers.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Driver Details - RentWheels</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Header -->
                <div class="p-6 bg-blue-600">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-white">Driver Details</h1>
                        <a href="manage_drivers.php?tab=driver-list" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Driver List
                        </a>
                    </div>
                </div>

                <!-- Driver Information -->
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-center mb-6">
                        <div class="mb-4 md:mb-0">
                            <?php if(!empty($driver['IMAGE']) && file_exists('../'.$driver['IMAGE'])): ?>
                                <img class="h-32 w-32 rounded-full object-cover border-4 border-blue-100" 
                                    src="../<?= htmlspecialchars($driver['IMAGE']) ?>" 
                                    alt="<?= htmlspecialchars($driver['DRIVER_NAME']) ?>'s photo">
                            <?php else: ?>
                                <img class="h-32 w-32 rounded-full object-cover border-4 border-blue-100" 
                                    src="https://ui-avatars.com/api/?name=<?= urlencode($driver['DRIVER_NAME']) ?>&background=random&size=256" 
                                    alt="Driver photo">
                            <?php endif; ?>
                        </div>
                        <div class="ml-0 md:ml-6 text-center md:text-left">
                            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($driver['DRIVER_NAME']) ?></h2>
                            <div class="mt-2">
                                <span class="px-3 py-1 rounded-full text-sm font-medium 
                                    <?= $driver['STATUS'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= htmlspecialchars($driver['STATUS']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <!-- Personal Information -->
                        <div class="bg-gray-50 p-5 rounded-lg space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <p class="text-sm text-gray-600">Gender</p>
                                    <p class="font-medium"><?= htmlspecialchars($driver['GENDER']) ?></p>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-sm text-gray-600">Birthdate</p>
                                    <p class="font-medium"><?= htmlspecialchars(date('F j, Y', strtotime($driver['BIRTHDATE']))) ?></p>
                                </div>
                                <div class="space-y-2 col-span-2">
                                    <p class="text-sm text-gray-600">Contact Number</p>
                                    <p class="font-medium"><?= htmlspecialchars($driver['CONTACT_NUMBER']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- License Information -->
                        <div class="bg-gray-50 p-5 rounded-lg space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                License Information
                            </h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-2">
                                    <p class="text-sm text-gray-600">License Number</p>
                                    <p class="font-medium"><?= htmlspecialchars($driver['LICENSE_NUMBER']) ?></p>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-sm text-gray-600">Address</p>
                                    <p class="font-medium"><?= htmlspecialchars($driver['ADDRESS']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Add tab activation when returning from this page
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to all back buttons
            const backButtons = document.querySelectorAll('a[href="manage_drivers.php?tab=driver-list"]');
            backButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // The actual navigation happens naturally through the href attribute
                    // This is just to ensure proper tab activation on return
                    localStorage.setItem('activeTab', 'driver-list');
                });
            });
        });
    </script>
</body>
</html>
