<?php
    session_start();
    include '../../db_connect/db_connect.php';

    function reconciliation_ledger_columns($db) {
        $columns = [];
        $result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $columns[] = $row['Field'];
            }
        }

        return $columns;
    }

    function reconciliation_insert_ledger_row($db, $ledger_columns, $ledger_data) {
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
            throw new Exception('Failed to insert reconciliation/claim ledger row: ' . mysqli_error($db));
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (
            empty($_GET['projectId']) || 
            empty($_GET['applicationId']) || 
            empty($_GET['applicationType']) || 
            empty($_GET['projectNo']) || 
            empty($_GET['staffId'])
        ) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        $project_id       = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no       = mysqli_real_escape_string($db, $_GET['projectNo']);
        $application_id   = mysqli_real_escape_string($db, $_GET['applicationId']);
        $staff_id         = mysqli_real_escape_string($db, $_GET['staffId']);
        $application_type = mysqli_real_escape_string($db, $_GET['applicationType']); // e.g. Advance, Claim, Reconciliation
        $date             = date('Y-m-d H:i:s');
        $remark           = "$application_type application has been approved by admin ($staff_id).";
    
        mysqli_begin_transaction($db);
    
        try {
            // Step 1: Update status
            $update_query = "UPDATE reconciliation_claim_applications 
                             SET status = 'Approved',
                                 remark_return = ''
                             WHERE application_id = '$application_id'";
            if (!mysqli_query($db, $update_query)) {
                throw new Exception("Failed to update $application_type application status.");
            }
    
            // Step 2: Insert into project_ledger using the current ledger structure.
            $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
            if (!$ledger_table_check || mysqli_num_rows($ledger_table_check) === 0) {
                throw new Exception('project_ledger table does not exist. Please create the ledger table first.');
            }

            $ledger_columns = reconciliation_ledger_columns($db);
            $safe_project_no = mysqli_real_escape_string($db, $project_no);
            $safe_application_type = mysqli_real_escape_string($db, $application_type);
            $safe_notes = mysqli_real_escape_string($db, "$application_type application approved by CST Level 2.");

            if (in_array('source_type', $ledger_columns) && in_array('source_id', $ledger_columns)) {
                $delete_existing_ledger = "
                    DELETE FROM project_ledger
                    WHERE source_type IN ('Advance', 'Reconciliation', 'Claim', 'reconciliation_claim')
                    AND source_id = '$application_id'
                ";

                if (!mysqli_query($db, $delete_existing_ledger)) {
                    throw new Exception('Failed to clear existing generated ledger rows: ' . mysqli_error($db));
                }
            }

            $base_ledger_data = [
                'project_id' => "'$project_id'",
                'project_no' => "'$safe_project_no'",
                'source_type' => "'$safe_application_type'",
                'source_id' => "'$application_id'",
                'transaction_date' => "'$date'",
                'details' => "'ARC-$application_id'",
                'details_2' => "'$safe_application_type'",
                'invoice_amount' => "'0.00'",
                'loan_adjustment_value' => "'0.00'",
                'payment_received' => "'0.00'",
                'expenses_amount' => "'0.00'",
                'debit_amount' => "'0.00'",
                'credit_amount' => "'0.00'",
                'notes' => "'$safe_notes'",
                'cst_action' => "'Approved'",
                'fin_action' => "'Pending Finance Update'",
                'created_by' => "'$staff_id'",
                'created_at' => "'$date'",
                'is_void' => "'0'"
            ];

            if ($application_type === 'Advance') {
                $amount_query = "
                    SELECT COALESCE(total_amount, 0) AS total_amount
                    FROM reconciliation_claim_applications
                    WHERE application_id = '$application_id'
                    LIMIT 1
                ";
                $amount_result = mysqli_query($db, $amount_query);

                if (!$amount_result) {
                    throw new Exception('Failed to retrieve advance amount: ' . mysqli_error($db));
                }

                $amount_row = mysqli_fetch_assoc($amount_result);
                $amount = number_format((float)($amount_row['total_amount'] ?? 0), 2, '.', '');

                if ($amount > 0) {
                    $ledger_data = $base_ledger_data;
                    $ledger_data['transaction_category'] = "'ADVANCE'";
                    $ledger_data['expenses_amount'] = "'$amount'";
                    $ledger_data['credit_amount'] = "'$amount'";

                    reconciliation_insert_ledger_row($db, $ledger_columns, $ledger_data);
                }
            } elseif ($application_type === 'Reconciliation') {
                $reconciliation_query = "
                    SELECT 
                        COALESCE(rc.total_amount, 0) AS reconciliation_amount,
                        COALESCE(rc.adjustment_amount, 0) AS adjustment_amount,
                        COALESCE(adv.total_amount, 0) AS advance_amount
                    FROM reconciliation_claim_applications rc
                    LEFT JOIN reconciliation_claim_matches rcm ON rc.application_id = rcm.application_id
                    LEFT JOIN reconciliation_claim_applications adv ON rcm.advance_id = adv.application_id
                    WHERE rc.application_id = '$application_id'
                    LIMIT 1
                ";
                $reconciliation_result = mysqli_query($db, $reconciliation_query);

                if (!$reconciliation_result) {
                    throw new Exception('Failed to retrieve reconciliation amount: ' . mysqli_error($db));
                }

                $reconciliation_row = mysqli_fetch_assoc($reconciliation_result);
                $advance_amount = (float)($reconciliation_row['advance_amount'] ?? 0);
                $reconciliation_amount = (float)($reconciliation_row['reconciliation_amount'] ?? 0);
                $adjustment_amount = (float)($reconciliation_row['adjustment_amount'] ?? 0);
                $difference = $advance_amount > 0 ? ($reconciliation_amount - $advance_amount) : $adjustment_amount;

                if (abs($difference) > 0.004) {
                    $amount = number_format(abs($difference), 2, '.', '');
                    $ledger_data = $base_ledger_data;
                    $ledger_data['transaction_category'] = "'RECONCILIATION'";

                    if ($difference > 0) {
                        $ledger_data['expenses_amount'] = "'$amount'";
                        $ledger_data['credit_amount'] = "'$amount'";
                        $ledger_data['notes'] = "'" . mysqli_real_escape_string($db, 'Reconciliation approved. Consultant spent more than the advance value.') . "'";
                    } else {
                        $ledger_data['loan_adjustment_value'] = "'$amount'";
                        $ledger_data['debit_amount'] = "'$amount'";
                        $ledger_data['notes'] = "'" . mysqli_real_escape_string($db, 'Reconciliation approved. Consultant spent less than the advance value.') . "'";
                    }

                    reconciliation_insert_ledger_row($db, $ledger_columns, $ledger_data);
                }
            } elseif ($application_type === 'Claim') {
                $items_query = "
                    SELECT 
                        CASE 
                            WHEN claim_category IN ('F&B', 'Travelling') THEN 'TRAVELLING AND F&B EXPENSES'
                            ELSE 'OTHER EXPENSES'
                        END AS ledger_category,
                        GROUP_CONCAT(DISTINCT claim_category ORDER BY claim_category SEPARATOR ', ') AS item_categories,
                        SUM(claim_amount) AS total_amount
                    FROM reconciliation_claim_items
                    WHERE application_id = '$application_id'
                    GROUP BY ledger_category
                ";
                $items_result = mysqli_query($db, $items_query);

                if (!$items_result) {
                    throw new Exception('Failed to retrieve claim items: ' . mysqli_error($db));
                }

                while ($row = mysqli_fetch_assoc($items_result)) {
                    $amount = number_format((float)($row['total_amount'] ?? 0), 2, '.', '');

                    if ($amount <= 0) {
                        continue;
                    }

                    $ledger_category = mysqli_real_escape_string($db, $row['ledger_category']);
                    $item_categories = mysqli_real_escape_string($db, $row['item_categories'] ?? '');

                    $ledger_data = $base_ledger_data;
                    $ledger_data['transaction_category'] = "'$ledger_category'";
                    $ledger_data['details_2'] = "'Claim: $item_categories'";
                    $ledger_data['expenses_amount'] = "'$amount'";
                    $ledger_data['credit_amount'] = "'$amount'";

                    reconciliation_insert_ledger_row($db, $ledger_columns, $ledger_data);
                }
            }
    
            // Step 3: Insert tracker
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
                'message' => "$application_type application has been successfully approved!"
            ]);
        } catch (Exception $e) {
            mysqli_rollback($db);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
?>
