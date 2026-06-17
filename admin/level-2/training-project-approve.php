<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../../function/function.php';

    function generateFinalProjectNo($db, $prefix, $tempProjectNo)
    {
        $date_code = date('Ym');
        if (preg_match('/^TEMP(\d{6})\d{4}$/', $tempProjectNo, $matches)) {
            $date_code = $matches[1];
        }

        $like = mysqli_real_escape_string($db, $prefix . $date_code . '%');
        $sequence_query = "
            SELECT MAX(CAST(RIGHT(project_no, 4) AS UNSIGNED)) AS last_sequence
            FROM project
            WHERE project_no LIKE '$like'
              AND project_no REGEXP '^{$prefix}{$date_code}[0-9]{4}$'
        ";
        $sequence_result = mysqli_query($db, $sequence_query);
        $sequence_data = $sequence_result ? mysqli_fetch_assoc($sequence_result) : null;
        $next_sequence = ((int)($sequence_data['last_sequence'] ?? 0)) + 1;

        do {
            $project_no = $prefix . $date_code . str_pad($next_sequence, 4, '0', STR_PAD_LEFT);
            $escaped_project_no = mysqli_real_escape_string($db, $project_no);
            $exists_result = mysqli_query($db, "SELECT id FROM project WHERE project_no = '$escaped_project_no' LIMIT 1");
            $exists = $exists_result && mysqli_num_rows($exists_result) > 0;
            $next_sequence++;
        } while ($exists);

        return $project_no;
    }
    
    // Check if 'id' is provided in the URL
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
    
        // Validate the input to prevent SQL injection
        $project_id = mysqli_real_escape_string($db, $project_id);
    
        // Update the project status
        $update_query = "UPDATE project SET project_status = 'Approved' WHERE id = '$project_id'";
        $update_result = mysqli_query($db, $update_query);
    
        // Retrieve the project_no for insertion into tracker
        $select_query = "SELECT project_no FROM project WHERE id = '$project_id'";
        $select_result = mysqli_query($db, $select_query);
    
        if ($update_result && $select_result && mysqli_num_rows($select_result) > 0) {
            $project_data = mysqli_fetch_assoc($select_result);
            $project_no = $project_data['project_no'];
            
            // Check if project_no starts with TEMP and update to CC
            if (strpos($project_no, 'TEMP') === 0) {
                $new_project_no = generateFinalProjectNo($db, 'TT', $project_no);
                
                // Update the project_no in the database
                $update_project_no_query = "UPDATE project SET project_no = '$new_project_no' WHERE id = '$project_id'";
                mysqli_query($db, $update_project_no_query);
                mysqli_query($db, "UPDATE project_members_consultant SET project_no = '$new_project_no' WHERE project_id = '$project_id'");
                $project_no = $new_project_no; // Update the variable for tracker insertion
            }
    
            // Insert into project_tracker
            $remark = "Project has been approved ({$userData['staff_id']})";
            $date = date('Y-m-d');
            $insert_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                             VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $insert_result = mysqli_query($db, $insert_query);
    
            if ($insert_result) {
                // Redirect with success message
                header("Location: training-project-info.php?update=approve-success&id=$project_id");
                exit();
            }
        }
    
        // If any step fails, redirect with an error message (fallback)
        header("Location: training-project-info.php?update=approve-fail&id=$project_id");
        exit();
    } else {
        // Invalid project ID, redirect to a default location
        header("Location: training-project.php?update=invalid-id");
        exit();
    }
?>
