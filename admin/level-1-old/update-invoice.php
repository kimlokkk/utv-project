<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
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
        $registered_project_value = $row['registered_project_value'];
        $adjusted_project_value = $row['adjusted_project_value'];
        $client_company_name = $row['client_company_name'];
        $client_address = $row['client_address'];
        $client_contact = $row['client_contact'];
        $client_pic = $row['client_pic'];
        $client_pic_email = $row['client_pic_email'];
        $client_pic_contact = $row['client_pic_contact'];
        $date_create = $row['date_create'];
    }
    
    // Query data invoices
    $query_invoices = "SELECT * FROM invoices WHERE id = '$invoice_id' ORDER BY id DESC";
    $result_invoices = mysqli_query($db, $query_invoices);
    while ($row = mysqli_fetch_array($result_invoices)) {
        $invoice_purpose = $row['invoice_purpose']; // Simpan semua invois dalam array
        $additional_info = $row['additional_info']; // Simpan semua invois dalam array
        $total_amount = $row['total_amount']; // Simpan semua invois dalam array
        $sst_amount = $row['sst_amount']; // Simpan semua invois dalam array
        $total_invoice = $row['total_invoice']; // Simpan semua invois dalam array
        $attachment = $row['attachment']; // Simpan semua invois dalam array
        $created_at = $row['created_at']; // Simpan semua invois dalam array
        $invoice_status = $row['invoice_status'];
    }
    
    // Query data milestones untuk invois
    $query_invoice_milestones = "
        SELECT im.*, pt.title, pt.description, pt.value, pt.date_timeline
        FROM invoice_milestones im
        LEFT JOIN project_timeline pt ON im.milestone_id = pt.id
        WHERE pt.project_id = '$invoice_id'
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
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="dist/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                        <h4 class="text-themecolor">Update Invoice Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Update Invoice Application</li>
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
                        <form id="verifyInvoice" method="POST" enctype="multipart/form-data">
                            <!-- Hidden Inputs -->
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>">
                            <input type="hidden" name="total_invoice" value="<?php echo $total_invoice; ?>">
                            <input type="hidden" name="project_no" value="<?php echo $project_no; ?>">
                            <input type="hidden" name="invoice_purpose" value="<?php echo $invoice_purpose; ?>">                
                            <!-- Project Details -->
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
                                                    <input type="text" name="project_leader_email" value="<?php echo $userData['email']; ?>" class="form-control" disabled required>
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
                                                    <input type="text" name="sst" value="6%" class="form-control" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Invoice Purpose & Attachment -->
                            <div class="row">
                                <!-- Invoice Purpose Card -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <h3 class="card-header bg-success text-white">Invoice Purpose & Additional Info</h3>
                                        <div class="card-body">
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
                                        </div>
                                    </div>
                                </div>
                
                                <!-- Attachment Card -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <h3 class="card-header bg-success text-white">Attachment (PO/Approval for Invoice)</h3>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong>Attachment</strong>
                                                </div>
                                                <div class="m-t-10 col-md-12">
                                                    <?php if (!empty($attachment)) { ?>
                                                        <a href="https://utv.domei.io/consultant/project-documents/invoice/<?php echo urlencode($attachment); ?>" 
                                                           class="btn btn-info" 
                                                           target="_blank">
                                                            View Attachment
                                                        </a>
                                                    <?php } else { ?>
                                                        <button class="btn btn-secondary" disabled>No Attachment Available</button>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                
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
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $timeline_query = "SELECT im.*, pt.title, pt.description, pt.value, pt.date_timeline
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
                                                <td><?php echo $milestone['date_timeline']; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <strong>Total Amount to Claim (RM)</strong>
                                                <h5><?php echo $total_amount; ?></h5>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <strong>SST Amount (6%)</strong>
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
                            <!-- Update Invoice Section -->
                            <div class="card">
                                <h3 class="card-header bg-success text-white">Update Invoice</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Invoice Status -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="invoice_status">Invoice Status</label>
                                                <select class="form-control" id="invoice_status" name="invoice_status" required>
                                                    <option value="Processed" <?php echo ($invoice_status == 'Processed') ? 'selected' : ''; ?>>Processed</option>
                                                    <option value="Verified" <?php echo ($invoice_status == 'Verified') ? 'selected' : ''; ?>>Verified</option>
                                                    <option value="Approved" <?php echo ($invoice_status == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ($invoice_status == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                            
                                        <!-- Invoice Number -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="invoice_no">Invoice No</label>
                                                <input type="text" class="form-control" id="invoice_no" name="invoice_no" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Upload Invoice File -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="invoice_file">Upload Invoice File <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" name="invoice_file" class="custom-file-input" id="inputGroupFile01" required>
                                                        <label class="custom-file-label" for="inputGroupFile01">Choose File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit Buttons -->
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="reset" class="btn btn-lg btn-danger">Reset</button>&nbsp;&nbsp;
                                    <button type="submit" id="saveInvoiceUpdate" class="btn btn-lg btn-primary">Save Changes</button>
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
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#verifyInvoice").on("submit", function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
        
                $.ajax({
                    url: "update_invoice.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        console.log("Submitting...");
                        $("#saveInvoiceUpdate").prop("disabled", true).text("Saving...");
                    },
                    success: function(response) {
                        console.log("Server response:", response);
                        
                        try {
                            var result = JSON.parse(response);
                            
                            if (result.status === "success") {
                                Swal.fire({
                                    title: "Success!",
                                    text: result.message,
                                    icon: "success",
                                    confirmButtonText: "OK"
                                }).then(() => {
                                    window.location.href = "new-invoice-application.php";
                                });
                            } else {
                                Swal.fire("Error!", result.message, "error");
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response:", e, response);
                            Swal.fire("Error!", "Invalid response from server.", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        Swal.fire("Error!", "Something went wrong!", "error");
                    },
                    complete: function() {
                        $("#saveInvoiceUpdate").prop("disabled", false).text("Save Changes");
                    }
                });
            });
        });
    </script>

</body>

</html>