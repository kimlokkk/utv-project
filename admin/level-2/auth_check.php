<?php
session_start();
include '../../db_connect/db_connect.php'; // Adjust as needed

// Check if Level 3 session exists
if (!isset($_SESSION['Admin_Level']) || $_SESSION['Admin_Level'] !== 'Level 2') {
    echo '<script>
        alert("You are not authorized to access this page. Please log in as Level 2 Admin.");
        window.location.href = "../index.php";
    </script>';
    exit();
}

// Ambil user data yang disimpan dengan nama unik
$userData = $_SESSION['user_data_Level 2'];
?>
