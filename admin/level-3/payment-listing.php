<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa

    date_default_timezone_set('Asia/Kuala_Lumpur');

    /*
        Payment Listing purpose:
        - Record all money received
        - Payment can be linked to invoice OR project only
        - If linked to invoice, system updates invoice paid/outstanding/payment status
        - System also updates project ledger as PAYMENT RECEIVED / DR
    */

    // Ambil data ringkasan status pembayaran
    $summary_query = "
        SELECT 
            COALESCE(SUM(amount_received), 0) AS total_received,
            COUNT(CASE WHEN payment_status = 'Pending HOD Verification' THEN 1 END) AS total_pending,
            COUNT(CASE WHEN payment_status = 'Verified by HOD' THEN 1 END) AS total_verified,
            COUNT(CASE WHEN payment_status = 'Completed by Finance' THEN 1 END) AS total_completed
        FROM payment_listing
    ";
    $summary_result = mysqli_query($db, $summary_query);
    $summary = mysqli_fetch_assoc($summary_result);

    $total_received = $summary['total_received'] ?? 0;
    $total_pending = $summary['total_pending'] ?? 0;
    $total_verified = $summary['total_verified'] ?? 0;
    $total_completed = $summary['total_completed'] ?? 0;
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
                                <li class="breadcrumb-item active">Payment Listing</li>
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
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Payment Received</h5>
                                <h3>RM <?php echo number_format((float)$total_received, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Pending HOD Verification</h5>
                                <h3><?php echo number_format((int)$total_pending); ?> Transactions</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Verified by HOD</h5>
                                <h3><?php echo number_format((int)$total_verified); ?> Transactions</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Completed by Finance</h5>
                                <h3><?php echo number_format((int)$total_completed); ?> Transactions</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row m-b-20">
                    <div class="col-md-12">
                        <a href="add-payment-listing.php" class="btn btn-success">
                            + Add Payment
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Payment Listing</h3>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    This page records money received. If payment is linked to an invoice, the invoice listing and project ledger will be updated automatically.
                                </div>
                                <div class="table-responsive">
                                    <table id="payment-listing-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Invoice No</th>
                                                <th>Payment Category</th>
                                                <th>Payer Name</th>
                                                <th>Amount Received (RM)</th>
                                                <th>Payment Date</th>
                                                <th>Payment Method</th>
                                                <th>Bank Reference</th>
                                                <th>Status</th>
                                                <th>Attachment</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Query untuk tarik data payment_listing dengan join project dan invoices
                                                $query = "
                                                    SELECT 
                                                        pl.*,
                                                        pr.project_no,
                                                        pr.project_title,
                                                        pr.client_company_name,
                                                        i.invoice_no
                                                    FROM payment_listing pl
                                                    INNER JOIN project pr ON pr.id = pl.project_id
                                                    LEFT JOIN invoices i ON i.id = pl.invoice_id
                                                    ORDER BY pl.payment_date DESC, pl.id DESC
                                                ";
                                                $result = mysqli_query($db, $query);
                
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $status = $row['payment_status'];
                                                    $statusBadge = 'badge-secondary';

                                                    if (stripos($status, 'Pending') !== false) {
                                                        $statusBadge = 'badge-warning';
                                                    } elseif (stripos($status, 'Verified') !== false) {
                                                        $statusBadge = 'badge-info';
                                                    } elseif (stripos($status, 'Completed') !== false) {
                                                        $statusBadge = 'badge-success';
                                                    } elseif (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusBadge = 'badge-danger';
                                                    }

                                                    $formatted_amount = number_format((float)$row['amount_received'], 2);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['project_no']); ?></td>
                                                <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                                                <td><?php echo !empty($row['invoice_no']) ? htmlspecialchars($row['invoice_no']) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($row['payment_category']); ?></td>
                                                <td><?php echo !empty($row['payer_name']) ? htmlspecialchars($row['payer_name']) : htmlspecialchars($row['client_company_name']); ?></td>
                                                <td>RM <?php echo $formatted_amount; ?></td>
                                                <td><?php echo !empty($row['payment_date']) ? date("j F Y", strtotime($row['payment_date'])) : '-'; ?></td>
                                                <td><?php echo !empty($row['payment_method']) ? htmlspecialchars($row['payment_method']) : '-'; ?></td>
                                                <td><?php echo !empty($row['bank_reference']) ? htmlspecialchars($row['bank_reference']) : '-'; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $statusBadge; ?>">
                                                        <?php echo htmlspecialchars($status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['payment_attachment'])) { ?>
                                                        <a href="../payment-documents/<?php echo urlencode($row['payment_attachment']); ?>" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-info">
                                                            View
                                                        </a>
                                                    <?php } else { ?>
                                                        -
                                                    <?php } ?>
                                                </td>
                                                <!-- Action Button -->
                                                <td class="text-center">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-primary view-payment-btn"
                                                            data-project-no="<?php echo htmlspecialchars($row['project_no'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-project-title="<?php echo htmlspecialchars($row['project_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-invoice-no="<?php echo htmlspecialchars($row['invoice_no'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-payment-category="<?php echo htmlspecialchars($row['payment_category'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-payer-name="<?php echo htmlspecialchars($row['payer_name'] ?? $row['client_company_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-amount="<?php echo htmlspecialchars($formatted_amount, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-payment-date="<?php echo !empty($row['payment_date']) ? date("j F Y", strtotime($row['payment_date'])) : '-'; ?>"
                                                            data-payment-method="<?php echo htmlspecialchars($row['payment_method'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-bank-reference="<?php echo htmlspecialchars($row['bank_reference'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-receipt-reference="<?php echo htmlspecialchars($row['receipt_reference'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-notes="<?php echo htmlspecialchars($row['notes'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-status="<?php echo htmlspecialchars($row['payment_status'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        View
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
        $(document).on('click', '.view-payment-btn', function () {
            const projectNo = $(this).data('project-no');
            const projectTitle = $(this).data('project-title');
            const invoiceNo = $(this).data('invoice-no');
            const paymentCategory = $(this).data('payment-category');
            const payerName = $(this).data('payer-name');
            const amount = $(this).data('amount');
            const paymentDate = $(this).data('payment-date');
            const paymentMethod = $(this).data('payment-method');
            const bankReference = $(this).data('bank-reference');
            const receiptReference = $(this).data('receipt-reference');
            const notes = $(this).data('notes');
            const status = $(this).data('status');

            Swal.fire({
                title: 'Payment Details',
                width: 700,
                html:
                    '<div class="text-left">' +
                        '<p><strong>Project No:</strong> ' + projectNo + '</p>' +
                        '<p><strong>Project Title:</strong> ' + projectTitle + '</p>' +
                        '<p><strong>Invoice No:</strong> ' + invoiceNo + '</p>' +
                        '<p><strong>Payment Category:</strong> ' + paymentCategory + '</p>' +
                        '<p><strong>Payer Name:</strong> ' + payerName + '</p>' +
                        '<p><strong>Amount Received:</strong> RM ' + amount + '</p>' +
                        '<p><strong>Payment Date:</strong> ' + paymentDate + '</p>' +
                        '<p><strong>Payment Method:</strong> ' + paymentMethod + '</p>' +
                        '<p><strong>Bank Reference:</strong> ' + bankReference + '</p>' +
                        '<p><strong>Receipt Reference:</strong> ' + receiptReference + '</p>' +
                        '<p><strong>Status:</strong> ' + status + '</p>' +
                        '<p><strong>Notes:</strong><br>' + notes + '</p>' +
                    '</div>',
                icon: 'info',
                confirmButtonText: 'Close'
            });
        });
    </script>
</body>
</html>