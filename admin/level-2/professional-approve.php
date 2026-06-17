<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';

    function professional_ledger_columns($db) {
        $columns = [];
        $result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $columns[] = $row['Field'];
            }
        }

        return $columns;
    }

    function professional_insert_ledger_row($db, $ledger_columns, $ledger_data) {
        $insert_columns = [];
        $insert_values = [];

        foreach ($ledger_data as $column => $value) {
            if (in_array($column, $ledger_columns)) {
                $insert_columns[] = $column;
                $insert_values[] = $value;
            }
        }

        if (empty($insert_columns)) {
            throw new Exception('No matching ledger columns found. Please check project_ledger table structure.');
        }

        $ledger_query = "
            INSERT INTO project_ledger (" . implode(', ', $insert_columns) . ")
            VALUES (" . implode(', ', $insert_values) . ")
        ";

        if (!mysqli_query($db, $ledger_query)) {
            throw new Exception('Failed to insert professional fee into project ledger: ' . mysqli_error($db));
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
        // Validasi input
        if (empty($_GET['projectId']) || empty($_GET['professionalId']) || empty($_GET['projectNo']) || empty($_GET['staffId'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari URL (GET)
        $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
        $staff_id = mysqli_real_escape_string($db, $_GET['staffId']);
        $professional_id = mysqli_real_escape_string($db, $_GET['professionalId']);

        $professional_query = "
            SELECT 
                pf.id,
                pf.project_id,
                pf.member_id,
                pf.amount,
                pf.status,
                us.full_name
            FROM professional_fee_applications pf
            INNER JOIN uitm_staff us ON us.id = pf.member_id
            WHERE pf.id = '$professional_id'
            AND pf.project_id = '$project_id'
            LIMIT 1
        ";
        $professional_result = mysqli_query($db, $professional_query);

        if (!$professional_result || mysqli_num_rows($professional_result) == 0) {
            echo json_encode(['success' => false, 'message' => 'Unable to retrieve professional fee application details.']);
            exit;
        }

        $professional = mysqli_fetch_assoc($professional_result);
        $member_name = $professional['full_name'];
        $amount = number_format((float)$professional['amount'], 2, '.', '');

        if ($professional['status'] === 'Approved' || $professional['status'] === 'Completed') {
            echo json_encode(['success' => false, 'message' => 'This professional fee application has already been approved or completed.']);
            exit;
        }

        $remark = "Professional fee application for $member_name has been approved by admin ($staff_id).";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'invoices'
            $update_query = "UPDATE professional_fee_applications 
                             SET status = 'Approved',
                                 return_to = '',
                                 return_remark = ''
                             WHERE id = '$professional_id'
                             AND project_id = '$project_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update procurement status.');
            }
            
            // Insert project ledger row using the updated ledger structure.
            if ((float)$amount > 0) {
                $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
                if (!$ledger_table_check || mysqli_num_rows($ledger_table_check) === 0) {
                    throw new Exception('project_ledger table does not exist. Please create the ledger table first.');
                }

                $ledger_columns = professional_ledger_columns($db);
                $safe_project_no = mysqli_real_escape_string($db, $project_no);
                $safe_member_name = mysqli_real_escape_string($db, $member_name);
                $safe_amount = mysqli_real_escape_string($db, $amount);
                $safe_details = mysqli_real_escape_string($db, "PF-$professional_id");
                $safe_details_2 = mysqli_real_escape_string($db, "Professional/Honorarium Fee: $member_name");
                $safe_notes = mysqli_real_escape_string($db, 'Professional fee approved by CST Level 2.');

                if (in_array('source_type', $ledger_columns) && in_array('source_id', $ledger_columns)) {
                    $delete_existing_ledger = "
                        DELETE FROM project_ledger
                        WHERE source_type IN ('professional_fee', 'professional')
                        AND source_id = '$professional_id'
                    ";

                    if (!mysqli_query($db, $delete_existing_ledger)) {
                        throw new Exception('Failed to clear existing generated ledger rows: ' . mysqli_error($db));
                    }
                }

                $ledger_data = [
                    'project_id' => "'$project_id'",
                    'project_no' => "'$safe_project_no'",
                    'source_type' => "'professional_fee'",
                    'source_id' => "'$professional_id'",
                    'transaction_date' => "'$date'",
                    'transaction_category' => "'PROFESSIONAL/HONORARIUM FEE'",
                    'details' => "'$safe_details'",
                    'details_2' => "'$safe_details_2'",
                    'invoice_amount' => "'0.00'",
                    'loan_adjustment_value' => "'0.00'",
                    'payment_received' => "'0.00'",
                    'expenses_amount' => "'$safe_amount'",
                    'debit_amount' => "'0.00'",
                    'credit_amount' => "'$safe_amount'",
                    'notes' => "'$safe_notes'",
                    'cst_action' => "'Approved'",
                    'fin_action' => "'Pending Finance Update'",
                    'created_by' => "'$staff_id'",
                    'created_at' => "'$date'",
                    'is_void' => "'0'",
                    'transaction_desc' => "'Professional/Honorarium fee for $safe_member_name'",
                    'transaction_type' => "'Credit'",
                    'amount' => "'$safe_amount'"
                ];

                professional_insert_ledger_row($db, $ledger_columns, $ledger_data);
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Professional fee application has been successfully approved !']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
