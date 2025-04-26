<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Our Vehicles</title>
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
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 w-full">
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
                            <a href="customer_homepage.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="vehicles.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Rent</span>
                            </a>
                        </li>
                        <li>
                            <a href="about.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <!-- User Circle Icon -->
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8.966 8.966 0 0112 15c2.136 0 4.096.747 5.621 1.997M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 1a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>About</span>
                            </a>
                        </li>
                        <li>
                            <a href="contact.php" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-600 font-medium transition-all duration-200 flex items-center space-x-1">
                                <!-- Phone Icon -->
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h1.586a1 1 0 01.707.293l2.828 2.828a1 1 0 010 1.414L9.414 9.414a16.001 16.001 0 006.172 6.172l1.879-1.879a1 1 0 011.414 0l2.828 2.828a1 1 0 01.293.707V19a2 2 0 01-2 2h-1C9.611 21 3 14.389 3 6V5z"/>
                                </svg>
                                <span>Contact</span>
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



    <!-- Search Form -->
    <div class="container mx-auto px-4 py-6">
        <div class="glass-effect rounded-2xl shadow-2xl p-6 mb-8">
            <form class="grid md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <select name="pickup_location" required class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Location</option>
                        <option value="cebu_city">Cebu City</option>
                        <option value="mandaue_city">Mandaue City</option>
                        <option value="lapu_lapu_city">Lapu-Lapu City</option>
                        <option value="talisay_city">Talisay City</option>
                        <option value="consolacion">Consolacion</option>
                        <option value="liloan">Liloan</option>
                        <option value="minglanilla">Minglanilla</option>
                        <option value="naga_city">Naga City</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pick-up Date</label>
                    <input type="datetime-local" name="pickup_datetime" id="pickup_datetime" required 
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Return Date</label>
                    <input type="datetime-local" name="return_datetime" id="return_datetime" required 
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                    <select id="capacity_filter" onchange="filterByCapacity(this.value)"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Capacities</option>
                        <option value="4-5">4-5 Seater</option>
                        <option value="7-8">7-8 Seater</option>
                        <option value="10-18">10-18 Seater</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-2 rounded-lg hover:opacity-90 transition duration-300">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vehicle Grid -->
    <div class="container mx-auto px-4 pb-12">
        <div class="grid md:grid-cols-3 gap-6">
            <?php
            $vehicles = [
                // 4-5 Seater Vehicles
                ['name' => 'Geely Coolray', 'capacity' => '4-5', 'transmission' => 'Automatic', 'price' => '3,500', 
                'image' => 'GeelyCoolray.jpg', 'brand' => 'Geely', 'model' => 'Coolray', 'year' => '2023', 
                'color' => 'Blue', 'plate_no' => 'ABC 123', 'description' => 'Compact SUV with premium features and comfortable interior'],
                
                ['name' => 'Honda City', 'capacity' => '4-5', 'transmission' => 'Automatic', 'price' => '2,800', 
                'image' => 'HondaCity.jpg', 'brand' => 'Honda', 'model' => 'City', 'year' => '2023', 
                'color' => 'Silver', 'plate_no' => 'DEF 456', 'description' => 'Reliable sedan with excellent fuel efficiency'],
                
                ['name' => 'Hyundai Accent', 'capacity' => '4-5', 'transmission' => 'Automatic', 'price' => '2,800', 
                'image' => 'HyundaiAccent.jpg', 'brand' => 'Hyundai', 'model' => 'Accent', 'year' => '2023', 
                'color' => 'White', 'plate_no' => 'GHI 789', 'description' => 'Stylish sedan with advanced safety features'],

                ['name' => 'Kia Soluto', 'capacity' => '4-5', 'transmission' => 'Automatic', 'price' => '2,700', 
                'image' => 'Kia-Soluto.jpg', 'brand' => 'Kia', 'model' => 'Soluto', 'year' => '2023', 
                'color' => 'Red', 'plate_no' => 'JKL 012', 'description' => 'Economical sedan perfect for city driving'],
                
                ['name' => 'Mazda Hatchback', 'capacity' => '4-5', 'transmission' => 'Automatic', 'price' => '3,000', 
                'image' => 'MazdaHatchback.jpeg', 'brand' => 'Mazda', 'model' => '3 Hatchback', 'year' => '2023', 
                'color' => 'Gray', 'plate_no' => 'MNO 345', 'description' => 'Sporty hatchback with premium amenities'],
                
                ['name' => 'Mitsubishi Mirage', 'capacity' => '4-5', 'transmission' => 'Manual', 'price' => '2,500', 
                'image' => 'MitsubishiMirage.jpg', 'brand' => 'Mitsubishi', 'model' => 'Mirage', 'year' => '2023', 
                'color' => 'Orange', 'plate_no' => 'PQR 678', 'description' => 'Fuel-efficient hatchback ideal for city commuting'],
                
                // 7-8 Seater Vehicles
                ['name' => 'BAIC M50S', 'capacity' => '7-8', 'transmission' => 'Manual', 'price' => '3,500', 
                'image' => 'BAICM50S.jpg', 'brand' => 'BAIC', 'model' => 'M50S', 'year' => '2023', 
                'color' => 'Silver', 'plate_no' => 'STU 901', 'description' => 'Practical MPV with spacious interior'],
                
                ['name' => 'Chery Tiggo', 'capacity' => '7-8', 'transmission' => 'Automatic', 'price' => '4,000', 
                'image' => 'CheryTiggo.jpg', 'brand' => 'Chery', 'model' => 'Tiggo', 'year' => '2023', 
                'color' => 'Black', 'plate_no' => 'VWX 234', 'description' => 'Modern SUV with advanced tech features'],

                ['name' => 'Geely Okavango', 'capacity' => '7-8', 'transmission' => 'Automatic', 'price' => '4,300', 
                'image' => 'GeelyOkavango.jpg', 'brand' => 'Geely', 'model' => 'Okavango', 'year' => '2023', 
                'color' => 'Blue', 'plate_no' => 'YZA 567', 'description' => 'Luxurious 7-seater with hybrid technology'],
                
                ['name' => 'Foton Gratour', 'capacity' => '7-8', 'transmission' => 'Manual', 'price' => '3,800', 
                'image' => 'FotonGratour.jpg', 'brand' => 'Foton', 'model' => 'Gratour', 'year' => '2023', 
                'color' => 'White', 'plate_no' => 'BCD 890', 'description' => 'Versatile MPV perfect for family trips'],
                
                ['name' => 'Honda BR-V', 'capacity' => '7-8', 'transmission' => 'Automatic', 'price' => '4,200', 
                'image' => 'Honda-BR-V.jpg', 'brand' => 'Honda', 'model' => 'BR-V', 'year' => '2023', 
                'color' => 'Red', 'plate_no' => 'EFG 123', 'description' => 'Reliable SUV with excellent safety features'],
                
                ['name' => 'Hyundai Stargazer', 'capacity' => '7-8', 'transmission' => 'Automatic', 'price' => '4,500', 
                'image' => 'HyundaiStargazer.jpg', 'brand' => 'Hyundai', 'model' => 'Stargazer', 'year' => '2023', 
                'color' => 'Silver', 'plate_no' => 'HIJ 456', 'description' => 'Modern MPV with futuristic design'],
                
                // 10-18 Seater Vehicles
                ['name' => 'BAIC MZ45', 'capacity' => '10-18', 'transmission' => 'Manual', 'price' => '5,000', 
                'image' => 'BAIC-MZ45.jpg', 'brand' => 'BAIC', 'model' => 'MZ45', 'year' => '2023', 
                'color' => 'White', 'plate_no' => 'KLM 789', 'description' => 'Spacious van ideal for group travel'],
                
                ['name' => 'DFSK EC35 Van', 'capacity' => '10-18', 'transmission' => 'Manual', 'price' => '4,800', 
                'image' => 'DFSK-EC35-Van.png', 'brand' => 'DFSK', 'model' => 'EC35', 'year' => '2023', 
                'color' => 'White', 'plate_no' => 'NOP 012', 'description' => 'Electric van with zero emissions'],

                ['name' => 'Ford Transit', 'capacity' => '10-18', 'transmission' => 'Manual', 'price' => '5,500', 
                'image' => 'FordTransit.jpg', 'brand' => 'Ford', 'model' => 'Transit', 'year' => '2023', 
                'color' => 'Silver', 'plate_no' => 'QRS 345', 'description' => 'Versatile van with premium comfort'],

                ['name' => 'Foton Toano', 'capacity' => '10-18', 'transmission' => 'Manual', 'price' => '5,300', 
                'image' => 'FotonToano.jpg', 'brand' => 'Foton', 'model' => 'Toano', 'year' => '2023', 
                'color' => 'Black', 'plate_no' => 'TUV 678', 'description' => 'Luxury van with executive amenities'],
                
                ['name' => 'Foton Traveller', 'capacity' => '10-18', 'transmission' => 'Manual', 'price' => '5,200', 
                'image' => 'FotonTraveller.jpg', 'brand' => 'Foton', 'model' => 'Traveller', 'year' => '2023', 
                'color' => 'White', 'plate_no' => 'WXY 901', 'description' => 'Comfortable van for long-distance travel'],
                
                ['name' => 'Hyundai H-350', 'capacity' => '10-18', 'transmission' => 'Manual', 'price' => '6,000', 
                'image' => 'Hyundai-H-350.jpg', 'brand' => 'Hyundai', 'model' => 'H-350', 'year' => '2023', 
                'color' => 'Silver', 'plate_no' => 'ZAB 234', 'description' => 'Premium van with maximum comfort and space']
            ];

            foreach ($vehicles as $index => $vehicle) {
                $folder = $vehicle['capacity'] . " Vehicle Capacity";
                echo <<<HTML
                <div class="vehicle-card bg-white rounded shadow-sm" data-capacity="{$vehicle['capacity']}">
                    <img src="../VEHICLE RENTAL -DB/{$folder}/{$vehicle['image']}" alt="{$vehicle['name']}" class="w-full h-48 object-cover rounded-t">
                    <div class="p-4">
                        <h3 class="font-bold">{$vehicle['name']}</h3>
                        <p class="text-gray-600 text-sm">{$vehicle['capacity']} Seater • {$vehicle['transmission']}</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-blue-600">₱{$vehicle['price']}/day</span>
                            <button onclick="openModal($index)" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                HTML;
            }
            ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="vehicleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl max-w-lg w-full mx-auto relative">
            <!-- Close button -->
            <button onclick="closeModal()" class="absolute top-2 right-2 z-10 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <img id="modalImage" class="w-full h-56 object-cover rounded-t-xl" src="" alt="">
            <div class="p-6">
                <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Brand</p>
                        <p id="modalBrand" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Model</p>
                        <p id="modalModel" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Year</p>
                        <p id="modalYear" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Color</p>
                        <p id="modalColor" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Plate No</p>
                        <p id="modalPlate" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Capacity</p>
                        <p id="modalCapacity" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Transmission</p>
                        <p id="modalTransmission" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Price per Day</p>
                        <p id="modalPrice" class="font-semibold text-blue-600"></p>
                    </div>
                </div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500 mb-1">Description</p>
                    <p id="modalDescription" class="text-gray-700 mb-4"></p>
                    <a href="customer_info.php" id="bookNowLink" class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2.5 rounded-lg text-center font-semibold hover:opacity-90 transition-colors">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function filterByCapacity(capacity) {
        const vehicles = document.querySelectorAll('.vehicle-card');
        vehicles.forEach(vehicle => {
            if (capacity === 'all') {
                vehicle.style.display = 'block';
            } else {
                const vehicleCapacity = vehicle.getAttribute('data-capacity');
                vehicle.style.display = vehicleCapacity === capacity ? 'block' : 'none';
            }
        });
    }

    // Set min datetime for pickup and return
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('pickup_datetime').min = now.toISOString().slice(0,16);
    document.getElementById('return_datetime').min = now.toISOString().slice(0,16);

    document.getElementById('pickup_datetime').addEventListener('change', function() {
        document.getElementById('return_datetime').min = this.value;
    });

    const vehicles = <?php echo json_encode($vehicles); ?>;
    const modal = document.getElementById('vehicleModal');

    function openModal(index) {
        const vehicle = vehicles[index];
        document.getElementById('modalTitle').textContent = vehicle.name;
        document.getElementById('modalBrand').textContent = vehicle.brand;
        document.getElementById('modalModel').textContent = vehicle.model;
        document.getElementById('modalYear').textContent = vehicle.year;
        document.getElementById('modalColor').textContent = vehicle.color;
        document.getElementById('modalPlate').textContent = vehicle.plate_no;
        document.getElementById('modalCapacity').textContent = vehicle.capacity + ' Seater';
        document.getElementById('modalTransmission').textContent = vehicle.transmission;
        document.getElementById('modalPrice').textContent = '₱' + vehicle.price;
        document.getElementById('modalDescription').textContent = vehicle.description;
        document.getElementById('modalImage').src = `../VEHICLE RENTAL -DB/${vehicle.capacity} Vehicle Capacity/${vehicle.image}`;

        // Update Book Now link with vehicle data
        const bookNowLink = document.getElementById('bookNowLink');
        const params = new URLSearchParams({
            vehicle_id: index,
            name: vehicle.name,
            price: vehicle.price,
            capacity: vehicle.capacity,
            image: vehicle.image
        });
        bookNowLink.href = `customer_info.php?${params.toString()}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">RentWheels</h3>
                    <p class="text-gray-400">Your trusted partner for quality vehicle rentals.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Email: info@rentwheels.com</li>
                        <li>Phone: (123) 456-7890</li>
                        <li>Address: 123 Rental Street</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; 2024 RentWheels. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>