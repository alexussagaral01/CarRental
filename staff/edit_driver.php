<?php
session_start();
require_once('../connect.php');

$message = '';

if (!isset($_GET['id'])) {
    header('Location: manage_drivers.php');
    exit();
}

$driver_id = $_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_driver'])) {
    $staff_id = 1; // You might want to get this from the session
    $driver_name = $_POST['driver_name'];
    $license_number = $_POST['license_number'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $status = $_POST['status'];
    
    $sql = "CALL sp_UpdateDriver(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssss", 
        $driver_id, $staff_id, $driver_name, $license_number, 
        $contact_number, $address, $birthdate, $gender, $status
    );
    
    if ($stmt->execute()) {
        echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Driver information has been updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location = 'manage_drivers.php';
                    });
                });
            </script>";
    } else {
        echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error updating driver: " . $stmt->error . "'
                    });
                });
            </script>";
    }
    $stmt->close();
}

// Fetch driver details
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
    <title>Edit Driver - RentWheels</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Header -->
                <div class="p-6 bg-indigo-600">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-white">Edit Driver</h1>
                        <a href="manage_drivers.php" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Driver List
                        </a>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" class="p-6" id="editDriverForm">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700">Personal Information</h3>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Driver Name</label>
                                <input type="text" 
                                    name="driver_name" 
                                    value="<?= htmlspecialchars($driver['DRIVER_NAME']) ?>" 
                                    required
                                    pattern="[A-Za-z\s]+"
                                    title="Please enter only letters and spaces"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Contact Number</label>
                                <input type="tel" 
                                    name="contact_number" 
                                    value="<?= htmlspecialchars($driver['CONTACT_NUMBER']) ?>" 
                                    required
                                    pattern="[0-9]+"
                                    maxlength="11"
                                    title="Please enter only numbers"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Gender</label>
                                <select name="gender" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="Male" <?= $driver['GENDER'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $driver['GENDER'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700">Additional Information</h3>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">License Number</label>
                                <input type="text" name="license_number" value="<?= htmlspecialchars($driver['LICENSE_NUMBER']) ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Birthdate</label>
                                <input type="date" name="birthdate" value="<?= htmlspecialchars($driver['BIRTHDATE']) ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="Available" <?= $driver['STATUS'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                    <option value="On Duty" <?= $driver['STATUS'] === 'On Duty' ? 'selected' : '' ?>>On Duty</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" required rows="3" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        ><?= htmlspecialchars($driver['ADDRESS']) ?></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="manage_drivers.php" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" name="update_driver"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Update Driver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add validation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editDriverForm');
            const driverNameInput = form.querySelector('input[name="driver_name"]');
            const contactNumberInput = form.querySelector('input[name="contact_number"]');

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

            // Form validation before submit
            form.addEventListener('submit', function(e) {
                const driverNameValue = driverNameInput.value;
                const contactNumberValue = contactNumberInput.value;

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
            });
        });
    </script>
</body>
</html>
