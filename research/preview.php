<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    
    $userData = $_SESSION['user_data'];
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
                    <?php
                    $ra_id = $_GET['ra_id'] ?? null;
                    $project_id = $_GET['project_id'] ?? null;
                
                    if (!$ra_id || !$project_id) {
                        echo "<div class='col-12'><div class='alert alert-danger'>Invalid Request</div></div>";
                    } else {
                        $query = "
                            SELECT 
                                ra.full_name,
                                ra.ic,
                                ra.epf_no,
                                ra.socso_no,
                                ra.income_tax_no,
                                ra.bank_name,
                                ra.no_account,
                                ra.status AS ra_status,
                                raa.payment_type,
                                raa.budget AS gross_salary,
                                p.project_no,
                                p.project_title
                            FROM research_assistant ra
                            JOIN research_assistant_application raa ON ra.id = raa.ra_id
                            JOIN project p ON p.id = raa.project_id
                            WHERE ra.id = '$ra_id' AND p.id = '$project_id'
                        ";
                        $result = mysqli_query($db, $query);
                
                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                
                            $gross = (float)$row['gross_salary'];
                            $epf_employee = $row['payment_type'] === 'Salary' ? $gross * 0.11 : 0;
                            $epf_employer = $row['payment_type'] === 'Salary' ? $gross * 0.13 : 0;
                            $socso_employee = $row['payment_type'] === 'Salary' ? $gross * 0.005 : 0;
                            $socso_employer = $row['payment_type'] === 'Salary' ? $gross * 0.0175 : 0;
                            $net_salary = $gross - $epf_employee - $socso_employee;
                    ?>
                    <div class="col-lg-12 mx-auto">
                        <div class="card shadow-sm border">
                            <div class="card-body px-5 py-4">
                                <h4 class="mb-4 pb-2 border-bottom font-weight-bold text-center">Salary / Wages Statement</h4>
                
                                <!-- RA/RO Block -->
                                <div class="bg-light p-4 rounded mb-4">
                                    <h5 class="text-dark font-weight-bold mb-3">RA/RO Details</h5>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Name:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>IC No:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['ic']); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>EPF No:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['epf_no']); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>SOCSO No:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['socso_no']); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Income Tax No:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['income_tax_no']); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Bank:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['bank_name']) . ' (' . htmlspecialchars($row['no_account']) . ')'; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>RA Status:</strong></div>
                                        <div class="col-md-8">
                                            <?php
                                                $status = htmlspecialchars($row['ra_status']);
                                                $badgeClass = 'badge-secondary';
                                    
                                                if ($status === 'Approved') $badgeClass = 'badge-success';
                                                else if ($status === 'Pending') $badgeClass = 'badge-warning';
                                                else if ($status === 'Rejected') $badgeClass = 'badge-danger';
                                    
                                                echo "<span class='badge $badgeClass' style='font-size: 12px; padding: 5px 8px;'>$status</span>";
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Appointment Type:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['payment_type']); ?></div>
                                    </div>
                                </div>
                
                                <!-- Project Block -->
                                <div class="bg-white border p-4 rounded mb-4">
                                    <h5 class="text-dark font-weight-bold mb-3">Project Details</h5>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Project No:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['project_no']); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Project Title:</strong></div>
                                        <div class="col-md-8"><?php echo htmlspecialchars($row['project_title']); ?></div>
                                    </div>
                                </div>
                
                                <!-- Salary Breakdown -->
                                <div class="bg-light p-4 rounded">
                                    <h5 class="text-dark font-weight-bold mb-3">Salary Breakdown</h5>
                                    <div class="row mb-2">
                                        <div class="col-md-6"><strong>Gross Salary:</strong></div>
                                        <div class="col-md-6">RM<?php echo number_format($gross, 2); ?></div>
                                    </div>
                                    <?php if ($row['payment_type'] === 'Salary') { ?>
                                        <div class="row mb-2">
                                            <div class="col-md-6"><strong>EPF (Employee):</strong></div>
                                            <div class="col-md-6">RM<?php echo number_format($epf_employee, 2); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6"><strong>EPF (Employer):</strong></div>
                                            <div class="col-md-6">RM<?php echo number_format($epf_employer, 2); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6"><strong>SOCSO (Employee):</strong></div>
                                            <div class="col-md-6">RM<?php echo number_format($socso_employee, 2); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6"><strong>SOCSO (Employer):</strong></div>
                                            <div class="col-md-6">RM<?php echo number_format($socso_employer, 2); ?></div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="text-muted">No deductions (Allowance type)</div>
                                    <?php } ?>
                
                                    <hr>
                                    <div class="row mt-3 align-items-center">
                                        <div class="col-md-6 text-right pr-3"><strong class="h5">Net Salary:</strong></div>
                                        <div class="col-md-6"><span class="h5 text-success font-weight-bold">RM<?php echo number_format($net_salary, 2); ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        } else {
                            echo "<div class='col-12'><div class='alert alert-warning'>No matching record found.</div></div>";
                        }
                    }
                    ?>
                </div>
                <div class="row m-t-20 m-b-30">
                    <div class="col-md-12">
                        <a href="monthly-statement.php?ra_id=<?= $ra_id ?>&project_id=<?= $project_id ?>" class="btn btn-lg btn-info">
                            View Monthly Breakdown
                        </a>
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