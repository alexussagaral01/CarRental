<?php
session_start();
require_once('../connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'])) {
    $driver_id = $_POST['driver_id'];
    
    $sql = "CALL sp_DeleteDriver(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driver_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    
    $stmt->close();
    exit();
}

header('Location: manage_drivers.php');
?>
