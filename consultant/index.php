<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';

    $userData = $_SESSION['user_data'];
    $user_id = (int) $userData['id'];
    $user_name = isset($userData['name']) && !empty($userData['name'])
        ? $userData['name']
        : (isset($userData['full_name']) ? $userData['full_name'] : 'User');

    // Helper: safely get single count result
    function getSingleCount($db, $query, $field = 'total') {
        $result = mysqli_query($db, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return (int) ($row[$field] ?? 0);
        }
        return 0;
    }

    // Helper: get badge class based on status
    function getStatusBadgeClass($status) {
        $status = strtolower(trim($status));

        if (
            strpos($status, 'rejected') !== false ||
            strpos($status, 'returned') !== false ||
            strpos($status, 'inactive') !== false
        ) {
            return 'badge-danger';
        }

        if (
            strpos($status, 'pending') !== false ||
            strpos($status, 'draft') !== false ||
            strpos($status, 'submission') !== false ||
            strpos($status, 'verification') !== false
        ) {
            return 'badge-warning';
        }

        if (
            strpos($status, 'approved') !== false ||
            strpos($status, 'appointed') !== false ||
            strpos($status, 'active') !== false ||
            strpos($status, 'completed') !== false
        ) {
            return 'badge-success';
        }

        return 'badge-secondary';
    }

    // ==============================================================
    // Dashboard overview queries
    // ==============================================================

    // Count invoice pending approval from project leader
    $pending_invoice_query = "
        SELECT COUNT(*) AS total_invoice_submitted 
        FROM invoices 
        WHERE (
                invoice_status = 'Pending Leader Review'
             OR invoice_status = 'Pending approval from project leader'
        )
          AND project_id IN (
            SELECT id FROM project WHERE leader_id = '$user_id'
        )
    ";
    $pending_invoice_count = getSingleCount($db, $pending_invoice_query, 'total_invoice_submitted');

    // Count procurement
    $pending_procurement_query = "
        SELECT COUNT(*) AS total_procurement_submitted 
        FROM procurement 
        WHERE status = 'Pending approval from project leader'
          AND project_id IN (
            SELECT id FROM project WHERE leader_id = '$user_id'
        )
    ";
    $pending_procurement_count = getSingleCount($db, $pending_procurement_query, 'total_procurement_submitted');

    // Count professional fee
    $pending_professional_query = "
        SELECT COUNT(*) AS total_professional_submitted 
        FROM professional_fee_applications 
        WHERE status = 'Pending approval from project leader'
          AND project_id IN (
            SELECT id FROM project WHERE leader_id = '$user_id'
        )
    ";
    $pending_professional_count = getSingleCount($db, $pending_professional_query, 'total_professional_submitted');

    // Count reconciliation
    $pending_reconciliation_query = "
        SELECT COUNT(*) AS total_reconciliation_submitted 
        FROM reconciliation_claim_applications 
        WHERE (
                status = 'Pending leader review'
             OR status = 'Pending approval from project leader'
        )
          AND project_id IN (
            SELECT id FROM project WHERE leader_id = '$user_id'
        )
    ";
    $pending_reconciliation_count = getSingleCount($db, $pending_reconciliation_query, 'total_reconciliation_submitted');

    // Count allowance
    $pending_allowance_query = "
        SELECT COUNT(*) AS total_allowance_submitted 
        FROM allowance_applications 
        WHERE status = 'Pending approval from project leader'
          AND project_id IN (
            SELECT id FROM project WHERE leader_id = '$user_id'
        )
    ";
    $pending_allowance_count = getSingleCount($db, $pending_allowance_query, 'total_allowance_submitted');

    // Count project funding
    $pending_project_funding_query = "
        SELECT COUNT(*) AS total_project_funding_submitted 
        FROM project_funding_assistance_applications 
        WHERE status = 'Pending approval from project leader'
          AND project_id IN (
            SELECT id FROM project WHERE leader_id = '$user_id'
        )
    ";
    $pending_project_funding_count = getSingleCount($db, $pending_project_funding_query, 'total_project_funding_submitted');

    // Count all related projects (leader or consultant/member)
    $total_projects_query = "
        SELECT COUNT(DISTINCT p.id) AS total
        FROM project p
        LEFT JOIN project_members_consultant pm ON p.id = pm.project_id
        WHERE p.leader_id = '$user_id'
           OR pm.member_id = '$user_id'
    ";
    $total_projects_count = getSingleCount($db, $total_projects_query);

    // Count approved / active projects
    $approved_projects_query = "
        SELECT COUNT(DISTINCT p.id) AS total
        FROM project p
        LEFT JOIN project_members_consultant pm ON p.id = pm.project_id
        WHERE (p.leader_id = '$user_id' OR pm.member_id = '$user_id')
          AND (
                p.project_status LIKE '%Approved%'
             OR p.project_status LIKE '%Appointed%'
          )
    ";
    $approved_projects_count = getSingleCount($db, $approved_projects_query);

    // Count projects needing attention
    $attention_projects_query = "
        SELECT COUNT(DISTINCT p.id) AS total
        FROM project p
        LEFT JOIN project_members_consultant pm ON p.id = pm.project_id
        WHERE (p.leader_id = '$user_id' OR pm.member_id = '$user_id')
          AND (
                p.project_status LIKE '%Pending%'
             OR p.project_status LIKE '%Draft%'
             OR p.project_status LIKE '%Returned%'
             OR p.project_status LIKE '%Rejected%'
          )
    ";
    $attention_projects_count = getSingleCount($db, $attention_projects_query);

    // Count RA/RO applications
    $total_raro_query = "
        SELECT COUNT(*) AS total
        FROM project_members
        WHERE leader_id = '$user_id'
    ";
    $total_raro_count = getSingleCount($db, $total_raro_query);

    // Total pending financial requests
    $total_pending_financial_count =
        $pending_invoice_count +
        $pending_procurement_count +
        $pending_professional_count +
        $pending_reconciliation_count +
        $pending_allowance_count +
        $pending_project_funding_count;

    // Total items that may need user attention
    $total_attention_count = $attention_projects_count + $total_pending_financial_count;
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

    <style>
        .dashboard-hero {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%);
            color: #fff;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.16);
        }

        .dashboard-hero .hero-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .dashboard-hero .hero-subtitle {
            font-size: 0.95rem;
            opacity: 0.95;
            margin-bottom: 0;
        }

        .dashboard-stat-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            height: 100%;
            transition: 0.2s ease;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-2px);
        }

        .dashboard-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            margin-bottom: 14px;
        }

        .icon-primary { background: #0d6efd; }
        .icon-success { background: #28a745; }
        .icon-warning { background: #ffc107; color: #212529; }
        .icon-danger  { background: #dc3545; }
        .icon-info    { background: #17a2b8; }
        .icon-dark    { background: #343a40; }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stat-value {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
            color: #1f2d3d;
        }

        .stat-note {
            font-size: 0.88rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1f2d3d;
            margin-bottom: 0.25rem;
        }

        .section-subtitle {
            font-size: 0.92rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .quick-action-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            height: 100%;
            transition: 0.2s ease;
        }

        .quick-action-card:hover {
            transform: translateY(-2px);
        }

        .quick-action-card .card-body {
            padding: 1.25rem;
        }

        .quick-action-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .quick-action-title {
            font-weight: 700;
            color: #1f2d3d;
            margin-bottom: 5px;
        }

        .quick-action-desc {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .quick-action-number {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1f2d3d;
            line-height: 1;
            margin-bottom: 10px;
        }

        .dashboard-table-card {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 22px rgba(0, 0, 0, 0.06);
        }

        .dashboard-table-card .card-header {
            border-bottom: 0;
            padding: 1rem 1.25rem;
        }

        .table-summary-badges .badge {
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 0.55em 0.8em;
            font-size: 0.78rem;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.78rem;
            border-radius: 999px;
        }

        .btn-soft-info {
            background: rgba(23, 162, 184, 0.12);
            color: #17a2b8;
            border: 1px solid rgba(23, 162, 184, 0.25);
        }

        .btn-soft-info:hover {
            background: #17a2b8;
            color: #fff;
        }

        .mini-kpi {
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 12px;
            padding: 14px 16px;
            height: 100%;
        }

        .mini-kpi .mini-label {
            font-size: 0.82rem;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .mini-kpi .mini-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 0;
        }

        .alert-dashboard {
            border-radius: 14px;
            border: 0;
        }

        .card-note {
            font-size: 0.88rem;
            color: #6c757d;
        }

        @media (max-width: 767px) {
            .dashboard-hero .hero-title {
                font-size: 1.3rem;
            }

            .stat-value {
                font-size: 1.6rem;
            }

            .quick-action-number {
                font-size: 1.4rem;
            }
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

                <!-- Declaration Modal -->
                <div class="modal fade" id="declarationModal" tabindex="-1" role="dialog" aria-labelledby="declarationModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="declarationModalLabel">Declaration</h5>
                      </div>
                      <div class="modal-body" style="max-height: 60vh; overflow-y:auto;">
                        <p>I hereby affirm that the information provided herein is true, accurate, and complete to the best of my knowledge.</p>
                        <p>I agree to be bound by and shall adhere to all rules, regulations, and directives as may be prescribed by UiTM Technoventure.</p>
                        <p>I acknowledge and accept that the registration of the project is subject to the applicable administrative fee as determined by UiTM Technoventure.</p>
                        <p>I further understand and agree that upon registration in the system, a binding agreement shall be deemed to exist between myself and UiTM Technoventure.</p>
                        <p>UiTM Technoventure reserves the absolute right to reject my application at its sole discretion and under any circumstances it deems appropriate.</p>
                        <p><strong>The Project Leader shall be responsible for the following:</strong></p>
                        <ul>
                            <li>Administering the financial operations of the project;</li>
                            <li>Supervising and managing the execution of project activities;</li>
                            <li>Ensuring the timely and successful delivery of the project to the client in accordance with the agreed terms.</li>
                        </ul>
                        <p><strong>Each team member shall be obligated to:</strong></p>
                        <ul>
                            <li>Extend full cooperation and support to the Project Leader to ensure the successful and timely completion of the project in accordance with the stipulated timeframe.</li>
                        </ul>
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="agreeDeclaration">
                            <label class="form-check-label" for="agreeDeclaration">
                                <strong>(* System requirement – Compulsory to tick)</strong> I hereby acknowledge and agree to comply with all rules, regulations, and requirements established by UiTM Technoventure.
                            </label>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="proceedConsultancy" class="btn btn-info" disabled>Proceed</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->

                <!-- ============================================================== -->
                <!-- Dashboard hero / overview header -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-hero mb-4">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-lg-7 mb-3 mb-lg-0">
                                        <div class="hero-title">Welcome back, <?php echo htmlspecialchars($user_name); ?></div>
                                        <p class="hero-subtitle">
                                            Here’s a clearer overview of your projects, applications and pending approvals so you can monitor important items faster.
                                        </p>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <div class="mini-kpi">
                                                    <div class="mini-label">Projects</div>
                                                    <p class="mini-value"><?php echo $total_projects_count; ?></p>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <div class="mini-kpi">
                                                    <div class="mini-label">Need Attention</div>
                                                    <p class="mini-value"><?php echo $total_attention_count; ?></p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mini-kpi">
                                                    <div class="mini-label">RA/RO Applications</div>
                                                    <p class="mini-value"><?php echo $total_raro_count; ?></p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mini-kpi">
                                                    <div class="mini-label">Pending Financial</div>
                                                    <p class="mini-value"><?php echo $total_pending_financial_count; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Info box -->
                <!-- ============================================================== -->
                <!-- Row -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card dashboard-stat-card">
                            <div class="card-body">
                                <div class="dashboard-stat-icon icon-primary">
                                    <i class="ti-briefcase"></i>
                                </div>
                                <div class="stat-label">Total Projects</div>
                                <div class="stat-value"><?php echo $total_projects_count; ?></div>
                                <p class="stat-note">All projects related to you as leader or team member.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card dashboard-stat-card">
                            <div class="card-body">
                                <div class="dashboard-stat-icon icon-success">
                                    <i class="ti-check-box"></i>
                                </div>
                                <div class="stat-label">Approved / Active</div>
                                <div class="stat-value"><?php echo $approved_projects_count; ?></div>
                                <p class="stat-note">Projects already approved or appointed.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card dashboard-stat-card">
                            <div class="card-body">
                                <div class="dashboard-stat-icon icon-info">
                                    <i class="ti-id-badge"></i>
                                </div>
                                <div class="stat-label">RA / RO Applications</div>
                                <div class="stat-value"><?php echo $total_raro_count; ?></div>
                                <p class="stat-note">Total applications under your projects.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card dashboard-stat-card">
                            <div class="card-body">
                                <div class="dashboard-stat-icon icon-danger">
                                    <i class="ti-alert"></i>
                                </div>
                                <div class="stat-label">Needs Attention</div>
                                <div class="stat-value"><?php echo $total_attention_count; ?></div>
                                <p class="stat-note">Combined project statuses and pending approvals requiring review.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Financial / approval summary -->
                <!-- ============================================================== -->
                <div class="row mb-3">
                    <div class="col-12">
                        <?php if ($total_pending_financial_count > 0) { ?>
                            <div class="alert alert-warning alert-dashboard mb-0">
                                <strong>Attention:</strong> You currently have
                                <strong><?php echo $total_pending_financial_count; ?></strong>
                                pending financial request(s) waiting for review.
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-success alert-dashboard mb-0">
                                <strong>Good job:</strong> There are currently no pending financial requests waiting for your approval.
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Approval centre -->
                <!-- ============================================================== -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                            <div>
                                <h5 class="section-title">Approval Centre</h5>
                                <p class="section-subtitle">Quick access to the financial applications that need your attention.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="invoice-application.php" class="text-decoration-none">
                            <div class="card quick-action-card">
                                <div class="card-body">
                                    <div class="quick-action-top">
                                        <div>
                                            <div class="dashboard-stat-icon icon-danger">
                                                <i class="ti-receipt"></i>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">Invoice</span>
                                    </div>
                                    <div class="quick-action-number"><?php echo $pending_invoice_count; ?></div>
                                    <div class="quick-action-title">Pending Invoice Application</div>
                                    <p class="quick-action-desc">Review invoice submissions waiting for project leader approval.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="procurement.php" class="text-decoration-none">
                            <div class="card quick-action-card">
                                <div class="card-body">
                                    <div class="quick-action-top">
                                        <div>
                                            <div class="dashboard-stat-icon icon-primary">
                                                <i class="ti-truck"></i>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">Procurement</span>
                                    </div>
                                    <div class="quick-action-number"><?php echo $pending_procurement_count; ?></div>
                                    <div class="quick-action-title">Pending Procurement Application</div>
                                    <p class="quick-action-desc">Check purchasing and procurement requests submitted under your projects.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="professional-fee.php" class="text-decoration-none">
                            <div class="card quick-action-card">
                                <div class="card-body">
                                    <div class="quick-action-top">
                                        <div>
                                            <div class="dashboard-stat-icon icon-info">
                                                <i class="ti-link"></i>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">Professional Fee</span>
                                    </div>
                                    <div class="quick-action-number"><?php echo $pending_professional_count; ?></div>
                                    <div class="quick-action-title">Pending Professional Fee Application</div>
                                    <p class="quick-action-desc">Monitor professional fee requests that are still awaiting approval.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="reconciliation-claim.php" class="text-decoration-none">
                            <div class="card quick-action-card">
                                <div class="card-body">
                                    <div class="quick-action-top">
                                        <div>
                                            <div class="dashboard-stat-icon icon-warning">
                                                <i class="ti-reload"></i>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">Reconciliation</span>
                                    </div>
                                    <div class="quick-action-number"><?php echo $pending_reconciliation_count; ?></div>
                                    <div class="quick-action-title">Pending Advance & Reconciliation/Claim</div>
                                    <p class="quick-action-desc">Follow up on advance and claim submissions pending project leader approval.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="allowances-wages.php" class="text-decoration-none">
                            <div class="card quick-action-card">
                                <div class="card-body">
                                    <div class="quick-action-top">
                                        <div>
                                            <div class="dashboard-stat-icon icon-success">
                                                <i class="ti-money"></i>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">Allowance & Wages</span>
                                    </div>
                                    <div class="quick-action-number"><?php echo $pending_allowance_count; ?></div>
                                    <div class="quick-action-title">Pending Allowance & Wages Application</div>
                                    <p class="quick-action-desc">See allowance and wage requests that still need your review.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <a href="project-funding.php" class="text-decoration-none">
                            <div class="card quick-action-card">
                                <div class="card-body">
                                    <div class="quick-action-top">
                                        <div>
                                            <div class="dashboard-stat-icon icon-dark">
                                                <i class="ti-blackboard"></i>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">Project Funding</span>
                                    </div>
                                    <div class="quick-action-number"><?php echo $pending_project_funding_count; ?></div>
                                    <div class="quick-action-title">Pending Project Funding Application</div>
                                    <p class="quick-action-desc">Track funding assistance submissions currently pending approval.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Project registration table -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-table-card">
                            <div class="card-header bg-info text-white">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h3 class="mb-1">Project Registration</h3>
                                        <p class="mb-0 text-white-50">Overview of all projects related to your account.</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="card-note text-white-50">Total: <?php echo $total_projects_count; ?> project(s)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-summary-badges mb-3">
                                    <span class="badge badge-success">Approved / Appointed</span>
                                    <span class="badge badge-warning">Pending / Draft</span>
                                    <span class="badge badge-danger">Rejected / Returned</span>
                                    <span class="badge badge-secondary">Other Status</span>
                                </div>

                                <div class="table-responsive">
                                    <table id="myTable3" class="table table-bordered table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Leader</th>
                                                <th>Project Source</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                // Get user ID
                                                $user_id = $userData['id'];

                                                // Query to fetch projects from both tables
                                                $query = "
                                                    SELECT DISTINCT p.*
                                                    FROM project p
                                                    LEFT JOIN project_members_consultant pm ON p.id = pm.project_id
                                                    WHERE p.leader_id = '$user_id'
                                                       OR pm.member_id = '$user_id'
                                                    ORDER BY p.project_no ASC
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
                                                    $project_source = $row['project_source'];
                                                    $project_id = $row['id']; // Assuming 'id' is the primary key for the project
                                                    $project_source = $row['project_source'];

                                                    // Determine the URL based on the project source
                                                    $info_page = ($project_source === 'Consultancy')
                                                                 ? 'consultancy-project-info.php'
                                                                 : 'training-project-info.php';

                                                    $statusClass = getStatusBadgeClass($project_status);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($project_leader); ?></td>
                                                <td><?php echo htmlspecialchars($project_source); ?></td>
                                                <td><?php echo htmlspecialchars($project_type); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars($project_status); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>"
                                                       class="btn btn-sm btn-soft-info"
                                                       title="Full Info">
                                                       Full Info
                                                    </a>
                                                </td>
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

                <!-- ============================================================== -->
                <!-- RA/RO table -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-table-card">
                            <div class="card-header bg-info text-white">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h3 class="mb-1">RA/RO Application</h3>
                                        <p class="mb-0 text-white-50">Track appointments and application status under your projects.</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="card-note text-white-50">Total: <?php echo $total_raro_count; ?> application(s)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="myTable4" class="table table-bordered table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Type</th>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Name</th>
                                                <th>IC Number</th>
                                                <th>Project Start</th>
                                                <th>Duration (Months)</th>
                                                <th>Appointment Type</th>
                                                <th>Salary/Month (RM)</th>
                                                <th>Status</th>
                                                <!--<th class="text-center">Action</th>-->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Query to fetch project members
                                            $query = "
                                                SELECT *
                                                FROM project_members
                                                WHERE leader_id = '$user_id'
                                                ORDER BY id DESC
                                            ";

                                            $result = mysqli_query($db, $query);

                                            // Check for errors in the query execution
                                            if (!$result) {
                                                // Log or display the MySQL error
                                                error_log("MySQL Query Error: " . mysqli_error($db));
                                                echo "<tr><td colspan='10'>Error fetching project members: " . mysqli_error($db) . "</td></tr>";
                                            } else {
                                                // Process the results if the query succeeded
                                                $counter = 1;
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $project_source = $row['project_source'];
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
                                                    $project_id = $row['id']; // Assuming 'id' is the primary key for the project

                                                    // Determine the URL based on the project source
                                                    $info_page = ($project_source === 'Consultancy')
                                                                 ? 'consultancy-project-info.php'
                                                                 : 'training-project-info.php';

                                                    $statusClass = getStatusBadgeClass($status);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project_source); ?></td>
                                                <td><?php echo htmlspecialchars($project_no); ?></td>
                                                <td><?php echo htmlspecialchars($project_title); ?></td>
                                                <td><?php echo htmlspecialchars($member_name); ?></td>
                                                <td><?php echo htmlspecialchars($member_ic); ?></td>
                                                <td><?php echo !empty($start_date) ? date("d F Y", strtotime($start_date)) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($duration); ?> Months</td>
                                                <td><?php echo htmlspecialchars($payment_type); ?></td>
                                                <td><?php echo is_numeric($budget) ? number_format((float)$budget, 2) : htmlspecialchars($budget); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars($status); ?>
                                                    </span>
                                                </td>
                                                <!--<td class="text-center">
                                                    <a href="<?php echo $info_page; ?>?id=<?php echo urlencode($project_id); ?>"
                                                       class="btn waves-effect waves-light btn-info assign-button"
                                                       title="Full Info">
                                                       Full Info
                                                    </a>
                                                </td>-->
                                            </tr>
                                            <?php
                                                $counter++;
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
                            <h3 class="card-header bg-info text-white">Project Financial Request</h3>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="myTable5" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Project No</th>
                                                <th>Project Title</th>
                                                <th>Project Type</th>
                                                <th>Status</th>
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
                                            </tr>
                                            <tr>
                                                <td>TC24081415001</td>
                                                <td>Efgh Project</td>
                                                <td>Webinar</td>
                                                <td>Pending Submission</td>
                                                <td class="text-center"><a href="project-info.php" class="btn waves-effect waves-light btn-info assign-button" title="Project Info">Project Info</a></td>
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
            // ==============================================================
            // DataTable: Project Registration
            // ==============================================================
            $('#myTable3').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc']],
                language: {
                    search: "Search project:",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No matching projects found",
                    info: "Showing _START_ to _END_ of _TOTAL_ projects",
                    infoEmpty: "No projects available",
                    infoFiltered: "(filtered from _MAX_ total projects)"
                }
            });

            // ==============================================================
            // DataTable: RA/RO Application
            // ==============================================================
            $('#myTable4').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[1, 'desc']],
                language: {
                    search: "Search application:",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No matching applications found",
                    info: "Showing _START_ to _END_ of _TOTAL_ applications",
                    infoEmpty: "No applications available",
                    infoFiltered: "(filtered from _MAX_ total applications)"
                }
            });

            // ==============================================================
            // Declaration modal checkbox
            // ==============================================================
            $('#agreeDeclaration').on('change', function () {
                $('#proceedConsultancy').prop('disabled', !$(this).is(':checked'));
            });
        });
    </script>
</body>

</html>
