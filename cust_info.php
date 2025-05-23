<?php
session_start();
require_once('connect.php');

// Store rental info in session when form is submitted from rent.php
if (isset($_POST['rental_info'])) {
    $_SESSION['rental_info'] = $_POST['rental_info'];
}

// Only validate and show errors if the form was actually submitted from cust_info.php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['customerType'])) {
    // Validate required fields
    $required_fields = ['customerType', 'firstName', 'lastName', 'email', 'contactNumber', 'customerAddress', 'driverLicense', 'driverType'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }

    if (empty($errors)) {
        // Get form data with proper validation
        $customerType = htmlspecialchars(trim($_POST['customerType']));
        $companyName = htmlspecialchars(trim($_POST['companyName'] ?? ''));
        $jobTitle = htmlspecialchars(trim($_POST['jobTitle'] ?? ''));
        $firstName = htmlspecialchars(trim($_POST['firstName']));
        $lastName = htmlspecialchars(trim($_POST['lastName']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $contactNum = preg_replace('/[^0-9+]/', '', $_POST['contactNumber']); // Only keep numbers and + symbol
        $address = htmlspecialchars(trim($_POST['customerAddress']));
        $driverLicense = htmlspecialchars(trim($_POST['driverLicense']));
        $paymentMethod = htmlspecialchars(trim($_POST['paymentMethod']));
        $driverType = htmlspecialchars(trim($_POST['driverType'])); // Add this line

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Validate contact number (basic format check)
        if (!preg_match('/^[0-9+]{10,13}$/', $contactNum)) {
            $errors[] = "Invalid contact number format";
        }

        // Start transaction to ensure data consistency
        mysqli_begin_transaction($conn);
        
        try {
            // Use stored procedure for customer insertion
            $stmt = mysqli_prepare($conn, "CALL sp_insert_customer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssssssss", 
                $customerType,
                $driverType,  // Add this parameter 
                $companyName, 
                $jobTitle, 
                $firstName, 
                $lastName, 
                $email, 
                $contactNum, 
                $address, 
                $driverLicense
            );
            
            $customerResult = mysqli_stmt_execute($stmt);
            
            // After insertion, we need to fetch the customer ID
            // Since sp_insert_customer doesn't return the ID, we need to query for it
            mysqli_stmt_close($stmt);
            
            // Query for customer ID based on unique information
            $getCustomerIdQuery = "SELECT CUSTOMER_ID FROM customer 
                                  WHERE FIRST_NAME = ? AND LAST_NAME = ? AND EMAIL = ? AND DRIVERS_LICENSE = ? 
                                  ORDER BY CUSTOMER_ID DESC LIMIT 1";
            $stmt = mysqli_prepare($conn, $getCustomerIdQuery);
            mysqli_stmt_bind_param($stmt, "ssss", $firstName, $lastName, $email, $driverLicense);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $customerId);
            
            if (!mysqli_stmt_fetch($stmt)) {
                throw new Exception("Failed to retrieve customer ID");
            }
            
            mysqli_stmt_close($stmt);

            // Insert payment information
            $paymentStmt = mysqli_prepare($conn, "INSERT INTO payment (PAYMENT_METHOD, STATUS) VALUES (?, 'Paid')");
            mysqli_stmt_bind_param($paymentStmt, "s", $paymentMethod);
            $paymentResult = mysqli_stmt_execute($paymentStmt);
            $paymentId = mysqli_insert_id($conn);
            mysqli_stmt_close($paymentStmt);

            if (!$paymentResult) {
                throw new Exception("Failed to insert payment information");
            }

            // Get rental details from session
            $rentalInfo = isset($_SESSION['rental_info']) ? json_decode($_SESSION['rental_info'], true) : null;
            
            if ($rentalInfo) {
                $rentalDtlId = isset($_SESSION['rental_dtl_id']) ? $_SESSION['rental_dtl_id'] : 0;
                $vehicleId = isset($_SESSION['vehicle_id']) ? $_SESSION['vehicle_id'] : 
                            (isset($rentalInfo['vehicle_id']) ? $rentalInfo['vehicle_id'] : 0);
                
                if ($rentalDtlId <= 0 || $vehicleId <= 0) {
                    throw new Exception("Missing rental details or vehicle information");
                }
                
                // Call stored procedure to insert rental header
                $rentalHdrStmt = mysqli_prepare($conn, "CALL sp_insert_rental_hdr(?, ?, ?, ?)");
                mysqli_stmt_bind_param($rentalHdrStmt, "iiii", 
                    $rentalDtlId,
                    $customerId,
                    $paymentId,
                    $vehicleId
                );
                
                $rentalHdrResult = mysqli_stmt_execute($rentalHdrStmt);
                
                if (!$rentalHdrResult) {
                    throw new Exception("Failed to create rental header: " . mysqli_stmt_error($rentalHdrStmt));
                }
                
                // Get the rental header ID
                $result = mysqli_stmt_get_result($rentalHdrStmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $_SESSION['rental_hdr_id'] = $row['rental_hdr_id'];
                } else {
                    throw new Exception("Failed to retrieve rental header ID");
                }
                
                mysqli_stmt_close($rentalHdrStmt);
            } else {
                throw new Exception("No rental information found in session");
            }
            
            // Store IDs in session for transaction details page
            $_SESSION['customer_id'] = $customerId;
            $_SESSION['payment_id'] = $paymentId;
            
            // If we reach this point, commit the transaction
            mysqli_commit($conn);
            
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Customer information saved successfully!',
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
                        window.location.href = 'transaction_details.php';
                    });
                });
            </script>";
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred: " . addslashes($e->getMessage()) . "',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Please fill in all required fields: " . implode(", ", $errors) . "',
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            const customerType = document.getElementById('customerType');
            const companyName = document.getElementById('companyName');
            const jobTitle = document.getElementById('jobTitle');

            // Function to toggle fields based on customer type
            function toggleFields() {
                if (customerType.value === 'individual') {
                    companyName.disabled = true;
                    jobTitle.disabled = true;
                    companyName.value = '';
                    jobTitle.value = '';
                    companyName.classList.add('bg-gray-100');
                    jobTitle.classList.add('bg-gray-100');
                    companyName.removeAttribute('required');
                    jobTitle.removeAttribute('required');
                } else if (customerType.value === 'company') {
                    companyName.disabled = false;
                    jobTitle.disabled = false;
                    companyName.classList.remove('bg-gray-100');
                    jobTitle.classList.remove('bg-gray-100');
                    companyName.setAttribute('required', 'required');
                    jobTitle.setAttribute('required', 'required');
                }
            }

            // Add event listener to customer type dropdown
            customerType.addEventListener('change', toggleFields);

            // Initial check on page load
            toggleFields();
        });
    </script>
