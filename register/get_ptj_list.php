<?php
header('Content-Type: application/json; charset=utf-8');
include '../db_connect/db_connect.php';

$response = [
    "status" => "error",
    "message" => "Unable to fetch PTJ list.",
    "data" => []
];

try {
    $sql = "SELECT ptj_id, ptj_name
            FROM ptj_list
            WHERE is_active = 1
            ORDER BY ptj_name ASC";

    $result = mysqli_query($db, $sql);

    if (!$result) {
        throw new Exception(mysqli_error($db));
    }

    $ptj = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ptj[] = $row;
    }

    $response["status"] = "success";
    $response["message"] = "PTJ list fetched successfully.";
    $response["data"] = $ptj;

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);