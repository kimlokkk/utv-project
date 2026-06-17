<?php
    session_start(); // Start the session
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
?>
<?php
    $id = $_GET['id']; // Get the ID from the URL parameter

    // Query to fetch data from the research_assistant table
    $query = "SELECT * FROM research_assistant WHERE id = '$id'";
    $result = mysqli_query($db, $query);

    // Fetch data into variables
    while ($row = mysqli_fetch_array($result)) {
        $full_name = $row['full_name'];
        $designation = $row['designation'];
        $ic = $row['ic'];
        $phone = $row['phone'];
        $email = $row['email'];
        $email_2 = $row['email_2'];
        $ptj_address = $row['ptj_address'];
        $gender = $row['gender'];
        $citizenship = $row['citizenship'];
        $marital_status = $row['marital_status'];
        $epf_no = $row['epf_no'];
        $socso_no = $row['socso_no'];
        $income_tax_no = $row['income_tax_no'];
        $employment_position = $row['employment_position'];
        $expertise = $row['expertise'];
        $bank_name = $row['bank_name'];
        $no_account = $row['no_account'];
        $bank_statement_file = $row['bank_statement_file'];
        $copy_ic_file = $row['copy_ic_file'];
        $copy_certificate_file = $row['copy_certificate_file'];
        $password = $row['password'];
        $date_register = $row['date_register'];
        $status = $row['status'];
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
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/node_modules/dropify/dist/css/dropify.min.css">
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
                        <h4 class="text-themecolor">RA/RA Full Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">RA/RO Registration</a></li>
                                <li class="breadcrumb-item active">RA/RA Full Info</li>
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
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                            <!-- Profile Info -->
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><?php echo $full_name; ?> - Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Position</dt>
                                        <dd class="col-sm-9"><?php echo $designation; ?></dd>
                
                                        <dt class="col-sm-3">IC Number</dt>
                                        <dd class="col-sm-9"><?php echo $ic; ?></dd>
                
                                        <dt class="col-sm-3">Contact Number</dt>
                                        <dd class="col-sm-9"><?php echo $phone; ?></dd>
                
                                        <dt class="col-sm-3">Email Address</dt>
                                        <dd class="col-sm-9"><?php echo $email; ?></dd>
                
                                        <dt class="col-sm-3">Email Address 2</dt>
                                        <dd class="col-sm-9"><?php echo !empty($email_2) ? htmlspecialchars($email_2) : "No data available"; ?></dd>
                
                                        <dt class="col-sm-3">Gender</dt>
                                        <dd class="col-sm-9"><?php echo $gender; ?></dd>
                
                                        <dt class="col-sm-3">Citizenship</dt>
                                        <dd class="col-sm-9"><?php echo $citizenship; ?></dd>
                
                                        <dt class="col-sm-3">Marital Status</dt>
                                        <dd class="col-sm-9"><?php echo $marital_status; ?></dd>
                                    </dl>
                                </div>
                            </div>
                
                            <!-- Employment Info -->
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Employment & Tax Information</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">EPF No</dt>
                                        <dd class="col-sm-9"><?php echo !empty($epf_no) ? htmlspecialchars($epf_no) : "No data available"; ?></dd>
                
                                        <dt class="col-sm-3">SOCSO No</dt>
                                        <dd class="col-sm-9"><?php echo !empty($socso_no) ? htmlspecialchars($socso_no) : "No data available"; ?></dd>
                
                                        <dt class="col-sm-3">Income Tax No</dt>
                                        <dd class="col-sm-9"><?php echo !empty($income_tax_no) ? htmlspecialchars($income_tax_no) : "No data available"; ?></dd>
                
                                        <dt class="col-sm-3">Employment Position</dt>
                                        <dd class="col-sm-9"><?php echo $employment_position; ?></dd>
                
                                        <dt class="col-sm-3">Expertise</dt>
                                        <dd class="col-sm-9"><?php echo $expertise; ?></dd>
                                    </dl>
                                </div>
                            </div>
                
                            <!-- File Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Document Files</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Copy of IC</dt>
                                        <dd class="col-sm-9"><a href="../registration-documents/ic-folder/<?php echo $copy_ic_file ?>" class="text-info" target="_blank">View File</a></dd>
                
                                        <dt class="col-sm-3">Copy of Certificate</dt>
                                        <dd class="col-sm-9"><a href="../registration-documents/certificate-folder/<?php echo $copy_certificate_file ?>" class="text-info" target="_blank">View File</a></dd>
                                    </dl>
                                </div>
                            </div>
                
                            <!-- Bank Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Bank Details</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Bank Name</dt>
                                        <dd class="col-sm-9"><?php echo $bank_name; ?></dd>
                
                                        <dt class="col-sm-3">Account No</dt>
                                        <dd class="col-sm-9"><?php echo $no_account; ?></dd>
                
                                        <dt class="col-sm-3">Bank Statement</dt>
                                        <dd class="col-sm-9"><a href="../registration-documents/bank-statement/<?php echo $bank_statement_file ?>" class="text-info" target="_blank">View File</a></dd>
                                    </dl>
                                </div>
                            </div>
                
                            <!-- Remarks Table -->
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Status Tracker</h5>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM research_assistant_registration_remark WHERE research_id = '$id' ORDER BY date_added DESC";
                                            $result = mysqli_query($db, $query);
                                            $counter = 1;
                                            while ($row = mysqli_fetch_array($result)) {
                                                echo '<tr>';
                                                echo '<td>' . $counter++ . '</td>';
                                                echo '<td>' . htmlspecialchars($row['remark']) . '</td>';
                                                echo '</tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Submit Buttons -->
                <?php
                $isRejected = stripos($status, 'Rejected') !== false;
                $isPendingApproval = stripos($status, 'Pending Approval') !== false;
                $isApproved = stripos($status, 'Approved') !== false;
                $disableButtons = $isRejected || $isPendingApproval || $isApproved;
                ?>
                <!--<div class="row m-b-30">
                    <div class="col-md-12">
                        <button type="button" id="verifyResearch" class="btn btn-lg btn-info"
                            data-research-id="<?php echo $id; ?>"
                            data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                            <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Verify
                        </button>&nbsp;&nbsp;
                
                        <button type="button" id="rejectResearch" class="btn btn-lg btn-danger"
                            data-research-id="<?php echo $id; ?>"
                            data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                            <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Reject
                        </button>
                    </div>
                </div>-->
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
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
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
        $(document).on('click', '#verifyResearch', function () {
            const researchId = $(this).data('research-id');
            const staffId = $(this).data('admin-staff-id');
        
            Swal.fire({
                title: 'Verify Research Assistant?',
                text: "Once verified, this action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, verify',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'ra-verify.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            research_id: researchId,
                            staff_id: staffId
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Verified!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = "index.php";
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message || 'Failed to verify.',
                                    'error'
                                );
                            }
                        },
                        error: function (xhr) {
                            console.error("AJAX Error:", xhr.responseText || xhr.statusText);
                            Swal.fire(
                                'Error!',
                                'Server error occurred.',
                                'error'
                            );
                        }
                    });
                } else {
                    Swal.fire('Cancelled', 'Verification was cancelled.', 'info');
                }
            });
        });
    </script>
    <script>
       $(document).on('click', '#rejectResearch', function () {
            const researchId = $(this).data('research-id');
            const staffId = $(this).data('admin-staff-id');
        
            Swal.fire({
                title: 'Reject Research Assistant?',
                input: 'textarea',
                inputLabel: 'Please provide a remark for rejection:',
                inputPlaceholder: 'Type your reason here...',
                inputAttributes: {
                    'aria-label': 'Rejection reason'
                },
                showCancelButton: true,
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write a remark!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const remark = result.value;
        
                    $.ajax({
                        url: 'ra-reject.php',
                        method: 'POST',
                        data: {
                            research_id: researchId,
                            staff_id: staffId,
                            remark: remark
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Rejected!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = "index.php";
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message || "Something went wrong!",
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
                    Swal.fire('Cancelled', 'Rejection was cancelled.', 'info');
                }
            });
        });
    </script>
</body>

</html>