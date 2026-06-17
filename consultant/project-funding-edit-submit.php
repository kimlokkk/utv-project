<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';

header('Content-Type: application/json');

function pfa_edit_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pfa_edit_response(false, 'Invalid request method.');
}

$userData = $_SESSION['user_data'];
$user_id = mysqli_real_escape_string($db, $userData['id']);

if (
    empty($_POST['application_id']) ||
    empty($_POST['expected_payment_date']) ||
    $_POST['expected_payment_amount'] === '' ||
    empty($_POST['pfa_number']) ||
    $_POST['total_previous_pfa_applied'] === ''
) {
    pfa_edit_response(false, 'Please complete all project funding assistance details.');
}

$application_id = mysqli_real_escape_string($db, $_POST['application_id']);

$app_query = "
    SELECT pfa.*, p.project_no, p.leader_id
    FROM project_funding_assistance_applications pfa
    INNER JOIN project p ON p.id = pfa.project_id
    LEFT JOIN project_members_consultant pmc ON p.id = pmc.project_id
    WHERE pfa.id = '$application_id'
      AND (p.leader_id = '$user_id' OR pmc.member_id = '$user_id')
      AND (pfa.status LIKE '%Returned%' OR pfa.status LIKE '%Rejected%')
      AND pfa.return_to = 'Consultant'
    LIMIT 1
";
$app_result = mysqli_query($db, $app_query);

if (!$app_result || mysqli_num_rows($app_result) === 0) {
    pfa_edit_response(false, 'This project funding assistance application cannot be edited.');
}

$application = mysqli_fetch_assoc($app_result);

$expected_payment_date = mysqli_real_escape_string($db, $_POST['expected_payment_date']);
$expected_payment_amount = number_format((float)$_POST['expected_payment_amount'], 2, '.', '');
$pfa_number = (int)$_POST['pfa_number'];
$total_previous_pfa_applied = number_format((float)$_POST['total_previous_pfa_applied'], 2, '.', '');
$categories = $_POST['category'] ?? [];
$items = $_POST['item'] ?? [];
$quantities = $_POST['quantity'] ?? [];
$amounts = $_POST['amount'] ?? [];

if ($pfa_number < 1 || (float)$expected_payment_amount < 0 || (float)$total_previous_pfa_applied < 0) {
    pfa_edit_response(false, 'Invalid project funding assistance amount or PFA number.');
}

$valid_items = [];
$total_item_amount = 0;
for ($i = 0; $i < count($categories); $i++) {
    $category = trim((string)($categories[$i] ?? ''));
    $item = trim((string)($items[$i] ?? ''));
    $quantity = (int)($quantities[$i] ?? 0);
    $amount = (float)($amounts[$i] ?? 0);

    if ($category === '' && $item === '' && $quantity === 0 && $amount == 0) {
        continue;
    }

    if ($category === '' || $item === '' || $quantity < 1 || $amount < 0) {
        pfa_edit_response(false, 'Please complete all item rows correctly.');
    }

    $total_item_amount += $amount;
    $valid_items[] = [$category, $item, $quantity, number_format($amount, 2, '.', '')];
}

if (empty($valid_items)) {
    pfa_edit_response(false, 'Please add at least one PFA item.');
}

if ($total_item_amount > (float)$expected_payment_amount) {
    pfa_edit_response(false, 'Total item amount (RM ' . number_format($total_item_amount, 2) . ') must not be more than the expected payment from client (RM ' . number_format((float)$expected_payment_amount, 2) . ').');
}

$next_status = ((string)$application['leader_id'] === (string)$user_id) ? 'Pending Verification' : 'Pending approval from project leader';
$date_now = date('Y-m-d H:i:s');

mysqli_begin_transaction($db);

try {
    $update_query = "
        UPDATE project_funding_assistance_applications
        SET expected_payment_date = '$expected_payment_date',
            expected_payment_amount = '$expected_payment_amount',
            pfa_number = '$pfa_number',
            total_previous_pfa_applied = '$total_previous_pfa_applied',
            status = '$next_status',
            return_to = '',
            return_remark = ''
        WHERE id = '$application_id'
    ";

    if (!mysqli_query($db, $update_query)) {
        throw new Exception('Failed to update project funding assistance application: ' . mysqli_error($db));
    }

    if (!mysqli_query($db, "DELETE FROM project_funding_assistance_items WHERE application_id = '$application_id'")) {
        throw new Exception('Failed to clear previous PFA items: ' . mysqli_error($db));
    }

    foreach ($valid_items as $valid_item) {
        $category = mysqli_real_escape_string($db, $valid_item[0]);
        $item = mysqli_real_escape_string($db, $valid_item[1]);
        $quantity = mysqli_real_escape_string($db, $valid_item[2]);
        $amount = mysqli_real_escape_string($db, $valid_item[3]);

        $item_query = "
            INSERT INTO project_funding_assistance_items (application_id, category, item, quantity, amount, date_create)
            VALUES ('$application_id', '$category', '$item', '$quantity', '$amount', '$date_now')
        ";

        if (!mysqli_query($db, $item_query)) {
            throw new Exception('Failed to save PFA item: ' . mysqli_error($db));
        }
    }

    $project_id = mysqli_real_escape_string($db, $application['project_id']);
    $project_no = mysqli_real_escape_string($db, $application['project_no']);
    $tracker_remark = mysqli_real_escape_string($db, 'Project funding assistance application has been edited and resubmitted by consultant.');
    mysqli_query($db, "INSERT INTO project_tracker (project_id, project_no, remark, date) VALUES ('$project_id', '$project_no', '$tracker_remark', '$date_now')");

    mysqli_commit($db);
    pfa_edit_response(true, 'Project funding assistance application resubmitted successfully.');
} catch (Exception $e) {
    mysqli_rollback($db);
    pfa_edit_response(false, $e->getMessage());
}
?>
