<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';

header('Content-Type: application/json');

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $error['message']]);
    }
});

function consultant_ra_edit_column_exists($db, $column) {
    $column = mysqli_real_escape_string($db, $column);
    $result = mysqli_query($db, "SHOW COLUMNS FROM research_assistant_application LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function consultant_ra_edit_ensure_end_date_column($db) {
    if (consultant_ra_edit_column_exists($db, 'end_date')) {
        return true;
    }

    return mysqli_query($db, "ALTER TABLE research_assistant_application ADD COLUMN end_date date DEFAULT NULL AFTER start_date");
}

function consultant_ra_edit_payable_months($start_date, $end_date) {
    $start = DateTime::createFromFormat('Y-m-d', $start_date);
    $end = DateTime::createFromFormat('Y-m-d', $end_date);

    if (!$start || !$end || $end < $start) {
        return 0;
    }

    $months = (((int)$end->format('Y') - (int)$start->format('Y')) * 12) + ((int)$end->format('n') - (int)$start->format('n'));

    if ((int)$end->format('j') > (int)$start->format('j')) {
        $months++;
    }

    if (
        $months === 0 &&
        (int)$start->format('Y') === (int)$end->format('Y') &&
        (int)$start->format('n') === (int)$end->format('n')
    ) {
        $months = 1;
    }

    return $months;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$required = ['raa_id', 'start_date', 'end_date', 'payment_type', 'budget'];
foreach ($required as $field) {
    if (trim((string)($_POST[$field] ?? '')) === '') {
        echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']);
        exit;
    }
}

$userData = $_SESSION['user_data'];
$user_id = mysqli_real_escape_string($db, $userData['id']);
$raa_id = mysqli_real_escape_string($db, $_POST['raa_id']);
$start_date_raw = $_POST['start_date'];
$end_date_raw = $_POST['end_date'];
$payment_type = mysqli_real_escape_string($db, $_POST['payment_type']);
$budget_value = (float)$_POST['budget'];
$duration = consultant_ra_edit_payable_months($start_date_raw, $end_date_raw);

if ($duration <= 0) {
    echo json_encode(['success' => false, 'message' => 'End Date must be on or after Start Date.']);
    exit;
}

if ($budget_value <= 0) {
    echo json_encode(['success' => false, 'message' => 'Monthly allowance/wage must be greater than 0.']);
    exit;
}

if (!consultant_ra_edit_ensure_end_date_column($db)) {
    echo json_encode(['success' => false, 'message' => 'Unable to prepare End Date storage: ' . mysqli_error($db)]);
    exit;
}

$application_query = "
    SELECT raa.id, raa.name, raa.status, raa.return_to, raa.project_id, p.project_no
    FROM research_assistant_application raa
    INNER JOIN project p ON raa.project_id = p.id
    WHERE raa.id = '$raa_id'
      AND p.leader_id = '$user_id'
    LIMIT 1
";
$application_result = mysqli_query($db, $application_query);

if (!$application_result || mysqli_num_rows($application_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found or you do not have access.']);
    exit;
}

$application = mysqli_fetch_assoc($application_result);
$status = (string)$application['status'];
$return_to = trim((string)($application['return_to'] ?? ''));

if (stripos($status, 'Rejected') === false && stripos($status, 'Returned') === false) {
    echo json_encode(['success' => false, 'message' => 'Only rejected or returned applications can be edited and resubmitted.']);
    exit;
}

if (strcasecmp($return_to, 'Level 3') === 0) {
    echo json_encode(['success' => false, 'message' => 'This application is pending Level 3 review and cannot be edited by consultant yet.']);
    exit;
}

$start_date = mysqli_real_escape_string($db, $start_date_raw);
$end_date = mysqli_real_escape_string($db, $end_date_raw);
$budget = number_format($budget_value, 2, '.', '');
$project_id = mysqli_real_escape_string($db, $application['project_id']);
$project_no = mysqli_real_escape_string($db, $application['project_no']);
$name = mysqli_real_escape_string($db, $application['name']);
$date = date('Y-m-d H:i:s');

mysqli_begin_transaction($db);

try {
    $update_query = "
        UPDATE research_assistant_application
        SET start_date = '$start_date',
            end_date = '$end_date',
            duration = '$duration',
            payment_type = '$payment_type',
            budget = '$budget',
            status = 'Pending Verification',
            return_to = '',
            return_remark = ''
        WHERE id = '$raa_id'
    ";

    if (!mysqli_query($db, $update_query)) {
        throw new Exception('Failed to update RA/RO application: ' . mysqli_error($db));
    }

    $tracker_query = "
        INSERT INTO project_tracker (project_id, project_no, remark, date)
        VALUES ('$project_id', '$project_no', 'RA/RO application for $name has been edited and resubmitted by consultant.', '$date')
    ";

    if (!mysqli_query($db, $tracker_query)) {
        throw new Exception('Failed to insert project tracker record: ' . mysqli_error($db));
    }

    mysqli_commit($db);
    echo json_encode(['success' => true, 'message' => 'RA/RO application updated and resubmitted successfully.']);
} catch (Exception $e) {
    mysqli_rollback($db);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
