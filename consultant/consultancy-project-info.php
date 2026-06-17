<?php
    if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';

    if (empty($id)) {
        echo '<script>
            alert("Invalid project ID.");
            window.location.href = "index.php";
        </script>';
        exit();
    }

    $query = "SELECT * FROM project WHERE id = '$id' ";  
    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<script>
            alert("Project not found.");
            window.location.href = "index.php";
        </script>';
        exit();
    }

    while($row = mysqli_fetch_array($result))  
    {
        $leader_id                      = $row['leader_id'];
        $project_leader                 = $row['project_leader'];
        $project_no                     = $row['project_no'];
        $project_title                  = $row['project_title'];
        $project_type                   = $row['project_type'];
        $project_start                  = $row['project_start'];
        $project_end                    = $row['project_end'];
        $registered_project_value       = $row['registered_project_value'];
        $adjusted_project_value         = $row['adjusted_project_value'];
        $quotation_ref_no               = $row['quotation_ref_no'];
        $appointment_letter             = $row['appointment_letter'];
        $approval_external_work         = $row['approval_external_work'];
        $quotation_doc                  = $row['quotation_doc'];
        $agreement_doc                  = $row['agreement_doc'];
        $project_proposal               = $row['project_proposal'];
        $other_doc_1                    = $row['other_doc_1'];
        $other_doc_2                    = $row['other_doc_2'];
        $client_company_name            = $row['client_company_name'];
        $client_address                 = $row['client_address'];
        $client_contact                 = $row['client_contact'];
        $client_business_type           = $row['client_business_type'];
        $client_pic                     = $row['client_pic'];
        $client_pic_email               = $row['client_pic_email'];
        $client_pic_contact             = $row['client_pic_contact'];
        $date_create                    = $row['date_create'];
        $project_status                 = $row['project_status'];
        $return_to                      = $row['return_to'] ?? '';
        $return_remark                  = $row['return_remark'] ?? '';
    }
    
    $tracking_query = "SELECT * FROM project_tracker WHERE project_id = '$id' ORDER BY id DESC";
    $tracking_result = mysqli_query($db, $tracking_query);
    $tracking_data = [];
    while ($track_row = mysqli_fetch_array($tracking_result)) {
        $tracking_data[] = $track_row;
    }
    
    $members_query = "SELECT * FROM project_members WHERE project_id = '$id'";
    $members_result = mysqli_query($db, $members_query);
    $members_data = [];
    while ($members_row = mysqli_fetch_array($members_result)) {
        $members_data[] = $members_row;
    }

    $timeline_count_query = "SELECT COUNT(*) AS total FROM project_timeline WHERE project_id = '$id'";
    $timeline_count_result = mysqli_query($db, $timeline_count_query);
    $timeline_count_row = $timeline_count_result ? mysqli_fetch_assoc($timeline_count_result) : ['total' => 0];
    $timelineCount = (int)($timeline_count_row['total'] ?? 0);

    $member_count_query = "SELECT COUNT(*) AS total FROM project_members_consultant WHERE project_id = '$id'";
    $member_count_result = mysqli_query($db, $member_count_query);
    $member_count_row = $member_count_result ? mysqli_fetch_assoc($member_count_result) : ['total' => 0];
    $memberCount = (int)($member_count_row['total'] ?? 0);

    $ledger_table_exists = false;
    $ledger_table_check = mysqli_query($db, "SHOW TABLES LIKE 'project_ledger'");
    if ($ledger_table_check && mysqli_num_rows($ledger_table_check) > 0) {
        $ledger_table_exists = true;
    }

    $ledger_void_filter = "";
    if ($ledger_table_exists) {
        $ledger_void_column_check = mysqli_query($db, "SHOW COLUMNS FROM project_ledger LIKE 'is_void'");
        if ($ledger_void_column_check && mysqli_num_rows($ledger_void_column_check) > 0) {
            $ledger_void_filter = " AND (is_void = 0 OR is_void IS NULL)";
        }
    }

    $ledger_count_query = $ledger_table_exists
        ? "SELECT COUNT(*) AS total FROM project_ledger WHERE project_id = '$id' $ledger_void_filter"
        : "";
    $ledger_count_result = $ledger_count_query !== "" ? mysqli_query($db, $ledger_count_query) : false;
    $ledger_count_row = $ledger_count_result ? mysqli_fetch_assoc($ledger_count_result) : ['total' => 0];
    $ledgerCount = (int)($ledger_count_row['total'] ?? 0);

    /*
        Consultant Project Ledger Display
        Purpose:
        - Display financial movements in Excel-style project ledger format
        - Invoice application appears in Invoice column
        - Invoice-linked payment received offsets the Invoice line
        - Invoice-linked payment received is also displayed as child row for visibility
        - Payment without invoice appears as standalone Revenue row
        - SST / UTV / PTJ / Insurance / Expenses appear in their own credit-side columns
        - Balance C/F is calculated on display
    */

    $ledger_query = $ledger_table_exists
        ? "SELECT * FROM project_ledger WHERE project_id = '$id' $ledger_void_filter ORDER BY transaction_date ASC, id ASC"
        : "";
    $ledger_result = $ledger_query !== "" ? mysqli_query($db, $ledger_query) : false;

    $ledger_rows = [];

	    if ($ledger_result) {
	        while ($ledger = mysqli_fetch_assoc($ledger_result)) {
	            $ledger_rows[] = $ledger;
	        }
	    }

    function ledger_table_has_column($db, $table, $column) {
        static $cache = [];
        $key = $table . '.' . $column;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $safe_table = mysqli_real_escape_string($db, $table);
        $safe_column = mysqli_real_escape_string($db, $column);
        $result = mysqli_query($db, "SHOW COLUMNS FROM `$safe_table` LIKE '$safe_column'");
        $cache[$key] = $result && mysqli_num_rows($result) > 0;

        return $cache[$key];
    }

    function ledger_status_from_table($db, $table, $id_column, $id, $status_column) {
        if (empty($id) || !ledger_table_has_column($db, $table, $id_column) || !ledger_table_has_column($db, $table, $status_column)) {
            return '';
        }

        $safe_id = mysqli_real_escape_string($db, $id);
        $result = mysqli_query($db, "SELECT `$status_column` AS current_status FROM `$table` WHERE `$id_column` = '$safe_id' LIMIT 1");

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return trim((string)($row['current_status'] ?? ''));
        }

        return '';
    }

    function ledger_current_status_text($db, $ledger) {
        $source_type = strtolower(trim($ledger['source_type'] ?? ''));
        $source_id = $ledger['source_id'] ?? '';
        $invoice_id = $ledger['invoice_id'] ?? '';
        $payment_id = $ledger['payment_id'] ?? '';

        $status = '';

        if ($source_type === 'payment_listing' && !empty($source_id)) {
            $status = ledger_status_from_table($db, 'payment_listing', 'id', $source_id, 'payment_status');
        }

        if ($status === '' && (strpos($source_type, 'invoice') !== false || !empty($invoice_id))) {
            $status = ledger_status_from_table($db, 'invoices', 'id', !empty($invoice_id) ? $invoice_id : $source_id, 'invoice_status');
        }

        if ($status === '' && !empty($invoice_id)) {
            $status = ledger_status_from_table($db, 'invoices', 'id', $invoice_id, 'invoice_status');
        }

        if ($status === '' && !empty($payment_id)) {
            $status = ledger_status_from_table($db, 'payments', 'id', $payment_id, 'status');
        }

        $status_sources = [
            'procurement' => ['procurement', 'id', 'status'],
            'professional' => ['professional_fee_applications', 'id', 'status'],
            'professional_fee' => ['professional_fee_applications', 'id', 'status'],
            'allowance_wages' => ['allowance_applications', 'id', 'status'],
            'allowance' => ['allowance_applications', 'id', 'status'],
            'reconciliation' => ['reconciliation_claim_applications', 'application_id', 'status'],
            'reconciliation_claim' => ['reconciliation_claim_applications', 'application_id', 'status'],
            'advance' => ['reconciliation_claim_applications', 'application_id', 'status'],
            'project_funding' => ['project_funding_assistance_applications', 'id', 'status'],
            'project_funding_assistance' => ['project_funding_assistance_applications', 'id', 'status'],
            'funding' => ['project_funding_assistance_applications', 'id', 'status']
        ];

        if ($status === '' && !empty($source_id)) {
            foreach ($status_sources as $type_key => $source_config) {
                if (strpos($source_type, $type_key) !== false) {
                    $status = ledger_status_from_table($db, $source_config[0], $source_config[1], $source_id, $source_config[2]);
                    break;
                }
            }
        }

        if ($status === '') {
            $status = trim((string)($ledger['application_status'] ?? ''));
        }

        if ($status === '') {
            $status = trim((string)($ledger['status'] ?? ''));
        }

        if ($status === '') {
            return '-';
        }

        return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
    }
	
	    /*
	        Build payment received map by invoice_id.
	        If payment received is linked to invoice, it offsets the invoice line.
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

    $display_rows = [];
    $running_balance = 0;

    $total_invoice_column = 0;
    $total_revenue_column = 0;
    $total_loan_adjustment_column = 0;
    $total_sst_column = 0;
    $total_utv_column = 0;
    $total_ptj_column = 0;
    $total_insurance_column = 0;
    $total_advance_column = 0;
    $total_reconciliation_column = 0;
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
            - It is already offset into the invoice application row.
            - Still display as child row for visibility.
            - Does not affect Balance C/F again.
        */
        if ($is_payment_received && !empty($invoice_id)) {
            $transaction_date = !empty($ledger['transaction_date']) 
                ? date("d/m/Y", strtotime($ledger['transaction_date'])) 
                : '-';

            $payment_amount = $payment_received_raw > 0 ? $payment_received_raw : $raw_debit;

            $ref_no = !empty($invoice_no) ? $invoice_no : (!empty($details) ? $details : 'PAYMENT-' . ($ledger['source_id'] ?? $ledger['id']));
            $source_label = !empty($source_type) ? ucwords(str_replace('_', ' ', $source_type)) : 'Payment';
            $source_class = 'ledger-source-payment';

            $status_text = ledger_current_status_text($db, $ledger);

            $child_notes = trim(($notes ?? '') . "\nIncluded in invoice offset above. Does not affect Balance C/F again.");

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
                'advance_column' => 0,
                'reconciliation_column' => 0,
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

        $invoice_column = 0;
        $revenue_column = 0;
        $loan_adjustment_column = 0;
        $sst_column = 0;
        $utv_column = 0;
        $ptj_column = 0;
        $insurance_column = 0;
        $advance_column = 0;
        $reconciliation_column = 0;
        $expenses_column = 0;
        $other_debit_column = 0;
        $other_credit_column = 0;

        $paid_status_note = '';
        $row_effect = 0;

        if ($is_invoice_application) {
            $invoice_column = $invoice_amount_raw > 0 ? $invoice_amount_raw : $raw_debit;
            $revenue_column = !empty($invoice_id) && isset($invoice_payment_map[$invoice_id]) ? $invoice_payment_map[$invoice_id] : 0;

            $row_effect = $invoice_column - $revenue_column;

            if ($revenue_column >= $invoice_column && $invoice_column > 0) {
                $paid_status_note = 'PAID';
            } elseif ($revenue_column > 0 && $revenue_column < $invoice_column) {
                $paid_status_note = 'PARTIAL PAYMENT';
            } else {
                $paid_status_note = 'UNPAID';
            }
        } elseif ($is_payment_received) {
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
            $is_reconciliation_entry = strpos($category_upper, 'RECONCILIATION') !== false;
            if ($raw_debit > 0 || $loan_adjustment_raw > 0) {
                $ledger_amount = $raw_debit > 0 ? $raw_debit : $loan_adjustment_raw;
                if ($is_reconciliation_entry) {
                    $reconciliation_column = $ledger_amount;
                } else {
                    $advance_column = $ledger_amount;
                }
                $row_effect = $ledger_amount;
            } else {
                $ledger_amount = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : 0);
                if ($is_reconciliation_entry) {
                    $reconciliation_column = $ledger_amount;
                } else {
                    $advance_column = $ledger_amount;
                }
                $row_effect = 0 - $ledger_amount;
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
            strpos($category_upper, 'CLAIM') !== false ||
            strpos($category_upper, 'EXPENSE') !== false ||
            strpos($category_upper, 'FEE') !== false ||
            strpos($category_upper, 'CHARGE') !== false
        ) {
            $expenses_column = $raw_credit > 0 ? $raw_credit : ($expenses_amount_raw > 0 ? $expenses_amount_raw : $raw_debit);
            $row_effect = 0 - $expenses_column;
        } else {
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
        $total_advance_column += $advance_column;
        $total_reconciliation_column += $reconciliation_column;
        $total_expenses_column += $expenses_column;
        $total_other_debit_column += $other_debit_column;
        $total_other_credit_column += $other_credit_column;

        $status_text = ledger_current_status_text($db, $ledger);

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
            'advance_column' => $advance_column,
            'reconciliation_column' => $reconciliation_column,
            'expenses_column' => $expenses_column,
            'other_debit_column' => $other_debit_column,
            'other_credit_column' => $other_credit_column,
            'running_balance' => $running_balance,
            'status_text' => $status_text,
            'is_child_row' => false
        ];
    }

    $total_credit_side = $total_sst_column + $total_utv_column + $total_ptj_column + $total_insurance_column + $total_advance_column + $total_reconciliation_column + $total_expenses_column + $total_other_credit_column;
    $total_debit_side = $total_invoice_column + $total_revenue_column + $total_loan_adjustment_column + $total_other_debit_column;

    $balance_cf = $running_balance;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="dist/css/pages/tab-page.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/libs/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<style>
