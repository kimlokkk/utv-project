<?php
session_start();
include 'auth_check.php';
include '../../db_connect/db_connect.php';
include '../../function/function.php';

$userData = $_SESSION['user_data'];
$application_id = mysqli_real_escape_string($db, $_GET['applicationId'] ?? $_POST['application_id'] ?? '');

function arc_edit_is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function arc_edit_json_response($success, $message, $redirect = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    exit;
}

function arc_edit_decode_proof_files($stored_files) {
    $stored_files = trim((string)$stored_files);
    if ($stored_files === '') {
        return [];
    }

    $decoded = json_decode($stored_files, true);
    if (is_array($decoded)) {
        return array_values(array_filter($decoded, function ($file) {
            return trim((string)$file) !== '';
        }));
    }

    return [$stored_files];
}

function arc_edit_nested_files_from_request($field) {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
        return [];
    }

    $files = $_FILES[$field];
    $result = [];

    foreach ($files['name'] as $row_index => $names) {
        $names = is_array($names) ? $names : [$names];

        foreach ($names as $file_index => $name) {
            $error = $files['error'][$row_index][$file_index] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $result[$row_index][] = [
                'name' => $name,
                'tmp_name' => $files['tmp_name'][$row_index][$file_index] ?? '',
                'error' => $error
            ];
        }
    }

    return $result;
}

function arc_edit_upload_one_file($file, $upload_dir, $prefix) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('One or more proof files failed to upload.');
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception('Invalid proof file type. Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX.');
    }

    $safe_prefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix);
    $unique_file_name = $safe_prefix . '_' . uniqid('', true) . '.' . $file_ext;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $unique_file_name)) {
        throw new Exception('Failed to upload proof file.');
    }

    return $unique_file_name;
}

