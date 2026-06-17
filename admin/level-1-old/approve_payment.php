<?php
session_start();
include '../db_connect/db_connect.php';
include 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'])) {
    $payment_id = mysqli_real_escape_string($db, $_POST['payment_id']);

    // Check kalau payment tu wujud
    $check_query = "SELECT * FROM payments WHERE id = '$payment_id' AND status = 'pending'";
    $check_result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update status kepada 'approved'
        $update_query = "UPDATE payments SET status = 'Completed' WHERE id = '$payment_id'";
        if (mysqli_query($db, $update_query)) {
            echo json_encode(["status" => "success", "message" => "Payment has been approve & complete successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to approve payment."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Payment not found or already approved."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
