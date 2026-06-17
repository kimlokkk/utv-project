<?php
    session_start();
    include '../../db_connect/db_connect.php';
    include 'auth_check.php';
    
    header('Content-Type: application/json');
    
    if (
        isset($_POST['research_id']) &&
        isset($_POST['staff_id']) &&
        isset($_POST['remark'])
    ) {
        $research_id = mysqli_real_escape_string($db, $_POST['research_id']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staff_id']);
        $reject_remark = mysqli_real_escape_string($db, $_POST['remark']);
    
        // Update status
        $update_query = "UPDATE research_assistant SET status = 'Rejected' WHERE id = '$research_id'";
        $update_result = mysqli_query($db, $update_query);
    
        // Get full name
        $select_query = "SELECT full_name FROM research_assistant WHERE id = '$research_id'";
        $select_result = mysqli_query($db, $select_query);
    
        if ($update_result && $select_result && mysqli_num_rows($select_result) > 0) {
            $research_data = mysqli_fetch_assoc($select_result);
            $full_name = $research_data['full_name'];
    
            // Insert rejection remark
            $final_remark = "$full_name has been verified by staff ID: $staff_id - " . $reject_remark;
            $date = date('Y-m-d H:i:s');
    
            $insert_query = "INSERT INTO research_assistant_registration_remark (research_id, remark, date_added) 
                             VALUES ('$research_id', '$final_remark', '$date')";
            $insert_result = mysqli_query($db, $insert_query);
    
            if ($insert_result) {
                echo json_encode([
                    'success' => true,
                    'message' => "$full_name has been rejected with remark."
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Failed to insert rejection remark."
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Research assistant not found or update failed."
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Missing required POST data."
        ]);
        exit;
    }
?>
