<?php
session_start();
include 'auth_check.php';
include '../../db_connect/db_connect.php';
include '../../function/function.php';

$userData = $_SESSION['user_data'];

if (empty($_GET['applicationId'])) {
    echo '<script>alert("Invalid application."); window.location.href = "project-funding.php";</script>';
    exit;
}

$application_id = mysqli_real_escape_string($db, $_GET['applicationId']);
$query = "
    SELECT pfa.*, p.project_no, p.project_title, p.project_leader, p.client_company_name
    FROM project_funding_assistance_applications pfa
    INNER JOIN project p ON p.id = pfa.project_id
    WHERE pfa.id = '$application_id'
      AND (pfa.status LIKE '%Returned%' OR pfa.status LIKE '%Rejected%')
      AND pfa.return_to = 'Level 3'
    LIMIT 1
";
$result = mysqli_query($db, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<script>alert("This application cannot be edited by Level 3."); window.location.href = "project-funding.php";</script>';
    exit;
}

$application = mysqli_fetch_assoc($result);
$items_result = mysqli_query($db, "SELECT category, item, quantity, amount FROM project_funding_assistance_items WHERE application_id = '$application_id' ORDER BY id ASC");
$categories = ['Printing', 'Project Materials/Equipment', 'Token', 'Subscription', 'Others'];

