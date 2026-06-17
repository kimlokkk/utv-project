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

$invoice_id = $_POST['invoice_id'] ?? '';
$project_id = $_POST['project_id'] ?? '';

if (trim($invoice_id) === '' || trim($project_id) === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Missing invoice ID or project ID.'
    ]);
    exit;
}

$invoice_id = mysqli_real_escape_string($db, $invoice_id);
$project_id = mysqli_real_escape_string($db, $project_id);

// Query data projek utama
$project_query = "SELECT * FROM project WHERE id = '$project_id' LIMIT 1";
$project_result = mysqli_query($db, $project_query);

if (!$project_result || mysqli_num_rows($project_result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Project not found.'
    ]);
    exit;
}

$project = mysqli_fetch_assoc($project_result);

$project_no = $project['project_no'];
$project_title = $project['project_title'];
$project_leader = $project['project_leader'];
$client_company_name = $project['client_company_name'];
$client_pic = $project['client_pic'];
$client_pic_email = $project['client_pic_email'];

// Query data invoices
$invoice_query = "SELECT * FROM invoices WHERE id = '$invoice_id' AND project_id = '$project_id' LIMIT 1";
$invoice_result = mysqli_query($db, $invoice_query);

if (!$invoice_result || mysqli_num_rows($invoice_result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invoice not found.'
    ]);
    exit;
}

$invoice = mysqli_fetch_assoc($invoice_result);

$invoice_purpose = $invoice['invoice_purpose'];
$additional_info = $invoice['additional_info'];
$tin_number = $invoice['tin_number'];
$follow_milestone = $invoice['follow_milestone'];
$amount_type = $invoice['amount_type'];
$total_amount = $invoice['total_amount'];
$sst_amount = $invoice['sst_amount'];
$total_invoice = $invoice['total_invoice'];
$invoice_status = $invoice['invoice_status'];
$invoice_no = $invoice['invoice_no'];
$invoice_file = $invoice['invoice_file'];

if (empty($client_pic_email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Client email address is not available.'
    ]);
    exit;
}

if (!filter_var($client_pic_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Client email address is invalid.'
    ]);
    exit;
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
} else {
    $milestone_rows = "
        <tr>
            <td colspan='5' style='border:1px solid #ddd;padding:8px;text-align:center;color:#777;'>
                No milestone data available.
            </td>
        </tr>
    ";
}

$milestone_section = '';
$follow_milestone_normalized = strtolower(trim($follow_milestone ?? ''));

if (in_array($follow_milestone_normalized, ['yes', 'y', '1', 'true'])) {
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

$invoice_no_display = !empty($invoice_no) ? htmlspecialchars($invoice_no) : 'Not Available';
$client_name_display = !empty($client_pic) ? htmlspecialchars($client_pic) : 'Client';
$client_company_display = !empty($client_company_name) ? htmlspecialchars($client_company_name) : 'Not Available';
$project_no_display = !empty($project_no) ? htmlspecialchars($project_no) : 'Not Available';
$project_title_display = !empty($project_title) ? htmlspecialchars($project_title) : 'Not Available';
$invoice_purpose_display = !empty($invoice_purpose) ? htmlspecialchars($invoice_purpose) : 'Not Available';
$additional_info_display = !empty($additional_info) ? htmlspecialchars($additional_info) : 'Not Available';
$amount_type_display = !empty($amount_type) ? htmlspecialchars($amount_type) : 'Not Available';
$invoice_status_display = !empty($invoice_status) ? htmlspecialchars($invoice_status) : 'Not Available';

$total_amount_display = number_format((float)$total_amount, 2);
$sst_amount_display = number_format((float)$sst_amount, 2);
$total_invoice_display = number_format((float)$total_invoice, 2);

$email_subject = "Invoice Confirmation - {$project_no_display}";

$email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Invoice Confirmation</title>
</head>
<body style='font-family:Arial, sans-serif;background:#f6f7fb;margin:0;padding:20px;color:#333;'>
    <div style='max-width:760px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e5e5;'>
        <div style='background:#198754;color:#ffffff;padding:20px;'>
            <h2 style='margin:0;'>Invoice Confirmation</h2>
        </div>

        <div style='padding:24px;'>
            <p>Dear {$client_name_display},</p>

            <p>
                We would like to confirm the invoice application for the following project.
                Please find the invoice details below for your reference.
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
                    <td style='border:1px solid #ddd;padding:8px;'><strong>Invoice Status</strong></td>
                    <td style='border:1px solid #ddd;padding:8px;'>{$invoice_status_display}</td>
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
                Kindly review the details and contact us if further clarification is required.
            </p>

            <p>
                Thank you.
            </p>
        </div>
    </div>
</body>
</html>
";

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'mail.domei.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'developer@domei.io';

    /*
        IMPORTANT:
        Jangan biarkan password hardcoded kalau boleh.
        Untuk sekarang, paste SMTP password yang kau bagi tadi dekat bawah ni.
    */
    $mail->Password   = 'domei@1234';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('developer@domei.io', 'IProms');
    $mail->addAddress($client_pic_email, $client_name_display);

    // Optional CC project leader kalau nak
    // $mail->addCC($leader_email);

    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body    = $email_body;
    $mail->AltBody = "Invoice confirmation for project {$project_no_display}. Total invoice: RM {$total_invoice_display}.";

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Confirmation email has been successfully sent to the client.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo
    ]);
    exit;
}
?>