<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Gunakan POST
        // Validasi input
        if (
            empty($_POST['projectId']) || 
            empty($_POST['applicationId']) || 
            empty($_POST['projectNo']) || 
            empty($_POST['remark']) || 
            empty($_POST['staffId']) ||
            empty($_POST['returnTo'])
        ) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari POST
        $project_id = mysqli_real_escape_string($db, $_POST['projectId']);
        $project_no = mysqli_real_escape_string($db, $_POST['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_POST['applicationId']);
        $return_to = mysqli_real_escape_string($db, $_POST['returnTo']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staffId']);
        $remark = mysqli_real_escape_string($db, $_POST['remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Keep status clean and store the return destination/reason separately.
            $update_query = "UPDATE allowance_applications 
                             SET status = 'Rejected',
                                 return_to = '$return_to',
                                 return_remark = '$remark'
                             WHERE id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception("Failed to update allowance/wages application status.");
            }
            
            // Step 2: Get application name
            $select_query = "SELECT name 
                             FROM allowance_applications 
                             WHERE id = '$application_id'";
            $select_result = mysqli_query($db, $select_query);
            $application_data = mysqli_fetch_assoc($select_result);
    
            if (!$application_data) {
                throw new Exception("Failed to retrieve application details.");
            }
    
            $member_name     = htmlspecialchars($application_data['name']);
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', 'Allowance/wages application for $member_name has been returned by admin ($staff_id); $remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => "Allowance/wages application for $member_name has been returned !"]);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>