function pfa_category_options($categories, $selected) {
    $html = '<option value="" disabled>Select</option>';
    foreach ($categories as $category) {
        $is_selected = $category === $selected ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($category) . '"' . $is_selected . '>' . htmlspecialchars($category) . '</option>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
</head>
<body class="skin-blue fixed-layout">
    <?php include 'include/preloader.php'; ?>
    <div id="main-wrapper">
        <?php include 'include/topbar.php'; ?>
        <?php include 'include/left_sidebar.php'; ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center"><h4 class="text-themecolor">Edit Project Funding Assistance</h4></div>
                    <div class="col-md-7 align-self-center text-right">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="project-funding.php">Project Funding Assistance</a></li>
                            <li class="breadcrumb-item active">Edit Application</li>
                        </ol>
                    </div>
                </div>

                <form id="projectFundingEditForm">
                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id']); ?>">
                    <div class="card">
                        <h3 class="card-header bg-info text-white">Project Details</h3>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                This application was returned to Level 3. Update the details and send it back for Level 2 approval.
                                <?php if (!empty($application['return_remark'])) { ?>
                                    <br><strong>Remark:</strong> <?php echo nl2br(htmlspecialchars($application['return_remark'])); ?>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label><strong>Project Number</strong></label><input type="text" class="form-control" value="<?php echo htmlspecialchars($application['project_no']); ?>" readonly></div></div>
                                <div class="col-md-6"><div class="form-group"><label><strong>Project Title</strong></label><input type="text" class="form-control" value="<?php echo htmlspecialchars($application['project_title']); ?>" readonly></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label><strong>Project Leader</strong></label><input type="text" class="form-control" value="<?php echo htmlspecialchars($application['project_leader']); ?>" readonly></div></div>
                                <div class="col-md-6"><div class="form-group"><label><strong>Client Name</strong></label><input type="text" class="form-control" value="<?php echo htmlspecialchars($application['client_company_name']); ?>" readonly></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <h3 class="card-header bg-info text-white">Project Funding Assistance</h3>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label><strong>Expected To Receive Payment From Client Date</strong></label><input type="date" name="expected_payment_date" class="form-control" value="<?php echo htmlspecialchars($application['expected_payment_date']); ?>" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label><strong>Expected To Receive Payment From Client Amount (RM)</strong></label><input type="number" name="expected_payment_amount" class="form-control" value="<?php echo htmlspecialchars($application['expected_payment_amount']); ?>" required step="0.01" min="0"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label><strong>PFA Application No.</strong></label><input type="number" name="pfa_number" class="form-control" value="<?php echo htmlspecialchars($application['pfa_number']); ?>" required min="1"></div></div>
                                <div class="col-md-6"><div class="form-group"><label><strong>Total Previous PFA Applied (RM)</strong></label><input type="number" name="total_previous_pfa_applied" class="form-control" value="<?php echo htmlspecialchars($application['total_previous_pfa_applied']); ?>" required step="0.01" min="0" readonly></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <h3 class="card-header bg-info text-white">Item to Apply</h3>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light"><tr><th>Category</th><th>Item</th><th>Quantity</th><th>Amount (RM)</th><th class="text-center">Action</th></tr></thead>
                                    <tbody id="fundingBody">
                                        <?php while ($item = mysqli_fetch_assoc($items_result)) { ?>
                                            <tr>
                                                <td><select name="category[]" class="form-control" required><?php echo pfa_category_options($categories, $item['category']); ?></select></td>
                                                <td><input type="text" name="item[]" class="form-control" value="<?php echo htmlspecialchars($item['item']); ?>" required></td>
                                                <td><input type="number" name="quantity[]" class="form-control" value="<?php echo htmlspecialchars($item['quantity']); ?>" required min="1"></td>
                                                <td><input type="number" name="amount[]" class="form-control" value="<?php echo htmlspecialchars($item['amount']); ?>" required step="0.01" min="0"></td>
                                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-success btn-sm" id="addFundingRow">+ Add Row</button>
                            </div>
                        </div>
                    </div>

                    <div class="row m-t-30 m-b-30">
                        <div class="col-md-12">
                            <button type="button" id="submitProjectFundingEdit" class="btn btn-lg btn-success">Update & Send for Approval</button>
                            <a href="project-funding.php" class="btn btn-lg btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php include 'include/footer.php'; ?>
        <?php include 'include/logoutmodal.php'; ?>
    </div>

    <script src="../../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <script src="../../assets/node_modules/popper/popper.min.js"></script>
    <script src="../../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="dist/js/waves.js"></script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.min.js"></script>
    <script src="../../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script>
        const categoryOptions = `<?php echo pfa_category_options($categories, ''); ?>`;
        function getTotalFundingAmount() {
            let total = 0;
            document.querySelectorAll('input[name="amount[]"]').forEach(input => total += parseFloat(input.value) || 0);
            return total;
        }
        function validateFundingAmountLimit() {
            const expectedAmount = parseFloat(document.querySelector('input[name="expected_payment_amount"]').value) || 0;
            const totalFundingAmount = getTotalFundingAmount();
            if (totalFundingAmount > expectedAmount) {
                Swal.fire('Invalid Amount', 'Total item amount (RM ' + totalFundingAmount.toFixed(2) + ') must not be more than the expected payment from client (RM ' + expectedAmount.toFixed(2) + ').', 'error');
                return false;
            }
            return true;
        }
        $('#addFundingRow').on('click', function() {
            $('#fundingBody').append(`<tr><td><select name="category[]" class="form-control" required>${categoryOptions}</select></td><td><input type="text" name="item[]" class="form-control" required></td><td><input type="number" name="quantity[]" class="form-control" required min="1"></td><td><input type="number" name="amount[]" class="form-control" required step="0.01" min="0"></td><td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td></tr>`);
        });
        $(document).on('click', '.removeRow', function() {
            if ($('#fundingBody tr').length > 1) $(this).closest('tr').remove();
        });
        $('#submitProjectFundingEdit').on('click', function() {
            const form = $('#projectFundingEditForm')[0];
            if (!form.checkValidity()) { form.reportValidity(); return; }
            if (!validateFundingAmountLimit()) return;
            Swal.fire({ title: 'Update Application?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, update' }).then((result) => {
                if (result.isConfirmed || result.value) {
                    $.ajax({
                        url: 'project-funding-edit-submit.php',
                        method: 'POST',
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function() {
                            Swal.fire({ title: 'Updating...', allowOutsideClick: false, allowEscapeKey: false, onOpen: () => Swal.showLoading() });
                        },
                        success: function(response) {
                            if (response && response.success) Swal.fire('Updated!', response.message, 'success').then(() => window.location.href = 'project-funding.php');
                            else Swal.fire('Failed!', response && response.message ? response.message : 'Unable to update application.', 'error');
                        },
                        error: function() { Swal.fire('Error!', 'An error occurred during submission.', 'error'); }
                    });
                }
            });
        });
    </script>
</body>
</html>
