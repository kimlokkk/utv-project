<?php
$password = "Admin@1234"; // Replace with your actual password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Use bcrypt to hash the password
echo $hashedPassword;
?>
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
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
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
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Add New Payment</h4>
                                <form id="paymentForm">
                                    <div class="form-group">
    <label for="searchProject" class="d-block">Search Project No</label>
    <select class="form-control select2" id="searchProject" style="width: 100%;">
        <option value="">Select Project No</option>
    </select>
</div>

                                    <div class="form-group">
                                        <label for="clientName">Client Name</label>
                                        <input type="text" class="form-control" id="clientCompanyName" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="projectType">Project Type</label>
                                        <input type="text" class="form-control" id="projectType" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="searchInvoice">Search Invoice No</label>
                                        <select class="form-control select2" id="searchInvoice">
                                            <option value="">Select Invoice No</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount">Amount (RM)</label>
                                        <input type="number" class="form-control" id="amount" placeholder="Enter Amount">
                                    </div>
                                    <div class="form-group">
                                        <label for="sstPercentage">SST Percentage (%)</label>
                                        <input type="number" class="form-control" id="sstPercentage" value="6" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="sstAmount">SST Amount (RM)</label>
                                        <input type="number" class="form-control" id="sstAmount" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="paymentDate">Payment Date</label>
                                        <input type="date" class="form-control" id="paymentDate">
                                    </div>
                                    <div class="form-group">
                                        <label for="paymentMethod">Payment Method</label>
                                        <select class="form-control" id="paymentMethod">
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="purpose">Purpose of Payment</label>
                                        <input type="text" class="form-control" id="purpose" placeholder="Enter Purpose">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Payment Listing</h4>
                                <div class="table-responsive">
                                    <table id="paymentTable" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Invoice No</th>
                                                <th>Amount (RM)</th>
                                                <th>SST Amount (RM)</th>
                                                <th>Payment Date</th>
                                                <th>Payment Method</th>
                                                <th>Purpose</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT p.*, pr.project_no, i.id
                                                    FROM payments p
                                                    LEFT JOIN project pr ON p.project_id = pr.id
                                                    LEFT JOIN invoices i ON p.project_id = i.project_id";
                                            $result = $db->query($sql);
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>
                                                        <td>{$row['project_no']}</td>
                                                        <td>{$row['invoice_no']}</td>
                                                        <td>{$row['amount']}</td>
                                                        <td>{$row['sst_amount']}</td>
                                                        <td>{$row['payment_date']}</td>
                                                        <td>{$row['payment_method']}</td>
                                                        <td>{$row['purpose']}</td>
                                                      </tr>";
                                            }
                                            ?>
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
            $('#paymentTable').DataTable();
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
        $(document).ready(function() {
            // Initialize Select2
            $(".select2").select2({
                placeholder: "Select an option",
                allowClear: true
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
        $("#amount").on("input", function() {
            var amount = parseFloat($(this).val());
            var sstPercentage = parseFloat($("#sstPercentage").val());
            var sstAmount = (amount * sstPercentage) / 100;
            $("#sstAmount").val(sstAmount.toFixed(2));
        });
    </script>
    <script>
        $("#paymentForm").on("submit", function(e) {
            e.preventDefault();
            var formData = {
                project_id: $("#searchProject").val(),
                invoice_id: $("#searchInvoice").val(),
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
                success: function(response) {
                    Swal.fire("Success!", "Payment saved successfully.", "success");
                    $("#paymentForm")[0].reset();
                    loadPaymentListing(); // Reload payment listing
                },
                error: function() {
                    Swal.fire("Error!", "Failed to save payment.", "error");
                }
            });
        });
    </script>
    <script>
        function loadPaymentListing() {
            $.ajax({
                url: "load_payment_listing.php",
                method: "GET",
                success: function(data) {
                    $("#paymentTable tbody").html(data);
                }
            });
        }
    </script>
</html>