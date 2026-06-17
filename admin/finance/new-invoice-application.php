<?php
    session_start();
    include '../../db_connect/db_connect.php';
    include 'auth_check.php';
    include '../../function/function.php';

    $userData = $_SESSION['user_data'];
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
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
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
                                                <th>Invoice Date</th>
                                                <th>Due Date</th>
                                                <th>Invoice Purpose</th>
                                                <th>Payment Status</th>
                                                <th class="text-center">Invoice Details</th>
                                                <th class="text-center">Update Invoice Documents</th>
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
                                                          WHERE p.project_status IN ('Approved', 'Appointed') AND i.invoice_status IN ('Approved')
                                                          ORDER BY i.id DESC";
                                                          
                                                $result = mysqli_query($db, $query);
                                        
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $project_id = $row['project_id'];
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $project_leader = $row['project_leader'];
                                        
                                                    // Semua data dari invoices
                                                    $invoice_id = $row['id'];
                                                    $invoice_status = $row['invoice_status'];
                                                    $invoice_purpose = $row['invoice_purpose'];
                                                    $invoice_no = $row['invoice_no'];
                                                    $invoice_file = $row['invoice_file'];
                                                    $invoice_date = $row['invoice_date'] ?? '';
                                                    $due_date = $row['due_date'] ?? '';
                                                    $payment_status = $row['payment_status'] ?? 'Unpaid';
                                                    $finance_remark = $row['finance_remark'] ?? '';
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
                                                        } elseif (stripos($invoice_status, 'Pending approval') !== false || stripos($invoice_status, 'Pending Verification') !== false) {
                                                            $statusClass = 'badge-warning';
                                                        } elseif (stripos($invoice_status, 'Approved') !== false) {
                                                            $statusClass = 'badge-success';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                </td>
                                                <td><?php echo !empty($invoice_no) ? $invoice_no : 'Not Available Yet'; ?></td>
                                                <td><?php echo !empty($invoice_date) ? date("j F Y", strtotime($invoice_date)) : 'Not Available Yet'; ?></td>
                                                <td><?php echo !empty($due_date) ? date("j F Y", strtotime($due_date)) : 'Not Available Yet'; ?></td>
                                                <td><?php echo htmlspecialchars($invoice_purpose); ?></td>
                                                <td>
                                                    <?php
                                                    $paymentStatusText = htmlspecialchars($payment_status);
                                                    $paymentStatusClass = 'badge-secondary';

                                                    if (stripos($payment_status, 'Paid') !== false && stripos($payment_status, 'Partial') === false && stripos($payment_status, 'Unpaid') === false) {
                                                        $paymentStatusClass = 'badge-success';
                                                    } elseif (stripos($payment_status, 'Partial') !== false) {
                                                        $paymentStatusClass = 'badge-info';
                                                    } elseif (stripos($payment_status, 'Unpaid') !== false) {
                                                        $paymentStatusClass = 'badge-warning';
                                                    } elseif (stripos($payment_status, 'Overpaid') !== false) {
                                                        $paymentStatusClass = 'badge-primary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $paymentStatusClass; ?>">
                                                        <?php echo $paymentStatusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="invoice-info.php?invoiceId=<?php echo urlencode($invoice_id); ?>&projectId=<?php echo urlencode($project_id); ?>" 
                                                       class="btn waves-effect waves-light btn-info" title="View Invoice">View Invoice</a>
                                                </td>
                                                <td class="text-center">
                                                    <button 
                                                      class="btn btn-primary btnUpdateInvoiceTrigger"
                                                      data-invoice-id="<?php echo $invoice_id; ?>"
                                                      data-project-id="<?php echo $project_id; ?>"
                                                      data-staff-id="<?php echo $userData['staff_id']; ?>"
                                                      data-invoice-no="<?php echo htmlspecialchars($invoice_no ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                      data-invoice-date="<?php echo htmlspecialchars($invoice_date ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                      data-due-date="<?php echo htmlspecialchars($due_date ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                      data-finance-remark="<?php echo htmlspecialchars($finance_remark ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                      data-has-file="<?php echo !empty($invoice_file) ? 'Yes' : 'No'; ?>">
                                                      Update Invoice Documents
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
                <!-- Update Invoice Documents Modal -->
                <div class="modal fade" id="updateInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="updateInvoiceModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <form id="updateInvoiceForm" enctype="multipart/form-data">
                      <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                          <h5 class="modal-title">Update Invoice Documents</h5>
                          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" id="modal_invoice_id" name="invoice_id">
                          <input type="hidden" id="modal_project_id" name="project_id">
                          <input type="hidden" id="modal_staff_id" name="staff_id">
                          <input type="hidden" id="modal_has_file" name="has_file">

                          <div class="row">
                            <div class="col-md-12">
                              <div class="alert alert-info">
                                Finance update will mark this invoice as <strong>Waiting Payment</strong>. Payment received will be recorded separately later.
                              </div>
                            </div>
                          </div>
                
                          <div class="form-group">
                            <label for="invoice_no">Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="invoice_no" name="invoice_no" required>
                          </div>

                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="invoice_date">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" required>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="due_date">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                              </div>
                            </div>
                          </div>
                
                          <div class="form-group">
                            <label for="invoice_file">Upload Invoice File</label>
                            <small class="text-muted d-block m-b-5">
                              Required if no invoice file exists. Uploading a new file will replace the previous finance invoice file.
                            </small>
                            <div class="custom-file">
                              <input type="file" class="custom-file-input" id="invoice_file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                              <label class="custom-file-label" for="invoice_file">Choose file...</label>
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="finance_remark">Finance Remark</label>
                            <textarea class="form-control" id="finance_remark" name="finance_remark" rows="4" placeholder="Enter finance notes or invoice update remark..."></textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" id="btnSubmitFinanceUpdate" class="btn btn-success">Update Documents</button>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#myTable3').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                columnDefs: [
                    { responsivePriority: 1, targets: [0, 3, 9, 10] },
                    { responsivePriority: 2, targets: [4, 8] },
                    { responsivePriority: 3, targets: [1, 5, 6] }
                ]
            });
        });
    </script>
    <script>
        $(document).on('click', '.open-update-modal', function () {
            const invoiceId = $(this).data('invoice-id');
            const projectId = $(this).data('project-id');
            const staffId = $(this).data('staff-id');
            $('#modal_invoice_id').val(invoiceId);
            $('#modal_project_id').val(projectId);
            $('#modal_staff_id').val(staffId);
        });
    </script>
    <script>
        // 1. Auto update label when file selected
        $('#invoice_file').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choose file...');
        });
        
        // 2. Open modal with pre-filled hidden input
        $(document).on('click', '.btnUpdateInvoiceTrigger', function () {
            const invoiceId = $(this).data('invoice-id');
            const projectId = $(this).data('project-id');
            const staffId = $(this).data('staff-id');
            const invoiceNo = $(this).data('invoice-no') || '';
            const invoiceDate = $(this).data('invoice-date') || '';
            const dueDate = $(this).data('due-date') || '';
            const financeRemark = $(this).data('finance-remark') || '';
            const hasFile = $(this).data('has-file') || 'No';
        
            $('#modal_invoice_id').val(invoiceId);
            $('#modal_project_id').val(projectId);
            $('#modal_staff_id').val(staffId);
            $('#modal_has_file').val(hasFile);

            $('#invoice_no').val(invoiceNo);
            $('#invoice_date').val(invoiceDate);
            $('#due_date').val(dueDate);
            $('#finance_remark').val(financeRemark);

            $('#invoice_file').val('');
            $('.custom-file-label').text('Choose file...');
            $('#updateInvoiceModal').modal('show');
        });
        
        // 3. Submit form with validation and AJAX
        $('#updateInvoiceForm').submit(function (e) {
            e.preventDefault();
        
            const invoiceNo = $('#invoice_no').val().trim();
            const invoiceDate = $('#invoice_date').val();
            const dueDate = $('#due_date').val();
            const invoiceFile = $('#invoice_file').prop('files')[0];
            const hasFile = $('#modal_has_file').val();
        
            if (!invoiceNo || !invoiceDate || !dueDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'Please enter invoice number, invoice date, and due date.',
                });
                return;
            }

            if (new Date(dueDate) < new Date(invoiceDate)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Due Date',
                    text: 'Due date cannot be earlier than invoice date.',
                });
                return;
            }

            if (hasFile !== 'Yes' && !invoiceFile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invoice File Required',
                    text: 'Please upload the invoice file before updating.',
                });
                return;
            }
        
            const formData = new FormData(this);
        
            Swal.fire({
                title: 'Confirm Update',
                text: 'This will update the invoice documents and mark it as Waiting Payment.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#btnSubmitFinanceUpdate').prop('disabled', true).html('Updating...');

                    Swal.fire({
                        title: 'Updating Invoice...',
                        text: 'Please wait while finance invoice documents are being updated.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'update_invoice_details.php',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({
                                    title: 'Success',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                $('#btnSubmitFinanceUpdate').prop('disabled', false).html('Update Documents');
                                Swal.fire('Error', res.message || 'Update failed.', 'error');
                            }
                        },
                        error: function (xhr) {
                            $('#btnSubmitFinanceUpdate').prop('disabled', false).html('Update Documents');
                            Swal.fire('Error', 'Unexpected server error: ' + (xhr.responseText || 'Unknown error.'), 'error');
                        }
                    });
                }
            });
        });

        $('#updateInvoiceModal').on('hidden.bs.modal', function () {
            $('#btnSubmitFinanceUpdate').prop('disabled', false).html('Update Documents');
            $('#updateInvoiceForm')[0].reset();
            $('.custom-file-label').text('Choose file...');
        });
    </script>
</body>

</html>
