<?php
session_start();
require_once('../connect.php');

if(isset($_POST['vehicle_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    
    $sql = "DELETE FROM vehicle WHERE VEHICLE_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    
    $stmt->close();
}
?>
