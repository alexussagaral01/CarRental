<?php
session_start();
require_once('../connect.php');

// Check if staff is logged in
if (!isset($_SESSION['staff']) || $_SESSION['staff'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Handle AJAX requests to get available drivers
if (isset($_POST['action']) && $_POST['action'] == 'get_drivers') {
    // Call the stored procedure to get all drivers
    $query = "CALL sp_GetAllDrivers()";
    $result = $conn->query($query);
    
    $drivers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
    }
    
    // Return drivers as JSON
    header('Content-Type: application/json');
    echo json_encode($drivers);
    exit();
}

// Invalid request
header('Content-Type: application/json');
echo json_encode(['error' => 'Invalid request']);
exit();
?>
