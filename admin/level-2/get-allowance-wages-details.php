<?php
session_start();
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['application_id']);

    $query = "
        SELECT 
            aa.application_for,
            aa.job_description,
            aa.total_allowance,
            aa.name,
            aa.email,
            aa.allowance_start_date,
            aa.allowance_end_date,
            aa.allowance_month,
            aa.allowance_month_no,
            aa.allowance_monthly_amount,
            aa.bank_name,
            aa.no_account,
            aa.ic,
            aa.outsider_ic_file,
            aa.outsider_bank_statement_file
        FROM allowance_applications aa
        WHERE aa.id = '$application_id';";

    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $allowance_start = !empty($row['allowance_start_date']) ? date('d F Y', strtotime($row['allowance_start_date'])) : '-';
        $allowance_end = !empty($row['allowance_end_date']) ? date('d F Y', strtotime($row['allowance_end_date'])) : '-';
        $allowance_month_text = !empty($row['allowance_month_no']) && !empty($row['allowance_month'])
            ? 'Month ' . $row['allowance_month_no'] . ' - ' . $row['allowance_month']
            : '-';

        // Style header (boleh letak dalam page head sekali je kalau nak)
        echo '<style>
                .elegant-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-family: Arial, sans-serif;
                    background-color: #fff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                }
                .elegant-table th {
                    background-color: #007BBD;
                    color: white;
                    padding: 12px;
                    text-align: left;
                    font-weight: normal;
                }
                .elegant-table td {
                    padding: 12px;
                    border-bottom: 1px solid #eee;
                    background-color: #fdfdfd;
                }
                .elegant-table tr:last-child td {
                    border-bottom: none;
                }
              </style>';

        echo '<table class="elegant-table">';
        echo '<tr><th>Field</th><th>Details</th></tr>';

        // Common fields
        echo '<tr><td>Application For</td><td>' . htmlspecialchars($row['application_for']) . '</td></tr>';
        echo '<tr><td>Job Description</td><td>' . htmlspecialchars($row['job_description']) . '</td></tr>';
        echo '<tr><td>Total Allowance (RM)</td><td>' . htmlspecialchars(number_format($row['total_allowance'], 2)) . '</td></tr>';

        // RA-specific
        if ($row['application_for'] === 'Research assistant allowance') {
            echo '<tr><td>RA Name</td><td>' . htmlspecialchars($row['name']) . '</td></tr>';
            echo '<tr><td>RA Email</td><td>' . htmlspecialchars($row['email']) . '</td></tr>';
            echo '<tr><td>RA IC</td><td>' . htmlspecialchars($row['ic']) . '</td></tr>';
            echo '<tr><td>Allowance Month</td><td>' . htmlspecialchars($allowance_month_text) . '</td></tr>';
            echo '<tr><td>Allowance Period</td><td>' . htmlspecialchars($allowance_start) . ' to ' . htmlspecialchars($allowance_end) . '</td></tr>';
            echo '<tr><td>Monthly Allowance/Wage (RM)</td><td>' . htmlspecialchars(number_format($row['allowance_monthly_amount'], 2)) . '</td></tr>';
            echo '<tr><td>RA Bank Name</td><td>' . htmlspecialchars($row['bank_name']) . '</td></tr>';
            echo '<tr><td>RA Bank Account</td><td>' . htmlspecialchars($row['no_account']) . '</td></tr>';
        }

        // Outsider-specific
        if ($row['application_for'] === 'Outsider allowance') {
            echo '<tr><td>Outsider Name</td><td>' . htmlspecialchars($row['name']) . '</td></tr>';
            echo '<tr><td>Outsider Email</td><td>' . htmlspecialchars($row['email']) . '</td></tr>';
            echo '<tr><td>Start Date</td><td>' . htmlspecialchars($allowance_start) . '</td></tr>';
            echo '<tr><td>End Date</td><td>' . htmlspecialchars($allowance_end) . '</td></tr>';
            echo '<tr><td>Outsider Bank Name</td><td>' . htmlspecialchars($row['bank_name']) . '</td></tr>';
            echo '<tr><td>Outsider Bank Account</td><td>' . htmlspecialchars($row['no_account']) . '</td></tr>';
            echo '<tr><td>Outsider IC</td><td>' . htmlspecialchars($row['ic']) . '</td></tr>';
            if (!empty($row['outsider_ic_file'])) {
                echo '<tr><td>IC Copy</td><td><a href="../../allowance-outsider-documents/' . rawurlencode($row['outsider_ic_file']) . '" target="_blank">View IC Copy</a></td></tr>';
            } else {
                echo '<tr><td>IC Copy</td><td>Not uploaded</td></tr>';
            }
            if (!empty($row['outsider_bank_statement_file'])) {
                echo '<tr><td>Bank Statement</td><td><a href="../../allowance-outsider-documents/' . rawurlencode($row['outsider_bank_statement_file']) . '" target="_blank">View Bank Statement</a></td></tr>';
            } else {
                echo '<tr><td>Bank Statement</td><td>Not uploaded</td></tr>';
            }
        }

        echo '</table>';
    } else {
        echo '<p style="text-align:center; color:#b30000; margin-top:20px;">No details found for this application.</p>';
    }
} else {
    echo '<p style="text-align:center; color:#b30000; margin-top:20px;">Invalid request.</p>';
}
?>
