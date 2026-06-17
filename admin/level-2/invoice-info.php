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
    }

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
    
    // Query data milestones untuk invois
    $query_invoice_milestones = "
        SELECT im.*, pt.title, pt.description, pt.value
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
            <!-- Container fluid  -->
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
                                                    <h5><?php echo $tin_number; ?></h5>
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
                                                        <?php if (!empty($attachment_list)) { ?>
                                                            <?php foreach ($attachment_list as $index => $attachment_file) { ?>
                                                                <a href="https://utv.domei.io/consultant/project-documents/invoice/<?php echo urlencode($attachment_file); ?>"
                                                                   class="btn-sm btn-info view-attachment m-r-5"
                                                                   target="_blank">
                                                                    View Attachment <?php echo $index + 1; ?>
                                                                </a>
                                                            <?php } ?>
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
                        <?php if ($follow_milestone === 'yes') { ?>
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
                                        <?php
                                        $timeline_query = "SELECT im.*, pt.title, pt.description, pt.value, pt.date_start, pt.date_end
                                                            FROM invoice_milestones im
                                                            LEFT JOIN project_timeline pt ON im.milestone_id = pt.id
                                                            WHERE im.invoice_id = '$invoice_id'";
                                        $timeline_result = mysqli_query($db, $timeline_query);
                                        while ($milestone = mysqli_fetch_assoc($timeline_result)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $milestone['title']; ?></td>
                                            <td><?php echo $milestone['description']; ?></td>
                                            <td><?php echo $milestone['value']; ?></td>
                                            <td><?php echo date("j F Y", strtotime($milestone['date_start'])); ?></td>
                                            <td><?php echo date("j F Y", strtotime($milestone['date_end'])); ?></td>
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
                            </div>
                        </div>
                        <!-- Submit Buttons -->
                        <?php
                        $isRejected = stripos($invoice_status, 'Rejected') !== false || stripos($invoice_status, 'Returned') !== false;
                        $isPendingApproval = stripos($invoice_status, 'Approved') !== false;
                        $isPendingVerification = stripos($invoice_status, 'Pending Verification') !== false;
                        $isSendToBank = stripos($invoice_status, 'Send to bank') !== false;
                        $disableButtons = $isRejected || $isPendingApproval || $isSendToBank || $isPendingVerification;
                        ?>
                        <div class="row m-t-30 m-b-30">
                            <div class="col-md-12">
                                <button type="button" id="editInvoice" class="btn btn-lg btn-warning"
                                    data-edit-url="invoice-edit.php?projectId=<?php echo urlencode($project_id); ?>&invoiceId=<?php echo urlencode($invoice_id); ?>"
                                    <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                    Edit
                                </button>&nbsp;&nbsp;
                        
                                <button type="button" id="approveInvoice" class="btn btn-lg btn-success"
                                    data-invoice-id="<?php echo $invoice_id; ?>"
                                    data-project-id="<?php echo $project_id; ?>"
                                    data-project-no="<?php echo $project_no; ?>"
                                    data-invoice-no="<?php echo $invoice_no; ?>"
                                    data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                    <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                    Approve
                                </button>&nbsp;&nbsp;
                        
                                <button type="button" id="returnInvoice" class="btn btn-lg btn-danger"
                                    data-invoice-id="<?php echo $invoice_id; ?>"
                                    data-project-id="<?php echo $project_id; ?>"
                                    data-project-no="<?php echo $project_no; ?>"
                                    data-invoice-no="<?php echo $invoice_no; ?>"
                                    data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                    <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                    Return
                                </button>
                            </div>
                        </div>
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
        function disableInvoiceActionButtons() {
            $('#editInvoice, #approveInvoice, #returnInvoice').prop('disabled', true);
        }
    
        function enableInvoiceActionButtons() {
            $('#editInvoice, #approveInvoice, #returnInvoice').prop('disabled', false);
        }
    
        $(document).on('click', '#editInvoice', function () {
            const editUrl = $(this).data('edit-url');
    
            if (!editUrl) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Edit URL not found.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
    
            disableInvoiceActionButtons();
    
            Swal.fire({
                title: 'Opening Edit Page...',
                text: 'Please wait while the invoice edit page is being opened.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
    
            window.location.href = editUrl;
        });
    </script>
    <script>
        $(document).on('click', '#approveInvoice', function () {
            const button = $(this);
            const invoiceId = button.data('invoice-id');
            const invoiceNo = button.data('invoice-no');
            const projectId = button.data('project-id');
            const projectNo = button.data('project-no');
            const staffId = button.data('admin-staff-id');
        
            Swal.fire({
                title: 'Are you sure?',
                text: "Once approve, you cannot edit this invoice!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    disableInvoiceActionButtons();
    
                    Swal.fire({
                        title: 'Approving Invoice...',
                        text: 'Please wait while the invoice is being approved.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    const formData = new FormData();
                    formData.append('invoice_id', invoiceId);
                    formData.append('invoice_no', invoiceNo);
                    formData.append('project_id', projectId);
                    formData.append('project_no', projectNo);
                    formData.append('staff_id', staffId);
    
                    $.ajax({
                        url: 'approve_invoice.php',
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Approved!',
                                    text: response.message || 'Invoice has been approved.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = "invoice-application.php";
                                });
                            } else {
                                enableInvoiceActionButtons();
    
                                Swal.fire({
                                    title: 'Failed!',
                                    text: response.message || 'Failed to approve invoice.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr) {
                            enableInvoiceActionButtons();
    
                            Swal.fire({
                                title: 'Error!',
                                text: 'Server error: ' + (xhr.responseText || 'Unknown error'),
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
       $(document).on('click', '#returnInvoice', function () {
            const button = $(this);
            const invoiceId = button.data('invoice-id');
            const invoiceNo = button.data('invoice-no');
            const projectId = button.data('project-id');
            const projectNo = button.data('project-no');
            const staffId = button.data('admin-staff-id');
        
            Swal.fire({
                title: 'Return Invoice Application',
                html:
                    '<select id="returnTo" class="swal2-select">' +
                        '<option value="" disabled selected>Select return destination</option>' +
                        '<option value="Consultant">Consultant</option>' +
                        '<option value="Level 4">CST Level 4</option>' +
                    '</select>' +
                    '<textarea id="returnRemark" class="swal2-textarea" placeholder="Enter your reason here..."></textarea>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Return Invoice',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const returnTo = document.getElementById('returnTo').value;
                    const remark = document.getElementById('returnRemark').value;
        
                    if (!returnTo) {
                        Swal.showValidationMessage('Please select where to return this invoice.');
                        return false;
                    }
    
                    if (!remark || remark.trim() === '') {
                        Swal.showValidationMessage('Please provide a reason.');
                        return false;
                    }
    
                    return {
                        returnTo: returnTo,
                        remark: remark
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    disableInvoiceActionButtons();
    
                    Swal.fire({
                        title: 'Returning Invoice...',
                        text: 'Please wait while the invoice is being returned.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    $.ajax({
                        url: 'return_invoice.php',
                        method: 'GET',
                        data: {
                            invoice_id: invoiceId,
                            invoice_no: invoiceNo,
                            project_id: projectId,
                            project_no: projectNo,
                            staff_id: staffId,
                            return_to: result.value.returnTo,
                            return_remark: result.value.remark
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Returned!',
                                    text: response.message || 'Invoice has been returned.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = "invoice-application.php";
                                });
                            } else {
                                enableInvoiceActionButtons();
    
                                Swal.fire({
                                    title: 'Failed!',
                                    text: response.message || "Failed to return invoice.",
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            enableInvoiceActionButtons();
    
                            Swal.fire({
                                title: 'Error!',
                                text: 'Server error occurred: ' + (xhr.responseText || error),
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
