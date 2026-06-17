<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
if (isset($_GET['update']) && $_GET['update'] == 'save-success') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Project Saved',
                text: 'Your project has been successfully saved!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.replace('training-project.php');
            });
        });
    </script>";
}
?>
<?php
if (isset($_GET['update']) && $_GET['update'] == 'submit-success') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Project Submit',
                text: 'Your project has been successfully submit for verification!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.replace('training-project.php');
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
                        <h4 class="text-themecolor">New Training Project</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">New Project</a></li>
                                <li class="breadcrumb-item active">New Training Project</li>
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
                            <input type="hidden" name="leader_id" value="<?php echo $userData['id']; ?>">
                            <input type="hidden" name="project_leader" value="<?php echo $userData['full_name']; ?>">
                            <input type="hidden" name="leader_ic" value="<?php echo $userData['ic']; ?>">
                            <div class="card">
                                <h3 class="card-header bg-info text-white">New Training Project</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Title <span class="text-danger">*</span></label>
                                                    <input type="text" name="project_title" value="<?php echo isset($_POST['project_title']) ? $_POST['project_title'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Type of Project <span class="text-danger">*</span></label>
                                                    <div>
                                                        <select class="form-control" name="project_type" required>
                                                            <option disabled selected>Select Option</option>
                                                            <option value="Webinar">Webinar</option>
                                                            <option value="Seminar">Seminar</option>
                                                            <option value="Conference">Conference</option>
                                                            <option value="Training/Workshop">Training/Workshop</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Start <span class="text-danger">*</span></label>
                                                    <input type="date" name="project_start" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project End <span class="text-danger">*</span></label>
                                                    <input type="date" name="project_end" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Registered Project Value (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="registered_project_value" value="<?php echo isset($_POST['registered_project_value']) ? $_POST['registered_project_value'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Adjusted Project Value (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="adjusted_project_value" value="<?php echo isset($_POST['adjusted_project_value']) ? $_POST['adjusted_project_value'] : '' ?>" class="form-control" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project-Related File Uploads</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Appointment/Offer Letter</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="appointment_letter" class="custom-file-input" id="inputGroupFile01">
                                                            <label class="custom-file-label" for="inputGroupFile01">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Approval to Undertake External Work <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="approval_external_work" class="custom-file-input" id="inputGroupFile02" required>
                                                            <label class="custom-file-label" for="inputGroupFile02">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Project Proposal & Budget</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="project_proposal" class="custom-file-input" id="inputGroupFile05">
                                                            <label class="custom-file-label" for="inputGroupFile05">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Other related document 1</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="other_doc_1" class="custom-file-input" id="inputGroupFile06">
                                                            <label class="custom-file-label" for="inputGroupFile06">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Other related document 2</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="other_doc_2" class="custom-file-input" id="inputGroupFile07">
                                                            <label class="custom-file-label" for="inputGroupFile07">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Client Information</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Client's Company Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_company_name" value="<?php echo isset($_POST['client_company_name']) ? $_POST['client_company_name'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Full Address <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_address" value="<?php echo isset($_POST['client_address']) ? $_POST['client_address'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Contact Number <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_contact" value="<?php echo isset($_POST['client_contact']) ? $_POST['client_contact'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Business Type <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="client_business_type" required>
                                                        <option disabled selected>Select Option</option>
                                                        <option value="Government">Government</option>
                                                        <option value="Statutory Body">Statutory Body</option>
                                                        <option value="Private">Private</option>
                                                        <option value="GLC">GLC</option>
                                                        <option value="UiTM">UiTM</option>
                                                        <option value="International">International</option>
                                                        <option value="NGO">NGO</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge</label>
                                                    <input type="text" name="client_pic" value="<?php echo isset($_POST['client_pic']) ? $_POST['client_pic'] : '' ?>" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge Email Address</label>
                                                    <input type="email" name="client_pic_email" value="<?php echo isset($_POST['client_pic_email']) ? $_POST['client_pic_email'] : '' ?>" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge Contact Number</label>
                                                    <input type="phone" name="client_pic_contact" value="<?php echo isset($_POST['client_pic_contact']) ? $_POST['client_pic_contact'] : '' ?>" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="reset" class="btn btn-lg btn-danger"> Reset</button>&nbsp;&nbsp;
                                    <button 
                                      type="submit"
                                      name="btn_saveTrainingProject"
                                      class="btn btn-lg btn-info"
                                      formnovalidate
                                      onclick="document.getElementById('action').value='save';"
                                    >Save</button>
                                    <!--<button type="submit" name="btn_submit" class="btn btn-lg btn-success"> Submit</button>-->
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
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
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
            $('#myTable2').DataTable();
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
    <script type="text/javascript">
        $(document).ready(function () {
            $("#search_therapist").autocomplete({
                source: 'ajax-therapist-search.php'
            });
        });
    </script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script>  
        $(document).ready(function () {
        var i = 1;
            $('#add').click(function () {
                i++;
                var newRow = $('<tr id="row' + i + '">');
                /*newRow.append('<td><select class="form-control name_list" name="program[]" id="program" required>' +
                    '<option disabled selected>Select Option</option>' +
                    '<option value="Occupational Therapy (Official)">Occupational Therapy (Official)</option>' +
                    '<option value="Speech Therapy (Screening)">Speech Therapy (Screening)</option>' +
                    '<option value="Speech Therapy (Official)">Speech Therapy (Official)</option>' +
                    '<option value="Early Intervention Program (Trial Class)">Early Intervention Program (Trial Class)</option>' +
                    '<option value="Early Intervention Program (Official Class)">Early Intervention Program (Official Class)</option>' +
                    '<option value="Playgroup (Trial Class)">Playgroup (Trial Class)</option>' +
                    '</select></td>');*/

                // Add an empty select for therapists
                var therapistSelect = $('<td><select class="select2 form-control custom-select" name="stud_therapist[]" required style="width:100%;">' +
                                            '<option disabled selected>Select Members</option>' +
                                            '<option value="Ali">Ali (UITM1234 - 970909090909)</option>' +
                                            '<option value="Aisha">Aisha (UITM5678 - 981010101010)</option>' +
                                            '<option value="John">John (UITM9101 - 960505050505)</option>' +
                                            '<option value="Maria">Maria (UITM1122 - 950303030303)</option>' +
                                            '<option value="Ahmad">Ahmad (UITM3344 - 970707070707)</option>' +
                                            '<option value="Sophia">Sophia (UITM5566 - 990808080808)</option>' +
                                            '<option value="Daniel">Daniel (UITM7788 - 951212121212)</option>' +
                                            '<option value="Fatimah">Fatimah (UITM9910 - 960909090909)</option>' +
                                        '</select></td>');


                newRow.append(therapistSelect);

                therapistSelect.find('select').select2();

                newRow.append('<td class="text-center"><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Delete</button></td>');

                $('#dynamic_field').append(newRow);

                // Fetch therapists using AJAX and populate the select options
                $.ajax({
                    url: 'get_therapist.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var therapistSelect = newRow.find('select[name="stud_therapist[]"]');
                        $.each(data, function (index, therapist) {
                            therapistSelect.append('<option value="' + therapist.value + '">' + therapist.text + '</option>');
                        });
                    }
                });
            });

            $(document).on('click', '.btn_remove', function () {
                var button_id = $(this).attr("id");
                $('#row' + button_id).remove();
            });
        });  
    </script>
    <script>  
        $(document).ready(function () {
            var i = 1;
            $('#add2').click(function () {
                i++;
                var newRow = $('<tr id="row' + i + '">');
        
                // Create Project Timeline input
                var timelineInput = $('<td><input type="text" class="form-control" name="project_timeline[]" placeholder="Enter Project Timeline" required /></td>');
        
                // Create Value (RM) input
                var valueInput = $('<td><input type="text" class="form-control" name="project_value[]" placeholder="Enter Value (RM)" required /></td>');
        
                // Append the inputs to the new row
                newRow.append(timelineInput);
                newRow.append(valueInput);
        
                // Add the remove button
                newRow.append('<td class="text-center"><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Delete</button></td>');
        
                // Append the new row to the dynamic field table
                $('#dynamic_field2').append(newRow);
            });
        
            // Handle the remove button click event
            $(document).on('click', '.btn_remove', function () {
                var button_id = $(this).attr("id");
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
            const saveButton = document.querySelector("button[name='btn_saveTrainingProject']");
        
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
        
            // Save button doesn't require validation
            saveButton.addEventListener("click", function () {
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