<?php
    session_start();
    include '../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_id'])) {
        $application_id = mysqli_real_escape_string($db, $_POST['application_id']);
    
        // Query to get allowance application details
        $query = "
            SELECT 
                aa.application_for,
                aa.job_description,
                aa.total_allowance,
                ra.full_name,
                ra.ic,
                ra.bank_name,
                ra.no_account,
                aa.member_name AS outsider_name,
                aa.bank_name AS outsider_bank_name,
                aa.bank_account AS outsider_bank_account,
                aa.ic_number AS outsider_ic
            FROM allowance_applications aa
            LEFT JOIN research_assistant ra ON aa.member_id = ra.id
            WHERE aa.id = '$application_id';";
    
        $result = mysqli_query($db, $query);
    
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>Field</th><th>Details</th></tr></thead>';
            echo '<tbody>';
    
            // Common fields
            echo '<tr><td>Application For</td><td>' . htmlspecialchars($row['application_for']) . '</td></tr>';
            echo '<tr><td>Job Description</td><td>' . htmlspecialchars($row['job_description']) . '</td></tr>';
            echo '<tr><td>Total Allowance (RM)</td><td>' . htmlspecialchars($row['total_allowance']) . '</td></tr>';
    
            // RA-specific fields
            if ($row['application_for'] === 'Research assistant allowance') {
                echo '<tr><td>RA Name</td><td>' . htmlspecialchars($row['full_name']) . '</td></tr>';
                echo '<tr><td>RA IC</td><td>' . htmlspecialchars($row['ic']) . '</td></tr>';
                echo '<tr><td>RA Bank Name</td><td>' . htmlspecialchars($row['bank_name']) . '</td></tr>';
                echo '<tr><td>RA Bank Account</td><td>' . htmlspecialchars($row['no_account']) . '</td></tr>';
            }
    
            // Outsider-specific fields
            if ($row['application_for'] === 'Outsider allowance') {
                echo '<tr><td>Outsider Name</td><td>' . htmlspecialchars($row['outsider_name']) . '</td></tr>';
                echo '<tr><td>Outsider Bank Name</td><td>' . htmlspecialchars($row['outsider_bank_name']) . '</td></tr>';
                echo '<tr><td>Outsider Bank Account</td><td>' . htmlspecialchars($row['outsider_bank_account']) . '</td></tr>';
                echo '<tr><td>Outsider IC</td><td>' . htmlspecialchars($row['outsider_ic']) . '</td></tr>';
            }
    
            echo '</tbody></table>';
        } else {
            echo '<p class="text-center">No details found for this application.</p>';
        }
    } else {
        echo '<p class="text-center text-danger">Invalid request.</p>';
    }
?>
