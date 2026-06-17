<?php
session_start(); // Start the session
include 'auth_check.php';
include '../db_connect/db_connect.php';
include '../function/function.php';

$userData = $_SESSION['user_data'];
?>
<?php
$id =   $_GET['projectId'];
$query  = "SELECT * FROM project WHERE id = '$id';";
$result =   mysqli_query($db, $query);
while ($row =   mysqli_fetch_array($result)) {
    $project_leader                 = $row['project_leader'];
    $leader_id                      = $row['leader_id'];
    $project_no                     = $row['project_no'];
    $project_title                  = $row['project_title'];
    $client_name                    = $row['client_company_name'];
    $registered_project_value       = $row['registered_project_value'];
    $project_start                  = $row['project_start'];
    $project_end                    = $row['project_end'];
}

$previous_pfa_count = 0;
$total_previous_pfa_applied = 0;
$previous_pfa_query = "
        SELECT COUNT(DISTINCT pfa.id) AS total_applications,
               COALESCE(SUM(pfai.amount), 0) AS total_applied
        FROM project_funding_assistance_applications pfa
        LEFT JOIN project_funding_assistance_items pfai ON pfa.id = pfai.application_id
        WHERE pfa.project_id = '$id'";
$previous_pfa_result = mysqli_query($db, $previous_pfa_query);
if ($previous_pfa_result && $previous_pfa_row = mysqli_fetch_assoc($previous_pfa_result)) {
    $previous_pfa_count = (int) $previous_pfa_row['total_applications'];
    $total_previous_pfa_applied = (float) $previous_pfa_row['total_applied'];
}
$next_pfa_number = $previous_pfa_count + 1;
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
                        <h4 class="text-themecolor">Project Funding Assistance Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Project Funding Assistance Application</li>
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
                        <form id="projectFundingForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Number</strong></label>
                                                <input type="text" name="project_number" value="<?php echo $project_no; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Title</strong></label>
                                                <input type="text" name="project_title" value="<?php echo $project_title; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Leader Name</strong></label>
                                                <input type="text" name="project_leader" value="<?php echo $project_leader; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Client Name</strong></label>
                                                <input type="text" name="client_name" value="<?php echo $client_name; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- PFA Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Funding Assistance</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label><strong>Expected To Receive Payment From Client Date</strong></label>
                                                <input type="date" name="expected_payment_date" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label><strong>Expected To Receive Payment From Client Amount (RM)</strong></label>
                                                <input type="number" name="expected_payment_amount" class="form-control" required step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label><strong>PFA Application No.</strong></label>
                                                <input type="number" name="pfa_number" class="form-control" required min="1" value="<?php echo $next_pfa_number; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label><strong>Total Previous PFA Applied (RM)</strong></label>
                                                <input type="number" name="total_previous_pfa_applied" class="form-control" required step="0.01" min="0" value="<?php echo number_format($total_previous_pfa_applied, 2, '.', ''); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- PFA Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Item to Apply</h3>
                                <div class="card-body">
                                    <h4 class="mt-3 mb-3">Item PFA To Apply</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="fundingTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 25%;">Category</th>
                                                    <th style="width: 30%;">Item</th>
                                                    <th style="width: 15%;">Quantity</th>
                                                    <th style="width: 20%;">Amount (RM)</th>
                                                    <th style="width: 10%;" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="fundingBody">
                                                <tr>
                                                    <td>
                                                        <select name="category[]" class="form-control" required>
                                                            <option value="" disabled selected>Select</option>
                                                            <option value="Printing">Printing</option>
                                                            <option value="Project Materials/Equipment">Project Materials/Equipment</option>
                                                            <option value="Token">Token</option>
                                                            <option value="Subscription">Subscription</option>
                                                            <option value="Others">Others</option>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" name="item[]" class="form-control" required></td>
                                                    <td><input type="number" name="quantity[]" class="form-control" required min="1"></td>
                                                    <td><input type="number" name="amount[]" class="form-control" required step="0.01" min="0"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-sm removeRow">&times;</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-success btn-sm" id="addFundingRow">+ Add Row</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <?php
                                    $isLeader = ($userData['id'] == $leader_id);
                                    ?>
                                    <button type="button" id="submitToProjectLeader" class="btn btn-lg btn-info" <?php echo $isLeader ? 'disabled' : ''; ?>> Submit to project leader</button>
                                    <button type="button" id="submitByProjectLeader" class="btn btn-lg btn-success" <?php echo !$isLeader ? 'disabled' : ''; ?>> Submit</button>
                                </div>
                            </div>
                        </form>
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
        function getTotalFundingAmount() {
            let total = 0;
            document.querySelectorAll('input[name="amount[]"]').forEach(function(input) {
                total += parseFloat(input.value) || 0;
            });
            return total;
        }

        function validateFundingAmountLimit() {
            const expectedAmountInput = document.querySelector('input[name="expected_payment_amount"]');
            const expectedAmount = parseFloat(expectedAmountInput.value) || 0;
            const totalFundingAmount = getTotalFundingAmount();

            if (totalFundingAmount > expectedAmount) {
                Swal.fire(
                    'Invalid Amount',
                    'Total item amount (RM ' + totalFundingAmount.toFixed(2) + ') must not be more than the expected payment from client (RM ' + expectedAmount.toFixed(2) + ').',
                    'error'
                );
                return false;
            }

            return true;
        }

        document.getElementById('addFundingRow').addEventListener('click', function() {
            const tbody = document.getElementById('fundingBody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>
                    <select name="category[]" class="form-control" required>
                        <option value="" disabled selected>Select</option>
                        <option value="Printing">Printing</option>
                        <option value="Project Materials/Equipment">Project Materials/Equipment</option>
                        <option value="Token">Token</option>
                        <option value="Subscription">Subscription</option>
                        <option value="Others">Others</option>
                    </select>
                </td>
                <td><input type="text" name="item[]" class="form-control" required></td>
                <td><input type="number" name="quantity[]" class="form-control" required min="1"></td>
                <td><input type="number" name="amount[]" class="form-control" required step="0.01" min="0"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm removeRow">&times;</button>
                </td>
            `;

            tbody.appendChild(newRow);
        });

        // Handle row removal
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#othersAmount').on('input', function() {
                const value = $(this).val();
                if (value && parseFloat(value) > 0) {
                    $('#othersDescriptionField').show();
                } else {
                    $('#othersDescriptionField').hide();
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#submitByProjectLeader', function() {
            const form = $('#projectFundingForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (!validateFundingAmountLimit()) {
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Once submit, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons

                if (result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");

                    // Ambil borang menggunakan ID borang
                    const formData = new FormData(form);

                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }

                    Swal.fire({
                        title: 'Submitting...',
                        text: 'Please wait while your project funding assistance application is being submitted.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        onOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'submit_leader_project_funding.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        success: function(response) {
                            console.log("AJAX success response:", response);

                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'project-funding.php'; // Alihkan ke halaman yang diinginkan
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire(
                                'Error!',
                                'An error occurred during submission. Please check the console for details.',
                                'error'
                            );
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Project funding assistance application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#submitToProjectLeader', function() {
            const form = $('#projectFundingForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (!validateFundingAmountLimit()) {
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Once submit, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons

                if (result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");

                    // Ambil borang menggunakan ID borang
                    const formData = new FormData(form);

                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }

                    Swal.fire({
                        title: 'Submitting...',
                        text: 'Please wait while your project funding assistance application is being submitted.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        onOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'process_project_funding.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        success: function(response) {
                            console.log("AJAX success response:", response);

                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'project-funding.php'; // Alihkan ke halaman yang diinginkan
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire(
                                'Error!',
                                'An error occurred during submission. Please check the console for details.',
                                'error'
                            );
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Project funding assistance application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>
