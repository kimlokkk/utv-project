<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa

    date_default_timezone_set('Asia/Kuala_Lumpur');

    /*
        Project Ledger Display
        Purpose:
        - Display financial movements in Excel-style project ledger format
        - Invoice application appears in Invoice column
        - Invoice application appears when billed, but does not affect available balance
        - Invoice-linked payment received appears as a child row and increases Balance C/F
        - Payment without invoice appears as standalone Revenue row
        - SST / UTV / PTJ / Insurance / Expenses appear in their own credit-side columns
        - Balance C/F is calculated on display
    */

    if (!isset($_GET['projectId']) || empty($_GET['projectId'])) {
        echo '<script>
            alert("Invalid project ID.");
            window.location.href = "invoice-listing.php";
        </script>';
        exit();
    }

    $project_id = mysqli_real_escape_string($db, $_GET['projectId']);

    // Query project details
    $project_query = "
        SELECT *
        FROM project
        WHERE id = '$project_id'
        LIMIT 1
    ";
    $project_result = mysqli_query($db, $project_query);

    if (!$project_result || mysqli_num_rows($project_result) === 0) {
        echo '<script>
            alert("Project not found.");
            window.location.href = "invoice-listing.php";
        </script>';
        exit();
    }

    $project = mysqli_fetch_assoc($project_result);

    $project_no = $project['project_no'];
    $project_title = $project['project_title'];
    $project_leader = $project['project_leader'];
    $client_company_name = $project['client_company_name'];
    $project_status = $project['project_status'];
    $registered_project_value = $project['registered_project_value'];
    $adjusted_project_value = $project['adjusted_project_value'];

    /*
        Pull all ledger rows first.
        We need to group invoice-linked payment rows into the invoice application row.
    */
    $ledger_query = "
        SELECT *
        FROM project_ledger
        WHERE project_id = '$project_id'
        AND is_void = 0
        ORDER BY created_at ASC, id ASC
    ";
    $ledger_result = mysqli_query($db, $ledger_query);

    $ledger_rows = [];

    if ($ledger_result) {
        while ($ledger = mysqli_fetch_assoc($ledger_result)) {
            $ledger_rows[] = $ledger;
        }
    }

    function ledger_row_timestamp($ledger) {
        $date_candidates = [
            $ledger['created_at'] ?? '',
            $ledger['transaction_date'] ?? '',
            $ledger['date'] ?? ''
        ];

        foreach ($date_candidates as $date_value) {
            $date_value = trim((string)$date_value);

            if ($date_value === '' || $date_value === '0000-00-00' || $date_value === '0000-00-00 00:00:00') {
                continue;
            }

            $formats = ['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y H:i:s', 'd/m/Y', 'd-m-Y H:i:s', 'd-m-Y'];

            foreach ($formats as $format) {
                $date = DateTime::createFromFormat($format, $date_value);
                if ($date instanceof DateTime) {
                    return $date->getTimestamp();
                }
            }

            $timestamp = strtotime($date_value);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        return 0;
    }

    usort($ledger_rows, function ($a, $b) {
        $a_timestamp = ledger_row_timestamp($a);
        $b_timestamp = ledger_row_timestamp($b);

        if ($a_timestamp !== $b_timestamp) {
            return ($a_timestamp < $b_timestamp) ? -1 : 1;
        }

        $a_id = (int)($a['id'] ?? 0);
        $b_id = (int)($b['id'] ?? 0);

        if ($a_id === $b_id) {
            return 0;
        }

        return ($a_id < $b_id) ? -1 : 1;
    });

    /*
        Build payment received map by invoice_id.
        This is used only to show PAID / PARTIAL PAYMENT / UNPAID on invoice rows.
        Balance C/F is increased by the actual payment received row.
    */
    $invoice_payment_map = [];

    foreach ($ledger_rows as $ledger) {
        $category_upper = strtoupper(trim($ledger['transaction_category'] ?? ''));
        $source_type_upper = strtoupper(trim($ledger['source_type'] ?? ''));
        $invoice_id_for_payment = $ledger['invoice_id'] ?? '';

        $is_payment_row = (
            strpos($category_upper, 'PAYMENT RECEIVED') !== false ||
            strpos($category_upper, 'RECEIVED') !== false ||
            strpos($source_type_upper, 'PAYMENT') !== false ||
            (float)($ledger['payment_received'] ?? 0) > 0
        );

        if ($is_payment_row && !empty($invoice_id_for_payment)) {
            $payment_amount = (float)($ledger['payment_received'] ?? 0);

            if ($payment_amount <= 0) {
                $payment_amount = (float)($ledger['debit_amount'] ?? 0);
            }

            if (!isset($invoice_payment_map[$invoice_id_for_payment])) {
                $invoice_payment_map[$invoice_id_for_payment] = 0;
            }

            $invoice_payment_map[$invoice_id_for_payment] += $payment_amount;
        }
    }

    /*
        Prepare display ledger rows.
        This lets us calculate totals before rendering summary cards.
    */
    $display_rows = [];
    $running_balance = 0;

    $total_invoice_column = 0;
    $total_revenue_column = 0;
    $total_loan_adjustment_column = 0;
    $total_sst_column = 0;
    $total_utv_column = 0;
    $total_ptj_column = 0;
    $total_insurance_column = 0;
    $total_advance_recon_column = 0;
    $total_expenses_column = 0;
    $total_other_debit_column = 0;
    $total_other_credit_column = 0;

    foreach ($ledger_rows as $ledger) {
        $category = trim($ledger['transaction_category'] ?? 'Ledger Entry');
        $category_upper = strtoupper($category);

        $source_type = trim($ledger['source_type'] ?? '');
        $source_type_upper = strtoupper($source_type);

        $invoice_id = $ledger['invoice_id'] ?? '';
        $invoice_no = $ledger['invoice_no'] ?? '';
        $details = $ledger['details'] ?? '';
        $details_2 = $ledger['details_2'] ?? '';
        $notes = $ledger['notes'] ?? '';

        $raw_debit = (float)($ledger['debit_amount'] ?? 0);
        $raw_credit = (float)($ledger['credit_amount'] ?? 0);
        $invoice_amount_raw = (float)($ledger['invoice_amount'] ?? 0);
        $loan_adjustment_raw = (float)($ledger['loan_adjustment_value'] ?? 0);
        $payment_received_raw = (float)($ledger['payment_received'] ?? 0);
        $expenses_amount_raw = (float)($ledger['expenses_amount'] ?? 0);

        $is_invoice_application = (
            strpos($category_upper, 'INVOICE APPLICATION') !== false ||
            ($invoice_amount_raw > 0 && $source_type_upper === 'INVOICE')
        );

        $is_payment_received = (
            strpos($category_upper, 'PAYMENT RECEIVED') !== false ||
            strpos($category_upper, 'RECEIVED') !== false ||
            strpos($source_type_upper, 'PAYMENT') !== false ||
            $payment_received_raw > 0
        );

        /*
            Invoice-linked payment received:
            - Displayed as a child row so users can see the payment.
            - Increases Balance C/F because cash has actually been received.
        */
        if ($is_payment_received && !empty($invoice_id)) {
            $transaction_date = !empty($ledger['transaction_date']) 
                ? date("d/m/Y", strtotime($ledger['transaction_date'])) 
                : '-';

            $payment_amount = $payment_received_raw > 0 ? $payment_received_raw : $raw_debit;

            $ref_no = !empty($invoice_no) ? $invoice_no : (!empty($details) ? $details : 'PAYMENT-' . ($ledger['source_id'] ?? $ledger['id']));
            $source_label = !empty($source_type) ? ucwords(str_replace('_', ' ', $source_type)) : 'Payment';
            $source_class = 'ledger-source-payment';

            $status_text = '';

            if (!empty($ledger['cst_action'])) {
                $status_text .= '<div><strong>CST:</strong> ' . htmlspecialchars($ledger['cst_action']) . '</div>';
            }

            if (!empty($ledger['fin_action'])) {
                $status_text .= '<div><strong>FIN:</strong> ' . htmlspecialchars($ledger['fin_action']) . '</div>';
            }

            if (empty($status_text)) {
                $status_text = '-';
            }

            $child_notes = trim(($notes ?? '') . "\nPayment received. Increases Balance C/F.");
            $running_balance += $payment_amount;
            $total_revenue_column += $payment_amount;

            $display_rows[] = [
                'transaction_date' => $transaction_date,
                'ref_no' => $ref_no,
                'category' => '↳ ' . $category,
                'details_2' => !empty($details_2) ? $details_2 : 'Payment received linked to invoice',
                'notes' => $child_notes,
                'source_label' => $source_label,
                'source_class' => $source_class,
                'invoice_column' => 0,
                'revenue_column' => $payment_amount,
                'loan_adjustment_column' => 0,
                'sst_column' => 0,
                'utv_column' => 0,
                'ptj_column' => 0,
                'insurance_column' => 0,
                'advance_recon_column' => 0,
                'expenses_column' => 0,
                'other_debit_column' => 0,
                'other_credit_column' => 0,
                'running_balance' => $running_balance,
                'status_text' => $status_text,
                'is_child_row' => true
            ];

            continue;
        }

        $transaction_date = !empty($ledger['transaction_date']) 
            ? date("d/m/Y", strtotime($ledger['transaction_date'])) 
            : '-';

        $ref_no = '-';

        if (!empty($invoice_no)) {
            $ref_no = $invoice_no;
        } elseif (!empty($details)) {
            $ref_no = $details;
        } elseif (!empty($source_type) && !empty($ledger['source_id'])) {
            $ref_no = strtoupper($source_type) . '-' . $ledger['source_id'];
        }

        $source_label = !empty($source_type) ? ucwords(str_replace('_', ' ', $source_type)) : 'Manual';

        $source_class = 'ledger-source-manual';

        if (stripos($source_type, 'invoice') !== false) {
            $source_class = 'ledger-source-invoice';
        } elseif (stripos($source_type, 'payment') !== false) {
            $source_class = 'ledger-source-payment';
        } elseif (stripos($category, 'sst') !== false) {
            $source_class = 'ledger-source-tax';
        }

        /*
            Excel-style columns.
        */
        $invoice_column = 0;
        $revenue_column = 0;
        $loan_adjustment_column = 0;
        $sst_column = 0;
        $utv_column = 0;
        $ptj_column = 0;
        $insurance_column = 0;
        $advance_recon_column = 0;
        $expenses_column = 0;
        $other_debit_column = 0;
        $other_credit_column = 0;

        $paid_status_note = '';
        $row_effect = 0;

        if ($is_invoice_application) {
            /*
                Invoice line:
                - Invoice amount appears in invoice column
                - Linked payment is used only to show paid status
                - Balance effect is zero until payment is actually received
            */
            $invoice_column = $invoice_amount_raw > 0 ? $invoice_amount_raw : $raw_debit;
            $paid_amount = !empty($invoice_id) && isset($invoice_payment_map[$invoice_id]) ? $invoice_payment_map[$invoice_id] : 0;

            $row_effect = 0;

            if ($paid_amount >= $invoice_column && $invoice_column > 0) {
                $paid_status_note = 'PAID';
            } elseif ($paid_amount > 0 && $paid_amount < $invoice_column) {
                $paid_status_note = 'PARTIAL PAYMENT';
            } else {
                $paid_status_note = 'UNPAID';
            }
        } elseif ($is_payment_received) {
            /*
                Payment received without invoice:
                - Appears in revenue column
                - Since no invoice to offset, it increases project received amount
            */
            $revenue_column = $payment_received_raw > 0 ? $payment_received_raw : $raw_debit;
            $row_effect = $revenue_column;
        } elseif (strpos($category_upper, 'BALANCE B/F') !== false || strpos($category_upper, 'BALANCE BF') !== false) {
            $other_debit_column = $raw_debit > 0 ? $raw_debit : $loan_adjustment_raw;
            $row_effect = $other_debit_column;
        } elseif (
            strpos($category_upper, 'PFA') !== false ||
            strpos($category_upper, 'PROJECT FUNDING ASSISTANCE') !== false ||
            strpos($category_upper, 'LOAN') !== false ||
            strpos($category_upper, 'ADJUSTMENT') !== false
        ) {
            /*
                Loan / adjustment debit.
            */
            $loan_adjustment_column = $loan_adjustment_raw > 0 ? $loan_adjustment_raw : $raw_debit;
            $row_effect = $loan_adjustment_column;
        } elseif (strpos($category_upper, 'SST') !== false) {
            $sst_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : $raw_debit);
            $row_effect = 0 - $sst_column;
        } elseif (strpos($category_upper, 'UTV') !== false) {
            $utv_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : $raw_debit);
            $row_effect = 0 - $utv_column;
        } elseif (strpos($category_upper, 'PTJ') !== false) {
            $ptj_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : $raw_debit);
            $row_effect = 0 - $ptj_column;
        } elseif (strpos($category_upper, 'INSURANCE') !== false) {
            $insurance_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : $raw_debit);
            $row_effect = 0 - $insurance_column;
        } elseif (
            strpos($category_upper, 'ADVANCE') !== false ||
            strpos($category_upper, 'RECONCILIATION') !== false
        ) {
            /*
                Advance / reconciliation can be credit or debit depending client rule.
            */
            if ($raw_debit > 0 || $loan_adjustment_raw > 0) {
                $advance_recon_column = $raw_debit > 0 ? $raw_debit : $loan_adjustment_raw;
                $row_effect = $advance_recon_column;
            } else {
                $advance_recon_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : 0);
                $row_effect = 0 - $advance_recon_column;
            }
        } elseif (
            strpos($category_upper, 'PROFESSIONAL') !== false ||
            strpos($category_upper, 'ALLOWANCE') !== false ||
            strpos($category_upper, 'TOKEN') !== false ||
            strpos($category_upper, 'PURCHASE ORDER') !== false ||
            strpos($category_upper, 'VENDOR') !== false ||
            strpos($category_upper, 'REIMBURSABLE') !== false ||
            strpos($category_upper, 'TRAVELLING') !== false ||
            strpos($category_upper, 'F&B') !== false ||
            strpos($category_upper, 'EXPENSE') !== false ||
            strpos($category_upper, 'FEE') !== false ||
            strpos($category_upper, 'CHARGE') !== false
        ) {
            $expenses_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : $raw_debit);
            $row_effect = 0 - $expenses_column;
        } else {
            /*
                Fallback:
                - Debit increases balance
                - Credit decreases balance
            */
            if ($raw_debit > 0) {
                $other_debit_column = $raw_debit;
                $row_effect = $other_debit_column;
            }

            if ($raw_credit > 0) {
                $other_credit_column = $raw_credit;
                $row_effect = 0 - $other_credit_column;
            }
        }

        $running_balance += $row_effect;

        $total_invoice_column += $invoice_column;
        $total_revenue_column += $revenue_column;
        $total_loan_adjustment_column += $loan_adjustment_column;
        $total_sst_column += $sst_column;
        $total_utv_column += $utv_column;
        $total_ptj_column += $ptj_column;
        $total_insurance_column += $insurance_column;
        $total_advance_recon_column += $advance_recon_column;
        $total_expenses_column += $expenses_column;
        $total_other_debit_column += $other_debit_column;
        $total_other_credit_column += $other_credit_column;

        $status_text = '';

        if (!empty($ledger['cst_action'])) {
            $status_text .= '<div><strong>CST:</strong> ' . htmlspecialchars($ledger['cst_action']) . '</div>';
        }

        if (!empty($ledger['fin_action'])) {
            $status_text .= '<div><strong>FIN:</strong> ' . htmlspecialchars($ledger['fin_action']) . '</div>';
        }

        if (empty($status_text)) {
            $status_text = '-';
        }

        $display_notes = $notes;

        if (!empty($paid_status_note) && $is_invoice_application) {
            $display_notes = trim($display_notes . "\n" . $paid_status_note);
        }

        $display_rows[] = [
            'transaction_date' => $transaction_date,
            'ref_no' => $ref_no,
            'category' => $category,
            'details_2' => $details_2,
            'notes' => $display_notes,
            'source_label' => $source_label,
            'source_class' => $source_class,
            'invoice_column' => $invoice_column,
            'revenue_column' => $revenue_column,
            'loan_adjustment_column' => $loan_adjustment_column,
            'sst_column' => $sst_column,
            'utv_column' => $utv_column,
            'ptj_column' => $ptj_column,
            'insurance_column' => $insurance_column,
            'advance_recon_column' => $advance_recon_column,
            'expenses_column' => $expenses_column,
            'other_debit_column' => $other_debit_column,
            'other_credit_column' => $other_credit_column,
            'running_balance' => $running_balance,
            'status_text' => $status_text,
            'is_child_row' => false
        ];
    }

    $total_credit_side = $total_sst_column + $total_utv_column + $total_ptj_column + $total_insurance_column + $total_advance_recon_column + $total_expenses_column + $total_other_credit_column;
    $total_debit_side = $total_invoice_column + $total_revenue_column + $total_loan_adjustment_column + $total_other_debit_column;

    $balance_cf = $running_balance;

    $optional_ledger_columns = [
        [
            'key' => 'loan_adjustment_column',
            'total' => $total_loan_adjustment_column,
            'label' => 'Loan / Adj.',
            'class' => 'ledger-debit'
        ],
        [
            'key' => 'sst_column',
            'total' => $total_sst_column,
            'label' => 'SST',
            'class' => 'ledger-credit'
        ],
        [
            'key' => 'utv_column',
            'total' => $total_utv_column,
            'label' => 'UTV Fee',
            'class' => 'ledger-credit'
        ],
        [
            'key' => 'ptj_column',
            'total' => $total_ptj_column,
            'label' => 'PTJ Fee',
            'class' => 'ledger-credit'
        ],
        [
            'key' => 'insurance_column',
            'total' => $total_insurance_column,
            'label' => 'Insurance',
            'class' => 'ledger-credit'
        ],
        [
            'key' => 'advance_recon_column',
            'total' => $total_advance_recon_column,
            'label' => 'Advance / Recon',
            'class' => ''
        ],
        [
            'key' => 'expenses_column',
            'total' => $total_expenses_column,
            'label' => 'Expenses / Fees',
            'class' => 'ledger-credit'
        ],
        [
            'key' => 'other_debit_column',
            'total' => $total_other_debit_column,
            'label' => 'Other DR',
            'class' => 'ledger-debit'
        ],
        [
            'key' => 'other_credit_column',
            'total' => $total_other_credit_column,
            'label' => 'Other CR',
            'class' => 'ledger-credit'
        ]
    ];

    $visible_optional_ledger_columns = $optional_ledger_columns;
    $compact_hidden_column_indexes = [];
    $base_optional_column_index = 7;

    foreach ($optional_ledger_columns as $index => $column) {
        if (abs((float)$column['total']) <= 0.00001) {
            $compact_hidden_column_indexes[] = $base_optional_column_index + $index;
        }
    }

    $ledger_table_column_count = 9 + count($visible_optional_ledger_columns);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <style>
        .ledger-page-title {
            font-weight: 700;
            color: #1f2937;
        }

        .ledger-paper {
            background: #ffffff;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.04);
        }

        .ledger-header {
            border-bottom: 2px solid #198754;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .ledger-company-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #1f2937;
        }

        .ledger-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 0;
        }

        .ledger-meta-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
            min-height: 76px;
        }

        .ledger-meta-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 3px;
            font-weight: 700;
            letter-spacing: .03em;
        }

        .ledger-meta-value {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
            line-height: 1.35;
        }

        .ledger-summary-card {
            border: 1px solid #e5e7eb;
            border-left: 5px solid #198754;
            background: #ffffff;
            border-radius: 6px;
            padding: 15px;
            height: 100%;
            margin-bottom: 15px;
        }

        .ledger-summary-card.invoice {
            border-left-color: #1d4ed8;
        }

        .ledger-summary-card.revenue {
            border-left-color: #047857;
        }

        .ledger-summary-card.credit {
            border-left-color: #b91c1c;
        }

        .ledger-summary-card.balance {
            border-left-color: #0f766e;
        }

        .ledger-summary-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: .03em;
        }

        .ledger-summary-value {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        .ledger-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-top: 10px;
            margin-bottom: 12px;
        }

        .ledger-table-wrapper {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow-x: auto;
            overflow-y: hidden;
            width: 100%;
        }

        .ledger-table {
            font-size: 11px;
            margin-bottom: 0;
            border: none;
            width: 100% !important;
        }

        .ledger-table-wrapper.ledger-full-details .ledger-table {
            min-width: 1500px;
        }

        .ledger-table-wrapper .dataTables_scroll,
        .ledger-table-wrapper .dataTables_scrollHead,
        .ledger-table-wrapper .dataTables_scrollBody {
            width: 100% !important;
        }

        .ledger-table-wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
        }

        .ledger-table thead th {
            background: #14532d;
            color: #ffffff;
            border-color: #14532d !important;
            vertical-align: middle;
            text-align: center;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
            padding: 8px 6px;
        }

        .ledger-table tbody td {
            border-color: #e5e7eb !important;
            vertical-align: top;
            background: #ffffff;
            padding: 8px 6px;
        }

        .ledger-table tbody tr:nth-child(even) td {
            background: #fbfdff;
        }

        .ledger-child-row td {
            background: #f8fafc !important;
            color: #4b5563;
        }

        .ledger-child-row .ledger-particular-title {
            color: #047857;
            font-size: 12px;
        }

        .ledger-child-row .ledger-ref {
            color: #047857;
        }

        .ledger-table tfoot td {
            background: #f3f4f6;
            font-weight: 800;
            border-top: 2px solid #198754 !important;
        }

        .ledger-date {
            white-space: nowrap;
            font-weight: 700;
            color: #374151;
            text-align: center;
        }

        .ledger-ref {
            white-space: nowrap;
            font-weight: 700;
            color: #1d4ed8;
            text-align: center;
        }

        .ledger-particulars {
            min-width: 260px;
            white-space: normal !important;
        }

        .ledger-particular-title {
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .ledger-particular-note {
            color: #6b7280;
            font-size: 11px;
            line-height: 1.45;
            margin-bottom: 3px;
        }

        .ledger-source-badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            background: #e5e7eb;
            color: #374151;
            white-space: nowrap;
        }

        .ledger-source-invoice {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .ledger-source-payment {
            background: #dcfce7;
            color: #047857;
        }

        .ledger-source-tax {
            background: #fee2e2;
            color: #b91c1c;
        }

        .ledger-source-manual {
            background: #f3f4f6;
            color: #374151;
        }

        .ledger-amount {
            text-align: right;
            white-space: nowrap;
            font-family: "Courier New", monospace;
            font-weight: 700;
        }

        .ledger-invoice {
            color: #1d4ed8;
        }

        .ledger-revenue {
            color: #047857;
        }

        .ledger-debit {
            color: #047857;
        }

        .ledger-credit {
            color: #b91c1c;
        }

        .ledger-balance-positive {
            color: #047857;
            font-weight: 800;
        }

        .ledger-balance-negative {
            color: #b91c1c;
            font-weight: 800;
        }

        .ledger-empty {
            color: #9ca3af;
            text-align: center;
            font-style: italic;
            padding: 25px !important;
        }

        .ledger-status {
            min-width: 160px;
            white-space: normal !important;
            font-size: 11px;
            color: #374151;
        }

        .ledger-status strong {
            color: #111827;
        }

        .ledger-print-note {
            font-size: 12px;
            color: #6b7280;
            margin-top: 12px;
        }

        .ledger-action-bar {
            margin-bottom: 12px;
        }

        .ledger-action-bar .btn {
            margin-left: 5px;
        }

        .dataTables_wrapper {
            padding-top: 8px;
        }

        @media print {
            .left-sidebar,
            .topbar,
            .footer,
            .page-titles,
            .ledger-action-bar,
            .dataTables_filter,
            .dataTables_length,
            .dataTables_info,
            .dataTables_paginate,
            .alert {
                display: none !important;
            }

            .page-wrapper {
                margin-left: 0 !important;
            }

            .container-fluid {
                padding: 0 !important;
            }

            .card,
            .ledger-paper {
                box-shadow: none !important;
                border: none !important;
            }

            .card-body {
                padding: 0 !important;
            }

            .ledger-table {
                font-size: 9px;
            }

            .ledger-company-title {
                font-size: 18px;
            }

            body {
                background: #ffffff !important;
            }
        }
    </style>
</head>

<body class="skin-green fixed-layout">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'include/preloader.php'; ?>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php include 'include/topbar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php include 'include/left_sidebar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor ledger-page-title">Project Ledger</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Project Finance</a></li>
                                <li class="breadcrumb-item active">Project Ledger</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Info box -->
                <!-- ============================================================== -->

                <!-- Ledger Paper -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card ledger-paper">
                            <div class="card-body">
                                <div class="ledger-header">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="ledger-company-title">Project Ledger Statement</div>
                                            <p class="ledger-subtitle">
                                                Excel-style project ledger showing invoice, revenue, deductions and balance carried forward.
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-right ledger-action-bar">
                                            <button type="button" class="btn btn-success" onclick="window.print();">
                                                Print Ledger
                                            </button>
                                            <a href="invoice-listing.php" class="btn btn-secondary">
                                                Back
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Project Meta -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Project No</div>
                                            <div class="ledger-meta-value"><?php echo htmlspecialchars($project_no); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Project Title</div>
                                            <div class="ledger-meta-value"><?php echo htmlspecialchars($project_title); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Project Leader</div>
                                            <div class="ledger-meta-value"><?php echo htmlspecialchars($project_leader); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Client</div>
                                            <div class="ledger-meta-value"><?php echo htmlspecialchars($client_company_name); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Project Status</div>
                                            <div class="ledger-meta-value"><?php echo htmlspecialchars($project_status); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Registered Project Value</div>
                                            <div class="ledger-meta-value">RM <?php echo number_format((float)$registered_project_value, 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ledger-meta-box">
                                            <div class="ledger-meta-label">Adjusted Project Value</div>
                                            <div class="ledger-meta-value">RM <?php echo number_format((float)$adjusted_project_value, 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Project Meta -->

                                <!-- Ledger Summary -->
                                <div class="row m-b-20">
                                    <div class="col-md-4">
                                        <div class="ledger-summary-card invoice">
                                            <div class="ledger-summary-label">Total Invoice</div>
                                            <div class="ledger-summary-value ledger-invoice">
                                                RM <?php echo number_format((float)$total_invoice_column, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ledger-summary-card revenue">
                                            <div class="ledger-summary-label">Total Revenue Received</div>
                                            <div class="ledger-summary-value ledger-revenue">
                                                RM <?php echo number_format((float)$total_revenue_column, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ledger-summary-card credit">
                                            <div class="ledger-summary-label">Total Credit / Deduction</div>
                                            <div class="ledger-summary-value ledger-credit">
                                                RM <?php echo number_format((float)$total_credit_side, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Ledger Summary -->

                                <div class="alert alert-info">
                                    Invoice applications are shown when billed. Balance C/F changes only when payment is received, or when project deductions such as claims, advances, reconciliations, fees and expenses are recorded.
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center m-b-10">
                                    <div class="ledger-section-title m-b-0">Project Ledger Account</div>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Ledger view mode">
                                        <button type="button" id="ledgerCompactView" class="btn btn-success active">Compact View</button>
                                        <button type="button" id="ledgerFullView" class="btn btn-outline-success">Full Details</button>
                                    </div>
                                </div>

                                <div class="table-responsive ledger-table-wrapper">
                                    <table id="project-ledger-table" class="table table-bordered ledger-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Date</th>
                                                <th>Ref / Doc No</th>
                                                <th>Particulars</th>
                                                <th>Source</th>
                                                <th>Invoice</th>
                                                <th>Revenue</th>
                                                <?php foreach ($visible_optional_ledger_columns as $column) { ?>
                                                    <th><?php echo htmlspecialchars($column['label']); ?></th>
                                                <?php } ?>
                                                <th>Balance C/F</th>
                                                <th>Status / Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $no = 1;

                                                if (!empty($display_rows)) {
                                                    foreach ($display_rows as $row) {
                                                        $balance_class = ($row['running_balance'] >= 0) ? 'ledger-balance-positive' : 'ledger-balance-negative';
                                            ?>
                                            <tr class="<?php echo !empty($row['is_child_row']) ? 'ledger-child-row' : ''; ?>">
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td class="ledger-date"><?php echo $row['transaction_date']; ?></td>
                                                <td class="ledger-ref"><?php echo htmlspecialchars($row['ref_no']); ?></td>
                                                <td class="ledger-particulars">
                                                    <div class="ledger-particular-title">
                                                        <?php echo htmlspecialchars($row['category']); ?>
                                                    </div>

                                                    <?php if (!empty($row['details_2'])) { ?>
                                                        <div class="ledger-particular-note">
                                                            <?php echo htmlspecialchars($row['details_2']); ?>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if (!empty($row['notes'])) { ?>
                                                        <div class="ledger-particular-note">
                                                            <?php echo nl2br(htmlspecialchars($row['notes'])); ?>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="ledger-source-badge <?php echo $row['source_class']; ?>">
                                                        <?php echo htmlspecialchars($row['source_label']); ?>
                                                    </span>
                                                </td>
                                                <td class="ledger-amount ledger-invoice">
                                                    <?php echo ($row['invoice_column'] > 0) ? number_format($row['invoice_column'], 2) : '-'; ?>
                                                </td>
                                                <td class="ledger-amount ledger-revenue">
                                                    <?php echo ($row['revenue_column'] > 0) ? number_format($row['revenue_column'], 2) : '-'; ?>
                                                </td>
                                                <?php foreach ($visible_optional_ledger_columns as $column) { ?>
                                                <td class="ledger-amount <?php echo htmlspecialchars($column['class']); ?>">
                                                    <?php
                                                        $column_value = (float)($row[$column['key']] ?? 0);
                                                        echo ($column_value > 0) ? number_format($column_value, 2) : '-';
                                                    ?>
                                                </td>
                                                <?php } ?>
                                                <td class="ledger-amount <?php echo $balance_class; ?>">
                                                    -
                                                </td>
                                                <td class="ledger-status">
                                                    <?php echo $row['status_text']; ?>
                                                </td>
                                            </tr>
                                            <?php
                                                    }
                                                } else {
                                            ?>
                                            <tr>
                                                <td colspan="<?php echo $ledger_table_column_count; ?>" class="ledger-empty">
                                                    No ledger transaction found for this project.
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5" class="text-right">Total</td>
                                                <td class="ledger-amount ledger-invoice">
                                                    <?php echo number_format((float)$total_invoice_column, 2); ?>
                                                </td>
                                                <td class="ledger-amount ledger-revenue">
                                                    <?php echo number_format((float)$total_revenue_column, 2); ?>
                                                </td>
                                                <?php foreach ($visible_optional_ledger_columns as $column) { ?>
                                                <td class="ledger-amount <?php echo htmlspecialchars($column['class']); ?>">
                                                    <?php echo number_format((float)$column['total'], 2); ?>
                                                </td>
                                                <?php } ?>
                                                <td class="ledger-amount <?php echo ($balance_cf >= 0) ? 'ledger-balance-positive' : 'ledger-balance-negative'; ?>">
                                                    <?php echo number_format((float)$balance_cf, 2); ?>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="ledger-print-note">
                                    Generated on <?php echo date("d/m/Y h:i A"); ?>. Balance C/F is auto-calculated from received revenue minus project deductions, claims, advances, reconciliations, fees and expenses.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Ledger Paper -->

                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <?php include 'include/footer.php'; ?>
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Logout Modal -->
        <!-- ============================================================== -->
        <?php include 'include/logoutmodal.php'; ?>
        <!-- ============================================================== -->
        <!-- End Logout Modal -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap popper Core JavaScript -->
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            const compactHiddenColumns = <?php echo json_encode($compact_hidden_column_indexes); ?>;
            const ledgerTable = $('#project-ledger-table').DataTable({
                "pageLength": 25,
                "order": [],
                "ordering": false,
                "responsive": false,
                "scrollX": true,
                "scrollCollapse": true,
                "autoWidth": false,
                "stateSave": false
            });

            function setLedgerViewMode(mode) {
                const isCompact = mode === 'compact';
                const ledgerWrapper = $('.ledger-table-wrapper');

                compactHiddenColumns.forEach(function(columnIndex) {
                    ledgerTable.column(columnIndex).visible(!isCompact, false);
                });

                ledgerWrapper
                    .toggleClass('ledger-compact-details', isCompact)
                    .toggleClass('ledger-full-details', !isCompact);

                ledgerTable.columns.adjust().draw(false);

                setTimeout(function() {
                    ledgerTable.columns.adjust().draw(false);
                }, 50);

                $('#ledgerCompactView')
                    .toggleClass('active btn-success', isCompact)
                    .toggleClass('btn-outline-success', !isCompact);

                $('#ledgerFullView')
                    .toggleClass('active btn-success', !isCompact)
                    .toggleClass('btn-outline-success', isCompact);
            }

            $('#ledgerCompactView').on('click', function() {
                setLedgerViewMode('compact');
            });

            $('#ledgerFullView').on('click', function() {
                setLedgerViewMode('full');
            });

            setLedgerViewMode('compact');
        });
    </script>
</body>
</html>
