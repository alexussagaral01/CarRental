<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store rental info
    $_SESSION['rental_info'] = [
        'pickup_location' => $_POST['pickup_location'],
        'start_date' => $_POST['start_date'],
        'return_date' => $_POST['return_date']
    ];
    
    // Store selected vehicles
    $_SESSION['selected_vehicles'] = $_POST['selected_vehicles'];
    
    echo json_encode(['success' => true]);
}
?>
