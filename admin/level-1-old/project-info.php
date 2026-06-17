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
    <link href="dist/css/pages/tab-page.css" rel="stylesheet">
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
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Project Info</li>
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
                            <h3 class="card-header bg-info text-white">Project Info</h3>
                            <div class="card-body">
                                <!--<div class="card-title center222">
                                    <img src="../assets/images/1.-UTV_Logo_Full.png" alt="UTV" width="350" height="280">
                                </div>
                                <hr>-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body p-t-0"></div>
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs customtab" role="tablist">
                                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#project-details" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Details</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-members" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Members</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-timeline" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Timeline</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#file-upload" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project-Related File Upload</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#client-details" role="tab"><span class="hidden-sm-up"></i></span> <span class="hidden-xs-down">Client Details</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-tracking" role="tab"><span class="hidden-sm-up"></i></span> <span class="hidden-xs-down">Project Tracking</span></a> </li>
                                            </ul>
                                            <!-- Tab panes -->
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="project-details" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Project Title</td>
                                                                                <td>Abcd Project</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project No</td>
                                                                                <td>CC24081415001</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Type of Project</td>
                                                                                <td>Contract Research</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Start</td>
                                                                                <td>27 September 2024</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project End</td>
                                                                                <td>27 September 2025</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Registered Project Value (RM)</td>
                                                                                <td>RM1000000</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Quotation Ref No.</td>
                                                                                <td>ABCD12345</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="project-members" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="table-responsive">
                                                                    <table class="table color-bordered-table info-bordered-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Members</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Hakim</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Ali</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Abu</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="project-timeline" role="tabpanel"><div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Starting Project</td>
                                                                                <td>RM 250000</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>First Half Project</td>
                                                                                <td>RM 250000</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Second Half Project</td>
                                                                                <td>RM 250000</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Ending Project</td>
                                                                                <td>RM 250000</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="file-upload" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Appointment/Offer Letter</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Approval to Undertake External Work</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Quotation Document</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Agreement/MoA</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Proposal & Budget</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 1</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 2</td>
                                                                                <td class="text-center"><a href="remarks.php?id=<?php echo $stud_id; ?>" class="btn waves-effect waves-light btn-info assign-button">View</a></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="client-details" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Client's Company Name</td>
                                                                                <td>Abcd Group Holding</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Full Address</td>
                                                                                <td>No.1 Jalan Bandar, 43000 Kajang, Selangor</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Contact Number</td>
                                                                                <td>01123456789</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Business Type</td>
                                                                                <td>Private</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC</td>
                                                                                <td>Hamdan</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Email Address</td>
                                                                                <td>hamdan@gmail.com</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Contact Number</td>
                                                                                <td>01987654321</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div></div>
                                                <div class="tab-pane p-20" id="project-tracking" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h4 class="card-header">File Number : CC24081415001</h4>
                                                                <div class="table-responsive">
                                                                    <table class="table color-bordered-table info-bordered-table">
                                                                        
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Remarks</th>
                                                                                <th>Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Project approval have been rejected (12345)</td>
                                                                                <td>27/09/2024</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project approval have been rejected (12345)</td>
                                                                                <td>27/09/2024</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project approval have been rejected (12345)</td>
                                                                                <td>27/09/2024</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project approval have been rejected (12345)</td>
                                                                                <td>27/09/2024</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project approval have been rejected (12345)</td>
                                                                                <td>27/09/2024</td>
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
    <script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
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