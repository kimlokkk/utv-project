<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';

    date_default_timezone_set('Asia/Kuala_Lumpur');

    $userData = $_SESSION['user_data'] ?? [];

    $id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';

    if (empty($id)) {
        header("Location: consultancy-project-list.php?update=invalid-id");
        exit();
    }

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
    $project_owned = '';

    $query = "SELECT * FROM project WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($db, $query);

    if ($result && mysqli_num_rows($result) > 0) {
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
            $project_owned                  = $row['project_owned'];
        }
    } else {
        header("Location: consultancy-project-list.php?update=invalid-id");
        exit();
    }

    // Ledger rule:
    // Selagi project belum Approved/Appointed, ledger tidak boleh dilihat.
    // Project Ledger tab/fetching disabled.
    $isLedgerAllowed = false;

    // Button flow:
    // Belum approve: Appoint disabled.
    // Dah approve: Approve disabled, Appoint enabled.
    $canApprove = ($project_status === 'Pending Approval');
    $canReturn = ($project_status === 'Pending Approval');
    $canAppoint = ($project_status === 'Approved');

    $tracking_query = "SELECT * FROM project_tracker WHERE project_id = '$id' ORDER BY id DESC";
    $tracking_result = mysqli_query($db, $tracking_query);
    $tracking_data = [];
    while ($track_row = mysqli_fetch_array($tracking_result)) {
        $tracking_data[] = $track_row;
    }

    $members_query = "SELECT * FROM project_members WHERE project_id = '$id'";
    $members_result = mysqli_query($db, $members_query);
    $members_data = [];
    while ($members_row = mysqli_fetch_array($members_result)) {
        $members_data[] = $members_row;
    }

    $alertTitle = '';
    $alertText = '';
    $alertIcon = '';
    $alertRedirect = '';

    if (isset($_GET['update'])) {
        $updateStatus = $_GET['update'];

        if ($updateStatus == 'save-success') {
            $alertTitle = 'Project Verified';
            $alertText = 'Project has been successfully verified!';
            $alertIcon = 'success';
            $alertRedirect = 'index.php';
        } elseif ($updateStatus == 'approve-success') {
            $alertTitle = 'Project Approved';
            $alertText = 'Project has been successfully approved!';
            $alertIcon = 'success';
            $alertRedirect = 'consultancy-project-info.php?id=' . urlencode($id);
        } elseif ($updateStatus == 'appoint-success') {
            $alertTitle = 'Project Appointed';
            $alertText = 'Project has been successfully appointed!';
            $alertIcon = 'success';
            $alertRedirect = 'consultancy-project-info.php?id=' . urlencode($id);
        } elseif ($updateStatus == 'update-success') {
            $alertTitle = 'Project Owned Updated';
            $alertText = 'Project ownership has been successfully updated!';
            $alertIcon = 'success';
            $alertRedirect = 'consultancy-project-info.php?id=' . urlencode($id);
        } elseif ($updateStatus == 'return-success') {
            $alertTitle = 'Project Returned';
            $alertText = 'Project has been successfully returned!';
            $alertIcon = 'success';
            $alertRedirect = 'consultancy-project-info.php?id=' . urlencode($id);
        } elseif ($updateStatus == 'approve-fail' || $updateStatus == 'appoint-fail' || $updateStatus == 'save-fail' || $updateStatus == 'return-fail') {
            $alertTitle = 'Error';
            $alertText = 'Failed to process your project. Please try again.';
            $alertIcon = 'error';
            $alertRedirect = '';
        } elseif ($updateStatus == 'invalid-id') {
            $alertTitle = 'Invalid Project';
            $alertText = 'The project ID provided is invalid.';
            $alertIcon = 'warning';
            $alertRedirect = '';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="dist/css/pages/tab-page.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<style>
.center222 {
  text-align: center;
}
</style>
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
                        <h4 class="text-themecolor">Project Info</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Project Info</li>
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
                            <h3 class="card-header bg-success text-white">Project Info</h3>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body p-t-0"></div>
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs customtab" role="tablist">
                                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#project-details" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Details</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#file-upload" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project-Related File Upload</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#client-details" role="tab"><span class="hidden-sm-up"></i></span> <span class="hidden-xs-down">Client Details</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-members" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Members</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-timeline" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Project Timeline</span></a> </li>
                                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#project-tracking" role="tab"><span class="hidden-sm-up"></i></span> <span class="hidden-xs-down">Project Tracking</span></a> </li>
                                                <!-- Project Ledger tab disabled -->
                                            </ul>
                                            <!-- Tab panes -->
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="project-details" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Project Title</td>
                                                                                <td><?php echo !empty($project_title) ? htmlspecialchars($project_title) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project No</td>
                                                                                <td><?php echo !empty($project_no) ? htmlspecialchars($project_no) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Type of Project</td>
                                                                                <td><?php echo !empty($project_type) ? htmlspecialchars($project_type) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Start</td>
                                                                                <td><?php echo !empty($project_start) ? date("d F Y", strtotime($project_start)) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project End</td>
                                                                                <td><?php echo !empty($project_start) ? date("d F Y", strtotime($project_end)) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Registered Project Value (RM)</td>
                                                                                <td><?php echo !empty($registered_project_value) ? htmlspecialchars($registered_project_value) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Quotation Ref No.</td>
                                                                                <td><?php echo !empty($quotation_ref_no) ? htmlspecialchars($quotation_ref_no) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Owned</td>
                                                                                <td><?php echo !empty($project_owned) ? htmlspecialchars($project_owned) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="project-members" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table id="members" class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Name</th>
                                                                                <th>IC Number</th>
                                                                                <!--<th class="text-center">Action</th>-->
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            // Query to fetch project members
                                                                            $query = "
                                                                                SELECT pmc.*, us.*
                                                                                FROM project_members_consultant pmc
                                                                                INNER JOIN uitm_staff us ON pmc.member_id = us.id
                                                                                WHERE pmc.project_id = '$id'
                                                                                ORDER BY pmc.project_no ASC
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
                                                                                /*$project_source = $row['project_source'];
                                                                                $project_no = $row['project_no'];
                                                                                $project_title = $row['project_title'];
                                                                                $member_name = $row['member_name'];
                                                                                $member_ic = $row['member_ic'];
                                                                                $start_date = $row['start_date'];
                                                                                $duration = $row['duration'];
                                                                                $status = $row['status'];
                                                                                $payment_type = $row['payment_type'];
                                                                                $budget = $row['budget'];
                                                                                $project_leader = $row['project_leader'];
                                                                                $project_id = $row['id']; // Assuming 'id' is the primary key for the project*/
                                                                                
                                                                                $member_name = $row['full_name'];
                                                                                $member_ic = $row['ic'];
                                                                            
                                                                                // Determine the URL based on the project source
                                                                                $info_page = ($project_source === 'Consultancy') 
                                                                                             ? 'consultancy-project-info.php' 
                                                                                             : 'training-project-info.php';
                                                                            ?>
                                                                                <td><?php echo htmlspecialchars($member_name); ?></td>
                                                                                <td><?php echo htmlspecialchars($member_ic); ?></td>
                                                                                <!--<td class="text-center">
                                                                                    <a href="<?php //echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>" 
                                                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                                                       title="Full Info">
                                                                                       Full Info
                                                                                    </a>
                                                                                </td>-->
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
                                                </div>
                                                <div class="tab-pane p-20" id="project-timeline" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <?php
                                                                // Query to fetch project timeline
                                                                $query = "
                                                                    SELECT *
                                                                    FROM project_timeline
                                                                    WHERE project_id = '$id'
                                                                    ORDER BY id DESC
                                                                ";
                                                                
                                                                $result = mysqli_query($db, $query);
                                                                
                                                                // Check for errors in the query execution
                                                                if (!$result) {
                                                                    // Log or display the MySQL error
                                                                    error_log("MySQL Query Error: " . mysqli_error($db));
                                                                    echo "<p>Error fetching project timeline: " . mysqli_error($db) . "</p>";
                                                                    exit;
                                                                }
                                                                
                                                                // Check if any data exists
                                                                if (mysqli_num_rows($result) > 0) {
                                                                ?>
                                                                <div class="table-responsive">
                                                                    <table id="timeline" class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Title</th>
                                                                                <th>Description</th>
                                                                                <th>Value</th>
                                                                                <th>Date of timeline</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            while ($row = mysqli_fetch_array($result)) {
                                                                                $title = $row['title'];
                                                                                $description = $row['description'];
                                                                                $value = $row['value'];
                                                                                $date_timeline = $row['date_timeline'];
                                                                            ?>
                                                                                <tr>
                                                                                    <td><?php echo htmlspecialchars($title); ?></td>
                                                                                    <td><?php echo htmlspecialchars($description); ?></td>
                                                                                    <td>RM <?php echo htmlspecialchars($value); ?></td>
                                                                                    <td><?php echo date("d F Y", strtotime($date_timeline)); ?></td>
                                                                                </tr>
                                                                            <?php 
                                                                            }
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <?php 
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="file-upload" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Appointment/Offer Letter</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($appointment_letter) ? "<a href=\"project-documents/consultancy-project/appointment-letter/" . htmlspecialchars($appointment_letter) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PTJ Approval</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($approval_external_work) ? "<a href=\"project-documents/consultancy-project/approval-external-work-letter/" . htmlspecialchars($approval_external_work) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Quotation Document</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($quotation_doc) ? "<a href=\"project-documents/consultancy-project/quotation/" . htmlspecialchars($quotation_doc) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Agreement/MoA</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($agreement_doc) ? "<a href=\"project-documents/consultancy-project/agreement-MoA/" . htmlspecialchars($agreement_doc) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Project Proposal & Budget</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($project_proposal) ? "<a href=\"project-documents/consultancy-project/project-proposal/" . htmlspecialchars($project_proposal) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 1</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($other_doc_1) ? "<a href=\"project-documents/consultancy-project/other-docs/" . htmlspecialchars($other_doc_1) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Other Related Document 2</td>
                                                                                <td class="text-center">
                                                                                    <?php echo !empty($other_doc_2) ? "<a href=\"project-documents/consultancy-project/other-docs/" . htmlspecialchars($other_doc_2) . "\" class=\"btn waves-effect waves-light btn-info assign-button\" target=\"_blank\">View</a>" : "No data available yet"; ?>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="client-details" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width: 30%;">Client's Company Name</td>
                                                                                <td><?php echo !empty($client_company_name) ? htmlspecialchars($client_company_name) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Full Address</td>
                                                                                <td><?php echo !empty($client_address) ? htmlspecialchars($client_address) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Contact Number</td>
                                                                                <td><?php echo !empty($client_contact) ? htmlspecialchars($client_contact) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Business Type</td>
                                                                                <td><?php echo !empty($client_business_type) ? htmlspecialchars($client_business_type) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC</td>
                                                                                <td><?php echo !empty($client_pic) ? htmlspecialchars($client_pic) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Email Address</td>
                                                                                <td><?php echo !empty($client_pic_email) ? htmlspecialchars($client_pic_email) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>PIC Contact Number</td>
                                                                                <td><?php echo !empty($client_pic_contact) ? htmlspecialchars($client_pic_contact) : "No data available yet"; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="project-tracking" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h4 class="card-header">File Number : <?php echo htmlspecialchars($project_no); ?></h4>
                                                                <div class="table-responsive">
                                                                    <table class="table color-bordered-table info-bordered-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Remarks</th>
                                                                                <th>Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php if (!empty($tracking_data)) : ?>
                                                                                <?php foreach ($tracking_data as $track) : ?>
                                                                                    <tr>
                                                                                        <td><?php echo htmlspecialchars($track['remark']); ?></td>
                                                                                        <td><?php echo date("d F Y", strtotime($track['date'])); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            <?php else : ?>
                                                                                <tr>
                                                                                    <td colspan="2" class="text-center">No tracking data available yet</td>
                                                                                </tr>
                                                                            <?php endif; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane p-20" id="project-ledger" role="tabpanel">
                                                    <div class="p-20">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <?php if (!$isLedgerAllowed) { ?>
                                                                    <div class="alert alert-warning mb-0">
                                                                        <h5 class="mb-2">
                                                                            <i class="fa fa-lock"></i> Project Ledger Locked
                                                                        </h5>
                                                                        <p class="mb-1">
                                                                            Project ledger is only available after this project has been approved.
                                                                        </p>
                                                                        <small>
                                                                            Current status:
                                                                            <strong><?php echo !empty($project_status) ? htmlspecialchars($project_status) : 'No status available'; ?></strong>
                                                                        </small>
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <div class="table-responsive">
                                                                        <table id="ledgerTable"
                                                                            class="display nowrap table table-hover table-striped table-bordered"
                                                                            cellspacing="0" width="100%">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>No</th>
                                                                                    <th>Date</th>
                                                                                    <th>Description</th>
                                                                                    <th>Debit (DR)</th>
                                                                                    <th>Credit (CR)</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="ledgerData"></tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    // Button flow:
                    // - Pending Approval: Approve and Return enabled, Appoint disabled.
                    // - Approved: Appoint enabled, Approve and Return disabled.
                    // - Appointed: all action buttons disabled.
                ?>
                <div class="row">
                    <div class="m-b-20 col-md-12">
                        <button type="button"
                                id="btnUpdate"
                                class="btn btn-lg btn-warning">
                            Update Project Owned
                        </button>
                        <a href="consultancy-project-edit.php?id=<?php echo urlencode($id); ?>" 
                           class="btn btn-lg btn-info" 
                           title="Edit Project">
                           Edit Project
                        </a>
                        <button type="button"
                                id="btnApprove"
                                class="btn btn-lg btn-success"
                                <?php echo !$canApprove ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Approve
                        </button>
                        <button type="button"
                                id="btnAppoint"
                                class="btn btn-lg btn-info"
                                <?php echo !$canAppoint ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Appoint
                        </button>
                        <button type="button" 
                                id="btnReturn" 
                                class="btn btn-lg btn-danger"
                                <?php echo !$canReturn ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            Return
                        </button>
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
    <script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
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
            if ($('#ledgerTable').length) {
                $('#ledgerTable').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ]
                });
                $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary mr-1');
            }
        });
    </script>
    <script>
        $(function () {
            $('#members').DataTable();
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
            $('#timeline').DataTable();
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
        function showActionLoading(title, text) {
            Swal.fire({
                title: title,
                text: text,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    </script>

    <?php if (!empty($alertTitle)) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: <?php echo json_encode($alertTitle); ?>,
                text: <?php echo json_encode($alertText); ?>,
                icon: <?php echo json_encode($alertIcon); ?>,
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                <?php if (!empty($alertRedirect)) { ?>
                    window.location.replace(<?php echo json_encode($alertRedirect); ?>);
                <?php } ?>
            });
        });
    </script>
    <?php } ?>

    <script>
        const projectId = <?php echo json_encode($id); ?>;

        const btnReturn = document.getElementById('btnReturn');
        if (btnReturn) {
            btnReturn.addEventListener('click', function () {
                if (btnReturn.disabled) return;

                Swal.fire({
                    title: 'Return Project',
                    html: `
                        <label for="returnLevel">Return this project to?</label>
                        <select id="returnLevel" class="swal2-input">
                            <option value="" disabled selected>Select return level</option>
                            <option value="Level 3">CST Level 3</option>
                            <option value="Consultant">Consultant</option>
                        </select>
                        <textarea id="returnRemark" class="swal2-textarea" placeholder="Enter your remarks here..."></textarea>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Return Project',
                    cancelButtonText: 'Cancel',
                    focusConfirm: false,
                    preConfirm: () => {
                        const level = document.getElementById('returnLevel').value;
                        const remark = document.getElementById('returnRemark').value.trim();

                        if (!level || !remark) {
                            Swal.showValidationMessage('Please select return level and enter remark.');
                            return false;
                        }

                        return { level, remark };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id', projectId);
                        formData.append('remark', result.value.remark);
                        formData.append('return_to', result.value.level);

                        btnReturn.disabled = true;
                        btnReturn.innerHTML = 'Returning...';

                        showActionLoading('Returning Project...', 'Please wait while this project is being returned.');

                        fetch('consultancy-project-return.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(() => {
                            window.location.href = 'consultancy-project-info.php?update=return-success&id=' + encodeURIComponent(projectId);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error',
                                text: 'An unexpected error occurred. Please try again later.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                btnReturn.disabled = false;
                                btnReturn.innerHTML = 'Return';
                            });
                        });
                    }
                });
            });
        }
    </script>

    <script>
        const btnApprove = document.getElementById('btnApprove');
        if (btnApprove) {
            btnApprove.addEventListener('click', function (e) {
                e.preventDefault();
                if (btnApprove.disabled) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to approve this project?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2fb344',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btnApprove.disabled = true;
                        btnApprove.innerHTML = 'Approving...';

                        showActionLoading('Approving Project...', 'Please wait while this project is being approved.');
                        window.location.href = 'consultancy-project-approve.php?id=' + encodeURIComponent(projectId);
                    }
                });
            });
        }
    </script>

    <script>
        const btnAppoint = document.getElementById('btnAppoint');
        if (btnAppoint) {
            btnAppoint.addEventListener('click', function (e) {
                e.preventDefault();
                if (btnAppoint.disabled) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to appoint this project?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#03a9f3',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, appoint it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btnAppoint.disabled = true;
                        btnAppoint.innerHTML = 'Appointing...';

                        showActionLoading('Appointing Project...', 'Please wait while this project is being appointed.');
                        window.location.href = 'consultancy-project-appoint.php?id=' + encodeURIComponent(projectId);
                    }
                });
            });
        }
    </script>

    <script>
        const btnUpdate = document.getElementById('btnUpdate');
        if (btnUpdate) {
            btnUpdate.addEventListener('click', function (e) {
                e.preventDefault();
            
                Swal.fire({
                    title: 'Update Project',
                    html: `
                        <label for="projectOwned">This Project Belongs To?</label>
                        <select id="projectOwned" class="swal2-input">
                            <option value="Consultant">Consultant</option>
                            <option value="UTV">UTV</option>
                        </select>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#03a9f3',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Cancel',
                    focusConfirm: false,
                    preConfirm: () => {
                        const selectedValue = document.getElementById('projectOwned').value;
                        if (!selectedValue) {
                            Swal.showValidationMessage('Please select an option.');
                            return false;
                        }
                        return selectedValue;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const projectOwned = result.value;

                        btnUpdate.disabled = true;
                        btnUpdate.innerHTML = 'Updating...';

                        showActionLoading('Updating Project...', 'Please wait while project ownership is being updated.');
                        window.location.href = 'consultancy-project-update.php?id=' + encodeURIComponent(projectId) + '&project_owned=' + encodeURIComponent(projectOwned);
                    }
                });
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            var projectId = <?php echo json_encode($id); ?>; // Ambil project_id dari PHP
            var isLedgerAllowed = <?php echo $isLedgerAllowed ? 'true' : 'false'; ?>;

            // ✅ Ledger hanya boleh dilihat selepas project Approved/Appointed
            if (!isLedgerAllowed || !$('#ledgerTable').length) {
                return;
            }

            // ✅ Initialize DataTable SEKALI SAHAJA
            var table = $('#ledgerTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                destroy: true, // ✅ Prevent error "Cannot reinitialize DataTable"
                ordering: false, // ✅ Disable sorting untuk total row
                columns: [
                    { data: "no", title: "No." },
                    { data: "transaction_date", title: "Transaction Date" },
                    { data: "transaction_desc", title: "Description" },
                    { data: "debit", title: "Debit (DR)", className: "text-right" },
                    { data: "credit", title: "Credit (CR)", className: "text-right" }
                ]
            });

            // ✅ Jika project ID ada, auto-fetch ledger data
            // Project Ledger fetching disabled.

            function fetchLedgerData(projectId) {
                return;
                $.ajax({
                    url: '',
                    method: 'GET',
                    data: { project_id: projectId },
                    dataType: 'json',
                    success: function(response) {
                        table.clear().rows.add(response.data).draw(); // ✅ Update DataTable dengan data baru
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        Swal.fire("Error", "Failed to fetch ledger data.", "error");
                    }
                });
            }
        });
    </script>
</body>

</html>
