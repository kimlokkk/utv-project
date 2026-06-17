<?php
    session_start(); // Start the session
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa
?>
<?php
    // Query untuk mendapatkan semua professional application yang dibuat oleh pengguna
    $query = "SELECT 
                p.id AS project_id,
                p.project_no,
                p.project_title,
                p.leader_id,
                pf.id AS professional_id,
                pf.* 
            FROM project p
            INNER JOIN professional_fee_applications pf ON p.id = pf.project_id
            WHERE p.leader_id = '$user_id'
               OR EXISTS (
                   SELECT 1
                   FROM project_members_consultant pm
                   WHERE pm.project_id = p.id AND pm.member_id = '$user_id'
               )
            ORDER BY pf.id DESC
            ";
    
    $result = mysqli_query($db, $query);
    $hasApplications = mysqli_num_rows($result) > 0; // Semak sama ada ada data atau tidak
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
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
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
                                <?php if ($hasApplications) { ?>
                                    <!-- Table to Display Professional Applications -->
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
                                                            WHERE 
                                                                (p.leader_id = '$user_id'
                                                                 OR EXISTS (
                                                                     SELECT 1 
                                                                     FROM project_members_consultant pm 
                                                                     WHERE pm.project_id = p.id 
                                                                       AND pm.member_id = '$user_id'
                                                                 )
                                                                 OR pf.return_to = 'Consultant'
                                                                )
                                                                AND (pf.return_to IS NULL OR pf.return_to != 'Level 3')
                                                            ORDER BY pf.id DESC

                                                        ";
                                    
                                                $result2 = mysqli_query($db, $query2);
                                    
                                                if (mysqli_num_rows($result2) > 0) {
                                                    while ($row = mysqli_fetch_array($result2)) {
                                                        $project_id = $row['project_id'];
                                                        $project_no = $row['project_no'];
                                                        $project_title = $row['project_title'];
                                                        $leader_id = $row['leader_id'];
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
                                                        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Send to bank') !== false) {
                                                            $statusClass = 'badge-success';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            echo (stripos($status, 'Rejected') !== false && strcasecmp($return_to, 'Consultant') === 0 && $return_remark !== '')
                                                                ? nl2br(htmlspecialchars($return_remark))
                                                                : '-';
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                            $isProjectLeader = ($user_id == $leader_id);
                                                            $allowApprove = false;
                                                            $allowReject = false;
                                                        
                                                            if ($isProjectLeader) {
                                                                if (stripos($status, 'Rejected') !== false) {
                                                                    // Kalau dah rejected, disable semua
                                                                    $allowApprove = false;
                                                                    $allowReject = false;
                                                                } elseif ( 
                                                                    stripos($status, 'project leader') !== false
                                                                ) {
                                                                    // Project leader sahaja yang boleh buat approve/reject
                                                                    $allowApprove = true;
                                                                    $allowReject = true;
                                                                }
                                                            }
                                                        ?>
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-success btn-sm" 
                                                            id="approveProfessional"
                                                            data-project-id="<?php echo urlencode($project_id); ?>" 
                                                            data-project-no="<?php echo urlencode($project_no); ?>"
                                                            data-application-id="<?php echo urlencode($application_id); ?>"
                                                            <?php echo $allowApprove ? '' : 'disabled'; ?>>
                                                            Approve
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            id="rejectProfessional"
                                                            data-project-id="<?php echo urlencode($project_id); ?>" 
                                                            data-project-no="<?php echo urlencode($project_no); ?>"
                                                            data-application-id="<?php echo urlencode($application_id); ?>"
                                                            <?php echo $allowReject ? '' : 'disabled'; ?>>
                                                            Reject
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo '<tr><td colspan="5" class="text-center">No applications found.</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Button to Create New Professional Application -->
                                    <div class="text-center mt-4">
                                        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#selectProjectModal">Create New Professional Fee Application</button>
                                    </div>
                                <?php } else { ?>
                                    <!-- No Data Message -->
                                    <div class="text-center py-5">
                                        <h4>No Professional Fee Applications Found</h4>
                                        <p>You have not created any professional fee applications yet.</p>
                                        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#selectProjectModal">Create New Professional Fee Application</button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal to Select Project -->
                <div class="modal fade" id="selectProjectModal" tabindex="-1" role="dialog" aria-labelledby="selectProjectModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="selectProjectModalLabel">Select a Project</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="selectProjectForm">
                                    <div class="form-group">
                                        <label for="projectSelect">Choose a Project</label>
                                        <select class="form-control select2" id="projectSelect" name="projectId" required>
                                            <option value="" disabled selected>Select a project</option>
                                            <?php 
                                            $user_id = $userData['id'];
                                            $projectQuery = "
                                                SELECT DISTINCT p.id, p.project_no, p.project_title
                                                FROM project p
                                                LEFT JOIN project_members_consultant pm ON p.id = pm.project_id
                                                WHERE p.project_status IN ('Approved', 'Appointed') AND (p.leader_id = '$user_id' OR pm.member_id = '$user_id')
                                                ORDER BY p.project_no ASC
                                            ";
                                            $projectResult = mysqli_query($db, $projectQuery);
                                            while ($project = mysqli_fetch_assoc($projectResult)) { ?>
                                                <option value="<?php echo $project['id']; ?>">
                                                    <?php echo $project['project_no'] . " - " . $project['project_title']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="proceedToProfessionalFee">Proceed</button>
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
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script>
        $('#projectSelect').select2({
            dropdownParent: $('#selectProjectModal'),
            placeholder: 'Search and select a project',
            width: '100%'
        });

        // Handle "Proceed" button click in the modal
        document.getElementById('proceedToProfessionalFee').addEventListener('click', function () {
            const projectId = document.getElementById('projectSelect').value;
            if (projectId) {
                window.location.href = `professional-fee-application.php?projectId=${encodeURIComponent(projectId)}`;
            } else {
                alert('Please select a project.');
            }
        });
    </script>
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
        $(document).on('click', '#approveProfessional', function () {
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
    
                    // Debug data yang akan dihantar
                    console.log("Professional Fee ID:", applicationId);
                    console.log("Project ID:", projectId);
                    console.log("Project No:", projectNo);
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'submit_professional.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            professionalId: applicationId,
                            projectId: projectId,
                            projectNo: projectNo
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
                        'Professional fee application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
       $(document).on('click', '#rejectProfessional', function () {
            const applicationId = $(this).data('application-id');
            const projectId = $(this).data('project-id');
            const projectNo = $(this).data('project-no');
        
            Swal.fire({
                title: 'Are you sure you want to reject this professional?',
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
                        url: 'reject_professional.php',
                        method: 'GET',
                        data: {
                            professionalId: applicationId,
                            projectId: projectId,
                            projectNo: projectNo,
                            reject_remark: remark
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Rejected!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
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
                    Swal.fire('Cancelled', 'Professional rejection has been cancelled.', 'info');
                }
            });
        });
    </script>
</html>
