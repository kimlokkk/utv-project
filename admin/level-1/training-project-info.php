<?php
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
?>
<?php
    $id                 =   $_GET['id'];
    $query              =   "SELECT * FROM project WHERE id = '$id' ";  
    $result             =   mysqli_query($db, $query);
    while($row          =   mysqli_fetch_array($result))  
    {
        $leader_id                      = $row['leader_id'];
        $project_leader                 = $row['project_leader'];
        $project_no                     = $row['project_no'];
        $project_title                  = $row['project_title'];
        $project_type                   = $row['project_type'];
        $project_start                  = $row['project_start'];
        $project_end                    = $row['project_end'];
        $registered_project_value       = $row['registered_project_value'];
        $adjusted_project_value         = $row['adjusted_project_value'];
        $appointment_letter             = $row['appointment_letter'];
        $approval_external_work         = $row['approval_external_work'];
        $project_proposal               = $row['project_proposal'];
        $other_doc_1                    = $row['other_doc_1'];
        $other_doc_2                    = $row['other_doc_2'];
        $client_company_name            = $row['client_company_name'];
        $client_address                 = $row['client_address'];
        $client_contact                 = $row['client_contact'];
        $client_business_type           = $row['client_business_type'];
        $client_pic                     = $row['client_pic'];
        $client_pic_email               = $row['client_pic_email'];
        $client_pic_contact             = $row['client_pic_contact'];
        $date_create                    = $row['date_create'];
        $project_status                 = $row['project_status'];
    }
    
    $tracking_query = "SELECT * FROM project_tracker WHERE project_id = '$id' ORDER BY id DESC";
    $tracking_result = mysqli_query($db, $tracking_query);
    $tracking_data = [];
    while ($track_row = mysqli_fetch_array($tracking_result)) {
        $tracking_data[] = $track_row;
    }
