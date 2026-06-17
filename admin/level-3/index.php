<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../../db_connect/db_connect.php';
?>
<?php
    /* ==============================================================
       Helper functions
       ============================================================== */
    function fetch_count($db, $query, $key)
    {
        $result = mysqli_query($db, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return isset($row[$key]) ? (int)$row[$key] : 0;
        }
        return 0;
    }

    function statusBadgeClass($status)
    {
        if (stripos($status, 'Rejected') !== false || stripos($status, 'Returned') !== false) {
            return 'badge-danger';
        } elseif (stripos($status, 'Pending Approval') !== false) {
            return 'badge-info';
        } elseif (stripos($status, 'Pending Verification') !== false || stripos($status, 'Pending Submission') !== false) {
            return 'badge-warning';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'badge-success';
        }
        return 'badge-secondary';
    }

    function actionNeededLabel($status)
    {
        if (stripos($status, 'Returned') !== false) {
            return 'Review Returned Item';
        } elseif (stripos($status, 'Pending Verification') !== false) {
            return 'Verify Application';
        } elseif (stripos($status, 'Pending Approval') !== false) {
            return 'Waiting for Approval';
        } elseif (stripos($status, 'Pending Submission') !== false) {
            return 'Waiting for Submission';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'Completed';
        }
        return 'Check Status';
    }

    function actionNeededClass($status)
    {
        if (stripos($status, 'Returned') !== false) {
            return 'action-danger';
        } elseif (stripos($status, 'Pending Verification') !== false) {
            return 'action-warning';
        } elseif (stripos($status, 'Pending Approval') !== false) {
            return 'action-info';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'action-success';
        }
        return 'action-muted';
    }

    function stageLabel($status)
    {
        if (stripos($status, 'Returned') !== false) {
            return 'Returned to Level 3';
        } elseif (stripos($status, 'Pending Verification') !== false) {
            return 'Level 3 Verification';
        } elseif (stripos($status, 'Pending Approval') !== false) {
            return 'Approval Stage';
        } elseif (stripos($status, 'Approved') !== false || stripos($status, 'Appointed') !== false) {
            return 'Completed Stage';
        }
        return 'In Progress';
    }

    function level3_column_exists($db, $table, $column)
    {
        $table = mysqli_real_escape_string($db, $table);
        $column = mysqli_real_escape_string($db, $column);
        $result = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && mysqli_num_rows($result) > 0;
    }

    function level3_format_short_date($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }

        $timestamp = strtotime($date);
        return $timestamp ? date("d M Y", $timestamp) : '-';
    }
