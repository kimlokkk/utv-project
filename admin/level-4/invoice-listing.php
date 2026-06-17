<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa

    date_default_timezone_set('Asia/Kuala_Lumpur');

    /*
        Invoice Listing purpose:
        - Monitor all issued/approved invoices
        - Track invoice date, due date, aging, outstanding amount, and warning
        - Data source: invoices + project
        - No separate invoice_listing table needed
    */

    // Summary query untuk Invoice Listing
    $summary_query = "
        SELECT 
            COUNT(i.id) AS total_invoices,
            COALESCE(SUM(i.total_invoice), 0) AS total_invoice_amount,
            COALESCE(SUM(
                CASE 
                    WHEN i.payment_status = 'Paid' THEN 0
                    ELSE GREATEST(i.total_invoice - COALESCE(i.paid_amount, 0), 0)
                END
            ), 0) AS total_outstanding,
            SUM(
                CASE 
                    WHEN i.due_date IS NOT NULL
                    AND i.due_date < CURDATE()
                    AND i.payment_status <> 'Paid'
                    AND GREATEST(i.total_invoice - COALESCE(i.paid_amount, 0), 0) > 0
                    THEN 1 ELSE 0
                END
            ) AS overdue_count
        FROM invoices i
        INNER JOIN project p ON i.project_id = p.id
        WHERE p.project_status IN ('Approved', 'Appointed')
          AND (
                i.invoice_status LIKE '%Approved%'
                OR i.invoice_status LIKE '%Waiting Payment%'
                OR i.invoice_status LIKE '%Fully Paid%'
              )
          AND i.invoice_status NOT LIKE '%Pending%'
          AND i.invoice_status NOT LIKE '%Verification%'
          AND i.invoice_status NOT LIKE '%project leader%'
          AND i.invoice_status NOT LIKE '%Rejected%'
          AND i.invoice_status NOT LIKE '%Returned%'
    ";

    $summary_result = mysqli_query($db, $summary_query);
    $summary = mysqli_fetch_assoc($summary_result);

    $total_invoices = $summary['total_invoices'] ?? 0;
    $total_invoice_amount = $summary['total_invoice_amount'] ?? 0;
    $total_outstanding = $summary['total_outstanding'] ?? 0;
    $overdue_count = $summary['overdue_count'] ?? 0;
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
    <style>
        .badge-waiting-payment {
            background-color: #6f42c1;
            color: #fff;
        }
    </style>
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
                        <h4 class="text-themecolor">Invoice Listing</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Invoice Monitoring</a></li>
                                <li class="breadcrumb-item active">Invoice Listing</li>
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
                                <h5>Total Invoices</h5>
                                <h3><?php echo number_format((int)$total_invoices); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Total Invoice Amount</h5>
                                <h3>RM <?php echo number_format((float)$total_invoice_amount, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Total Outstanding</h5>
                                <h3>RM <?php echo number_format((float)$total_outstanding, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5>Overdue Invoices</h5>
                                <h3><?php echo number_format((int)$overdue_count); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Invoice Listing</h3>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    This page monitors issued invoices, outstanding amount, aging days and overdue warning. Payment details will be updated separately through Payment Listing.
                                </div>
                                <div class="table-responsive">
                                    <table id="mytable3" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Client</th>
                                                <th>Project Leader</th>
                                                <th>Invoice No</th>
                                                <th>Invoice Date</th>
                                                <th>Due Date</th>
                                                <th>Total Invoice (RM)</th>
                                                <th>Paid Amount (RM)</th>
                                                <th>Outstanding (RM)</th>
                                                <th>Invoice Status</th>
                                                <th>Payment Status</th>
                                                <th>Aging Days</th>
                                                <th>Overdue Days</th>
                                                <th>Warning</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                /*
                                                    Query untuk Invoice Listing:
                                                    - Data invoice datang daripada invoices
                                                    - Data project/client datang daripada project
                                                    - outstanding_display dikira daripada total_invoice - paid_amount
                                                */
                                                $query = "
                                                    SELECT 
                                                        p.id AS project_id,
                                                        p.project_no,
                                                        p.project_title,
                                                        p.project_leader,
                                                        p.client_company_name,
                                                        p.client_pic,
                                                        p.client_pic_email,
                                                        i.id AS invoice_id,
                                                        i.invoice_no,
                                                        i.invoice_purpose,
                                                        i.total_invoice,
                                                        i.paid_amount,
                                                        i.outstanding_amount,
                                                        i.payment_status,
                                                        i.invoice_status,
                                                        i.invoice_date,
                                                        i.due_date,
                                                        i.sent_to_client_at,
                                                        i.last_payment_date,
                                                        CASE 
                                                            WHEN i.payment_status = 'Paid' THEN 0
                                                            ELSE GREATEST(i.total_invoice - COALESCE(i.paid_amount, 0), 0)
                                                        END AS outstanding_display,
                                                        CASE 
                                                            WHEN i.invoice_date IS NULL THEN NULL
                                                            ELSE DATEDIFF(CURDATE(), i.invoice_date)
                                                        END AS aging_days,
                                                        CASE 
                                                            WHEN i.due_date IS NULL THEN NULL
                                                            ELSE DATEDIFF(CURDATE(), i.due_date)
                                                        END AS overdue_days
                                                    FROM invoices i
                                                    INNER JOIN project p ON i.project_id = p.id
                                                    WHERE p.project_status IN ('Approved', 'Appointed')
                                                      AND (
                                                            i.invoice_status LIKE '%Approved%'
                                                            OR i.invoice_status LIKE '%Waiting Payment%'
                                                            OR i.invoice_status LIKE '%Fully Paid%'
                                                          )
                                                      AND i.invoice_status NOT LIKE '%Pending%'
                                                      AND i.invoice_status NOT LIKE '%Verification%'
                                                      AND i.invoice_status NOT LIKE '%project leader%'
                                                      AND i.invoice_status NOT LIKE '%Rejected%'
                                                      AND i.invoice_status NOT LIKE '%Returned%'
                                                    ORDER BY 
                                                        CASE WHEN i.due_date IS NULL THEN 1 ELSE 0 END,
                                                        i.due_date ASC,
                                                        i.id DESC
                                                ";

                                                $result = mysqli_query($db, $query);

                                                while ($row = mysqli_fetch_array($result)) {
                                                    $project_id = $row['project_id'];
                                                    $invoice_id = $row['invoice_id'];

                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $project_leader = $row['project_leader'];
                                                    $client_company_name = $row['client_company_name'];

                                                    $invoice_no = $row['invoice_no'];
                                                    $invoice_status = $row['invoice_status'];
                                                    $payment_status = $row['payment_status'] ?? 'Unpaid';

                                                    $total_invoice = (float)$row['total_invoice'];
                                                    $paid_amount = (float)$row['paid_amount'];
                                                    $outstanding_display = (float)$row['outstanding_display'];

                                                    $invoice_date = $row['invoice_date'];
                                                    $due_date = $row['due_date'];
                                                    $aging_days = $row['aging_days'];
                                                    $overdue_days = $row['overdue_days'];

                                                    // Invoice status badge
                                                    $invoiceStatusClass = 'badge-secondary';

                                                    if (stripos($invoice_status, 'Rejected') !== false || stripos($invoice_status, 'Returned') !== false) {
                                                        $invoiceStatusClass = 'badge-danger';
                                                    } elseif (stripos($invoice_status, 'Pending') !== false) {
                                                        $invoiceStatusClass = 'badge-warning';
                                                    } elseif (stripos($invoice_status, 'Waiting Payment') !== false) {
                                                        $invoiceStatusClass = 'badge-waiting-payment';
                                                    } elseif (stripos($invoice_status, 'Approved') !== false || stripos($invoice_status, 'Fully Paid') !== false || stripos($invoice_status, 'Invoice Sent') !== false) {
                                                        $invoiceStatusClass = 'badge-success';
                                                    }

                                                    // Payment status badge
                                                    $paymentStatusClass = 'badge-secondary';

                                                    if ($payment_status === 'Paid') {
                                                        $paymentStatusClass = 'badge-success';
                                                    } elseif ($payment_status === 'Partial') {
                                                        $paymentStatusClass = 'badge-info';
                                                    } elseif ($payment_status === 'Unpaid') {
                                                        $paymentStatusClass = 'badge-waiting-payment';
                                                    } elseif ($payment_status === 'Overpaid') {
                                                        $paymentStatusClass = 'badge-primary';
                                                    }

                                                    // Warning logic
                                                    $warningText = 'Not Available';
                                                    $warningClass = 'badge-secondary';

                                                    if ($payment_status === 'Paid' || $outstanding_display <= 0) {
                                                        $warningText = 'Paid';
                                                        $warningClass = 'badge-success';
                                                    } elseif (empty($due_date)) {
                                                        $warningText = 'No Due Date';
                                                        $warningClass = 'badge-secondary';
                                                    } else {
                                                        $days_to_due = (int)((strtotime($due_date) - strtotime(date('Y-m-d'))) / 86400);

                                                        if ($days_to_due < 0) {
                                                            $overdue_abs = abs($days_to_due);

                                                            if ($overdue_abs >= 90) {
                                                                $warningText = 'Critical Overdue';
                                                                $warningClass = 'badge-danger';
                                                            } elseif ($overdue_abs >= 60) {
                                                                $warningText = 'High Overdue';
                                                                $warningClass = 'badge-danger';
                                                            } else {
                                                                $warningText = 'Overdue';
                                                                $warningClass = 'badge-danger';
                                                            }
                                                        } elseif ($days_to_due <= 7) {
                                                            $warningText = 'Due Soon';
                                                            $warningClass = 'badge-warning';
                                                        } else {
                                                            $warningText = 'Not Due';
                                                            $warningClass = 'badge-success';
                                                        }
                                                    }
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo !empty($client_company_name) ? htmlspecialchars($client_company_name) : 'Not Available'; ?></td>
                                                <td><?php echo htmlspecialchars($project_leader); ?></td>
                                                <td><?php echo !empty($invoice_no) ? htmlspecialchars($invoice_no) : 'Not Available Yet'; ?></td>
                                                <td><?php echo !empty($invoice_date) ? date("j F Y", strtotime($invoice_date)) : 'Not Available'; ?></td>
                                                <td><?php echo !empty($due_date) ? date("j F Y", strtotime($due_date)) : 'Not Available'; ?></td>
                                                <td>RM <?php echo number_format($total_invoice, 2); ?></td>
                                                <td>RM <?php echo number_format($paid_amount, 2); ?></td>
                                                <td>RM <?php echo number_format($outstanding_display, 2); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $invoiceStatusClass; ?>">
                                                        <?php echo htmlspecialchars($invoice_status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $paymentStatusClass; ?>">
                                                        <?php echo htmlspecialchars($payment_status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo ($aging_days !== null) ? htmlspecialchars($aging_days) . ' days' : '-'; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        if ($overdue_days === null) {
                                                            echo '-';
                                                        } elseif ((int)$overdue_days > 0) {
                                                            echo htmlspecialchars($overdue_days) . ' days';
                                                        } else {
                                                            echo '0 days';
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $warningClass; ?>">
                                                        <?php echo $warningText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="invoice-info.php?invoiceId=<?php echo urlencode($invoice_id); ?>&projectId=<?php echo urlencode($project_id); ?>" 
                                                       class="btn btn-sm waves-effect waves-light btn-info" 
                                                       title="View Invoice">
                                                        View Invoice
                                                    </a>
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
            $('#myTable3').DataTable();
    
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
</body>

</html>
