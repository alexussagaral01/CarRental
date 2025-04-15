<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Rental Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header Section -->
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
    
    <!-- Hero Section -->
    <div class="container mx-auto px-4 my-12">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between p-8 md:p-12">
                <div class="text-white max-w-xl mb-8 md:mb-0">
                    <h2 class="text-4xl font-bold mb-4">Find Your Perfect Ride</h2>
                    <p class="text-blue-100 text-lg">Discover our extensive fleet of vehicles for any occasion. From luxury to economy, we've got you covered.</p>
                </div>
                <div class="w-full md:w-1/3 relative">
                    <div class="carousel relative rounded-lg shadow-xl overflow-hidden h-[300px] w-full">
                        <div class="carousel-inner transition-transform duration-500 ease-in-out h-full">
                            <img src="img/toyota.jpg" alt="Toyota" class="w-full h-full object-cover">
                            <img src="img/hyundai.jpg" alt="Hyundai" class="w-full h-full object-cover hidden">
                            <img src="img/mercedes.jpg" alt="Mercedes" class="w-full h-full object-cover hidden">
                            <img src="img/mitsubishi.jpg" alt="Mitsubishi" class="w-full h-full object-cover hidden">
                            <img src="img/kia.jpg" alt="Kia" class="w-full h-full object-cover hidden">
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
    </div>

    <!-- Available Cars Section -->
    <div class="container mx-auto px-4 my-16">
        <h2 class="text-3xl font-bold text-center mb-8">Available Cars</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Luxury Car Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform hover:scale-105">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-3">
                    <span class="text-sm font-semibold text-white">Luxury</span>
                </div>
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=800" 
                         alt="Red Sports Car" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Mercedes AMG GT</h3>
                    <div class="flex justify-between items-center mb-4">
                        <p class="text-gray-600">Luxury Sports</p>
                        <span class="text-blue-600 font-bold">$200/day</span>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                            </svg>
                            Automatic
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            4.5/5
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Economy Car Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform hover:scale-105">
                <div class="bg-gradient-to-r from-green-600 to-green-800 p-3">
                    <span class="text-sm font-semibold text-white">Economy</span>
                </div>
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=800" 
                         alt="Blue Sedan" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Toyota Camry</h3>
                    <div class="flex justify-between items-center mb-4">
                        <p class="text-gray-600">Economy Sedan</p>
                        <span class="text-green-600 font-bold">$80/day</span>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                            </svg>
                            Automatic
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            4.8/5
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Premium Car Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform hover:scale-105">
                <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-3">
                    <span class="text-sm font-semibold text-white">Premium</span>
                </div>
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800" 
                         alt="Black Convertible" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">BMW M4 Convertible</h3>
                    <div class="flex justify-between items-center mb-4">
                        <p class="text-gray-600">Premium Convertible</p>
                        <span class="text-purple-600 font-bold">$150/day</span>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                            </svg>
                            Automatic
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            4.7/5
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="container mx-auto px-4 my-24">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Why Choose Us</h2>
            <p class="text-gray-600 text-lg">Experience premium car rental service with unmatched convenience</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Wide Selection -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Wide Selection</h3>
                <p class="text-gray-600">Choose from our extensive fleet of vehicles ranging from economy to luxury cars.</p>
            </div>
            
            <!-- Easy Reservations -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Easy Reservations</h3>
                <p class="text-gray-600">Simple and fast booking process. Reserve your car in less than 2 minutes.</p>
            </div>
            
            <!-- 24/7 Support -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">24/7 Support</h3>
                <p class="text-gray-600">Round-the-clock customer support to assist you whenever you need help.</p>
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <div class="container mx-auto px-4 my-24">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">What Our Customers Say</h2>
            <p class="text-gray-600">Real experiences from our valued customers</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Review 1 -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <img src="https://i.pravatar.cc/150?img=1" alt="Jane Doe" class="w-12 h-12 rounded-full object-cover mr-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Jane Doe</h3>
                        <p class="text-sm text-gray-500">Premium Member</p>
                    </div>
                </div>
                <div class="flex mb-4">
                    <div class="flex items-center">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">"Fantastic service! The car was in perfect condition and the staff was incredibly helpful. Would definitely recommend to anyone!"</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Rented 2 weeks ago</span>
                </div>
            </div>
            
            <!-- Review 2 -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <img src="https://i.pravatar.cc/150?img=2" alt="John Smith" class="w-12 h-12 rounded-full object-cover mr-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">John Smith</h3>
                        <p class="text-sm text-gray-500">Business Traveler</p>
                    </div>
                </div>
                <div class="flex mb-4">
                    <div class="flex items-center">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">"Smooth process from start to finish. The online booking system is very user-friendly and the car was exactly as advertised."</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Rented 1 month ago</span>
                </div>
            </div>
            
            <!-- Review 3 -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <img src="https://i.pravatar.cc/150?img=3" alt="Alice Johnson" class="w-12 h-12 rounded-full object-cover mr-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Alice Johnson</h3>
                        <p class="text-sm text-gray-500">Regular Customer</p>
                    </div>
                </div>
                <div class="flex mb-4">
                    <div class="flex items-center">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">5.0</span>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">"Best rental experience ever! The customer service is outstanding and the vehicles are always in pristine condition."</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Rented 3 months ago</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rental Trends Section -->
    <div class="container mx-auto px-4 my-24">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Most Rented Cars</h2>
            <p class="text-gray-600 text-lg">Monthly Rental Statistics by Brand</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-4xl mx-auto">
            <!-- Static Bar Graph -->
            <div class="relative h-96 w-full">
                <!-- Grid lines -->
                <div class="absolute inset-0 grid grid-rows-5 h-80">
                    <div class="border-b border-gray-200"></div>
                    <div class="border-b border-gray-200"></div>
                    <div class="border-b border-gray-200"></div>
                    <div class="border-b border-gray-200"></div>
                    <div class="border-b border-gray-200"></div>
                </div>

                <!-- Bars Container -->
                <div class="absolute bottom-0 left-0 right-0 flex justify-around items-end h-80 px-4">
                    <!-- Toyota -->
                    <div class="flex flex-col items-center group">
                        <div class="relative">
                            <div class="bg-blue-500 w-16 group-hover:bg-blue-600 transition-all duration-200" style="height: 280px;">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    140 Rentals
                                </div>
                            </div>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-600">Toyota</span>
                    </div>

                    <!-- Hyundai -->
                    <div class="flex flex-col items-center group">
                        <div class="relative">
                            <div class="bg-green-500 w-16 group-hover:bg-green-600 transition-all duration-200" style="height: 220px;">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    110 Rentals
                                </div>
                            </div>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-600">Hyundai</span>
                    </div>

                    <!-- Mercedes -->
                    <div class="flex flex-col items-center group">
                        <div class="relative">
                            <div class="bg-purple-500 w-16 group-hover:bg-purple-600 transition-all duration-200" style="height: 300px;">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-purple-600 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    150 Rentals
                                </div>
                            </div>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-600">Mercedes</span>
                    </div>

                    <!-- Mitsubishi -->
                    <div class="flex flex-col items-center group">
                        <div class="relative">
                            <div class="bg-yellow-500 w-16 group-hover:bg-yellow-600 transition-all duration-200" style="height: 180px;">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-yellow-600 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    90 Rentals
                                </div>
                            </div>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-600">Mitsubishi</span>
                    </div>

                    <!-- Kia -->
                    <div class="flex flex-col items-center group">
                        <div class="relative">
                            <div class="bg-red-500 w-16 group-hover:bg-red-600 transition-all duration-200" style="height: 200px;">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    100 Rentals
                                </div>
                            </div>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-600">Kia</span>
                    </div>
                </div>

                <!-- Y-axis labels -->
                <div class="absolute left-0 top-0 h-80 flex flex-col justify-between text-sm text-gray-600">
                    <span>150</span>
                    <span>120</span>
                    <span>90</span>
                    <span>60</span>
                    <span>30</span>
                    <span>0</span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-800 mb-2">Top Brand</h4>
                    <p class="text-gray-700">Mercedes leads with 150 monthly rentals</p>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-800 mb-2">Total Rentals</h4>
                    <p class="text-gray-700">590 vehicles rented this month</p>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="font-semibold text-green-800 mb-2">Growth Rate</h4>
                    <p class="text-gray-700">15% increase from last month</p>
                </div>
            </div>
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