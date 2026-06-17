<?php
    session_start();
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';

    $userData = $_SESSION['user_data'];
    $user_id = $userData['id'];

    function level3_ra_edit_column_exists_page($db, $column) {
        $column = mysqli_real_escape_string($db, $column);
        $result = mysqli_query($db, "SHOW COLUMNS FROM research_assistant_application LIKE '$column'");
        return $result && mysqli_num_rows($result) > 0;
    }

    function level3_ra_edit_date_value($date) {
        return (!empty($date) && $date !== '0000-00-00') ? $date : '';
    }

    function level3_ra_edit_fallback_end_date($start_date, $duration) {
        if (empty($start_date) || (int)$duration <= 0) {
            return '';
        }

        $date = DateTime::createFromFormat('Y-m-d', $start_date);
        if (!$date) {
            return '';
        }

        $date->modify('+' . (int)$duration . ' month');
        return $date->format('Y-m-d');
    }

    if (!isset($_GET['id'])) {
        die('Missing RA Application ID.');
    }

    $raa_id = mysqli_real_escape_string($db, $_GET['id']);

    $has_end_date = level3_ra_edit_column_exists_page($db, 'end_date');
    $select_end_date = $has_end_date ? "end_date" : "NULL AS end_date";

    $query = "SELECT *, $select_end_date FROM research_assistant_application WHERE id = '$raa_id'";
    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        die('Application not found or you do not have access.');
    }

    $application = mysqli_fetch_assoc($result);
    $start_date_value = level3_ra_edit_date_value($application['start_date']);
    $end_date_value = level3_ra_edit_date_value($application['end_date'] ?? '');
    if ($end_date_value === '') {
        $end_date_value = level3_ra_edit_fallback_end_date($start_date_value, $application['duration'] ?? 0);
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
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" />
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .readonly-field {
            background: #f8fafc;
        }
        .duration-preview,
        .total-preview {
            font-weight: 700;
            color: #00695c;
        }
    </style>
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
                        <h4 class="text-themecolor">Edit RA/RO Listing</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">RA/RO Application</a></li>
                                <li class="breadcrumb-item active">Edit RA/RO Application</li>
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
                <!-- RA/RO Form Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Edit RA/RO Application</h4>
                                <form id="editRaForm">
                                    <input type="hidden" name="raa_id" value="<?php echo $raa_id; ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label><strong>RA Name</strong></label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['name']); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label><strong>IC/Passport No</strong></label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['ic']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Faculty / PTJ</label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['ptj_address']); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Expertise</label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['expertise']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Start Date</label>
                                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo htmlspecialchars($start_date_value); ?>" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>End Date</label>
                                            <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo htmlspecialchars($end_date_value); ?>" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Payable Duration</label>
                                            <input type="text" class="form-control readonly-field duration-preview" id="durationPreview" value="0 month(s)" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Payment Type</label>
                                            <select name="payment_type" class="form-control select2">
                                                <option value="">Select</option>
                                                <option value="Token" <?php if($application['payment_type'] === 'Token') echo 'selected'; ?>>Token & Allowance</option>
                                                <option value="Salary" <?php if($application['payment_type'] === 'Salary') echo 'selected'; ?>>Salary</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Monthly Allowance/Wage (RM)</label>
                                            <input type="number" step="0.01" min="0.01" class="form-control" id="budget" name="budget" value="<?php echo htmlspecialchars($application['budget']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Estimated Total (RM)</label>
                                            <input type="text" class="form-control readonly-field total-preview" id="totalPreview" value="0.00" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Status</label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['status']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                    </div>
                                </form>
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
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        function calculatePayableMonths(startDate, endDate) {
            if (!startDate || !endDate) {
                return 0;
            }

            const start = new Date(startDate + 'T00:00:00');
            const end = new Date(endDate + 'T00:00:00');

            if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) {
                return 0;
            }

            const startDay = start.getDate();
            const endDay = end.getDate();
            let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());

            if (months === 0) {
                return 1;
            }

            if (endDay >= startDay) {
                return months;
            }

            return Math.max(1, months);
        }

        function updateDurationPreview() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            const budget = parseFloat($('#budget').val()) || 0;
            const months = calculatePayableMonths(startDate, endDate);
            const total = months * budget;

            $('#durationPreview').val(months ? months + (months === 1 ? ' month' : ' months') : '');
            $('#totalPreview').val(months && budget ? 'RM ' + total.toLocaleString('en-MY', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '');
        }

        $('#startDate, #endDate, #budget').on('change keyup', updateDurationPreview);
        updateDurationPreview();

        $('#editRaForm').submit(function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');

            if (!calculatePayableMonths($('#startDate').val(), $('#endDate').val())) {
                Swal.fire('Invalid Period', 'Please make sure the end date is on or after the start date.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Confirm Changes?',
                text: 'You are about to save changes to this RA application.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    $.ajax({
                        url: 'ra-application-edit-submit.php',
                        type: 'POST',
                        data: $form.serialize(),
                        dataType: 'json',
                        timeout: 30000,
                        beforeSend: function () {
                            $submitBtn.prop('disabled', true).text('Saving...');
                            Swal.fire({
                                title: 'Saving Changes',
                                text: 'Please wait while the application is updated.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                                onOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function (response) {
                            if (response && response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message || 'RA application updated successfully.'
                                }).then(() => {
                                    window.location.href = 'ra-application.php';
                                });
                            } else {
                                Swal.fire('Error', response && response.message ? response.message : 'Unable to update the application.', 'error');
                            }
                        },
                        error: function (xhr, status) {
                            const message = status === 'timeout'
                                ? 'The request timed out. Please try again.'
                                : 'Something went wrong with the request.';
                            Swal.fire('Error', message, 'error');
                        },
                        complete: function () {
                            $submitBtn.prop('disabled', false).text('Save Changes');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
