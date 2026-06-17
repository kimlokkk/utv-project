<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id                 =   $_GET['id'];
    $query              =   "SELECT * FROM project WHERE id = '$id' ";  
    $result             =   mysqli_query($db, $query);
    while($row          =   mysqli_fetch_array($result))  
    {
        $leader_id                      = $row['leader_id'];
        $project_leader                 = $row['project_leader'];
        $project_no                     = $row['project_no'];
        $project_title                  = $row['project_title'];
        $project_type                   = $row['project_type'];
        $project_start                  = $row['project_start'];
        $project_end                    = $row['project_end'];
        $registered_project_value       = $row['registered_project_value'];
        $adjusted_project_value         = $row['adjusted_project_value'];
        $quotation_ref_no               = $row['quotation_ref_no'];
        $appointment_letter             = $row['appointment_letter'];
        $approval_external_work         = $row['approval_external_work'];
        $quotation_doc                  = $row['quotation_doc'];
        $agreement_doc                  = $row['agreement_doc'];
        $project_proposal               = $row['project_proposal'];
        $other_doc_1                    = $row['other_doc_1'];
        $other_doc_2                    = $row['other_doc_2'];
        $client_company_name            = $row['client_company_name'];
        $client_address                 = $row['client_address'];
        $client_contact                 = $row['client_contact'];
        $client_business_type           = $row['client_business_type'];
        $client_pic                     = $row['client_pic'];
        $client_pic_email               = $row['client_pic_email'];
        $client_pic_contact             = $row['client_pic_contact'];
        $date_create                    = $row['date_create'];
        $project_status                 = $row['project_status'];
    }
    
    $tracking_query = "SELECT * FROM project_tracker WHERE project_id = '$id' ORDER BY date DESC";
    $tracking_result = mysqli_query($db, $tracking_query);
    $tracking_data = [];
    while ($track_row = mysqli_fetch_array($tracking_result)) {
        $tracking_data[] = $track_row;
    }
