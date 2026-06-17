<?php
session_start();
include '../db_connect/db_connect.php';
include 'auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_data'])) {
    header('Location: login.php');
    exit();
}

$userData = $_SESSION['user_data'];
$user_id = $userData['id'];

// Path to the PTJ form file
$file_path = '../assets/forms/ptj_approval_form.pdf'; // Adjust path as needed
$file_name = 'PTJ_Approval_Form.pdf';

// Check if file exists
if (!file_exists($file_path)) {
    // If file doesn't exist, show error
    echo "<script>
        alert('File not found. Please contact administrator.');
        window.history.back();
    </script>";
    exit();
}

// Set headers for file download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($file_path);
exit();
?>