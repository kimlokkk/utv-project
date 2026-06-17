<?php
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        $project_id = $_POST['project_id'] ?? null;
        $invoice_no = $_POST['invoice_no'] ?? null;
        $client_name = $_POST['client_name'] ?? '';
        $project_type = $_POST['project_type'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $sst_amount = $_POST['sst_amount'] ?? 0;
        $total_amount = $amount + $sst_amount;
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_method = $_POST['payment_method'] ?? '';
        $purpose = $_POST['purpose'] ?? '';
        $status = "Pending";
        $created_by = $_SESSION['user_data']['id'];
        $created_at = date('Y-m-d H:i:s');
    
        // **Validasi Data**
        if (!$project_id || !$amount || !$payment_date || !$payment_method) {
            echo json_encode(["status" => "error", "message" => "Sila isi semua maklumat pembayaran yang diperlukan!"]);
            exit;
        }
    
        // **Mulakan transaksi**
        mysqli_begin_transaction($db);
    
        try {
            // **INSERT ke dalam table `payments`**
            $sql = "INSERT INTO payments (project_id, invoice_no, client_name, project_type, amount, sst_amount, payment_date, payment_method, purpose, status, created_by) 
                    VALUES ('$project_id', '$invoice_no', '$client_name', '$project_type', '$amount', '$sst_amount', '$payment_date', '$payment_method', '$purpose', '$status', '$created_by')";
            
            if (!mysqli_query($db, $sql)) {
                throw new Exception("Failed to insert payment: " . mysqli_error($db));
            }
    
            $payment_id = mysqli_insert_id($db);
            error_log("✅ Payment Inserted: ID $payment_id | Project: $project_id | Amount (RM $total_amount)");
    
            // **Jika ada invois, update Invoice Report**
            if (!empty($invoice_no)) {
                $update_invoice = "UPDATE invoices 
                                   SET paid_amount = paid_amount + $total_amount, 
                                       invoice_status = CASE 
                                                   WHEN total_amount <= paid_amount + $total_amount 
                                                   THEN 'Paid' 
                                                   ELSE 'Partial' 
                                               END 
                                   WHERE invoice_no = '$invoice_no'";
    
                if (!mysqli_query($db, $update_invoice)) {
                    throw new Exception("Failed to update invoice: " . mysqli_error($db));
                }
    
                error_log("✅ Invoice Updated: Invoice No: $invoice_no | Amount Paid: RM $total_amount");
            }
    
            // **Insert ke dalam `project_ledger` sebagai "Received (DR)"**
            $ledger_sql = "INSERT INTO project_ledger (project_id, payment_id, transaction_desc, transaction_type, amount, created_at) 
                           VALUES ('$project_id', '$payment_id', 'Payment received ($purpose)', 'Debit', '$total_amount', '$created_at')";
    
            if (!mysqli_query($db, $ledger_sql)) {
                throw new Exception("Failed to insert into project_ledger: " . mysqli_error($db));
            }
    
            error_log("✅ Project Ledger Inserted: Payment ID: $payment_id | Amount: RM $total_amount");
    
            // **Insert ke dalam `project_tracker`**
            $remark = "Payment of RM " . number_format($total_amount, 2) . " has been recorded by admin for Invoice No: $invoice_no.";
            $tracker_sql = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                            VALUES ('$project_id', (SELECT project_no FROM project WHERE id = '$project_id'), '$remark', '$created_at')";
    
            if (!mysqli_query($db, $tracker_sql)) {
                throw new Exception("Failed to insert into project_tracker: " . mysqli_error($db));
            }
    
            error_log("✅ Project Tracker Updated: Project ID: $project_id | Remark: $remark");
    
            // **Commit semua perubahan**
            mysqli_commit($db);
    
            echo json_encode(["status" => "success", "message" => "Payment successfully saved!", "payment_id" => $payment_id]);
    
        } catch (Exception $e) {
            // **Rollback jika ada error**
            mysqli_rollback($db);
            error_log("❌ ERROR: " . $e->getMessage());
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    
        mysqli_close($db);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request!"]);
    }
?>