?>
<?php
    if (isset($_GET['update']) && $_GET['update'] == 'save-success' && isset($_GET['id'])) {
        $id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); // Sanitize the ID
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Project Verified',
                    text: 'Project has been successfully verified!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    window.location.replace('index.php');
                });
            });
        </script>";
    } elseif (isset($_GET['update']) && $_GET['update'] == 'save-fail') {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to save your project. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } elseif (isset($_GET['update']) && $_GET['update'] == 'invalid-id') {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Invalid Project',
                    text: 'The project ID provided is invalid.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    }
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
    <link href="dist/css/pages/tab-page.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
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
                        <h4 class="text-themecolor">Project Info</h4>
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
                            <h3 class="card-header bg-success text-white">Project Info</h3>
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
                                                                                <td><?php echo !empty($project_title) ? htmlspecialchars($project_title) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project No</td>
                                                                                <td><?php echo !empty($project_no) ? htmlspecialchars($project_no) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Type of Project</td>
                                                                                <td><?php echo !empty($project_type) ? htmlspecialchars($project_type) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Start</td>
                                                                                <td><?php echo !empty($project_start) ? date("d F Y", strtotime($project_start)) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project End</td>
                                                                                <td><?php echo !empty($project_start) ? date("d F Y", strtotime($project_end)) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Registered Project Value (RM)</td>
                                                                                <td><?php echo !empty($registered_project_value) ? htmlspecialchars($registered_project_value) : "No data available yet"; ?></td>
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
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table id="members" class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Name</th>
                                                                                <th>IC Number</th>
                                                                                <!--<th class="text-center">Action</th>-->
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            // Query to fetch project members
                                                                            $query = "
                                                                                SELECT pmc.*, us.*
                                                                                FROM project_members_consultant pmc
                                                                                INNER JOIN uitm_staff us ON pmc.member_id = us.id
                                                                                WHERE pmc.project_id = '$id'
                                                                                ORDER BY pmc.project_no ASC
                                                                            ";
                                                                            
                                                                            $result = mysqli_query($db, $query);
                                                                            
                                                                            // Check for errors in the query execution
                                                                            if (!$result) {
                                                                                // Log or display the MySQL error
                                                                                error_log("MySQL Query Error: " . mysqli_error($db));
                                                                                echo "<p>Error fetching project members: " . mysqli_error($db) . "</p>";
                                                                                exit;
                                                                            }
                                                                            
                                                                            // Process the results if the query succeeded
                                                                            $counter = 1;
                                                                            while ($row = mysqli_fetch_array($result)) {
                                                                                /*$project_source = $row['project_source'];
                                                                                $project_no = $row['project_no'];
                                                                                $project_title = $row['project_title'];
                                                                                $member_name = $row['member_name'];
                                                                                $member_ic = $row['member_ic'];
                                                                                $start_date = $row['start_date'];
                                                                                $duration = $row['duration'];
                                                                                $status = $row['status'];
                                                                                $payment_type = $row['payment_type'];
                                                                                $budget = $row['budget'];
                                                                                $project_leader = $row['project_leader'];
                                                                                $project_id = $row['id']; // Assuming 'id' is the primary key for the project*/
                                                                                
                                                                                $member_name = $row['full_name'];
                                                                                $member_ic = $row['ic'];
                                                                            
                                                                                // Determine the URL based on the project source
                                                                                $info_page = ($project_source === 'Consultancy') 
                                                                                             ? 'consultancy-project-info.php' 
                                                                                             : 'training-project-info.php';
                                                                            ?>
                                                                                <td><?php echo htmlspecialchars($member_name); ?></td>
                                                                                <td><?php echo htmlspecialchars($member_ic); ?></td>
                                                                                <!--<td class="text-center">
                                                                                    <a href="<?php //echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>" 
                                                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                                                       title="Full Info">
                                                                                       Full Info
                                                                                    </a>
                                                                                </td>-->
                                                                            </tr>
                                                                            <?php 
                                                                                $counter++;
                                                                            } 
                                                                            ?>
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
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($appointment_letter) ? "<a href=\"project-documents/training-project/appointment-letter/" . htmlspecialchars($appointment_letter) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Approval to Undertake External Work</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($approval_external_work) ? "<a href=\"project-documents/training-project/approval-external-work-letter/" . htmlspecialchars($approval_external_work) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Proposal & Budget</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($project_proposal) ? "<a href=\"project-documents/training-project/project-proposal/" . htmlspecialchars($project_proposal) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 1</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($other_doc_1) ? "<a href=\"project-documents/training-project/other-docs/" . htmlspecialchars($other_doc_1) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 2</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($other_doc_2) ? "<a href=\"project-documents/training-project/other-docs/" . htmlspecialchars($other_doc_2) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
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
                                                                                <td><?php echo !empty($client_company_name) ? htmlspecialchars($client_company_name) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Full Address</td>
                                                                                <td><?php echo !empty($client_address) ? htmlspecialchars($client_address) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Contact Number</td>
                                                                                <td><?php echo !empty($client_contact) ? htmlspecialchars($client_contact) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Business Type</td>
                                                                                <td><?php echo !empty($client_business_type) ? htmlspecialchars($client_business_type) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC</td>
                                                                                <td><?php echo !empty($client_pic) ? htmlspecialchars($client_pic) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Email Address</td>
                                                                                <td><?php echo !empty($client_pic_email) ? htmlspecialchars($client_pic_email) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Contact Number</td>
                                                                                <td><?php echo !empty($client_pic_contact) ? htmlspecialchars($client_pic_contact) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="project-tracking" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h4 class="card-header">File Number : <?php echo htmlspecialchars($project_no); ?></h4>
                                                                <div class="table-responsive">
                                                                    <table class="table color-bordered-table info-bordered-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Remarks</th>
                                                                                <th>Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php if (!empty($tracking_data)) : ?>
                                                                                <?php foreach ($tracking_data as $track) : ?>
                                                                                    <tr>
                                                                                        <td><?php echo htmlspecialchars($track['remark']); ?></td>
                                                                                        <td><?php echo date("d F Y", strtotime($track['date'])); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            <?php else : ?>
                                                                                <tr>
                                                                                    <td colspan="2" class="text-center">No tracking data available yet</td>
                                                                                </tr>
                                                                            <?php endif; ?>
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
                <?php
                    $isVerifiedOrPending = in_array($project_status, ['Verified', 'Pending Approval', 'Approved', 'Appointed']);
                ?>
                <div class="row">
                    <div class="m-b-20 col-md-12">
                        <button type="button"
                                id="btnVerify"
                                class="btn btn-lg btn-info"
                                <?php echo $isVerifiedOrPending ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Verify
                        </button>&nbsp;&nbsp;
                        <button type="button" 
                                id="btnReturn" 
                                class="btn btn-lg btn-danger"
                                <?php echo $isVerifiedOrPending ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Return</button>
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
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#members').DataTable();
            var table = $('#example').DataTable({
                "columnDefs": [{
                    "visible": false,
                    "targets": 2
                }],
                "order": [
                    [2, 'asc']
                ],
                "displayLength": 25,
                "drawCallback": function (settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;
                    api.column(2, {
                        page: 'current'
                    }).data().each(function (group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before('<tr class="group"><td colspan="5">' + group + '</td></tr>');
                            last = group;
                        }
                    });
                }
            });
            // Order by the grouping
            $('#example tbody').on('click', 'tr.group', function () {
                var currentOrder = table.order()[0];
                if (currentOrder[0] === 2 && currentOrder[1] === 'asc') {
                    table.order([2, 'desc']).draw();
                } else {
                    table.order([2, 'asc']).draw();
                }
            });
            // responsive table
            $('#config-table').DataTable({
                responsive: true
            });
            $('#example23').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary mr-1');
        });
    </script>
    <!-- Sweet-Alert  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script>
        document.getElementById('btnReturn').addEventListener('click', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to return this project?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, return it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prompt for remarks
                    Swal.fire({
                        title: 'Remarks',
                        input: 'textarea',
                        inputPlaceholder: 'Enter your remarks here...',
                        inputAttributes: {
                            'aria-label': 'Enter your remarks here'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        preConfirm: (remark) => {
                            if (!remark) {
                                Swal.showValidationMessage('You need to provide a remark to proceed!');
                            }
                            return remark;
                        }
                    }).then((remarkResult) => {
                        if (remarkResult.isConfirmed) {
                            const remark = remarkResult.value;
    
                            // Send the data via POST
                            const formData = new FormData();
                            formData.append('id', '<?php echo $id; ?>'); // Add the project ID dynamically
                            formData.append('remark', remark);
    
                            fetch('training-project-return.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.text()) // Expect a redirect
                            .then(() => {
                                // Success: Redirect will handle the rest
                                Swal.fire({
                                    title: 'Returned!',
                                    text: 'The project has been returned successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An unexpected error occurred. Please try again later.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
    document.getElementById('btnVerify').addEventListener('click', function (e) {
        e.preventDefault();
    
        // Confirm action with SweetAlert2
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to verify this project?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#03a9f3',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, verify it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to consultancy_project_verify.php with the project ID
                const projectId = "<?php echo $id; ?>"; // Get project ID dynamically
                window.location.href = `training-project-verify.php?id=${projectId}`;
            }
        });
    });
    </script>
</body>

</html>