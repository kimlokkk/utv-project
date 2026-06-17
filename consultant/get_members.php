<?php
// Include database connection
include "../db_connect/db_connect.php";

// Fetch members from the database
$query = "SELECT full_name, ic FROM research_assistant";
$result = mysqli_query($db, $query);

// Prepare data for JSON response
$data = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'full_name' => htmlspecialchars($row['full_name']),
            'ic' => htmlspecialchars($row['ic']),
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
?>
