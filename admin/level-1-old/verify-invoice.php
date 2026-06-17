<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        if (empty($_POST['project_id']) || empty($_POST['invoice_id']) || empty($_POST['project_no'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari form
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $invoice_id = mysqli_real_escape_string($db, $_POST['invoice_id']);
        $invoice_purpose = mysqli_real_escape_string($db, $_POST['invoice_purpose']);
        $remark = "Invoice application has been verify by admin";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'invoices'
            $update_query = "UPDATE invoices 
                             SET invoice_status = 'Verified' 
                             WHERE id = '$invoice_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update invoice status.');
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Invoice has been successfully verified and tracked!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>