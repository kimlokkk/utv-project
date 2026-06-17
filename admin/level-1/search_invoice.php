<?php
    include '../db_connect/db_connect.php';
    
    $keyword = $_POST['keyword'];
    $query = "SELECT id, invoice_no FROM invoices WHERE invoice_no LIKE '%$keyword%' LIMIT 10";
    $result = $db->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode($data);
?>
