<?php
session_start();
require_once('../connect.php');

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
    $status = $_POST['status']; // Add this line
    
    // Using the stored procedure to add driver
    $sql = "CALL sp_AddDriver(?, ?, ?, ?, ?, ?, ?, ?)"; // Updated parameter count
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("isssssss", $staff_id, $driver_name, $license_number, 
                    $contact_number, $address, $birthdate, $gender, $status); // Added status
    
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
                        window.location = 'manage_drivers.php';
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

// Query to display drivers - Update to fetch required fields
$query = "SELECT DRIVER_ID, DRIVER_NAME, LICENSE_NUMBER, CONTACT_NUMBER, STATUS FROM driver";
$result = $conn->query($query);

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
                    
                    <form class="grid grid-cols-1 md:grid-cols-2 gap-8" method="POST">
                        <!-- Left Column - Personal Details -->
                        <div class="space-y-6">
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
                                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Personal Information
                                </h4>
                                
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
                                    <input type="date" name="birthdate" required
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
                        <h3 class="text-xl font-semibold">Driver List</h3>
                        <div class="flex space-x-2">
                            <input type="search" placeholder="Search drivers..." 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Drivers list table -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <!-- Table Header -->
                        <div class="grid grid-cols-5 gap-4 p-4 bg-gray-50 border-b border-gray-200">
                            <div class="font-semibold text-gray-600">Driver Name</div>
                            <div class="font-semibold text-gray-600">License Number</div>
                            <div class="font-semibold text-gray-600">Contact</div>
                            <div class="font-semibold text-gray-600">Status</div>
                            <div class="font-semibold text-gray-600">Actions</div>
                        </div>

                        <!-- Driver Entries from Database -->
                        <div class="divide-y divide-gray-200">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <div class="grid grid-cols-5 gap-4 p-4 items-center hover:bg-gray-50">
                                        <div class="flex items-center space-x-3">
                                            <img class="h-10 w-10 rounded-full" 
                                                src="https://ui-avatars.com/api/?name=<?= urlencode($row['DRIVER_NAME']) ?>" 
                                                alt="Driver photo">
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
                                <div class="p-4 text-center text-gray-500">
                                    No drivers found
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

        // Add this after your existing script
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
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