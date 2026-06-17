<?php
    session_start();
    include '../../db_connect/db_connect.php';
    include 'auth_check.php';
    
    header('Content-Type: application/json');
    
    // Pastikan POST ada 'research_id'
    if (isset($_POST['research_id'])) {
        $research_id = mysqli_real_escape_string($db, $_POST['research_id']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staff_id']);
    
        // Update status
        $update_query = "UPDATE research_assistant SET status = 'Approved' WHERE id = '$research_id'";
        $update_result = mysqli_query($db, $update_query);
    
        // Get full name
        $select_query = "SELECT full_name FROM research_assistant WHERE id = '$research_id'";
        $select_result = mysqli_query($db, $select_query);
    
        if ($update_result && $select_result && mysqli_num_rows($select_result) > 0) {
            $research_data = mysqli_fetch_assoc($select_result);
            $full_name = $research_data['full_name'];
    
            // Insert remark
            $remark = "$full_name has been approved by staff ID: $staff_id";
            $date = date('Y-m-d H:i:s');
    
            $insert_query = "INSERT INTO research_assistant_registration_remark (research_id, remark, date_added) 
                             VALUES ('$research_id', '$remark', '$date')";
            $insert_result = mysqli_query($db, $insert_query);
    
            if ($insert_result) {
                echo json_encode([
                    'success' => true,
                    'message' => "$full_name has been successfully approved."
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Failed to insert remark."
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
            'message' => "Missing research assistant ID."
        ]);
        exit;
    }
?>
