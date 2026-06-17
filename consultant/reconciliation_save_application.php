<?php
function reconciliation_column_exists($db, $table, $column) {
    $table = mysqli_real_escape_string($db, $table);
    $column = mysqli_real_escape_string($db, $column);
    $result = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function reconciliation_table_exists($db, $table) {
    $table = mysqli_real_escape_string($db, $table);
    $result = mysqli_query($db, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function reconciliation_files_from_request($field) {
    if (!isset($_FILES[$field])) {
        return [];
    }

    $files = $_FILES[$field];
    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $tmps = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
    $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
    $result = [];

    foreach ($names as $index => $name) {
        $error = $errors[$index] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $result[] = [
            'index' => $index,
            'name' => $name,
            'tmp_name' => $tmps[$index] ?? '',
            'error' => $error
        ];
    }

    return $result;
}

function reconciliation_nested_files_from_request($field) {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
        return [];
    }

    $files = $_FILES[$field];
    $result = [];

    foreach ($files['name'] as $row_index => $names) {
        $row_files = [];
        $names = is_array($names) ? $names : [$names];

        foreach ($names as $file_index => $name) {
            $error = $files['error'][$row_index][$file_index] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $row_files[] = [
                'name' => $name,
                'tmp_name' => $files['tmp_name'][$row_index][$file_index] ?? '',
                'error' => $error
            ];
        }

        $result[$row_index] = $row_files;
    }

    return $result;
}

function reconciliation_upload_one_file($file, $upload_dir, $prefix) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('One or more files failed to upload.');
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception('Invalid file type. Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX.');
    }

    $safe_prefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix);
    $unique_file_name = $safe_prefix . '_' . uniqid('', true) . '.' . $file_ext;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $unique_file_name)) {
        throw new Exception('Failed to upload file.');
    }

    return $unique_file_name;
}

function reconciliation_insert_receipts($db, $application_id, $uploaded_files, $original_names, $uploaded_by) {
    if (!reconciliation_table_exists($db, 'reconciliation_claim_receipts')) {
        return;
    }

    foreach ($uploaded_files as $index => $file_name) {
        $safe_file = mysqli_real_escape_string($db, $file_name);
        $safe_original = mysqli_real_escape_string($db, $original_names[$index] ?? $file_name);
        $safe_user = mysqli_real_escape_string($db, $uploaded_by);
        $query = "
            INSERT INTO reconciliation_claim_receipts (application_id, file_name, original_name, uploaded_by, uploaded_at)
            VALUES ('$application_id', '$safe_file', '$safe_original', '$safe_user', NOW())
        ";

        if (!mysqli_query($db, $query)) {
            throw new Exception('Failed to save receipt upload: ' . mysqli_error($db));
        }
    }
}

