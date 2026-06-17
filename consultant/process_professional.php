<?php
    session_start();
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['project_id']) || empty($_POST['amount'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete data. Please fill out all required fields.']);
            exit;
        }

        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $member_ids = $_POST['member_id'];
        $amounts = $_POST['amount'];
        $date = date('Y-m-d H:i:s');

        mysqli_begin_transaction($db);

        try {
            for ($i = 0; $i < count($member_ids); $i++) {
                $member_id = mysqli_real_escape_string($db, $member_ids[$i]);
                $amount = mysqli_real_escape_string($db, $amounts[$i]);

                $fee_query = "INSERT INTO professional_fee_applications (project_id, member_id, amount, status, created_at) 
                              VALUES ('$project_id', '$member_id', '$amount', 'Pending approval from project leader', '$date')";
                $fee_result = mysqli_query($db, $fee_query);

                if (!$fee_result) {
                    throw new Exception('Failed to insert fee application for ' . $member_id);
                }
            }

            mysqli_commit($db);
            echo json_encode(['success' => true, 'message' => 'Professional fee applications have been successfully submitted and recorded!']);
        } catch (Exception $e) {
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
