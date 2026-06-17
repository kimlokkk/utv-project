<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../../db_connect/db_connect.php';
?>
<?php
    
     // Query to count invoice with status 'submitted' from both tables
    $pending_invoice_query = "
        SELECT  COUNT(*) AS total_invoice_submitted FROM invoices WHERE invoice_status = 'Approved'
    ";
    $pending_invoice_result = mysqli_query($db, $pending_invoice_query);
    $pending_invoice_count = mysqli_fetch_assoc($pending_invoice_result)['total_invoice_submitted'];
    
    // Query to count procurement with status 'submitted' from both tables
    $pending_procurement_query = "
        SELECT  COUNT(*) AS total_procurement_submitted FROM procurement WHERE status = 'Approved'
    ";
    $pending_procurement_result = mysqli_query($db, $pending_procurement_query);
    $pending_procurement_count = mysqli_fetch_assoc($pending_procurement_result)['total_procurement_submitted'];
    
    // Query to count professional fee with status 'submitted' from both tables
    $pending_professional_query = "
        SELECT  COUNT(*) AS total_professional_submitted FROM professional_fee_applications WHERE status = 'Approved'
    ";
    $pending_professional_result = mysqli_query($db, $pending_professional_query);
    $pending_professional_count = mysqli_fetch_assoc($pending_professional_result)['total_professional_submitted'];
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_reconciliation_query = "
        SELECT  COUNT(*) AS total_reconciliation_submitted FROM reconciliation_claim_applications WHERE status = 'Approved'
    ";
    $pending_reconciliation_result = mysqli_query($db, $pending_reconciliation_query);
    $pending_reconciliation_count = mysqli_fetch_assoc($pending_reconciliation_result)['total_reconciliation_submitted'];
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_allowance_query = "
        SELECT  COUNT(*) AS total_allowance_submitted FROM allowance_applications WHERE status = 'Approved'
    ";
    $pending_allowance_result = mysqli_query($db, $pending_allowance_query);
    $pending_allowance_count = mysqli_fetch_assoc($pending_allowance_result)['total_allowance_submitted'];
    
    // Query to count project funding with status 'submitted' from both tables
    $pending_project_funding_query = "
        SELECT  COUNT(*) AS total_project_funding_submitted FROM project_funding_assistance_applications WHERE status = 'Approved'
    ";
    $pending_project_funding_result = mysqli_query($db, $pending_project_funding_query);
    $pending_project_funding_count = mysqli_fetch_assoc($pending_project_funding_result)['total_project_funding_submitted'];
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
                        <h4 class="text-themecolor">Dashboard</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
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
                    <div class="col-lg-4 col-md-6">
                        <a href="new-invoice-application.php">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex no-block">
                                        <div class="round align-self-center round-danger"><i class="ti-receipt"></i></div>
                                        <div class="m-l-10 align-self-center">
                                            <h3 class="m-b-0"><?php echo $pending_invoice_count; ?></h3>
                                            <h5 class="text-muted m-b-0">Pending Invoice Application</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="new-procurement-application.php">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex no-block">
                                        <div class="round align-self-center round-danger"><i class="ti-truck"></i></div>
                                        <div class="m-l-10 align-self-center">
                                            <h3 class="m-b-0"><?php echo $pending_procurement_count; ?></h3>
                                            <h5 class="text-muted m-b-0">Pending Procurement Application</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="new-professional-fee-application.php">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex no-block">
                                        <div class="round align-self-center round-danger"><i class="ti-link"></i></div>
                                        <div class="m-l-10 align-self-center">
                                            <h3 class="m-b-0"><?php echo $pending_professional_count; ?></h3>
                                            <h5 class="text-muted m-b-0">Pending Professional Fee Application</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <a href="new-reconciliation-application.php">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex no-block">
                                        <div class="round align-self-center round-danger"><i class="ti-reload"></i></div>
                                        <div class="m-l-10 align-self-center">
                                            <h3 class="m-b-0"><?php echo $pending_reconciliation_count; ?></h3>
                                            <h5 class="text-muted m-b-0">Pending Advance & Reconciliation/Claim</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="new-allowance-wages-application.php">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex no-block">
                                        <div class="round align-self-center round-danger"><i class="ti-money"></i></div>
                                        <div class="m-l-10 align-self-center">
                                            <h3 class="m-b-0"><?php echo $pending_allowance_count; ?></h3>
                                            <h5 class="text-muted m-b-0">Pending Allowance & Wages Application</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="new-project-funding-assistance-application.php">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex no-block">
                                        <div class="round align-self-center round-danger"><i class="ti-blackboard"></i></div>
                                        <div class="m-l-10 align-self-center">
                                            <h3 class="m-b-0"><?php echo $pending_project_funding_count; ?></h3>
                                            <h5 class="text-muted m-b-0">Pending Project Funding Application</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Registration</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="project-registration-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Leader</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Full Info</th>
                                                <!--<th class="text-center">Action</th>-->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Get user ID
                                                $user_id = $userData['id'];
                            
                                                // Query to fetch projects from both tables
                                                $query = "
                                                    SELECT *
                                                    FROM project
                                                    WHERE project_status NOT LIKE '%project leader%' AND project_status != 'Pending Submission'
                                                    ORDER BY project_no ASC;
                                                ";
                            
                                                $result = mysqli_query($db, $query);
                                                $counter = 1; 
                            
                                                // Fetch and display each project
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $project_leader = $row['project_leader'];
                                                    $project_type = $row['project_type'];
                                                    $project_status = $row['project_status'];
                                                    $project_id = $row['id']; // Assuming 'id' is the primary key for the project
                                                    $project_source = $row['project_source'];
                                                    
                                                    // Determine the URL based on the project source
                                                    $info_page = ($project_source === 'Consultancy') 
                                                                 ? 'consultancy-project-info.php' 
                                                                 : 'training-project-info.php';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_leader); ?></td>
                                                <td><?php echo htmlspecialchars($project_type); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($project_status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($project_status, 'Rejected') !== false || stripos($project_status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($project_status, 'Pending approval') !== false || stripos($project_status, 'Pending Verification') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($project_status, 'Approved') !== false || stripos($project_status, 'Appointed') !== false) {
                                                        $statusClass = 'badge-success';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>" 
                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                       title="Full Info">
                                                       Full Info
                                                    </a>
                                                </td>
                                                <!--<td class="text-center">
                                                    <button type="button" class="btn btn-info btn-circle" title="Verify"><i class="fa fa-search"></i> </button>
                                                    <button type="button" class="btn btn-success btn-circle" title="Approve"><i class="fa fa-check"></i> </button>
                                                    <button type="button" class="btn btn-danger btn-circle" title="Reject"><i class="fa fa-times"></i> </button>
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-info assign-button" title="Verify">Verify</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-danger assign-button" title="Reject">Reject</a>
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
                <!-- Modal to View Project -->
                <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewDetailsModalLabel">Application Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Content will be dynamically loaded here -->
                                <div id="detailsContent">
                                    <p class="text-center">Loading details...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    <script>
        $(document).on('click', '.viewDetails', function () {
            const raaId = $(this).data('raa-id');
            
            // Paparkan modal dan setkan loading message
            $('#viewDetailsModal').modal('show');
            $('#detailsContent').html('<p class="text-center">Loading details...</p>');
        
            // AJAX untuk dapatkan data
            $.ajax({
                url: 'get-research-application-details.php',
                method: 'POST',
                data: { raa_id: raaId },
                dataType: 'html',
                success: function (response) {
                    $('#detailsContent').html(response); // Paparkan kandungan ke modal
                },
                error: function () {
                    $('#detailsContent').html('<p class="text-center text-danger">Failed to load details. Please try again later.</p>');
                }
            });
        });
    </script>
    <script>
        $(function () {
            $('#ra-registration-table').DataTable();
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
    <script>
        $(function () {
            $('#ra-appointment-table').DataTable();
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
    <script>
        $(function () {
            $('#project-registration-table').DataTable();
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
    <script>
        $(document).on('click', '#returnApplication', function () {
            Swal.fire({
                title: 'Return Application',
                html:
                    '<select id="returnLevel" class="swal2-input">' +
                        '<option value="" disabled selected>Select return level</option>' +
                        '<option value="Level 3">CST Level 3</option>' +
                        '<option value="Consultant">Consultant</option>' +
                    '</select>' +
                    '<textarea id="returnRemark" class="swal2-textarea" placeholder="Enter your remarks here..."></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const level = $('#returnLevel').val();
                    const remark = $('#returnRemark').val();
    
                    if (!level || !remark) {
                        Swal.showValidationMessage('Please select return level and enter remark.');
                        return false;
                    }
    
                    return {
                        level: level,
                        remark: remark
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const applicationId = $(this).data('application-id');
                    const staffId = $(this).data('admin-staff-id');
                    const { level, remark } = result.value;
    
                    $.ajax({
                        url: 'ra-application-return.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            applicationId: applicationId,
                            remark: remark,
                            staffId: staffId,
                            return_to: level
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Returned!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message || 'Unable to return application.',
                                    'error'
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire('Error', 'Server error occurred.', 'error');
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#approveApplication', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once approve, you cannot edit this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.value) { // Jika pengguna mengesahkan
                     // Ambil data dari atribut butang
                    const applicationId = $(this).data('application-id');
                    const staffId = $(this).data('admin-staff-id');
    
                    // Debug data yang akan dihantar
                    console.log("Application Fee ID:", applicationId);
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'ra-application-approve.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            applicationId: applicationId,
                            staffId: staffId
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);
    
                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload(); // Muat semula halaman selepas berjaya
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire(
                                'Error!',
                                'An error occurred during submission. Please check the console for details.',
                                'error'
                            );
                        }
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Research assistant application approval has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>