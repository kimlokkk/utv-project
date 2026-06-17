<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
    $leaderId = isset($userData['id']) ? (string)$userData['id'] : '';
    
    // Define $staffOptions to be used later in the select box.
    $staffOptions = "<option disabled selected>Select Members</option>";
    // Use the correct connection variable; update $db to $conn if needed.
    $query = "SELECT id, full_name, ic FROM uitm_staff";
    $result = mysqli_query($db, $query); // If your connection variable is $db
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $selectedStaffId = (string)$row['id'];
            $isLeader = ($leaderId !== '' && $selectedStaffId === $leaderId);
            $disabledAttr = $isLeader ? " disabled data-self='1'" : "";
            $leaderLabel = $isLeader ? " (Project Leader)" : "";
            $staffOptions .= "<option value='" . $row['id'] . "'" . $disabledAttr . ">" . $row['full_name'] . " (ID: " . $row['id'] . " - " . $row['ic'] . ")" . $leaderLabel . "</option>";
        }
    } else {
        $staffOptions .= "<option disabled>Error loading staff</option>";
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
                window.location.replace('consultancy-project.php');
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
                        <h4 class="text-themecolor">New Consultancy Project</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">New Project</a></li>
                                <li class="breadcrumb-item active">New Consultancy Project</li>
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
                        <form method="POST" enctype="multipart/form-data" id="consultancyProjectForm">
                            <input type="hidden" id="action" name="action" value="">
                            <input type="hidden" name="leader_id" value="<?php echo $userData['id']; ?>">
                            <input type="hidden" name="project_leader" value="<?php echo $userData['full_name']; ?>">
                            <input type="hidden" name="leader_ic" value="<?php echo $userData['ic']; ?>">
                            <div class="card">
                                <h3 class="card-header bg-info text-white">New Consultancy Project</h3>
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
                                                    <label class="control-label d-flex align-items-center">
                                                        Type of Project <span class="text-danger ml-1">*</span>
                                                        <span 
                                                            class="ml-2 badge badge-info"
                                                            style="cursor: help; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center;"
                                                            title="Select the category that best describes the consultancy project."
                                                        >
                                                            !
                                                        </span>
                                                    </label>
                                                    <div>
                                                        <select class="form-control" name="project_type" id="project_type" required>
                                                            <option disabled selected>Select Option</option>
                                                            <option value="Contract Research" data-info="Research and development (R&D) activities for external organizations on a fee-for-service basis. Deliverables usually include reports, data sets, or final study results.">
                                                                Contract Research
                                                            </option>
                                                            <option value="Testing" data-info="Executes a defined set of tests to identify defects, ensure compliance, or confirm performance. Outputs usually include test reports, bug logs, or certificates of compliance.">
                                                                Testing
                                                            </option>
                                                            <option value="Evaluation" data-info="A systematic assessment used to determine the value, merit, or effectiveness of an existing service, program, or policy.">
                                                                Evaluation
                                                            </option>
                                                            <option value="Expert Panel" data-info="A group of specialists convened to provide authoritative guidance, technical assessments, or consensus-based recommendations on a specific problem.">
                                                                Expert Panel
                                                            </option>
                                                            <option value="Professional Services" data-info="Specialized, knowledge-based functions provided by individuals or firms with technical expertise or professional licenses, such as trainers or speakers.">
                                                                Professional Services
                                                            </option>
                                                        </select>
                                                        <div 
                                                            id="project_type_info_box" 
                                                            class="alert alert-info mt-2 mb-0 py-2 px-3"
                                                            style="display: none; font-size: 13px;"
                                                        >
                                                            <strong id="project_type_title">Definition:</strong>
                                                            <span id="project_type_info">Please select a project type to view its definition.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Start <span class="text-danger">*</span></label>
                                                    <input type="date" name="project_start" id="project_start" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project End <span class="text-danger">*</span></label>
                                                    <input type="date" name="project_end" id="project_end" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Registered Project Value (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="registered_project_value" id="registered_project_value" min="1" step="0.01" value="<?php echo isset($_POST['registered_project_value']) ? $_POST['registered_project_value'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Adjusted Project Value (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="adjusted_project_value" id="adjusted_project_value" value="<?php echo isset($_POST['adjusted_project_value']) ? $_POST['adjusted_project_value'] : '' ?>" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Add Members (Consultant)</label>
                                                    <small class="form-text text-muted">Project leader is excluded automatically and cannot be selected as a consultant member.</small>
                                                    <div class="table-responsive">  
                                                        <table class="table table-bordered" id="dynamic_field_members">
                                                            <tr>
                                                                <td style="width: 80%;">
                                                                    <select class="select2 form-control custom-select member-select" name="project_members[]">
                                                                        <?php echo $staffOptions; ?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" name="add_member" id="add_member" class="btn btn-info">Add More</button>
                                                                </td>  
                                                            </tr>  
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--<div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Project Timeline / Milestone</label>
                                                    <div class="table-responsive">  
                                                        <table class="table table-bordered" id="dynamic_field2">  
                                                            <tr>
                                                                <td style="width: 50%;">
                                                                    <input type="text" class="form-control" name="project_timeline[]" placeholder="Enter Project Timeline" />
                                                                </td>
                                                                <td style="width: 30%;">
                                                                    <input type="text" class="form-control" name="project_value[]" placeholder="Enter Value (RM)" />
                                                                </td>
                                                                <td class="text-center" style="width: 20%;">
                                                                    <button type="button" name="add" id="add2" class="btn btn-info">Add More</button>
                                                                </td>  
                                                            </tr>  
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>-->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Quotation Ref No. <span class="text-danger">*</span></label>
                                                    <input type="text" name="quotation_ref_no" value="<?php echo isset($_POST['quotation_ref_no']) ? $_POST['quotation_ref_no'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Timeline</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Add Phase</label>
                                                    <div class="table-responsive">  
                                                        <table class="table table-bordered" id="dynamic_field_timeline">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 20%;">Phase</th>
                                                                    <th style="width: 30%;">Description</th>
                                                                    <th style="width: 20%;">Value (RM)</th>
                                                                    <th style="width: 10%;">Start Date</th>
                                                                    <th style="width: 10%;">End Date</th>
                                                                    <th style="width: 10%;">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td style="width: 20%;">
                                                                        <input type="text" name="project_timeline[1][title]" class="form-control" placeholder="Title" required>
                                                                    </td>
                                                                    <td style="width: 30%;">
                                                                        <input type="text" name="project_timeline[1][description]" class="form-control" placeholder="Description" required>
                                                                    </td>
                                                                    <td style="width: 20%;">
                                                                        <input type="number" name="project_timeline[1][value]" class="form-control timeline-value" placeholder="Value (RM)" step="0.01" min="0.01" required>
                                                                    </td>
                                                                    <td style="width: 10%;">
                                                                        <input type="date" name="project_timeline[1][date_start]" class="form-control timeline-start" placeholder="Start date" readonly required>
                                                                    </td>
                                                                    <td style="width: 10%;">
                                                                        <input type="date" name="project_timeline[1][date_end]" class="form-control timeline-end" placeholder="End date" required>
                                                                    </td>
                                                                    <td class="text-center" style="width: 10%;">
                                                                        <button type="button" name="add_timeline" id="add_timeline" class="btn btn-info">Add</button>
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
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project-Related File Uploads</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Appointment/Offer Letter <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="appointment_letter" class="custom-file-input" id="inputGroupFile01" required>
                                                            <label class="custom-file-label" for="inputGroupFile01">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>PTJ Approval<span class="text-danger">*</span></label>
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Quotation Document <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="quotation_doc" class="custom-file-input" id="inputGroupFile03" required>
                                                            <label class="custom-file-label" for="inputGroupFile03">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Agreement/MoA (If any)</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="agreement_doc" class="custom-file-input" id="inputGroupFile04">
                                                            <label class="custom-file-label" for="inputGroupFile04">Choose File</label>
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
                                                    <label class="control-label">Person in Charge <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_pic" value="<?php echo isset($_POST['client_pic']) ? $_POST['client_pic'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" name="client_pic_email" value="<?php echo isset($_POST['client_pic_email']) ? $_POST['client_pic_email'] : '' ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge Contact Number <span class="text-danger">*</span></label>
                                                    <input type="phone" name="client_pic_contact" value="<?php echo isset($_POST['client_pic_contact']) ? $_POST['client_pic_contact'] : '' ?>" class="form-control" required>
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
                                      name="btn_saveConsultancyProject"
                                      id="btn_saveConsultancyProject"
                                      class="btn btn-lg btn-info"
                                      formnovalidate
                                    >
                                        Save
                                    </button>
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
     <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
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
    <script>  
        $(document).ready(function () {
            let i = $('#dynamic_field_timeline tbody tr').length;

            function getProjectDates() {
                return {
                    start: $('#project_start').val(),
                    end: $('#project_end').val()
                };
            }

            function updateTimelineValueSummary() {
                const totalValue = parseFloat($('#registered_project_value').val()) || 0;
                let timelineTotal = 0;
                $('#dynamic_field_timeline tbody .timeline-value').each(function () {
                    timelineTotal += parseFloat($(this).val()) || 0;
                });

                $('#adjusted_project_value').val(timelineTotal > 0 ? timelineTotal.toFixed(2) : '');

                $('#dynamic_field_timeline tbody .timeline-value').each(function () {
                    const currentValue = parseFloat($(this).val()) || 0;
                    const maxAllowed = Math.max(0, totalValue - (timelineTotal - currentValue));
                    $(this).attr('max', maxAllowed.toFixed(2));
                });
            }

            function syncTimelineDates() {
                const dates = getProjectDates();
                const $rows = $('#dynamic_field_timeline tbody tr');
                if (!$rows.length) return;

                $rows.each(function (index) {
                    const $row = $(this);
                    const $start = $row.find('.timeline-start');
                    const $end = $row.find('.timeline-end');

                    $start.attr('min', dates.start || '');
                    $start.attr('max', dates.end || '');
                    $end.attr('min', dates.start || '');
                    $end.attr('max', dates.end || '');

                    if (index === 0 && dates.start) {
                        $start.val(dates.start).prop('readonly', true);
                    }

                    if (index > 0) {
                        // Other phases can start on their own date.
                        // This supports cases where multiple phases run in parallel or overlap.
                        $start.prop('readonly', false);
                    
                        // Keep the date within project duration only
                        $start.attr('min', dates.start || '');
                        $start.attr('max', dates.end || '');
                    }

                    if (index === $rows.length - 1 && dates.end) {
                        $end.val(dates.end).prop('readonly', true);
                    } else {
                        $end.prop('readonly', false);
                    }
                });
            }

            $('#add_timeline').click(function () {
                i++;
                let newRow = `
                    <tr id="row${i}">
                        <td><input type="text" name="project_timeline[${i}][title]" class="form-control" placeholder="Title" required></td>
                        <td><input type="text" name="project_timeline[${i}][description]" class="form-control" placeholder="Description" required></td>
                        <td><input type="number" name="project_timeline[${i}][value]" class="form-control timeline-value" placeholder="Value (RM)" step="0.01" min="0.01" required></td>
                        <td><input type="date" name="project_timeline[${i}][date_start]" class="form-control timeline-start" readonly required></td>
                        <td><input type="date" name="project_timeline[${i}][date_end]" class="form-control timeline-end" required></td>
                        <td class="text-center">
                            <button type="button" name="remove_timeline" id="${i}" class="btn btn-danger btn_remove_timeline">Remove</button>
                        </td>
                    </tr>`;
                $('#dynamic_field_timeline tbody').append(newRow);
                updateTimelineValueSummary();
                syncTimelineDates();
            });
    
            $(document).on('click', '.btn_remove_timeline', function () {
                $(this).closest('tr').remove();
                updateTimelineValueSummary();
                syncTimelineDates();
            });

            $(document).on('change', '.timeline-end, #project_start, #project_end', function () {
                syncTimelineDates();
            });

            $('#registered_project_value').on('input change', function () {
                updateTimelineValueSummary();
            });

            $(document).on('input change', '.timeline-value', function () {
                const totalValue = parseFloat($('#registered_project_value').val()) || 0;
                let runningTotal = 0;
                $('#dynamic_field_timeline tbody .timeline-value').each(function () {
                    runningTotal += parseFloat($(this).val()) || 0;
                });

                if (runningTotal > totalValue) {
                    $(this).val('');
                    Swal.fire({
                        title: 'Exceeded Project Value',
                        text: 'Total phase value cannot exceed Registered Project Value.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }

                updateTimelineValueSummary();
            });

            syncTimelineDates();
            updateTimelineValueSummary();
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector("#consultancyProjectForm");
            const submitButton = document.querySelector("button[name='btn_submit']");
            const saveButton = document.querySelector("button[name='btn_saveConsultancyProject']");
            const projectTypeSelect = document.getElementById('project_type');
            const projectTypeInfo = document.getElementById('project_type_info');
            const registeredValueInput = document.getElementById('registered_project_value');
            const leaderId = '<?php echo htmlspecialchars($leaderId, ENT_QUOTES); ?>';

            function updateProjectTypeInfo() {
                if (!projectTypeSelect || !projectTypeInfo) return;
            
                const selectedOption = projectTypeSelect.options[projectTypeSelect.selectedIndex];
                const info = selectedOption ? selectedOption.getAttribute('data-info') : '';
                const selectedText = selectedOption ? selectedOption.text.trim() : '';
                const infoBox = document.getElementById('project_type_info_box');
                const infoTitle = document.getElementById('project_type_title');
            
                if (info) {
                    projectTypeInfo.textContent = info;
                    if (infoTitle) {
                        infoTitle.textContent = selectedText + ':';
                    }
                    if (infoBox) {
                        infoBox.style.display = 'block';
                    }
                } else {
                    projectTypeInfo.textContent = 'Please select a project type to view its definition.';
                    if (infoTitle) {
                        infoTitle.textContent = 'Definition:';
                    }
                    if (infoBox) {
                        infoBox.style.display = 'none';
                    }
                }
            }

            function validateProjectValue() {
                if (!registeredValueInput) return true;
                const value = parseFloat(registeredValueInput.value);
                if (isNaN(value) || value < 1) {
                    registeredValueInput.classList.add('is-invalid');
                    return false;
                }
                registeredValueInput.classList.remove('is-invalid');
                return true;
            }

            function validateMembers() {
                let isValid = true;
                const selectedValues = [];
                document.querySelectorAll('.member-select').forEach((select) => {
                    const value = (select.value || '').trim();
                    select.classList.remove('is-invalid');
                    if (value && leaderId && value === leaderId) {
                        isValid = false;
                        select.classList.add('is-invalid');
                    }
                    if (value) {
                        if (selectedValues.includes(value)) {
                            isValid = false;
                            select.classList.add('is-invalid');
                        }
                        selectedValues.push(value);
                    }
                });
                return isValid;
            }

            function validateTimelineDates() {
                let isValid = true;
                const projectStart = document.getElementById('project_start')?.value || '';
                const projectEnd = document.getElementById('project_end')?.value || '';
                const rows = document.querySelectorAll('#dynamic_field_timeline tbody tr');
                rows.forEach((row, index) => {
                    const start = row.querySelector('.timeline-start');
                    const end = row.querySelector('.timeline-end');
                    start.classList.remove('is-invalid');
                    end.classList.remove('is-invalid');

                    if (!start.value || !end.value || (projectStart && start.value < projectStart) || (projectEnd && end.value > projectEnd) || start.value > end.value) {
                        isValid = false;
                        start.classList.add('is-invalid');
                        end.classList.add('is-invalid');
                    }

                    if (index === 0 && projectStart && start.value !== projectStart) {
                        isValid = false;
                        start.classList.add('is-invalid');
                    }

                    if (index === rows.length - 1 && projectEnd && end.value !== projectEnd) {
                        isValid = false;
                        end.classList.add('is-invalid');
                    }

                });
                return isValid;
            }

            function validateTimelineValues() {
                let isValid = true;
                const totalAllowed = parseFloat(document.getElementById('registered_project_value')?.value || '0') || 0;
                let totalTimeline = 0;
                document.querySelectorAll('.timeline-value').forEach((input) => {
                    input.classList.remove('is-invalid');
                    const value = parseFloat(input.value || '0') || 0;
                    if (value <= 0) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    }
                    totalTimeline += value;
                });

                if (totalTimeline > totalAllowed || Math.abs(totalTimeline - totalAllowed) > 0.009) {
                    isValid = false;
                    document.querySelectorAll('.timeline-value').forEach((input) => input.classList.add('is-invalid'));
                }
                return isValid;
            }

            function validateForm() {
                let isValid = true;
                const requiredFields = form.querySelectorAll("input[required], select[required], textarea[required]");
                requiredFields.forEach((field) => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add("is-invalid");
                    } else {
                        field.classList.remove("is-invalid");
                    }
                });
                if (!validateProjectValue() || !validateMembers() || !validateTimelineDates() || !validateTimelineValues()) {
                    isValid = false;
                }
                return isValid;
            }

            function showSavingLoading() {
                Swal.fire({
                    title: "Saving Project...",
                    text: "Please wait while your project is being saved.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                if (saveButton) {
                    saveButton.disabled = true;
                    saveButton.innerHTML = "Saving...";
                }
            }

            if (submitButton) {
                submitButton.addEventListener("click", function (event) {
                    if (!validateForm()) {
                        event.preventDefault();
                        Swal.fire({
                            title: "Incomplete Fields",
                            text: "Please complete all required fields before submitting!",
                            icon: "error",
                            confirmButtonText: "OK",
                        });
                    }
                });
            }
        
            if (saveButton) {
                saveButton.addEventListener("click", function () {
                    document.getElementById('action').value = 'save';
                    form.setAttribute("novalidate", "novalidate");
                });
            }
            
            if (form) {
                form.addEventListener("submit", function (event) {
                    const clickedButton = event.submitter || document.activeElement;
            
                    if (clickedButton && clickedButton.name === "btn_saveConsultancyProject") {
                        document.getElementById('action').value = 'save';
                        showSavingLoading();
                    }
                });
            }

            if (projectTypeSelect) {
                projectTypeSelect.addEventListener('change', updateProjectTypeInfo);
                updateProjectTypeInfo();
            }

            if (registeredValueInput) {
                registeredValueInput.addEventListener('input', validateProjectValue);
                validateProjectValue();
            }
        });
    </script>
    <?php if (isset($_GET['update']) && $_GET['update'] == 'save-success') { ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Project Saved',
            text: 'Your project has been successfully saved!',
            icon: 'success',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.replace('consultancy-project.php');
        });
    });
    </script>
    <?php } ?>
    
    <?php if (isset($_GET['update']) && $_GET['update'] == 'submit-success') { ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Project Submitted',
            text: 'Your project has been successfully submitted for verification!',
            icon: 'success',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.replace('consultancy-project.php');
        });
    });
    </script>
    <?php } ?>
    <script>
    $(document).ready(function() {
        function createMemberRow() {
            return `
                <tr>
                    <td style="width: 80%;">
                        <select class="select2 form-control custom-select member-select" name="project_members[]">
                            <?php echo $staffOptions; ?>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn_remove_member">Remove</button>
                    </td>
                </tr>`;
        }

        function initMemberSelect($select) {
            $select.select2();
        }

        $('#add_member').click(function () {
            let newRow = createMemberRow();
            $('#dynamic_field_members').append(newRow);
            initMemberSelect($('#dynamic_field_members .member-select').last());
        });
    
        $(document).on('click', '.btn_remove_member', function () {
            $(this).closest('tr').remove();
        });
    });
    </script>
</body>

</html>