function reconciliation_save_application($db, $status) {
    $project_id = mysqli_real_escape_string($db, $_POST['project_id'] ?? '');
    $project_no = mysqli_real_escape_string($db, $_POST['project_number'] ?? '');
    $project_leader = mysqli_real_escape_string($db, $_POST['project_leader'] ?? '');
    $applicant_id = mysqli_real_escape_string($db, $_POST['applicant_id'] ?? '');
    $applicant_ic = mysqli_real_escape_string($db, $_POST['applicant_ic'] ?? '');
    $application_type = mysqli_real_escape_string($db, $_POST['application_type'] ?? '');
    $advance_id = mysqli_real_escape_string($db, $_POST['advance_id'] ?? '');
    $date = date('Y-m-d H:i:s');

    if ($project_id === '' || $project_no === '' || $project_leader === '' || $applicant_id === '' || $application_type === '') {
        throw new Exception('Incomplete form data. Please check all required fields.');
    }

    $allowed_types = ['Advance', 'Reconciliation', 'Claim'];
    if (!in_array($application_type, $allowed_types)) {
        throw new Exception('Invalid application type.');
    }

    $claim_categories = $_POST['claim_category'] ?? [];
    $claim_items = $_POST['claim_item'] ?? [];
    $claim_quantities = $_POST['claim_quantity'] ?? [];
    $claim_amounts = $_POST['claim_amount'] ?? [];
    $adjustment_amounts = $_POST['adjustment_amount'] ?? [];
    $appendix_types = $_POST['appendix_type'] ?? [];
    $appendix_data = $_POST['appendix_data'] ?? [];

    if ($application_type === 'Reconciliation' && $advance_id === '') {
        throw new Exception('Please select an advance application to reconcile.');
    }

    $upload_dir = 'project-documents/reconciliation-claim-receipts/';
    $receipt_files = [];
    $receipt_originals = [];
    $legacy_receipt_file = '';
    $total_amount = 0;
    $total_adjustment = 0;

    foreach ($claim_amounts as $amount) {
        $total_amount += (float)$amount;
    }

    foreach ($adjustment_amounts as $amount) {
        $total_adjustment += (float)$amount;
    }

    mysqli_begin_transaction($db);

    try {
        $columns = [
            'project_id' => "'$project_id'",
            'project_leader' => "'$project_leader'",
            'applicant_id' => "'$applicant_id'",
            'application_type' => "'$application_type'",
            'receipt_file' => "'" . mysqli_real_escape_string($db, $legacy_receipt_file) . "'",
            'status' => "'" . mysqli_real_escape_string($db, $status) . "'",
            'date_applied' => "'" . date('Y-m-d') . "'",
            'created_at' => "'$date'"
        ];

        if (reconciliation_column_exists($db, 'reconciliation_claim_applications', 'total_amount')) {
            $columns['total_amount'] = "'" . number_format($total_amount, 2, '.', '') . "'";
        }

        if (reconciliation_column_exists($db, 'reconciliation_claim_applications', 'adjustment_amount')) {
            $columns['adjustment_amount'] = "'" . number_format($total_adjustment, 2, '.', '') . "'";
        }

        if (reconciliation_column_exists($db, 'reconciliation_claim_applications', 'remark_return')) {
            $columns['remark_return'] = "''";
        }

        $query = "INSERT INTO reconciliation_claim_applications (" . implode(', ', array_keys($columns)) . ")
                  VALUES (" . implode(', ', array_values($columns)) . ")";

        if (!mysqli_query($db, $query)) {
            throw new Exception('Failed to save application: ' . mysqli_error($db));
        }

        $application_id = mysqli_insert_id($db);
        $proof_files = [];
        foreach (reconciliation_nested_files_from_request('proof_file') as $row_index => $row_files) {
            foreach ($row_files as $file) {
                $proof_files[$row_index][] = reconciliation_upload_one_file($file, $upload_dir, $project_no . '_' . $applicant_ic . '_proof');
            }
        }

        $count = count($claim_categories);
        for ($i = 0; $i < $count; $i++) {
            $cat = mysqli_real_escape_string($db, $claim_categories[$i] ?? '');
            $item = mysqli_real_escape_string($db, $claim_items[$i] ?? '');
            $qty = mysqli_real_escape_string($db, $claim_quantities[$i] ?? '1');
            $amount = mysqli_real_escape_string($db, $claim_amounts[$i] ?? '0');
            $adjustment = mysqli_real_escape_string($db, $adjustment_amounts[$i] ?? '0');
            $appendix = mysqli_real_escape_string($db, $appendix_types[$i] ?? '');
            $appendix_json = mysqli_real_escape_string($db, $appendix_data[$i] ?? '');
            $proof_file = mysqli_real_escape_string($db, json_encode($proof_files[$i] ?? []));
            $advance_item_id = mysqli_real_escape_string($db, $_POST['advance_item_id'][$i] ?? '');

            if ($cat === '' && $item === '' && $qty === '' && $amount === '') {
                continue;
            }

            $item_columns = [
                'application_id' => "'$application_id'",
                'claim_category' => "'$cat'",
                'claim_item' => "'$item'",
                'claim_quantity' => "'$qty'",
                'claim_amount' => "'$amount'",
                'date_created' => "'$date'"
            ];

            if (reconciliation_column_exists($db, 'reconciliation_claim_items', 'advance_item_id')) {
                $item_columns['advance_item_id'] = $advance_item_id === '' ? "NULL" : "'$advance_item_id'";
            }

            if (reconciliation_column_exists($db, 'reconciliation_claim_items', 'adjustment_amount')) {
                $item_columns['adjustment_amount'] = "'$adjustment'";
            }

            if (reconciliation_column_exists($db, 'reconciliation_claim_items', 'proof_file')) {
                $item_columns['proof_file'] = "'$proof_file'";
            }

            if (reconciliation_column_exists($db, 'reconciliation_claim_items', 'appendix_type')) {
                $item_columns['appendix_type'] = "'$appendix'";
            }

            if (reconciliation_column_exists($db, 'reconciliation_claim_items', 'appendix_data')) {
                $item_columns['appendix_data'] = "'$appendix_json'";
            }

            $item_query = "INSERT INTO reconciliation_claim_items (" . implode(', ', array_keys($item_columns)) . ")
                           VALUES (" . implode(', ', array_values($item_columns)) . ")";

            if (!mysqli_query($db, $item_query)) {
                throw new Exception('Failed to save application item: ' . mysqli_error($db));
            }
        }

        if ($application_type === 'Reconciliation' && $advance_id !== '') {
            $match_query = "INSERT INTO reconciliation_claim_matches (application_id, advance_id, created_at)
                            VALUES ('$application_id', '$advance_id', '$date')";
            if (!mysqli_query($db, $match_query)) {
                throw new Exception('Failed to save reconciliation match: ' . mysqli_error($db));
            }
        }

        mysqli_commit($db);
        return $application_id;
    } catch (Exception $e) {
        mysqli_rollback($db);
        $uploaded_proofs = [];
        foreach (($proof_files ?? []) as $row_files) {
            foreach ($row_files as $row_file) {
                $uploaded_proofs[] = $row_file;
            }
        }

        foreach (array_merge($receipt_files, $uploaded_proofs) as $uploaded_file) {
            if ($uploaded_file && file_exists($upload_dir . $uploaded_file)) {
                unlink($upload_dir . $uploaded_file);
            }
        }
        throw $e;
    }
}
?>
