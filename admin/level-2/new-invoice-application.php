<?php
    session_start();
    include '../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../function/function.php';
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
                        <h4 class="text-themecolor">New Invoice Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Invoice Application</li>
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
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">List of Invoice Application</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="myTable3" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Leader</th>
                                                <th>Invoice Status</th>
                                                <th>Invoice No</th>
                                                <th>Invoice Purpose</th>
                                                <th>Total Invoice</th>
                                                <th class="text-center">Invoice Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Ambil ID pengguna semasa
                                                $user_id = $userData['id'];
                                                
                                                // Query dengan INNER JOIN untuk gabungkan data invoices & project
                                                $query = "SELECT 
                                                            p.id AS project_id, 
                                                            p.project_title, 
                                                            p.project_no, 
                                                            p.project_leader, 
                                                            i.* 
                                                          FROM invoices i
                                                          INNER JOIN project p ON i.project_id = p.id
                                                          WHERE p.project_status IN ('Approved', 'Appointed') AND i.invoice_status IN ('Pending Approval')
                                                          ORDER BY i.id DESC";
                                                          
                                                $result = mysqli_query($db, $query);
                                        
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $project_id = $row['project_id'];
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $project_leader = $row['project_leader'];
                                        
                                                    // Semua data dari invoices
                                                    $invoice_id = $row['id'];
                                                    $total_invoice = $row['total_invoice'];
                                                    $invoice_status = $row['invoice_status'];
                                                    $invoice_purpose = $row['invoice_purpose'];
                                                    $total_invoice = $row['total_invoice'];
                                                    $invoice_no = $row['invoice_no'];
                                                    $invoice_file = $row['invoice_file'];
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_leader); ?></td>
                                                <td>
                                                        <?php
                                                        $statusText = htmlspecialchars($invoice_status);
                                                        $statusClass = 'badge-secondary'; // default
                                                    
                                                        if (stripos($invoice_status, 'Rejected') !== false) {
                                                            $statusClass = 'badge-danger';
                                                        } elseif (stripos($invoice_status, 'Pending approval') !== false) {
                                                            $statusClass = 'badge-warning';
                                                        } elseif (stripos($invoice_status, 'Pending Verification') !== false) {
                                                            $statusClass = 'badge-success';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                <td><?php echo !empty($invoice_no) ? $invoice_no : 'Not Available Yet'; ?></td>
                                                <td><?php echo htmlspecialchars($invoice_purpose); ?></td>
                                                <td><?php echo htmlspecialchars($total_invoice); ?></td>
                                                <td class="text-center">
                                                    <a href="invoice-info.php?invoiceId=<?php echo urlencode($invoice_id); ?>&projectId=<?php echo urlencode($project_id); ?>" 
                                                       class="btn waves-effect waves-light btn-info" title="View Invoice">View Invoice</a>
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
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
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