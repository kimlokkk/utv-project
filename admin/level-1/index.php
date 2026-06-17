<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../../db_connect/db_connect.php';
?>
<?php
    // Query to count all new projects (Consultancy + Training) with status 'Pending Submission'
    $new_projects_query = "
        SELECT COUNT(*) AS total_new_projects
        FROM project
    ";
    $new_projects_result = mysqli_query($db, $new_projects_query);
    $new_projects_count = mysqli_fetch_assoc($new_projects_result)['total_new_projects'];
    
    // Query to count Research Assistants with status 'Pending Verification'
    $pending_ra_query = "SELECT COUNT(*) AS pending_ra FROM research_assistant";
    $pending_ra_result = mysqli_query($db, $pending_ra_query);
    $pending_ra_count = ($pending_ra_result) ? mysqli_fetch_assoc($pending_ra_result)['pending_ra'] : 0;
    
    // Query to count members with status 'Pending Verification' from both tables
    $pending_members_query = "
        SELECT  COUNT(*) AS total_pending_members FROM project_members
    ";
    $pending_members_result = mysqli_query($db, $pending_members_query);
    $pending_members_count = mysqli_fetch_assoc($pending_members_result)['total_pending_members'];
    
     // Query to count invoice with status 'submitted' from both tables
    $pending_invoice_query = "
        SELECT COUNT(*) AS total_invoice_submitted FROM invoices
    ";
    $pending_invoice_result = mysqli_query($db, $pending_invoice_query);
    $pending_invoice_count = mysqli_fetch_assoc($pending_invoice_result)['total_invoice_submitted'];
    
    // Query to count procurement with status 'submitted' from both tables
    $pending_procurement_query = "
        SELECT  COUNT(*) AS total_procurement_submitted FROM procurement
    ";
    $pending_procurement_result = mysqli_query($db, $pending_procurement_query);
    $pending_procurement_count = mysqli_fetch_assoc($pending_procurement_result)['total_procurement_submitted'];
    
    // Query to count professional fee with status 'submitted' from both tables
    $pending_professional_query = "
        SELECT  COUNT(*) AS total_professional_submitted FROM professional_fee_applications
    ";
    $pending_professional_result = mysqli_query($db, $pending_professional_query);
    $pending_professional_count = mysqli_fetch_assoc($pending_professional_result)['total_professional_submitted'];
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_reconciliation_query = "
        SELECT  COUNT(*) AS total_reconciliation_submitted FROM reconciliation_claim_applications
    ";
    $pending_reconciliation_result = mysqli_query($db, $pending_reconciliation_query);
    $pending_reconciliation_count = mysqli_fetch_assoc($pending_reconciliation_result)['total_reconciliation_submitted'];
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_allowance_query = "
        SELECT  COUNT(*) AS total_allowance_submitted FROM allowance_applications
    ";
    $pending_allowance_result = mysqli_query($db, $pending_allowance_query);
    $pending_allowance_count = mysqli_fetch_assoc($pending_allowance_result)['total_allowance_submitted'];
    
    // Query to count project funding with status 'submitted' from both tables
    $pending_project_funding_query = "
        SELECT  COUNT(*) AS total_project_funding_submitted FROM project_funding_assistance_applications
    ";
    $pending_project_funding_result = mysqli_query($db, $pending_project_funding_query);
    $pending_project_funding_count = mysqli_fetch_assoc($pending_project_funding_result)['total_project_funding_submitted'];
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
                        <h4 class="text-themecolor">Dashboard</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
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
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-success"><i class="ti-briefcase"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $new_projects_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Total Project Registration</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-success"><i class="ti-user"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_members_count; ?></h3>
                                        <h5 class="text-muted m-b-0">RA Application</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-success"><i class="ti-user"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_ra_count; ?></h3>
                                        <h5 class="text-muted m-b-0">RA Registration</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-danger"><i class="ti-receipt"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_invoice_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Invoice Application</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-danger"><i class="ti-truck"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_procurement_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Procurement Application</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-danger"><i class="ti-link"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_professional_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Professional Fee Application</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-danger"><i class="ti-reload"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_reconciliation_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Advance & Reconciliation/Claim</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-danger"><i class="ti-money"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_allowance_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Allowance & Wages Application</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block">
                                    <div class="round align-self-center round-danger"><i class="ti-blackboard"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0"><?php echo $pending_project_funding_count; ?></h3>
                                        <h5 class="text-muted m-b-0">Project Funding Application</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Registration</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="project-registration-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Leader</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Full Info</th>
                                                <!--<th class="text-center">Action</th>-->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Get user ID
                                                $user_id = $userData['id'];
                            
                                                // Query to fetch projects from both tables
                                                $query = "
                                                    SELECT *
                                                    FROM project
                                                    WHERE project_status NOT LIKE '%project leader%' AND project_status NOT LIKE '%Pending Submission%'
                                                    ORDER BY project_no ASC
                                                ";
                            
                                                $result = mysqli_query($db, $query);
                                                $counter = 1; 
                            
                                                // Fetch and display each project
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $project_no = $row['project_no'];
                                                    $project_title = $row['project_title'];
                                                    $project_leader = $row['project_leader'];
                                                    $project_type = $row['project_type'];
                                                    $project_status = $row['project_status'];
                                                    $project_id = $row['id']; // Assuming 'id' is the primary key for the project
                                                    $project_source = $row['project_source'];
                                                    
                                                    // Determine the URL based on the project source
                                                    $info_page = ($project_source === 'Consultancy') 
                                                                 ? 'consultancy-project-info.php' 
                                                                 : 'training-project-info.php';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_leader); ?></td>
                                                <td><?php echo htmlspecialchars($project_type); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($project_status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($project_status, 'Rejected') !== false || stripos($project_status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($project_status, 'Pending approval') !== false || stripos($project_status, 'Pending Verification') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($project_status, 'Approved') !== false || stripos($project_status, 'Appointed') !== false) {
                                                        $statusClass = 'badge-success';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>" 
                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                       title="Full Info">
                                                       Full Info
                                                    </a>
                                                </td>
                                                <!--<td class="text-center">
                                                    <button type="button" class="btn btn-info btn-circle" title="Verify"><i class="fa fa-search"></i> </button>
                                                    <button type="button" class="btn btn-success btn-circle" title="Approve"><i class="fa fa-check"></i> </button>
                                                    <button type="button" class="btn btn-danger btn-circle" title="Reject"><i class="fa fa-times"></i> </button>
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-info assign-button" title="Verify">Verify</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-danger assign-button" title="Reject">Reject</a>
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
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">RA/RO Registration</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="ra-registration-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>IC Number</th>
                                                <th>Date Register</th>
                                                <th>Status</th>
                                                <th class="text-center">Full Info</th>
                                                <!--<th class="text-center">Action</th>-->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Query to fetch projects from both tables
                                                $query = "
                                                    SELECT *
                                                    FROM research_assistant 
                                                ";
                            
                                                $result = mysqli_query($db, $query);
                                                $counter = 1; 
                            
                                                // Fetch and display each project
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $id = $row['id'];
                                                    $full_name = $row['full_name'];
                                                    $ic = $row['ic'];
                                                    $date_register = $row['date_register'];
                                                    $status = $row['status'];
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($full_name); ?></td>
                                                <td><?php echo htmlspecialchars($ic); ?></td>
                                                <td><?php echo date("d F Y", strtotime($date_register)); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($status, 'Pending approval') !== false || stripos($status, 'Pending Verification') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($status, 'Approved') !== false) {
                                                        $statusClass = 'badge-success';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="ra-info.php?id=<?php echo urlencode($id); ?>" 
                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                       title="Full Info">
                                                       Full Info
                                                    </a>
                                                </td>
                                                <!--<td class="text-center">
                                                    <button type="button" class="btn btn-info btn-circle" title="Verify"><i class="fa fa-search"></i> </button>
                                                    <button type="button" class="btn btn-success btn-circle" title="Approve"><i class="fa fa-check"></i> </button>
                                                    <button type="button" class="btn btn-danger btn-circle" title="Reject"><i class="fa fa-times"></i> </button>
                                                    <!--<a href="" class="btn waves-effect waves-light btn-sm btn-info assign-button" title="Verify">Verify</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-sm btn-danger assign-button" title="Reject">Reject</a>
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
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">RA/RO Appointment</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="ra-appointment-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>RA Name</th>
                                                <th>IC</th>
                                                <th>Project Title</th>
                                                <th>Project No</th>
                                                <th>Status</th>
                                                <th class="text-center">Details</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $query = "
                                                    SELECT 
                                                        raa.id AS application_id,
                                                        ra.id,
                                                        ra.full_name,
                                                        ra.ic,
                                                        p.project_title,
                                                        p.project_no,
                                                        raa.status,
                                                        raa.return_to
                                                    FROM 
                                                        project p
                                                    JOIN 
                                                        research_assistant_application raa ON raa.project_id = p.id
                                                    JOIN 
                                                        research_assistant ra ON ra.id = raa.ra_id
                                                    ORDER BY ra.full_name ASC
                                                ";
                                                $result = mysqli_query($db, $query);
                                                $counter = 1;
                                    
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $raa_id         = $row['application_id'];
                                                        $full_name     = $row['full_name'];
                                                        $ic            = $row['ic'];
                                                        $project_title = $row['project_title'];
                                                        $project_no    = $row['project_no'];
                                                        $status = $row['status'];
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($full_name); ?></td>
                                                <td><?php echo htmlspecialchars($ic); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td>
                                                    <?php
                                                    $statusText = htmlspecialchars($status);
                                                    $statusClass = 'badge-secondary'; // default
                                                
                                                    if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
                                                        $statusClass = 'badge-danger';
                                                    } elseif (stripos($status, 'Pending Approval') !== false || stripos($status, 'Pending Verification') !== false || stripos($status, 'Pending Submission') !== false) {
                                                        $statusClass = 'badge-warning';
                                                    } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
                                                        $statusClass = 'badge-success';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="javascript:void(0);" 
                                                       class="btn btn-info btn-sm viewDetails" 
                                                       data-raa-id="<?php echo urlencode($raa_id); ?>">
                                                       View Details
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $isRejected = stripos($status, 'Rejected') !== false;
                                                    $isPendingApproval = stripos($status, 'Pending Approval') !== false || stripos($status, 'Approved') !== false;
                                                    $disableButtons = $isRejected || $isPendingApproval;
                                                    ?>
                                                    <a 
                                                        href="ra-application-edit.php?id=<?php echo urlencode($raa_id); ?>" 
                                                        class="btn btn-info btn-sm">
                                                        Edit
                                                    </a>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        id="verifyApplication"
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Verify
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        id="rejectApplication"
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Reject
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php 
                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Invoice Application</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="project-invoice-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Invoice</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>CC24081415001</td>
                                                <td>Abcd Project</td>
                                                <td>Contract Research</td>
                                                <td>Pending Submission</td>
                                                <td class="text-center"><a href="" class="btn waves-effect waves-light btn-info assign-button" title="Invoice">Invoice</a></td>
                                                <td class="text-center">
                                                    <a href="" class="btn waves-effect waves-light btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-danger assign-button" title="Reject">Reject</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>TC24081415001</td>
                                                <td>Efgh Project</td>
                                                <td>Webinar</td>
                                                <td>Pending Submission</td>
                                                <td class="text-center"><a href="" class="btn waves-effect waves-light btn-info assign-button" title="Invoice">Invoice</a></td>
                                                <td class="text-center">
                                                    <a href="" class="btn waves-effect waves-light btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-danger assign-button" title="Reject">Reject</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <h3 class="card-header bg-success text-white">Project Financial Request</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="project-financial-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Project Info</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>CC24081415001</td>
                                                <td>Abcd Project</td>
                                                <td>Contract Research</td>
                                                <td>Pending Submission</td>
                                                <td class="text-center"><a href="project-info.php" class="btn waves-effect waves-light btn-info assign-button" title="Project Info">Project Info</a></td>
                                                <td class="text-center">
                                                    <a href="" class="btn waves-effect waves-light btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-danger assign-button" title="Reject">Reject</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>TC24081415001</td>
                                                <td>Efgh Project</td>
                                                <td>Webinar</td>
                                                <td>Pending Submission</td>
                                                <td class="text-center"><a href="project-info.php" class="btn waves-effect waves-light btn-info assign-button" title="Project Info">Project Info</a></td>
                                                <td class="text-center">
                                                    <a href="" class="btn waves-effect waves-light btn-success assign-button" title="Approve">Approve</a> | 
                                                    <a href="" class="btn waves-effect waves-light btn-danger assign-button" title="Reject">Reject</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->
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
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#ra-registration-table').DataTable();
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
            $('#ra-appointment-table').DataTable();
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
            $('#project-registration-table').DataTable();
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
            $('#project-financial-table').DataTable();
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
            $('#project-invoice-table').DataTable();
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
</body>

</html>