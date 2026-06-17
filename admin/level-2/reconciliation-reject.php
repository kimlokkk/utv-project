<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Gunakan POST
        // Validasi input
        if (empty($_POST['projectId']) || empty($_POST['applicationId']) || empty($_POST['projectNo']) || empty($_POST['remark']) || empty($_POST['applicationType'])  || empty($_POST['returnTo'])   || empty($_POST['staffId'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari POST
        $project_id = mysqli_real_escape_string($db, $_POST['projectId']);
        $project_no = mysqli_real_escape_string($db, $_POST['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_POST['applicationId']);
        $application_type = mysqli_real_escape_string($db, $_POST['applicationType']);
        $return_to = mysqli_real_escape_string($db, $_POST['returnTo']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staffId']);
        $remark = mysqli_real_escape_string($db, $_POST['remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status dalam jadual 'procurement'
            $update_query = "UPDATE reconciliation_claim_applications 
                             SET status = 'Returned',
                                 remark_return = '$remark',
                                 return_to = '$return_to'
                             WHERE application_id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception("Failed to update $application_type application status.");
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$application_type application has been returned to $return_to by admin ($staff_id); $remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => "$application_type application has been returned successfully!"]);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
