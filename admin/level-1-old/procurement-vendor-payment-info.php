<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $project_id =   $_GET['projectId'];
    $procurement_id =   $_GET['procurementId'];
    $query = "SELECT 
                p.id AS project_id,
                p.project_no,
                p.project_title,
                p.project_leader,
                pr.*,
                pv.*,
                pvpc.*,
                pvpe.*
            FROM project p
            INNER JOIN procurement pr ON p.id = pr.project_id
            INNER JOIN procurement_vendors pv ON pr.id = pv.procurement_id
            INNER JOIN procurement_vendor_payment_consultant_input pvpc ON pr.id = pvpc.procurement_id
            INNER JOIN procurement_vendor_payment_evaluations pvpe ON pr.id = pvpe.procurement_id
            WHERE p.id = '$project_id' AND pr.id = '$procurement_id'
            ORDER BY p.id DESC";  
    $result =   mysqli_query($db, $query);
    while($row =   mysqli_fetch_array($result))  
    {
        
        $project_no     = $row['project_no'];
        $project_title  = $row['project_title'];
        $project_leader = $row['project_leader'];
        
        //procurement
        $procurement_type     = $row['procurement_type'];
        $application_type     = $row['application_type'];
        $payment_type     = $row['payment_type'];
        $po_number     = $row['po_number'];
        $status     = $row['status'];
        
        //vendors
        $vendor_name     = $row['vendor_name'];
        $vendor_ssm_no     = $row['vendor_ssm_no'];
        $mailing_address     = $row['mailing_address'];
        $pic_name     = $row['pic_name'];
        $pic_email     = $row['pic_email'];
        $phone_no     = $row['phone_no'];
        $bank_name     = $row['bank_name'];
        $bank_account_no     = $row['bank_account_no'];
        
        //consultant input
        $goods_service_type     = $row['goods_service_type'];
        $purchase_reason     = $row['purchase_reason'];
        $total_to_pay     = $row['total_to_pay'];
        $percentage_to_pay     = $row['percentage_to_pay'];
        $custiodian_of_asset     = $row['custiodian_of_asset'];
        $location     = $row['location'];
        
        //evaluations
        $pricing_term_payment     = $row['pricing_term_payment'];
        $delivery_time     = $row['delivery_time'];
        $product_quality     = $row['product_quality'];
        $response_time     = $row['response_time'];
        $approve_payment     = $row['approve_payment'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="skin-green fixed-layout">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'include/preloader.php'; ?>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php include 'include/topbar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php include 'include/left_sidebar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Procurement Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Procurement Application</a></li>
                                <li class="breadcrumb-item active">Procurement Info</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Info box -->
                <!-- ============================================================== -->
                <!-- Row -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- Project Details -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Details</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Number</strong></label>
                                            <h5><?php echo $project_no; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Title</strong></label>
                                            <h5><?php echo $project_title; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Leader Name</strong></label>
                                            <h5><?php echo $project_leader; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Project Leader Email</strong></label>
                                            <h5><?php echo $userData['email']; ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Vendor Details -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Vendor Details</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor Name</strong></label>
                                            <h5><?php echo $vendor_name; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor SSM No</strong></label>
                                            <h5><?php echo $vendor_ssm_no; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor Address</strong></label>
                                            <h5><?php echo $mailing_address; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor PIC Name</strong></label>
                                            <h5><?php echo $pic_name; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor PIC Email</strong></label>
                                            <h5><?php echo $pic_email; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Vendor Phone No</strong></label>
                                            <h5><?php echo $phone_no; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Bank Name</strong></label>
                                            <h5><?php echo $bank_name; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Bank Account No</strong></label>
                                            <h5><?php echo $bank_account_no; ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Evaluation Criteria -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Evaluation Criteria</h3>
                            <div class="card-body">
                                <label><strong>Criteria Evaluation</strong></label>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Criteria</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Pricing and Term Payment</th>
                                            <th><h5><?php echo $pricing_term_payment; ?></h5></th>
                                        </tr>
                                        <tr>
                                            <th>Delivery Time</th>
                                            <th><h5><?php echo $delivery_time; ?></h5></th>
                                        </tr>
                                        <tr>
                                            <th>Products/Services Quality</th>
                                            <th><h5><?php echo $product_quality; ?></h5></th>
                                        </tr>
                                        <tr>
                                            <th>Response Time</th>
                                            <th><h5><?php echo $response_time; ?></h5></th>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="form-group">
                                    <label><strong>Approve for Payment</strong></label>
                                    <h5><?php echo ($approve_payment == 1) ? "Yes" : "No"; ?></h5>
                                </div>
                            </div>
                        </div>
                        <!-- Attachments -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Attachments</h3>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Document</th>
                                            <th>Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Ambil ID pengguna semasa
                                        $user_id = $userData['id'];
                                        $counter = 1;
                                    
                                        // Query untuk mengambil dokumen berdasarkan procurement_id
                                        $query2 = "SELECT 
                                                    document_type,
                                                    file_path
                                                FROM procurement_vendor_payment_documents
                                                WHERE procurement_id = '$procurement_id'
                                                ORDER BY id DESC";
                                    
                                        $result2 = mysqli_query($db, $query2);
                                    
                                        while ($row = mysqli_fetch_array($result2)) {
                                            $document_type = $row['document_type'];
                                            $file_name = $row['file_path'];
                                    
                                            // Tentukan base URL berdasarkan jenis dokumen
                                            $base_url = "";
                                            if ($document_type === "supplier_invoice") {
                                                $base_url = "https://utv.domei.io/consultant/project-documents/supplier-invoice/";
                                            } elseif ($document_type === "delivery_order") {
                                                $base_url = "https://utv.domei.io/consultant/project-documents/delivery-order/";
                                            } elseif ($document_type === "service_confirmation_form") {
                                                $base_url = "https://utv.domei.io/consultant/project-documents/service-confirmation/";
                                            } elseif ($document_type === "goods_received_notes") {
                                                $base_url = "https://utv.domei.io/consultant/project-documents/goods-received-notes/";
                                            }
                                    
                                            // URL penuh dokumen
                                            $full_url = $base_url . $file_name;
                                        ?>
                                            <tr>
                                                <th><?php echo $counter++; ?></th>
                                                <th><?php echo htmlspecialchars($document_type); ?></th>
                                                <th>
                                                    <a href="<?php echo htmlspecialchars($full_url); ?>" class="btn btn-info btn-sm" target="_blank" rel="noopener noreferrer">
                                                        View Document
                                                    </a>
                                                </th>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Additional Details -->
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Additional Details</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Type of Goods/Services Purchased</strong></label>
                                            <h5><?php echo $goods_service_type; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Reason for Purchase</strong></label>
                                            <h5><?php echo $purchase_reason; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Total to Pay (RM)</strong></label>
                                            <h5><?php echo $total_to_pay; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>% to Pay</strong></label>
                                            <h5><?php echo $percentage_to_pay; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Custodian of Asset</strong></label>
                                            <h5><?php echo $custodian_of_asset; ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Location</strong></label>
                                            <h5><?php echo $location; ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <?php include 'include/footer.php'; ?>
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Logout Modal -->
        <!-- ============================================================== -->
        <?php include 'include/logoutmodal.php'; ?>
        <!-- ============================================================== -->
        <!-- End Logout Modal -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap popper Core JavaScript -->
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        // Tunjukkan/hide bahagian attachment berdasarkan pilihan Goods/Services
        document.querySelector("select[name='goods_or_services']").addEventListener("change", function() {
            const goodsAttachment = document.getElementById("goodsAttachment");
            const serviceAttachment = document.getElementById("serviceAttachment");
    
            if (this.value === "Goods") {
                goodsAttachment.style.display = "block";
                serviceAttachment.style.display = "none";
            } else if (this.value === "Services") {
                goodsAttachment.style.display = "none";
                serviceAttachment.style.display = "block";
            } else {
                goodsAttachment.style.display = "none";
                serviceAttachment.style.display = "none";
            }
        });
    </script>
    <script>
        // Tunjuk/hide pilihan vendor payment options
        document.querySelector("select[name='application_type']").addEventListener("change", function() {
            const vendorPaymentOptions = document.getElementById("vendorPaymentOptions");
    
            if (this.value === "Vendor Payment") {
                vendorPaymentOptions.style.display = "block";
            } else {
                vendorPaymentOptions.style.display = "none";
            }
        });
    
        document.querySelector("select[name='payment_type']").addEventListener("change", function() {
            const poNumberField = document.getElementById("poNumberField");
    
            if (this.value === "PO Vendor Payment") {
                poNumberField.style.display = "block";
            } else {
                poNumberField.style.display = "none";
            }
        });
    </script>
    <script>
        document.getElementById('applicationType').addEventListener('change', function () {
            const applicationType = this.value;
            const purchaseOrderForm = document.getElementById('purchaseOrderForm');
            const vendorPaymentForm = document.getElementById('vendorPaymentForm');
        
            // Aktifkan form yang dipilih dan disable form yang lain
            if (applicationType === 'Purchase Order Application') {
                purchaseOrderForm.style.display = 'block';
                vendorPaymentForm.style.display = 'none';
        
                // Enable semua input dalam Purchase Order Form
                toggleFormInputs(purchaseOrderForm, true);
                // Disable semua input dalam Vendor Payment Form
                toggleFormInputs(vendorPaymentForm, false);
            } else if (applicationType === 'Vendor Payment') {
                purchaseOrderForm.style.display = 'none';
                vendorPaymentForm.style.display = 'block';
        
                // Enable semua input dalam Vendor Payment Form
                toggleFormInputs(vendorPaymentForm, true);
                // Disable semua input dalam Purchase Order Form
                toggleFormInputs(purchaseOrderForm, false);
            } else {
                purchaseOrderForm.style.display = 'none';
                vendorPaymentForm.style.display = 'none';
        
                // Disable semua input
                toggleFormInputs(purchaseOrderForm, false);
                toggleFormInputs(vendorPaymentForm, false);
            }
        });
        
        // Fungsi untuk toggle enable/disable semua input dalam form
        function toggleFormInputs(formElement, enable) {
            const inputs = formElement.querySelectorAll('input, select, textarea');
            for (const input of inputs) {
                input.disabled = !enable;
            }
        }
    </script>
    <script>
        $(document).on('click', '#approveProcurement', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once submitted, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons
        
                if (result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");
        
                    // Ambil borang menggunakan ID borang
                    const form = $('#procurementForm')[0]; // Gantikan #invoiceForm dengan ID borang anda
                    const formData = new FormData(form);
        
                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }
        
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'process_procurement.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);
        
                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'procurement.php'; // Alihkan ke halaman yang diinginkan
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire(
                                'Error!',
                                'An error occurred during submission. Please check the console for details.',
                                'error'
                            );
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Procurement application apporoval has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>