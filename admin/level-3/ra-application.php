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
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
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
                        <h4 class="text-themecolor">RA/RO Status</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">RA/RO Application</a></li>
                                <li class="breadcrumb-item active">RA/RO Status</li>
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
                            <h3 class="card-header bg-success text-white">RA/RO Application Status</h3>
                            <div class="card-body">
                                <h4 class="card-title">List of RA/RO</h4>
                                <div class="table-responsive">
                                    <table id="myTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>RA Name</th>
                                                <th>IC</th>
                                                <th>Project Title</th>
                                                <th>Project No</th>
                                                <th>Status</th>
                                                <th class="text-center">Details</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $query = "
                                                    SELECT 
                                                        raa.id AS application_id,
                                                        ra.id,
                                                        ra.full_name,
                                                        ra.ic,
                                                        p.project_title,
                                                        p.project_no,
                                                        raa.status,
                                                        raa.return_to
                                                    FROM 
                                                        project p
                                                    JOIN 
                                                        research_assistant_application raa ON raa.project_id = p.id
                                                    JOIN 
                                                        research_assistant ra ON ra.id = raa.ra_id
                                                    ORDER BY ra.full_name ASC
                                                ";
                                                $result = mysqli_query($db, $query);
                                                $counter = 1;
                                    
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $raa_id         = $row['application_id'];
                                                        $full_name     = $row['full_name'];
                                                        $ic            = $row['ic'];
                                                        $project_title = $row['project_title'];
                                                        $project_no    = $row['project_no'];
                                                        $status = $row['status'];
                                                        $return_to = trim((string)($row['return_to'] ?? ''));
                                                        $isReturnedToLevel3 = strcasecmp($return_to, 'Level 3') === 0 && (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false);
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($full_name); ?></td>
                                                <td><?php echo htmlspecialchars($ic); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($status, 'Pending Approval') !== false || stripos($status, 'Pending Verification') !== false || stripos($status, 'Pending Submission') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
                                                        $statusClass = 'badge-success';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="javascript:void(0);" 
                                                       class="btn btn-info btn-sm viewDetails" 
                                                       data-raa-id="<?php echo urlencode($raa_id); ?>">
                                                       View Details
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $isRejected = stripos($status, 'Rejected') !== false && !$isReturnedToLevel3;
                                                    $isPendingApproval = stripos($status, 'Pending Approval') !== false || stripos($status, 'Approved') !== false;
                                                    $disableButtons = $isRejected || $isPendingApproval;
                                                    ?>
                                                    <a 
                                                        href="ra-application-edit.php?id=<?php echo urlencode($raa_id); ?>" 
                                                        class="btn btn-info btn-sm" 
                                                        <?php echo $disableButtons ? 'style="pointer-events: none; opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Edit
                                                    </a>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        id="verifyApplication"
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Verify
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        id="rejectApplication"
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Reject
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php 
                                                    }
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
            $('#myTable').DataTable();
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
            $('#myTable2').DataTable();
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
        $(document).on('click', '#rejectApplication', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once rejected, you cannot undo this action!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prompt for remarks
                    Swal.fire({
                        title: 'Remarks',
                        input: 'textarea',
                        inputPlaceholder: 'Enter your remarks here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        preConfirm: (remark) => {
                            if (!remark) {
                                Swal.showValidationMessage('You need to provide a remark to proceed!');
                                return false; // Prevents closing of the modal
                            }
                            return remark;
                        }
                    }).then((remarkResult) => {
                        if (remarkResult.isConfirmed) {
                            const remark = remarkResult.value;
    
                             // Ambil data dari atribut butang
                            const applicationId = $(this).data('application-id');
                            const staffId = $(this).data('admin-staff-id');
    
                            // Debug data yang akan dihantar
                            console.log("Application ID:", applicationId);
                            console.log("Staff Id:", staffId);
                            console.log("Remark:", remark);
    
                            // Hantar permintaan AJAX ke server
                            $.ajax({
                                url: 'ra-application-reject.php',
                                method: 'POST',
                                data: {
                                    applicationId: applicationId,
                                    remark: remark,
                                    staffId: staffId,
                                },
                                dataType: 'json',
                                success: function (response) {
                                    console.log("AJAX success response:", response);
    
                                    if (response.success) {
                                        Swal.fire(
                                            'Rejected!',
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
                                        'An error occurred during rejection. Please check the console for details.',
                                        'error'
                                    );
                                }
                            });
                        } else {
                            Swal.fire(
                                'Cancelled',
                                'You need to provide a remark to reject the research assistant application.',
                                'info'
                            );
                        }
                    }).catch((error) => {
                        console.error("Error during remark modal handling:", error);
                        Swal.fire(
                            'Error!',
                            'An unexpected error occurred while handling the remarks. Please try again.',
                            'error'
                        );
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Research assistant application rejection has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#verifyApplication', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once verified, you cannot edit this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, verify it!',
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
                        url: 'ra-application-verify.php',
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
                        'Research assistant application verification has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>
