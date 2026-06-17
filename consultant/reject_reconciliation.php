<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Validasi input
        if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['applicationType']) || empty($_GET['projectNo'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari form
        $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
        $application_type = mysqli_real_escape_string($db, $_GET['applicationType']);
        $reject_remark = mysqli_real_escape_string($db, $_GET['reject_remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'professionals'
            $update_query = "UPDATE reconciliation_claim_applications
                             SET status = 'Rejected by leader',
                                 remark_return = '$reject_remark'
                             WHERE application_id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update advance, reconciliation and claim status.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Advance/Reconciliation/Claim application has been rejected!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
