<?php
session_start();
include '../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['raa_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['raa_id']);

    $query = "
        SELECT 
            raa.id,
            raa.project_id,
            raa.ra_id,
            raa.status,
            raa.start_date,
            raa.duration,
            raa.payment_type,
            raa.budget,
            raa.expertise,
            raa.created_at,
            ra.full_name AS research_name,
            p.project_no,
            p.project_title,
            s.full_name AS leader_name
        FROM research_assistant_application raa
        LEFT JOIN research_assistant ra ON raa.ra_id = ra.id
        LEFT JOIN project p ON raa.project_id = p.id
        LEFT JOIN uitm_staff s ON p.leader_id = s.id
        WHERE raa.id = '$application_id'
    ";

    $result = mysqli_query($db, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $date_start = date('d F Y', strtotime($row['start_date']));
        $created_at = date('d F Y, h:i A', strtotime($row['created_at']));
        $budget = number_format($row['budget'], 2);

        echo '<style>
            .info-table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                margin-bottom: 30px;
            }
            .info-table th {
                background-color: #007BBD;
                color: white;
                padding: 10px;
                text-align: left;
                width: 30%;
            }
            .info-table td {
                padding: 10px;
                border: 1px solid #ddd;
                background-color: #fff;
            }
            .section-title {
                font-weight: bold;
                font-size: 16px;
                color: #007BBD;
                margin-top: 25px;
                margin-bottom: 10px;
            }
        </style>';

        echo '<div class="section-title">Research Assistant Application Details</div>';
        echo '<table class="info-table">';
        echo '<tr><th>Research Assistant</th><td>' . htmlspecialchars($row['research_name']) . '</td></tr>';
        echo '<tr><th>Status</th><td>' . htmlspecialchars($row['status']) . '</td></tr>';
        echo '<tr><th>Start Date</th><td>' . $date_start . '</td></tr>';
        echo '<tr><th>Duration (Month)</th><td>' . $row['duration'] . '</td></tr>';
        echo '<tr><th>Payment Type</th><td>' . htmlspecialchars($row['payment_type']) . '</td></tr>';
        echo '<tr><th>Budget (RM)</th><td>' . $budget . '</td></tr>';
        echo '<tr><th>Expertise</th><td>' . nl2br(htmlspecialchars($row['expertise'])) . '</td></tr>';
        echo '<tr><th>Created At</th><td>' . $created_at . '</td></tr>';
        echo '<tr><th>Project Number</th><td>' . htmlspecialchars($row['project_no']) . '</td></tr>';
        echo '<tr><th>Project Title</th><td>' . htmlspecialchars($row['project_title']) . '</td></tr>';
        echo '<tr><th>Project Leader</th><td>' . htmlspecialchars($row['leader_name']) . '</td></tr>';
        echo '</table>';
    } else {
        echo '<p class="text-center text-danger">No details found for this application.</p>';
    }
} else {
    echo '<p class="text-center text-danger">Invalid request.</p>';
}
?>
