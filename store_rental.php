<?php
require_once 'connect.php';

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit;
}

$vehicle_ids = $data['vehicle_ids'];
$pickup_location = $data['pickup_location'];
$start_date = $data['start_date'];
$return_date = $data['return_date'];

$success = true;
$message = '';

try {
    foreach ($vehicle_ids as $vehicle_id) {
        $stmt = $conn->prepare("CALL sp_InsertRentalDetail(?, ?, ?, ?)");
        $stmt->bind_param("isss", 
            $vehicle_id, 
            $pickup_location, 
            $start_date, 
            $return_date
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to store rental for vehicle ID: " . $vehicle_id);
        }
        
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Rental details stored successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