if ($application_id === '') {
    echo '<script>alert("Invalid application ID."); window.location.href="reconciliation-claim.php";</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_ajax = arc_edit_is_ajax_request();
    $application_type = mysqli_real_escape_string($db, $_POST['application_type'] ?? '');
    $claim_categories = $_POST['claim_category'] ?? [];
    $claim_items = $_POST['claim_item'] ?? [];
    $claim_quantities = $_POST['claim_quantity'] ?? [];
    $claim_amounts = $_POST['claim_amount'] ?? [];
    $adjustment_amounts = $_POST['adjustment_amount'] ?? [];
    $old_proof_files = $_POST['old_proof_file'] ?? [];
    $appendix_types = $_POST['appendix_type'] ?? [];
    $total_amount = 0;
    $total_adjustment = 0;

    foreach ($claim_amounts as $amount) {
        $total_amount += (float)$amount;
    }
    foreach ($adjustment_amounts as $amount) {
        $total_adjustment += (float)$amount;
    }

    $context_query = "
        SELECT rc.applicant_id, p.project_no
        FROM reconciliation_claim_applications rc
        INNER JOIN project p ON rc.project_id = p.id
        WHERE rc.application_id = '$application_id'
        LIMIT 1
    ";
    $context_result = mysqli_query($db, $context_query);
    if (!$context_result || mysqli_num_rows($context_result) === 0) {
        if ($is_ajax) {
            arc_edit_json_response(false, 'Application not found.');
        }
        echo '<script>alert("Application not found."); window.location.href="reconciliation-claim.php";</script>';
        exit;
    }
    $upload_context = mysqli_fetch_assoc($context_result);
    $upload_dir = '../../consultant/project-documents/reconciliation-claim-receipts/';
    $uploaded_proofs = [];
    $uploaded_file_names = [];

    mysqli_begin_transaction($db);
    try {
        foreach (arc_edit_nested_files_from_request('proof_file') as $row_index => $row_files) {
            foreach ($row_files as $file) {
                $uploaded_name = arc_edit_upload_one_file(
                    $file,
                    $upload_dir,
                    ($upload_context['project_no'] ?? 'project') . '_' . ($upload_context['applicant_id'] ?? 'applicant') . '_proof'
                );
                $uploaded_proofs[$row_index][] = $uploaded_name;
                $uploaded_file_names[] = $uploaded_name;
            }
        }

        $update_parts = ["application_type = '$application_type'"];

        $column_check = mysqli_query($db, "SHOW COLUMNS FROM reconciliation_claim_applications LIKE 'total_amount'");
        if ($column_check && mysqli_num_rows($column_check) > 0) {
            $update_parts[] = "total_amount = '" . number_format($total_amount, 2, '.', '') . "'";
        }

        $column_check = mysqli_query($db, "SHOW COLUMNS FROM reconciliation_claim_applications LIKE 'adjustment_amount'");
        if ($column_check && mysqli_num_rows($column_check) > 0) {
            $update_parts[] = "adjustment_amount = '" . number_format($total_adjustment, 2, '.', '') . "'";
        }

        $update_query = "UPDATE reconciliation_claim_applications SET " . implode(', ', $update_parts) . " WHERE application_id = '$application_id'";
        if (!mysqli_query($db, $update_query)) {
            throw new Exception('Failed to update application.');
        }

        if (!mysqli_query($db, "DELETE FROM reconciliation_claim_items WHERE application_id = '$application_id'")) {
            throw new Exception('Failed to reset item details.');
        }

        $has_adjustment = mysqli_query($db, "SHOW COLUMNS FROM reconciliation_claim_items LIKE 'adjustment_amount'");
        $has_adjustment = $has_adjustment && mysqli_num_rows($has_adjustment) > 0;
        $has_proof = mysqli_query($db, "SHOW COLUMNS FROM reconciliation_claim_items LIKE 'proof_file'");
        $has_proof = $has_proof && mysqli_num_rows($has_proof) > 0;
        $has_appendix = mysqli_query($db, "SHOW COLUMNS FROM reconciliation_claim_items LIKE 'appendix_type'");
        $has_appendix = $has_appendix && mysqli_num_rows($has_appendix) > 0;

        for ($i = 0; $i < count($claim_categories); $i++) {
            $cat = mysqli_real_escape_string($db, $claim_categories[$i] ?? '');
            $item = mysqli_real_escape_string($db, $claim_items[$i] ?? '');
            $qty = mysqli_real_escape_string($db, $claim_quantities[$i] ?? '1');
            $amount = mysqli_real_escape_string($db, $claim_amounts[$i] ?? '0');
            $adjustment = mysqli_real_escape_string($db, $adjustment_amounts[$i] ?? '0');
            $proof_files = array_merge(
                arc_edit_decode_proof_files($old_proof_files[$i] ?? ''),
                $uploaded_proofs[$i] ?? []
            );
            $proof_file = mysqli_real_escape_string($db, json_encode(array_values($proof_files)));
            $appendix_type = mysqli_real_escape_string($db, $appendix_types[$i] ?? '');

            if ($cat === '' && $item === '' && $amount === '') {
                continue;
            }

            $item_columns = ['application_id', 'claim_category', 'claim_item', 'claim_quantity', 'claim_amount', 'date_created'];
            $item_values = ["'$application_id'", "'$cat'", "'$item'", "'$qty'", "'$amount'", "NOW()"];

            if ($has_adjustment) {
                $item_columns[] = 'adjustment_amount';
                $item_values[] = "'$adjustment'";
            }
            if ($has_proof) {
                $item_columns[] = 'proof_file';
                $item_values[] = "'$proof_file'";
            }
            if ($has_appendix) {
                $item_columns[] = 'appendix_type';
                $item_values[] = "'$appendix_type'";
            }

            $item_query = "INSERT INTO reconciliation_claim_items (" . implode(', ', $item_columns) . ")
                           VALUES (" . implode(', ', $item_values) . ")";

            if (!mysqli_query($db, $item_query)) {
                throw new Exception('Failed to save item details.');
            }
        }

        mysqli_commit($db);
        if ($is_ajax) {
            arc_edit_json_response(true, 'ARC application updated successfully.', 'reconciliation-claim.php');
        }
        echo '<script>alert("ARC application updated successfully."); window.location.href="reconciliation-claim.php";</script>';
        exit;
    } catch (Exception $e) {
        mysqli_rollback($db);
        foreach ($uploaded_file_names as $uploaded_file_name) {
            $uploaded_path = $upload_dir . $uploaded_file_name;
            if (is_file($uploaded_path)) {
                unlink($uploaded_path);
            }
        }
        if ($is_ajax) {
            arc_edit_json_response(false, $e->getMessage());
        }
        echo '<script>alert("' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '");</script>';
    }
}

$application_query = "
    SELECT rc.*, p.project_no, p.project_title, p.project_leader AS project_leader_name
    FROM reconciliation_claim_applications rc
    INNER JOIN project p ON rc.project_id = p.id
    WHERE rc.application_id = '$application_id'
    LIMIT 1
";
$application_result = mysqli_query($db, $application_query);
if (!$application_result || mysqli_num_rows($application_result) === 0) {
    echo '<script>alert("Application not found."); window.location.href="reconciliation-claim.php";</script>';
    exit;
}
$application = mysqli_fetch_assoc($application_result);

