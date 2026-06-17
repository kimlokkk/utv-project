<?php
session_start();
include '../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['application_id']);

    $query = "
        SELECT 
            us.full_name AS member_name,
            pf.amount,
            pf.status,
            pf.return_to,
            pf.return_remark,
            us.bank_name,
            us.no_account
        FROM professional_fee_applications pf
        INNER JOIN uitm_staff us ON pf.member_id = us.id
        WHERE pf.id = '$application_id'";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Field</th><th>Details</th></tr></thead>';
        echo '<tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr><td>Member Name</td><td>' . htmlspecialchars($row['member_name']) . '</td></tr>';
            echo '<tr><td>Amount (RM)</td><td>' . htmlspecialchars(number_format((float)$row['amount'], 2)) . '</td></tr>';
            echo '<tr><td>Status</td><td>' . htmlspecialchars($row['status']) . '</td></tr>';
            if (stripos((string)$row['status'], 'Rejected') !== false && strcasecmp((string)$row['return_to'], 'Consultant') === 0 && !empty($row['return_remark'])) {
                echo '<tr><td>Return Remark</td><td>' . nl2br(htmlspecialchars($row['return_remark'])) . '</td></tr>';
            }
            echo '<tr><td>Bank Name</td><td>' . htmlspecialchars($row['bank_name']) . '</td></tr>';
            echo '<tr><td>Bank Account</td><td>' . htmlspecialchars($row['no_account']) . '</td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-center">No details found for this application.</p>';
    }
} else {
    echo '<p class="text-center text-danger">Invalid request.</p>';
}
?>
