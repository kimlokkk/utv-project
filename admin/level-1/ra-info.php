<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id = $_GET['id']; // Get the ID from the URL parameter

    // Query to fetch data from the research_assistant table
    $query = "SELECT * FROM research_assistant WHERE id = '$id'";
    $result = mysqli_query($db, $query);

    // Fetch data into variables
    while ($row = mysqli_fetch_array($result)) {
        $full_name = $row['full_name'];
        $designation = $row['designation'];
        $ic = $row['ic'];
        $phone = $row['phone'];
        $email = $row['email'];
        $email_2 = $row['email_2'];
        $ptj_address = $row['ptj_address'];
        $gender = $row['gender'];
        $citizenship = $row['citizenship'];
        $marital_status = $row['marital_status'];
        $epf_no = $row['epf_no'];
        $socso_no = $row['socso_no'];
        $income_tax_no = $row['income_tax_no'];
        $employment_position = $row['employment_position'];
        $expertise = $row['expertise'];
        $bank_name = $row['bank_name'];
        $no_account = $row['no_account'];
        $bank_statement_file = $row['bank_statement_file'];
        $copy_ic_file = $row['copy_ic_file'];
        $copy_certificate_file = $row['copy_certificate_file'];
        $password = $row['password'];
        $date_register = $row['date_register'];
        $status = $row['status'];
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
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/node_modules/dropify/dist/css/dropify.min.css">
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
                        <h4 class="text-themecolor">RA/RA Full Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">RA/RO Registration</a></li>
                                <li class="breadcrumb-item active">RA/RA Full Info</li>
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
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="card">
                                <h3 class="card-header bg-success text-white"><?php echo $full_name; ?></h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Position</h4>
                                            <p class="font-size-h5"><?php echo $designation; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">IC Number</h4>
                                            <p class="font-size-h5"><?php echo $ic; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Contact Number</h4>
                                            <p class="font-size-h5"><?php echo $phone; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">PTJ Address</h4>
                                            <p class="font-size-h5"><?php echo $ptj_address; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Email Address</h4>
                                            <p class="font-size-h5"><?php echo $email; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">Email Address 2</h4>
                                            <p class="font-size-h5"><?php echo !empty($email_2) ? htmlspecialchars($email_2) : "No data available"; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Gender</h4>
                                            <p class="font-size-h5"><?php echo $gender; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">Citizenship</h4>
                                            <p class="font-size-h5"><?php echo $citizenship; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Marital Status</h4>
                                            <p class="font-size-h5"><?php echo $marital_status; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">EPF No</h4>
                                            <p class="font-size-h5"><?php echo !empty($epf_no) ? htmlspecialchars($epf_no) : "No data available"; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">SOCSO No</h4>
                                            <p class="font-size-h5"><?php echo !empty($socso_no) ? htmlspecialchars($socso_no) : "No data available"; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">Income Tax No</h4>
                                            <p class="font-size-h5"><?php echo !empty($income_tax_no) ? htmlspecialchars($income_tax_no) : "No data available"; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Employment Position</h4>
                                            <p class="font-size-h5"><?php echo $employment_position; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">Expertise</h4>
                                            <p class="font-size-h5"><?php echo $expertise; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-success text-white">File Information</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Copy of IC</h4>
                                            <a href="../registration-documents/ic-folder/<?php echo $copy_ic_file ?>" class="text-info font-weight-bolder d-block font-size-h5">Click to view</a>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">Copy of Certificate</h4>
                                            <a href="../registration-documents/certificate-folder/<?php echo $copy_certificate_file ?>" class="text-info font-weight-bolder d-block font-size-h5">Click to view</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-success text-white">Bank Information</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Bank Name</h4>
                                            <p class="font-size-h5"><?php echo $bank_name; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="card-title">Account No</h4>
                                            <p class="font-size-h5"><?php echo $no_account; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="card-title">Bank Statement File</h4>
                                            <a href="../registration-documents/bank-statement/<?php echo $bank_statement_file ?>" class="text-info font-weight-bolder d-block font-size-h5">Click to view</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-success text-white">Status Tracker</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table id="myTable" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Query to fetch project members
                                                    $query = "
                                                        SELECT *
                                                        FROM research_assistant_registration_remark
                                                        WHERE research_id = '$id'
                                                    ";
                                                    
                                                    $result = mysqli_query($db, $query);
                                                    
                                                    // Check for errors in the query execution
                                                    if (!$result) {
                                                        // Log or display the MySQL error
                                                        error_log("MySQL Query Error: " . mysqli_error($db));
                                                        echo "<p>Error fetching project members: " . mysqli_error($db) . "</p>";
                                                        exit;
                                                    }
                                                    
                                                    // Process the results if the query succeeded
                                                    $counter = 1;
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        $remarks = $row['remarks'];
                                                    ?>
                                                        <td><?php echo htmlspecialchars($counter); ?></td>
                                                        <td><?php echo htmlspecialchars($remarks); ?></td>
                                                    </tr>
                                                    <?php 
                                                        $counter++;
                                                    } 
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="verify" id="btnVerify" class="btn btn-lg btn-info"> Verify</button>&nbsp;&nbsp;
                                    <button type="approve" id="btnApprove" class="btn btn-lg btn-success"> Approve</button>&nbsp;&nbsp;
                                    <button type="button" class="btn btn-lg btn-danger" id="btnReject">Reject</button>
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
    <!-- Sweet-Alert  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#myTable').DataTable();
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
        $(function () {
            // Switchery
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            $('.js-switch').each(function () {
                new Switchery($(this)[0], $(this).data());
            });
            // For select 2
            $(".select2").select2();
            $('.selectpicker').selectpicker();
            //Bootstrap-TouchSpin
            $(".vertical-spin").TouchSpin({
                verticalbuttons: true
            });
            var vspinTrue = $(".vertical-spin").TouchSpin({
                verticalbuttons: true
            });
            if (vspinTrue) {
                $('.vertical-spin').prev('.bootstrap-touchspin-prefix').remove();
            }
            $("input[name='tch1']").TouchSpin({
                min: 0,
                max: 100,
                step: 0.1,
                decimals: 2,
                boostat: 5,
                maxboostedstep: 10,
                postfix: '%'
            });
            $("input[name='tch2']").TouchSpin({
                min: -1000000000,
                max: 1000000000,
                stepinterval: 50,
                maxboostedstep: 10000000,
                prefix: '$'
            });
            $("input[name='tch3']").TouchSpin();
            $("input[name='tch3_22']").TouchSpin({
                initval: 40
            });
            $("input[name='tch5']").TouchSpin({
                prefix: "pre",
                postfix: "post"
            });
            // For multiselect
            $('#pre-selected-options').multiSelect();
            $('#optgroup').multiSelect({
                selectableOptgroup: true
            });
            $('#public-methods').multiSelect();
            $('#select-all').click(function () {
                $('#public-methods').multiSelect('select_all');
                return false;
            });
            $('#deselect-all').click(function () {
                $('#public-methods').multiSelect('deselect_all');
                return false;
            });
            $('#refresh').on('click', function () {
                $('#public-methods').multiSelect('refresh');
                return false;
            });
            $('#add-option').on('click', function () {
                $('#public-methods').multiSelect('addOption', {
                    value: 42,
                    text: 'test 42',
                    index: 0
                });
                return false;
            });
            $(".ajax").select2({
                ajax: {
                    url: "https://api.github.com/search/repositories",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                }, // let our custom formatter work
                minimumInputLength: 1,
                //templateResult: formatRepo, // omitted for brevity, see the source of this page
                //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
            });
        });
    </script>
    <script>
    document.getElementById('btnReject').addEventListener('click', function () {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to reject this Research Assistant?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ask for the reason for rejection
                Swal.fire({
                    title: 'Remarks',
                    input: 'textarea',
                    inputPlaceholder: 'Enter your remarks here...',
                    inputAttributes: {
                        'aria-label': 'Enter your remarks here'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Cancel',
                    preConfirm: (remark) => {
                        if (!remark) {
                            Swal.showValidationMessage('You need to provide a remark to proceed!');
                        }
                        return remark;
                    }
                }).then((remarkResult) => {
                    if (remarkResult.isConfirmed) {
                        const remark = remarkResult.value;
    
                        // Show loading spinner
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Please wait a moment.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
    
                        // Submit the form with the rejection reason
                        const form = document.querySelector('form'); // Locate the form
                        if (form) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'btn_rejectResearchAssistant';
                            hiddenInput.value = 'true';
                            form.appendChild(hiddenInput);
    
                            const remarkInput = document.createElement('input');
                            remarkInput.type = 'hidden';
                            remarkInput.name = 'rejection_remark';
                            remarkInput.value = remark;
                            form.appendChild(remarkInput);
    
                            form.submit(); // Submit the form
                        } else {
                            Swal.fire('Error', 'Form not found!', 'error');
                        }
                    }
                });
            }
        });
    });
    </script>
    <script>
    document.getElementById('btnVerify').addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to verify this Research Assistant?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, verify it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading spinner
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait a moment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
    
                // Submit the form with the verification flag
                const form = document.querySelector('form');
                if (form) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'btn_verifyResearchAssistant';
                    hiddenInput.value = 'true';
                    form.appendChild(hiddenInput);
                    form.submit();
                } else {
                    Swal.fire('Error', 'Form not found!', 'error');
                }
            }
        });
    });
    </script>
    <script>
    document.getElementById('btnApprove').addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to approve this Research Assistant?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading spinner
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait a moment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
    
                // Submit the form with the approval flag
                const form = document.querySelector('form');
                if (form) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'btn_approveResearchAssistant';
                    hiddenInput.value = 'true';
                    form.appendChild(hiddenInput);
                    form.submit();
                } else {
                    Swal.fire('Error', 'Form not found!', 'error');
                }
            }
        });
    });
    </script>
</body>

</html>