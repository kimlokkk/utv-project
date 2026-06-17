<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa

    date_default_timezone_set('Asia/Kuala_Lumpur');
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
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
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
                        <h4 class="text-themecolor">Add Payment</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="payment-listing.php">Payment Listing</a></li>
                                <li class="breadcrumb-item active">Add Payment</li>
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
                        <form id="paymentForm" enctype="multipart/form-data">
                            <input type="hidden" name="created_by" value="<?php echo htmlspecialchars($userData['staff_id']); ?>">

                            <div class="card">
                                <h3 class="card-header bg-success text-white">Payment Information</h3>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        Use this form to record money received. If the payment is linked to an invoice, the invoice listing and project ledger will be updated automatically.
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Category <span class="text-danger">*</span></label>
                                                <select class="form-control" id="payment_category" name="payment_category" required>
                                                    <option value="Invoice Payment" selected>Invoice Payment</option>
                                                    <option value="Advance Refund">Advance Refund</option>
                                                    <option value="Fund Received">Fund Received</option>
                                                    <option value="Refund Received">Refund Received</option>
                                                    <option value="Other Received">Other Received</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Project No <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="project_id" name="project_id" required style="width:100%;">
                                                    <option value="">Search Project No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="invoiceSelectWrapper">
                                            <div class="form-group">
                                                <label>Invoice No <span class="text-danger invoice-required-star">*</span></label>
                                                <select class="form-control select2" id="invoice_id" name="invoice_id" style="width:100%;">
                                                    <option value="">Search Invoice No</option>
                                                </select>
                                                <small class="text-muted">Required for Invoice Payment. Optional for other payment categories.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="projectInfoWrapper" style="display:none;">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Project Title</label>
                                                <input type="text" class="form-control" id="project_title" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Client Company</label>
                                                <input type="text" class="form-control" id="client_company_name" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Project Leader</label>
                                                <input type="text" class="form-control" id="project_leader" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="invoiceInfoWrapper" style="display:none;">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Total Invoice (RM)</label>
                                                <input type="text" class="form-control" id="total_invoice" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Paid Amount (RM)</label>
                                                <input type="text" class="form-control" id="paid_amount" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Outstanding Amount (RM)</label>
                                                <input type="text" class="form-control" id="outstanding_amount" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h3 class="card-header bg-success text-white">Received Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Amount Received (RM) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="amount_received" name="amount_received" min="0.01" step="0.01" required placeholder="e.g. 5000.00">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payer Name</label>
                                                <input type="text" class="form-control" id="payer_name" name="payer_name" placeholder="e.g. Client / Consultant / Organisation name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Payment Method</label>
                                                <select class="form-control" id="payment_method" name="payment_method">
                                                    <option value="">Select Payment Method</option>
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Online Banking">Online Banking</option>
                                                    <option value="FPX">FPX</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Bank Reference</label>
                                                <input type="text" class="form-control" id="bank_reference" name="bank_reference" placeholder="Bank transaction reference">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Receipt Reference</label>
                                                <input type="text" class="form-control" id="receipt_reference" name="receipt_reference" placeholder="Optional receipt reference">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Payment Attachment</label>
                                        <small class="text-muted d-block m-b-5">Optional. Upload proof of payment if available.</small>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="payment_attachment" name="payment_attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <label class="custom-file-label" for="payment_attachment">Choose file...</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Enter payment notes..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="submit" id="btnSavePayment" class="btn btn-lg btn-success">
                                        Save Payment
                                    </button>
                                    <a href="payment-listing.php" class="btn btn-lg btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
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
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <script>
        // 1. Auto update label when file selected
        $('#payment_attachment').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choose file...');
        });

        // 2. Initialize Select2
        $(document).ready(function() {
            $(".select2").select2();

            $("#project_id").select2({
                placeholder: "Search Project No",
                ajax: {
                    url: "search_project.php",
                    type: "POST",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return { keyword: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.project_no + " - " + item.project_title,
                                    project_no: item.project_no,
                                    project_title: item.project_title,
                                    client_company_name: item.client_company_name,
                                    project_leader: item.project_leader
                                };
                            })
                        };
                    },
                    cache: true
                }
            }).on("select2:select", function(e) {
                var data = e.params.data;

                $("#project_title").val(data.project_title);
                $("#client_company_name").val(data.client_company_name);
                $("#project_leader").val(data.project_leader);
                $("#payer_name").val(data.client_company_name);
                $("#projectInfoWrapper").show();

                $("#invoice_id").val(null).trigger("change");
                $("#invoiceInfoWrapper").hide();
                $("#total_invoice").val('');
                $("#paid_amount").val('');
                $("#outstanding_amount").val('');
            });

            $("#invoice_id").select2({
                placeholder: "Search Invoice No",
                ajax: {
                    url: "search_invoice.php",
                    type: "POST",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return { 
                            keyword: params.term,
                            project_id: $("#project_id").val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.invoice_no + " | Outstanding: RM " + item.outstanding_amount,
                                    invoice_no: item.invoice_no,
                                    total_invoice: item.total_invoice,
                                    paid_amount: item.paid_amount,
                                    outstanding_amount: item.outstanding_amount,
                                    client_company_name: item.client_company_name
                                };
                            })
                        };
                    },
                    cache: true
                }
            }).on("select2:select", function(e) {
                var data = e.params.data;

                $("#total_invoice").val(data.total_invoice);
                $("#paid_amount").val(data.paid_amount);
                $("#outstanding_amount").val(data.outstanding_amount);
                $("#amount_received").val(data.outstanding_amount);
                $("#payer_name").val(data.client_company_name);
                $("#invoiceInfoWrapper").show();
            });
        });

        // 3. Toggle invoice requirement by payment category
        $('#payment_category').on('change', function () {
            const category = $(this).val();

            if (category === 'Invoice Payment') {
                $('.invoice-required-star').show();
                $('#invoiceSelectWrapper small').text('Required for Invoice Payment.');
            } else {
                $('.invoice-required-star').hide();
                $('#invoiceSelectWrapper small').text('Optional for this payment category.');
            }
        });

        $('#payment_category').trigger('change');

        // 4. Submit form with validation and AJAX
        $("#paymentForm").on("submit", function (e) {
            e.preventDefault();

            const category = $('#payment_category').val();
            const projectId = $('#project_id').val();
            const invoiceId = $('#invoice_id').val();
            const amountReceived = parseFloat($('#amount_received').val()) || 0;
            const paymentDate = $('#payment_date').val();

            if (!category || !projectId || !paymentDate || amountReceived <= 0) {
                Swal.fire({
                    title: 'Missing Fields',
                    text: 'Please complete payment category, project, payment date and amount received.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (category === 'Invoice Payment' && !invoiceId) {
                Swal.fire({
                    title: 'Invoice Required',
                    text: 'Please select an invoice for Invoice Payment.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const formData = new FormData(this);

            Swal.fire({
                title: 'Save Payment?',
                text: 'This will record the payment, update ledger and update invoice listing if invoice is selected.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#btnSavePayment').prop('disabled', true).html('Saving...');

                    Swal.fire({
                        title: 'Saving Payment...',
                        text: 'Please wait while the payment is being saved.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "save_payment_listing.php",
                        method: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonText: "OK",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = "payment-listing.php";
                                });
                            } else {
                                $('#btnSavePayment').prop('disabled', false).html('Save Payment');

                                Swal.fire({
                                    title: "Error!",
                                    text: response.message || "Something went wrong.",
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#btnSavePayment').prop('disabled', false).html('Save Payment');

                            console.error("AJAX Error:", xhr.responseText);

                            Swal.fire({
                                title: "Error!",
                                text: "Failed to save payment. " + (xhr.responseText || error),
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>