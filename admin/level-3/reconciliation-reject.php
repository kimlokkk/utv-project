<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    include '../../function/function.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Gunakan POST
        $staff_id_input = $_POST['staffId'] ?? ($_SESSION['user_data']['staff_id'] ?? '');

        // Validasi input
        if (empty($_POST['projectId']) || empty($_POST['applicationId']) || empty($_POST['projectNo']) || empty($_POST['remark']) || empty($_POST['applicationType']) || empty($staff_id_input)) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari POST
        $project_id = mysqli_real_escape_string($db, $_POST['projectId']);
        $project_no = mysqli_real_escape_string($db, $_POST['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_POST['applicationId']);
        $staff_id = mysqli_real_escape_string($db, $staff_id_input);
        $application_type = mysqli_real_escape_string($db, $_POST['applicationType']);
        $remark = mysqli_real_escape_string($db, $_POST['remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status dalam jadual 'procurement'
            $update_query = "UPDATE reconciliation_claim_applications 
                             SET status = 'Rejected',
                                 remark_return = '$remark'
                             WHERE application_id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception("Failed to update $application_type application status.");
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$application_type application has been rejected by admin ($staff_id) - $remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => "$application_type application has been rejected successfully!"]);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
