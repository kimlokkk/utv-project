<?php
session_start();
include '../../db_connect/db_connect.php'; // Adjust as needed

// Check if Level 4 AND role is Financial
if (
    !isset($_SESSION['Admin_Level']) || $_SESSION['Admin_Level'] !== 'Level 4' ||
    !isset($_SESSION['user_data_Level 4']['role']) || $_SESSION['user_data_Level 4']['role'] !== 'Financial'
) {
    echo '<script>
        alert("You are not authorized to access this page. Only Level 4 with Financial role allowed.");
        window.location.href = "../index.php";
    </script>';
    exit();
}

$userData = $_SESSION['user_data_Level 4'];
?>