<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $project_id = isset($_GET['projectId']) ? $_GET['projectId'] : '';
    $invoice_id = isset($_GET['invoiceId']) ? $_GET['invoiceId'] : '';

    if (empty($project_id) || empty($invoice_id)) {
        echo '<script>
            alert("Invalid invoice or project ID.");
            window.location.href = "invoice-application.php";
        </script>';
        exit();
    }

    $project_id = mysqli_real_escape_string($db, $project_id);
    $invoice_id = mysqli_real_escape_string($db, $invoice_id);

    $id = $project_id;

    $query = "SELECT * FROM project WHERE id = '$project_id' ";  
    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<script>
            alert("Project not found.");
            window.location.href = "invoice-application.php";
        </script>';
        exit();
    }

    while($row = mysqli_fetch_array($result))  
    {
        $leader_id                      = $row['leader_id'];
        $project_leader                 = $row['project_leader'];
        $project_no                     = $row['project_no'];
        $project_title                  = $row['project_title'];
        $project_type                   = $row['project_type'];
        $project_start                  = $row['project_start'];
        $project_end                    = $row['project_end'];
        $registered_project_value       = $row['registered_project_value'];
        $adjusted_project_value         = $row['adjusted_project_value'];
        $quotation_ref_no               = $row['quotation_ref_no'];
        $appointment_letter             = $row['appointment_letter'];
        $approval_external_work         = $row['approval_external_work'];
        $quotation_doc                  = $row['quotation_doc'];
        $agreement_doc                  = $row['agreement_doc'];
        $project_proposal               = $row['project_proposal'];
        $other_doc_1                    = $row['other_doc_1'];
        $other_doc_2                    = $row['other_doc_2'];
        $client_company_name            = $row['client_company_name'];
        $client_address                 = $row['client_address'];
        $client_contact                 = $row['client_contact'];
        $client_business_type           = $row['client_business_type'];
        $client_pic                     = $row['client_pic'];
        $client_pic_email               = $row['client_pic_email'];
        $client_pic_contact             = $row['client_pic_contact'];
        $date_create                    = $row['date_create'];
        $project_status                 = $row['project_status'];
    }

    // Query data invoice
    $invoice_query = "SELECT * FROM invoices WHERE id = '$invoice_id' AND project_id = '$project_id' LIMIT 1";
    $invoice_result = mysqli_query($db, $invoice_query);

    if (!$invoice_result || mysqli_num_rows($invoice_result) == 0) {
        echo '<script>
            alert("Invoice not found.");
            window.location.href = "invoice-application.php";
        </script>';
        exit();
    }

    while ($invoice_row = mysqli_fetch_array($invoice_result)) {
        $invoice_purpose    = $invoice_row['invoice_purpose'];
        $additional_info    = $invoice_row['additional_info'];
        $tin_number         = $invoice_row['tin_number'];
        $ssm_number         = isset($invoice_row['ssm_number']) ? $invoice_row['ssm_number'] : '';
        $follow_milestone   = $invoice_row['follow_milestone'];
        $amount_type        = $invoice_row['amount_type'];
        $total_amount       = $invoice_row['total_amount'];
        $sst_amount         = $invoice_row['sst_amount'];
        $total_invoice      = $invoice_row['total_invoice'];
        $attachment         = $invoice_row['attachment'];
        $created_at         = $invoice_row['created_at'];
        $invoice_status     = $invoice_row['invoice_status'];
        $invoice_no         = $invoice_row['invoice_no'];
        $invoice_file       = $invoice_row['invoice_file'];
        $member_id          = $invoice_row['member_id'];
    }

    // Query selected invoice milestones
    $selected_milestones = [];
    $selected_milestone_query = "SELECT milestone_id FROM invoice_milestones WHERE invoice_id = '$invoice_id'";
    $selected_milestone_result = mysqli_query($db, $selected_milestone_query);
    while ($selected_row = mysqli_fetch_assoc($selected_milestone_result)) {
        $selected_milestones[] = $selected_row['milestone_id'];
    }
    
    $tracking_query = "SELECT * FROM project_tracker WHERE project_id = '$id' ORDER BY date DESC";
    $tracking_result = mysqli_query($db, $tracking_query);
    $tracking_data = [];
    while ($track_row = mysqli_fetch_array($tracking_result)) {
        $tracking_data[] = $track_row;
    }
    
    $leader_query = "SELECT email FROM uitm_staff WHERE id = '$leader_id' LIMIT 1";
    $leader_result = mysqli_query($db, $leader_query);
    if ($leader_result && mysqli_num_rows($leader_result) > 0) {
        $leader_row = mysqli_fetch_assoc($leader_result);
        $leader_email = $leader_row['email'];
    }

    $follow_milestone_normalized = strtolower(trim($follow_milestone ?? ''));
    $is_follow_milestone = in_array($follow_milestone_normalized, ['yes', 'y', '1', 'true']);

    function invoice_attachment_list($attachment_value) {
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

    $attachment_list = invoice_attachment_list($attachment);

    $current_user_id = isset($userData['id']) ? (string)$userData['id'] : '';
    $is_locked_for_edit = stripos((string)$invoice_status, 'Pending Verification') !== false
        || stripos((string)$invoice_status, 'Pending Leader Review') !== false
        || stripos((string)$invoice_status, 'Pending approval from project leader') !== false
        || stripos((string)$invoice_status, 'Approved') !== false
        || stripos((string)$invoice_status, 'Rejected') !== false
        || stripos((string)$invoice_status, 'Send to bank') !== false;
    $can_edit_invoice = ((string)$member_id === $current_user_id || (string)$leader_id === $current_user_id)
        && !$is_locked_for_edit;
    $update_button_text = stripos((string)$invoice_status, 'Returned') !== false ? 'Update & Resubmit Invoice' : 'Update Invoice';

    if (!$can_edit_invoice) {
        echo '<script>
            alert("You are not allowed to edit this invoice.");
            window.location.href = "invoice-info.php?projectId=' . urlencode($project_id) . '&invoiceId=' . urlencode($invoice_id) . '";
        </script>';
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/node_modules/dropify/dist/css/dropify.min.css">
    <link rel="stylesheet" href="../assets/node_modules/dropzone-master/dist/dropzone.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="skin-blue fixed-layout">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'include/preloader.php'; ?>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php include 'include/topbar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php include 'include/left_sidebar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Edit Invoice Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Invoice Application</a></li>
                                <li class="breadcrumb-item active">Edit Invoice Application</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Info box -->
                <!-- ============================================================== -->
                <!-- Row -->
                <div class="row">
                    <div class="col-md-12">
                        <form method="POST" id="invoiceForm" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>">
                            <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                            <input type="hidden" name="old_attachment" value="<?php echo htmlspecialchars($attachment, ENT_QUOTES, 'UTF-8'); ?>">

                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Details</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Number <span class="text-danger">*</span></label>
                                                    <input type="text" name="project_no" value="<?php echo $project_no; ?>" class="form-control" disabled required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Title <span class="text-danger">*</span></label>
                                                    <input type="text" name="project_title" value="<?php echo $project_title; ?>" class="form-control" disabled required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Leader <span class="text-danger">*</span></label>
                                                    <input type="text" name="project_leader" value="<?php echo $project_leader; ?>" class="form-control" disabled required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Leader Email Address <span class="text-danger">*</span></label>
                                                    <input type="text" name="project_leader_email" value="<?php echo $leader_email; ?>" class="form-control" disabled required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Client Address <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_address" value="<?php echo $client_address; ?>" class="form-control" disabled required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Client Phone No. <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_contact" value="<?php echo $client_contact; ?>" class="form-control" disabled required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Client Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" 
                                                           name="client_pic_email" 
                                                           value="<?php echo !empty($client_pic_email) ? $client_pic_email : 'Not Available'; ?>" 
                                                           class="form-control" 
                                                           disabled 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">SST %</label>
                                                    <input type="text" name="sst" value="8%" class="form-control" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Purpose -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Invoice Purpose</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Invoice Purpose <span class="text-danger">*</span></label>
                                                    <select class="form-control" id="invoicePurpose" name="invoice_purpose" required>
                                                        <option disabled>Select Option</option>
                                                        <option value="Professional fee" <?php echo ($invoice_purpose == 'Professional fee') ? 'selected' : ''; ?>>Professional fee</option>
                                                        <option value="Reimbursable" <?php echo ($invoice_purpose == 'Reimbursable') ? 'selected' : ''; ?>>Reimbursable</option>
                                                        <option value="Upon signing" <?php echo ($invoice_purpose == 'Upon signing') ? 'selected' : ''; ?>>Upon signing</option>
                                                        <option value="Replacement" <?php echo ($invoice_purpose == 'Replacement') ? 'selected' : ''; ?>>Replacement (Please state invoice no to cancel)</option>
                                                        <option value="Others" <?php echo ($invoice_purpose == 'Others') ? 'selected' : ''; ?>>Others (Please state)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Additional Information (if any)</label>
                                                    <input type="text" class="form-control" name="additional_info" value="<?php echo htmlspecialchars($additional_info, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter details here (optional)">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">TIN Number <small class="text-muted">(Optional)</small></label>
                                                    <input type="text" class="form-control" name="tin_number" value="<?php echo htmlspecialchars($tin_number, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter TIN Number (optional)">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">SSM Number <small class="text-muted">(Optional)</small></label>
                                                    <input type="text" class="form-control" name="ssm_number" value="<?php echo htmlspecialchars($ssm_number, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter SSM Number (optional)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Milestone -->
                            <div class="card" id="milestoneSection" style="display:none;">
                                <h3 class="card-header bg-info text-white">Project Milestones</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Title</th>
                                                    <th>Description</th>
                                                    <th>Value (RM)</th>
                                                    <th>Date Start</th>
                                                    <th>Date End</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $timeline_query = "SELECT * FROM project_timeline WHERE project_id = '$id' ORDER BY id ASC";
                                                    $timeline_result = mysqli_query($db, $timeline_query);
                                                    while ($milestone = mysqli_fetch_assoc($timeline_result)) {
                                                        $is_checked = in_array($milestone['id'], $selected_milestones) ? 'checked' : '';
                                                ?>
                                                    <tr>
                                                        <td><input type="checkbox" name="milestones[]" value="<?php echo $milestone['id']; ?>" <?php echo $is_checked; ?>></td>
                                                        <td><?php echo $milestone['title']; ?></td>
                                                        <td><?php echo $milestone['description']; ?></td>
                                                        <td><input type="text" class="form-control milestone-value" name="milestone_values[]" data-value="<?php echo $milestone['value']; ?>" readonly value="<?php echo $milestone['value']; ?>"></td>
                                                        <td><?php echo date("j F Y", strtotime($milestone['date_start'])); ?></td>
                                                        <td><?php echo date("j F Y", strtotime($milestone['date_end'])); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- invoice details amount -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Invoice Details</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <!-- Follow milestone? -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Invoice amount will follow milestone?</label>
                                                    <select class="form-control" name="follow_milestone" id="follow_milestone" required>
                                                        <option disabled>Select Option</option>
                                                        <option value="Yes" <?php echo $is_follow_milestone ? 'selected' : ''; ?>>Yes</option>
                                                        <option value="No" <?php echo !$is_follow_milestone ? 'selected' : ''; ?>>No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Amount Type (dropdown baharu) -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Amount Type (SST)</label>
                                                    <select id="amountType" class="form-control" name="amount_type" required>
                                                        <option value="Basic amount + SST" <?php echo ($amount_type == 'Basic amount + SST') ? 'selected' : ''; ?>>Basic amount + SST</option>
                                                        <option value="Amount inclusive SST" <?php echo ($amount_type == 'Amount inclusive SST') ? 'selected' : ''; ?>>Amount inclusive SST</option>
                                                        <option value="SST Exempted" <?php echo ($amount_type == 'SST Exempted') ? 'selected' : ''; ?>>SST Exempted</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Manual amount (muncul bila NO) -->
                                        <div class="row" id="manualAmountDiv" style="display:none;">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Enter Manual Invoice Amount (RM)</label>
                                                    <input type="number" class="form-control" name="manual_invoice_amount"
                                                           id="manual_invoice_amount" step="0.01" value="<?php echo !$is_follow_milestone ? $total_amount : ''; ?>" placeholder="e.g. 5000.00">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Output sentiasa nampak -->
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Total Amount to Claim (RM)</label>
                                                    <input type="text" id="total-amount" name="total_amount" class="form-control" readonly value="<?php echo $total_amount; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>SST Amount (8%)</label>
                                                    <input type="text" id="sst-amount" name="sst_amount" class="form-control" readonly value="<?php echo $sst_amount; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Total Invoice (RM)</label>
                                                    <input type="text" id="total-invoice" name="total_invoice" class="form-control" readonly value="<?php echo $total_invoice; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Attachment (PO/Approval for Invoice)</label>
                                            <div id="existingInvoiceAttachments" class="m-b-10">
                                                <?php if (!empty($attachment_list)) { ?>
                                                    <?php foreach ($attachment_list as $index => $attachment_file) { ?>
                                                        <div class="d-flex align-items-center m-b-5 existing-attachment" data-file="<?php echo htmlspecialchars($attachment_file, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="retained_attachments[]" value="<?php echo htmlspecialchars($attachment_file, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <a href="https://utv.domei.io/consultant/project-documents/invoice/<?php echo urlencode($attachment_file); ?>"
                                                               class="btn btn-sm btn-info m-r-5"
                                                               target="_blank">
                                                                View Attachment <?php echo $index + 1; ?>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger remove-existing-attachment">
                                                                Remove
                                                            </button>
                                                        </div>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="text-muted">No attachment uploaded.</span>
                                                <?php } ?>
                                            </div>
                                            <div id="invoiceAttachmentDropzone" class="dropzone">
                                                <div class="dz-message">
                                                    Drop file here or click to upload.
                                                </div>
                                                <div class="fallback">
                                                    <input name="invoice_attachment[]" type="file" multiple />
                                                </div>
                                            </div>
                                            <small class="text-muted">Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX. Remove existing files above or add new files here.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!--Declaration-->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Consultant Declaration</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>I hereby declare that:</p>
                                                <ul>
                                                    <li>I have reviewed and edited this invoice application for resubmission.</li>
                                                    <li>The invoice details updated above are based on the latest correct information.</li>
                                                    <li>All the information given above is true and correct.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="button" id="updateInvoice" class="btn btn-lg btn-info">
                                        <?php echo $update_button_text; ?>
                                    </button>
                                    <a href="invoice-info.php?projectId=<?php echo urlencode($project_id); ?>&invoiceId=<?php echo urlencode($invoice_id); ?>" class="btn btn-lg btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <?php include 'include/footer.php'; ?>
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Logout Modal -->
        <!-- ============================================================== -->
        <?php include 'include/logoutmodal.php'; ?>
        <!-- ============================================================== -->
        <!-- End Logout Modal -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap popper Core JavaScript -->
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <script src="../assets/node_modules/dropzone-master/dist/dropzone.js"></script>
    <script>
    $(document).ready(function() {
        // Basic
        $('.dropify').dropify();

        // Translated
        $('.dropify-fr').dropify({
            messages: {
                default: 'Glissez-déposez un fichier ici ou cliquez',
                replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                remove: 'Supprimer',
                error: 'Désolé, le fichier trop volumineux'
            }
        });

        // Used events
        var drEvent = $('#input-file-events').dropify();

        drEvent.on('dropify.beforeClear', function(event, element) {
            return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
        });

        drEvent.on('dropify.afterClear', function(event, element) {
            alert('File deleted');
        });

        drEvent.on('dropify.errors', function(event, element) {
            console.log('Has Errors');
        });

        var drDestroy = $('#input-file-to-destroy').dropify();
        drDestroy = drDestroy.data('dropify')
        $('#toggleDropify').on('click', function(e) {
            e.preventDefault();
            if (drDestroy.isDropified()) {
                drDestroy.destroy();
            } else {
                drDestroy.init();
            }
        })
    });
    </script>
    <script>
        $(document).on('change', '.custom-file-input', function () {
            const files = this.files ? Array.from(this.files).map(file => file.name) : [];
            const fileName = files.length ? files.join(', ') : $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choose File(s)');
        });
    </script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        $(function () {
            // Switchery
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            $('.js-switch').each(function () {
                new Switchery($(this)[0], $(this).data());
            });
            // For select 2
            $(".select2").select2();
            $('.selectpicker').selectpicker();
            //Bootstrap-TouchSpin
            $(".vertical-spin").TouchSpin({
                verticalbuttons: true
            });
            var vspinTrue = $(".vertical-spin").TouchSpin({
                verticalbuttons: true
            });
            if (vspinTrue) {
                $('.vertical-spin').prev('.bootstrap-touchspin-prefix').remove();
            }
            $("input[name='tch1']").TouchSpin({
                min: 0,
                max: 100,
                step: 0.1,
                decimals: 2,
                boostat: 5,
                maxboostedstep: 10,
                postfix: '%'
            });
            $("input[name='tch2']").TouchSpin({
                min: -1000000000,
                max: 1000000000,
                stepinterval: 50,
                maxboostedstep: 10000000,
                prefix: '$'
            });
            $("input[name='tch3']").TouchSpin();
            $("input[name='tch3_22']").TouchSpin({
                initval: 40
            });
            $("input[name='tch5']").TouchSpin({
                prefix: "pre",
                postfix: "post"
            });
            // For multiselect
            $('#pre-selected-options').multiSelect();
            $('#optgroup').multiSelect({
                selectableOptgroup: true
            });
            $('#public-methods').multiSelect();
            $('#select-all').click(function () {
                $('#public-methods').multiSelect('select_all');
                return false;
            });
            $('#deselect-all').click(function () {
                $('#public-methods').multiSelect('deselect_all');
                return false;
            });
            $('#refresh').on('click', function () {
                $('#public-methods').multiSelect('refresh');
                return false;
            });
            $('#add-option').on('click', function () {
                $('#public-methods').multiSelect('addOption', {
                    value: 42,
                    text: 'test 42',
                    index: 0
                });
                return false;
            });
            $(".ajax").select2({
                ajax: {
                    url: "https://api.github.com/search/repositories",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                }, // let our custom formatter work
                minimumInputLength: 1,
                //templateResult: formatRepo, // omitted for brevity, see the source of this page
                //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
            });
        });
    </script>
    <script>
        function parseMoney(value) {
            return parseFloat(String(value || '0').replace(/,/g, '')) || 0;
        }

        function calculateTotals() {
            // IMPORTANT:
            // Dropdown value is "Yes" / "No", so comparison must match the actual value.
            // Before this it compared with lowercase "yes", causing milestone amount not to calculate.
            let useMilestone = $('#follow_milestone').val() === 'Yes';
            let totalAmt = 0;
        
            if (useMilestone) {
                $('.milestone-value').each(function () {
                    const isChecked = $(this).closest('tr').find('input[type="checkbox"]').is(':checked');

                    if (isChecked) {
                        // Use current input value first, fallback to data-value.
                        // This keeps amount correct if value/data-value formatting changes.
                        totalAmt += parseMoney($(this).val() || $(this).data('value'));
                    }
                });
            } else {
                totalAmt = parseMoney($('#manual_invoice_amount').val());
            }
        
            const type = $('#amountType').val();   // ← dropdown baharu
            let basic = totalAmt, sst = 0, invoice = 0;
        
            if (type === 'Basic amount + SST') {
                sst = basic * 0.08;
                invoice = basic + sst;
            } else if (type === 'Amount inclusive SST') {
                basic = totalAmt * 100 / 108;
                sst   = totalAmt - basic;
                invoice = totalAmt;
            } else { // SST Exempted
                sst = 0;
                invoice = basic;
            }
        
            $('#total-amount').val(basic.toFixed(2));
            $('#sst-amount').val(sst.toFixed(2));
            $('#total-invoice').val(invoice.toFixed(2));
        }
        
        $(document).ready(function () {
            // Mula-mula: semua input manual & milestone disembunyikan
            $('#manualAmountDiv').hide();
            $('#milestoneSection').hide();
        
            // Tukar YES/NO → toggle section
            $('#follow_milestone').on('change', function () {
                const isMilestone = $(this).val() === 'Yes';
                $('#milestoneSection').toggle(isMilestone);
                $('#manualAmountDiv').toggle(!isMilestone);

                if (isMilestone) {
                    $('#manual_invoice_amount').val('');
                } else {
                    $('input[name="milestones[]"]').prop('checked', false);
                }

                calculateTotals();
            });
        
            // Trigger kiraan bila manual amount, amount type, atau milestone change
            $('#manual_invoice_amount, #amountType').on('input change', calculateTotals);
            $('input[name="milestones[]"]').on('change', calculateTotals);
        
            // Optional: auto-trigger kalau nak prefill
            $('#follow_milestone').trigger('change');
        });
    </script>
    <script>
        Dropzone.autoDiscover = false;
        let invoiceAttachmentDropzone = null;

        $(function() {
            invoiceAttachmentDropzone = new Dropzone("#invoiceAttachmentDropzone", {
                url: "#",
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 10,
                maxFiles: 10,
                acceptedFiles: ".pdf,.jpg,.jpeg,.png,.doc,.docx",
                addRemoveLinks: true,
                dictDefaultMessage: "Drop file here or click to upload."
            });

            $(document).on('click', '.remove-existing-attachment', function() {
                $(this).closest('.existing-attachment').remove();

                if ($('#existingInvoiceAttachments .existing-attachment').length === 0) {
                    $('#existingInvoiceAttachments').html('<span class="text-muted">No attachment uploaded.</span>');
                }
            });
        });

        function setInvoiceButtonsLoading(activeButton, loadingText) {
            $('#updateInvoice').prop('disabled', true);
            activeButton.html(loadingText);
        }

        function resetInvoiceButtons(activeButton, originalText) {
            activeButton.html(originalText);
            $('#updateInvoice').prop('disabled', false);
        }

        function validateInvoiceForm() {
            const form = $('#invoiceForm')[0];

            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const followMilestone = $('#follow_milestone').val();

            if (followMilestone === 'Yes' && $('input[name="milestones[]"]:checked').length === 0) {
                Swal.fire({
                    title: 'Milestone Required',
                    text: 'Please select at least one milestone before updating.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            if (followMilestone === 'No') {
                const manualAmount = parseFloat($('#manual_invoice_amount').val()) || 0;

                if (manualAmount <= 0) {
                    Swal.fire({
                        title: 'Invalid Amount',
                        text: 'Please enter a valid manual invoice amount.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
            }

            const retainedAttachmentCount = $('input[name="retained_attachments[]"]').length;
            const newAttachmentCount = invoiceAttachmentDropzone ? invoiceAttachmentDropzone.getAcceptedFiles().length : 0;

            if (retainedAttachmentCount === 0 && newAttachmentCount === 0) {
                Swal.fire({
                    title: 'Attachment Required',
                    text: 'Please keep at least one existing attachment or upload a new file.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            calculateTotals();
            return true;
        }

        function submitInvoiceApplication(config) {
            const activeButton = $(config.buttonSelector);
            const originalText = activeButton.html();

            if (activeButton.prop('disabled')) {
                return;
            }

            if (!validateInvoiceForm()) {
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "This will update the invoice application details.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons

                if (result.isConfirmed || result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");

                    // Ambil borang menggunakan ID borang
                    const form = $('#invoiceForm')[0]; // Gantikan #invoiceForm dengan ID borang anda
                    const formData = new FormData(form);
                    const invoiceFiles = invoiceAttachmentDropzone ? invoiceAttachmentDropzone.getAcceptedFiles() : [];

                    formData.delete('invoice_attachment');
                    formData.delete('invoice_attachment[]');

                    invoiceFiles.forEach((file) => {
                        formData.append('invoice_attachment[]', file, file.name);
                    });

                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }

                    setInvoiceButtonsLoading(activeButton, config.buttonLoadingText);

                    Swal.fire({
                        title: config.loadingTitle,
                        text: config.loadingText,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: config.url,
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);

                            if (response.success) {
                                Swal.fire({
                                    title: 'Updated!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.replace('invoice-info.php?projectId=' + response.project_id + '&invoiceId=' + response.invoice_id);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Failed!',
                                    text: response.message || 'Unable to update invoice application.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    resetInvoiceButtons(activeButton, originalText);
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred during update. Please check the console for details.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                resetInvoiceButtons(activeButton, originalText);
                            });
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Invoice update has been cancelled.',
                        'info'
                    );
                }
            });
        }

        $(document).on('click', '#updateInvoice', function () {
            submitInvoiceApplication({
                buttonSelector: '#updateInvoice',
                buttonLoadingText: 'Updating...',
                loadingTitle: 'Updating Invoice...',
                loadingText: 'Please wait while the invoice application is being updated.',
                url: 'update_invoice.php'
            });
        });
    </script>
</body>

</html>
