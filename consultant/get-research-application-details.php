<?php
session_start();
include '../db_connect/db_connect.php';

function raa_details_column_exists($db, $column) {
    $column = mysqli_real_escape_string($db, $column);
    $result = mysqli_query($db, "SHOW COLUMNS FROM research_assistant_application LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function raa_details_format_date($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date('d F Y', $timestamp) : '-';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['raa_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['raa_id']);
    $has_end_date = raa_details_column_exists($db, 'end_date');
    $select_end_date = $has_end_date ? "raa.end_date," : "NULL AS end_date,";
    $has_return_remark = raa_details_column_exists($db, 'return_remark');
    $select_return_remark = $has_return_remark ? "raa.return_remark," : "NULL AS return_remark,";
    $has_return_to = raa_details_column_exists($db, 'return_to');
    $select_return_to = $has_return_to ? "raa.return_to," : "NULL AS return_to,";

    $query = "
        SELECT 
            raa.id,
            raa.project_id,
            raa.ra_id,
            raa.status,
            $select_return_remark
            $select_return_to
            raa.start_date,
            $select_end_date
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

        $date_start = raa_details_format_date($row['start_date']);
        $date_end = raa_details_format_date($row['end_date'] ?? '');
        $duration = (int)($row['duration'] ?? 0);
        $budget_raw = (float)($row['budget'] ?? 0);
        $budget = number_format($budget_raw, 2);
        $estimated_total = number_format($budget_raw * max($duration, 0), 2);
        $return_remark = trim((string)($row['return_remark'] ?? ''));
        $return_to = trim((string)($row['return_to'] ?? ''));
        $is_internal_level3_review = stripos((string)$row['status'], 'Rejected') !== false && strcasecmp($return_to, 'Level 3') === 0;
        $display_status = $is_internal_level3_review ? 'Pending Level 3 Review' : $row['status'];
        $created_at = !empty($row['created_at']) ? date('d F Y, h:i A', strtotime($row['created_at'])) : '-';

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
        echo '<tr><th>Status</th><td>' . htmlspecialchars($display_status) . '</td></tr>';
        if (!$is_internal_level3_review && $return_remark !== '') {
            echo '<tr><th>Return Remark</th><td>' . nl2br(htmlspecialchars($return_remark)) . '</td></tr>';
        }
        echo '<tr><th>Start Date</th><td>' . $date_start . '</td></tr>';
        echo '<tr><th>End Date</th><td>' . $date_end . '</td></tr>';
        echo '<tr><th>Payable Duration</th><td>' . htmlspecialchars($duration) . ' month(s)</td></tr>';
        echo '<tr><th>Payment Type</th><td>' . htmlspecialchars($row['payment_type']) . '</td></tr>';
        echo '<tr><th>Monthly Allowance/Wage (RM)</th><td>' . $budget . '</td></tr>';
        echo '<tr><th>Estimated Total (RM)</th><td>' . $estimated_total . '</td></tr>';
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
