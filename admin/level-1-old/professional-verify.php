<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
        // Validasi input
        if (empty($_GET['projectId']) || empty($_GET['professionalId']) || empty($_GET['projectNo']) || empty($_GET['memberName']) || empty($_GET['amount'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari URL (GET)
        $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
        $professional_id = mysqli_real_escape_string($db, $_GET['professionalId']);
        $member_name = mysqli_real_escape_string($db, $_GET['memberName']);
        $amount = mysqli_real_escape_string($db, $_GET['amount']);
        $remark = "Professional fee application for $member_name has been verified by admin.";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'professional_fee_applications'
            $update_query = "UPDATE professional_fee_applications 
                             SET status = 'Pending Approval' 
                             WHERE id = '$professional_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update professional fee status.');
            }
    
            // Masukkan rekod ke dalam jadual 'ledger'
            $ledger_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                             VALUES ('$project_id', 'Professional/Honorarium fee for $member_name', 'Credit', '$amount', NOW())";
            $ledger_result = mysqli_query($db, $ledger_query);
    
            if (!$ledger_result) {
                throw new Exception('Failed to insert record into ledger.');
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
    
            echo json_encode(['success' => true, 'message' => 'Professional fee application has been successfully verified and recorded in ledger!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
