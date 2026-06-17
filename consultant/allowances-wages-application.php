<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id =   $_GET['projectId'];
    $query  = "SELECT 
                project.*,
                uitm_staff.ic,
                uitm_staff.staff_id,
                uitm_staff.bank_name,
                uitm_staff.no_account
            FROM 
                project
            INNER JOIN 
                uitm_staff 
            ON 
                project.leader_id = uitm_staff.id
            WHERE 
                project.id = '$id';";  
    $result =   mysqli_query($db, $query);
    while($row =   mysqli_fetch_array($result))  
    {
        $project_leader                 = $row['project_leader'];
        $project_no                     = $row['project_no'];
        $project_title                  = $row['project_title'];
        $applicant_id                   = $row['leader_id'];
        $applicant_ic                   = $row['ic'];
        $applicant_staff_id            = $row['staff_id'];
        $bank_name                      = $row['bank_name'];
        $no_account                     = $row['no_account'];
    }
    
    // Fetch approved RA appointments related to this project.
    $ra_query = "
        SELECT
            raa.id AS ra_application_id,
            raa.ra_id,
            raa.name AS application_name,
            raa.start_date,
            raa.end_date,
            raa.duration,
            raa.budget,
            ra.full_name,
            ra.email,
            ra.ic,
            ra.bank_name,
            ra.no_account
        FROM research_assistant_application raa
        INNER JOIN research_assistant ra ON ra.id = raa.ra_id
        WHERE raa.project_id = '$id'
          AND raa.status = 'Approved'
        ORDER BY ra.full_name ASC, raa.start_date ASC
    ";
    $ra_result = mysqli_query($db, $ra_query);
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
    <link rel="stylesheet" href="../assets/node_modules/dropzone-master/dist/dropzone.css">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <style>
        .outsider-doc-dropzone {
            min-height: 120px;
            border: 2px dashed #b7c3cf;
            border-radius: 6px;
            background: #f8fbfd;
            padding: 18px;
        }

        .outsider-document-status {
            display: none;
            border-left: 4px solid #28a745;
            background: #f2fbf5;
            padding: 12px 14px;
            margin-bottom: 15px;
        }
    </style>
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
                        <h4 class="text-themecolor">Allowance/Wages Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Allowance/Wages Application</li>
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
                        <form id="allowanceForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="applicant_id" value="<?php echo $applicant_id; ?>">
                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Number</strong></label>
                                                <input type="text" name="project_number" value="<?php echo $project_no; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Title</strong></label>
                                                <input type="text" name="project_title" value="<?php echo $project_title; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Leader Name</strong></label>
                                                <input type="text" name="project_leader" value="<?php echo $project_leader; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Application for</strong></label>
                                                <select name="application_for" id="applicationType" class="form-control">
                                                    <option value="" disabled selected>Select Option</option>
                                                    <option value="Research assistant allowance">Research assistant allowance</option>
                                                    <option value="Outsider allowance">Outsider allowance</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- RA Details -->
                            <div id="raFields" class="card" style="display: none;">
                                <h3 class="card-header bg-info text-white">Research Assistant Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>RA Name</strong></label>
                                                <select name="ra_application_id" id="raSelect" class="select2 form-control custom-select">
                                                    <option value="" disabled selected>Select Research Assistant</option>
                                                    <?php while($ra = mysqli_fetch_assoc($ra_result)): ?>
                                                        <option value="<?php echo $ra['ra_application_id']; ?>"
                                                            data-ra-id="<?php echo htmlspecialchars($ra['ra_id']); ?>"
                                                            data-name="<?php echo htmlspecialchars($ra['full_name']); ?>"
                                                            data-email="<?php echo htmlspecialchars($ra['email']); ?>"
                                                            data-ic="<?php echo htmlspecialchars($ra['ic']); ?>"
                                                            data-bank-name="<?php echo htmlspecialchars($ra['bank_name']); ?>"
                                                            data-bank-account="<?php echo htmlspecialchars($ra['no_account']); ?>"
                                                            data-start-date="<?php echo htmlspecialchars($ra['start_date']); ?>"
                                                            data-end-date="<?php echo htmlspecialchars($ra['end_date']); ?>"
                                                            data-duration="<?php echo htmlspecialchars($ra['duration']); ?>"
                                                            data-monthly-amount="<?php echo htmlspecialchars($ra['budget']); ?>">
                                                            <?php echo htmlspecialchars($ra['full_name']); ?> - <?php echo date('d/m/Y', strtotime($ra['start_date'])); ?> to <?php echo date('d/m/Y', strtotime($ra['end_date'])); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <input type="hidden" name="ra_id" id="raId">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>RA IC Number</strong></label>
                                                <input type="text" name="ra_ic" id="raIC" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group">
                                            <input type="text" name="ra_name" id="raName" class="form-control" hidden readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>RA Email</strong></label>
                                                <input type="email" name="ra_email" id="raEmail" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>RA Bank Name</strong></label>
                                                <input type="text" name="ra_bank_name" id="raBankName" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>RA Bank Account Number</strong></label>
                                                <input type="text" name="ra_bank_account" id="raBankAccount" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Appointment Start Date</strong></label>
                                                <input type="text" id="raStartDate" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Appointment End Date</strong></label>
                                                <input type="text" id="raEndDate" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Monthly Allowance/Wage (RM)</strong></label>
                                                <input type="text" name="ra_monthly_amount" id="raMonthlyAmount" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Allowance Month Applied</strong></label>
                                                <select name="allowance_month_no" id="allowanceMonthSelect" class="form-control">
                                                    <option value="" disabled selected>Select RA first</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Allowance Period</strong></label>
                                                <input type="text" id="allowancePeriodPreview" class="form-control" readonly>
                                                <input type="hidden" name="allowance_start_date" id="allowanceStartDate">
                                                <input type="hidden" name="allowance_end_date" id="allowanceEndDate">
                                                <input type="hidden" name="allowance_month" id="allowanceMonth">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><strong>Job Description</strong></label>
                                                <textarea name="ra_job_description" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Outsider Details -->
                            <div id="outsiderFields" class="card" style="display: none;">
                                <h3 class="card-header bg-info text-white">Outsider Details</h3>
                                <div class="card-body">
                                    <input type="hidden" name="outsider_existing_documents" id="outsiderExistingDocuments" value="0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Name</strong></label>
                                                <input type="text" name="outsider_name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Email Address</strong></label>
                                                <input type="email" name="outsider_email" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>IC Number</strong></label>
                                                <input type="text" name="outsider_ic" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Bank Name</strong></label>
                                                <input type="text" name="outsider_bank_name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Bank Account Number</strong></label>
                                                <input type="text" name="outsider_bank_account" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><strong>Job Description</strong></label>
                                                <textarea name="outsider_job_description" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Total Allowance (RM)</strong></label>
                                                <input type="number" name="outsider_total_allowance" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Start Date</strong></label>
                                                <input type="date" name="outsider_start_date" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>End Date</strong></label>
                                                <input type="date" name="outsider_end_date" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="outsiderExistingDocumentNotice" class="outsider-document-status">
                                        Existing IC copy and bank statement found. The system will reuse the documents stored from the previous outsider allowance application.
                                    </div>
                                    <div id="outsiderUploadFields">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Upload IC Copy</strong></label>
                                                    <div id="outsiderIcDropzone" class="dropzone outsider-doc-dropzone">
                                                        <div class="dz-message">Drop IC copy here or click to upload.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Upload Bank Statement</strong></label>
                                                    <div id="outsiderBankStatementDropzone" class="dropzone outsider-doc-dropzone">
                                                        <div class="dz-message">Drop bank statement here or click to upload.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Declaration -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-info text-white fw-semibold">
                                    Consultant Declaration
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">I hereby declare that:</p>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                             The job has been carried out by the above name.
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                             Declaration to IRB is the responsibility of the recipient.
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                             My project has no other pending financial issues.
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                             All the information given above is true and correct.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="button" id="submitByProjectLeader" class="btn btn-lg btn-success"> Submit</button>
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
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="../assets/node_modules/dropzone-master/dist/dropzone.js"></script>
    <script>
        Dropzone.autoDiscover = false;
    </script>
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
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script>
        $(function () {
            // Switchery
            if (typeof Switchery !== 'undefined') {
                $('.js-switch').each(function () {
                    new Switchery($(this)[0], $(this).data());
                });
            }
            // For select 2
            $(".select2").select2();
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker();
            }
            //Bootstrap-TouchSpin
            if ($.fn.TouchSpin) {
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
            }
            // For multiselect
            if ($.fn.multiSelect) {
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
            }
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
        $(document).ready(function () {
            let outsiderIcDropzone = null;
            let outsiderBankStatementDropzone = null;

            function initOutsiderDropzones() {
                const icElement = $('#outsiderIcDropzone')[0];
                const bankStatementElement = $('#outsiderBankStatementDropzone')[0];

                if (icElement && !icElement.dropzone) {
                    outsiderIcDropzone = new Dropzone(icElement, {
                        url: '#',
                        autoProcessQueue: false,
                        uploadMultiple: false,
                        maxFiles: 1,
                        maxFilesize: 5,
                        acceptedFiles: '.pdf,.jpg,.jpeg,.png',
                        addRemoveLinks: true,
                        dictDefaultMessage: 'Drop IC copy here or click to upload.',
                        dictRemoveFile: 'Remove',
                        init: function () {
                            this.on('maxfilesexceeded', function (file) {
                                this.removeFile(file);
                                Swal.fire('Too Many Files', 'You can upload only one IC copy.', 'warning');
                            });
                        }
                    });
                } else if (icElement && icElement.dropzone) {
                    outsiderIcDropzone = icElement.dropzone;
                }

                if (bankStatementElement && !bankStatementElement.dropzone) {
                    outsiderBankStatementDropzone = new Dropzone(bankStatementElement, {
                        url: '#',
                        autoProcessQueue: false,
                        uploadMultiple: false,
                        maxFiles: 1,
                        maxFilesize: 5,
                        acceptedFiles: '.pdf,.jpg,.jpeg,.png',
                        addRemoveLinks: true,
                        dictDefaultMessage: 'Drop bank statement here or click to upload.',
                        dictRemoveFile: 'Remove',
                        init: function () {
                            this.on('maxfilesexceeded', function (file) {
                                this.removeFile(file);
                                Swal.fire('Too Many Files', 'You can upload only one bank statement.', 'warning');
                            });
                        }
                    });
                } else if (bankStatementElement && bankStatementElement.dropzone) {
                    outsiderBankStatementDropzone = bankStatementElement.dropzone;
                }
            }

            function resetOutsiderDocumentState() {
                $('#outsiderExistingDocuments').val('0');
                $('#outsiderExistingDocumentNotice').hide();
                $('#outsiderUploadFields').show();
            }

            function checkExistingOutsiderDocuments() {
                const email = $.trim($('input[name="outsider_email"]').val());
                const ic = $.trim($('input[name="outsider_ic"]').val());

                if (!email && !ic) {
                    resetOutsiderDocumentState();
                    return;
                }

                $.ajax({
                    url: 'check-outsider-allowance-profile.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        email: email,
                        ic: ic
                    },
                    success: function(response) {
                        if (response.exists) {
                            $('#outsiderExistingDocuments').val('1');
                            $('#outsiderExistingDocumentNotice').show();
                            $('#outsiderUploadFields').hide();

                            if (response.bank_name && !$('input[name="outsider_bank_name"]').val()) {
                                $('input[name="outsider_bank_name"]').val(response.bank_name);
                            }

                            if (response.no_account && !$('input[name="outsider_bank_account"]').val()) {
                                $('input[name="outsider_bank_account"]').val(response.no_account);
                            }
                        } else {
                            resetOutsiderDocumentState();
                        }
                    },
                    error: function() {
                        resetOutsiderDocumentState();
                    }
                });
            }

            function formatDate(dateString) {
                if (!dateString) {
                    return '';
                }

                const date = new Date(dateString + 'T00:00:00');
                if (isNaN(date.getTime())) {
                    return dateString;
                }

                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }

            function addMonths(date, months) {
                const result = new Date(date.getTime());
                const day = result.getDate();
                result.setMonth(result.getMonth() + months);

                if (result.getDate() !== day) {
                    result.setDate(0);
                }

                return result;
            }

            function toSqlDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return year + '-' + month + '-' + day;
            }

            function buildAllowanceMonthOptions(startDate, endDate, duration) {
                const $monthSelect = $('#allowanceMonthSelect');
                $monthSelect.empty().append('<option value="" disabled selected>Select month</option>');

                if (!startDate || !endDate || !duration) {
                    return;
                }

                const start = new Date(startDate + 'T00:00:00');
                const end = new Date(endDate + 'T00:00:00');

                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                    return;
                }

                for (let i = 1; i <= parseInt(duration, 10); i++) {
                    const periodStart = addMonths(start, i - 1);
                    let periodEnd = new Date(addMonths(start, i).getTime());
                    periodEnd.setDate(periodEnd.getDate() - 1);

                    if (periodEnd > end || i === parseInt(duration, 10)) {
                        periodEnd = end;
                    }

                    const monthLabel = periodStart.toLocaleDateString('en-GB', {
                        month: 'long',
                        year: 'numeric'
                    });
                    const label = 'Month ' + i + ' - ' + monthLabel + ' (' + formatDate(toSqlDate(periodStart)) + ' to ' + formatDate(toSqlDate(periodEnd)) + ')';

                    $monthSelect.append(
                        $('<option>', {
                            value: i,
                            text: label,
                            'data-month': monthLabel,
                            'data-start': toSqlDate(periodStart),
                            'data-end': toSqlDate(periodEnd)
                        })
                    );
                }
            }

            function resetRaFields() {
                $('#raId, #raName, #raEmail, #raIC, #raBankName, #raBankAccount, #raStartDate, #raEndDate, #raMonthlyAmount, #allowancePeriodPreview, #allowanceStartDate, #allowanceEndDate, #allowanceMonth').val('');
                $('#allowanceMonthSelect').empty().append('<option value="" disabled selected>Select RA first</option>');
            }

            // Toggle fields based on application type
            $('#applicationType').on('change', function () {
                const selectedType = $(this).val();
                if (selectedType === 'Research assistant allowance') {
                    $('#raFields').show();
                    $('#outsiderFields').hide();
                } else if (selectedType === 'Outsider allowance') {
                    $('#raFields').hide();
                    $('#outsiderFields').show();
                    resetRaFields();
                    initOutsiderDropzones();
                    checkExistingOutsiderDocuments();
                } else {
                    $('#raFields, #outsiderFields').hide();
                    resetRaFields();
                }
                
                setTimeout(() => {
                    $('.select2').select2();
                }, 50);
            });
    
            // Update RA details dynamically
            $('#raSelect').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const raId = selectedOption.data('ra-id');
                const raName = selectedOption.data('name');
                const raEmail = selectedOption.data('email');
                const raIC = selectedOption.data('ic');
                const raBankName = selectedOption.data('bank-name');
                const raBankAccount = selectedOption.data('bank-account');
                const startDate = selectedOption.data('start-date');
                const endDate = selectedOption.data('end-date');
                const duration = selectedOption.data('duration');
                const monthlyAmount = parseFloat(selectedOption.data('monthly-amount')) || 0;
    
                // Update input fields
                $('#raId').val(raId);
                $('#raName').val(raName);  // Update RA name field if available
                $('#raEmail').val(raEmail);
                $('#raIC').val(raIC);
                $('#raBankName').val(raBankName);
                $('#raBankAccount').val(raBankAccount);
                $('#raStartDate').val(formatDate(startDate));
                $('#raEndDate').val(formatDate(endDate));
                $('#raMonthlyAmount').val(monthlyAmount.toFixed(2));
                $('#allowancePeriodPreview, #allowanceStartDate, #allowanceEndDate, #allowanceMonth').val('');
                buildAllowanceMonthOptions(startDate, endDate, duration);
            });

            $('#allowanceMonthSelect').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const month = selectedOption.data('month') || '';
                const start = selectedOption.data('start') || '';
                const end = selectedOption.data('end') || '';

                $('#allowanceMonth').val(month);
                $('#allowanceStartDate').val(start);
                $('#allowanceEndDate').val(end);
                $('#allowancePeriodPreview').val(month ? month + ' (' + formatDate(start) + ' to ' + formatDate(end) + ')' : '');
            });

            $('input[name="outsider_email"], input[name="outsider_ic"]').on('blur change', checkExistingOutsiderDocuments);

            window.getOutsiderDocumentDropzones = function() {
                return {
                    ic: outsiderIcDropzone,
                    bankStatement: outsiderBankStatementDropzone
                };
            };
        });
    </script>
    <script>
        $(document).on('click', '#submitByProjectLeader', function () {
            const $submitBtn = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "Once submit, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons
        
                if (result.isConfirmed || result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");
        
                    // Ambil borang menggunakan ID borang
                    const form = $('#allowanceForm')[0]; // Gantikan #invoiceForm dengan ID borang anda
                    const formData = new FormData(form);
                    const selectedType = $('#applicationType').val();
                    const documentDropzones = window.getOutsiderDocumentDropzones ? window.getOutsiderDocumentDropzones() : {};

                    if (selectedType === 'Outsider allowance' && $('#outsiderExistingDocuments').val() !== '1') {
                        const icFiles = documentDropzones.ic ? documentDropzones.ic.getAcceptedFiles() : [];
                        const bankStatementFiles = documentDropzones.bankStatement ? documentDropzones.bankStatement.getAcceptedFiles() : [];

                        if (icFiles.length === 0 || bankStatementFiles.length === 0) {
                            Swal.fire('Missing Documents', 'Please upload both IC copy and bank statement for first-time outsider.', 'warning');
                            return;
                        }

                        formData.append('outsider_ic_file', icFiles[0], icFiles[0].name);
                        formData.append('outsider_bank_statement_file', bankStatementFiles[0], bankStatementFiles[0].name);
                    }
        
                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }
        
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'process_allowance.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        timeout: 30000,
                        beforeSend: function () {
                            $submitBtn.prop('disabled', true).text('Submitting...');
                            Swal.fire({
                                title: 'Submitting Application',
                                text: 'Please wait while your allowance/wages application is being submitted.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                                onOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function (response) {
                            console.log("AJAX success response:", response);
        
                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'allowances-wages.php'; // Alihkan ke halaman yang diinginkan
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
                            const message = status === 'timeout'
                                ? 'Submission timed out. Please try again.'
                                : 'An error occurred during submission. Please check the console for details.';
                            Swal.fire(
                                'Error!',
                                message,
                                'error'
                            );
                        },
                        complete: function () {
                            $submitBtn.prop('disabled', false).text('Submit');
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Allowance/Wages application apporoval has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>
