<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff']) || $_SESSION['staff'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_transactions.php");
    exit();
}

$transaction_id = intval($_GET['id']);

// Database connection
$conn = new mysqli("localhost", "root", "", "vehicle_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start transaction
$conn->begin_transaction();

try {
    // First, get the necessary IDs to update related records
    $get_ids_query = "
        SELECT 
            rh.VEHICLE_ID,
            rh.RENTAL_DTL_ID,
            rh.PAYMENT_ID
        FROM 
            rental_hdr rh
        WHERE 
            rh.RENTAL_HDR_ID = ?";
    
    $stmt = $conn->prepare($get_ids_query);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Transaction not found
        throw new Exception("Transaction not found");
    }
    
    $ids = $result->fetch_assoc();
    $vehicle_id = $ids['VEHICLE_ID'];
    $rental_dtl_id = $ids['RENTAL_DTL_ID'];
    $payment_id = $ids['PAYMENT_ID'];
    
    // Delete the rental header record
    $delete_hdr_query = "DELETE FROM rental_hdr WHERE RENTAL_HDR_ID = ?";
    $stmt = $conn->prepare($delete_hdr_query);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    
    // Delete the rental detail record
    $delete_dtl_query = "DELETE FROM rental_dtl WHERE RENTAL_DTL_ID = ?";
    $stmt = $conn->prepare($delete_dtl_query);
    $stmt->bind_param("i", $rental_dtl_id);
    $stmt->execute();
    
    // Delete the payment record
    $delete_payment_query = "DELETE FROM payment WHERE PAYMENT_ID = ?";
    $stmt = $conn->prepare($delete_payment_query);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    
    // Update vehicle status back to Available
    $update_vehicle_query = "UPDATE vehicle SET STATUS = 'Available' WHERE VEHICLE_ID = ?";
    $stmt = $conn->prepare($update_vehicle_query);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success message and redirect
    $_SESSION['toast_message'] = "Transaction #$transaction_id has been successfully deleted.";
    $_SESSION['toast_type'] = "success";
    header("Location: view_transactions.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message and redirect
    $_SESSION['toast_message'] = "Error deleting transaction: " . $e->getMessage();
    $_SESSION['toast_type'] = "error";
    header("Location: view_transactions.php");
    exit();
}
