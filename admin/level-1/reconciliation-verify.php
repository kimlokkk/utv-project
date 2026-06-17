<?php
// Start the session and include required files
session_start();
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
    // Validasi input
    if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['applicationType']) || empty($_GET['projectNo']) || empty($_GET['staffId'])) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }

    // Ambil data dari URL (GET)
    $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
    $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
    $staff_id = mysqli_real_escape_string($db, $_GET['staffId']);
    $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
    $application_type = mysqli_real_escape_string($db, $_GET['applicationType']);
    $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa

    // Mulakan transaksi
    mysqli_begin_transaction($db);

    try {
        // **Step 1: Update Status dalam reconciliation_claim_applications**
        $update_query = "UPDATE reconciliation_claim_applications 
                         SET status = 'Pending Approval', return_to = ''
                         WHERE application_id = '$application_id'";
        $update_result = mysqli_query($db, $update_query);

        if (!$update_result) {
            throw new Exception("Failed to update $application_type status.");
        }

        // **Step 4: Insert dalam project_tracker**
        $remark = "$application_type application has been verified by admin ($staff_id).";
        /*if ($ledger_inserted) {
            $remark .= " and project ledger has been updated.";
        }*/

        $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                          VALUES ('$project_id', '$project_no', '$remark', '$date')";
        $tracker_result = mysqli_query($db, $tracker_query);

        if (!$tracker_result) {
            throw new Exception('Failed to insert record into project tracker.');
        }

        // Commit transaksi
        mysqli_commit($db);

        echo json_encode(['success' => true, 'message' => "$application_type application has been successfully verified updated!"]);
    } catch (Exception $e) {
        // Rollback transaksi jika berlaku ralat
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
