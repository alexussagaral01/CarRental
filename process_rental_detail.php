<?php
session_start();
require_once('connect.php');

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Check if required data exists
if (!isset($data['vehicle_id']) || !isset($data['pickup_location']) || 
    !isset($data['start_date']) || !isset($data['return_date'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Extract data
$vehicle_id = intval($data['vehicle_id']);
$pickup_location = $data['pickup_location'];
$start_date = $data['start_date'];
$return_date = $data['return_date'];

// Call stored procedure to insert rental detail
try {
    // Check if vehicle exists
    $checkVehicleStmt = $conn->prepare("SELECT VEHICLE_ID FROM vehicle WHERE VEHICLE_ID = ?");
    $checkVehicleStmt->bind_param("i", $vehicle_id);
    $checkVehicleStmt->execute();
    $checkVehicleResult = $checkVehicleStmt->get_result();
    
    if ($checkVehicleResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Vehicle not found']);
        exit;
    }
    
    $checkVehicleStmt->close();
    
    // Now insert the rental detail
    $stmt = $conn->prepare("CALL sp_InsertRentalDetail(?, ?, ?, ?)");
    $stmt->bind_param("isss", $vehicle_id, $pickup_location, $start_date, $return_date);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Store the rental detail ID in session for later use
            $_SESSION['rental_dtl_id'] = $row['rental_dtl_id'];
            $_SESSION['vehicle_id'] = $vehicle_id;
            
            // Return success response with rental data
            echo json_encode([
                'success' => true,
                'rental_id' => $row['rental_dtl_id'],
                'pickup_location' => $row['pickup_location'],
                'duration' => $row['duration_hours'],
                'vat_amount' => $row['vat_amount'],
                'total' => $row['final_total'],
                'hourly_rate' => $row['hourly_rate']
            ]);
            exit;
        }
    }
    
    // If we reach here, something went wrong
    echo json_encode(['success' => false, 'error' => $stmt->error]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
