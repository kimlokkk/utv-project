<?php
    session_start(); // Start the session
    include '../../db_connect/db_connect.php';
    include 'auth_check.php';
?>
<?php
    $project_id = $_GET['projectId'];
    $invoice_id = $_GET['invoiceId'];
    
    // Query data projek utama
    $query = "SELECT * FROM project WHERE id = '$project_id'";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        $leader_id = $row['leader_id'];
        $project_leader = $row['project_leader'];
        $project_no = $row['project_no'];
        $project_title = $row['project_title'];
        $project_type = $row['project_type'];
        $project_start = $row['project_start'];
        $project_end = $row['project_end'];
        $registered_project_value = $row['registered_project_value'];
        $adjusted_project_value = $row['adjusted_project_value'];
        $quotation_ref_no = $row['quotation_ref_no'];
        $appointment_letter = $row['appointment_letter'];
        $approval_external_work = $row['approval_external_work'];
        $quotation_doc = $row['quotation_doc'];
        $agreement_doc = $row['agreement_doc'];
        $project_proposal = $row['project_proposal'];
        $other_doc_1 = $row['other_doc_1'];
        $other_doc_2 = $row['other_doc_2'];
        $client_company_name = $row['client_company_name'];
        $client_address = $row['client_address'];
        $client_contact = $row['client_contact'];
        $client_business_type = $row['client_business_type'];
        $client_pic = $row['client_pic'];
        $client_pic_email = $row['client_pic_email'];
        $client_pic_contact = $row['client_pic_contact'];
        $date_create = $row['date_create'];
        $project_status = $row['project_status'];
    }
    
    // Query data invoices
    $query_invoices = "SELECT * FROM invoices WHERE id = '$invoice_id' ORDER BY id DESC";
    $result_invoices = mysqli_query($db, $query_invoices);
    while ($row = mysqli_fetch_array($result_invoices)) {
        $invoice_purpose = $row['invoice_purpose']; // Simpan semua invois dalam array
        $additional_info = $row['additional_info']; // Simpan semua invois dalam array
        $tin_number = $row['tin_number'];
        $follow_milestone = $row['follow_milestone'];
        $amount_type = $row['amount_type'];
        $total_amount = $row['total_amount']; // Simpan semua invois dalam array
        $sst_amount = $row['sst_amount']; // Simpan semua invois dalam array
        $total_invoice = $row['total_invoice']; // Simpan semua invois dalam array
        $attachment = $row['attachment']; // Simpan semua invois dalam array
        $created_at = $row['created_at']; // Simpan semua invois dalam array
        $invoice_status = $row['invoice_status'];
        $invoice_no = $row['invoice_no'];
        $invoice_file = $row['invoice_file'];
        $invoice_date = $row['invoice_date'] ?? '';
        $due_date = $row['due_date'] ?? '';
        $finance_updated_by = $row['finance_updated_by'] ?? '';
        $finance_updated_at = $row['finance_updated_at'] ?? '';
        $finance_remark = $row['finance_remark'] ?? '';
        $payment_status = $row['payment_status'] ?? 'Unpaid';
        $paid_amount = $row['paid_amount'] ?? '0.00';
        $outstanding_amount = $row['outstanding_amount'] ?? $total_invoice;
        $sent_to_client_at = $row['sent_to_client_at'] ?? '';
        $sent_to_client_by = $row['sent_to_client_by'] ?? '';
    }
    
    // Query data milestones untuk invois
    $query_invoice_milestones = "
        SELECT im.*, pt.title, pt.description, pt.value, pt.date_start, pt.date_end
        FROM invoice_milestones im
        LEFT JOIN project_timeline pt ON im.milestone_id = pt.id
        WHERE im.invoice_id = '$invoice_id'
    ";
    $result_invoice_milestones = mysqli_query($db, $query_invoice_milestones);
    $invoice_milestones = [];
    while ($row = mysqli_fetch_array($result_invoice_milestones)) {
        $invoice_milestones[] = $row; // Simpan data milestone invois dalam array
    }
    
    // Query data project_timeline
    $query_timeline = "SELECT * FROM project_timeline WHERE project_id = '$project_id'";
    $result_timeline = mysqli_query($db, $query_timeline);
    $project_timelines = [];
    while ($row = mysqli_fetch_array($result_timeline)) {
        $project_timelines[] = $row; // Simpan semua data timeline dalam array
    }
    
    $leader_query = "SELECT email FROM uitm_staff WHERE id = '$leader_id' LIMIT 1";
    $leader_result = mysqli_query($db, $leader_query);
    if ($leader_result && mysqli_num_rows($leader_result) > 0) {
        $leader_row = mysqli_fetch_assoc($leader_result);
        $leader_email = $leader_row['email'];
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
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="skin-green fixed-layout">
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
            <!-- Container fluid -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Invoice Application Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Invoice Application</a></li>
                                <li class="breadcrumb-item active">Invoice Application Info</li>
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
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Details</h3>
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
                        <!-- Invoice Purpose & Attachment -->
                        <div class="row">
                            <!-- Invoice Purpose Card -->
                            <div class="col-md-12">
                                <div class="card">
                                    <h3 class="card-header bg-success text-white">Invoice Info</h3>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Invoice Status</strong>
                                                    <h5><?php echo $invoice_status; ?></h5>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Invoice No</strong>
                                                    <h5><?php echo !empty($invoice_no) ? $invoice_no : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Invoice Date</strong>
                                                    <h5><?php echo !empty($invoice_date) ? date("j F Y", strtotime($invoice_date)) : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Due Date</strong>
                                                    <h5><?php echo !empty($due_date) ? date("j F Y", strtotime($due_date)) : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Invoice Purpose</strong>
                                                    <h5><?php echo $invoice_purpose; ?></h5>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Additional Info</strong>
                                                    <h5><?php echo !empty($additional_info) ? $additional_info : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>TIN Number</strong>
                                                    <h5><?php echo !empty($tin_number) ? $tin_number : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Payment Status</strong>
                                                    <h5><?php echo !empty($payment_status) ? $payment_status : 'Unpaid'; ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Finance Updated By</strong>
                                                    <h5><?php echo !empty($finance_updated_by) ? $finance_updated_by : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Finance Updated At</strong>
                                                    <h5><?php echo !empty($finance_updated_at) ? date("j F Y, h:i A", strtotime($finance_updated_at)) : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <strong>Finance Remark</strong>
                                                    <h5><?php echo !empty($finance_remark) ? nl2br(htmlspecialchars($finance_remark)) : 'Not Available'; ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Attachment Card -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <h3 class="card-header bg-success text-white">Invoice Documents</h3>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>PO/Approval for invoice</strong>
                                                    <div class="m-t-10">
                                                        <?php if (!empty($attachment)) { ?>
                                                            <a href="https://utv.domei.io/consultant/project-documents/invoice/<?php echo urlencode($attachment); ?>" 
                                                               class="btn-sm btn-info view-attachment" 
                                                               target="_blank">
                                                                View Attachment
                                                            </a>
                                                        <?php } else { ?>
                                                            <button class="btn-sm btn-secondary" disabled>No Attachment Available</button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <strong>Invoice File</strong>
                                                    <div class="m-t-10">
                                                        <?php if (!empty($invoice_file)) { ?>
                                                            <a href="https://utv.domei.io/finance-invoice-documents/<?php echo urlencode($invoice_file); ?>" 
                                                               class="btn-sm btn-info" 
                                                               target="_blank">
                                                                View Attachment
                                                            </a>
                                                        <?php } else { ?>
                                                            <button class="btn-sm btn-secondary" disabled>No Attachment Available</button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Project Milestones -->
                        <?php
                        $follow_milestone_normalized = strtolower(trim($follow_milestone ?? ''));
                        if (in_array($follow_milestone_normalized, ['yes', 'y', '1', 'true'])) {
                        ?>
                        <!-- Project Milestones -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Milestones</h3>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Value (RM)</th>
                                            <th>Date Start</th>
                                            <th>Date End</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($invoice_milestones)) { ?>
                                            <?php foreach ($invoice_milestones as $milestone) { ?>
                                                <tr>
                                                    <td><?php echo !empty($milestone['title']) ? $milestone['title'] : 'Milestone not found'; ?></td>
                                                    <td><?php echo !empty($milestone['description']) ? $milestone['description'] : 'Not Available'; ?></td>
                                                    <td><?php echo !empty($milestone['value']) ? $milestone['value'] : '0.00'; ?></td>
                                                    <td><?php echo !empty($milestone['date_start']) ? date("j F Y", strtotime($milestone['date_start'])) : 'Not Available'; ?></td>
                                                    <td><?php echo !empty($milestone['date_end']) ? date("j F Y", strtotime($milestone['date_end'])) : 'Not Available'; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No milestone data found for this invoice.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php } ?>
                        <!-- Invoice Amount -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Invoice Amount</h3>
                            <div class="card-body"><div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Invoice amount will follow milestone?</strong>
                                            <h5><?php echo $follow_milestone; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Amount Type (SST)</strong>
                                            <h5><?php echo $amount_type; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Project Amount (RM)</strong>
                                            <h5><?php echo $total_amount; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>SST Amount (8%)</strong>
                                            <h5><?php echo $sst_amount; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Total Invoice (RM)</strong>
                                            <h5><?php echo $total_invoice; ?></h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Paid Amount (RM)</strong>
                                            <h5><?php echo number_format((float)$paid_amount, 2); ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Outstanding Amount (RM)</strong>
                                            <h5><?php echo number_format((float)$outstanding_amount, 2); ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Submit Buttons -->
                <?php
                    $canSendInvoiceEmail = !empty($invoice_no) && !empty($invoice_file) && !empty($client_pic_email) && !empty($leader_email);
                    $emailAlreadySent = !empty($sent_to_client_at);
                ?>
                <div class="row m-t-30 m-b-30">
                    <div class="col-md-12">
                        <button type="button" 
                                id="sendInvoiceEmail" 
                                class="btn btn-lg btn-primary"
                                data-invoice-id="<?php echo $invoice_id; ?>"
                                data-project-id="<?php echo $project_id; ?>"
                                data-staff-id="<?php echo $userData['staff_id']; ?>"
                                <?php echo !$canSendInvoiceEmail ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            <?php echo $emailAlreadySent ? 'Resend Invoice Email' : 'Send Invoice Email'; ?>
                        </button>

                        <a href="invoice-application.php" class="btn btn-lg btn-secondary">
                            Back
                        </a>

                        <?php if (!$canSendInvoiceEmail) { ?>
                            <div class="m-t-10">
                                <small class="text-danger">
                                    Invoice email can only be sent when invoice number, invoice file, client email, and project leader email are available.
                                </small>
                            </div>
                        <?php } ?>

                        <?php if ($emailAlreadySent) { ?>
                            <div class="m-t-10">
                                <small class="text-muted">
                                    Last sent on <?php echo date("j F Y, h:i A", strtotime($sent_to_client_at)); ?> by <?php echo $sent_to_client_by; ?>.
                                </small>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper -->
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
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
        $(document).on('click', '.view-attachment', function(event) {
            event.preventDefault();  // Halang dari trigger event lain
            event.stopPropagation(); // Halang dari trigger event Swal
            window.open($(this).attr('href'), '_blank');
        });
    </script>
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
        $(document).on('click', '#sendInvoiceEmail', function () {
            const button = $(this);
            const invoiceId = button.data('invoice-id');
            const projectId = button.data('project-id');
            const staffId = button.data('staff-id');

            if (!invoiceId || !projectId || !staffId) {
                Swal.fire({
                    title: 'Missing Data',
                    text: 'Invoice ID, Project ID, or Staff ID is missing.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Send Invoice Email?',
                text: 'This will send the invoice file to the client and project leader.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, send it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true);

                    Swal.fire({
                        title: 'Sending Invoice Email...',
                        text: 'Please wait while the invoice email is being sent.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'send_invoice_email.php',
                        method: 'POST',
                        data: {
                            invoice_id: invoiceId,
                            project_id: projectId,
                            staff_id: staffId
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Email Sent!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                button.prop('disabled', false);

                                Swal.fire({
                                    title: 'Failed!',
                                    text: response.message || 'Unable to send invoice email.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            button.prop('disabled', false);

                            Swal.fire({
                                title: 'Error!',
                                text: 'Server error: ' + (xhr.responseText || error),
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>