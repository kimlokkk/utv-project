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
        $reject_remark = mysqli_real_escape_string($db, trim($_GET['reject_remark'] ?? ''));
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa

        if ($reject_remark === '') {
            echo json_encode(['success' => false, 'message' => 'Please provide a rejection remark.']);
            exit;
        }
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            $invoice_query = "
                SELECT i.invoice_status, p.leader_id, p.project_no
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
                throw new Exception('Only the project leader can reject this invoice.');
            }

            if (
                stripos($current_status, 'Pending Leader Review') === false &&
                stripos($current_status, 'Pending approval from project leader') === false
            ) {
                throw new Exception('This invoice is not waiting for project leader review.');
            }

            // Kemas kini status invois dalam jadual 'invoices'
            $update_query = "UPDATE invoices 
                             SET invoice_status = 'Rejected by project leader - $reject_remark'
                             WHERE id = '$invoice_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update invoice status.');
            }

            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', 'Invoice application has been rejected by project leader.', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);

            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Invoice has been rejected!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
