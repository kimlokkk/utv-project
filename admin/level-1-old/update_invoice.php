<?php
session_start();
include '../db_connect/db_connect.php';
include '../function/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $project_id = $_POST['project_id'] ?? null;
    $project_no = $_POST['project_no'] ?? null;
    $invoice_id = $_POST['invoice_id'] ?? null;
    $invoice_status = $_POST['invoice_status'] ?? null;
    $invoice_no = $_POST['invoice_no'] ?? null;
    $total_invoice = $_POST['total_invoice'] ?? null;
    $date = date('Y-m-d H:i:s');

    // Validasi data
    if (!$invoice_id || !$invoice_status || !$invoice_no) {
        echo json_encode(["status" => "error", "message" => "Sila isi semua maklumat yang diperlukan!"]);
        exit;
    }

    // **UPLOAD FILE INVOICE**
    $new_file_name = null;
    if (!empty($_FILES['invoice_file']['name'])) {
        $file_name = $_FILES['invoice_file']['name'];
        $file_tmp = $_FILES['invoice_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(["status" => "error", "message" => "Fail tidak dibenarkan! Hanya PDF, JPG, PNG, DOCX dibenarkan."]);
            exit;
        }

        // Simpan fail ke folder
        $upload_path = "../consultant/project-documents/invoice/";
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $new_file_name = "invoice_" . time() . "." . $file_ext;
        move_uploaded_file($file_tmp, $upload_path . $new_file_name);
    }

    // **UPDATE DATABASE (INVOICES)**
    if ($new_file_name) {
        $sql = "UPDATE invoices 
                SET invoice_status = '$invoice_status', 
                    invoice_no = '$invoice_no', 
                    invoice_file = '$new_file_name', 
                    updated_at = NOW() 
                WHERE id = '$invoice_id'";
    } else {
        $sql = "UPDATE invoices 
                SET invoice_status = '$invoice_status', 
                    invoice_no = '$invoice_no', 
                    updated_at = NOW() 
                WHERE id = '$invoice_id'";
    }

    if (mysqli_query($db, $sql)) {

        // **Masukkan rekod ke dalam `project_tracker`**
        $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                          VALUES ('$project_id', '$project_no', 'Invoice status, invoice no and invoice file has been updated by admin', '$date')";
        $tracker_result = mysqli_query($db, $tracker_query);

        if (!$tracker_result) {
            echo json_encode(["status" => "error", "message" => "Invoice updated, but failed to insert project tracker record!"]);
            exit;
        }

        // **INSERT ke `project_ledger`**
        $ledger_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                         VALUES ('$project_id', 'Invoice application for $invoice_no', 'Credit', '$total_invoice', NOW())";
        $ledger_result = mysqli_query($db, $ledger_query);

        if (!$ledger_result) {
            echo json_encode(["status" => "error", "message" => "Invoice & tracker updated, but failed to insert project ledger!"]);
            exit;
        }

        echo json_encode(["status" => "success", "message" => "Invoice, tracker, and ledger inserted successfully!"]);

    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update invoice!"]);
    }

    mysqli_close($db);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request!"]);
}
?>
