<?php
session_start();
include '../db_connect/db_connect.php';
include '../function/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve common fields
    $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
    $expected_payment_date = mysqli_real_escape_string($db, $_POST['expected_payment_date']);
    $expected_payment_amount = mysqli_real_escape_string($db, $_POST['expected_payment_amount']);
    $pfa_number = mysqli_real_escape_string($db, $_POST['pfa_number']);
    $total_previous_pfa_applied = mysqli_real_escape_string($db, $_POST['total_previous_pfa_applied']);
    $date_now = date('Y-m-d H:i:s');

    // Retrieve dynamic item fields
    $categories = $_POST['category'] ?? [];
    $items = $_POST['item'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $amounts = $_POST['amount'] ?? [];

    if (
        empty($project_id) ||
        empty($expected_payment_date) ||
        $expected_payment_amount === '' ||
        empty($pfa_number) ||
        $total_previous_pfa_applied === ''
    ) {
        echo json_encode(['success' => false, 'message' => 'Please complete all project funding assistance details.']);
        exit;
    }

    $total_item_amount = 0;
    foreach ($amounts as $amount) {
        $total_item_amount += (float) $amount;
    }

    if ($total_item_amount > (float) $expected_payment_amount) {
        echo json_encode([
            'success' => false,
            'message' => 'Total item amount (RM ' . number_format($total_item_amount, 2) . ') must not be more than the expected payment from client (RM ' . number_format((float) $expected_payment_amount, 2) . ').'
        ]);
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($db);

    try {
        // Step 1: Insert main application
        $query = "INSERT INTO project_funding_assistance_applications (
                    project_id, expected_payment_date, expected_payment_amount, pfa_number, total_previous_pfa_applied, status, created_at, return_to, return_remark
                  ) VALUES (
                    '$project_id', '$expected_payment_date', '$expected_payment_amount', '$pfa_number', '$total_previous_pfa_applied', 'Pending approval from project leader', '$date_now', '', ''
                  )";
        $result = mysqli_query($db, $query);

        if (!$result) {
            throw new Exception("Failed to insert project funding application: " . mysqli_error($db));
        }

        $application_id = mysqli_insert_id($db);

        // Step 2: Insert each item
        $count = count($categories);
        for ($i = 0; $i < $count; $i++) {
            $cat = mysqli_real_escape_string($db, $categories[$i]);
            $item = mysqli_real_escape_string($db, $items[$i]);
            $qty = mysqli_real_escape_string($db, $quantities[$i]);
            $amount = mysqli_real_escape_string($db, $amounts[$i]);

            // Only insert if there's at least one value
            if (!empty($cat) || !empty($item) || !empty($qty) || !empty($amount)) {
                $item_query = "INSERT INTO project_funding_assistance_items (
                                application_id, category, item, quantity, amount, date_create
                              ) VALUES (
                                '$application_id', '$cat', '$item', '$qty', '$amount', '$date_now'
                              )";
                if (!mysqli_query($db, $item_query)) {
                    throw new Exception("Failed to insert item: " . mysqli_error($db));
                }
            }
        }

        // Commit if all OK
        mysqli_commit($db);
        echo json_encode(['success' => true, 'message' => 'Project funding assistance application submitted successfully.']);

    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
