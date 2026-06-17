<?php
    // Start the session and include required files
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';

    $userData = $_SESSION['user_data'] ?? [];
    $current_user_id = isset($userData['id']) ? (string)$userData['id'] : '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Validasi input
        if (empty($_GET['project_id']) || empty($_GET['invoice_id']) || empty($_GET['project_no'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari form
        $project_id = mysqli_real_escape_string($db, $_GET['project_id']);
        $invoice_id = mysqli_real_escape_string($db, $_GET['invoice_id']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            $invoice_query = "
                SELECT i.invoice_status, i.member_id, p.leader_id, p.project_no
                FROM invoices i
                INNER JOIN project p ON i.project_id = p.id
                WHERE i.id = '$invoice_id' AND p.id = '$project_id'
                LIMIT 1
            ";
            $invoice_result = mysqli_query($db, $invoice_query);

            if (!$invoice_result || mysqli_num_rows($invoice_result) === 0) {
                throw new Exception('Invoice not found.');
            }

            $invoice_data = mysqli_fetch_assoc($invoice_result);
            $project_no = mysqli_real_escape_string($db, $invoice_data['project_no']);
            $current_status = (string)$invoice_data['invoice_status'];

            if ($current_user_id === '' || (string)$invoice_data['leader_id'] !== $current_user_id) {
                throw new Exception('Only the project leader can approve this invoice.');
            }

            if (
                stripos($current_status, 'Pending Leader Review') === false &&
                stripos($current_status, 'Pending approval from project leader') === false
            ) {
                throw new Exception('This invoice is not waiting for project leader review.');
            }

            $remark = "Invoice application has been approved by project leader.";

            // Kemas kini status invois dalam jadual 'invoices'
            $update_query = "UPDATE invoices 
                             SET invoice_status = 'Pending Verification' 
                             WHERE id = '$invoice_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update invoice status.');
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Invoice has been successfully submitted and tracked!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
