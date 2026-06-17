<?php
    include '../db_connect/db_connect.php';
    
    $keyword = $_POST['keyword'];
    $query = "SELECT id, project_no, client_company_name, project_type FROM project WHERE project_no LIKE '%$keyword%' LIMIT 10";
    $result = $db->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode($data);
?>
