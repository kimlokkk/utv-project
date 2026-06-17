<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include 'auth_check.php';
include '../db_connect/db_connect.php';
include '../function/function.php';
include 'reconciliation_save_application.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $application_id = reconciliation_save_application($db, 'Pending leader review');
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully for project leader review.',
        'application_id' => $application_id
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
