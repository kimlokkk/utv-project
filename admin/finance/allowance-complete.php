<?php
session_start();
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['projectNo']) || empty($_GET['staffId'])) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }

    $project_id     = mysqli_real_escape_string($db, $_GET['projectId']);
    $staff_id     = mysqli_real_escape_string($db, $_GET['staffId']);
    $project_no     = mysqli_real_escape_string($db, $_GET['projectNo']);
    $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
    $date           = date('Y-m-d H:i:s');

    mysqli_begin_transaction($db);

    try {
        // Step 1: Update status
        $update_query = "UPDATE allowance_applications 
                         SET status = 'Send to bank'
                         WHERE id = '$application_id'";
        if (!mysqli_query($db, $update_query)) {
            throw new Exception('Failed to update allowance/wages application status.');
        }

        // Step 2: Get total_allowance & name
        $select_query = "SELECT total_allowance, name 
                         FROM allowance_applications 
                         WHERE id = '$application_id'";
        $select_result = mysqli_query($db, $select_query);
        $application_data = mysqli_fetch_assoc($select_result);

        if (!$application_data) {
            throw new Exception("Failed to retrieve application details.");
        }

        $total_allowance = number_format(floatval($application_data['total_allowance']), 2, '.', '');
        $member_name     = htmlspecialchars($application_data['name']);

        $ledger_column_result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");
        $ledger_columns = [];
        if ($ledger_column_result) {
            while ($ledger_column = mysqli_fetch_assoc($ledger_column_result)) {
                $ledger_columns[] = $ledger_column['Field'];
            }
        }

        $ledger_updates = [];
        if (in_array('fin_action', $ledger_columns)) {
            $ledger_updates[] = "fin_action = 'Completed'";
        }
        if (in_array('updated_by', $ledger_columns)) {
            $ledger_updates[] = "updated_by = '$staff_id'";
        }
        if (in_array('updated_at', $ledger_columns)) {
            $ledger_updates[] = "updated_at = '$date'";
        }

        if (!empty($ledger_updates) && in_array('source_type', $ledger_columns) && in_array('source_id', $ledger_columns)) {
            $ledger_update_query = "
                UPDATE project_ledger
                SET " . implode(', ', $ledger_updates) . "
                WHERE source_type IN ('allowance_wages', 'allowance')
                AND source_id = '$application_id'
            ";

            if (!mysqli_query($db, $ledger_update_query)) {
                throw new Exception('Failed to update allowance/wages project ledger status.');
            }
        }

        // Step 3: Insert to ledger
        /*if ($total_allowance > 0) {
            $ledger_query = "INSERT INTO project_ledger (
                                project_id, 
                                transaction_desc, 
                                transaction_type, 
                                amount, 
                                created_at
                             ) VALUES (
                                '$project_id', 
                                'Allowance/Wages for $member_name', 
                                'Credit', 
                                '$total_allowance', 
                                NOW()
                             )";
            if (!mysqli_query($db, $ledger_query)) {
                throw new Exception("Failed to insert allowance into project ledger.");
            }
        }*/

        // Step 4: Insert tracker
        $remark = "Allowance/wages application for $member_name has been sent to bank by finance ($staff_id)";
        /*if ($total_allowance > 0) {
            $remark .= " and project ledger has been updated.";
        }*/

        $tracker_query = "INSERT INTO project_tracker (
                            project_id, 
                            project_no, 
                            remark, 
                            date
                          ) VALUES (
                            '$project_id', 
                            '$project_no', 
                            '$remark', 
                            '$date'
                          )";
        if (!mysqli_query($db, $tracker_query)) {
            throw new Exception('Failed to insert record into project tracker.');
        }

        mysqli_commit($db);
        echo json_encode([
            'success' => true,
            'message' => "Allowance/wages application for $member_name has been successfully completed!"
        ]);
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
