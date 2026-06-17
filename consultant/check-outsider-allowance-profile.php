<?php
session_start();
include '../db_connect/db_connect.php';

header('Content-Type: application/json');

function outsider_profile_response($data) {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    outsider_profile_response(['exists' => false]);
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$ic = isset($_POST['ic']) ? trim($_POST['ic']) : '';

if ($email === '' && $ic === '') {
    outsider_profile_response(['exists' => false]);
}

$columns = [];
$column_result = mysqli_query($db, "SHOW COLUMNS FROM allowance_applications");
if ($column_result) {
    while ($column = mysqli_fetch_assoc($column_result)) {
        $columns[] = $column['Field'];
    }
}

if (!in_array('outsider_ic_file', $columns) || !in_array('outsider_bank_statement_file', $columns)) {
    outsider_profile_response([
        'exists' => false,
        'table_ready' => false
    ]);
}

$conditions = [];

if ($email !== '') {
    $safe_email = mysqli_real_escape_string($db, $email);
    $conditions[] = "email = '$safe_email'";
}

if ($ic !== '') {
    $safe_ic = mysqli_real_escape_string($db, $ic);
    $conditions[] = "ic = '$safe_ic'";
}

$query = "
    SELECT 
        name,
        email,
        ic,
        bank_name,
        no_account,
        outsider_ic_file,
        outsider_bank_statement_file
    FROM allowance_applications
    WHERE application_for = 'Outsider allowance'
    AND (" . implode(' OR ', $conditions) . ")
    AND outsider_ic_file IS NOT NULL
    AND outsider_ic_file <> ''
    AND outsider_bank_statement_file IS NOT NULL
    AND outsider_bank_statement_file <> ''
    ORDER BY id DESC
    LIMIT 1
";

$result = mysqli_query($db, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    outsider_profile_response([
        'exists' => false,
        'table_ready' => true
    ]);
}

$profile = mysqli_fetch_assoc($result);

outsider_profile_response([
    'exists' => true,
    'table_ready' => true,
    'name' => $profile['name'],
    'email' => $profile['email'],
    'ic' => $profile['ic'],
    'bank_name' => $profile['bank_name'],
    'no_account' => $profile['no_account']
]);
?>
