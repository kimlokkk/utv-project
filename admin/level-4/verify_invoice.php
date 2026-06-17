<?php
    session_start();
    include '../../db_connect/db_connect.php';
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        if (
            empty($_POST['project_id']) || empty($_POST['invoice_id']) ||
            empty($_POST['project_no']) || empty($_POST['staff_id']) /*||
            /*empty($_POST['invoice_no']) || !isset($_FILES['invoice_file'])*/
        ) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
    
        // Sanitize inputs
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $invoice_id = mysqli_real_escape_string($db, $_POST['invoice_id']);
        $staff_id = mysqli_real_escape_string($db, $_POST['staff_id']);
        //$invoice_no = mysqli_real_escape_string($db, $_POST['invoice_no']);
        $remark = "Invoice application has been verified by admin ($staff_id).";
        //$remark = "Invoice application ($invoice_no) has been verified by admin ($staff_id).";
        $date = date('Y-m-d H:i:s');
    
        /*// Handle file upload
        $uploadDir = '../../consultant/project-documents/invoice/';
        $filename = 'INVOICE_' . time() . '_' . basename($_FILES['invoice_file']['name']);
        $filepath = $uploadDir . $filename;
    
        if (!move_uploaded_file($_FILES['invoice_file']['tmp_name'], $filepath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload invoice file.']);
            exit;
        }
    
        // Begin DB transaction*/
        mysqli_begin_transaction($db);
    
        try {
            /*$update_query = "UPDATE invoices 
                             SET invoice_status = 'Pending Approval',
                                 invoice_no = '$invoice_no',
                                 invoice_file = '$filename'
                             WHERE id = '$invoice_id'";
            if (!mysqli_query($db, $update_query)) {
                throw new Exception('Failed to update invoice.');
            }*/
            
            $update_query = "UPDATE invoices 
                             SET invoice_status = 'Pending Approval'
                             WHERE id = '$invoice_id'";
            if (!mysqli_query($db, $update_query)) {
                throw new Exception('Failed to update invoice.');
            }
    
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date)
                              VALUES ('$project_id', '$project_no', '$remark', '$date')";
            if (!mysqli_query($db, $tracker_query)) {
                throw new Exception('Failed to insert tracker record.');
            }
    
            mysqli_commit($db);
            echo json_encode(['success' => true, 'message' => 'Invoice verified and saved successfully.']);
        } catch (Exception $e) {
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
