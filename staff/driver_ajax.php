<?php
session_start();
require_once('../connect.php');

// Check if the user is logged in as staff
if (!isset($_SESSION['staff_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Handle AJAX requests
if (isset($_POST['action']) && $_POST['action'] === 'get_drivers') {
    // Call the stored procedure to get all drivers
    $stmt = $conn->prepare("CALL sp_GetAllDrivers()");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $drivers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($drivers);
    exit;
}

// Invalid request
header('Content-Type: application/json');
echo json_encode(['error' => 'Invalid request']);
exit;
?>
