<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];

    // Define $staffOptions to be used later in the select box.
    $staffOptions = "<option disabled>Select Members</option>";
    $currentUserId = isset($userData['id']) ? $userData['id'] : 0;
    $staffQuery = "SELECT id, full_name, ic FROM uitm_staff WHERE id != '$currentUserId'";
    $staffResult = mysqli_query($db, $staffQuery);

    if ($staffResult) {
        while ($staffRow = mysqli_fetch_assoc($staffResult)) {
            $staffOptions .= "<option value='" . $staffRow['id'] . "'>" . $staffRow['full_name'] . " (ID: " . $staffRow['id'] . " - " . $staffRow['ic'] . ")</option>";
        }
    } else {
        $staffOptions .= "<option disabled>Error loading staff</option>";
    }
?>
<?php
    $id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';

    $leader_id = '';
    $project_leader = '';
    $project_no = '';
    $project_title = '';
    $project_type = '';
    $project_start = '';
    $project_end = '';
    $registered_project_value = '';
    $adjusted_project_value = '';
    $quotation_ref_no = '';
    $appointment_letter = '';
    $approval_external_work = '';
    $quotation_doc = '';
    $agreement_doc = '';
    $project_proposal = '';
    $other_doc_1 = '';
    $other_doc_2 = '';
    $client_company_name = '';
    $client_address = '';
    $client_contact = '';
    $client_business_type = '';
    $client_pic = '';
    $client_pic_email = '';
    $client_pic_contact = '';
    $date_create = '';
    $project_status = '';

    if ($id !== '') {
        $query = "SELECT * FROM project WHERE id = '$id' ";  
        $result = mysqli_query($db, $query);

        while($row = mysqli_fetch_array($result))  
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
    }
    
    $tracking_query = "SELECT * FROM project_tracker WHERE project_id = '$id' ORDER BY date DESC";
    $tracking_result = mysqli_query($db, $tracking_query);
    $tracking_data = [];
    if ($tracking_result) {
        while ($track_row = mysqli_fetch_array($tracking_result)) {
            $tracking_data[] = $track_row;
        }
    }

    $existing_members = [];
    $members_query = "
        SELECT pmc.member_id
        FROM project_members_consultant pmc
        WHERE pmc.project_id = '$id'
        ORDER BY pmc.project_no ASC
    ";
    $members_result = mysqli_query($db, $members_query);
    if ($members_result) {
        while ($members_row = mysqli_fetch_assoc($members_result)) {
            $existing_members[] = $members_row['member_id'];
        }
    }

    $existing_timeline = [];
    $timeline_query = "
        SELECT id, title, description, value, date_start, date_end
        FROM project_timeline
        WHERE project_id = '$id'
        ORDER BY id ASC
    ";
    $timeline_result = mysqli_query($db, $timeline_query);
    if ($timeline_result) {
        while ($timeline_row = mysqli_fetch_assoc($timeline_result)) {
            $existing_timeline[] = $timeline_row;
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
                        <h4 class="text-themecolor">Edit Consultancy Project</h4>
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
                        <form method="POST" enctype="multipart/form-data" id="editConsultancyProjectForm">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="leader_id" value="<?php echo htmlspecialchars($userData['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="project_leader" value="<?php echo htmlspecialchars($userData['full_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="leader_ic" value="<?php echo htmlspecialchars($userData['ic'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="project_no" value="<?php echo htmlspecialchars($project_no, ENT_QUOTES, 'UTF-8'); ?>">

                            <!-- IMPORTANT:
                                 This hidden input is used to trigger btn_UpdateConsultancyProject()
                                 even after the visible Save button is disabled during loading.
                            -->
                            <input type="hidden" name="btn_updateConsultancyProject" value="1">

                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Consultancy Project</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Title <span class="text-danger">*</span></label>
                                                    <input type="text" name="project_title" value="<?php echo htmlspecialchars($project_title, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Type of Project <span class="text-danger">*</span></label>
                                                    <div>
                                                        <select class="form-control" name="project_type" required>
                                                            <option value="<?php echo htmlspecialchars($project_type, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($project_type, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <option value="Contract Research">Contract Research</option>
                                                            <option value="Testing">Testing</option>
                                                            <option value="Evaluation">Evaluation</option>
                                                            <option value="Expert Panel">Expert Panel</option>
                                                            <option value="Professional Services">Professional Services</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project Start <span class="text-danger">*</span></label>
                                                    <input type="date" name="project_start" value="<?php echo htmlspecialchars($project_start, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Project End <span class="text-danger">*</span></label>
                                                    <input type="date" name="project_end" value="<?php echo htmlspecialchars($project_end, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Registered Project Value (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="registered_project_value" value="<?php echo htmlspecialchars($registered_project_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Adjusted Project Value (RM)</label>
                                                    <input type="number" name="adjusted_project_value" id="adjusted_project_value_top" value="<?php echo htmlspecialchars($adjusted_project_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Quotation Ref No. <span class="text-danger">*</span></label>
                                                    <input type="text" name="quotation_ref_no" value="<?php echo htmlspecialchars($quotation_ref_no, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h3 class="card-header bg-info text-white">Members</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Add Members (Consultant)</label>
                                                    <div class="table-responsive">  
                                                        <table class="table table-bordered" id="dynamic_field_members">
                                                            <?php if (!empty($existing_members)) { ?>
                                                                <?php foreach ($existing_members as $memberIndex => $memberId) { ?>
                                                                    <tr>
                                                                        <td style="width: 80%;">
                                                                            <select class="select2 form-control custom-select member-select" name="project_members[]">
                                                                                <option disabled>Select Members</option>
                                                                                <?php
                                                                                    $staffResultLoop = mysqli_query($db, $staffQuery);
                                                                                    if ($staffResultLoop) {
                                                                                        while ($staffRowLoop = mysqli_fetch_assoc($staffResultLoop)) {
                                                                                            $selected = ($staffRowLoop['id'] == $memberId) ? 'selected' : '';
                                                                                            echo "<option value='" . $staffRowLoop['id'] . "' $selected>" . $staffRowLoop['full_name'] . " (ID: " . $staffRowLoop['id'] . " - " . $staffRowLoop['ic'] . ")</option>";
                                                                                        }
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?php if ($memberIndex === 0) { ?>
                                                                                <button type="button" name="add_member" id="add_member" class="btn btn-info">Add More</button>
                                                                            <?php } else { ?>
                                                                                <button type="button" class="btn btn-danger btn_remove_member">Remove</button>
                                                                            <?php } ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                            <?php } else { ?>
                                                                <tr>
                                                                    <td style="width: 80%;">
                                                                        <select class="select2 form-control custom-select member-select" name="project_members[]">
                                                                            <option disabled selected>Select Members</option>
                                                                            <?php echo $staffOptions; ?>
                                                                        </select>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button type="button" name="add_member" id="add_member" class="btn btn-info">Add More</button>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </table>
                                                    </div>
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
                                                                    <th style="width: 20%;">Title</th>
                                                                    <th style="width: 35%;">Description</th>
                                                                    <th style="width: 15%;">Value (RM)</th>
                                                                    <th style="width: 10%;">Start Date</th>
                                                                    <th style="width: 10%;">End Date</th>
                                                                    <th style="width: 10%;">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($existing_timeline)) { ?>
                                                                    <?php foreach ($existing_timeline as $timelineIndex => $timeline) { $rowNo = $timelineIndex + 1; ?>
                                                                        <tr id="row<?php echo $rowNo; ?>">
                                                                            <td style="width: 20%;">
                                                                                <input type="hidden" name="project_timeline[<?php echo $rowNo; ?>][id]" value="<?php echo htmlspecialchars($timeline['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                                <input type="text" name="project_timeline[<?php echo $rowNo; ?>][title]" class="form-control" placeholder="Title" value="<?php echo htmlspecialchars($timeline['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </td>
                                                                            <td style="width: 35%;">
                                                                                <input type="text" name="project_timeline[<?php echo $rowNo; ?>][description]" class="form-control" placeholder="Description" value="<?php echo htmlspecialchars($timeline['description'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </td>
                                                                            <td style="width: 15%;">
                                                                                <input type="number" step="0.01" min="0" name="project_timeline[<?php echo $rowNo; ?>][value]" class="form-control timeline-value" placeholder="Value (RM)" value="<?php echo htmlspecialchars($timeline['value'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </td>
                                                                            <td style="width: 10%;">
                                                                                <input type="date" name="project_timeline[<?php echo $rowNo; ?>][date_start]" class="form-control timeline-start" value="<?php echo htmlspecialchars($timeline['date_start'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </td>
                                                                            <td style="width: 10%;">
                                                                                <input type="date" name="project_timeline[<?php echo $rowNo; ?>][date_end]" class="form-control timeline-end" value="<?php echo htmlspecialchars($timeline['date_end'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </td>
                                                                            <td class="text-center" style="width: 10%;">
                                                                                <?php if ($timelineIndex === 0) { ?>
                                                                                    <button type="button" name="add_timeline" id="add_timeline" class="btn btn-info">Add</button>
                                                                                <?php } else { ?>
                                                                                    <button type="button" name="remove_timeline" class="btn btn-danger btn_remove_timeline">Remove</button>
                                                                                <?php } ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                <?php } else { ?>
                                                                    <tr id="row1">
                                                                        <td style="width: 20%;">
                                                                            <input type="text" name="project_timeline[1][title]" class="form-control" placeholder="Title" required>
                                                                        </td>
                                                                        <td style="width: 35%;">
                                                                            <input type="text" name="project_timeline[1][description]" class="form-control" placeholder="Description" required>
                                                                        </td>
                                                                        <td style="width: 15%;">
                                                                            <input type="number" step="0.01" min="0" name="project_timeline[1][value]" class="form-control timeline-value" placeholder="Value (RM)" required>
                                                                        </td>
                                                                        <td style="width: 10%;">
                                                                            <input type="date" name="project_timeline[1][date_start]" class="form-control timeline-start" required>
                                                                        </td>
                                                                        <td style="width: 10%;">
                                                                            <input type="date" name="project_timeline[1][date_end]" class="form-control timeline-end" required>
                                                                        </td>
                                                                        <td class="text-center" style="width: 10%;">
                                                                            <button type="button" name="add_timeline" id="add_timeline" class="btn btn-info">Add</button>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="2" class="text-right"><strong>Total Timeline Value (RM)</strong></td>
                                                                    <td>
                                                                        <input type="number" step="0.01" id="adjusted_project_value_display" value="<?php echo htmlspecialchars($adjusted_project_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                                                                    </td>
                                                                    <td colspan="3"></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                        <small class="text-muted">User can set each phase value manually, but total phase value must not exceed Registered Project Value.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File Uploads -->                            <!-- File Uploads -->
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
                                                            <input type="file" name="appointment_letter" class="custom-file-input" id="inputGroupFile01" <?php echo empty($appointment_letter) ? 'required' : ''; ?>>
                                                            <label class="custom-file-label" for="inputGroupFile01">
                                                                <?php echo !empty($appointment_letter) ? 'Already Uploaded' : 'Choose File'; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($appointment_letter)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($appointment_letter, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>PTJ Approval<span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="approval_external_work" class="custom-file-input" id="inputGroupFile02" <?php echo empty($approval_external_work) ? 'required' : ''; ?>>
                                                            <label class="custom-file-label" for="inputGroupFile02">
                                                                <?php echo !empty($approval_external_work) ? 'Already Uploaded' : 'Choose File'; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($approval_external_work)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($approval_external_work, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Quotation Document <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="quotation_doc" class="custom-file-input" id="inputGroupFile03" <?php echo empty($quotation_doc) ? 'required' : ''; ?>>
                                                            <label class="custom-file-label" for="inputGroupFile03">
                                                                <?php echo !empty($quotation_doc) ? 'Already Uploaded' : 'Choose File'; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($quotation_doc)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($quotation_doc, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Agreement/MoA (If any)</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="agreement_doc" class="custom-file-input" id="inputGroupFile04">
                                                            <label class="custom-file-label" for="inputGroupFile04">
                                                                <?php echo !empty($agreement_doc) ? 'Already Uploaded' : 'Choose File'; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($agreement_doc)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($agreement_doc, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Project Proposal & Budget (If any)</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="project_proposal" class="custom-file-input" id="inputGroupFile05">
                                                            <label class="custom-file-label" for="inputGroupFile05">
                                                                <?php echo !empty($project_proposal) ? 'Already Uploaded' : 'Choose File'; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($project_proposal)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($project_proposal, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Other related document 1 (If any)</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="other_doc_1" class="custom-file-input" id="inputGroupFile06">
                                                            <label class="custom-file-label" for="inputGroupFile06"><?php echo !empty($other_doc_1) ? 'Already Uploaded' : 'Choose File'; ?></label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($other_doc_1)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($other_doc_1, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Other related document 2 (If any)</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="other_doc_2" class="custom-file-input" id="inputGroupFile07">
                                                            <label class="custom-file-label" for="inputGroupFile07"><?php echo !empty($other_doc_2) ? 'Already Uploaded' : 'Choose File'; ?></label>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($other_doc_2)) { ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($other_doc_2, ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Client Information -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Client Information</h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Client's Company Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_company_name" value="<?php echo htmlspecialchars($client_company_name, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Full Address <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_address" value="<?php echo htmlspecialchars($client_address, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Contact Number <span class="text-danger">*</span></label>
                                                    <input type="text" name="client_contact" value="<?php echo htmlspecialchars($client_contact, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Business Type <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="client_business_type" required>
                                                        <option value="<?php echo htmlspecialchars($client_business_type, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($client_business_type, ENT_QUOTES, 'UTF-8'); ?></option>
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
                                                    <input type="text" name="client_pic" value="<?php echo htmlspecialchars($client_pic, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" name="client_pic_email" value="<?php echo htmlspecialchars($client_pic_email, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Person in Charge Contact Number <span class="text-danger">*</span></label>
                                                    <input type="phone" name="client_pic_contact" value="<?php echo htmlspecialchars($client_pic_contact, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
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
                                    <button 
                                        type="submit"
                                        id="btn_updateConsultancyProject"
                                        class="btn btn-lg btn-info"
                                        formnovalidate
                                    >
                                        Save
                                    </button>
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
        <!-- End Page wrapper -->
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

    <?php if (isset($_GET['update']) && $_GET['update'] == 'save-success' && isset($_GET['id'])) { 
        $redirectId = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
    ?>
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
                window.location.replace('consultancy-project-info.php?id=<?php echo $redirectId; ?>');
            });
        });
    </script>
    <?php } ?>

    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <script>
    $(document).ready(function() {
        // Basic
        $('.dropify').dropify();

        // Translated
        $('.dropify-fr').dropify({
            messages: {
                default: 'Glissez-déposez un fichier ici ou cliquez',
                replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                remove: 'Supprimer',
                error: 'Désolé, le fichier trop volumineux'
            }
        });

        // Used events
        var drEvent = $('#input-file-events').dropify();

        drEvent.on('dropify.beforeClear', function(event, element) {
            return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
        });

        drEvent.on('dropify.afterClear', function(event, element) {
            alert('File deleted');
        });

        drEvent.on('dropify.errors', function(event, element) {
            console.log('Has Errors');
        });

        var drDestroy = $('#input-file-to-destroy').dropify();
        drDestroy = drDestroy.data('dropify')
        $('#toggleDropify').on('click', function(e) {
            e.preventDefault();
            if (drDestroy.isDropified()) {
                drDestroy.destroy();
            } else {
                drDestroy.init();
            }
        })
    });
    </script>
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
            let timelineRowIndex = $('#dynamic_field_timeline tbody tr').length || 1;
            const staffOptionsHtml = <?php echo json_encode($staffOptions); ?>;

            function initializeSelect2(scope) {
                $(scope).find('.select2').select2();
            }

            function updateTimelineTotal() {
                let total = 0;

                $('.timeline-value').each(function () {
                    const value = parseFloat($(this).val());

                    if (!isNaN(value)) {
                        total += value;
                    }
                });

                $('#adjusted_project_value_display').val(total.toFixed(2));
                $('#adjusted_project_value_top').val(total.toFixed(2));

                return total;
            }

            function syncTimelineDates() {
                const projectStart = $('input[name="project_start"]').val();
                const projectEnd = $('input[name="project_end"]').val();
                const rows = $('#dynamic_field_timeline tbody tr');
            
                rows.each(function (index) {
                    const startInput = $(this).find('.timeline-start');
                    const endInput = $(this).find('.timeline-end');
            
                    // Set date range based on main project dates
                    startInput.attr('min', projectStart || '');
                    startInput.attr('max', projectEnd || '');
                    endInput.attr('min', projectStart || '');
                    endInput.attr('max', projectEnd || '');
            
                    if (index === 0) {
                        // First phase follows Project Start
                        if (projectStart) {
                            startInput.val(projectStart);
                        }

                        startInput.prop('readonly', true);
                    } else {
                        // Other phases can be adjusted manually
                        // Useful for phases that run in parallel or have different timelines
                        startInput.prop('readonly', false);
                    }
            
                    if (index === rows.length - 1 && projectEnd) {
                        // Last phase follows Project End
                        endInput.val(projectEnd);
                        endInput.prop('readonly', true);
                    } else {
                        endInput.prop('readonly', false);
                    }
            
                    // End date cannot be before its own start date
                    if (startInput.val()) {
                        endInput.attr('min', startInput.val());
                    }
                });
            }

            function validateDuplicateMembers() {
                const selected = [];
                let hasDuplicate = false;

                $('.member-select').each(function () {
                    const value = $(this).val();

                    $(this).removeClass('is-invalid');

                    if (value) {
                        if (selected.includes(value)) {
                            hasDuplicate = true;
                            $(this).addClass('is-invalid');
                        } else {
                            selected.push(value);
                        }
                    }
                });

                return !hasDuplicate;
            }

            function validateTimelineValue(currentInput = null) {
                const registeredValue = parseFloat($('input[name="registered_project_value"]').val()) || 0;
                const total = updateTimelineTotal();

                if (registeredValue > 0 && total > registeredValue) {
                    if (currentInput) {
                        $(currentInput).val('');
                        updateTimelineTotal();
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Project value exceeded',
                        text: 'Total phase value cannot be more than Registered Project Value.'
                    });

                    return false;
                }

                return true;
            }

            function showSavingLoading() {
                Swal.fire({
                    title: 'Saving Project...',
                    text: 'Please wait while your project is being saved.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $('#btn_updateConsultancyProject').prop('disabled', true).html('Saving...');
            }

            function updateFileLabel(input) {
                const fileName = input.files && input.files.length > 0 ? input.files[0].name : '';
                const label = $(input).next('.custom-file-label');

                if (fileName && label.length) {
                    label.text(fileName);
                }
            }

            $(document).on('change', '.custom-file-input', function () {
                updateFileLabel(this);
            });

            $(document).on('click', '#add_member', function () {
                const newRow = `
                    <tr>
                        <td style="width: 80%;">
                            <select class="select2 form-control custom-select member-select" name="project_members[]">
                                <option disabled selected>Select Members</option>
                                ${staffOptionsHtml}
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn_remove_member">Remove</button>
                        </td>
                    </tr>`;

                $('#dynamic_field_members').append(newRow);
                initializeSelect2($('#dynamic_field_members tr').last());
            });

            $(document).on('click', '.btn_remove_member', function () {
                $(this).closest('tr').remove();
                validateDuplicateMembers();
            });

            $(document).on('change', '.member-select', function () {
                if (!validateDuplicateMembers()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate member',
                        text: 'The same member cannot be selected more than once.'
                    });
                }
            });

            $(document).on('click', '#add_timeline', function () {
                timelineRowIndex++;

                const newRow = `
                    <tr id="row${timelineRowIndex}">
                        <td style="width: 20%;">
                            <input type="text" name="project_timeline[${timelineRowIndex}][title]" class="form-control" placeholder="Title" required>
                        </td>
                        <td style="width: 35%;">
                            <input type="text" name="project_timeline[${timelineRowIndex}][description]" class="form-control" placeholder="Description" required>
                        </td>
                        <td style="width: 15%;">
                            <input type="number" step="0.01" min="0" name="project_timeline[${timelineRowIndex}][value]" class="form-control timeline-value" placeholder="Value (RM)" required>
                        </td>
                        <td style="width: 10%;">
                            <input type="date" name="project_timeline[${timelineRowIndex}][date_start]" class="form-control timeline-start" required>
                        </td>
                        <td style="width: 10%;">
                            <input type="date" name="project_timeline[${timelineRowIndex}][date_end]" class="form-control timeline-end" required>
                        </td>
                        <td class="text-center" style="width: 10%;">
                            <button type="button" class="btn btn-danger btn_remove_timeline">Remove</button>
                        </td>
                    </tr>`;

                $('#dynamic_field_timeline tbody').append(newRow);
                syncTimelineDates();
            });

            $(document).on('click', '.btn_remove_timeline', function () {
                $(this).closest('tr').remove();
                syncTimelineDates();
                updateTimelineTotal();
            });

            $(document).on('input', '.timeline-value', function () {
                validateTimelineValue(this);
            });

            $(document).on('change', '.timeline-start, .timeline-end', function () {
                syncTimelineDates();
            });

            $('input[name="project_start"], input[name="project_end"]').on('change', function () {
                syncTimelineDates();
            });

            $('input[name="registered_project_value"]').on('input', function () {
                validateTimelineValue();
            });

            $('#btn_updateConsultancyProject').on('click', function () {
                $('#editConsultancyProjectForm').attr('novalidate', 'novalidate');
            });

            $('#editConsultancyProjectForm').on('submit', function (e) {
                const registeredValue = parseFloat($('input[name="registered_project_value"]').val()) || 0;
                const total = updateTimelineTotal();

                if (registeredValue < 1) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid project value',
                        text: 'Registered Project Value must be at least RM 1.'
                    });

                    return false;
                }

                if (!validateDuplicateMembers()) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate member',
                        text: 'Please remove duplicate members before saving.'
                    });

                    return false;
                }

                if (total > registeredValue) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'Project value exceeded',
                        text: 'Total phase value cannot be more than Registered Project Value.'
                    });

                    return false;
                }

                showSavingLoading();
            });

            initializeSelect2(document);
            syncTimelineDates();
            updateTimelineTotal();
        });
    </script>
</body>

</html>
