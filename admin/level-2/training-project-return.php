<?php
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['remark']) && isset($_POST['return_to'])) {
        $project_id     = mysqli_real_escape_string($db, $_POST['id']);
        $user_remark    = mysqli_real_escape_string($db, $_POST['remark']);
        $return_to      = mysqli_real_escape_string($db, $_POST['return_to']); // new

        // Update the project status and return_to field
        $update_query = "
            UPDATE project 
            SET project_status = 'Returned', return_to = '$return_to' 
            WHERE id = '$project_id'
        ";
        if (!mysqli_query($db, $update_query)) {
            error_log("Error updating project status: " . mysqli_error($db));
            header("Location: training-project-info.php?update=save-fail&id=$project_id");
            exit();
        }

        // Retrieve the project_no for tracker
        $select_query = "SELECT project_no FROM project WHERE id = '$project_id'";
        $select_result = mysqli_query($db, $select_query);

        if ($select_result && mysqli_num_rows($select_result) > 0) {
            $project_data = mysqli_fetch_assoc($select_result);
            $project_no = $project_data['project_no'];

            // Log the remark with return info
            $final_remark = "Returned to $return_to - $user_remark ({$userData['staff_id']})";
            $date = date('Y-m-d');

            $insert_query = "
                INSERT INTO project_tracker (project_id, project_no, remark, date) 
                VALUES ('$project_id', '$project_no', '$final_remark', '$date')
            ";

            if (!mysqli_query($db, $insert_query)) {
                error_log("Error inserting into project tracker: " . mysqli_error($db));
                header("Location: training-project-info.php?update=save-fail&id=$project_id");
                exit();
            }

            // Success
            header("Location: training-project-info.php?update=save-success&id=$project_id");
            exit();
        } else {
            error_log("Error fetching project number: " . mysqli_error($db));
            header("Location: training-project-info.php?update=save-fail&id=$project_id");
            exit();
        }
    } else {
        error_log("Invalid POST request: Missing required fields");
        header("Location: training-project-list.php?update=invalid-request");
        exit();
    }
?>
