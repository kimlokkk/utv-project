<?php
// Start the session and include required files
session_start();
include '../db_connect/db_connect.php';
include '../function/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
    // Validasi input
    if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['applicationType']) || empty($_GET['projectNo'])) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }

    // Ambil data dari URL (GET)
    $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
    $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
    $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
    $application_type = mysqli_real_escape_string($db, $_GET['applicationType']);
    $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa

    // Mulakan transaksi
    mysqli_begin_transaction($db);

    try {
        // **Step 1: Update Status dalam reconciliation_claim_applications**
        $update_query = "UPDATE reconciliation_claim_applications 
                         SET status = 'Pending Approval' 
                         WHERE application_id = '$application_id'";
        $update_result = mysqli_query($db, $update_query);

        if (!$update_result) {
            throw new Exception("Failed to update $application_type status.");
        }

        // **Step 2: Ambil data dari reconciliation_claim_applications untuk insert ke project_ledger**
        $select_query = "SELECT fnb, hotel, travelling, printing, materials, others 
                         FROM reconciliation_claim_applications 
                         WHERE application_id = '$application_id'";
        $select_result = mysqli_query($db, $select_query);
        $application_data = mysqli_fetch_assoc($select_result);

        if (!$application_data) {
            throw new Exception("Failed to retrieve application details.");
        }

        // Array untuk column yang nak diperiksa dan dimasukkan dalam `project_ledger`
        $categories = [
            'fnb' => 'Food & Beverages',
            'hotel' => 'Hotel',
            'travelling' => 'Travelling',
            'printing' => 'Printing',
            'materials' => 'Materials',
            'others' => 'Others'
        ];

        $ledger_inserted = false; // Flag untuk check jika ada data masuk dalam ledger

        // **Step 3: Insert ke dalam project_ledger**
        foreach ($categories as $column => $description) {
            $amount = number_format(floatval($application_data[$column]), 2, '.', ''); // Convert ke DECIMAL(10,2)

            if ($amount > 0) {
                $ledger_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                                 VALUES ('$project_id', '$application_type for $description', 'Credit', '$amount', NOW())";
                $ledger_result = mysqli_query($db, $ledger_query);

                if (!$ledger_result) {
                    throw new Exception("Failed to insert $description into project ledger.");
                }
                $ledger_inserted = true;
            }
        }

        // **Step 4: Insert dalam project_tracker**
        $remark = "$application_type application has been verified by admin.";
        if ($ledger_inserted) {
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

        echo json_encode(['success' => true, 'message' => "$application_type application has been successfully verified and ledger updated!"]);
    } catch (Exception $e) {
        // Rollback transaksi jika berlaku ralat
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
