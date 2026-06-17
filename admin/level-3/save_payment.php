<?php
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        $project_id     = $_POST['project_id'] ?? null;
        $invoice_no     = $_POST['invoice_no'] ?? null;
        $client_name    = $_POST['client_name'] ?? '';
        $project_type   = $_POST['project_type'] ?? '';
        $amount         = floatval($_POST['amount'] ?? 0);
        $sst_amount     = floatval($_POST['sst_amount'] ?? 0);
        $total_amount   = $amount + $sst_amount;
        $payment_date   = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_method = $_POST['payment_method'] ?? '';
        $purpose        = $_POST['purpose'] ?? '';
        $status         = "Pending";
        $created_by     = $_SESSION['user_data']['id'];
        $created_at     = date('Y-m-d H:i:s');
    
        // Validation
        if (!$project_id || !$amount || !$payment_date || !$payment_method) {
            echo json_encode(["status" => "error", "message" => "Sila isi semua maklumat pembayaran yang diperlukan!"]);
            exit;
        }
    
        mysqli_begin_transaction($db);
    
        try {
            // Insert into payments
            $sql = "INSERT INTO payments (project_id, invoice_no, client_name, project_type, amount, sst_amount, payment_date, payment_method, purpose, status, created_by) 
                    VALUES ('$project_id', '$invoice_no', '$client_name', '$project_type', '$amount', '$sst_amount', '$payment_date', '$payment_method', '$purpose', '$status', '$created_by')";
    
            if (!mysqli_query($db, $sql)) {
                throw new Exception("Failed to insert payment: " . mysqli_error($db));
            }
    
            $payment_id = mysqli_insert_id($db);
            error_log("✅ Payment Inserted: ID $payment_id | Project: $project_id | Amount: RM $total_amount");
    
            // Update invoice if exists
            if (!empty($invoice_no)) {
                $update_invoice = "UPDATE invoices 
                                   SET paid_amount = paid_amount + $total_amount, 
                                       invoice_status = CASE 
                                           WHEN total_amount <= paid_amount + $total_amount THEN 'Paid' 
                                           ELSE 'Partial' 
                                       END 
                                   WHERE invoice_no = '$invoice_no'";
    
                if (!mysqli_query($db, $update_invoice)) {
                    throw new Exception("Failed to update invoice: " . mysqli_error($db));
                }
    
                error_log("✅ Invoice Updated: $invoice_no | +RM $total_amount");
            }
    
            // Insert into project_ledger (Debit for amount only)
            $ledger_sql_1 = "INSERT INTO project_ledger (project_id, payment_id, transaction_desc, transaction_type, amount, created_at) 
                             VALUES ('$project_id', '$payment_id', 'Payment received ($purpose)', 'Debit', '$amount', '$created_at')";
    
            if (!mysqli_query($db, $ledger_sql_1)) {
                throw new Exception("Failed to insert debit ledger: " . mysqli_error($db));
            }
    
            // Insert SST as Credit if exists
            if ($sst_amount > 0) {
                $ledger_sql_2 = "INSERT INTO project_ledger (project_id, payment_id, transaction_desc, transaction_type, amount, created_at) 
                                 VALUES ('$project_id', '$payment_id', 'SST Charge', 'Credit', '$sst_amount', '$created_at')";
    
                if (!mysqli_query($db, $ledger_sql_2)) {
                    throw new Exception("Failed to insert credit ledger (SST): " . mysqli_error($db));
                }
            }
    
            error_log("✅ Ledger Updated: DR RM $amount, CR (SST) RM $sst_amount");
    
            // Insert into project_tracker
            $remark = "Payment of RM " . number_format($total_amount, 2) . " has been recorded by admin for Invoice No: $invoice_no.";
            $tracker_sql = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                            VALUES ('$project_id', (SELECT project_no FROM project WHERE id = '$project_id'), '$remark', '$created_at')";
    
            if (!mysqli_query($db, $tracker_sql)) {
                throw new Exception("Failed to insert into tracker: " . mysqli_error($db));
            }
    
            // Send email to Project Leader
            $leader_q = "SELECT u.email FROM project p JOIN user u ON p.project_leader_id = u.id WHERE p.id = '$project_id'";
            $leader_r = mysqli_query($db, $leader_q);
    
            if ($leader_r && mysqli_num_rows($leader_r) > 0) {
                $leader = mysqli_fetch_assoc($leader_r);
                $leader_email = $leader['email'];
    
                $subject = "Payment Received - Project #$project_id";
                $message = "Dear Project Leader,<br><br>
                A payment of <strong>RM " . number_format($total_amount, 2) . "</strong> has been recorded for your project.<br><br>
                <strong>Purpose:</strong> $purpose<br>
                <strong>Payment Date:</strong> $payment_date<br><br>
                This is a system notification.<br><br>
                Regards,<br>Finance System";
    
                $headers  = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                $headers .= "From: noreply@domei.io" . "\r\n";
    
                mail($leader_email, $subject, $message, $headers);
            }
    
            mysqli_commit($db);
    
            echo json_encode([
                "status" => "success",
                "message" => "Payment successfully saved!",
                "payment_id" => $payment_id
            ]);
    
        } catch (Exception $e) {
            mysqli_rollback($db);
            error_log("❌ ERROR: " . $e->getMessage());
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    
        mysqli_close($db);
    
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request!"]);
    }
?>
