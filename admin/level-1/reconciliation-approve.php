<?php
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (
            empty($_GET['projectId']) || 
            empty($_GET['applicationId']) || 
            empty($_GET['applicationType']) || 
            empty($_GET['projectNo']) || 
            empty($_GET['staffId'])
        ) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        $project_id       = mysqli_real_escape_string($db, $_GET['projectId']);
        $project_no       = mysqli_real_escape_string($db, $_GET['projectNo']);
        $application_id   = mysqli_real_escape_string($db, $_GET['applicationId']);
        $staff_id         = mysqli_real_escape_string($db, $_GET['staffId']);
        $application_type = mysqli_real_escape_string($db, $_GET['applicationType']); // e.g. Advance, Claim, Reconciliation
        $date             = date('Y-m-d H:i:s');
        $remark           = "$application_type application has been approved by admin ($staff_id).";
    
        mysqli_begin_transaction($db);
    
        try {
            // Step 1: Update status
            $update_query = "UPDATE reconciliation_claim_applications 
                             SET status = 'Approved' 
                             WHERE application_id = '$application_id'";
            if (!mysqli_query($db, $update_query)) {
                throw new Exception("Failed to update $application_type application status.");
            }
    
            // Step 2: Insert into ledger (only for Advance or Claim)
            if (in_array($application_type, ['Advance', 'Claim'])) {
                $transaction_type = ($application_type === 'Claim') ? 'Debit' : 'Credit';
    
                $items_query = "SELECT claim_category, SUM(claim_amount) AS total_amount
                                FROM reconciliation_claim_items
                                WHERE application_id = '$application_id'
                                GROUP BY claim_category";
                $items_result = mysqli_query($db, $items_query);
    
                if (!$items_result) {
                    throw new Exception("Failed to retrieve claim items.");
                }
    
                while ($row = mysqli_fetch_assoc($items_result)) {
                    $description = $row['claim_category'];
                    $amount = number_format(floatval($row['total_amount']), 2, '.', '');
    
                    if ($amount > 0) {
                        $ledger_query = "INSERT INTO project_ledger (
                                            project_id, 
                                            transaction_desc, 
                                            transaction_type, 
                                            amount, 
                                            created_at
                                        ) VALUES (
                                            '$project_id', 
                                            '$application_type for $description', 
                                            '$transaction_type', 
                                            '$amount', 
                                            '$date'
                                        )";
                        if (!mysqli_query($db, $ledger_query)) {
                            throw new Exception("Failed to insert $description into project ledger.");
                        }
                    }
                }
            }
    
            // Step 3: Insert tracker
            $tracker_query = "INSERT INTO project_tracker (
                                project_id, 
                                project_no, 
                                remark, 
                                date
                              ) VALUES (
                                '$project_id', 
                                '$project_no', 
                                '$remark', 
                                '$date'
                              )";
            if (!mysqli_query($db, $tracker_query)) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            mysqli_commit($db);
            echo json_encode([
                'success' => true, 
                'message' => "$application_type application has been successfully approved!"
            ]);
        } catch (Exception $e) {
            mysqli_rollback($db);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
?>
