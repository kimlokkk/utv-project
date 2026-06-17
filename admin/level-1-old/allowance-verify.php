<?php
// Start the session and include required files
session_start();
include '../db_connect/db_connect.php';
include '../function/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
    // Validasi input
    if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['memberName']) || empty($_GET['projectNo'])) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }

    // Ambil data dari URL (GET)
    $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
    $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
    $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
    $member_name = mysqli_real_escape_string($db, $_GET['memberName']);
    $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa

    // Mulakan transaksi
    mysqli_begin_transaction($db);

    try {
        // **Step 1: Update status dalam allowance_applications**
        $update_query = "UPDATE allowance_applications 
                         SET status = 'Pending Approval' 
                         WHERE id = '$application_id'";
        $update_result = mysqli_query($db, $update_query);

        if (!$update_result) {
            throw new Exception('Failed to update allowance/wages application status.');
        }

        // **Step 2: Ambil data total_allowance dari allowance_applications**
        $select_query = "SELECT total_allowance FROM allowance_applications WHERE id = '$application_id'";
        $select_result = mysqli_query($db, $select_query);
        $application_data = mysqli_fetch_assoc($select_result);

        if (!$application_data) {
            throw new Exception("Failed to retrieve application details.");
        }

        $total_allowance = number_format(floatval($application_data['total_allowance']), 2, '.', ''); // Format ke DECIMAL(10,2)

        // **Step 3: Insert ke dalam project_ledger**
        if ($total_allowance > 0) {
            $ledger_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                             VALUES ('$project_id', 'Allowance/Wages for $member_name', 'Credit', '$total_allowance', NOW())";
            $ledger_result = mysqli_query($db, $ledger_query);

            if (!$ledger_result) {
                throw new Exception("Failed to insert allowance into project ledger.");
            }
        }

        // **Step 4: Insert dalam project_tracker**
        $remark = "Allowance/wages application for $member_name has been verified by admin";
        if ($total_allowance > 0) {
            $remark .= " and project ledger has been updated.";
        }

        $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                          VALUES ('$project_id', '$project_no', '$remark', '$date')";
        $tracker_result = mysqli_query($db, $tracker_query);

        if (!$tracker_result) {
            throw new Exception('Failed to insert record into project tracker.');
        }

        // Commit transaksi
        mysqli_commit($db);

        echo json_encode(['success' => true, 'message' => "Allowance/wages application for $member_name has been successfully verified and ledger updated!"]);
    } catch (Exception $e) {
        // Rollback transaksi jika berlaku ralat
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