.center222 {
  text-align: center;
}

.ledger-paper {
    background: #ffffff;
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}

.ledger-header {
    border-bottom: 2px solid #0288d1;
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
    border-left: 5px solid #0288d1;
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
    overflow: hidden;
}

.ledger-table {
    font-size: 12px;
    margin-bottom: 0;
    border: none;
}

.ledger-table thead th {
    background: #0277bd;
    color: #ffffff;
    border-color: #0277bd !important;
    vertical-align: middle;
    text-align: center;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
}

.ledger-table tbody td {
    border-color: #e5e7eb !important;
    vertical-align: top;
    background: #ffffff;
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
    border-top: 2px solid #0288d1 !important;
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
    min-width: 300px;
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
    .alert,
    .nav-tabs {
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
<body class="skin-blue fixed-layout">
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
                        <h4 class="text-themecolor">Project Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Project Info</li>
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
                <!-- Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-info text-white">Project Info</h3>
                            <div class="card-body">
                                <!--<div class="card-title center222">
                                    <img src="../assets/images/1.-UTV_Logo_Full.png" alt="UTV" width="350" height="280">
                                </div>
                                <hr>-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body p-t-0"></div>
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs customtab" role="tablist">
                                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#project-details" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Details</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#file-upload" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project-Related File Upload</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#client-details" role="tab"><span class="hidden-sm-up"></i></span> <span class="hidden-xs-down">Client Details</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-members" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Members</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-timeline" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Timeline</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-tracking" role="tab"><span class="hidden-sm-up"></i></span> <span class="hidden-xs-down">Project Tracking</span></a> </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab" href="#project-ledger" role="tab">
                                                        <span class="hidden-sm-up"></span>
                                                        <span class="hidden-xs-down">Project Ledger</span>
                                                        <?php if ($ledgerCount > 0) { ?>
                                                            <span class="badge badge-info ml-1"><?php echo $ledgerCount; ?></span>
                                                        <?php } ?>
                                                    </a>
                                                </li>
                                            </ul>
                                            <!-- Tab panes -->
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="project-details" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Project Title</td>
                                                                                <td><?php echo !empty($project_title) ? htmlspecialchars($project_title) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project No</td>
                                                                                <td><?php echo !empty($project_no) ? htmlspecialchars($project_no) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Type of Project</td>
                                                                                <td><?php echo !empty($project_type) ? htmlspecialchars($project_type) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Start</td>
                                                                                <td><?php echo !empty($project_start) ? date("d F Y", strtotime($project_start)) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project End</td>
                                                                                <td><?php echo !empty($project_end) ? date("d F Y", strtotime($project_end)) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Registered Project Value (RM)</td>
                                                                                <td><?php echo !empty($registered_project_value) ? htmlspecialchars($registered_project_value) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Quotation Ref No.</td>
                                                                                <td><?php echo !empty($quotation_ref_no) ? htmlspecialchars($quotation_ref_no) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Status</td>
                                                                                <td><?php echo !empty($project_status) ? htmlspecialchars($project_status) : "No status available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Return Remark</td>
                                                                                <td>
                                                                                    <?php
                                                                                        $isReturned = stripos((string)$project_status, 'Returned') !== false;
                                                                                        $isReturnedToConsultant = strcasecmp(trim((string)$return_to), 'Consultant') === 0;
                                                                                        echo ($isReturned && $isReturnedToConsultant && trim((string)$return_remark) !== '')
                                                                                            ? htmlspecialchars($return_remark)
                                                                                            : 'No return remark';
                                                                                    ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Members</td>
                                                                                <td>
                                                                                    <?php echo $memberCount > 0 ? $memberCount . ' member(s) added' : 'No members added yet'; ?>
                                                                                    <div class="m-t-10">
                                                                                        <small class="text-muted">Manage members in Edit/Update Project.</small>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Timeline</td>
                                                                                <td>
                                                                                    <?php echo $timelineCount > 0 ? $timelineCount . ' phase(s) added' : 'No timeline added yet'; ?>
                                                                                    <div class="m-t-10">
                                                                                        <small class="text-muted">Manage project timeline in Edit/Update Project.</small>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane p-20" id="project-members" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <?php
                                                                // Query to fetch project members
                                                                $query = "
                                                                    SELECT pmc.*, us.*
                                                                    FROM project_members_consultant pmc
                                                                    INNER JOIN uitm_staff us ON pmc.member_id = us.id
                                                                    WHERE pmc.project_id = '$id'
                                                                    ORDER BY pmc.project_no ASC
                                                                ";
                                                                
                                                                $result = mysqli_query($db, $query);
                                                                
                                                                if (!$result) {
                                                                    error_log("MySQL Query Error: " . mysqli_error($db));
                                                                    echo "<p>Error fetching project members: " . mysqli_error($db) . "</p>";
                                                                    exit;
                                                                }
                                                                
                                                                if (mysqli_num_rows($result) > 0) {
                                                                ?>
                                                                <div class="table-responsive">
                                                                    <table id="members" class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Name</th>
                                                                                <th>IC Number</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                                                <tr>
                                                                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                                                    <td><?php echo htmlspecialchars($row['ic']); ?></td>
                                                                                </tr>
                                                                            <?php } ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <?php } else { ?>
                                                                <div class="alert alert-light text-center mb-0">No project members added yet.</div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane p-20" id="project-timeline" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <?php
                                                                // Query to fetch project timeline
                                                                $query = "
                                                                    SELECT *
                                                                    FROM project_timeline
                                                                    WHERE project_id = '$id'
                                                                    ORDER BY id DESC
                                                                ";
                                                                
                                                                $result = mysqli_query($db, $query);
                                                                
                                                                if (!$result) {
                                                                    error_log("MySQL Query Error: " . mysqli_error($db));
                                                                    echo "<p>Error fetching project timeline: " . mysqli_error($db) . "</p>";
                                                                    exit;
                                                                }
                                                                
                                                                if (mysqli_num_rows($result) > 0) {
                                                                ?>
                                                                <div class="table-responsive">
                                                                    <table id="timeline" class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Title</th>
                                                                                <th>Description</th>
                                                                                <th>Value</th>
                                                                                <th>Start Date</th>
                                                                                <th>End Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                                                <tr>
                                                                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                                                    <td>RM <?php echo htmlspecialchars($row['value']); ?></td>
                                                                                    <td><?php echo date("d F Y", strtotime($row['date_start'])); ?></td>
                                                                                    <td><?php echo date("d F Y", strtotime($row['date_end'])); ?></td>
                                                                                </tr>
                                                                            <?php } ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <?php } else { ?>
                                                                <div class="alert alert-light text-center mb-0">No project timeline added yet.</div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane p-20" id="file-upload" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Appointment/Offer Letter</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($appointment_letter) ? "<a href=\"project-documents/consultancy-project/appointment-letter/" . htmlspecialchars($appointment_letter) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PTJ Approval</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($approval_external_work) ? "<a href=\"project-documents/consultancy-project/approval-external-work-letter/" . htmlspecialchars($approval_external_work) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Quotation Document</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($quotation_doc) ? "<a href=\"project-documents/consultancy-project/quotation/" . htmlspecialchars($quotation_doc) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Agreement/MoA</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($agreement_doc) ? "<a href=\"project-documents/consultancy-project/agreement-MoA/" . htmlspecialchars($agreement_doc) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Proposal & Budget</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($project_proposal) ? "<a href=\"project-documents/consultancy-project/project-proposal/" . htmlspecialchars($project_proposal) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 1</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($other_doc_1) ? "<a href=\"project-documents/consultancy-project/other-docs/" . htmlspecialchars($other_doc_1) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 2</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($other_doc_2) ? "<a href=\"project-documents/consultancy-project/other-docs/" . htmlspecialchars($other_doc_2) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane p-20" id="client-details" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Client's Company Name</td>
                                                                                <td><?php echo !empty($client_company_name) ? htmlspecialchars($client_company_name) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Full Address</td>
                                                                                <td><?php echo !empty($client_address) ? htmlspecialchars($client_address) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Contact Number</td>
                                                                                <td><?php echo !empty($client_contact) ? htmlspecialchars($client_contact) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Business Type</td>
                                                                                <td><?php echo !empty($client_business_type) ? htmlspecialchars($client_business_type) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC</td>
                                                                                <td><?php echo !empty($client_pic) ? htmlspecialchars($client_pic) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Email Address</td>
                                                                                <td><?php echo !empty($client_pic_email) ? htmlspecialchars($client_pic_email) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Contact Number</td>
                                                                                <td><?php echo !empty($client_pic_contact) ? htmlspecialchars($client_pic_contact) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane p-20" id="project-tracking" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h4 class="card-header">File Number : <?php echo htmlspecialchars($project_no); ?></h4>
                                                                <div class="table-responsive">
                                                                    <table class="table color-bordered-table info-bordered-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Remarks</th>
                                                                                <th>Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php if (!empty($tracking_data)) : ?>
                                                                                <?php foreach ($tracking_data as $track) : ?>
                                                                                    <tr>
                                                                                        <td><?php echo htmlspecialchars($track['remark']); ?></td>
                                                                                        <td><?php echo date("d F Y", strtotime($track['date'])); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            <?php else : ?>
                                                                                <tr>
                                                                                    <td colspan="2" class="text-center">No tracking data available yet</td>
                                                                                </tr>
                                                                            <?php endif; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane p-20" id="project-ledger" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <?php if ($project_status == "Draft" || empty($project_status) || $ledgerCount <= 0) { ?>
                                                                    <div class="alert alert-light border text-center m-b-0">
                                                                        <strong>Project ledger is not available yet.</strong><br>
                                                                        It will only appear after the project has been submitted and ledger data has been recorded.
                                                                    </div>
                                                                <?php } else { ?>
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
                                                                                        <button type="button" class="btn btn-info" onclick="window.print();">
                                                                                            Print Ledger
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

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
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-meta-box">
                                                                                        <div class="ledger-meta-label">Project Status</div>
                                                                                        <div class="ledger-meta-value"><?php echo htmlspecialchars($project_status); ?></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-meta-box">
                                                                                        <div class="ledger-meta-label">Registered Project Value</div>
                                                                                        <div class="ledger-meta-value">RM <?php echo number_format((float)$registered_project_value, 2); ?></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-meta-box">
                                                                                        <div class="ledger-meta-label">Adjusted Project Value</div>
                                                                                        <div class="ledger-meta-value">RM <?php echo number_format((float)$adjusted_project_value, 2); ?></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-meta-box">
                                                                                        <div class="ledger-meta-label">Balance C/F</div>
                                                                                        <div class="ledger-meta-value <?php echo ($balance_cf >= 0) ? 'ledger-balance-positive' : 'ledger-balance-negative'; ?>">
                                                                                            RM <?php echo number_format((float)$balance_cf, 2); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row m-b-20">
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-summary-card invoice">
                                                                                        <div class="ledger-summary-label">Total Invoice</div>
                                                                                        <div class="ledger-summary-value ledger-invoice">
                                                                                            RM <?php echo number_format((float)$total_invoice_column, 2); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-summary-card revenue">
                                                                                        <div class="ledger-summary-label">Total Revenue Received</div>
                                                                                        <div class="ledger-summary-value ledger-revenue">
                                                                                            RM <?php echo number_format((float)$total_revenue_column, 2); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-summary-card credit">
                                                                                        <div class="ledger-summary-label">Total Credit / Deduction</div>
                                                                                        <div class="ledger-summary-value ledger-credit">
                                                                                            RM <?php echo number_format((float)$total_credit_side, 2); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <div class="ledger-summary-card balance">
                                                                                        <div class="ledger-summary-label">Balance C/F</div>
                                                                                        <div class="ledger-summary-value <?php echo ($balance_cf >= 0) ? 'ledger-balance-positive' : 'ledger-balance-negative'; ?>">
                                                                                            RM <?php echo number_format((float)$balance_cf, 2); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="alert alert-info">
                                                                                Invoice-linked payment received is offset against the invoice line. Payment received is also shown as a child row for visibility, but it does not affect Balance C/F twice.
                                                                            </div>

                                                                            <div class="ledger-section-title">Project Ledger Account</div>

                                                                            <div class="table-responsive ledger-table-wrapper">
                                                                                <table id="ledgerTable" class="table table-bordered ledger-table">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>No</th>
                                                                                            <th>Date</th>
                                                                                            <th>Ref / Doc No</th>
                                                                                            <th>Particulars</th>
                                                                                            <th>Source</th>
                                                                                            <th>Invoice</th>
                                                                                            <th>Revenue</th>
                                                                                            <th>Loan / Adj.</th>
                                                                                            <th>SST</th>
                                                                                            <th>UTV Fee</th>
                                                                                            <th>PTJ Fee</th>
                                                                                            <th>Insurance</th>
                                                                                            <th>Advance</th>
                                                                                            <th>Reconciliation</th>
                                                                                            <th>Expenses / Fees</th>
                                                                                            <th>Other DR</th>
                                                                                            <th>Other CR</th>
                                                                                            <th>Balance C/F</th>
                                                                                            <th>Current Status</th>
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
                                                                                            <td class="ledger-amount ledger-debit">
                                                                                                <?php echo ($row['loan_adjustment_column'] > 0) ? number_format($row['loan_adjustment_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo ($row['sst_column'] > 0) ? number_format($row['sst_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo ($row['utv_column'] > 0) ? number_format($row['utv_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo ($row['ptj_column'] > 0) ? number_format($row['ptj_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo ($row['insurance_column'] > 0) ? number_format($row['insurance_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount">
                                                                                                <?php echo ($row['advance_column'] > 0) ? number_format($row['advance_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount">
                                                                                                <?php echo ($row['reconciliation_column'] > 0) ? number_format($row['reconciliation_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo ($row['expenses_column'] > 0) ? number_format($row['expenses_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-debit">
                                                                                                <?php echo ($row['other_debit_column'] > 0) ? number_format($row['other_debit_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo ($row['other_credit_column'] > 0) ? number_format($row['other_credit_column'], 2) : '-'; ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount <?php echo $balance_class; ?>">
                                                                                                <?php echo number_format($row['running_balance'], 2); ?>
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
                                                                                            <td colspan="19" class="ledger-empty">
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
                                                                                            <td class="ledger-amount ledger-debit">
                                                                                                <?php echo number_format((float)$total_loan_adjustment_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo number_format((float)$total_sst_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo number_format((float)$total_utv_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo number_format((float)$total_ptj_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo number_format((float)$total_insurance_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount">
                                                                                                <?php echo number_format((float)$total_advance_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount">
                                                                                                <?php echo number_format((float)$total_reconciliation_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo number_format((float)$total_expenses_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-debit">
                                                                                                <?php echo number_format((float)$total_other_debit_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount ledger-credit">
                                                                                                <?php echo number_format((float)$total_other_credit_column, 2); ?>
                                                                                            </td>
                                                                                            <td class="ledger-amount <?php echo ($balance_cf >= 0) ? 'ledger-balance-positive' : 'ledger-balance-negative'; ?>">
                                                                                                <?php echo number_format((float)$balance_cf, 2); ?>
                                                                                            </td>
                                                                                            <td></td>
                                                                                        </tr>
                                                                                    </tfoot>
                                                                                </table>
                                                                            </div>

                                                                            <div class="ledger-print-note">
                                                                                Generated on <?php echo date("d/m/Y h:i A"); ?>. Balance C/F is auto-calculated based on invoice, revenue, loan/adjustment, credit deductions and expenses.
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Tab panes -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Submit-->
                
                <div class="row">
                    <div class="m-b-30 col-md-12">
                        <?php
                            $user_id = $userData['id'];
                            $isLeader = $user_id == $leader_id; // Semak user ni leader ke tak
                            $isLocked = in_array($project_status, ['Pending Verification', 'Pending Approval', 'Approved', 'Appointed']) || !$isLeader;
                        ?>
                        <a href="consultancy-project-edit.php?id=<?php echo urlencode($id); ?>" 
                           class="btn btn-lg btn-info <?php echo $isLocked ? 'disabled' : ''; ?>" 
                           title="Edit/Update Project"
                           <?php echo $isLocked ? 'onclick="return false;" style="pointer-events: none; opacity: 0.5;"' : ''; ?>>
                           Edit/Update Project
                        </a>
                        <button id="submitProject" 
                                class="btn btn-lg btn-success" 
                                <?php echo $isLocked ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Submit Project
                        </button>
                    </div>
                </div>
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
    <script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!-- Sweet-Alert  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        $(function () {
            $('#members').DataTable();
            $('#timeline').DataTable();

            if ($('#ledgerTable').length) {
                $('#ledgerTable').DataTable({
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    ordering: false,
                    scrollX: true,
                    pageLength: 25
                });

                $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary mr-1');
            }
        });
    </script>
    <script>
        $(document).on('click', '#submitProject', function () {
            const submitButton = $('#submitProject');
            const editButton = $('a[title="Edit/Update Project"]');

            Swal.fire({
                title: 'Are you sure?',
                text: "Once submitted, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("User confirmed submission");

                    // Disable button to prevent double submit
                    submitButton.prop('disabled', true).html('Submitting...');
                    editButton.addClass('disabled').css({
                        'pointer-events': 'none',
                        'opacity': '0.5'
                    });

                    // Show loading while AJAX is processing
                    Swal.fire({
                        title: 'Submitting Project...',
                        text: 'Please wait while your project is being submitted.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'validate-project.php', // URL ke fail PHP backend
                        method: 'POST',
                        data: { project_id: <?php echo json_encode($id); ?> }, // Gantikan dengan ID projek sebenar
                        dataType: 'json', // Tetapkan respons sebagai JSON terus
                        success: function (response) {
                            console.log("Server response (parsed):", response);
    
                            if (response.success) {
                                Swal.fire({
                                    title: 'Submitted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    location.reload(); // Muat semula halaman selepas berjaya
                                });
                            } else {
                                Swal.fire({
                                    title: 'Failed!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Jika gagal, kekal di halaman tanpa refresh
                                    submitButton.prop('disabled', false).html('Submit Project');
                                    editButton.removeClass('disabled').css({
                                        'pointer-events': '',
                                        'opacity': ''
                                    });
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", error);
                            console.error("Server response:", xhr.responseText);

                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred during submission.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                submitButton.prop('disabled', false).html('Submit Project');
                                editButton.removeClass('disabled').css({
                                    'pointer-events': '',
                                    'opacity': ''
                                });
                            });
                        }
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Project submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>
