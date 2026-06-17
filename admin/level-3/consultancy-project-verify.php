<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';

    date_default_timezone_set('Asia/Kuala_Lumpur');

    // Get staff id safely. Some pages store staff data in $_SESSION['user_data'].
    $staffId = 'Unknown Staff';
    if (isset($userData['staff_id']) && !empty($userData['staff_id'])) {
        $staffId = $userData['staff_id'];
    } elseif (isset($_SESSION['user_data']['staff_id']) && !empty($_SESSION['user_data']['staff_id'])) {
        $staffId = $_SESSION['user_data']['staff_id'];
    }

    // Check if 'id' is provided in the URL
    if (!isset($_GET['id']) || trim($_GET['id']) === '') {
        // Invalid project ID, redirect to a default location
        header("Location: consultancy-project-list.php?update=invalid-id");
        exit();
    }

    // Validate the input to prevent SQL injection
    $project_id = mysqli_real_escape_string($db, $_GET['id']);

    // Start transaction so project status and tracker update together
    mysqli_begin_transaction($db);

    try {
        // Retrieve project details first
        $select_query = "SELECT id, project_no, project_status FROM project WHERE id = '$project_id' LIMIT 1";
        $select_result = mysqli_query($db, $select_query);

        if (!$select_result || mysqli_num_rows($select_result) <= 0) {
            throw new Exception('Project not found.');
        }

        $project_data = mysqli_fetch_assoc($select_result);
        $project_no = mysqli_real_escape_string($db, $project_data['project_no']);
        $current_status = $project_data['project_status'];

        // Prevent duplicate verification if already verified / approved flow
        if (in_array($current_status, ['Pending Approval', 'Approved', 'Appointed'])) {
            mysqli_commit($db);
            header("Location: consultancy-project-info.php?update=save-success&id=$project_id");
            exit();
        }

        // Update the project status
        $update_query = "UPDATE project 
                         SET project_status = 'Pending Approval',
                             return_to = NULL
                         WHERE id = '$project_id'";
        $update_result = mysqli_query($db, $update_query);

        if (!$update_result) {
            throw new Exception('Failed to update project status: ' . mysqli_error($db));
        }

        // Insert into project tracker
        $remark = mysqli_real_escape_string($db, "Project has been verified ($staffId)");
        $date = date('Y-m-d');
        $insert_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                         VALUES ('$project_id', '$project_no', '$remark', '$date')";
        $insert_result = mysqli_query($db, $insert_query);

        if (!$insert_result) {
            throw new Exception('Failed to insert project tracker: ' . mysqli_error($db));
        }

        mysqli_commit($db);

        // Redirect with success message
        header("Location: consultancy-project-info.php?update=save-success&id=$project_id");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($db);
        error_log($e->getMessage());

        // If any step fails, redirect with an error message (fallback)
        header("Location: consultancy-project-info.php?update=save-fail&id=$project_id");
        exit();
    }
?>
