<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';
include '../function/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['project_id']) || empty($_POST['amount']) || empty($_POST['member_id'])) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data. Please fill out all required fields.']);
        exit;
    }

    $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
    $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
    $member_ids = $_POST['member_id'];
    $amounts = $_POST['amount'];
    $date = date('Y-m-d H:i:s');

    mysqli_begin_transaction($db);

    try {
        $submitted_names = [];

        for ($i = 0; $i < count($member_ids); $i++) {
            $member_id = mysqli_real_escape_string($db, $member_ids[$i]);
            $amount = mysqli_real_escape_string($db, $amounts[$i]);

            // Get member name for remark only
            $staff_query = "SELECT full_name FROM uitm_staff WHERE id = '$member_id' LIMIT 1";
            $staff_result = mysqli_query($db, $staff_query);
            if ($staff_result && mysqli_num_rows($staff_result) > 0) {
                $staff = mysqli_fetch_assoc($staff_result);
                $submitted_names[] = $staff['full_name'];
            }

            // Save application without member_name
            $fee_query = "INSERT INTO professional_fee_applications 
                (project_id, member_id, amount, status, created_at) 
                VALUES ('$project_id', '$member_id', '$amount', 'Pending Verification', '$date')";
            if (!mysqli_query($db, $fee_query)) {
                throw new Exception("Failed to insert fee application for member ID $member_id");
            }
        }

        // Insert tracker remark
        $names_str = implode(', ', $submitted_names);
        $remark = "Professional fee application for $names_str has been submitted";

        $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                          VALUES ('$project_id', '$project_no', '$remark', '$date')";
        if (!mysqli_query($db, $tracker_query)) {
            throw new Exception("Failed to insert record into project tracker.");
        }

        mysqli_commit($db);
        echo json_encode(['success' => true, 'message' => 'Professional fee applications have been successfully submitted and recorded!']);
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
