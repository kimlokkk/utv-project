<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Gunakan POST
        // Validasi input
        if (empty($_POST['projectId']) || empty($_POST['applicationId']) || empty($_POST['projectNo']) || empty($_POST['remark']) || empty($_POST['memberName'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari POST
        $project_id = mysqli_real_escape_string($db, $_POST['projectId']);
        $project_no = mysqli_real_escape_string($db, $_POST['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_POST['applicationId']);
        $member_name = mysqli_real_escape_string($db, $_POST['memberName']);
        $remark = mysqli_real_escape_string($db, $_POST['remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status dalam jadual 'procurement'
            $update_query = "UPDATE allowance_applications 
                             SET status = 'Rejected - $remark'
                             WHERE id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception("Failed to update allowance/wages application status.");
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', 'Allowance/wages application for $member_name has been rejected by admin; $remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => "Allowance/wages application for $member_name has been rejected !"]);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>

