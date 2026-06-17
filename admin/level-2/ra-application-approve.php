<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
        // Validasi input
        if (empty($_GET['applicationId']) || empty($_GET['staffId'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari URL (GET)
        $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
        $staff_id = mysqli_real_escape_string($db, $_GET['staffId']);
        
        //Dapatkan nama research
        
        $get_name_query = "SELECT name, project_id FROM research_assistant_application WHERE id = '$application_id' LIMIT 1";
        $get_name_result = mysqli_query($db, $get_name_query);
        
        if (!$get_name_result || mysqli_num_rows($get_name_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve research assistant.']);
            exit;
        }
        
        $name_row = mysqli_fetch_assoc($get_name_result);
        $research_name = $name_row['name'];
        $project_id = $name_row['project_id'];
        
        //Dapatkan project no
        
        $get_no_query = "SELECT project_no FROM project WHERE id = '$project_id' LIMIT 1";
        $get_no_result = mysqli_query($db, $get_no_query);
        
        if (!$get_no_result || mysqli_num_rows($get_no_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve research assistant.']);
            exit;
        }
        
        $no_row = mysqli_fetch_assoc($get_no_result);
        $project_no = $no_row['project_no'];
        
        //$amount = mysqli_real_escape_string($db, $_GET['amount']);
        $remark = "Research assistant application for $research_name has been approved by admin ($staff_id).";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'professional_fee_applications'
            $update_query = "UPDATE research_assistant_application 
                             SET status = 'Approved',
                                 return_to = '',
                                 return_remark = ''
                             WHERE id = $application_id";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update research assistant application status.');
            }
    
            // Masukkan rekod ke dalam jadual 'ledger'
            /*$ledger_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                             VALUES ('$project_id', 'Professional/Honorarium fee for $research_name', 'Credit', '$amount', NOW())";
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
    
            echo json_encode(['success' => true, 'message' => 'Research assistant application has been successfully approved !']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
