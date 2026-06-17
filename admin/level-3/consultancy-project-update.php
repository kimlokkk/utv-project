<?php
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
    
    if (isset($_GET['id']) && isset($_GET['project_owned'])) {
        $project_id = mysqli_real_escape_string($db, $_GET['id']);
        $project_owned = mysqli_real_escape_string($db, $_GET['project_owned']);
    
        $update_query = "UPDATE project SET project_owned = '$project_owned' WHERE id = '$project_id'";
        $update_result = mysqli_query($db, $update_query);
    
        if ($update_result) {
            header("Location: consultancy-project-info.php?update=update-success&id=$project_id");
            exit();
        }
    
        header("Location: consultancy-project-info.php?update=save-fail&id=$project_id");
        exit();
    } else {
        header("Location: consultancy-project-list.php?update=invalid-id");
        exit();
    }
?>
