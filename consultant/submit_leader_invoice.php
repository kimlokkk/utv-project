<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include 'auth_check.php';
include '../db_connect/db_connect.php';
include '../function/function.php';

$userData = $_SESSION['user_data'] ?? [];
$current_user_id = isset($userData['id']) ? (string)$userData['id'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Semakan dan kumpul error
    $errors = [];

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

    if (empty($_POST['id'])) $errors[] = 'Project ID';
    if (empty($_POST['invoice_purpose'])) $errors[] = 'Invoice Purpose';
    if (empty($_POST['total_amount'])) $errors[] = 'Total Amount';
    if (empty($_POST['sst_amount'])) $errors[] = 'SST Amount';
    if (empty($_POST['total_invoice'])) $errors[] = 'Total Invoice';
    if (empty($_POST['follow_milestone'])) $errors[] = 'Follow Milestone';
    if (empty($_POST['amount_type'])) $errors[] = 'Amount Type';
    if (empty($_POST['member_id'])) $errors[] = 'Member ID';
    if (!has_invoice_attachments()) {
        $errors[] = 'Invoice Attachment';
    }

    // Kalau follow milestone, pastikan ada at least satu milestone
    if (
        isset($_POST['follow_milestone']) && 
        in_array(strtolower(trim($_POST['follow_milestone'])), ['yes', 'y', '1', 'true']) && 
        (empty($_POST['milestones']) || !is_array($_POST['milestones']))
    ) {
        $errors[] = 'Milestone Selection';
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
    $project_id         = mysqli_real_escape_string($db, $_POST['id']);
    $invoice_purpose    = mysqli_real_escape_string($db, $_POST['invoice_purpose']);
    $additional_info    = mysqli_real_escape_string($db, $_POST['additional_info'] ?? null);
    $tin_number         = mysqli_real_escape_string($db, $_POST['tin_number']);
    $follow_normalized = strtolower(trim($_POST['follow_milestone']));
    $is_follow_milestone = in_array($follow_normalized, ['yes', 'y', '1', 'true']);
    $follow_milestone   = mysqli_real_escape_string($db, $is_follow_milestone ? 'Yes' : 'No');
    $amount_type        = mysqli_real_escape_string($db, $_POST['amount_type']);
    $total_amount       = mysqli_real_escape_string($db, $_POST['total_amount']);
    $sst_amount         = mysqli_real_escape_string($db, $_POST['sst_amount']);
    $total_invoice      = mysqli_real_escape_string($db, $_POST['total_invoice']);
    $member_id          = mysqli_real_escape_string($db, $_POST['member_id']);
    if ($current_user_id === '' || (string)$member_id !== $current_user_id) {
        echo json_encode(['success' => false, 'message' => 'You are not allowed to submit this invoice.']);
        exit;
    }

    $project_query = mysqli_query($db, "SELECT leader_id, project_no FROM project WHERE id = '$project_id' LIMIT 1");
    if (!$project_query || mysqli_num_rows($project_query) === 0) {
        echo json_encode(['success' => false, 'message' => 'Project not found.']);
        exit;
    }

    $project_data = mysqli_fetch_assoc($project_query);
    $project_no = $project_data['project_no'];
    $is_project_leader_submission = (string)$project_data['leader_id'] === $current_user_id;
    if (!$is_project_leader_submission) {
        $member_check = mysqli_query($db, "
            SELECT 1 
            FROM project_members_consultant 
            WHERE project_id = '$project_id' AND member_id = '$current_user_id' 
            LIMIT 1
        ");

        if (!$member_check || mysqli_num_rows($member_check) === 0) {
            echo json_encode(['success' => false, 'message' => 'Only project members can submit this invoice to the project leader.']);
            exit;
        }
    }

    $invoice_status = $is_project_leader_submission ? 'Pending Verification' : 'Pending Leader Review';
    $remark = $is_project_leader_submission
        ? "Invoice application has been submitted by project leader."
        : "Invoice application has been submitted by team member and is pending project leader review.";
    $date = date('Y-m-d H:i:s');
    $milestones         = $_POST['milestones'] ?? [];

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
            echo json_encode(['success' => false, 'message' => 'One or more files failed to upload.']);
            exit;
        }

        $file_tmp = $file_tmps[$index] ?? '';
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX.']);
            exit;
        }

        $unique_file_name = uniqid('invoice_', true) . '.' . $file_ext;

        if (!move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
            exit;
        }

        $uploaded_files[] = $unique_file_name;
    }

    $attachment_value = mysqli_real_escape_string($db, json_encode($uploaded_files));

    // Mula transaksi
    mysqli_begin_transaction($db);
    try {
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
                '$invoice_status'
            )
        ";
        
        if (!mysqli_query($db, $query)) {
            throw new Exception('Failed to save invoice.');
        }

        $invoice_id = mysqli_insert_id($db);

        // Simpan milestone jika follow = yes
        if ($is_follow_milestone && !empty($milestones)) {
            foreach ($milestones as $milestone_id) {
                $milestone_id = mysqli_real_escape_string($db, $milestone_id);
                $linkQuery = "INSERT INTO invoice_milestones (invoice_id, milestone_id) VALUES ('$invoice_id', '$milestone_id')";
                if (!mysqli_query($db, $linkQuery)) {
                    throw new Exception('Failed to save selected milestones.');
                }
            }
        }
        
        $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                          VALUES ('$project_id', '$project_no', '$remark', '$date')";
        $tracker_result = mysqli_query($db, $tracker_query);

        if (!$tracker_result) {
            throw new Exception('Failed to insert record into project tracker.');
        }

        mysqli_commit($db);
        echo json_encode(['success' => true, 'message' => 'Invoice has been successfully saved!']);
    } catch (Exception $e) {
        mysqli_rollback($db);
        foreach ($uploaded_files as $uploaded_file) {
            if (file_exists($upload_dir . $uploaded_file)) {
                unlink($upload_dir . $uploaded_file);
            }
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
