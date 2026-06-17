<?php
session_start();
include '../db_connect/db_connect.php';
include '../function/function.php';

function allowance_json_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function allowance_table_columns($db, $table_name) {
    $columns = [];
    $safe_table_name = mysqli_real_escape_string($db, $table_name);
    $result = mysqli_query($db, "SHOW COLUMNS FROM `$safe_table_name`");

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[] = $row['Field'];
        }
    }

    return $columns;
}

function allowance_upload_outsider_document($file, $prefix) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $original_name = $file['name'];
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions)) {
        throw new Exception('Only PDF, JPG, JPEG, and PNG files are allowed for outsider documents.');
    }

    if ((int)$file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Outsider document file size must not exceed 5MB.');
    }

    $upload_dir = '../allowance-outsider-documents/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        throw new Exception('Unable to prepare outsider document upload folder.');
    }

    $file_name = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $target_path = $upload_dir . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception('Failed to upload outsider document.');
    }

    return $file_name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve common fields
    $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
    $application_for = mysqli_real_escape_string($db, $_POST['application_for']);

    if ($application_for === 'Research assistant allowance') {
        if (
            empty($_POST['ra_application_id']) ||
            empty($_POST['allowance_month_no']) ||
            empty($_POST['allowance_start_date']) ||
            empty($_POST['allowance_end_date']) ||
            empty($_POST['allowance_month']) ||
            empty($_POST['ra_job_description'])
        ) {
            allowance_json_response(false, 'Please complete the RA allowance details.');
        }

        $ra_application_id = mysqli_real_escape_string($db, $_POST['ra_application_id']);
        $allowance_month_no = (int)$_POST['allowance_month_no'];
        $allowance_start_date = mysqli_real_escape_string($db, $_POST['allowance_start_date']);
        $allowance_end_date = mysqli_real_escape_string($db, $_POST['allowance_end_date']);
        $allowance_month = mysqli_real_escape_string($db, $_POST['allowance_month']);
        $ra_job_description = mysqli_real_escape_string($db, $_POST['ra_job_description']);

        $ra_query = "
            SELECT
                raa.id AS ra_application_id,
                raa.ra_id,
                raa.duration,
                raa.budget,
                ra.full_name,
                ra.email,
                ra.ic,
                ra.bank_name,
                ra.no_account
            FROM research_assistant_application raa
            INNER JOIN research_assistant ra ON ra.id = raa.ra_id
            WHERE raa.id = '$ra_application_id'
              AND raa.project_id = '$project_id'
              AND raa.status = 'Approved'
            LIMIT 1
        ";
        $ra_result = mysqli_query($db, $ra_query);

        if (!$ra_result || mysqli_num_rows($ra_result) === 0) {
            allowance_json_response(false, 'Selected RA appointment is invalid or not approved.');
        }

        $ra = mysqli_fetch_assoc($ra_result);
        $duration = (int)($ra['duration'] ?? 0);

        if ($allowance_month_no < 1 || ($duration > 0 && $allowance_month_no > $duration)) {
            allowance_json_response(false, 'Selected allowance month is outside the RA appointment duration.');
        }

        $ra_id = mysqli_real_escape_string($db, $ra['ra_id']);
        $name = mysqli_real_escape_string($db, $ra['full_name']);
        $email = mysqli_real_escape_string($db, $ra['email']);
        $bank_name = mysqli_real_escape_string($db, $ra['bank_name']);
        $no_account = mysqli_real_escape_string($db, $ra['no_account']);
        $ic = mysqli_real_escape_string($db, $ra['ic']);
        $monthly_amount = number_format((float)$ra['budget'], 2, '.', '');
        $total_allowance = $monthly_amount;

        // Insert into database
        $query = "INSERT INTO allowance_applications 
                    (project_id, application_for, member_id, ra_application_id, name, email, job_description, allowance_start_date, allowance_end_date, allowance_month, allowance_month_no, allowance_monthly_amount, total_allowance, bank_name, no_account, ic, status, created_at) 
                  VALUES 
                    ('$project_id', '$application_for', '$ra_id', '$ra_application_id', '$name', '$email', '$ra_job_description', '$allowance_start_date', '$allowance_end_date', '$allowance_month', '$allowance_month_no', '$monthly_amount', '$total_allowance', '$bank_name', '$no_account', '$ic', 'Pending Verification', NOW());";

    } elseif ($application_for === 'Outsider allowance') {
        if (
            empty($_POST['outsider_name']) ||
            empty($_POST['outsider_email']) ||
            empty($_POST['outsider_bank_name']) ||
            empty($_POST['outsider_bank_account']) ||
            empty($_POST['outsider_ic']) ||
            empty($_POST['outsider_job_description']) ||
            empty($_POST['outsider_total_allowance']) ||
            empty($_POST['outsider_start_date']) ||
            empty($_POST['outsider_end_date'])
        ) {
            allowance_json_response(false, 'Please complete all outsider allowance details.');
        }

        // Fields specific to Outsider allowance
        $outsider_name = mysqli_real_escape_string($db, $_POST['outsider_name']);
        $outsider_email = mysqli_real_escape_string($db, $_POST['outsider_email']);
        $outsider_bank_name = mysqli_real_escape_string($db, $_POST['outsider_bank_name']);
        $outsider_bank_account = mysqli_real_escape_string($db, $_POST['outsider_bank_account']);
        $outsider_ic = mysqli_real_escape_string($db, $_POST['outsider_ic']);
        $outsider_job_description = mysqli_real_escape_string($db, $_POST['outsider_job_description']);
        $outsider_start_date = mysqli_real_escape_string($db, $_POST['outsider_start_date']);
        $outsider_end_date = mysqli_real_escape_string($db, $_POST['outsider_end_date']);
        $outsider_total_allowance = number_format((float)$_POST['outsider_total_allowance'], 2, '.', '');

        if ((float)$outsider_total_allowance <= 0) {
            allowance_json_response(false, 'Total allowance must be greater than 0.');
        }

        if (strtotime($outsider_end_date) < strtotime($outsider_start_date)) {
            allowance_json_response(false, 'Outsider end date cannot be earlier than start date.');
        }

        $allowance_columns = allowance_table_columns($db, 'allowance_applications');
        if (!in_array('outsider_ic_file', $allowance_columns) || !in_array('outsider_bank_statement_file', $allowance_columns)) {
            allowance_json_response(false, 'Please add outsider_ic_file and outsider_bank_statement_file columns to allowance_applications before submitting outsider allowance.');
        }

        $existing_document_query = "
            SELECT outsider_ic_file, outsider_bank_statement_file
            FROM allowance_applications
            WHERE application_for = 'Outsider allowance'
            AND (
                email = '$outsider_email'
                OR ic = '$outsider_ic'
            )
            AND outsider_ic_file IS NOT NULL
            AND outsider_ic_file <> ''
            AND outsider_bank_statement_file IS NOT NULL
            AND outsider_bank_statement_file <> ''
            ORDER BY id DESC
            LIMIT 1
        ";
        $existing_document_result = mysqli_query($db, $existing_document_query);
        $outsider_ic_file = '';
        $outsider_bank_statement_file = '';

        if ($existing_document_result && mysqli_num_rows($existing_document_result) > 0) {
            $existing_document = mysqli_fetch_assoc($existing_document_result);
            $outsider_ic_file = mysqli_real_escape_string($db, $existing_document['outsider_ic_file']);
            $outsider_bank_statement_file = mysqli_real_escape_string($db, $existing_document['outsider_bank_statement_file']);
        } else {
            try {
                $outsider_ic_file = mysqli_real_escape_string($db, allowance_upload_outsider_document($_FILES['outsider_ic_file'] ?? null, 'outsider_ic'));
                $outsider_bank_statement_file = mysqli_real_escape_string($db, allowance_upload_outsider_document($_FILES['outsider_bank_statement_file'] ?? null, 'outsider_bank_statement'));
            } catch (Exception $e) {
                allowance_json_response(false, $e->getMessage());
            }

            if ($outsider_ic_file === '' || $outsider_bank_statement_file === '') {
                allowance_json_response(false, 'Please upload both IC copy and bank statement for first-time outsider.');
            }
        }

        // Insert into database
        $query = "INSERT INTO allowance_applications 
                    (project_id, application_for, bank_name, no_account, ic, outsider_ic_file, outsider_bank_statement_file, name, email, job_description, allowance_start_date, allowance_end_date, total_allowance, status, created_at) 
                  VALUES 
                    ('$project_id', '$application_for', '$outsider_bank_name', '$outsider_bank_account', '$outsider_ic', '$outsider_ic_file', '$outsider_bank_statement_file', '$outsider_name', '$outsider_email', '$outsider_job_description', '$outsider_start_date', '$outsider_end_date', '$outsider_total_allowance', 'Pending Verification', NOW());";
    } else {
        // Invalid application type
        allowance_json_response(false, 'Invalid application type');
    }

    // Execute query
    if (mysqli_query($db, $query)) {
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit application: ' . mysqli_error($db)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
