<?php
    session_start();
    include '../db_connect/db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_id'])) {
        $application_id = mysqli_real_escape_string($db, $_POST['application_id']);

        // Query to get allowance application details
        $query = "
            SELECT 
                pfa.expected_payment_date,
                pfa.printing,
                pfa.token,
                pfa.project_equipment,
                pfa.subscription,
                pfa.others,
                pfa.other_desc,
                pfa.status,
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
            WHERE pfa.id = '$application_id';";

        $result = mysqli_query($db, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>Field</th><th>Details</th></tr></thead>';
            echo '<tbody>';

            // Display Project Details
            echo '<tr><td>Project Number</td><td>' . htmlspecialchars($row['project_no']) . '</td></tr>';
            echo '<tr><td>Project Title</td><td>' . htmlspecialchars($row['project_title']) . '</td></tr>';
            echo '<tr><td>Project Leader</td><td>' . htmlspecialchars($row['project_leader']) . '</td></tr>';
            echo '<tr><td>Client Name</td><td>' . htmlspecialchars($row['client_company_name']) . '</td></tr>';
            echo '<tr><td>Project Start</td><td>' . htmlspecialchars($row['project_start']) . '</td></tr>';
            echo '<tr><td>Project End</td><td>' . htmlspecialchars($row['project_end']) . '</td></tr>';
            echo '<tr><td>Project Value (RM)</td><td>' . htmlspecialchars($row['registered_project_value']) . '</td></tr>';

            // Display Allowance Details
            echo '<tr><td>Expected Payment Date</td><td>' . htmlspecialchars($row['expected_payment_date']) . '</td></tr>';
            echo '<tr><td>Printing (RM)</td><td>' . htmlspecialchars($row['printing']) . '</td></tr>';
            echo '<tr><td>Token (RM)</td><td>' . htmlspecialchars($row['token']) . '</td></tr>';
            echo '<tr><td>Project Materials/Equipment (RM)</td><td>' . htmlspecialchars($row['project_equipment']) . '</td></tr>';
            echo '<tr><td>Subscription (RM)</td><td>' . htmlspecialchars($row['subscription']) . '</td></tr>';
            echo '<tr><td>Others (RM)</td><td>' . htmlspecialchars($row['others']) . '</td></tr>';

            // Display Other Description if applicable
            if (!empty($row['other_desc'])) {
                echo '<tr><td>Other Description</td><td>' . htmlspecialchars($row['other_desc']) . '</td></tr>';
            }

            // Display Status
            echo '<tr><td>Status</td><td>' . htmlspecialchars($row['status']) . '</td></tr>';

            echo '</tbody></table>';
        } else {
            echo '<p class="text-center">No details found for this application.</p>';
        }
    } else {
        echo '<p class="text-center text-danger">Invalid request.</p>';
    }
?>
