<?php
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['applicationId']) || empty($_POST['remark']) || empty($_POST['staffId']) || empty($_POST['return_to'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data.']);
            exit;
        }
    
        $application_id = mysqli_real_escape_string($db, $_POST['applicationId']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staffId']);
        $remark = mysqli_real_escape_string($db, $_POST['remark']);
        $return_to = mysqli_real_escape_string($db, $_POST['return_to']);
        $date = date('Y-m-d H:i:s');
    
        // Get research assistant details
        $get_name_query = "SELECT name, project_id FROM research_assistant_application WHERE id = '$application_id' LIMIT 1";
        $get_name_result = mysqli_query($db, $get_name_query);
    
        if (!$get_name_result || mysqli_num_rows($get_name_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve research assistant.']);
            exit;
        }
    
        $name_row = mysqli_fetch_assoc($get_name_result);
        $research_name = $name_row['name'];
        $project_id = $name_row['project_id'];
    
        // Get project number
        $get_no_query = "SELECT project_no FROM project WHERE id = '$project_id' LIMIT 1";
        $get_no_result = mysqli_query($db, $get_no_query);
    
        if (!$get_no_result || mysqli_num_rows($get_no_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve project number.']);
            exit;
        }
    
        $no_row = mysqli_fetch_assoc($get_no_result);
        $project_no = $no_row['project_no'];
    
        mysqli_begin_transaction($db);
    
        try {
            // Keep status clean and store the reason separately.
            $update_query = "UPDATE research_assistant_application 
                             SET status = 'Rejected',
                                 return_to = '$return_to',
                                 return_remark = '$remark'
                             WHERE id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update research assistant application status.');
            }
    
            // Insert tracker log
            $tracker_msg = "Research assistant application for $research_name has been returned to $return_to by admin ($staff_id); $remark";
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$tracker_msg', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            mysqli_commit($db);
            echo json_encode(['success' => true, 'message' => 'Research assistant application has been returned successfully!']);
        } catch (Exception $e) {
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
