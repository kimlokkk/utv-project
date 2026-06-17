<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include '../db_connect/db_connect.php';
include '../function/function.php';
include 'auth_check.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

$userData = $_SESSION['user_data'] ?? [];
$current_user_id = isset($userData['id']) ? (string)$userData['id'] : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Semakan dan kumpul error
$errors = [];

$project_id_raw       = $_POST['id'] ?? '';
$invoice_purpose_raw  = $_POST['invoice_purpose'] ?? '';
$total_amount_raw     = $_POST['total_amount'] ?? '';
$sst_amount_raw       = $_POST['sst_amount'] ?? '';
$total_invoice_raw    = $_POST['total_invoice'] ?? '';
$follow_raw           = $_POST['follow_milestone'] ?? '';
$amount_type_raw      = $_POST['amount_type'] ?? '';
$member_id_raw        = $_POST['member_id'] ?? '';

/*
    IMPORTANT:
    Form value guna "Yes" / "No".
    Old backend check "yes", so milestone tak disimpan.
    Normalize value supaya Yes/yes/Y/1/true semua dianggap follow milestone.
*/
$follow_normalized = strtolower(trim($follow_raw));
$is_follow_milestone = in_array($follow_normalized, ['yes', 'y', '1', 'true']);
$follow_milestone_db = $is_follow_milestone ? 'Yes' : 'No';

if (trim($project_id_raw) === '') $errors[] = 'Project ID';
if (trim($invoice_purpose_raw) === '') $errors[] = 'Invoice Purpose';
if (trim($total_amount_raw) === '') $errors[] = 'Total Amount';
if (trim($sst_amount_raw) === '') $errors[] = 'SST Amount';
if (trim($total_invoice_raw) === '') $errors[] = 'Total Invoice';
if (trim($follow_raw) === '') $errors[] = 'Follow Milestone';
if (trim($amount_type_raw) === '') $errors[] = 'Amount Type';
if (trim($member_id_raw) === '') $errors[] = 'Member ID';

function has_invoice_attachments() {
    if (!isset($_FILES['invoice_attachment'])) {
        return false;
    }

    $errors = is_array($_FILES['invoice_attachment']['error'])
        ? $_FILES['invoice_attachment']['error']
        : [$_FILES['invoice_attachment']['error']];

    foreach ($errors as $error) {
        if ($error === UPLOAD_ERR_OK) {
            return true;
        }
    }

    return false;
}

if (!has_invoice_attachments()) {
    $errors[] = 'Invoice Attachment';
}

// Kalau follow milestone, pastikan ada at least satu milestone
if ($is_follow_milestone && (empty($_POST['milestones']) || !is_array($_POST['milestones']))) {
    $errors[] = 'Milestone Selection';
}

// Kalau tak follow milestone, manual amount should already calculate total amount.
// No need to require milestones.
if (!$is_follow_milestone) {
    $_POST['milestones'] = [];
}

// Return jika ada error
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete fields: ' . implode(', ', $errors)
    ]);
    exit;
}

// Ambil data dari POST
$project_id         = mysqli_real_escape_string($db, $project_id_raw);
$invoice_purpose    = mysqli_real_escape_string($db, $invoice_purpose_raw);
$additional_info    = mysqli_real_escape_string($db, $_POST['additional_info'] ?? '');
$tin_number         = mysqli_real_escape_string($db, $_POST['tin_number'] ?? ''); // Optional
$ssm_number         = mysqli_real_escape_string($db, $_POST['ssm_number'] ?? ''); // Optional
$follow_milestone   = mysqli_real_escape_string($db, $follow_milestone_db);
$amount_type        = mysqli_real_escape_string($db, $amount_type_raw);
$total_amount       = mysqli_real_escape_string($db, $total_amount_raw);
$sst_amount         = mysqli_real_escape_string($db, $sst_amount_raw);
$total_invoice      = mysqli_real_escape_string($db, $total_invoice_raw);
$member_id          = mysqli_real_escape_string($db, $member_id_raw);
$milestones         = $_POST['milestones'] ?? [];

if ($current_user_id === '' || (string)$member_id !== $current_user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'You are not allowed to submit this invoice.'
    ]);
    exit;
}

$project_check_query = "SELECT leader_id FROM project WHERE id = '$project_id' LIMIT 1";
$project_check_result = mysqli_query($db, $project_check_query);

if (!$project_check_result || mysqli_num_rows($project_check_result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Project not found.'
    ]);
    exit;
}

