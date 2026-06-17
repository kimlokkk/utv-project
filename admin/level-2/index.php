<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../../db_connect/db_connect.php';
?>
<?php
    function getCount($db, $query, $key) {
        $result = mysqli_query($db, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return isset($row[$key]) ? (int)$row[$key] : 0;
        }
        return 0;
    }

    function statusBadgeClass($status) {
        if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
            return 'badge-danger';
        } elseif (stripos($status, 'Pending Approval') !== false) {
            return 'badge-warning';
        } elseif (stripos($status, 'Pending Verification') !== false || stripos($status, 'Pending Submission') !== false) {
            return 'badge-info';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'badge-success';
        }
        return 'badge-secondary';
    }

    function actionNeededText($status) {
        if (stripos($status, 'Pending Approval') !== false) {
            return 'Approve or Return';
        } elseif (stripos($status, 'Returned') !== false) {
            return 'Returned for Correction';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'Completed';
        }
        return 'Review Status';
    }

    function actionBadgeClass($status) {
        if (stripos($status, 'Pending Approval') !== false) {
            return 'badge-warning';
        } elseif (stripos($status, 'Returned') !== false) {
            return 'badge-danger';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'badge-success';
        }
        return 'badge-secondary';
    }

    function safeEcho($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    // Query to count all new projects (Consultancy + Training) with status 'Pending Submission'
    $new_projects_query = "
        SELECT COUNT(*) AS total_new_projects
        FROM project
        WHERE project_status = 'Pending Approval'
    ";
    $new_projects_count = getCount($db, $new_projects_query, 'total_new_projects');
    
    // Query to count Research Assistants with status 'Pending Verification'
    $pending_ra_query = "SELECT COUNT(*) AS pending_ra FROM research_assistant WHERE status = 'Pending Approval'";
    $pending_ra_count = getCount($db, $pending_ra_query, 'pending_ra');
    
    // Query to count members with status 'Pending Verification' from both tables
    $pending_members_query = "
        SELECT  COUNT(*) AS total_pending_research_application FROM research_assistant_application WHERE status = 'Pending Approval'
    ";
    $pending_members_count = getCount($db, $pending_members_query, 'total_pending_research_application');
    
     // Query to count invoice with status 'submitted' from both tables
    $pending_invoice_query = "
        SELECT  COUNT(*) AS total_invoice_submitted FROM invoices WHERE invoice_status = 'Pending Approval'
    ";
    $pending_invoice_count = getCount($db, $pending_invoice_query, 'total_invoice_submitted');
    
    // Query to count procurement with status 'submitted' from both tables
    $pending_procurement_query = "
        SELECT  COUNT(*) AS total_procurement_submitted FROM procurement WHERE status = 'Pending Approval'
    ";
    $pending_procurement_count = getCount($db, $pending_procurement_query, 'total_procurement_submitted');
    
    // Query to count professional fee with status 'submitted' from both tables
    $pending_professional_query = "
        SELECT  COUNT(*) AS total_professional_submitted FROM professional_fee_applications WHERE status = 'Pending Approval'
    ";
    $pending_professional_count = getCount($db, $pending_professional_query, 'total_professional_submitted');
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_reconciliation_query = "
        SELECT  COUNT(*) AS total_reconciliation_submitted FROM reconciliation_claim_applications WHERE status = 'Pending Approval'
    ";
    $pending_reconciliation_count = getCount($db, $pending_reconciliation_query, 'total_reconciliation_submitted');
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_allowance_query = "
        SELECT  COUNT(*) AS total_allowance_submitted FROM allowance_applications WHERE status = 'Pending Approval'
    ";
    $pending_allowance_count = getCount($db, $pending_allowance_query, 'total_allowance_submitted');
    
    // Query to count project funding with status 'submitted' from both tables
    $pending_project_funding_query = "
        SELECT  COUNT(*) AS total_project_funding_submitted FROM project_funding_assistance_applications WHERE status = 'Pending Approval'
    ";
    $pending_project_funding_count = getCount($db, $pending_project_funding_query, 'total_project_funding_submitted');

    $total_pending_approval = $new_projects_count + $pending_ra_count + $pending_members_count + $pending_invoice_count + $pending_procurement_count + $pending_professional_count + $pending_reconciliation_count + $pending_allowance_count + $pending_project_funding_count;
    $finance_pending_total = $pending_invoice_count + $pending_procurement_count + $pending_professional_count + $pending_reconciliation_count + $pending_allowance_count + $pending_project_funding_count;
    $people_pending_total = $pending_ra_count + $pending_members_count;

    $moduleOverview = [
        ['title' => 'Project Registration', 'count' => $new_projects_count, 'icon' => 'ti-briefcase', 'link' => '#project-registration-section', 'note' => 'Project approval queue'],
        ['title' => 'RA/RO Registration', 'count' => $pending_ra_count, 'icon' => 'ti-user', 'link' => '#ra-registration-section', 'note' => 'New RA/RO approval'],
        ['title' => 'RA/RO Appointment', 'count' => $pending_members_count, 'icon' => 'ti-id-badge', 'link' => '#ra-appointment-section', 'note' => 'Appointment approval'],
        ['title' => 'Invoice', 'count' => $pending_invoice_count, 'icon' => 'ti-receipt', 'link' => 'new-invoice-application.php', 'note' => 'Financial approval'],
        ['title' => 'Procurement', 'count' => $pending_procurement_count, 'icon' => 'ti-truck', 'link' => 'new-procurement-application.php', 'note' => 'Purchase approval'],
        ['title' => 'Professional Fee', 'count' => $pending_professional_count, 'icon' => 'ti-link', 'link' => 'new-professional-fee-application.php', 'note' => 'Fee approval'],
        ['title' => 'Advance & Reconciliation/Claim', 'count' => $pending_reconciliation_count, 'icon' => 'ti-reload', 'link' => 'new-reconciliation-application.php', 'note' => 'Claim approval'],
        ['title' => 'Allowance & Wages', 'count' => $pending_allowance_count, 'icon' => 'ti-money', 'link' => 'new-allowance-wages-application.php', 'note' => 'Allowance approval'],
        ['title' => 'Project Funding', 'count' => $pending_project_funding_count, 'icon' => 'ti-blackboard', 'link' => 'new-project-funding-assistance-application.php', 'note' => 'Funding approval'],
    ];
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
    <style>
        .dashboard-hero {
            background: linear-gradient(135deg, #0f766e 0%, #16a085 48%, #27ae60 100%);
            color: #ffffff;
            border-radius: 18px;
            padding: 26px;
            margin-bottom: 22px;
            box-shadow: 0 16px 38px rgba(15, 118, 110, 0.22);
            position: relative;
            overflow: hidden;
        }
        .dashboard-hero:before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            right: -90px;
            top: -120px;
        }
        .dashboard-hero:after {
            content: "";
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            right: 110px;
            bottom: -90px;
        }
        .dashboard-hero .row {
            position: relative;
            z-index: 2;
        }
        .dashboard-hero h2 {
            color: #fff;
            margin-bottom: 8px;
            font-weight: 800;
            letter-spacing: -0.4px;
        }
        .dashboard-hero p {
            margin-bottom: 0;
            opacity: 0.9;
            max-width: 780px;
        }
        .approval-card {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            transition: all .18s ease;
            height: 100%;
        }
        .approval-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
        }
        .approval-card .card-body {
            padding: 20px;
        }
        .approval-card .approval-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #ffffff;
            flex: 0 0 52px;
        }
        .approval-card .approval-count {
            font-size: 32px;
            line-height: 1;
            font-weight: 800;
            margin-bottom: 6px;
            color: #0f172a;
        }
        .approval-card .approval-title {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 3px;
            color: #263238;
        }
        .approval-card .approval-note {
            font-size: 12.5px;
            color: #78909c;
            margin-bottom: 0;
        }
        .bg-soft-warning { background: #fff7e6; }
        .bg-soft-info { background: #e8f6ff; }
        .bg-soft-success { background: #eafaf1; }
        .bg-soft-danger { background: #fff0f0; }
        .icon-warning { background: linear-gradient(135deg,#f59e0b,#f97316); }
        .icon-info { background: linear-gradient(135deg,#0284c7,#38bdf8); }
        .icon-success { background: linear-gradient(135deg,#16a34a,#22c55e); }
        .icon-danger { background: linear-gradient(135deg,#ef4444,#f97316); }
        .section-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .section-title-row h3 {
            margin-bottom: 0;
            font-weight: 800;
            color: #263238;
        }
        .section-subtitle {
            color: #78909c;
            font-size: 13px;
            margin-top: 4px;
            margin-bottom: 0;
        }
        .module-card-link {
            color: inherit;
            display: block;
            height: 100%;
        }
        .module-card-link:hover {
            color: inherit;
            text-decoration: none;
        }
        .module-card {
            border: 1px solid #edf2f7;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            transition: all .18s ease;
            height: 100%;
            background: #fff;
            overflow: hidden;
            position: relative;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 13px 34px rgba(15, 23, 42, 0.10);
        }
        .module-card:before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 4px;
            width: 100%;
            background: #22c55e;
        }
        .module-card.has-pending:before {
            background: linear-gradient(90deg,#f59e0b,#f97316);
        }
        .module-card .card-body {
            padding: 18px;
        }
        .module-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            flex: 0 0 46px;
        }
        .module-icon.pending { background: linear-gradient(135deg,#f59e0b,#f97316); }
        .module-icon.clear { background: linear-gradient(135deg,#16a34a,#22c55e); }
        .module-count {
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }
        .module-title {
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 4px;
            min-height: 38px;
        }
        .module-note {
            font-size: 12.5px;
            color: #78909c;
            margin-bottom: 10px;
            min-height: 34px;
        }
        .mini-pill {
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 4px;
        }
        .mini-pill-warning { background: rgba(245, 158, 11, .14); color: #92400e; }
        .mini-pill-success { background: rgba(34, 197, 94, .14); color: #166534; }
        .table thead th {
            white-space: nowrap;
        }
        .action-pill {
            font-size: 12px;
            padding: 7px 10px;
            border-radius: 999px;
        }
        .empty-state {
            padding: 18px;
            border: 1px dashed #cfd8dc;
            border-radius: 14px;
            text-align: center;
            color: #78909c;
            background: #fbfdff;
        }
    </style>
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

                <div class="dashboard-hero">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2>Level 2 Approval Dashboard</h2>
                            <p>Monitor items waiting for approval, review supporting details, and take action faster.</p>
                        </div>
                        <div class="col-lg-4 text-lg-right m-t-10 m-lg-t-0">
                            <span class="badge badge-light p-10">Current queue: <?php echo $total_pending_approval; ?> item(s)</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 m-b-20">
                        <div class="card approval-card bg-soft-warning">
                            <div class="card-body">
                                <div class="d-flex no-block align-items-center">
                                    <div class="approval-icon icon-warning"><i class="ti-alert"></i></div>
                                    <div class="m-l-15">
                                        <div class="approval-count"><?php echo $total_pending_approval; ?></div>
                                        <div class="approval-title">Need Approval</div>
                                        <p class="approval-note">Total items waiting for Level 2 action</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 m-b-20">
                        <div class="card approval-card bg-soft-info">
                            <div class="card-body">
                                <div class="d-flex no-block align-items-center">
                                    <div class="approval-icon icon-info"><i class="ti-briefcase"></i></div>
                                    <div class="m-l-15">
                                        <div class="approval-count"><?php echo $new_projects_count; ?></div>
                                        <div class="approval-title">Projects</div>
                                        <p class="approval-note">Pending project approval</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 m-b-20">
                        <div class="card approval-card bg-soft-success">
                            <div class="card-body">
                                <div class="d-flex no-block align-items-center">
                                    <div class="approval-icon icon-success"><i class="ti-user"></i></div>
                                    <div class="m-l-15">
                                        <div class="approval-count"><?php echo $people_pending_total; ?></div>
                                        <div class="approval-title">RA/RO</div>
                                        <p class="approval-note">Registration and appointment approval</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 m-b-20">
                        <div class="card approval-card bg-soft-danger">
                            <div class="card-body">
                                <div class="d-flex no-block align-items-center">
                                    <div class="approval-icon icon-danger"><i class="ti-money"></i></div>
                                    <div class="m-l-15">
                                        <div class="approval-count"><?php echo $finance_pending_total; ?></div>
                                        <div class="approval-title">Finance</div>
                                        <p class="approval-note">Financial applications requiring approval</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row m-b-10">
                    <div class="col-12">
                        <div class="section-title-row m-b-15">
                            <div>
                                <h3>Approval Modules</h3>
                                <p class="section-subtitle">Nine approval areas grouped in one compact overview. Open the module that needs action.</p>
                            </div>
                            <span class="badge badge-warning action-pill"><?php echo $total_pending_approval; ?> Pending Approval</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($moduleOverview as $module) { 
                        $hasPending = $module['count'] > 0;
                    ?>
                        <div class="col-xl-4 col-lg-4 col-md-6 m-b-20">
                            <a class="module-card-link" href="<?php echo safeEcho($module['link']); ?>">
                                <div class="card module-card <?php echo $hasPending ? 'has-pending' : ''; ?>">
                                    <div class="card-body">
                                        <div class="d-flex no-block align-items-start">
                                            <div class="module-icon <?php echo $hasPending ? 'pending' : 'clear'; ?>">
                                                <i class="<?php echo safeEcho($module['icon']); ?>"></i>
                                            </div>
                                            <div class="m-l-15" style="width:100%;">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="module-title"><?php echo safeEcho($module['title']); ?></div>
                                                    <div class="module-count"><?php echo $module['count']; ?></div>
                                                </div>
                                                <p class="module-note"><?php echo safeEcho($module['note']); ?></p>
                                                <?php if ($hasPending) { ?>
                                                    <span class="mini-pill mini-pill-warning">
                                                        <i class="ti-alert"></i> Approve / Return Needed
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="mini-pill mini-pill-success">
                                                        <i class="ti-check"></i> Clear
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <div class="row" id="project-registration-section">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="section-title-row m-b-20">
                                    <div>
                                        <h3>Project Registration</h3>
                                        <p class="section-subtitle">Projects already verified by Level 3 and waiting for Level 2 approval.</p>
                                    </div>
                                    <span class="badge badge-warning action-pill"><?php echo $new_projects_count; ?> Pending</span>
                                </div>
                                <div class="table-responsive">
                                    <table id="project-registration-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Leader</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
                                                <th>Current Stage</th>
                                                <th>Action Needed</th>
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
                                                    WHERE project_status IN ('Pending Approval')
                                                    ORDER BY project_no ASC;
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
                                                    <span class="badge <?php echo statusBadgeClass($project_status); ?>">
                                                        <?php echo htmlspecialchars($project_status); ?>
                                                    </span>
                                                </td>
                                                <td>Level 2 Approval</td>
                                                <td><span class="badge <?php echo actionBadgeClass($project_status); ?> action-pill"><?php echo actionNeededText($project_status); ?></span></td>
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
                <div class="row" id="ra-registration-section">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="section-title-row m-b-20">
                                    <div>
                                        <h3>RA/RO Registration</h3>
                                        <p class="section-subtitle">New RA/RO profiles waiting for Level 2 approval.</p>
                                    </div>
                                    <span class="badge badge-warning action-pill"><?php echo $pending_ra_count; ?> Pending</span>
                                </div>
                                <div class="table-responsive">
                                    <table id="ra-registration-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>IC Number</th>
                                                <th>Date Register</th>
                                                <th>Status</th>
                                                <th>Current Stage</th>
                                                <th>Action Needed</th>
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
                                                    WHERE status = 'Pending Approval'
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
                                                    <span class="badge <?php echo statusBadgeClass($status); ?>">
                                                        <?php echo htmlspecialchars($status); ?>
                                                    </span>
                                                </td>
                                                <td>Level 2 Approval</td>
                                                <td><span class="badge <?php echo actionBadgeClass($status); ?> action-pill"><?php echo actionNeededText($status); ?></span></td>
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
                <div class="row" id="ra-appointment-section">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="section-title-row m-b-20">
                                    <div>
                                        <h3>RA/RO Appointment</h3>
                                        <p class="section-subtitle">RA/RO appointments that require approve or return action.</p>
                                    </div>
                                    <span class="badge badge-warning action-pill"><?php echo $pending_members_count; ?> Pending</span>
                                </div>
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
                                                <th>Current Stage</th>
                                                <th>Action Needed</th>
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
                                                        raa.status
                                                    FROM 
                                                        project p
                                                    JOIN 
                                                        research_assistant_application raa ON raa.project_id = p.id
                                                    JOIN 
                                                        research_assistant ra ON ra.id = raa.ra_id
                                                    WHERE 
                                                        raa.status = 'Pending Approval'
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
                                                    <span class="badge <?php echo statusBadgeClass($status); ?>">
                                                        <?php echo htmlspecialchars($status); ?>
                                                    </span>
                                                </td>
                                                <td>Level 2 Approval</td>
                                                <td><span class="badge <?php echo actionBadgeClass($status); ?> action-pill"><?php echo actionNeededText($status); ?></span></td>
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
                                                    $isPendingApproval = stripos($status, 'Approved') !== false;
                                                    $disableButtons = $isRejected || $isPendingApproval;
                                                    ?>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-success btn-sm approveApplication" 
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Approve
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-danger btn-sm returnApplication" 
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Return
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
                <!-- Modal to View Project -->
                <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewDetailsModalLabel">Application Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Content will be dynamically loaded here -->
                                <div id="detailsContent">
                                    <p class="text-center">Loading details...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <script>
        $(document).on('click', '.viewDetails', function () {
            const raaId = $(this).data('raa-id');
            
            // Paparkan modal dan setkan loading message
            $('#viewDetailsModal').modal('show');
            $('#detailsContent').html('<p class="text-center">Loading details...</p>');
        
            // AJAX untuk dapatkan data
            $.ajax({
                url: 'get-research-application-details.php',
                method: 'POST',
                data: { raa_id: raaId },
                dataType: 'html',
                success: function (response) {
                    $('#detailsContent').html(response); // Paparkan kandungan ke modal
                },
                error: function () {
                    $('#detailsContent').html('<p class="text-center text-danger">Failed to load details. Please try again later.</p>');
                }
            });
        });
    </script>
    <script>
        $(function () {
            $('#ra-registration-table').DataTable({ responsive: true });
            $('#ra-appointment-table').DataTable({ responsive: true });
            $('#project-registration-table').DataTable({ responsive: true });
        });
    </script>
    <script>
        $(document).on('click', '.returnApplication', function () {
            const button = $(this);

            Swal.fire({
                title: 'Return Application',
                html:
                    '<select id="returnLevel" class="swal2-input">' +
                        '<option value="" disabled selected>Select return level</option>' +
                        '<option value="Level 3">CST Level 3</option>' +
                        '<option value="Consultant">Consultant</option>' +
                    '</select>' +
                    '<textarea id="returnRemark" class="swal2-textarea" placeholder="Enter your remarks here..."></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const level = $('#returnLevel').val();
                    const remark = $('#returnRemark').val();
    
                    if (!level || !remark) {
                        Swal.showValidationMessage('Please select return level and enter remark.');
                        return false;
                    }
    
                    return {
                        level: level,
                        remark: remark
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const applicationId = button.data('application-id');
                    const staffId = button.data('admin-staff-id');
                    const { level, remark } = result.value;

                    button.prop('disabled', true).html('Returning...');

                    Swal.fire({
                        title: 'Returning Application...',
                        text: 'Please wait while the application is being returned.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    $.ajax({
                        url: 'ra-application-return.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            applicationId: applicationId,
                            remark: remark,
                            staffId: staffId,
                            return_to: level
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Returned!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message || 'Unable to return application.',
                                    'error'
                                ).then(() => {
                                    button.prop('disabled', false).html('Return');
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire('Error', 'Server error occurred.', 'error').then(() => {
                                button.prop('disabled', false).html('Return');
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '.approveApplication', function () {
            const button = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "Once approve, you cannot edit this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.value) { // Jika pengguna mengesahkan
                     // Ambil data dari atribut butang
                    const applicationId = button.data('application-id');
                    const staffId = button.data('admin-staff-id');
    
                    // Debug data yang akan dihantar
                    console.log("Application Fee ID:", applicationId);

                    button.prop('disabled', true).html('Approving...');

                    Swal.fire({
                        title: 'Approving Application...',
                        text: 'Please wait while the application is being approved.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'ra-application-approve.php',
                        method: 'GET', // Gunakan GET seperti yang diminta
                        data: {
                            applicationId: applicationId,
                            staffId: staffId
                        },
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);
    
                            if (response.success) {
                                Swal.fire(
                                    'Approved!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload(); // Muat semula halaman selepas berjaya
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                ).then(() => {
                                    button.prop('disabled', false).html('Approve');
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire(
                                'Error!',
                                'An error occurred during submission. Please check the console for details.',
                                'error'
                            ).then(() => {
                                button.prop('disabled', false).html('Approve');
                            });
                        }
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Research assistant application approval has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>