</head>
<body class="bg-gray-50">
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

                <!-- Navigation - Centered -->
                <div class="flex-1 flex justify-center">
                    <nav class="md:block">
                        <ul class="flex space-x-8 justify-center">
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
                                <a href="details.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>About</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                
                <!-- Empty div for spacing balance -->
                <div class="w-40"></div>
            </div>
        </div>
    </header>
    <!-- Spacer for fixed header -->
    <div class="h-20"></div>
    <div class="max-w-4xl mx-auto my-8">
        <!-- Form Content -->
        <div class="bg-gradient-to-br from-white to-gray-50 shadow-lg rounded-2xl p-8 border border-gray-100">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Customer Information</h2>
                <p class="text-gray-600 text-lg">Please fill in your details below</p>
            </div>
            <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="customerForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <!-- Customer Type Dropdown -->
                    <div class="relative group">
                        <label for="customerType" class="block text-sm font-semibold text-gray-700 mb-2">Customer Type</label>
                        <select id="customerType" name="customerType" required class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent appearance-none hover:border-blue-400">
                            <option value="" disabled selected>Select Customer Type</option>
                            <option value="individual">Individual Customer</option>
                            <option value="company">Corporate Customer</option>
                        </select>
                        <div class="absolute right-4 top-[42px] pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <!-- Company Name -->
                    <div class="relative group">
                        <label for="companyName" class="block text-sm font-semibold text-gray-700 mb-2">Company Name</label>
                        <input type="text" id="companyName" name="companyName" placeholder="Enter company name" 
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Last Name -->
                    <div class="relative group">
                        <label for="lastName" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required placeholder="Enter last name"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- First Name -->
                    <div class="relative group">
                        <label for="firstName" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                        <input type="text" id="firstName" name="firstName" required placeholder="Enter first name"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Email -->
                    <div class="relative group">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="text" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="Enter email address"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Job Title -->
                    <div class="relative group">
                        <label for="jobTitle" class="block text-sm font-semibold text-gray-700 mb-2">Job Title</label>
                        <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter job title"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Driver License -->
                    <div class="relative group">
                        <label for="driverLicense" class="block text-sm font-semibold text-gray-700 mb-2">Driver License</label>
                        <input type="text" id="driverLicense" name="driverLicense" required placeholder="Enter driver license number"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Contact Number -->
                    <div class="relative group">
                        <label for="contactNumber" class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                        <input type="tel" 
                            id="contactNumber" 
                            name="contactNumber" 
                            required 
                            placeholder="Enter contact number"
                            pattern="[0-9]+"
                            maxlength="11"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Customer Address (New Field) -->
                    <div class="relative group">
                        <label for="customerAddress" class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                        <input type="text" id="customerAddress" name="customerAddress" required placeholder="Enter your address"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Payment Method -->
                    <div class="relative group">
                        <label for="paymentMethod" class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                        <select id="paymentMethod" name="paymentMethod" required class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent appearance-none hover:border-blue-400">
                            <option value="" disabled selected>Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                        </select>
                        <div class="absolute right-4 top-[42px] pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <!-- Driver Type Dropdown - Add this section -->
                    <div class="relative group">
                        <label for="driverType" class="block text-sm font-semibold text-gray-700 mb-2">Driver Type</label>
                        <select id="driverType" name="driverType" required class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent appearance-none hover:border-blue-400">
                            <option value="" disabled selected>Select Driver Type</option>
                            <option value="self">Self Drive</option>
                            <option value="with_driver">With Driver</option>
                        </select>
                        <div class="absolute right-4 top-[42px] pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Back and Submit Button - Modify this section -->
                <div class="flex justify-between items-center mt-10 pt-6 border-t border-gray-100">
                    <a href="rent.php" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg">
                        Submit Information
                    </button>
                </div>
            </form>
        </div>
    </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('customerForm');
            const companyNameInput = document.getElementById('companyName');
            const lastNameInput = document.getElementById('lastName');
            const firstNameInput = document.getElementById('firstName');
            const jobTitleInput = document.getElementById('jobTitle');
            const contactNumberInput = document.getElementById('contactNumber');
            const emailInput = document.getElementById('email');

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
                const input = e.target;
                if (input.value.length >= 11 && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Contact number cannot be more than 11 digits',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

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

            // Add event listeners for text inputs
            companyNameInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            lastNameInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            firstNameInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            jobTitleInput.addEventListener('keypress', preventSpecialCharsAndNumbers);
            contactNumberInput.addEventListener('keypress', preventNonNumbers);

            // Add email validation
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                if (email && !email.endsWith('@gmail.com')) {
                    this.value = email + '@gmail.com';
                }
            });

            // Form validation before submit
            form.addEventListener('submit', function(e) {
                const namePattern = /^[A-Za-z\s]+$/;
                const contactPattern = /^[0-9]+$/;
                const email = emailInput.value.trim();

                if (companyNameInput.value && !namePattern.test(companyNameInput.value)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Company name can only contain letters and spaces'
                    });
                    return;
                }

                if (!namePattern.test(lastNameInput.value)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Last name can only contain letters and spaces'
                    });
                    return;
                }

                if (!namePattern.test(firstNameInput.value)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'First name can only contain letters and spaces'
                    });
                    return;
                }

                if (jobTitleInput.value && !namePattern.test(jobTitleInput.value)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Job title can only contain letters and spaces'
                    });
                    return;
                }

                if (!contactPattern.test(contactNumberInput.value)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Contact number can only contain numbers'
                    });
                    return;
                }

                if (contactNumberInput.value.length !== 11) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Contact number must be exactly 11 digits'
                    });
                    return;
                }

                if (!email.endsWith('@gmail.com')) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Email',
                        text: 'Email must end with @gmail.com'
                    });
                    return;
                }
            });
        });
    </script>
</body>
</html>