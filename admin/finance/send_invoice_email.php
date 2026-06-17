<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../db_connect/db_connect.php';
include 'auth_check.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
    IMPORTANT:
    Adjust PHPMailer path ikut folder sebenar dalam hosting.
    Kalau kau guna Composer, boleh guna:
    require '../../vendor/autoload.php';

    Kalau kau letak PHPMailer manual, guna path bawah.
*/
require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$invoice_id_raw = $_POST['invoice_id'] ?? '';
$project_id_raw = $_POST['project_id'] ?? '';
$staff_id_raw = $_POST['staff_id'] ?? '';

if (trim($invoice_id_raw) === '' || trim($project_id_raw) === '' || trim($staff_id_raw) === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Missing invoice ID, project ID, or staff ID.'
    ]);
    exit;
}

$invoice_id = mysqli_real_escape_string($db, $invoice_id_raw);
$project_id = mysqli_real_escape_string($db, $project_id_raw);
$staff_id = mysqli_real_escape_string($db, $staff_id_raw);
$date_now = date('Y-m-d H:i:s');

// Mulakan transaksi
mysqli_begin_transaction($db);

try {
    // Query data projek utama
    $project_query = "
        SELECT *
        FROM project 
        WHERE id = '$project_id'
        LIMIT 1
    ";
    $project_result = mysqli_query($db, $project_query);

    if (!$project_result || mysqli_num_rows($project_result) === 0) {
        throw new Exception('Project not found.');
    }

    $project = mysqli_fetch_assoc($project_result);

    $leader_id = $project['leader_id'];
    $project_no = $project['project_no'];
    $project_title = $project['project_title'];
    $project_leader = $project['project_leader'];
    $client_company_name = $project['client_company_name'];
    $client_pic = $project['client_pic'];
    $client_pic_email = $project['client_pic_email'];

    // Query project leader email
    $leader_email = '';
    $leader_query = "SELECT email FROM uitm_staff WHERE id = '$leader_id' LIMIT 1";
    $leader_result = mysqli_query($db, $leader_query);
    if ($leader_result && mysqli_num_rows($leader_result) > 0) {
        $leader_row = mysqli_fetch_assoc($leader_result);
        $leader_email = $leader_row['email'];
    }

    // Query data invoices
    $invoice_query = "
        SELECT *
        FROM invoices
        WHERE id = '$invoice_id'
        AND project_id = '$project_id'
        LIMIT 1
    ";
    $invoice_result = mysqli_query($db, $invoice_query);

    if (!$invoice_result || mysqli_num_rows($invoice_result) === 0) {
        throw new Exception('Invoice not found.');
    }

    $invoice = mysqli_fetch_assoc($invoice_result);

    $invoice_no = $invoice['invoice_no'];
    $invoice_file = $invoice['invoice_file'];
    $invoice_purpose = $invoice['invoice_purpose'];
    $additional_info = $invoice['additional_info'];
    $amount_type = $invoice['amount_type'];
    $total_amount = $invoice['total_amount'];
    $sst_amount = $invoice['sst_amount'];
    $total_invoice = $invoice['total_invoice'];
    $invoice_status = $invoice['invoice_status'];
    $invoice_date = $invoice['invoice_date'] ?? '';
    $due_date = $invoice['due_date'] ?? '';
    $payment_status = $invoice['payment_status'] ?? 'Unpaid';

    if (empty($invoice_no)) {
        throw new Exception('Invoice number is missing. Please update invoice details first.');
    }

    if (empty($invoice_file)) {
        throw new Exception('Invoice file is missing. Please upload invoice file first.');
    }

    if (empty($client_pic_email)) {
        throw new Exception('Client email is missing.');
    }

    if (empty($leader_email)) {
        throw new Exception('Project leader email is missing.');
    }

    if (!filter_var($client_pic_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Client email is invalid.');
    }

    if (!filter_var($leader_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Project leader email is invalid.');
    }

    /*
        IMPORTANT:
        Finance invoice file path.
        This must match update_invoice_details.php upload path.
    */
    $invoice_file_path = "../../finance-invoice-documents/" . $invoice_file;

    if (!file_exists($invoice_file_path)) {
        throw new Exception('Invoice file not found on server.');
    }

    // Query data milestones untuk invois
    $query_invoice_milestones = "
        SELECT im.*, pt.title, pt.description, pt.value, pt.date_start, pt.date_end
        FROM invoice_milestones im
        LEFT JOIN project_timeline pt ON im.milestone_id = pt.id
        WHERE im.invoice_id = '$invoice_id'
    ";
    $result_invoice_milestones = mysqli_query($db, $query_invoice_milestones);

    $milestone_rows = '';
    if ($result_invoice_milestones && mysqli_num_rows($result_invoice_milestones) > 0) {
        while ($milestone = mysqli_fetch_assoc($result_invoice_milestones)) {
            $title = !empty($milestone['title']) ? htmlspecialchars($milestone['title']) : 'Milestone not found';
            $description = !empty($milestone['description']) ? htmlspecialchars($milestone['description']) : 'Not Available';
            $value = !empty($milestone['value']) ? number_format((float)$milestone['value'], 2) : '0.00';
            $date_start = !empty($milestone['date_start']) ? date("j F Y", strtotime($milestone['date_start'])) : 'Not Available';
            $date_end = !empty($milestone['date_end']) ? date("j F Y", strtotime($milestone['date_end'])) : 'Not Available';

            $milestone_rows .= "
                <tr>
                    <td style='border:1px solid #ddd;padding:8px;'>{$title}</td>
                    <td style='border:1px solid #ddd;padding:8px;'>{$description}</td>
                    <td style='border:1px solid #ddd;padding:8px;'>RM {$value}</td>
                    <td style='border:1px solid #ddd;padding:8px;'>{$date_start}</td>
                    <td style='border:1px solid #ddd;padding:8px;'>{$date_end}</td>
                </tr>
            ";
        }
    }

    $milestone_section = '';
    if (!empty($milestone_rows)) {
        $milestone_section = "
            <h3 style='margin-top:25px;color:#222;'>Invoice Milestones</h3>
            <table style='width:100%;border-collapse:collapse;margin-top:10px;'>
                <thead>
                    <tr style='background:#f2f2f2;'>
                        <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Title</th>
                        <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Description</th>
                        <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Value</th>
                        <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Date Start</th>
                        <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Date End</th>
                    </tr>
                </thead>
                <tbody>
                    {$milestone_rows}
                </tbody>
            </table>
        ";
    }

    $client_name_display = !empty($client_pic) ? htmlspecialchars($client_pic) : 'Client';
    $client_company_display = !empty($client_company_name) ? htmlspecialchars($client_company_name) : 'Not Available';
    $project_no_display = !empty($project_no) ? htmlspecialchars($project_no) : 'Not Available';
    $project_title_display = !empty($project_title) ? htmlspecialchars($project_title) : 'Not Available';
    $project_leader_display = !empty($project_leader) ? htmlspecialchars($project_leader) : 'Not Available';

    $invoice_no_display = htmlspecialchars($invoice_no);
    $invoice_purpose_display = !empty($invoice_purpose) ? htmlspecialchars($invoice_purpose) : 'Not Available';
    $additional_info_display = !empty($additional_info) ? htmlspecialchars($additional_info) : 'Not Available';
    $amount_type_display = !empty($amount_type) ? htmlspecialchars($amount_type) : 'Not Available';
    $invoice_status_display = !empty($invoice_status) ? htmlspecialchars($invoice_status) : 'Not Available';
    $payment_status_display = !empty($payment_status) ? htmlspecialchars($payment_status) : 'Unpaid';

    $invoice_date_display = !empty($invoice_date) ? date("j F Y", strtotime($invoice_date)) : 'Not Available';
    $due_date_display = !empty($due_date) ? date("j F Y", strtotime($due_date)) : 'Not Available';

    $total_amount_display = number_format((float)$total_amount, 2);
    $sst_amount_display = number_format((float)$sst_amount, 2);
    $total_invoice_display = number_format((float)$total_invoice, 2);

    $email_subject = "Invoice {$invoice_no_display} - {$project_no_display}";

    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Invoice Email</title>
    </head>
    <body style='font-family:Arial, sans-serif;background:#f6f7fb;margin:0;padding:20px;color:#333;'>
        <div style='max-width:780px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e5e5;'>
            <div style='background:#198754;color:#ffffff;padding:20px;'>
                <h2 style='margin:0;'>Invoice Submission</h2>
            </div>

            <div style='padding:24px;'>
                <p>Dear {$client_name_display},</p>

                <p>
                    Please find attached the invoice for the project below.
                    Kindly review the details and proceed with payment based on the invoice information.
                </p>

                <h3 style='margin-top:25px;color:#222;'>Project Details</h3>
                <table style='width:100%;border-collapse:collapse;margin-top:10px;'>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;width:35%;'><strong>Project No</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$project_no_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Project Title</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$project_title_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Project Leader</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$project_leader_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Client Company</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$client_company_display}</td>
                    </tr>
                </table>

                <h3 style='margin-top:25px;color:#222;'>Invoice Details</h3>
                <table style='width:100%;border-collapse:collapse;margin-top:10px;'>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;width:35%;'><strong>Invoice No</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$invoice_no_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Invoice Date</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$invoice_date_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Due Date</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$due_date_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Invoice Purpose</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$invoice_purpose_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Additional Info</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$additional_info_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Amount Type</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$amount_type_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Payment Status</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>{$payment_status_display}</td>
                    </tr>
                </table>

                {$milestone_section}

                <h3 style='margin-top:25px;color:#222;'>Invoice Amount</h3>
                <table style='width:100%;border-collapse:collapse;margin-top:10px;'>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;width:35%;'><strong>Project Amount</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>RM {$total_amount_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>SST Amount</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'>RM {$sst_amount_display}</td>
                    </tr>
                    <tr>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>Total Invoice</strong></td>
                        <td style='border:1px solid #ddd;padding:8px;'><strong>RM {$total_invoice_display}</strong></td>
                    </tr>
                </table>

                <p style='margin-top:25px;'>
                    The invoice document is attached to this email for your reference.
                </p>

                <p>
                    Thank you.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";

    /*
        Send email first.
        DB update only happens after email is successfully sent.
    */
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'mail.domei.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'developer@domei.io';

    /*
        IMPORTANT:
        Paste SMTP password here from your server config.
        Avoid committing this password to public repository.
    */
    $mail->Password   = 'domei@1234';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('developer@domei.io', 'IProms Finance');
    $mail->addAddress($client_pic_email, $client_name_display);
    $mail->addCC($leader_email, $project_leader_display);

    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body    = $email_body;
    $mail->AltBody = "Invoice {$invoice_no_display} for project {$project_no_display}. Total invoice: RM {$total_invoice_display}.";
    $mail->addAttachment($invoice_file_path, $invoice_file);

    $mail->send();

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

    $invoice_update_parts = [];

    if (in_array('sent_to_client_at', $invoice_columns)) {
        $invoice_update_parts[] = "sent_to_client_at = '$date_now'";
    }

    if (in_array('sent_to_client_by', $invoice_columns)) {
        $invoice_update_parts[] = "sent_to_client_by = '$staff_id'";
    }

    /*
        Status kekal Waiting Payment.
        Sebab email sent bukan payment event.
    */
    $invoice_update_parts[] = "invoice_status = 'Waiting Payment'";

    if (!empty($invoice_update_parts)) {
        $update_invoice_query = "
            UPDATE invoices
            SET " . implode(', ', $invoice_update_parts) . "
            WHERE id = '$invoice_id'
            AND project_id = '$project_id'
        ";

        if (!mysqli_query($db, $update_invoice_query)) {
            throw new Exception('Email sent, but failed to update invoice email status: ' . mysqli_error($db));
        }
    }

    /*
        Update existing project ledger row.
        Jangan insert ledger baru untuk email sent.
        Ini masih same invoice application transaction.
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

        $ledger_update_parts = [];

        $possible_ledger_update = [
            'fin_action' => "'Invoice Sent to Client'",
            'notes' => "'Invoice sent to client and project leader. Payment has not been received yet.'",
            'updated_by' => "'$staff_id'",
            'updated_at' => "'$date_now'"
        ];

        foreach ($possible_ledger_update as $column => $value) {
            if (in_array($column, $ledger_columns)) {
                $ledger_update_parts[] = "$column = $value";
            }
        }

        if (!empty($ledger_update_parts)) {
            $ledger_update_query = "
                UPDATE project_ledger
                SET " . implode(', ', $ledger_update_parts) . "
                WHERE source_type = 'invoice'
                AND source_id = '$invoice_id'
                AND transaction_category = 'INVOICE APPLICATION'
                AND is_void = 0
            ";

            if (!mysqli_query($db, $ledger_update_query)) {
                throw new Exception('Email sent, but failed to update project ledger: ' . mysqli_error($db));
            }
        }
    }

    // Insert tracker
    $safe_invoice_no = mysqli_real_escape_string($db, $invoice_no);
    $remark = "Invoice email has been sent to client and project leader by Finance. Invoice No: $safe_invoice_no ($staff_id)";
    $tracker_query = "
        INSERT INTO project_tracker (project_id, project_no, remark, date)
        VALUES ('$project_id', '$project_no', '$remark', '$date_now')
    ";

    if (!mysqli_query($db, $tracker_query)) {
        throw new Exception('Email sent, but failed to insert tracker record: ' . mysqli_error($db));
    }

    mysqli_commit($db);

    echo json_encode([
        'success' => true,
        'message' => 'Invoice email has been successfully sent to the client and project leader.'
    ]);
    exit;

} catch (Exception $e) {
    mysqli_rollback($db);

    echo json_encode([
        'success' => false,
        'message' => 'Failed to send invoice email. ' . $e->getMessage()
    ]);
    exit;
}
?>