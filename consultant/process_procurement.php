<?php
    // Start the session and include required files
    session_start();
    include '../db_connect/db_connect.php';
    include '../function/function.php'; 
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        $missingFields = [];
    
        // Keperluan umum
        if (empty($_POST['project_id'])) $missingFields[] = 'Project ID';
        if (empty($_POST['goods_or_services'])) $missingFields[] = 'Goods or Services type';
        if (empty($_POST['application_type'])) $missingFields[] = 'Application Type';
        
        // Keperluan berdasarkan application_type
        if ($_POST['application_type'] === 'Vendor Payment') {
            if (empty($_POST['payment_type'])) $missingFields[] = 'Payment Type';
            if (empty($_POST['goods_service_type'])) $missingFields[] = 'Goods/Service Type';
            if (empty($_POST['total_to_pay'])) $missingFields[] = 'Total to Pay';
            if (empty($_POST['percentage_to_pay'])) $missingFields[] = 'Percentage to Pay';
            if (empty($_POST['pricing_term_payment'])) $missingFields[] = 'Pricing Term Payment';
            if (empty($_POST['delivery_time'])) $missingFields[] = 'Delivery Time';
            if (empty($_POST['product_quality'])) $missingFields[] = 'Product Quality';
            if (empty($_POST['response_time'])) $missingFields[] = 'Response Time';
            if (!isset($_POST['approve_payment'])) $missingFields[] = 'Approval for Payment';
        } elseif ($_POST['application_type'] === 'Purchase Order Application') {
            if (empty($_POST['criteria'])) $missingFields[] = 'Criteria';
            if (!isset($_FILES['quotation_file'])) $missingFields[] = 'Quotation File';
        }
        
        // Semak jika ada medan yang hilang
        if (!empty($missingFields)) {
            $missingFieldsList = implode(', ', $missingFields);
            echo json_encode([
                'success' => false,
                'message' => "Incomplete form data: {$missingFieldsList}."
            ]);
            exit;
        }
    
        // Ambil data dari form
        $member_id = mysqli_real_escape_string($db, $_POST['member_id']);
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $goods_or_services = mysqli_real_escape_string($db, $_POST['goods_or_services']);
        $application_type = mysqli_real_escape_string($db, $_POST['application_type']);
        $payment_type = mysqli_real_escape_string($db, $_POST['payment_type'] ?? null);
        $po_number = mysqli_real_escape_string($db, $_POST['po_number'] ?? null);
        $criteria = $_POST['criteria'] ?? [];
        $goods_service_type = mysqli_real_escape_string($db, $_POST['goods_service_type'] ?? null);
        $purchase_reason = mysqli_real_escape_string($db, $_POST['purchase_reason'] ?? null);
        $total_to_pay = mysqli_real_escape_string($db, $_POST['total_to_pay'] ?? null);
        $percentage_to_pay = mysqli_real_escape_string($db, $_POST['percentage_to_pay'] ?? null);
        $custodian_of_asset = mysqli_real_escape_string($db, $_POST['custodian_of_asset'] ?? null);
        $location = mysqli_real_escape_string($db, $_POST['location'] ?? null);
        $pricing_term_payment = mysqli_real_escape_string($db, $_POST['pricing_term_payment'] ?? null);
        $delivery_time = mysqli_real_escape_string($db, $_POST['delivery_time'] ?? null);
        $product_quality = mysqli_real_escape_string($db, $_POST['product_quality'] ?? null);
        $response_time = mysqli_real_escape_string($db, $_POST['response_time'] ?? null);
        $approve_payment = mysqli_real_escape_string($db, $_POST['approve_payment'] === 'Yes' ? 1 : 0);
    
        // Vendor Details
        $vendor_id = mysqli_real_escape_string($db, $_POST['vendor_id'] ?? null);
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Simpan ke dalam jadual 'procurement'
            $procurement_query = "INSERT INTO procurement (project_id, member_id, vendor_id, procurement_type, application_type, payment_type, po_number, status) 
                                  VALUES ('$project_id', '$member_id', '$vendor_id', '$goods_or_services', '$application_type', '$payment_type', '$po_number', 'Pending approval from project leader')";
            if (!mysqli_query($db, $procurement_query)) {
                throw new Exception('Failed to save procurement.');
            }
    
            // Dapatkan ID procurement terakhir
            $procurement_id = mysqli_insert_id($db);
    
            if ($application_type === 'Purchase Order Application') {
                // Validasi dan muat naik fail
                if (isset($_FILES['quotation_file']) && $_FILES['quotation_file']['error'] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['quotation_file']['name'];
                    $file_tmp = $_FILES['quotation_file']['tmp_name'];
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $unique_file_name = uniqid('quotation_', true) . '.' . $file_extension;
                    $upload_dir = "project-documents/quotation-vendor/";
    
                    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
                        throw new Exception("Failed to create upload directory.");
                    }
    
                    if (!move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
                        throw new Exception("Failed to upload quotation file.");
                    }
                    
                    // Simpan input perunding ke dalam 'procurement_vendor_payment_consultant_input'
                    $consultant_input = "INSERT INTO procurement_consultant_input (procurement_id, goods_service_type, purchase_reason, location, quotation_file) 
                                             VALUES ('$procurement_id', '$goods_service_type', '$purchase_reason', '$location', 'https://utv.domei.io/consultant/project-documents/quotation-vendor/$unique_file_name')";
                    if (!mysqli_query($db, $consultant_input)) {
                        $error_message = mysqli_error($db); // Ambil mesej ralat dari MySQL
                        throw new Exception('Failed to save consultant input. MySQL Error: ' . $error_message);
                    }
    
                    $criteria_query = "INSERT INTO procurement_criteria (procurement_id, criteria, is_checked) VALUES ";
                    $criteria_values = [];
                    foreach ($criteria as $criterion) {
                        $criteria_values[] = "('$procurement_id', '" . mysqli_real_escape_string($db, $criterion) . "', 1)";
                    }
                    $criteria_query .= implode(', ', $criteria_values);
                    if (!mysqli_query($db, $criteria_query)) {
                        throw new Exception('Failed to save procurement criteria.');
                    }
                }
            } elseif ($application_type === 'Vendor Payment') {
                $evaluation_query = "INSERT INTO procurement_vendor_payment_evaluations (procurement_id, pricing_term_payment, delivery_time, product_quality, response_time, approve_payment) 
                                     VALUES ('$procurement_id', '$pricing_term_payment', '$delivery_time', '$product_quality', '$response_time', '$approve_payment')";
                if (!mysqli_query($db, $evaluation_query)) {
                    throw new Exception('Failed to save vendor payment evaluation.');
                }
                
                // Simpan input perunding ke dalam 'procurement_vendor_payment_consultant_input'
                $vendor_payment_query = "INSERT INTO procurement_vendor_payment_consultant_input (procurement_id, goods_service_type, purchase_reason, total_to_pay, percentage_to_pay, custodian_of_asset, location) 
                                         VALUES ('$procurement_id', '$goods_service_type', '$purchase_reason', '$total_to_pay', '$percentage_to_pay', '$custodian_of_asset', '$location')";
                if (!mysqli_query($db, $vendor_payment_query)) {
                    $error_message = mysqli_error($db); // Ambil mesej ralat dari MySQL
                    throw new Exception('Failed to save vendor payment consultant input. MySQL Error: ' . $error_message);
                }
    
                // Tetapkan direktori untuk setiap jenis dokumen
                $document_directories = [
                    'goods_received_notes' => 'project-documents/goods-notes/',
                    'service_confirmation_form' => 'project-documents/service-confirmation/',
                    'delivery_order' => 'project-documents/delivery-order/',
                    'supplier_invoice' => 'project-documents/supplier-invoice/'
                ];
                
                // Tentukan fail yang diperlukan berdasarkan jenis Goods atau Services
                $required_documents = $goods_or_services === 'Goods'
                    ? ['goods_received_notes', 'delivery_order', 'supplier_invoice']
                    : ['service_confirmation_form', 'delivery_order', 'supplier_invoice'];
                
                // Simpan setiap fail dalam direktori masing-masing
                foreach ($required_documents as $input_name) {
                    if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES[$input_name]['name'];
                        $file_tmp = $_FILES[$input_name]['tmp_name'];
                        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $unique_file_name = uniqid($input_name . '_', true) . '.' . $file_extension;
                
                        // Dapatkan direktori untuk fail semasa
                        $upload_dir = $document_directories[$input_name] ?? 'project-documents/';
                        
                        error_log("Processing $input_name. File name: $file_name. Temp file: $file_tmp. Directory: $upload_dir");
                
                        // Pastikan direktori wujud
                        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
                            error_log("Failed to create upload directory $upload_dir");
                            throw new Exception("Failed to create upload directory $upload_dir.");
                        }
                
                        // Pindahkan fail ke direktori destinasi
                        if (!move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
                            error_log("Failed to move uploaded file: $file_tmp to $upload_dir$unique_file_name");
                            throw new Exception("Failed to upload file: $file_name.");
                        }
                
                        // Simpan maklumat fail ke pangkalan data
                        $document_query = "INSERT INTO procurement_vendor_payment_documents (procurement_id, document_type, file_path) 
                                           VALUES ('$procurement_id', '$input_name', '$unique_file_name')";
                        if (!mysqli_query($db, $document_query)) {
                            error_log("MySQL Error: " . mysqli_error($db));
                            throw new Exception("Failed to save vendor payment document: $file_name.");
                        }
                        error_log("File $file_name uploaded to $upload_dir and record inserted.");
                    } else {
                        error_log("File $input_name not uploaded or an error occurred.");
                    }
                }
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Procurement application has been successfully submitted to project leader!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
