<?php
    session_start();
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';

    $userData = $_SESSION['user_data'];
    $user_id = mysqli_real_escape_string($db, $userData['id']);

    function consultant_ra_edit_column_exists_page($db, $column) {
        $column = mysqli_real_escape_string($db, $column);
        $result = mysqli_query($db, "SHOW COLUMNS FROM research_assistant_application LIKE '$column'");
        return $result && mysqli_num_rows($result) > 0;
    }

    function consultant_ra_edit_date_value($date) {
        return (!empty($date) && $date !== '0000-00-00') ? $date : '';
    }

    function consultant_ra_edit_fallback_end_date($start_date, $duration) {
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

    if (empty($_GET['id'])) {
        echo '<script>alert("Missing RA/RO application ID."); window.location.href = "ra-listing.php";</script>';
        exit;
    }

    $raa_id = mysqli_real_escape_string($db, $_GET['id']);
    $has_end_date = consultant_ra_edit_column_exists_page($db, 'end_date');
    $select_end_date = $has_end_date ? "raa.end_date," : "NULL AS end_date,";
    $has_return_to = consultant_ra_edit_column_exists_page($db, 'return_to');
    $select_return_to = $has_return_to ? "raa.return_to," : "NULL AS return_to,";

    $query = "
        SELECT 
            raa.id,
            raa.name,
            raa.ic,
            raa.ptj_address,
            raa.expertise,
            raa.start_date,
            $select_end_date
            raa.duration,
            raa.payment_type,
            raa.budget,
            raa.status,
            $select_return_to
            p.project_no,
            p.project_title
        FROM research_assistant_application raa
        INNER JOIN project p ON raa.project_id = p.id
        WHERE raa.id = '$raa_id'
          AND p.leader_id = '$user_id'
        LIMIT 1
    ";
    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<script>alert("Application not found or you do not have access."); window.location.href = "ra-listing.php";</script>';
        exit;
    }

    $application = mysqli_fetch_assoc($result);
    $status = (string)$application['status'];
    $return_to = trim((string)($application['return_to'] ?? ''));

    if (stripos($status, 'Rejected') === false && stripos($status, 'Returned') === false) {
        echo '<script>alert("Only rejected or returned applications can be edited."); window.location.href = "ra-listing.php";</script>';
        exit;
    }

    if (strcasecmp($return_to, 'Level 3') === 0) {
        echo '<script>alert("This application is pending Level 3 review and cannot be edited by consultant yet."); window.location.href = "ra-listing.php";</script>';
        exit;
    }

    $start_date_value = consultant_ra_edit_date_value($application['start_date']);
    $end_date_value = consultant_ra_edit_date_value($application['end_date'] ?? '');
    if ($end_date_value === '') {
        $end_date_value = consultant_ra_edit_fallback_end_date($start_date_value, $application['duration'] ?? 0);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <link rel="stylesheet" type="text/css" href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .edit-card {
            border: 1px solid #e9edf2;
            border-radius: 6px;
            box-shadow: 0 4px 14px rgba(47, 61, 74, 0.06);
        }
        .readonly-field {
            background: #f8fafc;
        }
        .duration-preview,
        .total-preview {
            font-weight: 700;
            color: #00695c;
        }
    </style>
</head>

<body class="skin-blue fixed-layout">
    <?php include 'include/preloader.php'; ?>
    <div id="main-wrapper">
        <?php include 'include/topbar.php'; ?>
        <?php include 'include/left_sidebar.php'; ?>

        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Edit RA/RO Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="ra-listing.php">RA/RO Listing</a></li>
                            <li class="breadcrumb-item active">Edit Application</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card edit-card">
                            <h3 class="card-header bg-info text-white">Edit & Resubmit RA/RO Application</h3>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    This application was returned/rejected. Update the appointment details and resubmit it for Level 3 verification.
                                </div>
                                <form id="editRaForm">
                                    <input type="hidden" name="raa_id" value="<?php echo htmlspecialchars($raa_id); ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label><strong>Project Number</strong></label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['project_no']); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label><strong>Project Title</strong></label>
                                            <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($application['project_title']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label><strong>RA/RO Name</strong></label>
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
                                            <select name="payment_type" class="form-control" required>
                                                <option value="">Select</option>
                                                <option value="Token" <?php echo $application['payment_type'] === 'Token' ? 'selected' : ''; ?>>Token & Allowance</option>
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
                                        <a href="ra-listing.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-success">Update & Resubmit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'include/footer.php'; ?>
        <?php include 'include/logoutmodal.php'; ?>
    </div>

    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="dist/js/waves.js"></script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script>
        function calculatePayableMonths(startDate, endDate) {
            if (!startDate || !endDate) {
                return 0;
            }

            const start = new Date(startDate + 'T00:00:00');
            const end = new Date(endDate + 'T00:00:00');

            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
                return 0;
            }

            let months = ((end.getFullYear() - start.getFullYear()) * 12) + (end.getMonth() - start.getMonth());

            if (end.getDate() > start.getDate()) {
                months++;
            }

            if (months === 0 && start.getFullYear() === end.getFullYear() && start.getMonth() === end.getMonth()) {
                months = 1;
            }

            return months;
        }

        function updateDurationPreview() {
            const months = calculatePayableMonths($('#startDate').val(), $('#endDate').val());
            const budget = parseFloat($('#budget').val()) || 0;

            $('#durationPreview').val(months + ' month(s)');
            $('#totalPreview').val((months * budget).toFixed(2));
            return months;
        }

        $(document).on('change input', '#startDate, #endDate, #budget', updateDurationPreview);
        updateDurationPreview();

        $('#editRaForm').on('submit', function (e) {
            e.preventDefault();

            const months = updateDurationPreview();
            if (months <= 0) {
                Swal.fire('Invalid Period', 'End Date must be on or after Start Date.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Update and resubmit?',
                text: 'This application will be sent back to Level 3 for verification.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, resubmit'
            }).then((result) => {
                if (!(result.isConfirmed || result.value)) {
                    return;
                }

                const submitButton = $('#editRaForm button[type="submit"]');
                const originalButtonText = submitButton.text();

                $.ajax({
                    url: 'ra-application-edit-submit.php',
                    type: 'POST',
                    data: $('#editRaForm').serialize(),
                    dataType: 'json',
                    timeout: 30000,
                    beforeSend: function () {
                        submitButton.prop('disabled', true).text('Submitting...');

                        Swal.fire({
                            title: 'Submitting...',
                            text: 'Please wait while the RA/RO application is being updated and resubmitted.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            onOpen: function () {
                                Swal.showLoading();
                            },
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Submitted!', response.message, 'success').then(() => {
                                window.location.href = 'ra-listing.php';
                            });
                        } else {
                            Swal.fire('Failed!', response.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : (xhr.statusText === 'timeout' ? 'Submission timed out. Please try again.' : 'Something went wrong with the request.');
                        Swal.fire('Error!', message, 'error');
                    },
                    complete: function () {
                        submitButton.prop('disabled', false).text(originalButtonText);
                    }
                });
            });
        });
    </script>
</body>
</html>
