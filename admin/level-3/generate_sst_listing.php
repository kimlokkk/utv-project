<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa
    
    if (!isset($_GET['project_id'])) {
        die("Invalid Request");
    }

    $project_id = $_GET['project_id'];
    $invoice_no = $_GET['invoice_no'] ?? null;

    // Ambil data projek
    $project_query = "
        SELECT p.project_no, p.project_title, p.client_company_name, 
               u.full_name AS project_leader_name
        FROM project p
        LEFT JOIN uitm_staff u ON p.leader_id = u.id
        WHERE p.id = '$project_id'
    ";
    $project_result = mysqli_query($db, $project_query);
    $project = mysqli_fetch_assoc($project_result);

    // Jika ada invoice, ambil data invois
    if ($invoice_no) {
        $invoice_query = "
            SELECT i.invoice_no
            FROM invoices i
            WHERE i.invoice_no = '$invoice_no' AND i.project_id = '$project_id'
        ";
        $invoice_result = mysqli_query($db, $invoice_query);
        $invoice = mysqli_fetch_assoc($invoice_result);
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
                <!-- SST Listing -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">SST Information</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Project No:</label>
                                        <p><?= $project['project_no'] ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Invoice No:</label>
                                        <p><?= $invoice_no ? $invoice['invoice_no'] : "No Invoice" ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Client Name:</label>
                                        <p><?= $project['client_company_name'] ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Project Leader Name:</label>
                                        <p><?= $project['project_leader_name'] ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label>SST Percentage:</label>
                                        <select id="sstPercentage" class="form-control">
                                            <option value="6">6%</option>
                                            <option value="8">8%</option>
                                            <option value="0">0%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button id="generateSSTReport" class="btn btn-primary">Generate SST Report</button>
                    </div>
                </div>
                <!-- data invoice listing disini -->
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
    <script>
        $(document).ready(function() {
            $("#generateSSTReport").click(function() {
                var sstPercentage = $("#sstPercentage").val();
                alert("SST Report Generated with SST Percentage: " + sstPercentage + "%");
            });
        });
    </script>
</html>