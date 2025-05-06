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
    $status = $_POST['STATUS'];
    $amount = $_POST['AMOUNT'];
    // Removed quantity field

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
        STATUS = ?,
        AMOUNT = ?
        WHERE VEHICLE_ID = ?";

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssissssssdi", 
        $vehicle_type,
        $vehicle_brand,
        $model,
        $year,
        $color,
        $license_plate,
        $vehicle_description,
        $capacity,
        $transmission,
        $status,
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
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Edit Vehicle</h2>
                <p class="text-gray-600">Update vehicle information</p>
            </div>
            <a href="manage_vehicle.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Vehicle List
            </a>
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
                                <select name="VEHICLE_TYPE" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
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
                                <input type="text" 
                                    name="VEHICLE_BRAND" 
                                    required 
                                    value="<?php echo htmlspecialchars($vehicle['VEHICLE_BRAND']); ?>"
                                    pattern="[A-Za-z\s]+"
                                    title="Please enter only letters and spaces"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <!-- Model and Year Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Model</label>
                                <input type="text" 
                                    name="MODEL" 
                                    required 
                                    value="<?php echo htmlspecialchars($vehicle['MODEL']); ?>"
                                    pattern="[A-Za-z\s]+"
                                    title="Please enter only letters and spaces"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Year</label>
                                <input type="text" name="YEAR" required value="<?php echo htmlspecialchars($vehicle['YEAR']); ?>" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
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
                                    value="<?php echo htmlspecialchars($vehicle['COLOR']); ?>"
                                    pattern="[A-Za-z\s]+"
                                    title="Please enter only letters and spaces"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
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

                    <!-- Status Section -->
                    <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                        <h4 class="font-medium text-gray-700">Status</h4>
                        <select name="STATUS" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                            <option value="available" <?php echo $vehicle['STATUS'] == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="rented" <?php echo $vehicle['STATUS'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                            <option value="maintenance" <?php echo $vehicle['STATUS'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
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
</script>
</body>
</html>