?>
<?php
    /* ==============================================================
       Dashboard counts - Pending Verification and Returned to Level 3
       ============================================================== */

    // Query to count all new projects (Consultancy + Training) with status 'Pending Submission'
    $new_projects_query = "
        SELECT COUNT(*) AS total_new_projects
        FROM project
        WHERE (project_status = 'Pending Verification' OR (project_status = 'Returned' AND return_to = 'Level 3'))
    ";
    $new_projects_count = fetch_count($db, $new_projects_query, 'total_new_projects');

    $pending_projects_query = "
        SELECT COUNT(*) AS total_pending_projects
        FROM project
        WHERE project_status = 'Pending Verification'
    ";
    $pending_projects_count = fetch_count($db, $pending_projects_query, 'total_pending_projects');

    $returned_projects_query = "
        SELECT COUNT(*) AS total_returned_projects
        FROM project
        WHERE project_status = 'Returned' AND return_to = 'Level 3'
    ";
    $returned_projects_count = fetch_count($db, $returned_projects_query, 'total_returned_projects');
    
    // Query to count Research Assistants with status 'Pending Verification'
    $pending_ra_query = "SELECT COUNT(*) AS pending_ra FROM research_assistant WHERE status = 'Pending Verification' OR (status LIKE '%Returned%' AND return_to = 'Level 3')";
    $pending_ra_count = fetch_count($db, $pending_ra_query, 'pending_ra');

    $pending_ra_registration_query = "SELECT COUNT(*) AS pending_ra_registration FROM research_assistant WHERE status = 'Pending Verification'";
    $pending_ra_registration_count = fetch_count($db, $pending_ra_registration_query, 'pending_ra_registration');

    $returned_ra_registration_query = "SELECT COUNT(*) AS returned_ra_registration FROM research_assistant WHERE status LIKE '%Returned%' AND return_to = 'Level 3'";
    $returned_ra_registration_count = fetch_count($db, $returned_ra_registration_query, 'returned_ra_registration');
    
    // Query to count members with status 'Pending Verification' from both tables
    $pending_members_query = "
        SELECT COUNT(*) AS total_pending_research_application 
        FROM research_assistant_application 
        WHERE status = 'Pending Verification' OR ((status LIKE '%Returned%' OR status = 'Rejected') AND return_to = 'Level 3')
    ";
    $pending_members_count = fetch_count($db, $pending_members_query, 'total_pending_research_application');

    $pending_members_verification_query = "
        SELECT COUNT(*) AS total_pending_ra_application 
        FROM research_assistant_application 
        WHERE status = 'Pending Verification'
    ";
    $pending_members_verification_count = fetch_count($db, $pending_members_verification_query, 'total_pending_ra_application');

    $returned_members_query = "
        SELECT COUNT(*) AS total_returned_ra_application 
        FROM research_assistant_application 
        WHERE (status LIKE '%Returned%' OR status = 'Rejected') AND return_to = 'Level 3'
    ";
    $returned_members_count = fetch_count($db, $returned_members_query, 'total_returned_ra_application');
    
     // Query to count invoice with status 'submitted' from both tables
    $pending_invoice_query = "
        SELECT COUNT(*) AS total_invoice_submitted 
        FROM invoices 
        WHERE invoice_status = 'Pending Verification' OR (invoice_status LIKE '%Returned%' AND return_to = 'Level 3')
    ";
    $pending_invoice_count = fetch_count($db, $pending_invoice_query, 'total_invoice_submitted');

    $returned_invoice_query = "
        SELECT COUNT(*) AS total_invoice_returned 
        FROM invoices 
        WHERE invoice_status LIKE '%Returned%' AND return_to = 'Level 3'
    ";
    $returned_invoice_count = fetch_count($db, $returned_invoice_query, 'total_invoice_returned');
    
    // Query to count procurement with status 'submitted' from both tables
    $pending_procurement_query = "
        SELECT COUNT(*) AS total_procurement_submitted 
        FROM procurement 
        WHERE status = 'Pending Verification' OR ((status LIKE '%Returned%' OR status = 'Rejected') AND return_to = 'Level 3')
    ";
    $pending_procurement_count = fetch_count($db, $pending_procurement_query, 'total_procurement_submitted');

    $returned_procurement_query = "
        SELECT COUNT(*) AS total_procurement_returned 
        FROM procurement 
        WHERE (status LIKE '%Returned%' OR status = 'Rejected') AND return_to = 'Level 3'
    ";
    $returned_procurement_count = fetch_count($db, $returned_procurement_query, 'total_procurement_returned');
    
    // Query to count professional fee with status 'submitted' from both tables
    $pending_professional_query = "
        SELECT COUNT(*) AS total_professional_submitted 
        FROM professional_fee_applications 
        WHERE status = 'Pending Verification' OR (status = 'Rejected' AND return_to = 'Level 3')
    ";
    $pending_professional_count = fetch_count($db, $pending_professional_query, 'total_professional_submitted');

    $returned_professional_query = "
        SELECT COUNT(*) AS total_professional_returned 
        FROM professional_fee_applications 
        WHERE status = 'Rejected' AND return_to = 'Level 3'
    ";
    $returned_professional_count = fetch_count($db, $returned_professional_query, 'total_professional_returned');
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_reconciliation_query = "
        SELECT COUNT(*) AS total_reconciliation_submitted 
        FROM reconciliation_claim_applications 
        WHERE status = 'Pending Verification' OR (status LIKE '%Returned%' AND return_to = 'Level 3')
    ";
    $pending_reconciliation_count = fetch_count($db, $pending_reconciliation_query, 'total_reconciliation_submitted');

    $returned_reconciliation_query = "
        SELECT COUNT(*) AS total_reconciliation_returned 
        FROM reconciliation_claim_applications 
        WHERE status LIKE '%Returned%' AND return_to = 'Level 3'
    ";
    $returned_reconciliation_count = fetch_count($db, $returned_reconciliation_query, 'total_reconciliation_returned');
    
    // Query to count reconciliation with status 'submitted' from both tables
    $pending_allowance_query = "
        SELECT COUNT(*) AS total_allowance_submitted 
        FROM allowance_applications 
        WHERE status = 'Pending Verification' OR (status LIKE '%Returned%' AND return_to = 'Level 3')
    ";
    $pending_allowance_count = fetch_count($db, $pending_allowance_query, 'total_allowance_submitted');

    $returned_allowance_query = "
        SELECT COUNT(*) AS total_allowance_returned 
        FROM allowance_applications 
        WHERE status LIKE '%Returned%' AND return_to = 'Level 3'
    ";
    $returned_allowance_count = fetch_count($db, $returned_allowance_query, 'total_allowance_returned');
    
    // Query to count project funding with status 'submitted' from both tables
    $pending_project_funding_query = "
        SELECT COUNT(*) AS total_project_funding_submitted 
        FROM project_funding_assistance_applications 
        WHERE status = 'Pending Verification' OR (status LIKE '%Returned%' AND return_to = 'Level 3')
    ";
    $pending_project_funding_count = fetch_count($db, $pending_project_funding_query, 'total_project_funding_submitted');

    $returned_project_funding_query = "
        SELECT COUNT(*) AS total_project_funding_returned 
        FROM project_funding_assistance_applications 
        WHERE status LIKE '%Returned%' AND return_to = 'Level 3'
    ";
    $returned_project_funding_count = fetch_count($db, $returned_project_funding_query, 'total_project_funding_returned');

    $total_need_action = $new_projects_count + $pending_ra_count + $pending_members_count + $pending_invoice_count + $pending_procurement_count + $pending_professional_count + $pending_reconciliation_count + $pending_allowance_count + $pending_project_funding_count;
    $total_returned = $returned_projects_count + $returned_ra_registration_count + $returned_members_count + $returned_invoice_count + $returned_procurement_count + $returned_professional_count + $returned_reconciliation_count + $returned_allowance_count + $returned_project_funding_count;
    $total_pending_verification = $total_need_action - $total_returned;

    $module_cards = [
        [
            'title' => 'Project Registration',
            'count' => $new_projects_count,
            'pending' => $pending_projects_count,
            'returned' => $returned_projects_count,
            'icon' => 'ti-briefcase',
            'link' => '#project-registration-section',
            'theme' => 'success'
        ],
        [
            'title' => 'RA/RO Registration',
            'count' => $pending_ra_count,
            'pending' => $pending_ra_registration_count,
            'returned' => $returned_ra_registration_count,
            'icon' => 'ti-user',
            'link' => '#ra-registration-section',
            'theme' => 'success'
        ],
        [
            'title' => 'RA/RO Appointment',
            'count' => $pending_members_count,
            'pending' => $pending_members_verification_count,
            'returned' => $returned_members_count,
            'icon' => 'ti-id-badge',
            'link' => '#ra-appointment-section',
            'theme' => 'success'
        ],
        [
            'title' => 'Invoice',
            'count' => $pending_invoice_count,
            'pending' => $pending_invoice_count - $returned_invoice_count,
            'returned' => $returned_invoice_count,
            'icon' => 'ti-receipt',
            'link' => 'new-invoice-application.php',
            'theme' => 'danger'
        ],
        [
            'title' => 'Procurement',
            'count' => $pending_procurement_count,
            'pending' => $pending_procurement_count - $returned_procurement_count,
            'returned' => $returned_procurement_count,
            'icon' => 'ti-truck',
            'link' => 'new-procurement-application.php',
            'theme' => 'danger'
        ],
        [
            'title' => 'Professional Fee',
            'count' => $pending_professional_count,
            'pending' => $pending_professional_count - $returned_professional_count,
            'returned' => $returned_professional_count,
            'icon' => 'ti-link',
            'link' => 'new-professional-fee-application.php',
            'theme' => 'danger'
        ],
        [
            'title' => 'Advance & Reconciliation/Claim',
            'count' => $pending_reconciliation_count,
            'pending' => $pending_reconciliation_count - $returned_reconciliation_count,
            'returned' => $returned_reconciliation_count,
            'icon' => 'ti-reload',
            'link' => 'new-reconciliation-application.php',
            'theme' => 'danger'
        ],
        [
            'title' => 'Allowance & Wages',
            'count' => $pending_allowance_count,
            'pending' => $pending_allowance_count - $returned_allowance_count,
            'returned' => $returned_allowance_count,
            'icon' => 'ti-money',
            'link' => 'new-allowance-wages-application.php',
            'theme' => 'danger'
        ],
        [
            'title' => 'Project Funding',
            'count' => $pending_project_funding_count,
            'pending' => $pending_project_funding_count - $returned_project_funding_count,
            'returned' => $returned_project_funding_count,
            'icon' => 'ti-blackboard',
            'link' => 'new-project-funding-assistance-application.php',
            'theme' => 'danger'
        ],
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
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <style>
        .dashboard-hero {
            background: linear-gradient(135deg, #0f766e 0%, #16a34a 55%, #22c55e 100%);
            border-radius: 18px;
            padding: 26px;
            color: #fff;
            box-shadow: 0 16px 38px rgba(15, 118, 110, 0.22);
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
        }
        .dashboard-hero:before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            right: -90px;
            top: -120px;
        }
        .dashboard-hero:after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            right: 110px;
            bottom: -100px;
        }
        .dashboard-hero-content {
            position: relative;
            z-index: 2;
        }
        .dashboard-hero h2 {
            color: #fff;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .dashboard-hero p {
            color: rgba(255,255,255,0.86);
            margin-bottom: 0;
            max-width: 760px;
        }
        .summary-card {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            transition: all 0.2s ease;
            height: 100%;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 38px rgba(15, 23, 42, 0.12);
        }
        .summary-card .card-body {
            padding: 20px;
        }
        .summary-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 8px;
        }
        .summary-number {
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .summary-help {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 0;
        }
        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .summary-warning .summary-icon { background: rgba(245, 158, 11, .13); color: #b45309; }
        .summary-danger .summary-icon { background: rgba(239, 68, 68, .13); color: #b91c1c; }
        .summary-info .summary-icon { background: rgba(14, 165, 233, .13); color: #0369a1; }
        .summary-success .summary-icon { background: rgba(34, 197, 94, .13); color: #15803d; }
        .module-card-link { color: inherit; text-decoration: none; display: block; height: 100%; }
        .module-card-link:hover { color: inherit; text-decoration: none; }
        .module-card {
            border: 1px solid #edf2f7;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            transition: all .2s ease;
            height: 100%;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.10);
        }
        .module-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 19px;
        }
        .module-icon.success { background: linear-gradient(135deg,#16a34a,#22c55e); }
        .module-icon.danger { background: linear-gradient(135deg,#ef4444,#f97316); }
        .module-count {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }
        .module-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            min-height: 38px;
        }
        .mini-pill {
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 5px;
            margin-top: 4px;
        }
        .mini-pill-warning { background: rgba(245, 158, 11, .14); color: #92400e; }
        .mini-pill-danger { background: rgba(239, 68, 68, .14); color: #991b1b; }
        .section-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
            overflow: hidden;
        }
        .section-card .card-header {
            border: 0;
            padding: 18px 22px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            color: #fff;
        }
        .section-subtitle {
            font-size: 13px;
            opacity: .88;
            margin-top: 3px;
        }
        .action-badge {
            border-radius: 999px;
            padding: 7px 11px;
            font-weight: 700;
            font-size: 12px;
            display: inline-block;
            white-space: nowrap;
        }
        .action-danger { background: #fee2e2; color: #991b1b; }
        .action-warning { background: #fef3c7; color: #92400e; }
        .action-info { background: #e0f2fe; color: #075985; }
        .action-success { background: #dcfce7; color: #166534; }
        .action-muted { background: #f1f5f9; color: #475569; }
        .status-cell .badge {
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
        }
        .quick-filter-box {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 8px 24px rgba(15,23,42,.06);
            margin-bottom: 22px;
        }
        .quick-filter-box h5 {
            font-weight: 800;
            margin-bottom: 12px;
        }
        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            margin-right: 6px;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 700;
            font-size: 13px;
        }
        .quick-link:hover {
            background: #ecfdf5;
            border-color: #86efac;
            color: #166534;
            text-decoration: none;
        }
        .table thead th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .35px;
            color: #475569;
            background: #f8fafc;
        }
        .table td {
            vertical-align: middle;
        }
        .empty-state {
            padding: 28px;
            border: 1px dashed #cbd5e1;
            border-radius: 15px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
        }
        @media (max-width: 767px) {
            .dashboard-hero { padding: 20px; }
            .dashboard-hero h2 { font-size: 24px; }
            .summary-number { font-size: 28px; }
        }
    </style>
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
                    <div class="dashboard-hero-content">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h2>Level 3 Action Dashboard</h2>
                                <p>Monitor pending verification, returned items, and applications that need your action. Use the summary cards to identify urgent work, then jump directly to the relevant section.</p>
                            </div>
                            <div class="col-lg-4 text-lg-right m-t-20 m-t-lg-0">
                                <span class="badge badge-light" style="font-size:13px;padding:10px 14px;border-radius:999px;">Current Scope: Level 3 Verification</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6 m-b-20">
                        <div class="card summary-card summary-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="summary-label">Need Action</div>
                                        <div class="summary-number"><?php echo $total_need_action; ?></div>
                                        <p class="summary-help">All pending or returned items under Level 3.</p>
                                    </div>
                                    <div class="summary-icon"><i class="ti-alert"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 m-b-20">
                        <div class="card summary-card summary-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="summary-label">Pending Verification</div>
                                        <div class="summary-number"><?php echo $total_pending_verification; ?></div>
                                        <p class="summary-help">Items waiting for Level 3 review.</p>
                                    </div>
                                    <div class="summary-icon"><i class="ti-search"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 m-b-20">
                        <div class="card summary-card summary-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="summary-label">Returned to Level 3</div>
                                        <div class="summary-number"><?php echo $total_returned; ?></div>
                                        <p class="summary-help">Returned cases that need re-check or correction.</p>
                                    </div>
                                    <div class="summary-icon"><i class="ti-back-left"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 m-b-20">
                        <div class="card summary-card summary-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="summary-label">Main Work Queue</div>
                                        <div class="summary-number"><?php echo $new_projects_count + $pending_ra_count + $pending_members_count; ?></div>
                                        <p class="summary-help">Projects, RA registration, and RA appointment cases.</p>
                                    </div>
                                    <div class="summary-icon"><i class="ti-view-list-alt"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quick-filter-box">
                    <h5>Quick Jump</h5>
                    <a href="#project-registration-section" class="quick-link"><i class="ti-briefcase"></i> Project Registration</a>
                    <a href="#ra-registration-section" class="quick-link"><i class="ti-user"></i> RA/RO Registration</a>
                    <a href="#ra-appointment-section" class="quick-link"><i class="ti-id-badge"></i> RA/RO Appointment</a>
                    <a href="new-invoice-application.php" class="quick-link"><i class="ti-receipt"></i> Invoice</a>
                    <a href="new-procurement-application.php" class="quick-link"><i class="ti-truck"></i> Procurement</a>
                    <a href="new-professional-fee-application.php" class="quick-link"><i class="ti-link"></i> Professional Fee</a>
                    <a href="new-reconciliation-application.php" class="quick-link"><i class="ti-reload"></i> Reconciliation/Claim</a>
                    <a href="new-allowance-wages-application.php" class="quick-link"><i class="ti-money"></i> Allowance & Wages</a>
                    <a href="new-project-funding-assistance-application.php" class="quick-link"><i class="ti-blackboard"></i> Project Funding</a>
                </div>

                <div class="row">
                    <?php foreach ($module_cards as $module) { ?>
                        <div class="col-xl-4 col-lg-4 col-md-6 m-b-20">
                            <a href="<?php echo $module['link']; ?>" class="module-card-link">
                                <div class="card module-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="module-title"><?php echo htmlspecialchars($module['title']); ?></div>
                                                <div class="module-count"><?php echo (int)$module['count']; ?></div>
                                            </div>
                                            <div class="module-icon <?php echo $module['theme']; ?>"><i class="<?php echo $module['icon']; ?>"></i></div>
                                        </div>
                                        <div class="m-t-15">
                                            <span class="mini-pill mini-pill-warning">Pending: <?php echo max(0, (int)$module['pending']); ?></span>
                                            <span class="mini-pill mini-pill-danger">Returned: <?php echo max(0, (int)$module['returned']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <div class="row" id="project-registration-section">
                    <div class="col-12">
                        <div class="card section-card">
                            <div class="card-header bg-success text-white">
                                <h3 class="section-title">Project Registration</h3>
                                <div class="section-subtitle">Projects that require Level 3 verification or returned-item review.</div>
                            </div>
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
                                                    WHERE (project_status = 'Pending Verification' OR (project_status = 'Returned' AND return_to = 'Level 3'))
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
                                                    $statusClass = statusBadgeClass($project_status);
                                                    $actionClass = actionNeededClass($project_status);
                                                    
                                                    // Determine the URL based on the project source
                                                    $info_page = ($project_source === 'Consultancy') 
                                                                 ? 'consultancy-project-info.php' 
                                                                 : 'training-project-info.php';
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($project_no); ?></strong></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_leader); ?></td>
                                                <td><?php echo htmlspecialchars($project_type); ?></td>
                                                <td class="status-cell"><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($project_status); ?></span></td>
                                                <td><?php echo stageLabel($project_status); ?></td>
                                                <td><span class="action-badge <?php echo $actionClass; ?>"><?php echo actionNeededLabel($project_status); ?></span></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>" 
                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                       title="Full Info">
                                                       Review
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
                        <div class="card section-card">
                            <div class="card-header bg-success text-white">
                                <h3 class="section-title">RA/RO Registration</h3>
                                <div class="section-subtitle">New RA/RO registrations pending verification or returned to Level 3.</div>
                            </div>
                            <div class="card-body">
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
                                                    WHERE status = 'Pending Verification' OR (status LIKE '%Returned%' AND return_to = 'Level 3')
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
                                                    $statusClass = statusBadgeClass($status);
                                                    $actionClass = actionNeededClass($status);
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($full_name); ?></strong></td>
                                                <td><?php echo htmlspecialchars($ic); ?></td>
                                                <td><?php echo date("d F Y", strtotime($date_register)); ?></td>
                                                <td class="status-cell"><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                                <td><?php echo stageLabel($status); ?></td>
                                                <td><span class="action-badge <?php echo $actionClass; ?>"><?php echo actionNeededLabel($status); ?></span></td>
                                                <td class="text-center">
                                                    <a href="ra-info.php?id=<?php echo urlencode($id); ?>" 
                                                       class="btn waves-effect waves-light btn-info assign-button" 
                                                       title="Full Info">
                                                       Review
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
                        <div class="card section-card">
                            <div class="card-header bg-success text-white">
                                <h3 class="section-title">RA/RO Appointment</h3>
                                <div class="section-subtitle">RA/RO project appointments that require verification, editing, or rejection action.</div>
                            </div>
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
                                                <th>Period</th>
                                                <th>Monthly Amount</th>
                                                <th>Status</th>
                                                <th>Current Stage</th>
                                                <th>Action Needed</th>
                                                <th class="text-center">Details</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $has_ra_end_date = level3_column_exists($db, 'research_assistant_application', 'end_date');
                                                $select_ra_end_date = $has_ra_end_date ? "raa.end_date," : "NULL AS end_date,";
                                                $query = "
                                                    SELECT 
                                                        raa.id AS application_id,
                                                        ra.id,
                                                        ra.full_name,
                                                        ra.ic,
                                                        p.project_title,
                                                        p.project_no,
                                                        raa.start_date,
                                                        $select_ra_end_date
                                                        raa.duration,
                                                        raa.budget,
                                                        raa.status,
                                                        raa.return_to
                                                    FROM 
                                                        project p
                                                    JOIN 
                                                        research_assistant_application raa ON raa.project_id = p.id
                                                    JOIN 
                                                        research_assistant ra ON ra.id = raa.ra_id
                                                    WHERE 
                                                        raa.status = 'Pending Verification' OR ((raa.status LIKE '%Returned%' OR raa.status = 'Rejected') AND raa.return_to = 'Level 3')
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
                                                        $start_date = $row['start_date'];
                                                        $end_date = $row['end_date'] ?? '';
                                                        $duration = (int)($row['duration'] ?? 0);
                                                        $budget = (float)($row['budget'] ?? 0);
                                                        $start_text = level3_format_short_date($start_date);
                                                        $end_text = level3_format_short_date($end_date);
                                                        $period_text = $start_text !== '-'
                                                            ? $start_text . " - " . $end_text . " (" . $duration . " month(s))"
                                                            : "-";
                                                        $status = $row['status'];
                                                        $return_to = trim((string)($row['return_to'] ?? ''));
                                                        $isReturnedToLevel3 = strcasecmp($return_to, 'Level 3') === 0 && (stripos($status, 'Returned') !== false || stripos($status, 'Rejected') !== false);
                                                        $statusClass = statusBadgeClass($status);
                                                        $actionClass = $isReturnedToLevel3 ? 'action-danger' : actionNeededClass($status);
                                                        $actionText = $isReturnedToLevel3 ? 'Review Returned Item' : actionNeededLabel($status);
                                                        $stageText = $isReturnedToLevel3 ? 'Returned to Level 3' : stageLabel($status);
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><strong><?php echo htmlspecialchars($full_name); ?></strong></td>
                                                <td><?php echo htmlspecialchars($ic); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($period_text); ?></td>
                                                <td>RM <?php echo number_format($budget, 2); ?></td>
                                                <td class="status-cell"><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                                <td><?php echo htmlspecialchars($stageText); ?></td>
                                                <td><span class="action-badge <?php echo $actionClass; ?>"><?php echo htmlspecialchars($actionText); ?></span></td>
                                                <td class="text-center">
                                                    <a href="javascript:void(0);" 
                                                       class="btn btn-info btn-sm viewDetails" 
                                                       data-raa-id="<?php echo urlencode($raa_id); ?>">
                                                       View Details
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $isRejected = stripos($status, 'Rejected') !== false && !$isReturnedToLevel3;
                                                    $isPendingApproval = stripos($status, 'Pending Approval') !== false || stripos($status, 'Approved') !== false;
                                                    $disableButtons = $isRejected || $isPendingApproval;
                                                    ?>
                                                    <a 
                                                        href="ra-application-edit.php?id=<?php echo urlencode($raa_id); ?>" 
                                                        class="btn btn-info btn-sm" 
                                                        <?php echo $disableButtons ? 'style="pointer-events: none; opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Edit
                                                    </a>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-success btn-sm verifyApplication" 
                                                        data-application-id="<?php echo urlencode($raa_id); ?>"
                                                        data-admin-staff-id="<?php echo $userData['staff_id']; ?>"
                                                        <?php echo $disableButtons ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                        Verify
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-danger btn-sm rejectApplication" 
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
            $('#ra-registration-table').DataTable({ responsive: true, pageLength: 10, order: [[3, 'asc']] });
            $('#ra-appointment-table').DataTable({ responsive: true, pageLength: 10, order: [[5, 'asc']] });
            $('#project-registration-table').DataTable({ responsive: true, pageLength: 10, order: [[4, 'asc']] });
        });
    </script>
    <script>
        $(document).on('click', '.rejectApplication', function () {
            const clickedButton = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "Once rejected, you cannot undo this action!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prompt for remarks
                    Swal.fire({
                        title: 'Remarks',
                        input: 'textarea',
                        inputPlaceholder: 'Enter your remarks here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        preConfirm: (remark) => {
                            if (!remark) {
                                Swal.showValidationMessage('You need to provide a remark to proceed!');
                                return false; // Prevents closing of the modal
                            }
                            return remark;
                        }
                    }).then((remarkResult) => {
                        if (remarkResult.isConfirmed) {
                            const remark = remarkResult.value;
    
                             // Ambil data dari atribut butang
                            const applicationId = clickedButton.data('application-id');
                            const staffId = clickedButton.data('admin-staff-id');
    
                            // Debug data yang akan dihantar
                            console.log("Application ID:", applicationId);
                            console.log("Staff Id:", staffId);
                            console.log("Remark:", remark);

                            Swal.fire({
                                title: 'Rejecting Application...',
                                text: 'Please wait while the application is being rejected.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
    
                            // Hantar permintaan AJAX ke server
                            $.ajax({
                                url: 'ra-application-reject.php',
                                method: 'POST',
                                data: {
                                    applicationId: applicationId,
                                    remark: remark,
                                    staffId: staffId,
                                },
                                dataType: 'json',
                                success: function (response) {
                                    console.log("AJAX success response:", response);
    
                                    if (response.success) {
                                        Swal.fire(
                                            'Rejected!',
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
                                        );
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error("AJAX Error:", xhr.responseText || error);
                                    Swal.fire(
                                        'Error!',
                                        'An error occurred during rejection. Please check the console for details.',
                                        'error'
                                    );
                                }
                            });
                        } else {
                            Swal.fire(
                                'Cancelled',
                                'You need to provide a remark to reject the research assistant application.',
                                'info'
                            );
                        }
                    }).catch((error) => {
                        console.error("Error during remark modal handling:", error);
                        Swal.fire(
                            'Error!',
                            'An unexpected error occurred while handling the remarks. Please try again.',
                            'error'
                        );
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Research assistant application rejection has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '.verifyApplication', function () {
            const clickedButton = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "Once verified, you cannot edit this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, verify it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.value) { // Jika pengguna mengesahkan
                     // Ambil data dari atribut butang
                    const applicationId = clickedButton.data('application-id');
                    const staffId = clickedButton.data('admin-staff-id');
    
                    // Debug data yang akan dihantar
                    console.log("Application Fee ID:", applicationId);

                    Swal.fire({
                        title: 'Verifying Application...',
                        text: 'Please wait while the application is being verified.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'ra-application-verify.php',
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
                                    'Submitted!',
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
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            Swal.fire(
                                'Error!',
                                'An error occurred during submission. Please check the console for details.',
                                'error'
                            );
                        }
                    });
                } else {
                    Swal.fire(
                        'Cancelled',
                        'Research assistant application verification has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>
