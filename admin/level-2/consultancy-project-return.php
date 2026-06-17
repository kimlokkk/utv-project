<?php
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';

    date_default_timezone_set('Asia/Kuala_Lumpur');

    $staffId = 'Unknown Staff';
    if (isset($userData['staff_id']) && !empty($userData['staff_id'])) {
        $staffId = $userData['staff_id'];
    } elseif (isset($_SESSION['user_data']['staff_id']) && !empty($_SESSION['user_data']['staff_id'])) {
        $staffId = $_SESSION['user_data']['staff_id'];
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['remark']) || !isset($_POST['return_to'])) {
        error_log('Invalid POST request: Missing required fields');
        header('Location: consultancy-project-list.php?update=invalid-request');
        exit();
    }

    $project_id  = mysqli_real_escape_string($db, $_POST['id']);
    $user_remark = mysqli_real_escape_string($db, trim($_POST['remark']));
    $return_to   = mysqli_real_escape_string($db, $_POST['return_to']);

    if (empty($project_id) || empty($user_remark) || empty($return_to)) {
        header("Location: consultancy-project-info.php?update=return-fail&id=$project_id");
        exit();
    }

    mysqli_begin_transaction($db);

    try {
        // Retrieve the project_no for tracker
        $select_query = "SELECT project_no, project_status FROM project WHERE id = '$project_id' LIMIT 1";
        $select_result = mysqli_query($db, $select_query);

        if (!$select_result || mysqli_num_rows($select_result) <= 0) {
            throw new Exception('Project not found.');
        }

        $project_data = mysqli_fetch_assoc($select_result);
        $project_no = mysqli_real_escape_string($db, $project_data['project_no']);

        // Update the project status and return_to field
        $update_query = "
            UPDATE project 
            SET project_status = 'Returned',
                return_to = '$return_to',
                return_remark = '$user_remark'
            WHERE id = '$project_id'
        ";

        if (!mysqli_query($db, $update_query)) {
            throw new Exception('Error updating project status: ' . mysqli_error($db));
        }

        // Log the remark with return info
        $final_remark = mysqli_real_escape_string($db, "Returned to $return_to - $user_remark ($staffId)");
        $date = date('Y-m-d');

        $insert_query = "
            INSERT INTO project_tracker (project_id, project_no, remark, date) 
            VALUES ('$project_id', '$project_no', '$final_remark', '$date')
        ";

        if (!mysqli_query($db, $insert_query)) {
            throw new Exception('Error inserting into project tracker: ' . mysqli_error($db));
        }

        mysqli_commit($db);

        header("Location: consultancy-project-info.php?update=return-success&id=$project_id");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($db);
        error_log($e->getMessage());
        header("Location: consultancy-project-info.php?update=return-fail&id=$project_id");
        exit();
    }
?>
