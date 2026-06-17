<?php
    session_start();

    header('Content-Type: application/json; charset=utf-8');

    include '../../db_connect/db_connect.php';
    include '../../function/function.php';

    date_default_timezone_set('Asia/Kuala_Lumpur');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (
            empty($_POST['invoice_id']) || 
            empty($_POST['project_id']) || 
            empty($_POST['invoice_no']) ||
            empty($_POST['invoice_date']) ||
            empty($_POST['due_date']) ||
            empty($_POST['staff_id'])
        ) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }
    
        $invoice_id = mysqli_real_escape_string($db, $_POST['invoice_id']);
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $invoice_no = mysqli_real_escape_string($db, $_POST['invoice_no']);
        $invoice_date = mysqli_real_escape_string($db, $_POST['invoice_date']);
        $due_date = mysqli_real_escape_string($db, $_POST['due_date']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staff_id']);
        $finance_remark = mysqli_real_escape_string($db, $_POST['finance_remark'] ?? '');
        $date_now = date('Y-m-d H:i:s');

        if (strtotime($due_date) < strtotime($invoice_date)) {
            echo json_encode(['success' => false, 'message' => 'Due date cannot be earlier than invoice date.']);
            exit;
        }
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);

        try {
            // Get project_no from project_id
            $projectResult = mysqli_query($db, "SELECT project_no FROM project WHERE id = '$project_id' LIMIT 1");
            if (!$projectResult || mysqli_num_rows($projectResult) == 0) {
                throw new Exception('Project not found.');
            }
        
            $projectRow = mysqli_fetch_assoc($projectResult);
            $project_no = $projectRow['project_no'];

            // Get invoice data
            $invoiceResult = mysqli_query($db, "SELECT * FROM invoices WHERE id = '$invoice_id' AND project_id = '$project_id' LIMIT 1");
            if (!$invoiceResult || mysqli_num_rows($invoiceResult) == 0) {
                throw new Exception('Invoice not found.');
            }

            $invoiceRow = mysqli_fetch_assoc($invoiceResult);
            $current_invoice_file = $invoiceRow['invoice_file'];
            $total_invoice = $invoiceRow['total_invoice'];
            $invoice_purpose = $invoiceRow['invoice_purpose'];

            /*
                Finance should only update invoice after Level 2 approval.
                If needed, boleh comment validation ni.
            */
            if (stripos($invoiceRow['invoice_status'], 'Approved') === false && stripos($invoiceRow['invoice_status'], 'Waiting Payment') === false) {
                throw new Exception('Only approved invoices can be updated by Finance.');
            }

            // Upload invoice file
            $upload_dir = "../../finance-invoice-documents/";
            $unique_file_name = $current_invoice_file;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['invoice_file']['tmp_name'];
                $file_name = $_FILES['invoice_file']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

                if (!in_array($file_ext, $allowed_ext)) {
                    throw new Exception('Invalid file type. Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX.');
                }

                $unique_file_name = uniqid('finance_invoice_', true) . '.' . $file_ext;

                if (!move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
                    throw new Exception('Failed to upload file.');
                }
            }

            if (empty($unique_file_name)) {
                throw new Exception('Invoice file is required.');
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
                "invoice_no = '$invoice_no'",
                "invoice_file = '$unique_file_name'",
                "invoice_status = 'Waiting Payment'"
            ];

            if (in_array('invoice_date', $invoice_columns)) {
                $update_parts[] = "invoice_date = '$invoice_date'";
            }

            if (in_array('due_date', $invoice_columns)) {
                $update_parts[] = "due_date = '$due_date'";
            }

            if (in_array('finance_updated_by', $invoice_columns)) {
                $update_parts[] = "finance_updated_by = '$staff_id'";
            }

            if (in_array('finance_updated_at', $invoice_columns)) {
                $update_parts[] = "finance_updated_at = '$date_now'";
            }

            if (in_array('finance_remark', $invoice_columns)) {
                $update_parts[] = "finance_remark = '$finance_remark'";
            }

            if (in_array('payment_status', $invoice_columns)) {
                $update_parts[] = "payment_status = 'Unpaid'";
            }

            if (in_array('paid_amount', $invoice_columns)) {
                $update_parts[] = "paid_amount = COALESCE(paid_amount, 0.00)";
            }

            if (in_array('outstanding_amount', $invoice_columns)) {
                $update_parts[] = "outstanding_amount = total_invoice - COALESCE(paid_amount, 0.00)";
            }
        
            // Update invoice record
            $updateQuery = "
                UPDATE invoices 
                SET " . implode(', ', $update_parts) . "
                WHERE id = '$invoice_id' 
                AND project_id = '$project_id'
            ";
        
            if (!mysqli_query($db, $updateQuery)) {
                throw new Exception('Failed to update invoice record: ' . mysqli_error($db));
            }

            /*
                Update existing project ledger row.
                Jangan insert ledger baru untuk Finance Update.
                Ledger row INVOICE APPLICATION dah created masa Level 2 approve.
            */
            $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
            if ($ledger_table_check && mysqli_num_rows($ledger_table_check) > 0) {
                $ledger_columns = [];
                $ledger_column_result = mysqli_query($db, "SHOW COLUMNS FROM project_ledger");
                if ($ledger_column_result) {
                    while ($ledger_column_row = mysqli_fetch_assoc($ledger_column_result)) {
                        $ledger_columns[] = $ledger_column_row['Field'];
                    }
                }

                $safe_invoice_purpose = mysqli_real_escape_string($db, $invoice_purpose);
                $safe_total_invoice = mysqli_real_escape_string($db, $total_invoice);

                $ledger_update_parts = [];

                $possible_ledger_update = [
                    'project_no' => "'$project_no'",
                    'details' => "'$invoice_no'",
                    'details_2' => "'$safe_invoice_purpose'",
                    'invoice_no' => "'$invoice_no'",
                    'invoice_amount' => "'$safe_total_invoice'",
                    'debit_amount' => "'$safe_total_invoice'",
                    'notes' => "'Invoice approved and updated by Finance. Payment has not been received yet.'",
                    'fin_action' => "'Waiting Payment'",
                    'updated_by' => "'$staff_id'",
                    'updated_at' => "'$date_now'"
                ];

                foreach ($possible_ledger_update as $column => $value) {
                    if (in_array($column, $ledger_columns)) {
                        $ledger_update_parts[] = "$column = $value";
                    }
                }

                if (!empty($ledger_update_parts)) {
                    $ledgerUpdateQuery = "
                        UPDATE project_ledger
                        SET " . implode(', ', $ledger_update_parts) . "
                        WHERE source_type = 'invoice'
                        AND source_id = '$invoice_id'
                        AND transaction_category = 'INVOICE APPLICATION'
                        AND is_void = 0
                    ";

                    if (!mysqli_query($db, $ledgerUpdateQuery)) {
                        throw new Exception('Invoice updated but failed to update project ledger: ' . mysqli_error($db));
                    }
                }
            }
        
            // Insert tracker
            $remark = "Invoice details have been updated by Finance and are waiting for payment. Invoice No: $invoice_no ($staff_id)";
            $tracker_date = date('Y-m-d H:i:s');

            $insertTrackerQuery = "
                INSERT INTO project_tracker (project_id, project_no, remark, date)
                VALUES ('$project_id', '$project_no', '$remark', '$tracker_date')
            ";
        
            if (!mysqli_query($db, $insertTrackerQuery)) {
                throw new Exception('Invoice updated but failed to insert into tracker: ' . mysqli_error($db));
            }

            mysqli_commit($db);

            // Remove old invoice file if new file uploaded
            if (
                isset($_FILES['invoice_file']) &&
                $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK &&
                !empty($current_invoice_file) &&
                $current_invoice_file !== $unique_file_name &&
                file_exists($upload_dir . $current_invoice_file)
            ) {
                unlink($upload_dir . $current_invoice_file);
            }
        
            echo json_encode([
                'success' => true,
                'message' => 'Invoice details updated successfully. Invoice is now waiting for payment.'
            ]);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($db);

            // Remove newly uploaded file if DB update failed
            if (
                isset($unique_file_name) &&
                isset($_FILES['invoice_file']) &&
                $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK &&
                !empty($unique_file_name) &&
                file_exists("../../finance-invoice-documents/" . $unique_file_name)
            ) {
                unlink("../../finance-invoice-documents/" . $unique_file_name);
            }

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
?>