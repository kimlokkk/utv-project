<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';

    function allowance_ledger_columns($db) {
        $columns = [];
        $result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $columns[] = $row['Field'];
            }
        }

        return $columns;
    }

    function allowance_insert_ledger_row($db, $ledger_columns, $ledger_data) {
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
            throw new Exception('Failed to insert allowance/wages into project ledger: ' . mysqli_error($db));
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
        // Validasi input
        if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['projectNo']) || empty($_GET['staffId'])) {
        echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
        exit;
    }
    
        // Ambil data dari URL (GET)
        $project_id     = mysqli_real_escape_string($db, $_GET['projectId']);
        $staff_id     = mysqli_real_escape_string($db, $_GET['staffId']);
        $project_no     = mysqli_real_escape_string($db, $_GET['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Step 1: Get allowance/wages application details and ensure it belongs to this project.
            $select_query = "SELECT 
                                project_id,
                                status,
                                total_allowance,
                                name,
                                application_for,
                                allowance_month,
                                allowance_month_no,
                                allowance_start_date,
                                allowance_end_date,
                                allowance_monthly_amount
                             FROM allowance_applications
                             WHERE id = '$application_id'
                             AND project_id = '$project_id'
                             LIMIT 1";
            $select_result = mysqli_query($db, $select_query);
            $application_data = mysqli_fetch_assoc($select_result);
    
            if (!$application_data) {
                throw new Exception("Failed to retrieve application details.");
            }

            if ($application_data['status'] === 'Approved' || $application_data['status'] === 'Completed') {
                throw new Exception('This allowance/wages application has already been approved or completed.');
            }

            // Step 2: Update application status.
            $update_query = "UPDATE allowance_applications 
                             SET status = 'Approved',
                                 return_to = '',
                                 return_remark = ''
                             WHERE id = '$application_id'
                             AND project_id = '$project_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update allowance/wages application status.');
            }
    
            $total_allowance = number_format(floatval($application_data['total_allowance']), 2, '.', '');
            $raw_name        = $application_data['name'];
            $member_name     = htmlspecialchars($raw_name);
            $remark = "Allowance/wages application for $member_name has been approved by admin ($staff_id).";
            
            // Step 3: Insert to ledger using the updated project_ledger structure
            if ($total_allowance > 0) {
                $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
                if (!$ledger_table_check || mysqli_num_rows($ledger_table_check) === 0) {
                    throw new Exception('project_ledger table does not exist. Please create the ledger table first.');
                }

                $ledger_columns = allowance_ledger_columns($db);
                $safe_project_no = mysqli_real_escape_string($db, $project_no);
                $safe_name = mysqli_real_escape_string($db, $raw_name);
                $safe_application_for = mysqli_real_escape_string($db, $application_data['application_for']);
                $safe_month = mysqli_real_escape_string($db, $application_data['allowance_month'] ?? '');
                $safe_month_no = mysqli_real_escape_string($db, $application_data['allowance_month_no'] ?? '');
                $safe_period_start = !empty($application_data['allowance_start_date']) ? date('d/m/Y', strtotime($application_data['allowance_start_date'])) : '';
                $safe_period_end = !empty($application_data['allowance_end_date']) ? date('d/m/Y', strtotime($application_data['allowance_end_date'])) : '';

                $details = "AW-$application_id";
                $details_2 = $application_data['application_for'] === 'Research assistant allowance'
                    ? trim("RA Allowance: $safe_name" . (!empty($safe_month_no) ? " - Month $safe_month_no" : '') . (!empty($safe_month) ? " ($safe_month)" : ''))
                    : "Outsider Allowance: $safe_name";

                $notes = "Allowance/wages approved by CST Level 2.";
                if (!empty($safe_period_start) && !empty($safe_period_end)) {
                    $notes .= " Period: $safe_period_start to $safe_period_end.";
                }

                $safe_details = mysqli_real_escape_string($db, $details);
                $safe_details_2 = mysqli_real_escape_string($db, $details_2);
                $safe_notes = mysqli_real_escape_string($db, $notes);
                $safe_total_allowance = mysqli_real_escape_string($db, $total_allowance);

                if (in_array('source_type', $ledger_columns) && in_array('source_id', $ledger_columns)) {
                    $delete_existing_ledger = "
                        DELETE FROM project_ledger
                        WHERE source_type IN ('allowance_wages', 'allowance')
                        AND source_id = '$application_id'
                    ";

                    if (!mysqli_query($db, $delete_existing_ledger)) {
                        throw new Exception('Failed to clear existing generated ledger rows: ' . mysqli_error($db));
                    }
                }

                $ledger_data = [
                    'project_id' => "'$project_id'",
                    'project_no' => "'$safe_project_no'",
                    'source_type' => "'allowance_wages'",
                    'source_id' => "'$application_id'",
                    'transaction_date' => "'$date'",
                    'transaction_category' => "'ALLOWANCE/WAGES'",
                    'details' => "'$safe_details'",
                    'details_2' => "'$safe_details_2'",
                    'invoice_amount' => "'0.00'",
                    'loan_adjustment_value' => "'0.00'",
                    'payment_received' => "'0.00'",
                    'expenses_amount' => "'$safe_total_allowance'",
                    'debit_amount' => "'0.00'",
                    'credit_amount' => "'$safe_total_allowance'",
                    'notes' => "'$safe_notes'",
                    'cst_action' => "'Approved'",
                    'fin_action' => "'Pending Finance Update'",
                    'created_by' => "'$staff_id'",
                    'created_at' => "'$date'",
                    'is_void' => "'0'",
                    'transaction_desc' => "'Allowance/Wages for $safe_name'",
                    'transaction_type' => "'Credit'",
                    'amount' => "'$safe_total_allowance'"
                ];

                allowance_insert_ledger_row($db, $ledger_columns, $ledger_data);
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
    
            echo json_encode(['success' => true, 'message' => "Allowance/wages application for $member_name has been successfully approved !"]);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
