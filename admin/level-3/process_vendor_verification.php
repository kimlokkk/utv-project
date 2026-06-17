<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';

// Clean output buffer to prevent any extra output
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $vendor_id = $_POST['vendor_id'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    
    if ($action == 'verify') {
        // Update vendor status to Verified
        $query = "UPDATE vendor SET status = 'Verified' WHERE id = '$vendor_id'";
        $result = mysqli_query($db, $query);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Vendor verified successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to verify vendor. Error: ' . mysqli_error($db)]);
        }
        
    } else if ($action == 'reject') {
        // Update vendor status to Reject and store reason
        $query = "UPDATE vendor SET status = 'Reject-$reason' WHERE id = '$vendor_id'";
        $result = mysqli_query($db, $query);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Vendor rejected successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject vendor. Error: ' . mysqli_error($db)]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($db);
exit;
?>