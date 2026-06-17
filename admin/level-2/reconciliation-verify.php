<?php
session_start();
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (
        empty($_GET['projectId']) ||
        empty($_GET['applicationId']) ||
        empty($_GET['applicationType']) ||
        empty($_GET['projectNo']) ||
        empty($_GET['staffId'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }

    $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
    $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
    $staff_id = mysqli_real_escape_string($db, $_GET['staffId']);
    $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
    $application_type = mysqli_real_escape_string($db, $_GET['applicationType']);
    $date = date('Y-m-d H:i:s');

    mysqli_begin_transaction($db);

    try {
        $update_query = "UPDATE reconciliation_claim_applications
                         SET status = 'Pending Approval',
                             return_to = '',
                             remark_return = ''
                         WHERE application_id = '$application_id'";

        if (!mysqli_query($db, $update_query)) {
            throw new Exception("Failed to update $application_type status.");
        }

        $remark = "$application_type application has been verified by admin ($staff_id).";
        $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date)
                          VALUES ('$project_id', '$project_no', '$remark', '$date')";

        if (!mysqli_query($db, $tracker_query)) {
            throw new Exception('Failed to insert record into project tracker.');
        }

        mysqli_commit($db);
        echo json_encode(['success' => true, 'message' => "$application_type application has been successfully verified."]);
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
