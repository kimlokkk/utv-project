<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
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
                        <h4 class="text-themecolor">Pending Allowances/Wages Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Allowances/Wages Application</li>
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
                        <div class="card">
                            <div class="card-body">
                                <!-- Table to Display Procurement Applications -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="allowanceTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Project Number</th>
                                                <th>Project Title</th>
                                                <th>Name</th>
                                                <th>Application Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Details</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Ambil ID pengguna semasa
                                            $user_id = $userData['id'];
                                            $counter = 1;
                                
                                            // Query untuk mendapatkan data professional fee berdasarkan leader_id
                                            $query2 = "
                                                SELECT 
                                                    p.id AS project_id,
                                                    p.project_no,
                                                    p.project_title,
                                                    aa.status,
                                                    aa.id,
                                                    aa.application_for,
                                                    aa.name
                                                FROM project p
                                                INNER JOIN allowance_applications aa ON p.id = aa.project_id
                                                WHERE aa.status = 'Pending Approval'
                                                ORDER BY aa.id DESC";
                                
                                            $result2 = mysqli_query($db, $query2);
                                
                                            if (mysqli_num_rows($result2) > 0) {
                                                while ($row = mysqli_fetch_array($result2)) {
                                                    $project_id = $row['project_id'];
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $status = $row['status'];
                                                    $application_id = $row['id'];
                                                    $application_for = $row['application_for'];
                                                    $name = $row['name'];
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($name); ?></td>
                                                <td><?php echo htmlspecialchars($application_for); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($status, 'Pending Verification') !== false || stripos($status, 'Pending Approval') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($status, 'Approved') !== false) {
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
                                                       data-application-id="<?php echo urlencode($application_id); ?>">
                                                       View Details
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $isRejected = stripos($status, 'Rejected') !== false;
                                                    $isPendingApproval = stripos($status, 'Approved') !== false;
                                                    $disableButtons = $isRejected || $isPendingApproval;
                                                    ?>
                                                    <button type="button" class="btn btn-success btn-sm" id="approveAllowance" 
                                                        data-application-id="<?php echo urlencode($application_id); ?>" 
                                                        data-project-id="<?php echo urlencode($project_id); ?>" 
                                                        data-project-no="<?php echo urlencode($project_no); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Approve
                                                    </button>&nbsp;
                                                    <button type="button" class="btn btn-danger btn-sm" id="ReturnAllowance" 
                                                        data-application-id="<?php echo urlencode($application_id); ?>" 
                                                        data-project-id="<?php echo urlencode($project_id); ?>" 
                                                        data-project-no="<?php echo urlencode($project_no); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Return
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
                                <h5 class="modal-title" id="viewDetailsModalLabel">Allowances/Wages Details</h5>
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
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
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
        $(document).on('click', '.viewDetails', function () {
            const applicationId = $(this).data('application-id');
            
            // Paparkan modal dan setkan loading message
            $('#viewDetailsModal').modal('show');
            $('#detailsContent').html('<p class="text-center">Loading details...</p>');
        
            // AJAX untuk dapatkan data
            $.ajax({
                url: 'get-allowance-wages-details.php',
                method: 'POST',
                data: { application_id: applicationId },
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
            $('#allowanceTable').DataTable();
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
        $(document).on('click', '#ReturnAllowance', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once returned, you cannot undo this action!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, return it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prompt for return_to + remark
                    Swal.fire({
                        title: 'Return To & Remarks',
                        html: `
                            <select id="returnToSelect" class="swal2-select" style="margin-bottom:10px;width:100%;">
                                <option value="" disabled selected>Select who to return to</option>
                                <option value="Level 4">Level 4</option>
                                <option value="Consultant">Consultant</option>
                            </select>
                            <textarea id="returnRemark" class="swal2-textarea" placeholder="Enter your remarks here..."></textarea>
                        `,
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const returnTo = document.getElementById('returnToSelect').value;
                            const remark = document.getElementById('returnRemark').value;
    
                            if (!returnTo) {
                                Swal.showValidationMessage('Please select who to return to!');
                                return false;
                            }
                            if (!remark) {
                                Swal.showValidationMessage('You need to provide a remark!');
                                return false;
                            }
    
                            return { returnTo, remark };
                        }
                    }).then((remarkResult) => {
                        if (remarkResult.isConfirmed) {
                            const remark = remarkResult.value.remark;
                            const returnTo = remarkResult.value.returnTo;
    
                            const applicationId = $(this).data('application-id');
                            const projectId = $(this).data('project-id');
                            const projectNo = $(this).data('project-no');
                            const staffId = $(this).data('admin-staff-id');
    
                            console.log("Allowance ID:", applicationId);
                            console.log("Project ID:", projectId);
                            console.log("Return To:", returnTo);
                            console.log("Remark:", remark);
    
                            $.ajax({
                                url: 'allowance-reject.php',
                                method: 'POST',
                                data: {
                                    applicationId: applicationId,
                                    projectId: projectId,
                                    projectNo: projectNo,
                                    staffId: staffId,
                                    remark: remark,
                                    returnTo: returnTo
                                },
                                dataType: 'json',
                                success: function (response) {
                                    console.log("AJAX success response:", response);
    
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
                                            response.message,
                                            'error'
                                        );
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error("AJAX Error:", xhr.responseText || error);
                                    Swal.fire(
                                        'Error!',
                                        'An error occurred during return. Please check the console for details.',
                                        'error'
                                    );
                                }
                            });
                        }
                    }).catch((error) => {
                        console.error("Error during modal:", error);
                        Swal.fire(
                            'Error!',
                            'An unexpected error occurred. Please try again.',
                            'error'
                        );
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Allowance/wages return has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#approveAllowance', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once approved, you cannot edit this!",
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
                    const projectId = $(this).data('project-id');
                    const projectNo = $(this).data('project-no');
                    const staffId = $(this).data('admin-staff-id');

                    // Debug data yang akan dihantar
                    console.log("Allowance ID:", applicationId);
                    console.log("Project ID:", projectId);
                    console.log("Project No:", projectNo);
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'allowance-approve.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            applicationId: applicationId,
                            projectId: projectId,
                            projectNo: projectNo,
                            staffId: staffId
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);
    
                            if (response.success) {
                                Swal.fire(
                                    'Approved!',
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
                        'Allowance/wages approval has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</html>
