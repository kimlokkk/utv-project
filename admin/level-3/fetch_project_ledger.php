<?php
session_start();
include 'auth_check.php';
include '../../db_connect/db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['project_id'])) {
    $project_id = mysqli_real_escape_string($db, $_GET['project_id']);

    $query = "SELECT id, created_at AS date, transaction_desc, transaction_type, amount 
              FROM project_ledger WHERE project_id = '$project_id' ORDER BY created_at DESC";

    $result = mysqli_query($db, $query);
    $ledger_data = [];
    $counter = 1;

    $total_debit = 0;
    $total_credit = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $debit = ($row['transaction_type'] == 'Debit') ? number_format($row['amount'], 2) : "-";
        $credit = ($row['transaction_type'] == 'Credit') ? number_format($row['amount'], 2) : "-";

        if ($row['transaction_type'] == 'Debit') {
            $total_debit += $row['amount'];
        } else {
            $total_credit += $row['amount'];
        }

        $ledger_data[] = [
            "no" => $counter++,
            "transaction_date" => date("d-m-Y", strtotime($row['date'])),
            "transaction_desc" => htmlspecialchars($row['transaction_desc']),
            "debit" => $debit,
            "credit" => $credit,
            "action" => "<button class='btn btn-danger btn-sm delete-transaction' data-id='{$row['id']}'><i class='material-icons'>delete</i></button>"
        ];
    }

    $balance = $total_debit - $total_credit;

    $ledger_data[] = [
        "no" => "",
        "transaction_date" => "",
        "transaction_desc" => "<strong>Total</strong>",
        "debit" => "<strong>RM " . number_format($total_debit, 2) . "</strong>",
        "credit" => "<strong>RM " . number_format($total_credit, 2) . "</strong>",
        "action" => ""
    ];

    $ledger_data[] = [
        "no" => "",
        "transaction_date" => "",
        "transaction_desc" => "<strong>Balance</strong>",
        "debit" => "",
        "credit" => "<strong>RM " . number_format($balance, 2) . "</strong>",
        "action" => ""
    ];

    echo json_encode(["data" => $ledger_data]);
    exit;
} else {
    echo json_encode(["data" => []]);
    exit;
}
