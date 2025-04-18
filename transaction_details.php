<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Transaction Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }
        @media print {
            .no-print { display: none; }
            body { background: white !important; }
            .glass-effect {
                background-color: white !important;
                backdrop-filter: none;
            }
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

                <!-- Navigation -->
                <nav class="hidden md:block">
                    <ul class="flex space-x-1">
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
                            <a href="#" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span>Notifications</span>
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

                <!-- Search and Profile Section -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search vehicles..." class="w-64 px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <button class="absolute right-3 top-2.5 text-gray-400 hover:text-blue-600 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                Transaction Details
            </h1>
            <p class="text-gray-600">Order Summary and Customer Information</p>
        </div>

        <!-- Content Card -->
        <div class="glass-effect rounded-2xl shadow-2xl p-6 md:p-8">
            <!-- Transaction Information -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Left Column - Rented Vehicles -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-800 pb-2 border-b">Rented Vehicles</h2>
                    
                    <!-- Vehicle 1 -->
                    <div class="bg-white/50 rounded-lg p-4 space-y-2">
                        <h3 class="font-semibold text-gray-800">Honda, Civic</h3>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p>2022, Light-Gray</p>
                            <p>License Plate: <span class="font-medium">XYZ5678</span></p>
                            <p>Description: Reliable and stylish sedan</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-blue-600 font-semibold">₱450.00/Ph</span>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">x1</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle 2 -->
                    <div class="bg-white/50 rounded-lg p-4 space-y-2">
                        <h3 class="font-semibold text-gray-800">Toyota, Camry</h3>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p>2021, Gray</p>
                            <p>License Plate: <span class="font-medium">ABC1234</span></p>
                            <p>Description: Spacious and comfortable sedan</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-blue-600 font-semibold">₱500.00/Ph</span>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">x1</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Customer Details -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-800 pb-2 border-b">Customer Details</h2>
                    <div class="bg-white/50 rounded-lg p-4">
                        <div class="grid gap-3 text-sm">
                            <div>
                                <p class="text-gray-500">Customer Name</p>
                                <p class="font-medium text-gray-800">Catubig, Mark Dave</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Email</p>
                                <p class="font-medium text-gray-800">catubigmarkdave0@gmail.com@gmail.com</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Contact Number</p>
                                <p class="font-medium text-gray-800">09773812852</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Customer Address</p>
                                <p class="font-medium text-gray-800">Buhisan Cebu City</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Company Name</p>
                                <p class="font-medium text-gray-800">None</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Job Title</p>
                                <p class="font-medium text-gray-800">None</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Transaction Date</p>
                                <p class="font-medium text-gray-800">2025-03-14</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Driver Type</p>
                                <p class="font-medium text-gray-800">Self-Drive</p>
                            </div>
                        </div>

                        <!-- Total Amount -->
                        <div class="mt-6 pt-4 border-t">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-800">Total Amount</span>
                                <span class="text-2xl font-bold text-blue-600">₱950.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t">
                <div class="flex space-x-4">
                    <button onclick="window.location.href='rent.php'" class="px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-900 text-white rounded-lg hover:opacity-90 transition-all duration-200 no-print">
                        Book Again
                    </button>
                    <button onclick="alert('Transaction Confirmed')" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:opacity-90 transition-all duration-200 no-print">
                        Confirm
                    </button>
                </div>
                
                <button onclick="window.print()" class="no-print flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:opacity-90 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Receipt</span>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
