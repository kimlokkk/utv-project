<?php

    include '../db_connect/db_connect.php';
    include '../function/function.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms | Registration Portal</title>

    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="dist/css/pages/file-upload.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        body.horizontal-nav.skin-megna.fixed-layout {
            background: #f4f7fb;
        }

        .page-wrapper {
            background:
                linear-gradient(135deg, rgba(13, 27, 62, 0.92), rgba(25, 118, 210, 0.88)),
                url('../assets/images/bg.jpg') center center / cover no-repeat;
            min-height: 100vh;
            position: relative;
        }

        .page-wrapper::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(10, 18, 35, 0.20);
            z-index: 0;
        }

        .container-fluid {
            position: relative;
            z-index: 1;
            padding-top: 40px;
            padding-bottom: 50px;
        }

        .portal-shell {
            max-width: 1200px;
            margin: 0 auto;
        }

        .top-brand-bar {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 18px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .brand-logo-box {
            background: #ffffff;
            border-radius: 14px;
            padding: 10px 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .brand-logo-box img {
            display: block;
            max-height: 68px;
            width: auto;
        }

        .brand-text small {
            display: block;
            color: rgba(255, 255, 255, 0.75);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 6px;
        }

        .brand-text h2 {
            color: #fff;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .brand-text p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 14px;
        }

        .hero-panel {
            background: #ffffff;
            border-radius: 22px;
            padding: 40px 40px 30px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
            margin-bottom: 28px;
        }

        .hero-badge {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 999px;
            background: #eaf3ff;
            color: #1459a6;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.4px;
            margin-bottom: 16px;
        }

        .hero-title {
            font-size: 34px;
            font-weight: 700;
            color: #1c2434;
            margin-bottom: 12px;
        }

        .hero-desc {
            color: #637085;
            font-size: 15px;
            line-height: 1.7;
            max-width: 850px;
            margin-bottom: 0;
        }

        .section-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 20px;
            margin: 22px 0 18px;
        }

        .register-card {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(17, 24, 39, 0.14);
            transition: all 0.25s ease;
            height: 100%;
        }

        .register-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 38px rgba(17, 24, 39, 0.18);
        }

        .register-card .card-header {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #fff;
            border: 0;
            padding: 20px 22px;
            min-height: 92px;
            display: flex;
            align-items: center;
        }

        .register-card .card-header h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.35;
        }

        .register-card .card-body {
            padding: 22px;
        }

        .register-card .card-text {
            color: #65748b;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 18px;
            min-height: 72px;
        }

        .card-meta {
            font-size: 12px;
            color: #8b97aa;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: 600;
        }

        .register-card .card-footer {
            background: #fff;
            border-top: 1px solid #eef2f7;
            padding: 20px 22px 24px;
        }

        .btn-register {
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 14px;
            background: #1a73e8;
            border: none;
            box-shadow: 0 8px 18px rgba(26, 115, 232, 0.24);
        }

        .btn-register:hover,
        .btn-register:focus {
            background: #125fc4;
        }

        .info-panel {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 18px 22px;
            color: rgba(255, 255, 255, 0.88);
            margin-top: 26px;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .info-panel strong {
            color: #ffffff;
        }

        @media (max-width: 767px) {
            .hero-panel {
                padding: 28px 22px;
            }

            .hero-title {
                font-size: 26px;
            }

            .brand-text h2 {
                font-size: 22px;
            }

            .register-card .card-text {
                min-height: auto;
            }
        }
    </style>
</head>

<body class="horizontal-nav skin-megna fixed-layout">
    <?php include 'include/preloader.php'; ?>

    <div id="main-wrapper">
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="portal-shell">

                    <!-- Top Branding -->
                    <div class="top-brand-bar">
                        <div class="brand-wrap">
                            <div class="brand-left">
                                <div class="brand-logo-box">
                                    <img src="../assets/images/Logo.png" alt="UTV Logo">
                                </div>
                                <div class="brand-logo-box">
                                    <img src="../assets/images/UiTM-Logo.png" alt="UiTM Logo">
                                </div>
                            </div>

                            <div class="brand-text">
                                <small>University Administration Portal</small>
                                <h2>IProms Registration Portal</h2>
                                <p>Professional registration access for staff, project personnel, and vendors.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero -->
                    <div class="hero-panel">
                        <span class="hero-badge">Centralised Registration Access</span>
                        <h1 class="hero-title">Select Your Registration Category</h1>
                        <p class="hero-desc">
                            Welcome to the IProms registration portal. Please choose the category that best matches your role
                            to continue with the appropriate registration process. All submitted information will be managed
                            securely in accordance with institutional requirements and privacy standards.
                        </p>
                    </div>

                    <h3 class="section-title">Available Registration Options</h3>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="card register-card">
                                <div class="card-header">
                                    <h4>UiTM Staff</h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        For current employees of Universiti Teknologi MARA who need to register under the staff access category.
                                    </p>
                                    <div class="card-meta">Internal Staff Registration</div>
                                </div>
                                <div class="card-footer">
                                    <a href="https://utv.domei.io/register/uitm-staff.php" class="d-block">
                                        <button type="button" class="btn btn-info btn-block btn-register">
                                            Proceed to Register
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="card register-card">
                                <div class="card-header">
                                    <h4>Non-UiTM Staff</h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        For external individuals or users who are not currently employed by Universiti Teknologi MARA.
                                    </p>
                                    <div class="card-meta">External User Registration</div>
                                </div>
                                <div class="card-footer">
                                    <a href="https://utv.domei.io/register/non-uitm-staff.php" class="d-block">
                                        <button type="button" class="btn btn-info btn-block btn-register">
                                            Proceed to Register
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="card register-card">
                                <div class="card-header">
                                    <h4>Research Assistant / Research Officer</h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        For project-based research assistants and research officers involved in approved academic or institutional projects.
                                    </p>
                                    <div class="card-meta">Project Personnel Registration</div>
                                </div>
                                <div class="card-footer">
                                    <a href="https://utv.domei.io/register/ra-register.php" class="d-block">
                                        <button type="button" class="btn btn-info btn-block btn-register">
                                            Proceed to Register
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="card register-card">
                                <div class="card-header">
                                    <h4>Vendor</h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        For vendors and service providers who are registering for project-related procurement or operational purposes.
                                    </p>
                                    <div class="card-meta">Vendor Registration</div>
                                </div>
                                <div class="card-footer">
                                    <a href="https://utv.domei.io/register/vendor-register.php" class="d-block">
                                        <button type="button" class="btn btn-info btn-block btn-register">
                                            Proceed to Register
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-panel">
                        <strong>Important:</strong> Please ensure you select the correct registration category before proceeding.
                        Choosing the right option will direct you to the correct form and help avoid delays during verification or approval.
                    </div>

                </div>
            </div>
        </div>

        <?php include 'include/footer.php'; ?>
    </div>

    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="dist/js/waves.js"></script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
    <script src="dist/js/custom.min.js"></script>
    <script src="dist/js/pages/jasny-bootstrap.js"></script>
</body>

</html>