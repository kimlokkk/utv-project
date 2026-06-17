<?php
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

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['remark'])) {
        error_log("Invalid POST request: Missing ID or Remark");
        header("Location: consultancy-project-list.php?update=invalid-request");
        exit();
    }

    $project_id = mysqli_real_escape_string($db, $_POST['id']);
    $user_remark = trim($_POST['remark']);

    if ($project_id === '' || $user_remark === '') {
        error_log("Invalid POST request: Empty ID or Remark");
        header("Location: consultancy-project-info.php?update=save-fail&id=$project_id");
        exit();
    }

    $user_remark = mysqli_real_escape_string($db, $user_remark);

    // Start transaction so project status and tracker update together
    mysqli_begin_transaction($db);

    try {
        // Retrieve the project_no for insertion into tracker
        $select_query = "SELECT id, project_no FROM project WHERE id = '$project_id' LIMIT 1";
        $select_result = mysqli_query($db, $select_query);

        if (!$select_result || mysqli_num_rows($select_result) <= 0) {
            throw new Exception('Project not found.');
        }

        $project_data = mysqli_fetch_assoc($select_result);
        $project_no = mysqli_real_escape_string($db, $project_data['project_no']);

        // Update the project status
        // return_to = 'Consultant' means this returned project should go back to the consultant/project leader side.
        $update_query = "UPDATE project 
                         SET project_status = 'Returned',
                             return_to = 'Consultant',
                             return_remark = '$user_remark'
                         WHERE id = '$project_id'";

        if (!mysqli_query($db, $update_query)) {
            throw new Exception('Error updating project status: ' . mysqli_error($db));
        }

        // Format the final remark
        $final_remark = mysqli_real_escape_string($db, "The project has been returned - $user_remark ($staffId)");

        // Insert into project tracker
        $date = date('Y-m-d');
        $insert_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                         VALUES ('$project_id', '$project_no', '$final_remark', '$date')";

        if (!mysqli_query($db, $insert_query)) {
            throw new Exception('Error inserting into project tracker: ' . mysqli_error($db));
        }

        mysqli_commit($db);

        // Redirect to info page with success message
        header("Location: consultancy-project-info.php?update=return-success&id=$project_id");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($db);
        error_log($e->getMessage());
        header("Location: consultancy-project-info.php?update=save-fail&id=$project_id");
        exit();
    }
?>
