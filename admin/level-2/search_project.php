<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../db_connect/db_connect.php';

$keyword = $_POST['keyword'] ?? '';
$keyword = mysqli_real_escape_string($db, $keyword);

$query = "
    SELECT 
        id,
        project_no,
        project_title,
        project_leader,
        client_company_name,
        project_type
    FROM project
    WHERE project_status IN ('Approved', 'Appointed')
";

if (!empty($keyword)) {
    $query .= "
        AND (
            project_no LIKE '%$keyword%'
            OR project_title LIKE '%$keyword%'
            OR client_company_name LIKE '%$keyword%'
            OR project_leader LIKE '%$keyword%'
        )
    ";
}

$query .= " ORDER BY project_no ASC LIMIT 20";

$result = mysqli_query($db, $query);

$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'project_no' => $row['project_no'],
            'project_title' => $row['project_title'],
            'project_leader' => $row['project_leader'],
            'client_company_name' => $row['client_company_name'],
            'project_type' => $row['project_type']
        ];
    }
}

echo json_encode($data);
exit;
?>