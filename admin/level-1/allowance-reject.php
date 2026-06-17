<?php
// Start the session and include required files
session_start();
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['projectId']) || 
        empty($_POST['applicationId']) || 
        empty($_POST['projectNo']) || 
        empty($_POST['remark']) || 
        empty($_POST['staffId'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }

    // Ambil data dari POST
    $project_id     = mysqli_real_escape_string($db, $_POST['projectId']);
    $project_no     = mysqli_real_escape_string($db, $_POST['projectNo']);
    $application_id = mysqli_real_escape_string($db, $_POST['applicationId']);
    $staff_id       = mysqli_real_escape_string($db, $_POST['staffId']);
    $remark         = mysqli_real_escape_string($db, $_POST['remark']);
    $date           = date('Y-m-d H:i:s');

    mysqli_begin_transaction($db);

    try {
        // Step 1: Dapatkan member_name
        $member_query = "SELECT member_name FROM allowance_applications WHERE id = '$application_id'";
        $member_result = mysqli_query($db, $member_query);
        $member_data = mysqli_fetch_assoc($member_result);

        if (!$member_data) {
            throw new Exception("Failed to retrieve member name.");
        }

        $member_name = htmlspecialchars($member_data['member_name']);

        // Step 2: Update status ke 'Rejected - remark'
        $update_query = "UPDATE allowance_applications 
                         SET status = 'Rejected - $remark' 
                         WHERE id = '$application_id'";
        if (!mysqli_query($db, $update_query)) {
            throw new Exception("Failed to update allowance/wages application status.");
        }

        // Step 3: Insert ke tracker
        $full_remark = "Allowance/wages application for $member_name has been rejected by admin ($staff_id); $remark";
        $tracker_query = "INSERT INTO project_tracker (
                            project_id, 
                            project_no, 
                            remark, 
                            date
                          ) VALUES (
                            '$project_id', 
                            '$project_no', 
                            '$full_remark', 
                            '$date'
                          )";
        if (!mysqli_query($db, $tracker_query)) {
            throw new Exception("Failed to insert record into project tracker.");
        }

        mysqli_commit($db);
        echo json_encode([
            'success' => true,
            'message' => "Allowance/wages application for $member_name has been rejected!"
        ]);
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
