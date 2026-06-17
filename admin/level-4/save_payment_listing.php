<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../db_connect/db_connect.php';
include '../../function/function.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Validate required fields
$payment_category_raw = $_POST['payment_category'] ?? '';
$project_id_raw = $_POST['project_id'] ?? '';
$invoice_id_raw = $_POST['invoice_id'] ?? '';
$payment_date_raw = $_POST['payment_date'] ?? '';
$amount_received_raw = $_POST['amount_received'] ?? '';
$created_by_raw = $_POST['created_by'] ?? '';

if (
    trim($payment_category_raw) === '' ||
    trim($project_id_raw) === '' ||
    trim($payment_date_raw) === '' ||
    trim($amount_received_raw) === '' ||
    trim($created_by_raw) === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required payment fields.'
    ]);
    exit;
}

$amount_received_check = (float)$amount_received_raw;
if ($amount_received_check <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Amount received must be greater than zero.'
    ]);
    exit;
}

if ($payment_category_raw === 'Invoice Payment' && trim($invoice_id_raw) === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Invoice is required for Invoice Payment.'
    ]);
    exit;
}

$payment_category = mysqli_real_escape_string($db, $payment_category_raw);
$project_id = mysqli_real_escape_string($db, $project_id_raw);
$invoice_id = trim($invoice_id_raw) !== '' ? mysqli_real_escape_string($db, $invoice_id_raw) : null;
$payment_date = mysqli_real_escape_string($db, $payment_date_raw);
$amount_received = mysqli_real_escape_string($db, number_format((float)$amount_received_raw, 2, '.', ''));
$payer_name = mysqli_real_escape_string($db, $_POST['payer_name'] ?? '');
$payment_method = mysqli_real_escape_string($db, $_POST['payment_method'] ?? '');
$bank_reference = mysqli_real_escape_string($db, $_POST['bank_reference'] ?? '');
$receipt_reference = mysqli_real_escape_string($db, $_POST['receipt_reference'] ?? '');
$notes = mysqli_real_escape_string($db, $_POST['notes'] ?? '');
$created_by = mysqli_real_escape_string($db, $created_by_raw);
$date_now = date('Y-m-d H:i:s');

$payment_status = 'Pending HOD Verification';

// Determine related source
$related_source_type = null;
$related_source_id = null;

if ($payment_category === 'Invoice Payment' && !empty($invoice_id)) {
    $related_source_type = 'invoice';
    $related_source_id = $invoice_id;
} elseif ($payment_category === 'Advance Refund') {
    $related_source_type = 'advance';
} elseif ($payment_category === 'Fund Received') {
    $related_source_type = 'fund_received';
} elseif ($payment_category === 'Refund Received') {
    $related_source_type = 'refund_received';
} else {
    $related_source_type = 'manual';
}

$related_source_type_sql = !empty($related_source_type) ? "'" . mysqli_real_escape_string($db, $related_source_type) . "'" : "NULL";
$related_source_id_sql = !empty($related_source_id) ? "'" . mysqli_real_escape_string($db, $related_source_id) . "'" : "NULL";
$invoice_id_sql = !empty($invoice_id) ? "'$invoice_id'" : "NULL";

// Upload payment attachment
$upload_dir = "../payment-documents/";
$payment_attachment = '';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (isset($_FILES['payment_attachment']) && $_FILES['payment_attachment']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['payment_attachment']['tmp_name'];
    $file_name = $_FILES['payment_attachment']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX.'
        ]);
        exit;
    }

    $payment_attachment = uniqid('payment_', true) . '.' . $file_ext;

    if (!move_uploaded_file($file_tmp, $upload_dir . $payment_attachment)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload payment attachment.'
        ]);
        exit;
    }
}

// Mulakan transaksi
mysqli_begin_transaction($db);

