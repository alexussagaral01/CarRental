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
    <header class="glass-effect fixed w-full z-50 py-4 px-6">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">R</span>
                </div>
                <span class="font-bold text-xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">RentWheels</span>
            </div>
            
            <nav class="hidden md:block">
                <ul class="flex space-x-8">
                    <li><a href="#" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Home</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Vehicle Fleet</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Services</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="container mx-auto px-6 min-h-screen flex items-center justify-center">
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
                <img 
                    src="https://images.unsplash.com/photo-1560031788-6516386c3f1f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=735&q=80" 
                    alt="Luxury car" 
                    class="w-full h-full object-cover rounded-xl"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent rounded-xl"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <h2 class="text-2xl font-bold">Premium Car Rental</h2>
                    <p class="text-sm opacity-90">Experience luxury and comfort on your journey</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>