<?php
    session_start();
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';

    $userData = $_SESSION['user_data']; 
    $user_id = $userData['id']; 

    if (!isset($_GET['project_id'])) {
        die("Invalid Request");
    }

    $project_id = $_GET['project_id'];
    $invoice_no = $_GET['invoice_no'] ?? null;
    $payment_id = $_GET['payment_id'] ?? null; // ✅ Handle tanpa invoice

    // ✅ Ambil data projek
    $project_query = "
        SELECT p.project_no, p.project_title, p.client_company_name, 
               u.full_name AS project_leader_name
        FROM project p
        LEFT JOIN uitm_staff u ON p.leader_id = u.id
        WHERE p.id = '$project_id'
    ";
    $project_result = mysqli_query($db, $project_query);
    $project = mysqli_fetch_assoc($project_result);

    // ✅ Ambil data invois jika ada
    $invoice = null;
    if ($invoice_no) {
        $invoice_query = "SELECT invoice_no, sst_amount FROM invoices WHERE invoice_no = '$invoice_no' AND project_id = '$project_id'";
        $invoice_result = mysqli_query($db, $invoice_query);
        $invoice = mysqli_fetch_assoc($invoice_result);
    }

    // ✅ Ambil data pembayaran (berdasarkan `invoice_no` atau `payment_id`)
    if ($invoice_no) {
        $payment_query = "SELECT SUM(amount) AS total_payment_received, GROUP_CONCAT(purpose SEPARATOR ', ') AS purpose 
                          FROM payments 
                          WHERE invoice_no = '$invoice_no' AND project_id = '$project_id'";
    } else {
        $payment_query = "SELECT amount AS total_payment_received, purpose 
                          FROM payments 
                          WHERE id = '$payment_id' AND project_id = '$project_id' LIMIT 1";
    }
    
    $payment_result = mysqli_query($db, $payment_query);
    $payment = mysqli_fetch_assoc($payment_result);
    $payment_received = $payment['total_payment_received'] ?? 0;
    $purpose = $payment['purpose'] ?? '-'; // Jika tak ada transaction_desc, paparkan "-"

    // ✅ Jika tiada invoice, SST amount = 0
    $sst_amount = $invoice['sst_amount'] ?? 0;

    // ✅ Kira Grand Total
    $grand_total = $payment_received + $sst_amount;

    // ✅ Auto generate Receipt No & Date jika belum ada
    $receipt_number = "REC-" . strtoupper(uniqid());
    $receipt_date = date("d-m-Y");
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
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
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
                        <h4 class="text-themecolor">SST Listing</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Payment Listing</a></li>
                                <li class="breadcrumb-item active">SST Listing</li></li>
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
                        <div class="card card-body printableArea">
                            <!-- Header -->
                            <div class="text-center">
                                <h3 class="font-weight-bold">UiTM Technoventure Sdn Bhd</h3>
                                <p>Finance Department, UiTM Technoventure</p>
                                <p>Email: finance@uitmtechnoventure.com | Phone: +60 3-5544 5555</p>
                                <hr>
                            </div>

                            <!-- Receipt Title -->
                            <h4 class="text-center"><b>OFFICIAL RECEIPT</b></h4>
                            <h5 class="text-center">Receipt No: <b><?= $receipt_number ?></b></h5>
                            <h6 class="text-center">Date: <b><?= $receipt_date ?></b></h6>
                            <hr>

                            <!-- Project & Invoice Details -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><b>Project Details</b></h5>
                                    <p><strong>Project No:</strong> <?= $project['project_no'] ?></p>
                                    <p><strong>Project Title:</strong> <?= $project['project_title'] ?></p>
                                    <p><strong>Client Name:</strong> <?= $project['client_company_name'] ?></p>
                                    <p><strong>Project Leader:</strong> <?= $project['project_leader_name'] ?></p>
                                </div>

                                <div class="col-md-6 text-right">
                                    <h5><b>Invoice Details</b></h5>
                                    <p><strong>Invoice No:</strong> <?= $invoice['invoice_no'] ?></p>
                                    <p><strong>SST Amount:</strong> RM <?= number_format($invoice['sst_amount'], 2) ?></p>
                                    <p><strong>Payment Received:</strong> RM <?= number_format($payment_received, 2) ?></p>
                                </div>
                            </div>
                            <hr>

                            <!-- Payment Breakdown -->
                            <h5 class="text-center"><b>PAYMENT SUMMARY</b></h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-right">Amount (RM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= $purpose ?></td>
                                        <td class="text-right"><?= number_format($payment_received, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>SST Amount</td>
                                        <td class="text-right"><?= number_format($invoice['sst_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><b>Grand Total</b></td>
                                        <td class="text-right"><b>RM <?= number_format($payment_received, 2) ?></b></td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr>

                            <!-- Notes -->
                            <p class="text-center">
                                <i>Thank you for your payment.<br>
                                This is an auto-generated receipt. No signature is required.</i>
                            </p>
                            <p class="text-center"><b>Finance UiTM Technoventure Sdn Bhd</b></p>
                            <hr>

                            <!-- Print & Download Button -->
                            <div class="text-center">
                                <button id="print" class="btn btn-primary btn-lg">
                                    <i class="fa fa-print"></i> Print Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- LETAK DISINI -->
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
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="dist/js/pages/jquery.PrintArea.js" type="text/JavaScript"></script>
    <script>
    $(document).ready(function() {
        $("#print").click(function() {
            var mode = 'iframe'; //popup
            var close = mode == "popup";
            var options = {
                mode: mode,
                popClose: close
            };
            $("div.printableArea").printArea(options);
        });
    });
    </script>
</html>