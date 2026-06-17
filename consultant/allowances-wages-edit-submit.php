<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';

header('Content-Type: application/json');

function allowance_edit_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function allowance_edit_payable_period($start_date, $month_no) {
    $start = DateTime::createFromFormat('Y-m-d', $start_date);
    if (!$start || $month_no < 1) {
        return [null, null, null];
    }

    $period_start = clone $start;
    $period_start->modify('+' . ($month_no - 1) . ' month');

    $period_end = clone $start;
    $period_end->modify('+' . $month_no . ' month');
    $period_end->modify('-1 day');

    $month_label = $period_start->format('F Y');

    return [
        $period_start->format('Y-m-d'),
        $period_end->format('Y-m-d'),
        $month_label
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    allowance_edit_response(false, 'Invalid request method.');
}

$userData = $_SESSION['user_data'];
$user_id = mysqli_real_escape_string($db, $userData['id']);

if (empty($_POST['application_id']) || empty($_POST['application_for'])) {
    allowance_edit_response(false, 'Incomplete form data.');
}

$application_id = mysqli_real_escape_string($db, $_POST['application_id']);
$application_for = mysqli_real_escape_string($db, $_POST['application_for']);

$app_query = "
    SELECT aa.*, p.project_no, p.leader_id
    FROM allowance_applications aa
    INNER JOIN project p ON p.id = aa.project_id
    WHERE aa.id = '$application_id'
      AND p.leader_id = '$user_id'
      AND aa.status LIKE '%Rejected%'
      AND aa.return_to = 'Consultant'
    LIMIT 1
";
$app_result = mysqli_query($db, $app_query);

if (!$app_result || mysqli_num_rows($app_result) === 0) {
    allowance_edit_response(false, 'This allowance/wages application cannot be edited.');
}

$application = mysqli_fetch_assoc($app_result);

if ($application_for === 'Research assistant allowance') {
    if (empty($_POST['allowance_month_no']) || empty($_POST['job_description'])) {
        allowance_edit_response(false, 'Please complete the RA allowance details.');
    }

    $ra_application_id = mysqli_real_escape_string($db, $application['ra_application_id']);
    $allowance_month_no = (int)$_POST['allowance_month_no'];
    $job_description = mysqli_real_escape_string($db, $_POST['job_description']);

    $ra_query = "
        SELECT
            raa.start_date,
            raa.end_date,
            raa.duration,
            raa.budget,
            ra.full_name,
            ra.email,
            ra.ic,
            ra.bank_name,
            ra.no_account
        FROM research_assistant_application raa
        INNER JOIN research_assistant ra ON ra.id = raa.ra_id
        WHERE raa.id = '$ra_application_id'
          AND raa.project_id = '{$application['project_id']}'
          AND raa.status = 'Approved'
        LIMIT 1
    ";
    $ra_result = mysqli_query($db, $ra_query);

    if (!$ra_result || mysqli_num_rows($ra_result) === 0) {
        allowance_edit_response(false, 'Unable to retrieve the approved RA appointment.');
    }

    $ra = mysqli_fetch_assoc($ra_result);
    $duration = (int)$ra['duration'];

    if ($allowance_month_no < 1 || ($duration > 0 && $allowance_month_no > $duration)) {
        allowance_edit_response(false, 'Selected allowance month is outside the RA appointment duration.');
    }

    [$period_start, $period_end, $month_label] = allowance_edit_payable_period($ra['start_date'], $allowance_month_no);

    if (!$period_start || !$period_end) {
        allowance_edit_response(false, 'Invalid RA allowance period.');
    }

    $appointment_end = DateTime::createFromFormat('Y-m-d', $ra['end_date']);
    $calculated_end = DateTime::createFromFormat('Y-m-d', $period_end);
    if ($appointment_end && $calculated_end && $calculated_end > $appointment_end) {
        $period_end = $appointment_end->format('Y-m-d');
    }

    $name = mysqli_real_escape_string($db, $ra['full_name']);
    $email = mysqli_real_escape_string($db, $ra['email']);
    $ic = mysqli_real_escape_string($db, $ra['ic']);
    $bank_name = mysqli_real_escape_string($db, $ra['bank_name']);
    $no_account = mysqli_real_escape_string($db, $ra['no_account']);
    $monthly_amount = number_format((float)$ra['budget'], 2, '.', '');

    $update_query = "
        UPDATE allowance_applications
        SET name = '$name',
            email = '$email',
            ic = '$ic',
            bank_name = '$bank_name',
            no_account = '$no_account',
            job_description = '$job_description',
            allowance_start_date = '$period_start',
            allowance_end_date = '$period_end',
            allowance_month = '$month_label',
            allowance_month_no = '$allowance_month_no',
            allowance_monthly_amount = '$monthly_amount',
            total_allowance = '$monthly_amount',
            status = 'Pending Verification',
            return_to = '',
            return_remark = '',
            updated_at = NOW()
        WHERE id = '$application_id'
    ";
} elseif ($application_for === 'Outsider allowance') {
    if (
        empty($_POST['outsider_name']) ||
        empty($_POST['outsider_email']) ||
        empty($_POST['outsider_ic']) ||
        empty($_POST['outsider_bank_name']) ||
        empty($_POST['outsider_bank_account']) ||
        empty($_POST['outsider_job_description']) ||
        empty($_POST['outsider_total_allowance']) ||
        empty($_POST['outsider_start_date']) ||
        empty($_POST['outsider_end_date'])
    ) {
        allowance_edit_response(false, 'Please complete all outsider allowance details.');
    }

    $name = mysqli_real_escape_string($db, $_POST['outsider_name']);
    $email = mysqli_real_escape_string($db, $_POST['outsider_email']);
    $ic = mysqli_real_escape_string($db, $_POST['outsider_ic']);
    $bank_name = mysqli_real_escape_string($db, $_POST['outsider_bank_name']);
    $no_account = mysqli_real_escape_string($db, $_POST['outsider_bank_account']);
    $job_description = mysqli_real_escape_string($db, $_POST['outsider_job_description']);
    $allowance_start_date = mysqli_real_escape_string($db, $_POST['outsider_start_date']);
    $allowance_end_date = mysqli_real_escape_string($db, $_POST['outsider_end_date']);
    $total_allowance = number_format((float)$_POST['outsider_total_allowance'], 2, '.', '');

    if ((float)$total_allowance <= 0) {
        allowance_edit_response(false, 'Total allowance must be greater than 0.');
    }

    if (strtotime($allowance_end_date) < strtotime($allowance_start_date)) {
        allowance_edit_response(false, 'Outsider end date cannot be earlier than start date.');
    }

    $update_query = "
        UPDATE allowance_applications
        SET name = '$name',
            email = '$email',
            ic = '$ic',
            bank_name = '$bank_name',
            no_account = '$no_account',
            job_description = '$job_description',
            allowance_start_date = '$allowance_start_date',
            allowance_end_date = '$allowance_end_date',
            total_allowance = '$total_allowance',
            status = 'Pending Verification',
            return_to = '',
            return_remark = '',
            updated_at = NOW()
        WHERE id = '$application_id'
    ";
} else {
    allowance_edit_response(false, 'Invalid application type.');
}

if (!mysqli_query($db, $update_query)) {
    allowance_edit_response(false, 'Failed to resubmit application: ' . mysqli_error($db));
}

$project_id = mysqli_real_escape_string($db, $application['project_id']);
$project_no = mysqli_real_escape_string($db, $application['project_no']);
$safe_name = mysqli_real_escape_string($db, $application['name']);
$date = date('Y-m-d H:i:s');
$tracker_remark = mysqli_real_escape_string($db, "Allowance/wages application for $safe_name has been edited and resubmitted by project leader.");
mysqli_query($db, "INSERT INTO project_tracker (project_id, project_no, remark, date) VALUES ('$project_id', '$project_no', '$tracker_remark', '$date')");

allowance_edit_response(true, 'Application resubmitted successfully.');
?>
