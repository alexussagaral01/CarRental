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
            
            <nav class="hidden md:flex">
                <ul class="flex space-x-8">
                    <li><a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">Home</a></li>
                    <li><a href="rent.php" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">Rent</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">Notifications</a></li>
                    <li><a href="details.php" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">About Us</a></li>
                </ul>
            </nav>
            
            <div class="relative">
                <div class="flex items-center bg-gray-100 rounded-full px-4 py-2 focus-within:ring-2 focus-within:ring-blue-500">
                    <input type="text" placeholder="Search vehicles..." class="bg-transparent outline-none text-sm w-40 md:w-48">
                    <button class="ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

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
