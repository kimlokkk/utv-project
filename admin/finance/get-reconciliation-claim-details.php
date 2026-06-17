<?php
include '../../db_connect/db_connect.php';
include '../../consultant/reconciliation_claim_details.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    render_reconciliation_claim_details($db, $_POST['application_id'], '../../consultant/project-documents/reconciliation-claim-receipts/');
} else {
    echo "<div class='alert alert-danger'>Invalid request or missing application ID.</div>";
}
?>
