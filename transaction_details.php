<?php
session_start();
require_once('connect.php');

// Initialize variables
$customer = null;
$payment = null;
$vehicle = null;
$rentalDetail = null;
$rentalHeader = null;
$driver = null;

// Check if we have rental header ID in session
if(isset($_SESSION['rental_hdr_id'])) {
    $rentalHdrId = $_SESSION['rental_hdr_id'];
    
    // Fetch rental header data
    $sql = "SELECT * FROM rental_hdr WHERE RENTAL_HDR_ID = $rentalHdrId";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $rentalHeader = $result->fetch_assoc();
        
        // Fetch related data
        $customerId = $rentalHeader['CUSTOMER_ID'];
        $paymentId = $rentalHeader['PAYMENT_ID'];
        $vehicleId = $rentalHeader['VEHICLE_ID'];
        $rentalDtlId = $rentalHeader['RENTAL_DTL_ID'];
        
        // Fetch customer data
        $customerSql = "SELECT * FROM customer WHERE CUSTOMER_ID = $customerId";
        $customerResult = $conn->query($customerSql);
        if ($customerResult->num_rows > 0) {
            $customer = $customerResult->fetch_assoc();
            
            // Fetch driver data if customer has assigned driver
            if ($customer['DRIVER_TYPE'] === 'with_driver' && !empty($customer['ASSIGNED_DRIVER_ID'])) {
                $driverId = $customer['ASSIGNED_DRIVER_ID'];
                $driverSql = "SELECT * FROM driver WHERE DRIVER_ID = $driverId";
                $driverResult = $conn->query($driverSql);
                if ($driverResult->num_rows > 0) {
                    $driver = $driverResult->fetch_assoc();
                }
            }
        }
        
        // Fetch payment data
        $paymentSql = "SELECT * FROM payment WHERE PAYMENT_ID = $paymentId";
        $paymentResult = $conn->query($paymentSql);
        if ($paymentResult->num_rows > 0) {
            $payment = $paymentResult->fetch_assoc();
        }
        
        // Fetch vehicle data
        $vehicleSql = "SELECT * FROM vehicle WHERE VEHICLE_ID = $vehicleId";
        $vehicleResult = $conn->query($vehicleSql);
        if ($vehicleResult->num_rows > 0) {
            $vehicle = $vehicleResult->fetch_assoc();
        }
        
        // Fetch rental detail data
        $rentalDetailSql = "SELECT * FROM rental_dtl WHERE RENTAL_DTL_ID = $rentalDtlId";
        $rentalDetailResult = $conn->query($rentalDetailSql);
        if ($rentalDetailResult->num_rows > 0) {
            $rentalDetail = $rentalDetailResult->fetch_assoc();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Transaction Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add jsPDF libraries for export functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Updated PDF export CSS -->
    <style>
        .print-only { display: none; }
        @media print {
            .print-only { display: block; }
            .no-print { display: none; }
            body { 
                font-family: Arial, sans-serif; 
                color: #000;
            }
            .print-container {
                padding: 20px;
                max-width: 800px;
                margin: 0 auto;
            }
        }
        
        /* PDF export layout styles */
        .pdf-content {
            font-family: 'Helvetica', sans-serif;
        }
        
        .pdf-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .pdf-columns {
            display: flex;
            justify-content: space-between;
        }
        
        .pdf-column {
            width: 48%;
        }
        
        .pdf-section {
            margin-bottom: 15px;
        }
        
        .pdf-section-title {
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100">
    <!-- Modern Header Section -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 fixed w-full top-0 z-50 no-print">
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
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <!-- Spacer for fixed header -->
    <div class="h-20 no-print"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6 max-w-5xl" id="transaction-content">
        <?php if ($rentalHeader && $customer && $vehicle && $rentalDetail && $payment): ?>
            <!-- Company Logo for Print Version -->
            <div class="print-only mb-8 text-center">
                <h1 class="text-2xl font-bold">RentWheels Premium Car Rental</h1>
                <p>123 Main Street, Cebu City | info@rentwheels.com | +63 912 345 6789</p>
                <hr class="my-4">
            </div>

            <!-- Success Message and Transaction ID Banner -->
            <div class="bg-green-100 border-l-4 border-green-500 rounded-lg p-4 flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-green-700">Booking Successful!</h2>
                    <p class="text-green-600">Your booking has been confirmed with the following details.</p>
                </div>
                <div class="no-print">
                    <button onclick="exportTransaction()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export Details</span>
                    </button>
                </div>
            </div>
            
            <!-- Transaction Reference -->
            <div class="mb-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-gray-700">Transaction Reference</h3>
                            <p class="text-2xl font-bold text-blue-600 mt-1">TXN-<?php echo str_pad($rentalHeader['RENTAL_HDR_ID'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Transaction Date</p>
                            <p class="font-medium"><?php echo date('F d, Y', strtotime($rentalHeader['DATE_CREATED'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transaction Details in 2 Columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Customer Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Customer Information</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($customer['FIRST_NAME'] . ' ' . $customer['LAST_NAME']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium"><?php echo htmlspecialchars($customer['EMAIL']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Contact Number</p>
                            <p class="font-medium"><?php echo htmlspecialchars($customer['CONTACT_NUM']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-medium"><?php echo htmlspecialchars($customer['CUSTOMER_ADDRESS']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Driver Type</p>
                            <p class="font-medium">
                                <?php echo $customer['DRIVER_TYPE'] === 'self' ? 'Self Drive' : 'With Driver'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Vehicle Information</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Vehicle</p>
                            <p class="font-medium"><?php echo htmlspecialchars($vehicle['VEHICLE_BRAND'] . ' ' . $vehicle['MODEL']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Type</p>
                            <p class="font-medium"><?php echo htmlspecialchars($vehicle['VEHICLE_TYPE']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Color</p>
                            <p class="font-medium"><?php echo htmlspecialchars($vehicle['COLOR']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">License Plate</p>
                            <p class="font-medium"><?php echo htmlspecialchars($vehicle['LICENSE_PLATE']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Transmission</p>
                            <p class="font-medium"><?php echo htmlspecialchars($vehicle['TRANSMISSION']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rental Details -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Rental Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Pickup Location</p>
                        <p class="font-medium"><?php echo htmlspecialchars($rentalDetail['PICKUP_LOCATION']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Start Date/Time</p>
                        <p class="font-medium"><?php echo date('F d, Y h:i A', strtotime($rentalDetail['START_DATE'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">End Date/Time</p>
                        <p class="font-medium"><?php echo date('F d, Y h:i A', strtotime($rentalDetail['END_DATE'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Duration</p>
                        <p class="font-medium">
                            <?php 
                                $hours = $rentalDetail['DURATION'];
                                if ($hours >= 24) {
                                    $days = floor($hours / 24);
                                    $remaining_hours = $hours % 24;
                                    echo $days . ' ' . ($days == 1 ? 'Day' : 'Days');
                                    if ($remaining_hours > 0) {
                                        echo ', ' . $remaining_hours . ' ' . ($remaining_hours == 1 ? 'Hour' : 'Hours');
                                    }
                                } else {
                                    echo $hours . ' ' . ($hours == 1 ? 'Hour' : 'Hours');
                                }
                            ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Rate</p>
                        <p class="font-medium">₱<?php echo number_format($rentalDetail['HOURLY_RATE'], 2); ?> per hour</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment Method</p>
                        <p class="font-medium"><?php echo ucfirst($payment['PAYMENT_METHOD']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Payment Summary</h3>
                <div class="border-t border-b border-gray-100 py-4 mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Rental Amount (<?php echo $rentalDetail['DURATION']; ?> hours × ₱<?php echo number_format($rentalDetail['HOURLY_RATE'], 2); ?>)</span>
                        <span class="font-medium">₱<?php echo number_format($rentalDetail['TOTAL_AMOUNT'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">VAT (12%)</span>
                        <span class="font-medium">₱<?php echo number_format($rentalDetail['VAT_AMOUNT'], 2); ?></span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">Total Amount</span>
                    <span class="text-xl font-bold text-blue-600">₱<?php echo number_format($rentalDetail['LINE_TOTAL'], 2); ?></span>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="mt-8 text-center text-gray-600 text-sm">
                <p>Thank you for choosing RentWheels Premium Car Rental.</p>
                <p>For inquiries or assistance, please contact our customer support at (032) 123-4567.</p>
            </div>
            
        <?php else: ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-bold text-yellow-700">Transaction information not found</h2>
                <p class="text-yellow-600">We couldn't find complete details for this transaction. Please contact customer support.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function exportTransaction() {
            // Show loading message
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-50 z-50';
            loadingMessage.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><p class="text-lg font-bold text-blue-600">Preparing document for export...</p></div>';
            document.body.appendChild(loadingMessage);

            // Use jsPDF to create a PDF with two-column layout
            const { jsPDF } = window.jspdf;
            
            // Create PDF document
            const pdf = new jsPDF();
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 20;
            const columnWidth = (pageWidth - margin * 3) / 2;
            
            // Add logo and title
            pdf.setFontSize(20);
            pdf.setTextColor(0, 51, 153);
            pdf.text("RentWheels Premium Car Rental", pageWidth / 2, margin, { align: "center" });
            
            pdf.setFontSize(12);
            pdf.setTextColor(100, 100, 100);
            pdf.text("Transaction Receipt", pageWidth / 2, margin + 8, { align: "center" });
            
            // Add transaction reference
            pdf.setFontSize(14);
            pdf.setTextColor(0, 0, 0);
            pdf.text("Transaction Reference: TXN-<?php echo str_pad($rentalHeader['RENTAL_HDR_ID'], 6, '0', STR_PAD_LEFT); ?>", pageWidth / 2, margin + 18, { align: "center" });
            
            // Add transaction date
            pdf.setFontSize(10);
            pdf.setTextColor(100, 100, 100);
            pdf.text("Generated on: <?php echo date('F d, Y', strtotime($rentalHeader['DATE_CREATED'])); ?>", pageWidth / 2, margin + 25, { align: "center" });
            
            // Add horizontal line
            pdf.setDrawColor(220, 220, 220);
            pdf.line(margin, margin + 30, pageWidth - margin, margin + 30);
            
            let yPos = margin + 40;
            
            // LEFT COLUMN - RENTAL DETAILS
            pdf.setFontSize(12);
            pdf.setTextColor(0, 102, 204);
            pdf.setFont(undefined, 'bold');
            pdf.text("Rental Details", margin, yPos);
            pdf.setDrawColor(0, 102, 204);
            pdf.line(margin, yPos + 2, margin + columnWidth - 10, yPos + 2);
            
            // Rental information
            pdf.setFont(undefined, 'normal');
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            yPos += 10;
            
            // Vehicle information
            pdf.setFontSize(11);
            pdf.setTextColor(80, 80, 80);
            pdf.setFont(undefined, 'bold');
            pdf.text("Vehicle Information:", margin, yPos);
            pdf.setFont(undefined, 'normal');
            
            yPos += 7;
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            pdf.text("Vehicle: <?php echo htmlspecialchars($vehicle['VEHICLE_BRAND'] . ' ' . $vehicle['MODEL']); ?>", margin, yPos);
            
            yPos += 6;
            pdf.text("Type: <?php echo htmlspecialchars($vehicle['VEHICLE_TYPE']); ?>", margin, yPos);
            
            yPos += 6;
            pdf.text("Year: <?php echo htmlspecialchars($vehicle['YEAR']); ?>", margin, yPos);
            
            yPos += 6;
            pdf.text("Color: <?php echo htmlspecialchars($vehicle['COLOR']); ?>", margin, yPos);
            
            yPos += 6;
            pdf.text("License Plate: <?php echo htmlspecialchars($vehicle['LICENSE_PLATE']); ?>", margin, yPos);
            
            yPos += 6;
            pdf.text("Transmission: <?php echo htmlspecialchars($vehicle['TRANSMISSION']); ?>", margin, yPos);
            
            yPos += 6;
            pdf.text("Capacity: <?php echo htmlspecialchars($vehicle['CAPACITY']); ?> Persons", margin, yPos);
            
            // Schedule information
            var scheduleSectionY = yPos + 12;
            pdf.setFontSize(11);
            pdf.setTextColor(80, 80, 80);
            pdf.setFont(undefined, 'bold');
            pdf.text("Schedule Information:", margin, scheduleSectionY);
            pdf.setFont(undefined, 'normal');
            
            scheduleSectionY += 7;
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            pdf.text("Pickup Location: <?php echo htmlspecialchars($rentalDetail['PICKUP_LOCATION']); ?>", margin, scheduleSectionY);
            
            scheduleSectionY += 6;
            pdf.text("Start Date: <?php echo date('F d, Y h:i A', strtotime($rentalDetail['START_DATE'])); ?>", margin, scheduleSectionY);
            
            scheduleSectionY += 6;
            pdf.text("End Date: <?php echo date('F d, Y h:i A', strtotime($rentalDetail['END_DATE'])); ?>", margin, scheduleSectionY);
            
            scheduleSectionY += 6;
            pdf.text("Duration: <?php 
                $hours = $rentalDetail['DURATION'];
                if ($hours >= 24) {
                    $days = floor($hours / 24);
                    $remaining_hours = $hours % 24;
                    echo $days . ' ' . ($days == 1 ? 'Day' : 'Days');
                    if ($remaining_hours > 0) {
                        echo ', ' . $remaining_hours . ' ' . ($remaining_hours == 1 ? 'Hour' : 'Hours');
                    }
                } else {
                    echo $hours . ' ' . ($hours == 1 ? 'Hour' : 'Hours');
                }
            ?>", margin, scheduleSectionY);
            
            // Payment information
            var paymentSectionY = scheduleSectionY + 12;
            pdf.setFontSize(11);
            pdf.setTextColor(80, 80, 80);
            pdf.setFont(undefined, 'bold');
            pdf.text("Payment Details:", margin, paymentSectionY);
            pdf.setFont(undefined, 'normal');
            
            paymentSectionY += 7;
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            pdf.text("Payment Method: <?php echo ucfirst($payment['PAYMENT_METHOD']); ?>", margin, paymentSectionY);
            
            paymentSectionY += 6;
            // Fix: Use proper peso sign (₱) instead of +_ in the base rate
            pdf.text("Base Rate: PHP <?php echo number_format($rentalDetail['HOURLY_RATE'], 2); ?> per hour", margin, paymentSectionY);
            
            paymentSectionY += 6;
            // Fix: Use proper peso sign (₱) instead of +_ in the rental amount
            pdf.text("Rental Amount: PHP <?php echo number_format($rentalDetail['TOTAL_AMOUNT'], 2); ?>", margin, paymentSectionY);
            
            paymentSectionY += 6;
            // Fix: Use proper peso sign (₱) instead of +_ in the VAT amount
            pdf.text("VAT (12%): PHP <?php echo number_format($rentalDetail['VAT_AMOUNT'], 2); ?>", margin, paymentSectionY);
            
            paymentSectionY += 6;
            pdf.setFont(undefined, 'bold');
            // Fix: Use proper peso sign (₱) instead of +_ in the total amount
            pdf.text("Total Amount: PHP <?php echo number_format($rentalDetail['LINE_TOTAL'], 2); ?>", margin, paymentSectionY);
            
            // RIGHT COLUMN - CUSTOMER INFORMATION
            var rightColumnX = margin * 2 + columnWidth;
            var rightColumnY = margin + 40;
            
            pdf.setFontSize(12);
            pdf.setTextColor(0, 102, 204);
            pdf.setFont(undefined, 'bold');
            pdf.text("Customer Information", rightColumnX, rightColumnY);
            pdf.setDrawColor(0, 102, 204);
            pdf.line(rightColumnX, rightColumnY + 2, rightColumnX + columnWidth - 10, rightColumnY + 2);
            
            // Customer personal information
            pdf.setFont(undefined, 'normal');
            rightColumnY += 10;
            
            pdf.setFontSize(11);
            pdf.setTextColor(80, 80, 80);
            pdf.setFont(undefined, 'bold');
            pdf.text("Personal Details:", rightColumnX, rightColumnY);
            pdf.setFont(undefined, 'normal');
            
            rightColumnY += 7;
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            pdf.text("Name: <?php echo htmlspecialchars($customer['FIRST_NAME'] . ' ' . $customer['LAST_NAME']); ?>", rightColumnX, rightColumnY);
            
            rightColumnY += 6;
            pdf.text("Email: <?php echo htmlspecialchars($customer['EMAIL']); ?>", rightColumnX, rightColumnY);
            
            rightColumnY += 6;
            pdf.text("Contact: <?php echo htmlspecialchars($customer['CONTACT_NUM']); ?>", rightColumnX, rightColumnY);
            
            rightColumnY += 6;
            pdf.text("Address: <?php echo htmlspecialchars($customer['CUSTOMER_ADDRESS']); ?>", rightColumnX, rightColumnY);
            
            rightColumnY += 6;
            pdf.text("Driver License: <?php echo htmlspecialchars($customer['DRIVERS_LICENSE']); ?>", rightColumnX, rightColumnY);
            
            // Driver information
            var driverSectionY = rightColumnY + 12;
            pdf.setFontSize(11);
            pdf.setTextColor(80, 80, 80);
            pdf.setFont(undefined, 'bold');
            pdf.text("Driver Information:", rightColumnX, driverSectionY);
            pdf.setFont(undefined, 'normal');
            
            driverSectionY += 7;
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            pdf.text("Driver Type: <?php echo $customer['DRIVER_TYPE'] === 'self' ? 'Self Drive' : 'With Driver'; ?>", rightColumnX, driverSectionY);
            
            <?php if ($customer['DRIVER_TYPE'] === 'with_driver' && isset($driver) && $driver): ?>
            driverSectionY += 6;
            pdf.text("Assigned Driver: <?php echo htmlspecialchars($driver['DRIVER_NAME']); ?>", rightColumnX, driverSectionY);
            
            driverSectionY += 6;
            pdf.text("Driver Contact: <?php echo htmlspecialchars($driver['CONTACT_NUMBER']); ?>", rightColumnX, driverSectionY);
            <?php endif; ?>
            
            // Company information if applicable
            <?php if (!empty($customer['COMPANY_NAME'])): ?>
            var companySectionY = driverSectionY + 12;
            pdf.setFontSize(11);
            pdf.setTextColor(80, 80, 80);
            pdf.setFont(undefined, 'bold');
            pdf.text("Company Information:", rightColumnX, companySectionY);
            pdf.setFont(undefined, 'normal');
            
            companySectionY += 7;
            pdf.setFontSize(10);
            pdf.setTextColor(60, 60, 60);
            pdf.text("Company Name: <?php echo htmlspecialchars($customer['COMPANY_NAME']); ?>", rightColumnX, companySectionY);
            
            companySectionY += 6;
            pdf.text("Job Title: <?php echo htmlspecialchars($customer['JOB_TITLE']); ?>", rightColumnX, companySectionY);
            
            // Set the maximum Y position for later use
            var maxY = Math.max(paymentSectionY, companySectionY);
            <?php else: ?>
            // If no company information, use driverSectionY as maxY
            var maxY = Math.max(paymentSectionY, driverSectionY);
            <?php endif; ?>
            
            // Add terms and conditions
            maxY += 20;
            pdf.setDrawColor(220, 220, 220);
            pdf.line(margin, maxY - 10, pageWidth - margin, maxY - 10);
            
            pdf.setFontSize(9);
            pdf.setTextColor(100, 100, 100);
            pdf.text("Terms and Conditions:", margin, maxY);
            maxY += 5;
            pdf.setFontSize(8);
            pdf.text("1. Customer is responsible for the vehicle during the rental period.", margin, maxY);
            maxY += 4;
            pdf.text("2. Full payment is required upon return of the vehicle.", margin, maxY);
            maxY += 4;
            pdf.text("3. Additional charges may apply for late returns or damages.", margin, maxY);
            maxY += 4;
            pdf.text("4. For inquiries or assistance, please contact our customer support at (032) 123-4567.", margin, maxY);
            
            // Add footer
            pdf.setFontSize(8);
            pdf.text("This is a computer-generated document. No signature required.", pageWidth / 2, pageHeight - 10, { align: "center" });
            
            // Save the PDF
            const transactionId = <?php echo $rentalHeader ? $rentalHeader['RENTAL_HDR_ID'] : '0'; ?>;
            pdf.save(`RentWheels_Transaction_TXN-${transactionId.toString().padStart(6, '0')}.pdf`);
            
            // Remove loading message
            document.body.removeChild(loadingMessage);
        }
    </script>
</body>
</html>