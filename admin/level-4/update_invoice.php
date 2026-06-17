<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../db_connect/db_connect.php';
include 'auth_check.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Semakan dan kumpul error
$errors = [];

$project_id_raw       = $_POST['project_id'] ?? '';
$invoice_id_raw       = $_POST['invoice_id'] ?? '';
$invoice_purpose_raw  = $_POST['invoice_purpose'] ?? '';
$total_amount_raw     = $_POST['total_amount'] ?? '';
$sst_amount_raw       = $_POST['sst_amount'] ?? '';
$total_invoice_raw    = $_POST['total_invoice'] ?? '';
$follow_raw           = $_POST['follow_milestone'] ?? '';
$amount_type_raw      = $_POST['amount_type'] ?? '';
$member_id_raw        = $_POST['member_id'] ?? '';
$old_attachment_raw   = $_POST['old_attachment'] ?? '';

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
if (trim($invoice_id_raw) === '') $errors[] = 'Invoice ID';
if (trim($invoice_purpose_raw) === '') $errors[] = 'Invoice Purpose';
if (trim($total_amount_raw) === '') $errors[] = 'Total Amount';
if (trim($sst_amount_raw) === '') $errors[] = 'SST Amount';
if (trim($total_invoice_raw) === '') $errors[] = 'Total Invoice';
if (trim($follow_raw) === '') $errors[] = 'Follow Milestone';
if (trim($amount_type_raw) === '') $errors[] = 'Amount Type';
if (trim($member_id_raw) === '') $errors[] = 'Member ID';

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
$invoice_id         = mysqli_real_escape_string($db, $invoice_id_raw);
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
$old_attachment     = mysqli_real_escape_string($db, $old_attachment_raw);
$milestones         = $_POST['milestones'] ?? [];

// Check invoice exists
$invoice_check_query = "SELECT * FROM invoices WHERE id = '$invoice_id' AND project_id = '$project_id' LIMIT 1";
$invoice_check_result = mysqli_query($db, $invoice_check_query);

if (!$invoice_check_result || mysqli_num_rows($invoice_check_result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invoice not found.'
    ]);
    exit;
}

$invoice_data = mysqli_fetch_assoc($invoice_check_result);
$current_attachment = $invoice_data['attachment'];

function parse_invoice_attachments($attachment_value) {
    $attachment_value = trim((string)$attachment_value);

    if ($attachment_value === '') {
        return [];
    }

    $decoded = json_decode($attachment_value, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map('trim', $decoded)));
    }

    return array_values(array_filter(array_map('trim', explode(',', $attachment_value))));
}

// Fail upload
$upload_dir = "../../consultant/project-documents/invoice/";
$current_attachments = parse_invoice_attachments($current_attachment);
$retained_attachments_raw = isset($_POST['retained_attachments']) && is_array($_POST['retained_attachments'])
    ? $_POST['retained_attachments']
    : [];
$retained_attachments = [];

foreach ($retained_attachments_raw as $retained_file) {
    $retained_file = trim(basename((string)$retained_file));

    if ($retained_file !== '' && in_array($retained_file, $current_attachments, true)) {
        $retained_attachments[] = $retained_file;
    }
}

$retained_attachments = array_values(array_unique($retained_attachments));
$removed_attachments = array_values(array_diff($current_attachments, $retained_attachments));
$new_uploaded_files = [];
$attachment_value = !empty($retained_attachments) ? json_encode($retained_attachments) : '';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (isset($_FILES['invoice_attachment'])) {
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

        $new_uploaded_files[] = $unique_file_name;
    }

    if (!empty($new_uploaded_files)) {
        $attachment_value = json_encode(array_values(array_unique(array_merge($retained_attachments, $new_uploaded_files))));
    }
}

if (empty($retained_attachments) && empty($new_uploaded_files)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please keep at least one existing attachment or upload a new file.'
    ]);
    exit;
}

$attachment_value_sql = mysqli_real_escape_string($db, $attachment_value);

// Mula transaksi
mysqli_begin_transaction($db);

try {
    /*
        SSM number optional.
        Kalau column ssm_number belum wujud dalam table invoices, query akan update tanpa column tu.
        Kalau column dah ada, query akan include value.
    */
    $has_ssm_column = false;
    $column_check = mysqli_query($db, "SHOW COLUMNS FROM invoices LIKE 'ssm_number'");
    if ($column_check && mysqli_num_rows($column_check) > 0) {
        $has_ssm_column = true;
    }

    if ($has_ssm_column) {
        // Update table invoices dengan SSM number
        $query = "
            UPDATE invoices SET
                member_id = '$member_id',
                invoice_purpose = '$invoice_purpose',
                additional_info = '$additional_info',
                total_amount = '$total_amount',
                sst_amount = '$sst_amount',
                total_invoice = '$total_invoice',
                attachment = '$attachment_value_sql',
                follow_milestone = '$follow_milestone',
                amount_type = '$amount_type',
                tin_number = '$tin_number',
                ssm_number = '$ssm_number'
            WHERE id = '$invoice_id'
            AND project_id = '$project_id'
        ";
    } else {
        // Update table invoices
        $query = "
            UPDATE invoices SET
                member_id = '$member_id',
                invoice_purpose = '$invoice_purpose',
                additional_info = '$additional_info',
                total_amount = '$total_amount',
                sst_amount = '$sst_amount',
                total_invoice = '$total_invoice',
                attachment = '$attachment_value_sql',
                follow_milestone = '$follow_milestone',
                amount_type = '$amount_type',
                tin_number = '$tin_number'
            WHERE id = '$invoice_id'
            AND project_id = '$project_id'
        ";
    }

    if (!mysqli_query($db, $query)) {
        throw new Exception('Failed to update invoice: ' . mysqli_error($db));
    }

    /*
        IMPORTANT:
        Untuk edit milestone, jangan insert terus tanpa clear.
        Kalau insert terus, data milestone boleh duplicate.
        Jadi delete mapping lama dulu, kemudian insert selected milestone baru.
    */
    $delete_milestones_query = "DELETE FROM invoice_milestones WHERE invoice_id = '$invoice_id'";
    if (!mysqli_query($db, $delete_milestones_query)) {
        throw new Exception('Failed to reset invoice milestones: ' . mysqli_error($db));
    }

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

    /*
        Optional tracker.
        Kalau table project_tracker column berbeza, comment block ni dulu.
    */
    $admin_staff_id = isset($_SESSION['user_data']['staff_id']) ? mysqli_real_escape_string($db, $_SESSION['user_data']['staff_id']) : '';
    $tracker_description = mysqli_real_escape_string($db, "Invoice application has been updated by Admin Level 3 ($admin_staff_id).");
    $tracker_query = "
        INSERT INTO project_tracker (project_id, remark, date)
        VALUES ('$project_id', '$tracker_description', NOW())
    ";
    @mysqli_query($db, $tracker_query);

    mysqli_commit($db);

    if (!empty($removed_attachments)) {
        foreach ($removed_attachments as $removed_file) {
            $removed_path = $upload_dir . $removed_file;

            if (is_file($removed_path)) {
                unlink($removed_path);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Invoice has been successfully updated!',
        'project_id' => $project_id,
        'invoice_id' => $invoice_id
    ]);
    exit;

} catch (Exception $e) {
    mysqli_rollback($db);

    // Remove new uploaded file if DB update failed
    if (
        !empty($new_uploaded_files)
    ) {
        foreach ($new_uploaded_files as $uploaded_file) {
            if (file_exists($upload_dir . $uploaded_file)) {
                unlink($upload_dir . $uploaded_file);
            }
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
