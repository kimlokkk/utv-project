<?php
session_start();
include '../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['application_id']);

    // Query untuk mendapatkan maklumat ahli dan jumlah
    $query = "
        SELECT 
            pf.member_name,
            pf.amount,
            ra.bank_name,
            ra.no_account
        FROM professional_fee_applications pf
        INNER JOIN research_assistant ra ON pf.member_id = ra.id
        WHERE pf.id = '$application_id'";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Member Name</th><th>Amount (RM)</th><th>Bank Name</th><th>Bank Account</th></tr></thead>';
        echo '<tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['member_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['amount']) . '</td>';
            echo '<td>' . htmlspecialchars($row['bank_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['no_account']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-center">No details found for this application.</p>';
    }
} else {
    echo '<p class="text-center text-danger">Invalid request.</p>';
}
?>
