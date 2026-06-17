<?php
include '../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['advance_id'])) {
    echo '<div class="alert alert-warning">Advance application not selected.</div>';
    exit;
}

$advance_id = mysqli_real_escape_string($db, $_POST['advance_id']);

$advance_query = "
    SELECT 
        rc.application_id,
        rc.date_applied,
        us.full_name AS applicant_name
    FROM reconciliation_claim_applications rc
    LEFT JOIN uitm_staff us ON rc.applicant_id = us.id
    WHERE rc.application_id = '$advance_id'
      AND rc.application_type = 'Advance'
    LIMIT 1
";
$advance_result = mysqli_query($db, $advance_query);

if (!$advance_result) {
    echo '<div class="alert alert-danger">Unable to retrieve the selected advance: ' . htmlspecialchars(mysqli_error($db)) . '</div>';
    exit;
}

if (mysqli_num_rows($advance_result) === 0) {
    echo '<div class="alert alert-warning">The selected advance could not be found. Please choose another approved advance.</div>';
    exit;
}

$advance = mysqli_fetch_assoc($advance_result);
$advance_date = !empty($advance['date_applied']) ? date('d M Y', strtotime($advance['date_applied'])) : 'Date not recorded';
$applicant_name = !empty($advance['applicant_name']) ? $advance['applicant_name'] : 'Applicant not recorded';

$query = "
    SELECT claim_category, claim_item, claim_quantity, claim_amount
    FROM reconciliation_claim_items
    WHERE application_id = '$advance_id'
    ORDER BY id ASC
";
$result = mysqli_query($db, $query);

if (!$result) {
    echo '<div class="alert alert-danger">Unable to retrieve advance items: ' . htmlspecialchars(mysqli_error($db)) . '</div>';
    exit;
}

echo '<div class="card border">';
echo '<div class="card-header bg-light">';
echo '<strong>Advance Items to Reconcile</strong>';
echo '<div class="small text-muted">' . htmlspecialchars($advance_date) . ' | ' . htmlspecialchars($applicant_name) . '</div>';
echo '</div>';
echo '<div class="card-body p-0">';
echo '<table class="table table-sm table-bordered m-b-0">';
echo '<thead><tr><th>Category</th><th>Item</th><th>Quantity</th><th>Advance Amount (RM)</th></tr></thead><tbody>';

$total = 0;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $amount = (float)$row['claim_amount'];
        $total += $amount;
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['claim_category']) . '</td>';
        echo '<td>' . htmlspecialchars($row['claim_item']) . '</td>';
        echo '<td>' . htmlspecialchars($row['claim_quantity']) . '</td>';
        echo '<td>' . number_format($amount, 2) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center text-muted">No advance items found.</td></tr>';
}

echo '</tbody><tfoot><tr><th colspan="3" class="text-right">Advance Total</th><th>' . number_format($total, 2) . '</th></tr></tfoot>';
echo '</table></div></div>';
?>
