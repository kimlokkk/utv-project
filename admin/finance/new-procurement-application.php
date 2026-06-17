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
                        <h4 class="text-themecolor">Pending Procurement Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Procurement Application</li>
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
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="procurementTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Project Number</th>
                                                <th>Project Title</th>
                                                <th>Application Type</th>
                                                <th>Goods/Services</th>
                                                <th>Payment Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Full Info</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Ambil ID pengguna semasa
                                                $user_id = $userData['id'];
                                                $counter = 1;
                                                
                                                // Query untuk mengambil projek berdasarkan status dan leader_id
                                                $query2 = "SELECT 
                                                    p.id,
                                                    p.project_no,
                                                    p.project_title,
                                                    p.project_leader,
                                                    p.leader_id,
                                                    p.project_type,
                                                    pr.id AS procurement_id,
                                                    pr.procurement_type,
                                                    pr.application_type,
                                                    pr.payment_type,
                                                    pr.po_number,
                                                    pr.status,
                                                    pr.created_at
                                                FROM project p
                                                INNER JOIN procurement pr ON p.id = pr.project_id
                                                WHERE pr.status = 'Approved'
                                                ORDER BY procurement_id DESC"; 
                                                
                                                $result2 = mysqli_query($db, $query2);
                                    
                                                while ($row = mysqli_fetch_array($result2)) {
                                                    $project_id = $row['id'];
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $project_type = $row['project_type'];
                                                    $procurement_id = $row['procurement_id'];
                                                    $application_type = $row['application_type'];
                                                    $procurement_type = $row['procurement_type'];
                                                    $payment_type = $row['payment_type'];
                                                    $status = $row['status'];
                                                    
                                                    // Determine the URL based on the project source
                                                    $info_page = ($application_type === 'Vendor Payment') 
                                                             ? 'procurement-vendor-payment-info.php' 
                                                             : 'procurement-po-info.php';
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($application_type); ?></td>
                                                <td><?php echo htmlspecialchars($procurement_type); ?></td>
                                                <td><?php echo !empty($payment_type) ? htmlspecialchars($payment_type) : "Not Available"; ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($status, 'Pending Approval') !== false || stripos($status, 'Pending Verification') !== false) {
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
                                                    <a href="<?php echo urlencode($info_page); ?>?procurementId=<?php echo urlencode($procurement_id); ?>&projectId=<?php echo urlencode($project_id); ?>" class="btn btn-info btn-sm">
                                                        Full Info
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $isRejected = stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false;
                                                    $isPendingVerification = stripos($status, 'Pending Verification') !== false;
                                                    $isSendToBank = stripos($status, 'Completed') !== false;
                                                    $disableButtons = $isRejected || $isSendToBank || $isPendingVerification;
                                                    ?>
                                                    <button type="button" id="completeProcurement" class="btn btn-sm btn-success"
                                                        data-procurement-id="<?php echo $procurement_id; ?>"
                                                        data-project-id="<?php echo $project_id; ?>"
                                                        data-project-no="<?php echo $project_no; ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Complete
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
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
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#procurementTable').DataTable();
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
        $(document).on('click', '#completeProcurement', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once completed, you cannot edit this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.value) { // Jika pengguna mengesahkan
                    // Ambil data dari atribut butang
                    const procurementId = $(this).data('procurement-id');
                    const projectId = $(this).data('project-id');
                    const projectNo = $(this).data('project-no');
                    const staffId = $(this).data('admin-staff-id');
    
                    // Debug data yang akan dihantar
                    console.log("Procurement ID:", procurementId);
                    console.log("Project ID:", projectId);
                    console.log("Project No:", projectNo);
                    console.log("Staff id:", staffId);
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'complete_procurement.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            procurement_id: procurementId,
                            project_id: projectId,
                            staff_id: staffId,
                            project_no: projectNo
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);
    
                            if (response.success) {
                                Swal.fire(
                                    'Complete!',
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
                        'Procurement completion has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</html>