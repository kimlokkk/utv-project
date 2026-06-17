<?php
    session_start(); // Start the session
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<style>
.center222 {
  text-align: center;
}
</style>
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
                        <h4 class="text-themecolor">Preview Statement</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Statement</a></li>
                                <li class="breadcrumb-item active">Preview Statement</li>
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
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-info text-white">Salary/Wages Statement</h3>
                            <div class="card-body">
                                <div class="card-title center222">
                                    <img src="../assets/images/1.-UTV_Logo_Full.png" alt="UTV" width="350" height="280">
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered color-bordered-table dark-bordered-table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center font-weight-bold">Item</th>
                                                        <th class="text-center font-weight-bold">Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Name</td>
                                                        <td class="text-center">Muhammad Hakim Bin Lokman</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">IC/Passport No</td>
                                                        <td class="text-center">9701234567</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">EPF No</td>
                                                        <td class="text-center">31313124</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">SOCSO No</td>
                                                        <td class="text-center">fw31313124</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Income Tax No</td>
                                                        <td class="text-center">128y189</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Bank Name</td>
                                                        <td class="text-center">Harimau Kuning</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Bank Account No</td>
                                                        <td class="text-center">19329821337281</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered color-bordered-table dark-bordered-table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center font-weight-bold">Item</th>
                                                        <th class="text-center font-weight-bold">Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">RA Status</td>
                                                        <td class="text-center">Active</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Type of Appointment</td>
                                                        <td class="text-center">Salary</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Project No</td>
                                                        <td class="text-center">CC240815001</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Project Title</td>
                                                        <td class="text-center">Abcd Project</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Payment Date</td>
                                                        <td class="text-center">01/01/2024-31/12/2024 (12 months)</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center font-weight-bold">Salary Amount</td>
                                                        <td class="text-center">RM2000</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
</body>

</html>