<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Available</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
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
    <main class="container mx-auto px-4 py-6">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl overflow-hidden mb-8">
            <div class="p-8 md:p-12">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Find Your Perfect Ride</h1>
                <p class="text-blue-100 text-lg mb-6">Choose from our premium selection of vehicles for any occasion</p>
                <div class="flex space-x-4">
                    <button class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">View Fleet</button>
                    <button class="border border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors">Learn More</button>
                </div>
            </div>
        </div>

        <!-- Rental Form Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Rental Details</h2>
                    <p class="text-gray-500 mt-1">Find available vehicles for your trip</p>
                </div>
                <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium">Best Rates</span>
            </div>
            
            <!-- Rental Form -->
            <div class="space-y-8">
                <!-- Location and Dates -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pickup Location</label>
                        <div class="relative">
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                                <option>Select Location</option>
                                <option>Cebu City Downtown</option>
                                <option>Mactan Airport</option>
                                <option>Mandaue City</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <div class="relative">
                            <input type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Return Date</label>
                        <div class="relative">
                            <input type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Additional Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option>All Types</option>
                            <option>Sedan</option>
                            <option>SUV</option>
                            <option>Van</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transmission</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option>Any</option>
                            <option>Automatic</option>
                            <option>Manual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <option>All Prices</option>
                            <option>₱1000 - ₱2000</option>
                            <option>₱2000 - ₱3000</option>
                            <option>₱3000+</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex justify-end">
                    <button onclick="showVehicles()" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search Available Vehicles
                    </button>
                </div>

                <!-- Static Vehicle Cards -->
                <div id="vehicleCards" class="hidden grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
                    <!-- Mercedes Card -->
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <div class="relative">
                            <img src="img/mercedes.jpg" alt="Mercedes" class="w-full h-48 object-cover">
                            <div class="absolute top-4 right-4">
                                <span class="bg-black/70 text-white px-3 py-1 rounded-full text-xs font-medium">Premium</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Mercedes</h3>
                                    <p class="text-gray-500 text-sm">Luxury Sedan</p>
                                </div>
                                <span class="text-xl font-bold text-blue-600">₱550</span>
                            </div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">5 seats</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">Auto</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Per hour</span>
                                <button onclick="showModal('mercedes')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors duration-200">
                                    View
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hyundai Card -->
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <div class="relative">
                            <img src="img/hyundai.jpg" alt="Hyundai" class="w-full h-48 object-cover">
                            <div class="absolute top-4 right-4">
                                <span class="bg-black/70 text-white px-3 py-1 rounded-full text-xs font-medium">Popular</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Hyundai</h3>
                                    <p class="text-gray-500 text-sm">Comfort Sedan</p>
                                </div>
                                <span class="text-xl font-bold text-blue-600">₱600</span>
                            </div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">5 seats</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">Auto</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Per hour</span>
                                <button onclick="showModal('hyundai')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors duration-200">
                                    View
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Kia Card -->
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <div class="relative">
                            <img src="img/kia.jpg" alt="Kia" class="w-full h-48 object-cover">
                            <div class="absolute top-4 right-4">
                                <span class="bg-black/70 text-white px-3 py-1 rounded-full text-xs font-medium">Economic</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Kia</h3>
                                    <p class="text-gray-500 text-sm">Economy Sedan</p>
                                </div>
                                <span class="text-xl font-bold text-blue-600">₱450</span>
                            </div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">5 seats</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">Auto</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Per hour</span>
                                <button onclick="showModal('kia')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors duration-200">
                                    View
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mitsubishi Card -->
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <div class="relative">
                            <img src="img/mitsubishi.jpg" alt="Land Cruiser" class="w-full h-48 object-cover">
                            <div class="absolute top-4 right-4">
                                <span class="bg-black/70 text-white px-3 py-1 rounded-full text-xs font-medium">SUV</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Mitsubishi</h3>
                                    <p class="text-gray-500 text-sm">Luxury SUV</p>
                                </div>
                                <span class="text-xl font-bold text-blue-600">₱500</span>
                            </div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">7 seats</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">Auto</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Per hour</span>
                                <button onclick="showModal('mitsubishi')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors duration-200">
                                    View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of Static Vehicle Cards -->

                <!-- Vehicle Modal -->
                <div id="vehicleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
                    <div class="bg-white rounded-xl max-w-xl w-full mx-4 overflow-hidden"> <!-- Changed from max-w-2xl to max-w-xl -->
                        <!-- Modal Header with close button -->
                        <div class="flex justify-between items-center p-3 border-b"> <!-- Changed padding from p-4 to p-3 -->
                            <h3 class="text-lg font-bold text-gray-900" id="modalTitle"></h3> <!-- Changed from text-xl to text-lg -->
                            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <!-- Changed from w-6 h-6 to w-5 h-5 -->
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <!-- Modal Content -->
                        <div class="p-4"> <!-- Changed padding from p-6 to p-4 -->
                            <img id="modalImage" class="w-full h-48 object-cover rounded-lg mb-4" src="" alt="Vehicle"> <!-- Changed height from h-64 to h-48 -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Year Model</p>
                                    <p id="modalYear" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Color</p>
                                    <p id="modalColor" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">License Plate</p>
                                    <p id="modalPlate" class="font-semibold"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Price</p>
                                    <p id="modalPrice" class="font-semibold text-blue-600"></p>
                                </div>
                            </div>
                            <div class="mb-6">
                                <p class="text-sm text-gray-600">Description</p>
                                <p id="modalDescription" class="text-gray-700"></p>
                            </div>
                            <button onclick="bookNow()" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                BOOK NOW
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Filters -->
                <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                    <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-200 cursor-pointer transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Passenger Capacity
                    </span>
                    <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-200 cursor-pointer transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Duration
                    </span>
                    <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-200 cursor-pointer transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Available Now
                    </span>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showVehicles() {
            const vehicleCards = document.getElementById('vehicleCards');
            vehicleCards.classList.remove('hidden');
            // Smooth scroll to vehicles
            vehicleCards.scrollIntoView({ behavior: 'smooth' });
        }

        // Vehicle data
        const vehicles = {
            'mercedes': {
                title: 'Mercedes Benz C-Class',
                image: 'img/mercedes.jpg',
                year: '2023',
                color: 'Silver',
                plate: 'ABC 123',
                price: '₱550 per hour',
                description: 'Luxury sedan featuring premium leather interior, advanced safety features, and superior comfort for an exceptional driving experience.'
            },
            'hyundai': {
                title: 'Hyundai Sonata',
                image: 'img/hyundai.jpg',
                year: '2022',
                color: 'White',
                plate: 'XYZ 789',
                price: '₱600 per hour',
                description: 'Modern comfort sedan with spacious interior, fuel efficiency, and smart technology features.'
            },
            'kia': {
                title: 'Kia Forte',
                image: 'img/kia.jpg',
                year: '2022',
                color: 'Red',
                plate: 'DEF 456',
                price: '₱450 per hour',
                description: 'Economic and reliable sedan with great fuel efficiency and modern amenities.'
            },
            'mitsubishi': {
                title: 'Mitsubishi Montero',
                image: 'img/mitsubishi.jpg',
                year: '2023',
                color: 'Black',
                plate: 'GHI 789',
                price: '₱500 per hour',
                description: 'Powerful SUV with excellent off-road capabilities and comfortable seating for 7 passengers.'
            }
        };

        function showModal(vehicle) {
            const modal = document.getElementById('vehicleModal');
            const data = vehicles[vehicle];

            // Update modal content
            document.getElementById('modalTitle').textContent = data.title;
            document.getElementById('modalImage').src = data.image;
            document.getElementById('modalYear').textContent = data.year;
            document.getElementById('modalColor').textContent = data.color;
            document.getElementById('modalPlate').textContent = data.plate;
            document.getElementById('modalPrice').textContent = data.price;
            document.getElementById('modalDescription').textContent = data.description;

            // Show modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeModal() {
            const modal = document.getElementById('vehicleModal');
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }

        function bookNow() {
            // Redirect to customer information page
            window.location.href = 'cust_info.php';
        }

        // Close modal when clicking outside
        document.getElementById('vehicleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
    
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
</body>
</html>