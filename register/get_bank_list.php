<?php
header('Content-Type: application/json; charset=utf-8');
include '../db_connect/db_connect.php';

$response = [
    "status" => "error",
    "message" => "Unable to fetch bank list.",
    "data" => []
];

try {
    $sql = "SELECT bank_id, bank_name, account_length_rule, min_length, max_length
            FROM bank_list
            WHERE is_active = 1
            ORDER BY bank_name ASC";
    $result = mysqli_query($db, $sql);

    if (!$result) {
        throw new Exception(mysqli_error($db));
    }

    $banks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $banks[] = $row;
    }

    $response["status"] = "success";
    $response["message"] = "Bank list fetched successfully.";
    $response["data"] = $banks;

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);