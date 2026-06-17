<?php
    // Start the session and include required files
    header('Content-Type: application/json');
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Validasi input
        if (empty($_GET['project_id']) || empty($_GET['invoice_id']) || empty($_GET['project_no']) || empty($_GET['staff_id']) || empty($_GET['return_remark'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari form
        $project_id = mysqli_real_escape_string($db, $_GET['project_id']);
        $project_no = mysqli_real_escape_string($db, $_GET['project_no']);
        $invoice_id = mysqli_real_escape_string($db, $_GET['invoice_id']);
        $invoice_no = mysqli_real_escape_string($db, $_GET['invoice_no'] ?? '');
        $staff_id = mysqli_real_escape_string($db, $_GET['staff_id']);
        $return_to = mysqli_real_escape_string($db, $_GET['return_to'] ?? 'Consultant');
        $return_remark = mysqli_real_escape_string($db, $_GET['return_remark']);
        $invoice_label = $invoice_no !== '' ? " ($invoice_no)" : '';
        $remark = "Invoice application$invoice_label has been returned to $return_to - $return_remark ($staff_id)";
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status invois dalam jadual 'invoices'
            $update_query = "UPDATE invoices 
                             SET invoice_status = 'Returned',
                                 return_to = '$return_to',
                                 return_remark = '$return_remark'
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
    
            echo json_encode(['success' => true, 'message' => 'Invoice has been returned and tracked!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
