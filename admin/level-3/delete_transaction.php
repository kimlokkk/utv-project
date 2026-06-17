<?php
session_start();
include 'auth_check.php';
include '../../db_connect/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = mysqli_real_escape_string($db, $_POST['id']);

    $delete_query = "DELETE FROM project_ledger WHERE id = '$id'";
    if (mysqli_query($db, $delete_query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Transaction deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete transaction: ' . mysqli_error($db)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
}
?>