?>
<?php
    if (isset($_GET['update']) && $_GET['update'] == 'save-success' && isset($_GET['id'])) {
        $id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); // Sanitize the ID
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Project Saved',
                    text: 'Your project has been successfully saved!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    window.location.replace('index.php');
                });
            });
        </script>";
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
                        <h4 class="text-themecolor">Appoint Members</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">RA/RO Listing</a></li>
                                <li class="breadcrumb-item active">Appoint Members</li>
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
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="leader_id" value="<?php echo $userData['id']; ?>">
                            <input type="hidden" name="project_leader" value="<?php echo $userData['full_name']; ?>">
                            <input type="hidden" name="leader_ic" value="<?php echo $userData['ic']; ?>">
                            <input type="hidden" name="project_no" value="<?php echo $project_no; ?>">
                            <input type="hidden" name="project_title" value="<?php echo $project_title; ?>">
                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Appoint Members to <?php echo htmlspecialchars($project_title); ?></h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Add Members (Consultant)</label>
                                                    <div class="table-responsive">  
                                                        <table class="table table-bordered" id="dynamic_field">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 20%;">Member Name</th>
                                                                    <th style="width: 15%;">Duration (Months)</th>
                                                                    <th style="width: 15%;">Start Date</th>
                                                                    <th style="width: 20%;">Payment Type</th>
                                                                    <th style="width: 15%;">Monthly Budget (RM)</th>
                                                                    <th style="width: 10%;">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td style="width: 20%;">
                                                                        <select class="select2 form-control custom-select" name="members[1][name_ic_id]" style="width: 100%;" required>
                                                                            <option disabled selected>Select Option</option>
                                                                            <?php
                                                                            $query = "SELECT id, full_name, ic FROM research_assistant WHERE status = 'Approved'";
                                                                            $result = mysqli_query($db, $query);
                                                                            while ($data = mysqli_fetch_array($result)) {
                                                                                echo "<option value='{$data['id']}|{$data['full_name']}|{$data['ic']}'>
                                                                                        {$data['full_name']} ({$data['ic']}, ID: {$data['id']})
                                                                                      </option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                    <td style="width: 15%;">
                                                                        <input type="number" name="members[1][duration]" class="form-control" placeholder="Duration (Months)" required>
                                                                    </td>
                                                                    <td style="width: 15%;">
                                                                        <input type="date" name="members[1][start_date]" class="form-control" required>
                                                                    </td>
                                                                    <td style="width: 20%;">
                                                                        <select name="members[1][payment_type]" class="form-control" required>
                                                                            <option disabled selected>Select Payment Type</option>
                                                                            <option value="Salary">Salary</option>
                                                                            <option value="Token">Token</option>
                                                                            <option value="Allowance">Allowance</option>
                                                                        </select>
                                                                    </td>
                                                                    <td style="width: 15%;">
                                                                        <input type="number" name="members[1][budget]" class="form-control" placeholder="Budget (RM)" min="0" required>
                                                                    </td>
                                                                    <td class="text-center" style="width: 10%;">
                                                                        <button type="button" name="add" id="add" class="btn btn-info">Add More</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit Buttons -->
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="reset" class="btn btn-lg btn-danger"> Reset</button>&nbsp;&nbsp;
                                    <button type="submit" name="btn_addMembersConsultancyProject" class="btn btn-lg btn-info"> Appoint Members</button>
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
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script>  
        $(document).ready(function () {
            let i = 1;
    
            // Add new row
            $('#add').click(function () {
                i++;
                let newRow = `
                <tr id="row${i}">
                    <td style="width: 20%;">
                        <select class="select2 form-control custom-select" name="members[${i}][name_ic_id]" style="width: 100%;" required>
                            <option disabled selected>Select Option</option>
                            <?php
                            $query = "SELECT id, full_name, ic FROM research_assistant";
                            $result = mysqli_query($db, $query);
                            while ($data = mysqli_fetch_array($result)) {
                                echo "<option value='{$data['id']}|{$data['full_name']}|{$data['ic']}'>
                                        {$data['full_name']} ({$data['ic']}, ID: {$data['id']})
                                      </option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 15%;">
                        <input type="number" name="members[${i}][duration]" class="form-control" placeholder="Duration (Months)" min="1" required>
                    </td>
                    <td style="width: 15%;">
                        <input type="date" name="members[${i}][start_date]" class="form-control" required>
                    </td>
                    <td style="width: 20%;">
                        <select name="members[${i}][payment_type]" class="form-control" required>
                            <option disabled selected>Select Payment Type</option>
                            <option value="Salary">Salary</option>
                            <option value="Token">Token</option>
                            <option value="Allowance">Allowance</option>
                        </select>
                    </td>
                    <td style="width: 15%;">
                        <input type="number" name="members[${i}][budget]" class="form-control" placeholder="Budget (RM)" min="0" required>
                    </td>
                    <td class="text-center" style="width: 10%;">
                        <button type="button" name="remove" id="${i}" class="btn btn-danger btn_remove">Remove</button>
                    </td>
                </tr>
                `;
                $('#dynamic_field').append(newRow);
    
                // Reinitialize Select2 for the new select element
                $('.select2').select2();
            });
    
            // Remove row
            $(document).on('click', '.btn_remove', function () {
                const button_id = $(this).attr("id");
                $('#row' + button_id).remove();
            });
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
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector("form");
            const submitButton = document.querySelector("button[name='btn_submit']");
            const updateButton = document.querySelector("button[name='btn_updateConsultancyProject']");
    
            // Event listener for Submit button
            submitButton.addEventListener("click", function (event) {
                if (!validateForm()) {
                    event.preventDefault(); // Prevent form submission if validation fails
                    Swal.fire({
                        title: "Incomplete Fields",
                        text: "Please complete all required fields before submitting!",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                }
            });
    
            // Save button should skip validation
            updateButton.addEventListener("click", function (event) {
                // No validation for Save button, form submits directly
                form.setAttribute("novalidate", "novalidate");
            });
    
            // Validation function
            function validateForm() {
                let isValid = true;
                const requiredFields = form.querySelectorAll(
                    "input[required], select[required], textarea[required]"
                );
    
                requiredFields.forEach((field) => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add("is-invalid"); // Highlight invalid fields
                    } else {
                        field.classList.remove("is-invalid");
                    }
                });
    
                return isValid;
            }
        });
    </script>
</body>

</html>