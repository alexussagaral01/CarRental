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
    $image_path = ''; // Default to empty string - will be handled in update procedure
    
    // Handle image upload
    if(isset($_FILES['driver_image']) && $_FILES['driver_image']['error'] == 0) {
        $upload_dir = '../driver_images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['driver_image']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if(move_uploaded_file($_FILES['driver_image']['tmp_name'], $upload_path)) {
            // Save file path to database (relative path)
            $image_path = 'driver_images/' . $unique_filename;
        } else {
            $message = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Error uploading image!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
                </script>";
        }
    }
    
    $sql = "CALL sp_UpdateDriver(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssssss", 
        $driver_id, $staff_id, $driver_name, $license_number, 
        $contact_number, $address, $birthdate, $gender, $status, $image_path
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
                        window.location = 'manage_drivers.php?tab=driver-list';
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
    <style>
        /* Driver Image Upload Preview */
        #imagePreview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            border: 4px solid #e5e7eb;
            margin: 0 auto 16px auto;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        #imagePreview:hover {
            border-color: #6366f1;
        }
        
        #imagePreview svg {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        #imagePreview:hover svg {
            opacity: 1;
        }
        
        #imagePreviewOverlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        #imagePreview:hover #imagePreviewOverlay {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php if(!empty($message)) echo $message; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Header -->
                <div class="p-6 bg-indigo-600">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-white">Edit Driver</h1>
                        <a href="manage_drivers.php?tab=driver-list" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Driver List
                        </a>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" class="p-6" id="editDriverForm" enctype="multipart/form-data">
                    <!-- Driver Photo Section -->
                    <div class="mb-6 flex justify-center">
                        <div class="text-center">
                            <input type="file" name="driver_image" id="imageInput" accept="image/*" class="hidden">
                            <div id="imagePreview" onclick="document.getElementById('imageInput').click();" 
                                 style="background-image: url('../<?= !empty($driver['IMAGE']) && file_exists('../'.$driver['IMAGE']) ? $driver['IMAGE'] : 'driver_images/default.jpg' ?>');">
                                <?php if(empty($driver['IMAGE']) || !file_exists('../'.$driver['IMAGE'])): ?>
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <?php endif; ?>
                                <div id="imagePreviewOverlay">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Click to update driver photo</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                <input type="date" name="birthdate" id="birthdate" value="<?= htmlspecialchars($driver['BIRTHDATE']) ?>" 
                                       required max="<?= date('Y-m-d') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="Available" <?= $driver['STATUS'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                    <option value="On Duty" <?= $driver['STATUS'] === 'On Duty' ? 'selected' : '' ?>>On Duty</option>
                                    <option value="Assigned" <?= $driver['STATUS'] === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
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
                        <a href="manage_drivers.php?tab=driver-list" 
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
            // Form elements
            const form = document.getElementById('editDriverForm');
            const driverNameInput = form.querySelector('input[name="driver_name"]');
            const contactNumberInput = form.querySelector('input[name="contact_number"]');
            const birthdateInput = document.getElementById('birthdate');
            
            // Image preview functionality
            const imageInput = document.getElementById('imageInput');
            const imagePreview = document.getElementById('imagePreview');
            const imagePreviewOverlay = document.getElementById('imagePreviewOverlay');

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Update the background image of the preview element
                        imagePreview.style.backgroundImage = `url(${e.target.result})`;
                        // Remove any placeholder icon if present
                        while (imagePreview.firstChild) {
                            if (imagePreview.firstChild !== imagePreviewOverlay) {
                                imagePreview.removeChild(imagePreview.firstChild);
                            } else {
                                break;
                            }
                        }
                        // Add a class to indicate an image is selected
                        imagePreview.classList.add('has-image');
                    };
                    
                    reader.readAsDataURL(file);
                }
            });

            // Setup birthdate validation
            
            // Set max date to today to prevent future dates in the date picker
            const today = new Date().toISOString().split('T')[0];
            birthdateInput.setAttribute('max', today);
            
            // Calculate date for minimum age requirement (18 years ago)
            const eighteenYearsAgo = new Date();
            eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);
            const maxBirthdate = eighteenYearsAgo.toISOString().split('T')[0];
            
            // Prevent manual date changes through direct input
            birthdateInput.addEventListener('input', function() {
                const selectedDate = new Date(this.value);
                const currentDate = new Date();
                const minAgeDate = new Date(maxBirthdate);
                
                if (selectedDate > currentDate) {
                    this.value = today;
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Birthdate cannot be in the future',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                
                if (selectedDate > minAgeDate) {
                    this.value = maxBirthdate;
                    Swal.fire({
                        icon: 'error',
                        title: 'Age Requirement',
                        text: 'Driver must be at least 18 years old',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
            
            // Also check on change event for browsers that trigger it instead of input
            birthdateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const currentDate = new Date();
                const minAgeDate = new Date(maxBirthdate);
                
                if (selectedDate > currentDate) {
                    this.value = today;
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Birthdate cannot be in the future',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                
                if (selectedDate > minAgeDate) {
                    this.value = maxBirthdate;
                    Swal.fire({
                        icon: 'error',
                        title: 'Age Requirement',
                        text: 'Driver must be at least 18 years old',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });

            // Input validation functions
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
                const birthdateValue = birthdateInput.value;

                const namePattern = /^[A-Za-z\s]+$/;
                const contactPattern = /^[0-9]+$/;

                // Validate name
                if (!namePattern.test(driverNameValue)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'Driver name can only contain letters and spaces'
                    });
                    return;
                }

                // Validate contact number
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

                // Validate birthdate
                const selectedDate = new Date(birthdateValue);
                const currentDate = new Date();
                
                if (selectedDate > currentDate) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Birthdate cannot be in the future'
                    });
                    return;
                }
                
                // Check if the person is at least 18 years old
                const minAge = 18;
                const minAgeDate = new Date();
                minAgeDate.setFullYear(minAgeDate.getFullYear() - minAge);
                
                if (selectedDate > minAgeDate) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Age Requirement',
                        text: 'Driver must be at least 18 years old to be registered'
                    });
                    return;
                }
            });
        });
    </script>
</body>
</html>
