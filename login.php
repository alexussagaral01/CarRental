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
    <!-- Navigation Bar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M8 17h8M8 17v-4m8 4v-4m-8 4h8m-8-4h8M4 11l2-6h12l2 6M4 11h16M4 11v6h16v-6" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 17h2M16 17h2" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-2xl font-bold text-gray-800">RentWheels</h1>
                    <span class="text-xs text-blue-600 font-medium -mt-1">Premium Car Rental</span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="container mx-auto px-6 h-[calc(100vh-4rem)] flex items-center justify-center mt-1">
        <div class="glass-effect w-full max-w-6xl rounded-2xl shadow-2xl p-8 grid md:grid-cols-2 gap-12">
            <!-- Login Form -->
            <div class="space-y-8">
                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Welcome Back</h1>
                    <p class="text-gray-600">Sign in to continue your journey with us</p>
                </div>
                
                <form class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Username</label>
                        <input 
                            type="text" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                            placeholder="Enter your username"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Password</label>
                        <input 
                            type="password" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                            placeholder="Enter your password"
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
                    
                    <p class="text-center text-gray-600">
                        Don't have an account? 
                        <a href="#" class="text-blue-600 hover:text-blue-800">Sign up</a>
                    </p>
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