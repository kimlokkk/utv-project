<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Gunakan POST
        // Validasi input
        if (empty($_POST['projectId']) || empty($_POST['professionalId']) || empty($_POST['projectNo']) || empty($_POST['remark']) || empty($_POST['staffId']) || empty($_POST['returnTo'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari POST
        $project_id = mysqli_real_escape_string($db, $_POST['projectId']);
        $project_no = mysqli_real_escape_string($db, $_POST['projectNo']);
        $professional_id = mysqli_real_escape_string($db, $_POST['professionalId']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staffId']);
        $remark = mysqli_real_escape_string($db, $_POST['remark']);
        $return_to = mysqli_real_escape_string($db, $_POST['returnTo']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
        
        $get_name_query = "
            SELECT us.full_name
            FROM professional_fee_applications pf
            INNER JOIN uitm_staff us ON us.id = pf.member_id
            WHERE pf.id = '$professional_id'
            AND pf.project_id = '$project_id'
            LIMIT 1
        ";
        $get_name_result = mysqli_query($db, $get_name_query);
        
        if (!$get_name_result || mysqli_num_rows($get_name_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve member name.']);
            exit;
        }
        
        $name_row = mysqli_fetch_assoc($get_name_result);
        $member_name = $name_row['full_name'];
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status dalam jadual 'procurement'
            $update_query = "UPDATE professional_fee_applications 
                             SET status = 'Rejected',
                                 return_to = '$return_to',
                                 return_remark = '$remark'
                             WHERE id = '$professional_id'
                             AND project_id = '$project_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update professional fee application status.');
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', 'Professional fee application for $member_name has been returned to $return_to by admin ($staff_id); $remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Professional fee application has been returned successfully!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