$project_data = mysqli_fetch_assoc($project_check_result);
if ((string)$project_data['leader_id'] !== $current_user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Only the project leader can submit directly for verification.'
    ]);
    exit;
}

$upload_dir = "project-documents/invoice/";
$uploaded_files = [];

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$file_names = is_array($_FILES['invoice_attachment']['name'])
    ? $_FILES['invoice_attachment']['name']
    : [$_FILES['invoice_attachment']['name']];
$file_tmps = is_array($_FILES['invoice_attachment']['tmp_name'])
    ? $_FILES['invoice_attachment']['tmp_name']
    : [$_FILES['invoice_attachment']['tmp_name']];
$file_errors = is_array($_FILES['invoice_attachment']['error'])
    ? $_FILES['invoice_attachment']['error']
    : [$_FILES['invoice_attachment']['error']];

foreach ($file_names as $index => $file_name) {
    $file_error = $file_errors[$index] ?? UPLOAD_ERR_NO_FILE;

    if ($file_error === UPLOAD_ERR_NO_FILE) {
        continue;
    }

    if ($file_error !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'One or more files failed to upload.'
        ]);
        exit;
    }

    $file_tmp = $file_tmps[$index] ?? '';
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX.'
        ]);
        exit;
    }

    $unique_file_name = uniqid('invoice_', true) . '.' . $file_ext;

    if (!move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload file.'
        ]);
        exit;
    }

    $uploaded_files[] = $unique_file_name;
}

$attachment_value = mysqli_real_escape_string($db, json_encode($uploaded_files));

// Mula transaksi
mysqli_begin_transaction($db);

try {
    /*
        SSM number optional.
        Kalau column ssm_number belum wujud dalam table invoices, query akan insert tanpa column tu.
        Kalau column dah ada, query akan include value.
    */
    $has_ssm_column = false;
    $column_check = mysqli_query($db, "SHOW COLUMNS FROM invoices LIKE 'ssm_number'");
    if ($column_check && mysqli_num_rows($column_check) > 0) {
        $has_ssm_column = true;
    }

    if ($has_ssm_column) {
        // Simpan ke dalam table invoices dengan SSM number
        $query = "
            INSERT INTO invoices (
                project_id, member_id, invoice_purpose, additional_info,
                total_amount, sst_amount, total_invoice,
                attachment, follow_milestone, amount_type, tin_number, ssm_number,
                invoice_status
            ) VALUES (
                '$project_id', '$member_id', '$invoice_purpose', '$additional_info',
                '$total_amount', '$sst_amount', '$total_invoice',
                '$attachment_value', '$follow_milestone', '$amount_type', '$tin_number', '$ssm_number',
                'Pending Verification'
            )
        ";
    } else {
        // Simpan ke dalam table invoices
        $query = "
            INSERT INTO invoices (
                project_id, member_id, invoice_purpose, additional_info,
                total_amount, sst_amount, total_invoice,
                attachment, follow_milestone, amount_type, tin_number,
                invoice_status
            ) VALUES (
                '$project_id', '$member_id', '$invoice_purpose', '$additional_info',
                '$total_amount', '$sst_amount', '$total_invoice',
                '$attachment_value', '$follow_milestone', '$amount_type', '$tin_number',
                'Pending Verification'
            )
        ";
    }

    if (!mysqli_query($db, $query)) {
        throw new Exception('Failed to save invoice: ' . mysqli_error($db));
    }

    $invoice_id = mysqli_insert_id($db);

    // Simpan milestone jika follow = Yes
    if ($is_follow_milestone && !empty($milestones)) {
        foreach ($milestones as $milestone_id) {
            $milestone_id = mysqli_real_escape_string($db, $milestone_id);

            $linkQuery = "
                INSERT INTO invoice_milestones (invoice_id, milestone_id) 
                VALUES ('$invoice_id', '$milestone_id')
            ";

            if (!mysqli_query($db, $linkQuery)) {
                throw new Exception('Failed to save selected milestones: ' . mysqli_error($db));
            }
        }
    }

    mysqli_commit($db);

    echo json_encode([
        'success' => true,
        'message' => 'Invoice has been successfully saved!'
    ]);
    exit;

} catch (Exception $e) {
    mysqli_rollback($db);

    // Remove uploaded file if DB insert failed
    foreach ($uploaded_files as $uploaded_file) {
        if (file_exists($upload_dir . $uploaded_file)) {
            unlink($upload_dir . $uploaded_file);
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
