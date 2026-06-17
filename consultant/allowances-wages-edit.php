<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';
include '../function/function.php';

$userData = $_SESSION['user_data'];
$user_id = mysqli_real_escape_string($db, $userData['id']);

if (empty($_GET['id'])) {
    echo '<script>alert("Invalid application."); window.location.href = "allowances-wages.php";</script>';
    exit;
}

$application_id = mysqli_real_escape_string($db, $_GET['id']);

$query = "
    SELECT
        aa.*,
        p.project_no,
        p.project_title,
        p.project_leader,
        p.leader_id,
        raa.start_date AS appointment_start_date,
        raa.end_date AS appointment_end_date,
        raa.duration AS appointment_duration,
        raa.budget AS appointment_budget
    FROM allowance_applications aa
    INNER JOIN project p ON p.id = aa.project_id
    LEFT JOIN research_assistant_application raa ON raa.id = aa.ra_application_id
    WHERE aa.id = '$application_id'
      AND p.leader_id = '$user_id'
      AND aa.status LIKE '%Rejected%'
      AND aa.return_to = 'Consultant'
    LIMIT 1
";
$result = mysqli_query($db, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<script>alert("This application cannot be edited."); window.location.href = "allowances-wages.php";</script>';
    exit;
}

$application = mysqli_fetch_assoc($result);

function allowance_edit_date($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date('d M Y', $timestamp) : '-';
}

function allowance_edit_period_options($start_date, $end_date, $duration, $selected_month_no) {
    $start = DateTime::createFromFormat('Y-m-d', $start_date);
    $end = DateTime::createFromFormat('Y-m-d', $end_date);
    $duration = (int)$duration;

    if (!$start || !$end || $duration < 1) {
        return '<option value="" disabled selected>No allowance month available</option>';
    }

    $html = '<option value="" disabled>Select month</option>';

    for ($i = 1; $i <= $duration; $i++) {
        $period_start = clone $start;
        $period_start->modify('+' . ($i - 1) . ' month');

        $period_end = clone $start;
        $period_end->modify('+' . $i . ' month');
        $period_end->modify('-1 day');

        if ($period_end > $end || $i === $duration) {
            $period_end = clone $end;
        }

        $month_label = $period_start->format('F Y');
        $label = 'Month ' . $i . ' - ' . $month_label . ' (' . $period_start->format('d M Y') . ' to ' . $period_end->format('d M Y') . ')';
        $selected = (int)$selected_month_no === $i ? ' selected' : '';
        $html .= '<option value="' . $i . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
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
                        <h4 class="text-themecolor">Edit Allowance/Wages Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="allowances-wages.php">Allowance/Wages</a></li>
                            <li class="breadcrumb-item active">Edit Application</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <form id="allowanceEditForm">
                            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id']); ?>">
                            <input type="hidden" name="application_for" value="<?php echo htmlspecialchars($application['application_for']); ?>">

                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Details</h3>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        This application was rejected by Level 3. Update the details and resubmit it for verification.
                                        <?php if (!empty($application['return_remark'])) { ?>
                                            <br><strong>Remark:</strong> <?php echo nl2br(htmlspecialchars($application['return_remark'])); ?>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Number</strong></label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($application['project_no']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Title</strong></label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($application['project_title']); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Application For</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($application['application_for']); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <?php if ($application['application_for'] === 'Research assistant allowance') { ?>
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Research Assistant Allowance</h3>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>RA Name</strong></label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($application['name']); ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>RA Email</strong></label>
                                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($application['email']); ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Appointment Start</strong></label>
                                                    <input type="text" class="form-control" value="<?php echo allowance_edit_date($application['appointment_start_date']); ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Appointment End</strong></label>
                                                    <input type="text" class="form-control" value="<?php echo allowance_edit_date($application['appointment_end_date']); ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Monthly Allowance/Wage (RM)</strong></label>
                                                    <input type="text" class="form-control" value="<?php echo number_format((float)$application['appointment_budget'], 2); ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Allowance Month Applied</strong></label>
                                            <select name="allowance_month_no" class="form-control" required>
                                                <?php echo allowance_edit_period_options($application['appointment_start_date'], $application['appointment_end_date'], $application['appointment_duration'], $application['allowance_month_no']); ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Job Description</strong></label>
                                            <textarea name="job_description" class="form-control" required><?php echo htmlspecialchars($application['job_description']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Outsider Allowance</h3>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Name</strong></label>
                                                    <input type="text" name="outsider_name" class="form-control" value="<?php echo htmlspecialchars($application['name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Email Address</strong></label>
                                                    <input type="email" name="outsider_email" class="form-control" value="<?php echo htmlspecialchars($application['email']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>IC Number</strong></label>
                                                    <input type="text" name="outsider_ic" class="form-control" value="<?php echo htmlspecialchars($application['ic']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Total Allowance (RM)</strong></label>
                                                    <input type="number" step="0.01" min="0.01" name="outsider_total_allowance" class="form-control" value="<?php echo htmlspecialchars($application['total_allowance']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Start Date</strong></label>
                                                    <input type="date" name="outsider_start_date" class="form-control" value="<?php echo htmlspecialchars($application['allowance_start_date']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>End Date</strong></label>
                                                    <input type="date" name="outsider_end_date" class="form-control" value="<?php echo htmlspecialchars($application['allowance_end_date']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Bank Name</strong></label>
                                                    <input type="text" name="outsider_bank_name" class="form-control" value="<?php echo htmlspecialchars($application['bank_name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Bank Account Number</strong></label>
                                                    <input type="text" name="outsider_bank_account" class="form-control" value="<?php echo htmlspecialchars($application['no_account']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Job Description</strong></label>
                                            <textarea name="outsider_job_description" class="form-control" required><?php echo htmlspecialchars($application['job_description']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="button" id="submitAllowanceEdit" class="btn btn-lg btn-success">Resubmit</button>
                                    <a href="allowances-wages.php" class="btn btn-lg btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
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
        $('#submitAllowanceEdit').on('click', function () {
            const $button = $(this);
            const formData = new FormData($('#allowanceEditForm')[0]);

            Swal.fire({
                title: 'Resubmit Application?',
                text: 'This will send the corrected allowance/wages application back to Level 3.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, resubmit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    $.ajax({
                        url: 'allowances-wages-edit-submit.php',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        timeout: 30000,
                        beforeSend: function () {
                            $button.prop('disabled', true).text('Submitting...');
                            Swal.fire({
                                title: 'Submitting Application',
                                text: 'Please wait while the corrected application is submitted.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => Swal.showLoading(),
                                onOpen: () => Swal.showLoading()
                            });
                        },
                        success: function (response) {
                            if (response && response.success) {
                                Swal.fire('Submitted!', response.message, 'success').then(() => {
                                    window.location.href = 'allowances-wages.php';
                                });
                            } else {
                                Swal.fire('Failed!', response && response.message ? response.message : 'Unable to resubmit application.', 'error');
                            }
                        },
                        error: function (xhr, status) {
                            const message = status === 'timeout'
                                ? 'Submission timed out. Please try again.'
                                : 'An error occurred during submission.';
                            Swal.fire('Error!', message, 'error');
                        },
                        complete: function () {
                            $button.prop('disabled', false).text('Resubmit');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
