<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa
?>
<?php
    // Query untuk mendapatkan semua allowance applications yang dibuat oleh pengguna
    $query = "SELECT 
                    p.id,
                    p.leader_id,
                    aa.id AS allowance_id
                FROM project p
                INNER JOIN allowance_applications aa ON p.id = aa.project_id
                LEFT JOIN project_members_consultant pmc ON p.id = pmc.project_id
                WHERE p.leader_id = '$user_id' OR pmc.member_id = '$user_id'
                GROUP BY aa.id
                ORDER BY allowance_id DESC
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
                        <h4 class="text-themecolor">Allowances/Wages Application</h4>
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
                                <?php if ($hasApplications) { ?>
                                    <!-- Table to Display Procurement Applications -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="professionalTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Project Number</th>
                                                    <th>Project Title</th>
                                                    <th>Name</th>
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
                                                        aa.id AS allowance_id,
                                                        aa.name,
                                                        aa.return_to
                                                    FROM project p
                                                    INNER JOIN allowance_applications aa ON p.id = aa.project_id
                                                    LEFT JOIN project_members_consultant pmc ON p.id = pmc.project_id
                                                    WHERE p.leader_id = '$user_id' OR pmc.member_id = '$user_id'
                                                    GROUP BY aa.id
                                                    ORDER BY aa.id DESC
                                                    ";
                                    
                                                $result2 = mysqli_query($db, $query2);
                                    
                                                if (mysqli_num_rows($result2) > 0) {
                                                    while ($row = mysqli_fetch_array($result2)) {
                                                        $project_id = $row['project_id'];
                                                        $project_no = $row['project_no'];
                                                        $project_title = $row['project_title'];
                                                        $status = $row['status'];
                                                        $application_id = $row['allowance_id'];
                                                        $name = $row['name'];
                                                        $return_to = trim((string)($row['return_to'] ?? ''));
                                                        $isInternalLevel3Review = stripos($status, 'Rejected') !== false && strcasecmp($return_to, 'Level 3') === 0;
                                                        $displayStatus = $isInternalLevel3Review ? 'Pending Level 3 Review' : $status;
                                                ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td><?php echo htmlspecialchars($project_no); ?></td>
                                                    <td><?php echo htmlspecialchars($project_title); ?></td>
                                                    <td><?php echo htmlspecialchars($name); ?></td>
                                                    <td>
                                                        <?php
                                                        $statusText = htmlspecialchars($displayStatus);
                                                        $statusClass = 'badge-secondary'; // default
                                                    
                                                        if ($isInternalLevel3Review) {
                                                            $statusClass = 'badge-warning';
                                                        } elseif (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
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
                                                    <td class="text-center">
                                                        <a href="javascript:void(0);" 
                                                           class="btn btn-info btn-sm viewDetails" 
                                                           data-application-id="<?php echo urlencode($application_id); ?>">
                                                           View Details
                                                        </a>
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
                                                            id="appproveAllowance"
                                                            data-project-id="<?php echo urlencode($project_id); ?>" 
                                                            data-project-no="<?php echo urlencode($project_no); ?>"
                                                            data-member-name="<?php echo urlencode($name); ?>"
                                                            data-application-id="<?php echo urlencode($application_id); ?>"
                                                            <?php echo $allowApprove ? '' : 'disabled'; ?>>
                                                            Approve
                                                        </button>
                                                        <?php if (!$isInternalLevel3Review && stripos($status, 'Rejected') !== false && strcasecmp($return_to, 'Consultant') === 0) { ?>
                                                            <a href="allowances-wages-edit.php?id=<?php echo urlencode($application_id); ?>" class="btn btn-warning btn-sm">
                                                                Edit
                                                            </a>
                                                        <?php } ?>
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
                                    <!-- Button to Create New Procurement Application -->
                                    <div class="text-center mt-4">
                                        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#selectProjectModal">Create New Allowances/Wages Application</button>
                                    </div>
                                <?php } else { ?>
                                    <!-- No Data Message -->
                                    <div class="text-center py-5">
                                        <h4>No Allowances/Wages Applications Found</h4>
                                        <p>You have not created any professional fee applications yet.</p>
                                        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#selectProjectModal">Create New Allowances/Wages Application</button>
                                    </div>
                                <?php } ?>
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
                                            $projectQuery = "SELECT DISTINCT 
                                                                p.id, 
                                                                p.project_no, 
                                                                p.project_title
                                                            FROM project p
                                                            LEFT JOIN project_members_consultant pmc ON p.id = pmc.project_id
                                                            WHERE p.leader_id = '{$userData['id']}' OR pmc.member_id = '{$userData['id']}'
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
                                <button type="button" class="btn btn-primary" id="proceedToAllowanceApplication">Proceed</button>
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
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script>
        $(function () {
            $('#projectSelect').select2({
                dropdownParent: $('#selectProjectModal'),
                placeholder: 'Search and select a project',
                width: '100%'
            });
        });
    </script>
    <script>
        // Handle "Proceed" button click in the modal
        document.getElementById('proceedToAllowanceApplication').addEventListener('click', function () {
            const projectId = document.getElementById('projectSelect').value;
            if (projectId) {
                window.location.href = `allowances-wages-application.php?projectId=${encodeURIComponent(projectId)}`;
            } else {
                alert('Please select a project.');
            }
        });
    </script>
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
        $(document).on('click', '#appproveAllowance', function () {
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
                    let memberName = $(this).data('member-name');
                    const projectId = $(this).data('project-id');
                    const projectNo = $(this).data('project-no');
    
                    // Gantikan simbol '+' dengan '%20' dalam nama
                    memberName = memberName.replace(/\+/g, ' ');
    
                    // Debug data yang akan dihantar
                    console.log("Allowances/Wages ID:", applicationId);
                    console.log("Name:", decodeURIComponent(memberName)); // Untuk debug selepas replace
                    console.log("Project ID:", projectId);
                    console.log("Project No:", projectNo);
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'submit_allowance.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            applicationId: applicationId,
                            memberName: memberName, // Gunakan nama yang telah diperbaiki
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
                        'Allowance/wages application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</html>
