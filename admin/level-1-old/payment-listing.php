<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa
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
                        <h4 class="text-themecolor">Payment Listing</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Payment Listing</a></li>
                                <li class="breadcrumb-item active">Payment Listing</li></li>
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
                <?php
                // Ambil data ringkasan status pembayaran
                $total_received = $db->query("SELECT SUM(amount) AS total FROM payments WHERE status='completed'")->fetch_assoc()['total'];
                $total_pending = $db->query("SELECT COUNT(id) AS count FROM payments WHERE status='pending'")->fetch_assoc()['count'];
                $total_finance = $db->query("SELECT COUNT(id) AS count FROM payments WHERE status='approved'")->fetch_assoc()['count'];
                $total_completed = $db->query("SELECT COUNT(id) AS count FROM payments WHERE status='completed'")->fetch_assoc()['count'];
                ?>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Payment Received</h5>
                                <h3>RM <?= number_format($total_received, 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Pending Approvals</h5>
                                <h3><?= $total_pending ?> Transactions</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5>Pending Finance Processing</h5>
                                <h3><?= $total_finance ?> Transactions</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Completed Payments</h5>
                                <h3><?= $total_completed ?> Transactions</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Payment Listing</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="payment-listing-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Invoice No</th>
                                                <th>Client Name</th>
                                                <th>Amount (RM)</th>
                                                <th>Payment Date</th>
                                                <th>Payment Method</th>
                                                <th>Purpose</th>
                                                <th>Status</th>
                                                <th class="text-center">Generate Invoice Listing</th>
                                                <th class="text-center">Generate Receipt</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Query untuk tarik data payment dengan join project dan invoice
                                                $query = "SELECT p.*, pr.project_no
                                                            FROM payments p
                                                            INNER JOIN project pr ON pr.id = p.project_id
                                                            ORDER BY p.payment_date DESC
                                                            ";
                                                $result = mysqli_query($db, $query);
                
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $statusBadge = ($row['status'] == 'Completed') ? 'badge-success' :
                                                                   (($row['status'] == 'Pending') ? 'badge-warning' : 'badge-danger');
                
                                                    $formatted_amount = number_format($row['amount'], 2);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['project_no']); ?></td>
                                                <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                                                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                                <td>RM <?php echo $formatted_amount; ?></td>
                                                <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                
                                                <!-- Generate Invoice Listing -->
                                                <td class="text-center">
                                                    <?php if (!empty($row['invoice_no'])) { ?>
                                                        <button class="btn btn-sm btn-warning generate-invoice-btn" 
                                                                data-invoice="<?php echo htmlspecialchars($row['invoice_no']); ?>" 
                                                                data-project="<?php echo htmlspecialchars($row['project_id']); ?>">
                                                            Generate
                                                        </button>
                                                    <?php } else { echo "-"; } ?>
                                                </td>
                
                                                <!-- Generate Receipt -->
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-info generate-receipt-btn" 
                                                            data-invoice="<?php echo htmlspecialchars($row['invoice_no'] ?? ''); ?>" 
                                                            data-project="<?php echo htmlspecialchars($row['project_id']); ?>"
                                                            data-payment="<?php echo htmlspecialchars($row['id']); ?>">
                                                        Generate
                                                    </button>
                                                </td>
                
                                                <!-- Action Button -->
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-primary approve-btn" 
                                                            data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                        Approve
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Button untuk buka modal -->
                <a href="add-payment-listing.php" class="btn btn-success mb-3">
                    + Add Payment
                </a>
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
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#payment-listing-table').DataTable();
            var table = $('#example').DataTable({
                "columnDefs": [{
                    "visible": false,
                    "targets": 2
                }],
                "order": [
                    [2, 'asc']
                ],
                "displayLength": 25,
                "drawCallback": function (settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;
                    api.column(2, {
                        page: 'current'
                    }).data().each(function (group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before('<tr class="group"><td colspan="5">' + group + '</td></tr>');
                            last = group;
                        }
                    });
                }
            });
            // Order by the grouping
            $('#example tbody').on('click', 'tr.group', function () {
                var currentOrder = table.order()[0];
                if (currentOrder[0] === 2 && currentOrder[1] === 'asc') {
                    table.order([2, 'desc']).draw();
                } else {
                    table.order([2, 'asc']).draw();
                }
            });
            // responsive table
            $('#config-table').DataTable({
                responsive: true
            });
            $('#example23').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary mr-1');
        });

    </script>
    <script>
        // Override enforceFocus untuk membolehkan Select2 berfungsi dalam modal
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        
        $(document).ready(function() {
            // Initialize Select2
            $(".select2").select2({
                placeholder: "Select an option",
                dropdownParent: '#addPaymentModal'
            });
            // Fetch Project No
            $("#searchProject").select2({
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
                                    text: item.project_no,
                                    client_company_name: item.client_company_name,
                                    project_type: item.project_type
                                };
                            })
                        };
                    },
                    cache: true
                }
            }).on("select2:select", function(e) {
                var data = e.params.data;
                $("#clientCompanyName").val(data.client_company_name);
                $("#projectType").val(data.project_type);
            });
            // Fetch Invoice No
            $("#searchInvoice").select2({
                ajax: {
                    url: "search_invoice.php",
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
                                    text: item.id
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
    <script>
        $("#paymentForm").on("submit", function (e) {
            e.preventDefault();
        
            var formData = {
                project_id: $("#searchProject").val(),
                invoice_no: $("#searchInvoice").val(),
                client_name: $("#clientName").val(),
                project_type: $("#projectType").val(),
                amount: $("#amount").val(),
                sst_amount: $("#sstAmount").val(),
                payment_date: $("#paymentDate").val(),
                payment_method: $("#paymentMethod").val(),
                purpose: $("#purpose").val()
            };
        
            $.ajax({
                url: "save_payment.php",
                method: "POST",
                data: formData,
                dataType: "json", // Pastikan response dalam format JSON
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success"
                        }).then(() => {
                            $("#paymentForm")[0].reset(); // Reset form
                            location.reload(); // Refresh page untuk update table
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: response.message || "Something went wrong.",
                            icon: "error"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to save payment. Please check console for details.",
                        icon: "error"
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Handle klik butang Generate Invoice
            $(".generate-invoice-btn").on("click", function() {
                var invoiceNo = $(this).data("invoice");
                var projectId = $(this).data("project");
    
                Swal.fire({
                    title: "Generate Invoice Listing?",
                    text: "Do you want to generate the invoice listing for Invoice No: " + invoiceNo + "?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, Generate",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "generate_invoice_listing.php?invoice_no=" + invoiceNo + "&project_id=" + projectId;
                    }
                });
            });
            
            // Handle klik butang Generate SST Listing
            $(".generate-sst-btn").on("click", function() {
                var invoiceNo = $(this).data("invoice");
                var projectId = $(this).data("project");
        
                Swal.fire({
                    title: "Generate SST Listing?",
                    text: "Do you want to generate the SST listing for this project?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, Generate",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "generate_sst_listing.php?project_id=" + projectId + (invoiceNo ? "&invoice_no=" + invoiceNo : "");
                    }
                });
            });
            
            // Handle klik butang Generate Receipt
            $(document).ready(function() {
                $(".generate-receipt-btn").on("click", function() {
                    var invoiceNo = $(this).data("invoice");
                    var projectId = $(this).data("project");
                    var paymentId = $(this).data("payment"); // ✅ Ambil payment_id
            
                    Swal.fire({
                        title: "Generate Receipt?",
                        text: invoiceNo 
                            ? "Do you want to generate the receipt for Invoice No: " + invoiceNo + "?"
                            : "Do you want to generate the receipt for this payment?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Generate",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (invoiceNo) {
                                window.location.href = "generate_receipt.php?invoice_no=" + invoiceNo + "&project_id=" + projectId;
                            } else {
                                window.location.href = "generate_receipt.php?payment_id=" + paymentId + "&project_id=" + projectId;
                            }
                        }
                    });
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $(".approve-btn").on("click", function() {
                var paymentId = $(this).data("id");
    
                Swal.fire({
                    title: "Approve Payment?",
                    text: "Are you sure you want to approve this payment?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, Approve",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "approve_payment.php",
                            method: "POST",
                            data: { payment_id: paymentId },
                            dataType: "json",
                            success: function(response) {
                                if (response.status === "success") {
                                    Swal.fire("Approved!", response.message, "success").then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("Error!", response.message || "Failed to approve payment.", "error");
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error:", xhr.responseText);
                                Swal.fire("Error!", "Something went wrong.", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
</html>