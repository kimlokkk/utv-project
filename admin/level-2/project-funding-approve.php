<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';

    function project_funding_ledger_columns($db) {
        $columns = [];
        $result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $columns[] = $row['Field'];
            }
        }

        return $columns;
    }

    function project_funding_insert_ledger_row($db, $ledger_columns, $ledger_data) {
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
            throw new Exception('Failed to insert project funding assistance into project ledger: ' . mysqli_error($db));
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan GET
        // Validasi input
        if (empty($_GET['projectId']) || empty($_GET['applicationId']) || empty($_GET['projectNo']) || empty($_GET['staffId'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari URL (GET)
        $project_id = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no = mysqli_real_escape_string($db, $_GET['projectNo']);
        $application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
        $staff_id = mysqli_real_escape_string($db, $_GET['staffId']);
        $remark = "Project funding assistance application has been approved by admin ($staff_id)";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            $application_query = "
                SELECT 
                    pfa.id,
                    pfa.project_id,
                    pfa.expected_payment_date,
                    pfa.expected_payment_amount,
                    pfa.pfa_number,
                    pfa.status,
                    p.project_no,
                    p.project_title,
                    COALESCE(SUM(pfai.amount), 0) AS total_amount,
                    GROUP_CONCAT(DISTINCT pfai.category ORDER BY pfai.category SEPARATOR ', ') AS item_categories
                FROM project_funding_assistance_applications pfa
                INNER JOIN project p ON p.id = pfa.project_id
                LEFT JOIN project_funding_assistance_items pfai ON pfai.application_id = pfa.id
                WHERE pfa.id = '$application_id'
                AND pfa.project_id = '$project_id'
                GROUP BY pfa.id
                LIMIT 1
            ";
            $application_result = mysqli_query($db, $application_query);

            if (!$application_result || mysqli_num_rows($application_result) === 0) {
                throw new Exception('Unable to retrieve project funding assistance application details.');
            }

            $application_data = mysqli_fetch_assoc($application_result);

            if ($application_data['status'] === 'Approved' || $application_data['status'] === 'Completed') {
                throw new Exception('This project funding assistance application has already been approved or completed.');
            }

            $total_amount = number_format((float)$application_data['total_amount'], 2, '.', '');

            if ((float)$total_amount <= 0) {
                throw new Exception('Project funding assistance item total must be greater than 0 before approval.');
            }

            // Kemas kini status dalam jadual project_funding_assistance_applications
            $update_query = "UPDATE project_funding_assistance_applications 
                             SET status = 'Approved',
                                 return_to = '',
                                 return_remark = ''
                             WHERE id = '$application_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update project funding assistance application status.');
            }

            // Insert project ledger row using the updated project_ledger structure.
            $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
            if (!$ledger_table_check || mysqli_num_rows($ledger_table_check) === 0) {
                throw new Exception('project_ledger table does not exist. Please create the ledger table first.');
            }

            $ledger_columns = project_funding_ledger_columns($db);
            $safe_project_no = mysqli_real_escape_string($db, $project_no);
            $safe_details = mysqli_real_escape_string($db, "PFA-$application_id");
            $safe_item_categories = mysqli_real_escape_string($db, $application_data['item_categories'] ?? '');
            $safe_details_2 = mysqli_real_escape_string($db, trim("Project Funding Assistance" . (!empty($safe_item_categories) ? ": $safe_item_categories" : '')));
            $safe_notes = mysqli_real_escape_string($db, "Project funding assistance approved by CST Level 2. Expected client payment: RM " . number_format((float)$application_data['expected_payment_amount'], 2) . (!empty($application_data['expected_payment_date']) ? " on " . date('d/m/Y', strtotime($application_data['expected_payment_date'])) : '') . ".");
            $safe_total_amount = mysqli_real_escape_string($db, $total_amount);

            if (in_array('source_type', $ledger_columns) && in_array('source_id', $ledger_columns)) {
                $delete_existing_ledger = "
                    DELETE FROM project_ledger
                    WHERE source_type IN ('project_funding_assistance', 'project_funding', 'pfa')
                    AND source_id = '$application_id'
                ";

                if (!mysqli_query($db, $delete_existing_ledger)) {
                    throw new Exception('Failed to clear existing generated ledger rows: ' . mysqli_error($db));
                }
            }

            $ledger_data = [
                'project_id' => "'$project_id'",
                'project_no' => "'$safe_project_no'",
                'source_type' => "'project_funding_assistance'",
                'source_id' => "'$application_id'",
                'transaction_date' => "'$date'",
                'transaction_category' => "'PROJECT FUNDING ASSISTANCE'",
                'details' => "'$safe_details'",
                'details_2' => "'$safe_details_2'",
                'invoice_amount' => "'0.00'",
                'loan_adjustment_value' => "'$safe_total_amount'",
                'payment_received' => "'0.00'",
                'expenses_amount' => "'0.00'",
                'debit_amount' => "'$safe_total_amount'",
                'credit_amount' => "'0.00'",
                'notes' => "'$safe_notes'",
                'cst_action' => "'Approved'",
                'fin_action' => "'Pending Finance Update'",
                'created_by' => "'$staff_id'",
                'created_at' => "'$date'",
                'is_void' => "'0'",
                'transaction_desc' => "'Project funding assistance $safe_details'",
                'transaction_type' => "'Debit'",
                'amount' => "'$safe_total_amount'"
            ];

            project_funding_insert_ledger_row($db, $ledger_columns, $ledger_data);
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => "Project funding assistance application has been successfully approved !"]);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
