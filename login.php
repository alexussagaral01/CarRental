<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // First check for default admin credentials
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        header("Location: admin/admin_dashboard.php");
        exit();
    }
    
    // If not default admin, check admin table using stored procedure
    $sql = "CALL sp_get_admin_login(?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        header("Location: admin/admin_dashboard.php");
        exit();
    } else {
        $error_message = "Invalid username or password";
    }
    
    // Close the previous statement before the next query
    $stmt->close();
    // Free the result
    $result->close();
    // Reset the connection to allow new queries
    $conn->next_result();
    
    // If not admin, check staff table using stored procedure
    $sql = "CALL sp_get_staff_login(?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        $_SESSION['staff'] = true;
        $_SESSION['staff_id'] = $staff['STAFF_ID'];
        $_SESSION['username'] = $username;
        header("Location: staff/staff_dashboard.php");
        exit();
    } else {
        $error_message = "Invalid username or password";
    }

    $stmt->close();
    $result->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
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
            </div>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-20"></div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-6 h-[calc(100vh-4rem)] flex items-center justify-center mt-1">
        <div class="glass-effect w-full max-w-6xl rounded-2xl shadow-2xl p-8 grid md:grid-cols-2 gap-12">
            <!-- Login Form -->
            <div class="space-y-8">
                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Welcome Back</h1>
                    <p class="text-gray-600">Sign in to continue your journey with us</p>
                </div>
                
                <form method="POST" action="" class="space-y-6">
                    <?php if (isset($error_message)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline"><?php echo $error_message; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Username</label>
                        <input 
                            type="text" 
                            name="username"
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                            placeholder="Enter your username"
                            required
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Password</label>
                        <input 
                            type="password"
                            name="password" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Forgot password?</a>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-lg font-medium hover:opacity-90 transition-opacity duration-300 transform hover:scale-[0.99]"
                    >
                        Sign In
                    </button>
                </form>
            </div>
            
            <!-- Image Section -->
            <div class="hidden md:block relative">
                <div class="carousel relative rounded-xl overflow-hidden h-full">
                    <div class="carousel-inner transition-transform duration-500 ease-in-out h-full">
                        <img src="img/toyota.jpg" alt="Toyota" class="w-full h-full object-cover">
                        <img src="img/hyundai.jpg" alt="Hyundai" class="w-full h-full object-cover hidden">
                        <img src="img/mercedes.jpg" alt="Mercedes" class="w-full h-full object-cover hidden">
                        <img src="img/mitsubishi.jpg" alt="Mitsubishi" class="w-full h-full object-cover hidden">
                        <img src="img/kia.jpg" alt="Kia" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 text-white">
                        <h2 class="text-2xl font-bold">Premium Car Rental</h2>
                        <p class="text-sm opacity-90">Experience luxury and comfort on your journey</p>
                    </div>
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                        <button class="w-2 h-2 rounded-full bg-white opacity-50 carousel-dot active"></button>
                        <button class="w-2 h-2 rounded-full bg-white opacity-50 carousel-dot"></button>
                        <button class="w-2 h-2 rounded-full bg-white opacity-50 carousel-dot"></button>
                        <button class="w-2 h-2 rounded-full bg-white opacity-50 carousel-dot"></button>
                        <button class="w-2 h-2 rounded-full bg-white opacity-50 carousel-dot"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carouselInner = document.querySelector('.carousel-inner');
            const images = carouselInner.querySelectorAll('img');
            const dots = document.querySelectorAll('.carousel-dot');
            let currentIndex = 0;

            function showImage(index) {
                images.forEach(img => img.classList.add('hidden'));
                dots.forEach(dot => dot.classList.remove('opacity-100'));
                
                images[index].classList.remove('hidden');
                dots[index].classList.add('opacity-100');
            }

            function nextImage() {
                currentIndex = (currentIndex + 1) % images.length;
                showImage(currentIndex);
            }

            // Auto slide every 3 seconds
            setInterval(nextImage, 3000);

            // Initialize dots click handlers
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentIndex = index;
                    showImage(currentIndex);
                });
            });

            // Show first image
            showImage(0);
        });
    </script>
</body>
</html>