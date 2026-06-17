<?php
    // Start the session and include required files
    header('Content-Type: application/json');
    session_start();
    include '../../db_connect/db_connect.php';

    date_default_timezone_set('Asia/Kuala_Lumpur');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        if (empty($_POST['project_id']) || empty($_POST['invoice_id']) || empty($_POST['project_no']) || empty($_POST['staff_id']) /*|| empty($_POST['invoice_no'])*/) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari form
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $invoice_id = mysqli_real_escape_string($db, $_POST['invoice_id']);
        //$invoice_no = mysqli_real_escape_string($db, $_POST['invoice_no']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staff_id']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Ambil data invois dari database supaya tak bergantung pada POST invoice_no
            $invoice_query = "
                SELECT 
                    id,
                    project_id,
                    member_id,
                    invoice_purpose,
                    additional_info,
                    total_amount,
                    sst_amount,
                    total_invoice,
                    follow_milestone,
                    amount_type,
                    tin_number,
                    invoice_status,
                    invoice_no,
                    invoice_file
                FROM invoices
                WHERE id = '$invoice_id'
                AND project_id = '$project_id'
                LIMIT 1
            ";
            $invoice_result = mysqli_query($db, $invoice_query);

            if (!$invoice_result || mysqli_num_rows($invoice_result) === 0) {
                throw new Exception('Invoice not found.');
            }

            $invoice_data = mysqli_fetch_assoc($invoice_result);

            $invoice_no = !empty($invoice_data['invoice_no']) ? $invoice_data['invoice_no'] : 'Not Available';
            $invoice_purpose = $invoice_data['invoice_purpose'];
            $additional_info = $invoice_data['additional_info'];
            $total_invoice = $invoice_data['total_invoice'];
            $current_status = $invoice_data['invoice_status'];

            /*
                IMPORTANT:
                Kalau invoice sudah approved, jangan approve ulang.
                Ini elak duplicate tracker dan duplicate ledger.
            */
            if (stripos($current_status, 'Approved') !== false) {
                throw new Exception('This invoice has already been approved.');
            }

            /*
                Check column optional dalam invoices.
                Kalau column belum ada, code tetap jalan tanpa include column tu.
            */
            $invoice_columns = [];
            $column_result = mysqli_query($db, "SHOW COLUMNS FROM invoices");
            if ($column_result) {
                while ($column_row = mysqli_fetch_assoc($column_result)) {
                    $invoice_columns[] = $column_row['Field'];
                }
            }

            $update_parts = [
                "invoice_status = 'Approved'"
            ];

            if (in_array('approved_by', $invoice_columns)) {
                $update_parts[] = "approved_by = '$staff_id'";
            }

            if (in_array('approved_at', $invoice_columns)) {
                $update_parts[] = "approved_at = '$date'";
            }

            if (in_array('payment_status', $invoice_columns)) {
                $update_parts[] = "payment_status = 'Unpaid'";
            }

            if (in_array('paid_amount', $invoice_columns)) {
                $update_parts[] = "paid_amount = '0.00'";
            }

            if (in_array('outstanding_amount', $invoice_columns)) {
                $safe_total_invoice = mysqli_real_escape_string($db, $total_invoice);
                $update_parts[] = "outstanding_amount = '$safe_total_invoice'";
            }

            // Kemas kini status invois dalam jadual 'invoices'
            $update_query = "
                UPDATE invoices 
                SET " . implode(', ', $update_parts) . "
                WHERE id = '$invoice_id'
                AND project_id = '$project_id'
            ";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update invoice status: ' . mysqli_error($db));
            }

            if (mysqli_affected_rows($db) <= 0) {
                throw new Exception('Invoice status was not updated. Please check invoice data.');
            }

            $remark_invoice_no = mysqli_real_escape_string($db, $invoice_no);
            $remark = "Invoice application ($remark_invoice_no) has been approved ($staff_id)";
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker: ' . mysqli_error($db));
            }

            /*
                Masukkan rekod ke dalam project_ledger.
                Flow:
                Bila Level 2 approve, invoice menjadi official.
                Tapi payment masih belum received.
            */

            // Check project_ledger table exists
            $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
            if (!$ledger_table_check || mysqli_num_rows($ledger_table_check) === 0) {
                throw new Exception('project_ledger table does not exist. Please create the ledger table first.');
            }

            // Check column optional dalam project_ledger
            $ledger_columns = [];
            $ledger_column_result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");
            if ($ledger_column_result) {
                while ($ledger_column_row = mysqli_fetch_assoc($ledger_column_result)) {
                    $ledger_columns[] = $ledger_column_row['Field'];
                }
            }

            /*
                Prevent duplicate ledger.
                Kalau approve request triggered twice, ledger tidak akan duplicate.
            */
            $duplicate_ledger_query = "
                SELECT id 
                FROM project_ledger
                WHERE source_type = 'invoice'
                AND source_id = '$invoice_id'
                AND transaction_category = 'INVOICE APPLICATION'
                AND is_void = 0
                LIMIT 1
            ";
            $duplicate_ledger_result = mysqli_query($db, $duplicate_ledger_query);

            if (!$duplicate_ledger_result) {
                throw new Exception('Failed to check existing ledger record: ' . mysqli_error($db));
            }

            $safe_invoice_no = mysqli_real_escape_string($db, $invoice_no);
            $safe_invoice_purpose = mysqli_real_escape_string($db, $invoice_purpose);
            $safe_additional_info = mysqli_real_escape_string($db, $additional_info);
            $safe_total_invoice = mysqli_real_escape_string($db, $total_invoice);

            if (mysqli_num_rows($duplicate_ledger_result) === 0) {
                $ledger_data = [
                    'project_id' => "'$project_id'",
                    'project_no' => "'$project_no'",
                    'source_type' => "'invoice'",
                    'source_id' => "'$invoice_id'",
                    'transaction_date' => "'$date'",
                    'transaction_category' => "'INVOICE APPLICATION'",
                    'details' => "'$safe_invoice_no'",
                    'details_2' => "'$safe_invoice_purpose'",
                    'invoice_id' => "'$invoice_id'",
                    'invoice_no' => "'$safe_invoice_no'",
                    'invoice_amount' => "'$safe_total_invoice'",
                    'loan_adjustment_value' => "'0.00'",
                    'payment_received' => "'0.00'",
                    'expenses_amount' => "'0.00'",
                    'debit_amount' => "'$safe_total_invoice'",
                    'credit_amount' => "'0.00'",
                    'notes' => "'Invoice approved. Payment has not been received yet.'",
                    'cst_action' => "'Approved'",
                    'fin_action' => "'Pending Finance Update'",
                    'created_by' => "'$staff_id'",
                    'created_at' => "'$date'",
                    'is_void' => "'0'"
                ];

                /*
                    IMPORTANT:
                    Insert ikut column yang wujud sahaja.
                    Jadi kalau project_ledger table kau tak ada semua column optional,
                    code masih boleh jalan selagi core column wujud.
                */
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

                $ledger_result = mysqli_query($db, $ledger_query);

                if (!$ledger_result) {
                    throw new Exception('Failed to insert invoice into project ledger: ' . mysqli_error($db));
                }
            } else {
                /*
                    Kalau ledger dah ada, update existing row supaya data masih sync.
                    Ini berguna kalau invoice amount diedit sebelum approve ulang secara accidental.
                */
                $existing_ledger = mysqli_fetch_assoc($duplicate_ledger_result);
                $existing_ledger_id = $existing_ledger['id'];

                $update_ledger_parts = [];

                $possible_update_parts = [
                    'project_no' => "'$project_no'",
                    'transaction_date' => "'$date'",
                    'details' => "'$safe_invoice_no'",
                    'details_2' => "'$safe_invoice_purpose'",
                    'invoice_no' => "'$safe_invoice_no'",
                    'invoice_amount' => "'$safe_total_invoice'",
                    'debit_amount' => "'$safe_total_invoice'",
                    'credit_amount' => "'0.00'",
                    'notes' => "'Invoice approved. Payment has not been received yet.'",
                    'cst_action' => "'Approved'",
                    'fin_action' => "'Pending Finance Update'",
                    'updated_by' => "'$staff_id'",
                    'updated_at' => "'$date'"
                ];

                foreach ($possible_update_parts as $column => $value) {
                    if (in_array($column, $ledger_columns)) {
                        $update_ledger_parts[] = "$column = $value";
                    }
                }

                if (!empty($update_ledger_parts)) {
                    $update_ledger_query = "
                        UPDATE project_ledger
                        SET " . implode(', ', $update_ledger_parts) . "
                        WHERE id = '$existing_ledger_id'
                    ";

                    $update_ledger_result = mysqli_query($db, $update_ledger_query);

                    if (!$update_ledger_result) {
                        throw new Exception('Failed to update existing ledger record: ' . mysqli_error($db));
                    }
                }
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode([
                'success' => true,
                'message' => 'Invoice has been approved, tracked, and added to project ledger!'
            ]);
            exit;

        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
?>