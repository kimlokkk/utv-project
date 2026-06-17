<?php
session_start();
include 'auth_check.php';
include '../../db_connect/db_connect.php';
include '../../function/function.php';

$userData = $_SESSION['user_data'];
$application_id = mysqli_real_escape_string($db, $_GET['applicationId'] ?? $_POST['application_id'] ?? '');

if ($application_id === '') {
    echo '<script>alert("Invalid application ID."); window.location.href="reconciliation-claim.php";</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    mysqli_begin_transaction($db);
    try {
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
            $proof_file = mysqli_real_escape_string($db, $old_proof_files[$i] ?? '');
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
        echo '<script>alert("ARC application updated successfully."); window.location.href="reconciliation-claim.php";</script>';
        exit;
    } catch (Exception $e) {
        mysqli_rollback($db);
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
            <form method="POST" id="arcEditForm">
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
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item) { ?>
                                    <tr>
                                        <td><input type="text" name="claim_category[]" class="form-control" value="<?php echo htmlspecialchars($item['claim_category'] ?? ''); ?>"></td>
                                        <td>
                                            <input type="text" name="claim_item[]" class="form-control" value="<?php echo htmlspecialchars($item['claim_item'] ?? ''); ?>">
                                            <input type="hidden" name="old_proof_file[]" value="<?php echo htmlspecialchars($item['proof_file'] ?? ''); ?>">
                                            <input type="hidden" name="appendix_type[]" value="<?php echo htmlspecialchars($item['appendix_type'] ?? ''); ?>">
                                        </td>
                                        <td><input type="number" name="claim_quantity[]" class="form-control" min="1" value="<?php echo htmlspecialchars($item['claim_quantity'] ?? '1'); ?>"></td>
                                        <td><input type="number" name="claim_amount[]" step="0.01" class="form-control amount-input" value="<?php echo htmlspecialchars($item['claim_amount'] ?? '0.00'); ?>"></td>
                                        <td class="reconcile-only"><input type="number" name="adjustment_amount[]" step="0.01" class="form-control adjustment-input" value="<?php echo htmlspecialchars($item['adjustment_amount'] ?? '0.00'); ?>"></td>
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
                                        <th></th>
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
<script>
function rowHtml() {
    return '<tr>' +
        '<td><input type="text" name="claim_category[]" class="form-control"></td>' +
        '<td><input type="text" name="claim_item[]" class="form-control"></td>' +
        '<td><input type="number" name="claim_quantity[]" class="form-control" min="1" value="1"></td>' +
        '<td><input type="number" name="claim_amount[]" step="0.01" class="form-control amount-input" value="0.00"></td>' +
        '<td class="reconcile-only"><input type="number" name="adjustment_amount[]" step="0.01" class="form-control adjustment-input" value="0.00"></td>' +
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
    $('.reconcile-only').toggle($('#applicationType').val() === 'Reconciliation');
}
$(document).on('click', '#addItem', function(){ $('#itemsTable tbody').append(rowHtml()); applyType(); calculateTotals(); });
$(document).on('click', '.removeItem', function(){ $(this).closest('tr').remove(); calculateTotals(); });
$(document).on('input', '.amount-input, .adjustment-input', calculateTotals);
$('#applicationType').on('change', applyType);
applyType();
calculateTotals();
</script>
</body>
</html>
