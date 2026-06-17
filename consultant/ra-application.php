<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
    $user_id = $userData['id'];
?>
<?php
    $ra_data = [];
    $ra_query = "SELECT id, full_name, ic, ptj_address, expertise FROM research_assistant WHERE status = 'Approved'";
    $ra_result = mysqli_query($db, $ra_query);
    
    if ($ra_result) {
        while ($row = mysqli_fetch_assoc($ra_result)) {
            $ra_data[] = $row;
        }
    }
?>
<?php
    $project_data = [];
    $project_query = "SELECT id, project_title 
                        FROM project 
                        WHERE leader_id = '$user_id'
                          AND project_status IN ('Approved', 'Appointed')
                        ";
    $project_result = mysqli_query($db, $project_query);
    
    if ($project_result) {
        while ($row = mysqli_fetch_assoc($project_result)) {
            $project_data[] = $row;
        }
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
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" />
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .ra-form-card {
            border: 1px solid #e9edf2;
            border-radius: 6px;
            box-shadow: 0 4px 14px rgba(47, 61, 74, 0.06);
        }
        .ra-form-title {
            margin-bottom: 4px;
            font-weight: 700;
            color: #2f3d4a;
        }
        .ra-form-subtitle {
            color: #6c757d;
            margin-bottom: 18px;
        }
        .ra-row {
            position: relative;
            border: 1px solid #e5e9ef;
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 18px;
            background: #fff;
        }
        .ra-row-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid #edf1f5;
        }
        .ra-row-title {
            font-weight: 700;
            color: #2f3d4a;
        }
        .ra-readonly {
            background: #f8fafc;
        }
        .duration-preview {
            font-weight: 700;
            color: #00695c;
        }
    </style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="skin-blue fixed-layout">
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
                        <h4 class="text-themecolor">RA/RO Listing</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">RA/RO Application</li>
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
                <!-- RA/RO Form Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card ra-form-card">
                            <div class="card-body">
                                <h4 class="card-title ra-form-title">RA/RO Application</h4>
                                <p class="ra-form-subtitle">Select the project, RA/RO, appointment period and monthly allowance/wage. The payable duration is calculated automatically from the start and end dates.</p>
                                <form id="raForm">
                                    <div id="raContainer"></div>
                            
                                    <div class="form-group text-right mt-3">
                                        <button type="button" class="btn btn-info" id="addRowBtn">+ Add Another RA/RO</button>
                                    </div>
                            
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-success" id="submitBtn">Submit</button>
                                    </div>
                                </form>
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
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
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
    <!-- Inject RA Data -->
    <script>
        const raList = <?php echo json_encode($ra_data); ?>;
        const projectList = <?php echo json_encode($project_data); ?>;
    </script>
    <script>
        let rowCount = 0;

        function calculatePayableMonths(startDate, endDate) {
            if (!startDate || !endDate) {
                return 0;
            }

            const start = new Date(startDate + 'T00:00:00');
            const end = new Date(endDate + 'T00:00:00');

            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
                return 0;
            }

            let months = ((end.getFullYear() - start.getFullYear()) * 12) + (end.getMonth() - start.getMonth());

            if (end.getDate() > start.getDate()) {
                months++;
            }

            if (
                months === 0 &&
                start.getFullYear() === end.getFullYear() &&
                start.getMonth() === end.getMonth()
            ) {
                months = 1;
            }

            return months;
        }

        function updateRowDuration(row) {
            const startDate = row.find('.start-date').val();
            const endDate = row.find('.end-date').val();
            const monthlyBudget = parseFloat(row.find('.monthly-budget').val()) || 0;
            const months = calculatePayableMonths(startDate, endDate);
            const total = months * monthlyBudget;

            row.find('.duration-value').val(months > 0 ? months : '');
            row.find('.duration-preview').val(months + ' month(s)');
            row.find('.total-preview').val(total.toFixed(2));
        }
        
        function createRaRow() {
            rowCount++;
        
            const raSelectOptions = raList.map(ra => `
                <option value="${ra.id}"
                    data-name="${ra.full_name}"
                    data-ic="${ra.ic}"
                    data-ptj="${ra.ptj_address}"
                    data-expertise="${ra.expertise}">
                    ${ra.full_name}
                </option>
            `).join('');
            
            const projectSelectOptions = projectList.map(p => `
                <option value="${p.id}">${p.project_title}</option>
            `).join('');
        
            const html = `
            <div class="ra-row">
                <div class="ra-row-header">
                    <div class="ra-row-title">RA/RO Appointment ${rowCount}</div>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn">
                        Remove
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label><strong>Project</strong></label>
                        <select name="project_id[]" class="form-control project-select" required>
                            <option value="" disabled selected>Select Project</option>
                            ${projectSelectOptions}
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label><strong>RA Name</strong></label>
                        <select name="ra_id[]" class="form-control ra-select" required>
                            <option value="" disabled selected>Select Research Assistant</option>
                            ${raSelectOptions}
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Name</label>
                        <input type="text" class="form-control ra-readonly" name="name[]" readonly required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>IC/Passport No</label>
                        <input type="text" class="form-control ra-readonly" name="ic_passport[]" readonly required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Expertise</label>
                        <input type="text" class="form-control ra-readonly" name="expertise[]" readonly required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Faculty / PTJ</label>
                        <input type="text" class="form-control ra-readonly" name="ptj_address[]" readonly required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Start Date</label>
                        <input type="date" class="form-control start-date" name="start_date[]" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>End Date</label>
                        <input type="date" class="form-control end-date" name="end_date[]" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Payable Duration</label>
                        <input type="hidden" class="duration-value" name="duration[]" value="">
                        <input type="text" class="form-control ra-readonly duration-preview" value="0 month(s)" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Payment Type</label>
                        <select class="form-control" name="payment_type[]" required>
                            <option value="">Select</option>
                            <option value="Token">Token & Allowance</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Monthly Allowance/Wage (RM)</label>
                        <input type="number" class="form-control monthly-budget" name="budget[]" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estimated Total (RM)</label>
                        <input type="text" class="form-control ra-readonly total-preview" value="0.00" readonly>
                    </div>
                </div>
            </div>
            `;
        
            const newRow = $(html);
            $('#raContainer').append(newRow);
            newRow.find('.project-select').select2();
            newRow.find('.ra-select').select2();
        }
        
        $(document).ready(function () {
            createRaRow(); // Add first row
        
            $('#addRowBtn').click(function () {
                if (rowCount < 10) {
                    createRaRow();
                }
            });
        
            $(document).on('change', '.ra-select', function () {
                const selected = $(this).find(':selected');
                const row = $(this).closest('.ra-row');
        
                row.find('input[name="name[]"]').val(selected.data('name'));
                row.find('input[name="ic_passport[]"]').val(selected.data('ic'));
                row.find('input[name="ptj_address[]"]').val(selected.data('ptj'));
                row.find('input[name="expertise[]"]').val(selected.data('expertise'));
            });

            $(document).on('change input', '.start-date, .end-date, .monthly-budget', function () {
                updateRowDuration($(this).closest('.ra-row'));
            });
            
            // ❌ Delete row handler
            $(document).on('click', '.remove-row-btn', function () {
                if ($('.ra-row').length === 1) {
                    Swal.fire('Cannot Remove', 'At least one RA/RO appointment is required.', 'warning');
                    return;
                }

                $(this).closest('.ra-row').remove();
                rowCount--;
            });
        });
    </script>
    <script>
        $('#submitBtn').click(function () {
            Swal.fire({
                title: 'Confirm Submission?',
                text: "Make sure everything is correct.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let isValid = true;
                    let validationMessage = 'Please complete all required fields.';

                    $('.ra-row').each(function () {
                        const row = $(this);
                        updateRowDuration(row);

                        row.find('[required]').each(function () {
                            if (!$(this).val()) {
                                isValid = false;
                            }
                        });

                        const startDate = row.find('.start-date').val();
                        const endDate = row.find('.end-date').val();
                        const months = parseInt(row.find('.duration-value').val(), 10) || 0;

                        if (startDate && endDate && months <= 0) {
                            isValid = false;
                            validationMessage = 'End Date must be on or after Start Date.';
                        }
                    });

                    if (!isValid) {
                        Swal.fire('Incomplete Form', validationMessage, 'warning');
                        return;
                    }

                    const formData = $('#raForm').serialize();
                    $.ajax({
                        url: 'submit_ra_application.php',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            let res = JSON.parse(response);
                            if (res.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = 'ra-listing.php';
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Submission failed due to server error.', 'error');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
