<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
        // Validasi input
        if (empty($_GET['projectId']) || empty($_GET['professionalId']) || empty($_GET['projectNo']) || empty($_GET['memberId']) || empty($_GET['staffId'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari URL (GET)
        $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
        $professional_id = mysqli_real_escape_string($db, $_GET['professionalId']);
        $staff_id = mysqli_real_escape_string($db, $_GET['staffId']);
        $member_id = mysqli_real_escape_string($db, $_GET['memberId']);
        
        $get_name_query = "SELECT full_name FROM uitm_staff WHERE id = '$member_id' LIMIT 1";
        $get_name_result = mysqli_query($db, $get_name_query);
        
        if (!$get_name_result || mysqli_num_rows($get_name_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve member name.']);
            exit;
        }
        
        $name_row = mysqli_fetch_assoc($get_name_result);
        $member_name = $name_row['full_name'];
        
        //$amount = mysqli_real_escape_string($db, $_GET['amount']);
        $remark = "Professional fee application for $member_name has been complete by finance ($staff_id).";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'professional_fee_applications'
            $update_query = "UPDATE professional_fee_applications 
                             SET status = 'Completed'
                             WHERE id = $professional_id";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update professional fee status.');
            }
    
            // Masukkan rekod ke dalam jadual 'ledger'
            /*$ledger_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                             VALUES ('$project_id', 'Professional/Honorarium fee for $member_name', 'Credit', '$amount', NOW())";
            $ledger_result = mysqli_query($db, $ledger_query);
    
            if (!$ledger_result) {
                throw new Exception('Failed to insert record into ledger.');
            }*/
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Professional fee application has been successfully completed !']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
