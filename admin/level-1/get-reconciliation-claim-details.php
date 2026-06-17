<?php
include '../../db_connect/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    $application_id = mysqli_real_escape_string($db, $_POST['application_id']);

    // Get main application, project, and applicant info
    $query = "SELECT 
                rc.application_id,
                rc.application_type,
                rc.status,
                rc.receipt_file,
                rc.date_applied,
                rc.applicant_id,
                us.full_name AS applicant_name,
                p.project_no,
                p.project_title,
                p.project_leader
              FROM reconciliation_claim_applications rc
              INNER JOIN project p ON rc.project_id = p.id
              LEFT JOIN uitm_staff us ON rc.applicant_id = us.id
              WHERE rc.application_id = '$application_id'";

    $result = mysqli_query($db, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $dateFormatted = date('d M Y', strtotime($data['date_applied']));

        echo "<div class='container-fluid'>";

        // SECTION: Application Details
        echo "<div class='row mb-4'>";
        echo "<div class='col-lg-12'>";
        echo "<div class='card shadow-sm border-0'>";
        echo "<div class='card-header bg-primary text-white fw-semibold'>Application Details</div>";
        echo "<div class='card-body'>";
        echo "<table class='table table-borderless'>";
        echo "<tr><th class='w-50'>Application ID</th><td>" . htmlspecialchars($data['application_id']) . "</td></tr>";
        echo "<tr><th>Application Type</th><td>" . htmlspecialchars(ucfirst($data['application_type'])) . "</td></tr>";
        echo "<tr><th>Status</th><td>" . htmlspecialchars($data['status']) . "</td></tr>";
        echo "<tr><th>Date Applied</th><td>" . htmlspecialchars($dateFormatted) . "</td></tr>";
        echo "<tr><th>Applicant</th><td>" . htmlspecialchars($data['applicant_name']) . "</td></tr>";
        echo "</table>";
        echo "</div></div></div></div>";

        // SECTION: Project Details
        echo "<div class='row mb-4'>";
        echo "<div class='col-lg-12'>";
        echo "<div class='card shadow-sm border-0'>";
        echo "<div class='card-header bg-primary text-white fw-semibold'>Project Details</div>";
        echo "<div class='card-body'>";
        echo "<table class='table table-borderless'>";
        echo "<tr><th class='w-50'>Project Number</th><td>" . htmlspecialchars($data['project_no']) . "</td></tr>";
        echo "<tr><th>Project Title</th><td>" . htmlspecialchars($data['project_title']) . "</td></tr>";
        echo "<tr><th>Project Leader</th><td>" . htmlspecialchars($data['project_leader']) . "</td></tr>";
        echo "</table>";
        echo "</div></div></div></div>";

        // SECTION: Claim Items
        echo "<div class='row mb-4'>";
        echo "<div class='col-12'>";
        echo "<div class='card shadow-sm border-0'>";
        echo "<div class='card-header bg-primary text-white fw-semibold'>Claim Items</div>";
        echo "<div class='card-body'>";
        echo "<table class='table table-striped table-hover'>";
        echo "<thead class='table-light'>";
        echo "<tr>";
        echo "<th>Category</th>";
        echo "<th>Item Description</th>";
        echo "<th>Quantity</th>";
        echo "<th>Amount (RM)</th>";
        echo "</tr>";
        echo "</thead><tbody>";

        $items_query = "SELECT claim_category, claim_item, claim_quantity, claim_amount
                        FROM reconciliation_claim_items
                        WHERE application_id = '$application_id'
                        ORDER BY id ASC";
        $items_result = mysqli_query($db, $items_query);

        if ($items_result && mysqli_num_rows($items_result) > 0) {
            while ($item = mysqli_fetch_assoc($items_result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['claim_category']) . "</td>";
                echo "<td>" . htmlspecialchars($item['claim_item']) . "</td>";
                echo "<td>" . htmlspecialchars($item['claim_quantity']) . "</td>";
                echo "<td>" . htmlspecialchars(number_format($item['claim_amount'], 2)) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center text-muted'>No claim items found.</td></tr>";
        }
        echo "</tbody></table>";
        echo "</div></div></div></div>";

        // SECTION: Uploaded Receipt
        if (!empty($data['receipt_file'])) {
            $file_path = "../consultant/project-documents/reconciliation-claim-receipts/{$data['receipt_file']}";
            echo "<div class='row mb-4'>";
            echo "<div class='col-lg-12'>";
            echo "<div class='card shadow-sm border-0'>";
            echo "<div class='card-header bg-primary text-white fw-semibold'>Uploaded Receipt</div>";
            echo "<div class='card-body'>";
            echo "<a href='" . htmlspecialchars($file_path) . "' target='_blank' class='btn btn-outline-primary'>View Receipt</a>";
            echo "</div></div></div></div>";
        }

        echo "</div>"; // Close container
    } else {
        echo "<div class='alert alert-danger'>No details found for this application.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Invalid request or missing application ID.</div>";
}
?>