try {
    // Get project data
    $project_query = "
        SELECT 
            id,
            project_no,
            project_title,
            project_leader,
            leader_id,
            client_company_name
        FROM project
        WHERE id = '$project_id'
        LIMIT 1
    ";
    $project_result = mysqli_query($db, $project_query);

    if (!$project_result || mysqli_num_rows($project_result) === 0) {
        throw new Exception('Project not found.');
    }

    $project = mysqli_fetch_assoc($project_result);

    $project_no = $project['project_no'];
    $project_title = $project['project_title'];
    $project_leader = $project['project_leader'];
    $leader_id = $project['leader_id'];
    $client_company_name = $project['client_company_name'];

    // If payer name empty, use client company
    if (empty($payer_name)) {
        $payer_name = mysqli_real_escape_string($db, $client_company_name);
    }

    $invoice_no = '';
    $invoice_total = 0;
    $invoice_purpose = '';

    // Get invoice data if invoice selected
    if (!empty($invoice_id)) {
        $invoice_query = "
            SELECT 
                id,
                project_id,
                invoice_no,
                invoice_purpose,
                total_invoice,
                paid_amount,
                outstanding_amount,
                payment_status
            FROM invoices
            WHERE id = '$invoice_id'
            AND project_id = '$project_id'
            LIMIT 1
        ";
        $invoice_result = mysqli_query($db, $invoice_query);

        if (!$invoice_result || mysqli_num_rows($invoice_result) === 0) {
            throw new Exception('Invoice not found for selected project.');
        }

        $invoice = mysqli_fetch_assoc($invoice_result);

        $invoice_no = $invoice['invoice_no'];
        $invoice_total = (float)$invoice['total_invoice'];
        $invoice_purpose = $invoice['invoice_purpose'];
    }

    // Insert into payment_listing
    $insert_payment_query = "
        INSERT INTO payment_listing (
            project_id,
            invoice_id,
            payment_category,
            related_source_type,
            related_source_id,
            payment_date,
            amount_received,
            payment_method,
            bank_reference,
            receipt_reference,
            payment_attachment,
            payer_name,
            notes,
            payment_status,
            created_by,
            created_at
        ) VALUES (
            '$project_id',
            $invoice_id_sql,
            '$payment_category',
            $related_source_type_sql,
            $related_source_id_sql,
            '$payment_date',
            '$amount_received',
            '$payment_method',
            '$bank_reference',
            '$receipt_reference',
            '$payment_attachment',
            '$payer_name',
            '$notes',
            '$payment_status',
            '$created_by',
            '$date_now'
        )
    ";

    if (!mysqli_query($db, $insert_payment_query)) {
        throw new Exception('Failed to save payment listing: ' . mysqli_error($db));
    }

    $payment_id = mysqli_insert_id($db);

    /*
        If invoice selected:
        Recalculate paid amount and outstanding amount from all payment_listing rows.
        This makes it safer than simply adding amount to existing paid_amount.
    */
    if (!empty($invoice_id)) {
        $total_paid_query = "
            SELECT COALESCE(SUM(amount_received), 0) AS total_paid
            FROM payment_listing
            WHERE invoice_id = '$invoice_id'
        ";
        $total_paid_result = mysqli_query($db, $total_paid_query);

        if (!$total_paid_result) {
            throw new Exception('Failed to calculate total paid amount: ' . mysqli_error($db));
        }

        $total_paid_row = mysqli_fetch_assoc($total_paid_result);
        $total_paid = (float)$total_paid_row['total_paid'];
        $outstanding_amount = $invoice_total - $total_paid;

        if ($total_paid <= 0) {
            $new_payment_status = 'Unpaid';
        } elseif ($total_paid < $invoice_total) {
            $new_payment_status = 'Partial';
        } elseif ($total_paid == $invoice_total) {
            $new_payment_status = 'Paid';
        } else {
            $new_payment_status = 'Overpaid';
        }

        $new_invoice_status = ($new_payment_status === 'Paid' || $new_payment_status === 'Overpaid')
            ? 'Fully Paid'
            : 'Waiting Payment';

        $safe_total_paid = mysqli_real_escape_string($db, number_format($total_paid, 2, '.', ''));
        $safe_outstanding = mysqli_real_escape_string($db, number_format($outstanding_amount, 2, '.', ''));
        $safe_invoice_status = mysqli_real_escape_string($db, $new_invoice_status);

        $update_invoice_query = "
            UPDATE invoices
            SET 
                paid_amount = '$safe_total_paid',
                outstanding_amount = '$safe_outstanding',
                payment_status = '$new_payment_status',
                invoice_status = '$safe_invoice_status',
                last_payment_date = '$payment_date'
            WHERE id = '$invoice_id'
            AND project_id = '$project_id'
        ";

        if (!mysqli_query($db, $update_invoice_query)) {
            throw new Exception('Failed to update invoice payment status: ' . mysqli_error($db));
        }
    }

    /*
        Insert into project_ledger as money received / debit.
        Payment Listing is money-in flow, so direction is DR.
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

        $ledger_category = 'PAYMENT RECEIVED';

        if ($payment_category === 'Advance Refund') {
            $ledger_category = 'ADVANCE REFUND';
        } elseif ($payment_category === 'Fund Received') {
            $ledger_category = 'FUND RECEIVED FROM';
        } elseif ($payment_category === 'Refund Received') {
            $ledger_category = 'REFUND RECEIVED';
        } elseif ($payment_category === 'Other Received') {
            $ledger_category = 'RECEIVED';
        }

        $safe_project_no = mysqli_real_escape_string($db, $project_no);
        $safe_invoice_no = mysqli_real_escape_string($db, $invoice_no);
        $safe_details = !empty($invoice_no) ? $safe_invoice_no : mysqli_real_escape_string($db, $payment_category);
        $safe_details_2 = mysqli_real_escape_string($db, $payer_name);
        $safe_notes = mysqli_real_escape_string($db, "Payment received. Pending HOD verification. " . $notes);

        $ledger_data = [
            'project_id' => "'$project_id'",
            'project_no' => "'$safe_project_no'",
            'source_type' => "'payment_listing'",
            'source_id' => "'$payment_id'",
            'transaction_date' => "'$payment_date'",
            'transaction_category' => "'" . mysqli_real_escape_string($db, $ledger_category) . "'",
            'details' => "'$safe_details'",
            'details_2' => "'$safe_details_2'",
            'invoice_id' => !empty($invoice_id) ? "'$invoice_id'" : "NULL",
            'invoice_no' => !empty($safe_invoice_no) ? "'$safe_invoice_no'" : "NULL",
            'invoice_amount' => !empty($invoice_total) ? "'" . mysqli_real_escape_string($db, number_format($invoice_total, 2, '.', '')) . "'" : "'0.00'",
            'loan_adjustment_value' => "'0.00'",
            'payment_received' => "'$amount_received'",
            'expenses_amount' => "'0.00'",
            'debit_amount' => "'$amount_received'",
            'credit_amount' => "'0.00'",
            'notes' => "'$safe_notes'",
            'cst_action' => "'Payment Recorded'",
            'fin_action' => "'Pending Finance Completion'",
            'created_by' => "'$created_by'",
            'created_at' => "'$date_now'",
            'is_void' => "'0'"
        ];

        $insert_columns = [];
        $insert_values = [];

        foreach ($ledger_data as $column => $value) {
            if (in_array($column, $ledger_columns)) {
                $insert_columns[] = $column;
                $insert_values[] = $value;
            }
        }

        if (!empty($insert_columns)) {
            $ledger_query = "
                INSERT INTO project_ledger (" . implode(', ', $insert_columns) . ")
                VALUES (" . implode(', ', $insert_values) . ")
            ";

            if (!mysqli_query($db, $ledger_query)) {
                throw new Exception('Failed to insert payment into project ledger: ' . mysqli_error($db));
            }
        }
    }

    // Insert tracker
    $safe_project_no = mysqli_real_escape_string($db, $project_no);
    $safe_invoice_text = !empty($invoice_no) ? " Invoice No: " . mysqli_real_escape_string($db, $invoice_no) . "." : "";
    $tracker_remark = mysqli_real_escape_string(
        $db,
        "Payment has been recorded. Category: $payment_category. Amount: RM $amount_received.$safe_invoice_text Pending HOD verification. ($created_by)"
    );

    $tracker_query = "
        INSERT INTO project_tracker (project_id, project_no, remark, date)
        VALUES ('$project_id', '$safe_project_no', '$tracker_remark', '$date_now')
    ";

    if (!mysqli_query($db, $tracker_query)) {
        throw new Exception('Payment saved but failed to insert project tracker: ' . mysqli_error($db));
    }

    mysqli_commit($db);

    echo json_encode([
        'success' => true,
        'message' => 'Payment has been saved, invoice listing updated if applicable, and project ledger updated.'
    ]);
    exit;

} catch (Exception $e) {
    mysqli_rollback($db);

    // Remove uploaded file if DB process failed
    if (!empty($payment_attachment) && file_exists($upload_dir . $payment_attachment)) {
        unlink($upload_dir . $payment_attachment);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
