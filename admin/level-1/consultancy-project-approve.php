<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
    
    // Check if 'id' is provided in the URL
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
    
        // Validate the input to prevent SQL injection
        $project_id = mysqli_real_escape_string($db, $project_id);
    
        // Update the project status
        $update_query = "UPDATE project SET project_status = 'Approved' WHERE id = '$project_id'";
        $update_result = mysqli_query($db, $update_query);
    
        // Retrieve the project_no for further updates
        $select_query = "SELECT project_no FROM project WHERE id = '$project_id'";
        $select_result = mysqli_query($db, $select_query);
    
        if ($update_result && $select_result && mysqli_num_rows($select_result) > 0) {
            $project_data = mysqli_fetch_assoc($select_result);
            $project_no = $project_data['project_no'];
    
            // Check if project_no starts with TEMP and update to CC
            if (strpos($project_no, 'TEMP') === 0) {
                $new_project_no = str_replace('TEMP', 'CC', $project_no);
                
                // Update the project_no in the database
                $update_project_no_query = "UPDATE project SET project_no = '$new_project_no' WHERE id = '$project_id'";
                mysqli_query($db, $update_project_no_query);
                $project_no = $new_project_no; // Update the variable for tracker insertion
            }
    
            // Insert into consultancy_project_tracker
            $remark = "Project has been approved ({$userData['staff_id']})";
            $date = date('Y-m-d');
            $insert_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                             VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $insert_result = mysqli_query($db, $insert_query);
    
            if ($insert_result) {
                // Redirect with success message
                header("Location: consultancy-project-info.php?update=approve-success&id=$project_id");
                exit();
            }
        }
    
        // If any step fails, redirect with an error message (fallback)
        header("Location: consultancy-project-info.php?update=approve-fail&id=$project_id");
        exit();
    } else {
        // Invalid project ID, redirect to a default location
        header("Location: consultancy-project-list.php?update=invalid-id");
        exit();
    }
?>
