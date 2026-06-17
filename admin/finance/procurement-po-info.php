<?php
    session_start(); // Start the session
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
?>
<?php
    $project_id =   $_GET['projectId'];
    $procurement_id =   $_GET['procurementId'];
    $query = "SELECT 
            p.id AS project_id,
            p.project_no,
            p.project_title,
            p.project_leader,
            p.leader_id,
            pr.*,
            pc.*,
            v.company_name AS vendor_name,
            v.ssm_no AS vendor_ssm_no,
            v.registered_address,
            v.contact_name AS pic_name,
            v.contact_email AS pic_email,
            v.contact_phone AS phone_no,
            v.bank_name,
            v.bank_account AS bank_account_no,
            u.email AS leader_email
        FROM project p
        INNER JOIN procurement pr ON p.id = pr.project_id
        INNER JOIN procurement_consultant_input pc ON pr.id = pc.procurement_id
        INNER JOIN vendor v ON pr.vendor_id = v.id
        LEFT JOIN uitm_staff u ON p.leader_id = u.id
        WHERE p.id = '$project_id' AND pr.id = '$procurement_id'
        ORDER BY p.id DESC";

    $result =   mysqli_query($db, $query);
    while($row =   mysqli_fetch_array($result))  
    {
        
        $project_no     = $row['project_no'];
        $project_title  = $row['project_title'];
        $project_leader = $row['project_leader'];
        $leader_id      = $row['leader_id'];
        $leader_email   = $row['leader_email'];
        
        //procurement
        $procurement_type     = $row['procurement_type'];
        $application_type     = $row['application_type'];
        $payment_type     = $row['payment_type'];
        $po_number     = $row['po_number'];
        $status     = $row['status'];
        
        //vendors
        $vendor_name     = $row['vendor_name'];
        $vendor_ssm_no     = $row['vendor_ssm_no'];
        $registered_address     = $row['registered_address'];
        $pic_name     = $row['pic_name'];
        $pic_email     = $row['pic_email'];
        $phone_no     = $row['phone_no'];
        $bank_name     = $row['bank_name'];
        $bank_account_no     = $row['bank_account_no'];
        
        //consultant input
        $goods_service_type     = $row['goods_service_type'];
        $purchase_reason     = $row['purchase_reason'];
        $location     = $row['location'];
        $quotation_file = $row['quotation_file'];
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
                        <h4 class="text-themecolor">Procurement Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Procurement Application</a></li>
                                <li class="breadcrumb-item active">Procurement Info</li>
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
                        <!-- Project Details -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Details</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Number</strong></label>
                                            <h5><?php echo $project_no; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Title</strong></label>
                                            <h5><?php echo $project_title; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Leader Name</strong></label>
                                            <h5><?php echo $project_leader; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Leader Email</strong></label>
                                            <h5><?php echo $leader_email; ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Vendor Details -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Vendor Details</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor Name</strong></label>
                                            <h5><?php echo $vendor_name; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor SSM No</strong></label>
                                            <h5><?php echo $vendor_ssm_no; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor Address</strong></label>
                                            <h5><?php echo $registered_address; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor PIC Name</strong></label>
                                            <h5><?php echo $pic_name; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor PIC Email</strong></label>
                                            <h5><?php echo $pic_email; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor Phone No</strong></label>
                                            <h5><?php echo $phone_no; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Bank Name</strong></label>
                                            <h5><?php echo $bank_name; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Bank Account No</strong></label>
                                            <h5><?php echo $bank_account_no; ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Criteria -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Evaluation Criteria</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr class="text-center">
                                                <th style="width: 5%;">#</th>
                                                <th>Criteria</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $user_id = $userData['id'];
                                            $counter = 1;
                                            
                                            $query2 = "SELECT criteria FROM procurement_criteria WHERE procurement_id = '$procurement_id' ORDER BY id ASC";
                                            $result2 = mysqli_query($db, $query2);
                                            
                                            while ($row = mysqli_fetch_array($result2)) {
                                                $criteria = $row['criteria'];
                                            ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?php echo $counter++; ?></td>
                                                    <td><?php echo htmlspecialchars($criteria); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Additional Details -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Additional Details</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Type of Goods/Services Purchased</strong></label>
                                            <h5><?php echo $goods_service_type; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Reason for Purchase</strong></label>
                                            <h5><?php echo $purchase_reason; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Location</strong></label>
                                            <h5><?php echo $location; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Quotation File</strong></label>
                                            <div><a href="<?php echo htmlspecialchars($quotation_file); ?>" class="btn btn-info btn-sm" target="_blank" rel="noopener noreferrer">View Document</a></div>
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
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
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
</body>

</html>