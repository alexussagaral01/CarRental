<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Information</title>
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

    <div class="max-w-4xl mx-auto my-8">
        <!-- Form Content -->
        <div class="bg-gradient-to-br from-white to-gray-50 shadow-lg rounded-2xl p-8 border border-gray-100">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Customer Information</h2>
                <p class="text-gray-600 text-lg">Please fill in your details below</p>
            </div>
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <!-- Customer Type Dropdown -->
                    <div class="relative group">
                        <label for="customerType" class="block text-sm font-semibold text-gray-700 mb-2">Customer Type</label>
                        <select id="customerType" name="customerType" class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent appearance-none hover:border-blue-400">
                            <option value="" disabled selected>Select Customer Type</option>
                            <option value="individual">Individual Customer</option>
                            <option value="company">Corporate Customer</option>
                            <option value="government">Government Agency</option>
                            <option value="student">Student</option>
                        </select>
                        <div class="absolute right-4 top-[42px] pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <!-- Company Name -->
                    <div class="relative group">
                        <label for="companyName" class="block text-sm font-semibold text-gray-700 mb-2">Company Name</label>
                        <input type="text" id="companyName" name="companyName" placeholder="Enter company name" 
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Last Name -->
                    <div class="relative group">
                        <label for="lastName" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                        <input type="text" id="lastName" name="lastName" placeholder="Enter last name"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- First Name -->
                    <div class="relative group">
                        <label for="firstName" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                        <input type="text" id="firstName" name="firstName" placeholder="Enter first name"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Email -->
                    <div class="relative group">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter email address"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Job Title -->
                    <div class="relative group">
                        <label for="jobTitle" class="block text-sm font-semibold text-gray-700 mb-2">Job Title</label>
                        <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter job title"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Driver License -->
                    <div class="relative group">
                        <label for="driverLicense" class="block text-sm font-semibold text-gray-700 mb-2">Driver License</label>
                        <input type="text" id="driverLicense" name="driverLicense" placeholder="Enter driver license number"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>

                    <!-- Contact Number -->
                    <div class="relative group">
                        <label for="contactNumber" class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                        <input type="tel" id="contactNumber" name="contactNumber" placeholder="Enter contact number"
                            class="block w-full px-4 py-3 text-gray-700 bg-white border border-gray-200 rounded-xl transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400">
                    </div>
                </div>

                <!-- Back and Submit Buttons -->
                <div class="flex justify-between items-center mt-10 pt-6 border-t border-gray-100">
                    <a href="rent.php" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg">
                        Submit Information
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="bg-gray-900 text-gray-300 mt-24">
        <div class="container mx-auto px-4 py-12">
            <!-- Footer content can be added here -->
        </div>
    </footer>
</body>
</html>