$items = [];
$items_result = mysqli_query($db, "SELECT * FROM reconciliation_claim_items WHERE application_id = '$application_id' ORDER BY id ASC");
while ($items_result && $row = mysqli_fetch_assoc($items_result)) {
    $items[] = $row;
}
if (empty($items)) {
    $items[] = ['claim_category' => 'F&B', 'claim_item' => '', 'claim_quantity' => 1, 'claim_amount' => '0.00', 'adjustment_amount' => '0.00'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/dropzone-master/dist/dropzone.css" rel="stylesheet">
    <style>
        #itemsTable th,
        #itemsTable td {
            vertical-align: middle;
        }
        .proof-dropzone {
            min-height: 86px;
            padding: 14px;
            border: 1px dashed #b8c2cc;
            border-radius: 4px;
            background: #fbfcfd;
        }
        .proof-dropzone .dz-message {
            margin: 0;
            color: #6c757d;
            font-size: 13px;
        }
        .proof-links {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        .proof-links .btn {
            padding: 3px 8px;
            font-size: 12px;
        }
    </style>
</head>
<body class="skin-green fixed-layout">
<?php include 'include/preloader.php'; ?>
<div id="main-wrapper">
    <?php include 'include/topbar.php'; ?>
    <?php include 'include/left_sidebar.php'; ?>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Edit ARC Application</h4>
                </div>
            </div>
            <form method="POST" id="arcEditForm" enctype="multipart/form-data">
                <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application_id); ?>">
                <div class="card">
                    <h3 class="card-header bg-success text-white">Project Details</h3>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4"><strong>Project No</strong><br><?php echo htmlspecialchars($application['project_no']); ?></div>
                            <div class="col-md-4"><strong>Project Title</strong><br><?php echo htmlspecialchars($application['project_title']); ?></div>
                            <div class="col-md-4"><strong>Project Leader</strong><br><?php echo htmlspecialchars($application['project_leader_name']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <h3 class="card-header bg-success text-white">Application Details</h3>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Application Type</label>
                            <select name="application_type" id="applicationType" class="form-control">
                                <?php foreach (['Advance', 'Reconciliation', 'Claim'] as $type) { ?>
                                <option value="<?php echo $type; ?>" <?php echo $application['application_type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Item Description</th>
                                        <th>Quantity</th>
                                        <th>Amount (RM)</th>
                                        <th class="reconcile-only">Adjustment (RM)</th>
                                        <th class="proof-column">Proof of Payment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item) { ?>
                                    <?php $proof_files = arc_edit_decode_proof_files($item['proof_file'] ?? ''); ?>
                                    <tr data-row-index="<?php echo $index; ?>">
                                        <td><input type="text" name="claim_category[]" class="form-control" value="<?php echo htmlspecialchars($item['claim_category'] ?? ''); ?>"></td>
                                        <td>
                                            <input type="text" name="claim_item[]" class="form-control" value="<?php echo htmlspecialchars($item['claim_item'] ?? ''); ?>">
                                            <input type="hidden" name="old_proof_file[]" value="<?php echo htmlspecialchars($item['proof_file'] ?? ''); ?>">
                                            <input type="hidden" name="appendix_type[]" value="<?php echo htmlspecialchars($item['appendix_type'] ?? ''); ?>">
                                        </td>
                                        <td><input type="number" name="claim_quantity[]" class="form-control" min="1" value="<?php echo htmlspecialchars($item['claim_quantity'] ?? '1'); ?>"></td>
                                        <td><input type="number" name="claim_amount[]" step="0.01" class="form-control amount-input" value="<?php echo htmlspecialchars($item['claim_amount'] ?? '0.00'); ?>"></td>
                                        <td class="reconcile-only"><input type="number" name="adjustment_amount[]" step="0.01" class="form-control adjustment-input" value="<?php echo htmlspecialchars($item['adjustment_amount'] ?? '0.00'); ?>"></td>
                                        <td class="proof-column">
                                            <?php if (!empty($proof_files)) { ?>
                                            <div class="proof-links">
                                                <?php foreach ($proof_files as $file_index => $proof_file) { ?>
                                                <a href="../../consultant/project-documents/reconciliation-claim-receipts/<?php echo htmlspecialchars($proof_file); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    File <?php echo $file_index + 1; ?>
                                                </a>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <div id="proofDropzone<?php echo $index; ?>" class="proof-dropzone dropzone" data-row-index="<?php echo $index; ?>">
                                                <div class="dz-message">Drop files here or click to upload.</div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($index === 0) { ?>
                                            <button type="button" id="addItem" class="btn btn-info">Add More</button>
                                            <?php } else { ?>
                                            <button type="button" class="btn btn-danger removeItem">Remove</button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total</th>
                                        <th><input type="text" id="totalAmount" class="form-control" readonly></th>
                                        <th class="reconcile-only"><input type="text" id="totalAdjustment" class="form-control" readonly></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="m-b-30">
                    <button type="submit" class="btn btn-success btn-lg">Save Changes</button>
                    <a href="reconciliation-claim.php" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include 'include/footer.php'; ?>
    <?php include 'include/logoutmodal.php'; ?>
</div>
<script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
<script src="../assets/node_modules/popper/popper.min.js"></script>
<script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
<script src="dist/js/waves.js"></script>
<script src="dist/js/sidebarmenu.js"></script>
<script src="dist/js/custom.min.js"></script>
<script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="../assets/node_modules/dropzone-master/dist/dropzone.js"></script>
<script>
Dropzone.autoDiscover = false;
let itemRowIndex = <?php echo count($items); ?>;

function rowHtml(rowIndex) {
    return '<tr data-row-index="' + rowIndex + '">' +
        '<td><input type="text" name="claim_category[]" class="form-control"></td>' +
        '<td>' +
            '<input type="text" name="claim_item[]" class="form-control">' +
            '<input type="hidden" name="old_proof_file[]" value="">' +
            '<input type="hidden" name="appendix_type[]" value="">' +
        '</td>' +
        '<td><input type="number" name="claim_quantity[]" class="form-control" min="1" value="1"></td>' +
        '<td><input type="number" name="claim_amount[]" step="0.01" class="form-control amount-input" value="0.00"></td>' +
        '<td class="reconcile-only"><input type="number" name="adjustment_amount[]" step="0.01" class="form-control adjustment-input" value="0.00"></td>' +
        '<td class="proof-column">' +
            '<div id="proofDropzone' + rowIndex + '" class="proof-dropzone dropzone" data-row-index="' + rowIndex + '">' +
                '<div class="dz-message">Drop files here or click to upload.</div>' +
            '</div>' +
        '</td>' +
        '<td class="text-center"><button type="button" class="btn btn-danger removeItem">Remove</button></td>' +
    '</tr>';
}

function calculateTotals() {
    var total = 0, adjustment = 0;
    $('.amount-input').each(function(){ total += parseFloat($(this).val()) || 0; });
    $('.adjustment-input').each(function(){ adjustment += parseFloat($(this).val()) || 0; });
    $('#totalAmount').val(total.toFixed(2));
    $('#totalAdjustment').val(adjustment.toFixed(2));
}

function applyType() {
    var type = $('#applicationType').val();
    $('.reconcile-only').toggle(type === 'Reconciliation');
    $('.proof-column').toggle(type !== 'Advance');
}

function initProofDropzone(row) {
    var zoneElement = row.find('.proof-dropzone')[0];
    if (!zoneElement || zoneElement.dropzone) {
        return;
    }

    new Dropzone(zoneElement, {
        url: '#',
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 10,
        maxFiles: 10,
        acceptedFiles: '.pdf,.jpg,.jpeg,.png,.doc,.docx',
        addRemoveLinks: true,
        dictDefaultMessage: 'Drop files here or click to upload.',
        dictRemoveFile: 'Remove'
    });
}

function normalizeItemRows() {
    $('#itemsTable tbody tr').each(function(index) {
        $(this).attr('data-row-index', index);
        $(this).find('.proof-dropzone').attr('data-row-index', index);
    });
}

function appendProofFiles(formData) {
    formData.delete('proof_file');
    formData.delete('proof_file[]');

    if ($('#applicationType').val() === 'Advance') {
        return;
    }

    $('#itemsTable tbody tr').each(function(index) {
        var dropzoneElement = $(this).find('.proof-dropzone')[0];
        if (!dropzoneElement || !dropzoneElement.dropzone) {
            return;
        }

        dropzoneElement.dropzone.getAcceptedFiles().forEach(function(file) {
            formData.append('proof_file[' + index + '][]', file, file.name);
        });
    });
}

$(document).on('click', '#addItem', function(){
    var rowIndex = itemRowIndex++;
    $('#itemsTable tbody').append(rowHtml(rowIndex));
    initProofDropzone($('#itemsTable tbody tr').last());
    applyType();
    calculateTotals();
});

$(document).on('click', '.removeItem', function(){
    var row = $(this).closest('tr');
    var dropzoneElement = row.find('.proof-dropzone')[0];
    if (dropzoneElement && dropzoneElement.dropzone) {
        dropzoneElement.dropzone.destroy();
    }
    row.remove();
    calculateTotals();
});

$(document).on('input', '.amount-input, .adjustment-input', calculateTotals);
$('#applicationType').on('change', applyType);

$('#arcEditForm').on('submit', function(event) {
    event.preventDefault();
    normalizeItemRows();

    var formData = new FormData(this);
    appendProofFiles(formData);

    Swal.fire({
        title: 'Saving Changes...',
        text: 'Please wait while the application is updated.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Updated!', response.message, 'success').then(function() {
                    window.location.href = response.redirect || 'reconciliation-claim.php';
                });
            } else {
                Swal.fire('Failed!', response.message || 'Unable to update application.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText || error);
            Swal.fire('Error!', 'An error occurred while updating the application. Please check the console for details.', 'error');
        }
    });
});

$('#itemsTable tbody tr').each(function(){
    initProofDropzone($(this));
});
applyType();
calculateTotals();
</script>
</body>
</html>
