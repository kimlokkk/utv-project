<?php
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
    $desc = mysqli_real_escape_string($db, $_POST['transaction_desc']);
    $type = mysqli_real_escape_string($db, $_POST['transaction_type']);
    $amount = floatval($_POST['amount']);
    $created_at = date('Y-m-d H:i:s');

    $query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at)
              VALUES ('$project_id', '$desc', '$type', '$amount', '$created_at')";

    if (mysqli_query($db, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($db)]);
    }
}
?>
