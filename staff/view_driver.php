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
                        <a href="manage_drivers.php" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Driver List
                        </a>
                    </div>
                </div>

                <!-- Driver Information -->
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <img class="h-20 w-20 rounded-full" 
                            src="https://ui-avatars.com/api/?name=<?= urlencode($driver['DRIVER_NAME']) ?>" 
                            alt="Driver photo">
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($driver['DRIVER_NAME']) ?></h2>
                            <span class="px-2 py-1 rounded-full text-sm font-medium 
                                <?= $driver['STATUS'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= htmlspecialchars($driver['STATUS']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700">Personal Information</h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">Gender</p>
                                <p class="font-medium"><?= htmlspecialchars($driver['GENDER']) ?></p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">Birthdate</p>
                                <p class="font-medium"><?= htmlspecialchars(date('F j, Y', strtotime($driver['BIRTHDATE']))) ?></p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">Contact Number</p>
                                <p class="font-medium"><?= htmlspecialchars($driver['CONTACT_NUMBER']) ?></p>
                            </div>
                        </div>

                        <!-- License Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700">License Information</h3>
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
</body>
</html>
