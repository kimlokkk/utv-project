<?php
    include '../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
        $application_id = mysqli_real_escape_string($db, $_POST['application_id']);
    
        // Query untuk mendapatkan butiran permohonan
        $query = "SELECT 
                    rc.application_id,
                    rc.application_type,
                    rc.status,
                    rc.fnb,
                    rc.hotel,
                    rc.travelling,
                    rc.printing,
                    rc.materials,
                    rc.others,
                    rc.receipt_file,
                    rc.date_applied,
                    p.project_no,
                    p.project_title,
                    p.project_leader
                  FROM reconciliation_claim_applications rc
                  INNER JOIN project p ON rc.project_id = p.id
                  INNER JOIN uitm_staff ON p.leader_id = uitm_staff.id
                  WHERE rc.application_id = '$application_id'";
    
        $result = mysqli_query($db, $query);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
    
            // Paparkan data dalam format HTML dengan styling
            echo "<div class='container-fluid'>";

            echo "<div class='row mb-4'>";
            echo "<div class='col-12'>";
            echo "<h5 class='text-primary'>Application Details</h5>";
            echo "<table class='table table-bordered'>";
            echo "<tr><th>Application ID</th><td>{$data['application_id']}</td></tr>";
            echo "<tr><th>Application Type</th><td>{$data['application_type']}</td></tr>";
            echo "<tr><th>Status</th><td>{$data['status']}</td></tr>";
            echo "<tr><th>Date Applied</th><td>{$data['date_applied']}</td></tr>";
            echo "</table>";
            echo "</div>";
            echo "</div>";

            echo "<div class='row mb-4'>";
            echo "<div class='col-12'>";
            echo "<h5 class='text-primary'>Project Details</h5>";
            echo "<table class='table table-bordered'>";
            echo "<tr><th>Project Number</th><td>{$data['project_no']}</td></tr>";
            echo "<tr><th>Project Title</th><td>{$data['project_title']}</td></tr>";
            echo "<tr><th>Project Leader</th><td>{$data['project_leader']}</td></tr>";
            echo "</table>";
            echo "</div>";
            echo "</div>";

            echo "<div class='row mb-4'>";
            echo "<div class='col-12'>";
            echo "<h5 class='text-primary'>Claim Details</h5>";
            echo "<table class='table table-bordered'>";
            echo "<tr><th>F&B (RM)</th><td>{$data['fnb']}</td></tr>";
            echo "<tr><th>Hotel/Room Stay (RM)</th><td>{$data['hotel']}</td></tr>";
            echo "<tr><th>Travelling (RM)</th><td>{$data['travelling']}</td></tr>";
            echo "<tr><th>Printing (RM)</th><td>{$data['printing']}</td></tr>";
            echo "<tr><th>Materials/Equipment (RM)</th><td>{$data['materials']}</td></tr>";
            echo "<tr><th>Others (RM)</th><td>{$data['others']}</td></tr>";
            echo "</table>";
            echo "</div>";
            echo "</div>";

            // Tunjukkan fail resit jika wujud
            if (!empty($data['receipt_file'])) {
                $file_path = "../consultant/project-documents/reconciliation-claim-receipts/{$data['receipt_file']}";
                echo "<div class='row mb-4'>";
                echo "<div class='col-12'>";
                echo "<h5 class='text-primary'>Uploaded Receipt</h5>";
                echo "<a href='$file_path' target='_blank' class='btn btn-outline-info'>View Receipt</a>";
                echo "</div>";
                echo "</div>";
            }

            echo "</div>"; // Close container-fluid
        } else {
            echo "<div class='alert alert-danger'>No details found for this application.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid request or missing application ID.</div>";
    }
?>
