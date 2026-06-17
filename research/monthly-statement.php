<?php
    session_start();
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    
    // Gantikan ini:
    // $ra_id = $_GET['ra_id'] ?? null;
    
    // Dengan ini:
    $ra_id = $userData['id']; // RA login sendiri
    $project_id = $_GET['project_id'] ?? null;
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    if (!$project_id) {
        echo "<div class='col-12'><div class='alert alert-danger'>Invalid Request</div></div>";
        exit;
    }
    
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
    if (!$result || mysqli_num_rows($result) === 0) {
        echo "<div class='col-12'><div class='alert alert-warning'>No data found.</div></div>";
        exit;
    }
    
    $row = mysqli_fetch_assoc($result);
    $gross = (float)$row['gross_salary'];
    $payment_type = $row['payment_type'];
    
    $epf_employee = $payment_type === 'Salary' ? $gross * 0.11 : 0;
    $epf_employer = $payment_type === 'Salary' ? $gross * 0.13 : 0;
    $socso_employee = $payment_type === 'Salary' ? $gross * 0.005 : 0;
    $socso_employer = $payment_type === 'Salary' ? $gross * 0.0175 : 0;
    $net_salary = $gross - $epf_employee - $socso_employee;
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
                        <h4 class="text-themecolor">Monthly Statement</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Statement</a></li>
                                <li class="breadcrumb-item active">Monthly Statement</li>
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
                    $month = $_GET['month'] ?? null;
                    $year = $_GET['year'] ?? null;
                    $project_id = $_GET['project_id'] ?? null;
                    $ra_id = $userData['id']; // RA login sendiri
                    ?>
                
                    <div class="col-12 mb-4">
                        <form method="get">
                            <div class="form-row">
                                <div class="col-md-3">
                                    <label>Month</label>
                                    <select name="month" class="form-control" required>
                                        <option value="">-- Select Month --</option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Year</label>
                                    <select name="year" class="form-control" required>
                                        <option value="">-- Select Year --</option>
                                        <?php
                                        $currentYear = date('Y');
                                        for ($y = $currentYear; $y >= $currentYear - 5; $y--):
                                        ?>
                                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Project</label>
                                    <select name="project_id" class="form-control" required>
                                        <option value="">-- Select Project --</option>
                                        <?php
                                        $res = mysqli_query($db, "
                                            SELECT p.id, p.project_title 
                                            FROM project p
                                            JOIN research_assistant_application raa ON raa.project_id = p.id
                                            WHERE raa.ra_id = '$ra_id'
                                        ");
                                        while ($p = mysqli_fetch_assoc($res)):
                                        ?>
                                            <option value="<?= $p['id'] ?>" <?= ($project_id == $p['id']) ? 'selected' : '' ?>>
                                                <?= $p['project_title'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">View Statement</button>
                                </div>
                            </div>
                        </form>
                    </div>
                
                    <?php
                    if ($month && $year && $project_id):
                        $q = "
                            SELECT 
                                ra.full_name, ra.ic, ra.epf_no, ra.socso_no, ra.income_tax_no,
                                ra.bank_name, ra.no_account, ra.status AS ra_status,
                                raa.payment_type, raa.budget AS gross_salary,
                                p.project_no, p.project_title
                            FROM research_assistant ra
                            JOIN research_assistant_application raa ON ra.id = raa.ra_id
                            JOIN project p ON p.id = raa.project_id
                            WHERE ra.id = '$ra_id' AND p.id = '$project_id'
                        ";
                        $res = mysqli_query($db, $q);
                        if ($res && mysqli_num_rows($res) > 0):
                            $row = mysqli_fetch_assoc($res);
                            $gross = (float)$row['gross_salary'];
                            $epf_employee = ($row['payment_type'] === 'Salary') ? $gross * 0.11 : 0;
                            $epf_employer = ($row['payment_type'] === 'Salary') ? $gross * 0.13 : 0;
                            $socso_employee = ($row['payment_type'] === 'Salary') ? $gross * 0.005 : 0;
                            $socso_employer = ($row['payment_type'] === 'Salary') ? $gross * 0.0175 : 0;
                            $net = $gross - $epf_employee - $socso_employee;
                    ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title mb-4 border-bottom pb-2 font-weight-bold">
                                    Statement for <?= date('F Y', strtotime("$year-$month-01")) ?>
                                </h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="text-muted mb-3">RA/RO Information</h5>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($row['full_name']) ?></p>
                                        <p><strong>IC:</strong> <?= htmlspecialchars($row['ic']) ?></p>
                                        <p><strong>EPF No:</strong> <?= htmlspecialchars($row['epf_no']) ?></p>
                                        <p><strong>SOCSO No:</strong> <?= htmlspecialchars($row['socso_no']) ?></p>
                                        <p><strong>Income Tax:</strong> <?= htmlspecialchars($row['income_tax_no']) ?></p>
                                        <p><strong>Bank:</strong> <?= htmlspecialchars($row['bank_name']) ?> (<?= htmlspecialchars($row['no_account']) ?>)</p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge badge-<?= $row['ra_status'] == 'Approved' ? 'success' : 'secondary' ?>">
                                                <?= $row['ra_status'] ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="text-muted mb-3">Project & Salary Info</h5>
                                        <p><strong>Project No:</strong> <?= htmlspecialchars($row['project_no']) ?></p>
                                        <p><strong>Project Title:</strong> <?= htmlspecialchars($row['project_title']) ?></p>
                                        <p><strong>Appointment Type:</strong> <?= htmlspecialchars($row['payment_type']) ?></p>
                                        <p><strong>Gross Salary:</strong> RM<?= number_format($gross, 2) ?></p>
                                        <?php if ($row['payment_type'] === 'Salary'): ?>
                                            <p><strong>EPF (Employee):</strong> RM<?= number_format($epf_employee, 2) ?></p>
                                            <p><strong>EPF (Employer):</strong> RM<?= number_format($epf_employer, 2) ?></p>
                                            <p><strong>SOCSO (Employee):</strong> RM<?= number_format($socso_employee, 2) ?></p>
                                            <p><strong>SOCSO (Employer):</strong> RM<?= number_format($socso_employer, 2) ?></p>
                                        <?php else: ?>
                                            <p class="text-muted">No deductions (Allowance)</p>
                                        <?php endif; ?>
                                        <hr>
                                        <h5><strong>Net Salary:</strong> <span class="text-primary">RM<?= number_format($net, 2) ?></span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        else:
                            echo "<div class='col-12'><div class='alert alert-warning'>No matching data found.</div></div>";
                        endif;
                    endif;
                    ?>
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