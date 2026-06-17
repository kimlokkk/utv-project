<?php
session_start();
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['application_id']);

    // Query for application + project details
    $query = "
        SELECT 
            pfa.status,
            pfa.expected_payment_date,
            pfa.expected_payment_amount,
            pfa.pfa_number,
            pfa.total_previous_pfa_applied,
            pfa.return_to,
            pfa.return_remark,
            p.id AS project_id,
            p.project_no,
            p.project_title,
            p.project_leader,
            p.client_company_name,
            p.project_start,
            p.project_end,
            p.registered_project_value
        FROM project_funding_assistance_applications pfa
        LEFT JOIN project p ON pfa.project_id = p.id
        WHERE pfa.id = '$application_id'";

    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Format dates
        $start = date('d F Y', strtotime($row['project_start']));
        $end = date('d F Y', strtotime($row['project_end']));
        $expected = date('d F Y', strtotime($row['expected_payment_date']));

        echo '<style>
            .elegant-table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                margin-bottom: 30px;
            }
            .elegant-table th {
                background-color: #007BBD;
                color: white;
                padding: 10px;
                text-align: left;
            }
            .elegant-table td {
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

        // Project Details
        echo '<div class="section-title">Project Information</div>';
        echo '<table class="elegant-table">';
        echo '<tr><th>Project Number</th><td>' . htmlspecialchars($row['project_no']) . '</td></tr>';
        echo '<tr><th>Project Title</th><td>' . htmlspecialchars($row['project_title']) . '</td></tr>';
        echo '<tr><th>Project Leader</th><td>' . htmlspecialchars($row['project_leader']) . '</td></tr>';
        echo '<tr><th>Client Name</th><td>' . htmlspecialchars($row['client_company_name']) . '</td></tr>';
        echo '<tr><th>Project Start</th><td>' . htmlspecialchars($start) . '</td></tr>';
        echo '<tr><th>Project End</th><td>' . htmlspecialchars($end) . '</td></tr>';
        echo '<tr><th>Project Value (RM)</th><td>' . htmlspecialchars($row['registered_project_value']) . '</td></tr>';
        echo '</table>';

        // Application Details
        echo '<div class="section-title">Funding Assistance Application</div>';
        echo '<table class="elegant-table">';
        echo '<tr><th>Expected To Receive Payment From Client Date</th><td>' . htmlspecialchars($expected) . '</td></tr>';
        echo '<tr><th>Expected To Receive Payment From Client Amount (RM)</th><td>' . number_format((float) $row['expected_payment_amount'], 2) . '</td></tr>';
        echo '<tr><th>PFA Application No.</th><td>' . htmlspecialchars($row['pfa_number']) . '</td></tr>';
        echo '<tr><th>Total Previous PFA Applied (RM)</th><td>' . number_format((float) $row['total_previous_pfa_applied'], 2) . '</td></tr>';
        echo '<tr><th>Status</th><td>' . htmlspecialchars($row['status']) . '</td></tr>';
        if (!empty($row['return_remark'])) {
            $remark_label = !empty($row['return_to']) ? 'Return Remark (' . $row['return_to'] . ')' : 'Return Remark';
            echo '<tr><th>' . htmlspecialchars($remark_label) . '</th><td>' . nl2br(htmlspecialchars($row['return_remark'])) . '</td></tr>';
        }
        echo '</table>';

        // Funding Items
        $item_query = "SELECT category, item, quantity, amount FROM project_funding_assistance_items WHERE application_id = '$application_id' ORDER BY id ASC";
        $item_result = mysqli_query($db, $item_query);

        echo '<div class="section-title">Requested Items</div>';
        echo '<table class="elegant-table">';
        echo '<thead><tr><th>Category</th><th>Item</th><th>Quantity</th><th>Amount (RM)</th></tr></thead>';
        echo '<tbody>';

        $has_items = false;
        $total = 0;

        while ($item = mysqli_fetch_assoc($item_result)) {
            $has_items = true;
            $amount = number_format($item['amount'], 2);
            $total += $item['amount'];

            echo '<tr>';
            echo '<td>' . htmlspecialchars($item['category']) . '</td>';
            echo '<td>' . htmlspecialchars($item['item']) . '</td>';
            echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
            echo '<td>' . $amount . '</td>';
            echo '</tr>';
        }

        if (!$has_items) {
            echo '<tr><td colspan="4" class="text-center">No items found.</td></tr>';
        } else {
            echo '<tr style="font-weight:bold;"><td colspan="3" style="text-align:right;">Total (RM)</td><td>' . number_format($total, 2) . '</td></tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p class="text-center text-danger">No details found for this application.</p>';
    }
} else {
    echo '<p class="text-center text-danger">Invalid request.</p>';
}
?>
