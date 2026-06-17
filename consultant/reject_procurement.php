<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Validasi input
        if (empty($_GET['project_id']) || empty($_GET['procurement_id']) || empty($_GET['project_no'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari form
        $project_id = mysqli_real_escape_string($db, $_GET['project_id']);
        $project_no = mysqli_real_escape_string($db, $_GET['project_no']);
        $procurement_id = mysqli_real_escape_string($db, $_GET['procurement_id']);
        $reject_remark = mysqli_real_escape_string($db, $_GET['reject_remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'procurements'
            $update_query = "UPDATE procurement 
                             SET status = 'Rejected by project leader - $reject_remark'
                             WHERE id = '$procurement_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update procurement status.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Procurement has been rejected!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>