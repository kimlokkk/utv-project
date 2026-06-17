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
                        <h4 class="text-themecolor">Professional Fee Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Professional Fee Application</li>
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
                                    <table class="table table-bordered table-striped" id="professionalTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Project Number</th>
                                                <th>Project Title</th>
                                                <th>Member Name</th>
                                                <th>Bank Name</th>
                                                <th>Bank Account No</th>
                                                <th>Amount (RM)</th>
                                                <th>Status</th>
                                                <th>Return To</th>
                                                <th>Return Remark</th>
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
                                                        p.leader_id,
                                                        pf.id AS application_id,
                                                        pf.member_id,
                                                        pf.amount,
                                                        pf.status,
                                                        pf.return_to,
                                                        pf.return_remark,
                                                        us.full_name AS member_name,
                                                        us.bank_name,
                                                        us.no_account
                                                    FROM project p
                                                    INNER JOIN professional_fee_applications pf ON p.id = pf.project_id
                                                    INNER JOIN uitm_staff us ON pf.member_id = us.id
                                                    WHERE pf.status NOT LIKE '%project leader%'
                                                    ORDER BY pf.id DESC";
                                
                                            $result2 = mysqli_query($db, $query2);
                                
                                            if (mysqli_num_rows($result2) > 0) {
                                                while ($row = mysqli_fetch_array($result2)) {
                                                    $project_id = $row['project_id'];
                                                    $member_id = $row['member_id'];
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $member_name = $row['member_name'];
                                                    $bank_name = $row['bank_name'];
                                                    $no_account = $row['no_account'];
                                                    $amount = $row['amount'];
                                                    $status = $row['status'];
                                                    $return_to = trim((string)($row['return_to'] ?? ''));
                                                    $return_remark = trim((string)($row['return_remark'] ?? ''));
                                                    $application_id = $row['application_id'];
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($member_name); ?></td>
                                                <td><?php echo htmlspecialchars($bank_name); ?></td>
                                                <td><?php echo htmlspecialchars($no_account); ?></td>
                                                <td>RM <?php echo htmlspecialchars($amount); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($status, 'Pending Verification') !== false || stripos($status, 'Pending Approval') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Send to bank') !== false || stripos($status, 'Completed') !== false) {
                                                        $statusClass = 'badge-success';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ($status === 'Rejected' && $return_to !== '') ? htmlspecialchars($return_to) : '-'; ?></td>
                                                <td><?php echo ($status === 'Rejected' && $return_remark !== '') ? nl2br(htmlspecialchars($return_remark)) : '-'; ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    $isRejected = stripos($status, 'Rejected') !== false;
                                                    $isPendingApproval = stripos($status, 'Pending Approval') !== false || stripos($status, 'Approved') !== false;
                                                    $isReturnedToLevel4 = $isRejected && strcasecmp($return_to, 'Level 4') === 0;
                                                    $disableButtons = ($isRejected && !$isReturnedToLevel3) || $isPendingApproval;
                                                    ?>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        id="verifyApplication"
                                                        data-member-id="<?php echo urlencode($member_id); ?>" 
                                                        data-project-id="<?php echo urlencode($project_id); ?>" 
                                                        data-project-no="<?php echo urlencode($project_no); ?>"
                                                        data-application-id="<?php echo urlencode($application_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Verify
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        id="rejectApplication"
                                                        data-member-id="<?php echo urlencode($member_id); ?>" 
                                                        data-project-id="<?php echo urlencode($project_id); ?>" 
                                                        data-project-no="<?php echo urlencode($project_no); ?>"
                                                        data-application-id="<?php echo urlencode($application_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Reject
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                                } }
                                            ?>
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
            $('#professionalTable').DataTable();
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
                            const memberId = $(this).data('member-id');
                            const professionalId = $(this).data('application-id');
                            const projectId = $(this).data('project-id');
                            const projectNo = $(this).data('project-no');
                            const staffId = $(this).data('admin-staff-id');
    
                            // Debug data yang akan dihantar
                            console.log("Professional ID:", professionalId);
                            console.log("Project ID:", projectId);
                            console.log("Project No:", projectNo);
                            console.log("Member Id:", memberId);
                            console.log("Staff Id:", staffId);
                            console.log("Remark:", remark);
    
                            // Hantar permintaan AJAX ke server
                            $.ajax({
                                url: 'professional-reject.php',
                                method: 'POST',
                                data: {
                                    professionalId: professionalId,
                                    projectId: projectId,
                                    projectNo: projectNo,
                                    remark: remark,
                                    staffId: staffId,
                                    memberId: memberId
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
                                'You need to provide a remark to reject the professional fee application.',
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
                        'Professional fee application rejection has been cancelled.',
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
                    const memberId = $(this).data('member-id');
                    const professionalId = $(this).data('application-id');
                    const projectId = $(this).data('project-id');
                    const projectNo = $(this).data('project-no');
                    const staffId = $(this).data('admin-staff-id');
    
                    // Debug data yang akan dihantar
                    console.log("Professional Fee ID:", professionalId);
                    console.log("Project ID:", projectId);
                    console.log("Project No:", projectNo);
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'professional-verify.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            professionalId: professionalId,
                            projectId: projectId,
                            projectNo: projectNo,
                            memberId: memberId,
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
                        'Professional fee application verification has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</html>
