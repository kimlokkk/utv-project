<?php
ob_start();
include 'db_connect/db_connect.php';
include 'function/function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'register/include/meta.php'; ?>
    <title>IProms</title>
    <!-- page css -->
    <link href="dist/css/pages/login-register-lock.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',sans-serif;background:#1e3a5f;overflow-x:hidden;">
    <!-- Animated Background -->
    <div style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:1;">
        <div style="position:absolute;top:18%;left:8%;width:280px;height:280px;background:radial-gradient(circle,rgba(79,134,198,0.22),transparent);border-radius:50%;animation:float1 6s ease-in-out infinite;"></div>
        <div style="position:absolute;top:58%;right:12%;width:220px;height:220px;background:radial-gradient(circle,rgba(100,149,237,0.20),transparent);border-radius:50%;animation:float2 8s ease-in-out infinite;"></div>
        <div style="position:absolute;bottom:8%;left:32%;width:160px;height:160px;background:radial-gradient(circle,rgba(70,130,180,0.20),transparent);border-radius:50%;animation:float3 7s ease-in-out infinite;"></div>
    </div>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'register/include/preloader.php'; ?>
    <!-- ============================================================== -->
    <!-- Main wrapper -->
    <!-- ============================================================== -->
    <section id="wrapper" style="position:relative;z-index:2;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
        <!-- Split Screen Layout -->
        <div class="login-shell" style="display:flex;max-width:1080px;width:100%;background:rgba(255,255,255,0.04);backdrop-filter:blur(22px);border-radius:22px;overflow:hidden;border:1px solid rgba(255,255,255,0.12);box-shadow:0 24px 70px rgba(0,0,0,0.22);">
            <!-- Left Side - Branding -->
            <div class="brand-panel" style="flex:0.95;background:linear-gradient(135deg,rgba(79,134,198,0.18),rgba(100,149,237,0.12));padding:46px 38px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;min-height:540px;">
                <div style="display:flex;align-items:center;justify-content:center;gap:22px;margin-bottom:34px;flex-wrap:wrap;">
                    <div style="width:150px;height:86px;background:rgba(255,255,255,0.10);border:1px solid rgba(255,255,255,0.14);border-radius:18px;display:flex;align-items:center;justify-content:center;padding:14px;">
                        <img src="../assets/images/Logo.png" alt="UTV Logo" style="max-width:100%;max-height:100%;object-fit:contain;">
                    </div>
                    <div style="width:150px;height:86px;background:rgba(255,255,255,0.10);border:1px solid rgba(255,255,255,0.14);border-radius:18px;display:flex;align-items:center;justify-content:center;padding:14px;">
                        <img src="../assets/images/UiTM-Logo.png" alt="UiTM Logo" style="max-width:100%;max-height:100%;object-fit:contain;">
                    </div>
                </div>
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.10);border:1px solid rgba(255,255,255,0.14);color:rgba(255,255,255,0.92);font-size:13px;font-weight:600;border-radius:999px;padding:8px 14px;margin-bottom:18px;">
                    <span style="width:8px;height:8px;background:#8fd3ff;border-radius:50%;display:inline-block;"></span>
                    UiTM Technoventure
                </div>
                <h1 style="color:white;font-size:34px;font-weight:800;margin:0 0 16px;line-height:1.18;letter-spacing:-0.6px;">
                    Welcome to<br>IProms Platform
                </h1>
                <p style="color:rgba(255,255,255,0.78);font-size:16px;line-height:1.65;max-width:390px;margin:0;">
                    Manage project registration, applications and approvals in one centralised platform.
                </p>
            </div>
            <!-- Right Side - Login Form -->
            <div class="form-panel" style="flex:1;background:rgba(255,255,255,0.97);padding:46px 48px;display:flex;flex-direction:column;justify-content:center;">
                <div style="margin-bottom:30px;">
                    <h2 style="color:#1f2937;font-size:28px;font-weight:800;margin:0 0 8px;letter-spacing:-0.4px;">Sign In</h2>
                    <p style="color:#6b7280;font-size:15px;margin:0;">Access your account to continue</p>
                </div>
                <form class="form-horizontal form-material" method="POST">
                    <!-- Email Input -->
                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="color:#374151;font-weight:600;margin-bottom:8px;display:block;">Email Address</label>
                        <div style="position:relative;">
                            <input class="form-control" type="text" name="email" required placeholder="Enter your email" style="width:100%;height:54px;border:1.5px solid #e5e7eb;border-radius:13px;padding:0 50px 0 18px;font-size:15px;background:white;transition:all 0.3s ease;">
                            <div style="position:absolute;right:18px;top:50%;transform:translateY(-50%);color:#6b7280;">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Password Input -->
                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="color:#374151;font-weight:600;margin-bottom:8px;display:block;">Password</label>
                        <div style="position:relative;">
                            <input class="form-control" type="password" name="password" id="passwordField" required placeholder="Enter your password" style="width:100%;height:54px;border:1.5px solid #e5e7eb;border-radius:13px;padding:0 50px 0 18px;font-size:15px;background:white;transition:all 0.3s ease;">
                            <div id="passwordToggle" style="position:absolute;right:18px;top:50%;transform:translateY(-50%);color:#6b7280;cursor:pointer;user-select:none;transition:color 0.2s ease;" onclick="togglePassword()">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Role Selection -->
                    <div class="form-group" style="margin-bottom:26px;">
                        <label style="color:#374151;font-weight:600;margin-bottom:8px;display:block;">Select Your Role</label>
                        <select class="form-control" name="login_option" required style="width:100%;height:54px;border:1.5px solid #e5e7eb;border-radius:13px;padding:0 45px 0 18px;font-size:15px;background:white;appearance:none;cursor:pointer;background-image:url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%236b7280\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>');background-repeat:no-repeat;background-position:right 15px center;background-size:12px;">
                            <option disabled selected>Choose your role</option>
                            <option value="Consultant">Consultant</option>
                            <option value="Research Assistant">Research Assistant</option>
                            <option value="Admin">Administrator</option>
                            <option value="Vendor">Vendor</option>
                        </select>
                    </div>
                    <!-- Login Button -->
                    <button class="btn" name="btn_login" type="submit" style="width:100%;height:54px;background:linear-gradient(135deg,#346ea8,#5b93df);color:white;border:none;border-radius:13px;font-size:16px;font-weight:700;cursor:pointer;transition:all 0.3s ease;margin-bottom:14px;">
                        Sign In →
                    </button>
                    <!-- Register Button -->
                    <a href="https://utv.domei.io/register" style="text-decoration:none;">
                        <button type="button" style="width:100%;height:54px;background:transparent;color:#346ea8;border:1.5px solid #346ea8;border-radius:13px;font-size:16px;font-weight:700;cursor:pointer;transition:all 0.3s ease;margin-bottom:24px;">
                            Create New Account
                        </button>
                    </a>
                    <!-- Links -->
                    <div style="text-align:center;">
                        <div style="margin-bottom:12px;">
                            <a href="forgot_password.php" id="to-recover" style="color:#6b7280;text-decoration:none;font-size:14.5px;transition:color 0.3s ease;display:inline-flex;align-items:center;gap:8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <circle cx="12" cy="16" r="1"></circle>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                Forgot your password?
                            </a>
                        </div>
                        <div>
                            <a href="contact_us.php" style="color:#6b7280;text-decoration:none;font-size:14.5px;transition:color 0.3s ease;display:inline-flex;align-items:center;gap:8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                Need help? Contact us
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Custom Styles -->
    <style>
        @keyframes float1 {
            0%,100% { transform:translateY(0px) rotate(0deg); }
            50% { transform:translateY(-20px) rotate(10deg); }
        }
        @keyframes float2 {
            0%,100% { transform:translateY(0px) rotate(0deg); }
            50% { transform:translateY(-30px) rotate(-10deg); }
        }
        @keyframes float3 {
            0%,100% { transform:translateY(0px) rotate(0deg); }
            50% { transform:translateY(-15px) rotate(5deg); }
        }
    
        html,
        body {
            width:100%;
            min-height:100%;
        }
    
        body {
            overflow-x:hidden;
        }
    
        #wrapper {
            min-height:100vh;
            min-height:100dvh;
        }
    
        .login-shell {
            width:100%;
            max-width:1080px;
        }
    
        .brand-panel,
        .form-panel {
            box-sizing:border-box;
        }
    
        .form-control:focus {
            border-color:#346ea8 !important;
            box-shadow:0 0 0 4px rgba(52,110,168,0.10) !important;
            outline:none;
        }
    
        button:hover {
            transform:translateY(-1px);
            box-shadow:0 10px 25px rgba(0,0,0,0.13);
        }
    
        #passwordToggle:hover {
            color:#346ea8 !important;
        }
    
        a:hover {
            color:#346ea8 !important;
        }
    
        /* ==========================================================
           Large desktop screen - 1440px and above
           ========================================================== */
        @media (min-width: 1440px) {
            #wrapper {
                padding:32px !important;
            }
    
            .login-shell {
                max-width:1180px !important;
            }
    
            .brand-panel {
                min-height:600px !important;
                padding:56px 48px !important;
            }
    
            .form-panel {
                padding:56px 58px !important;
            }
        }
    
        /* ==========================================================
           Normal laptop / desktop - 1200px to 1439px
           ========================================================== */
        @media (min-width: 1200px) and (max-width: 1439px) {
            #wrapper {
                padding:24px !important;
            }
    
            .login-shell {
                max-width:1040px !important;
            }
    
            .brand-panel {
                min-height:540px !important;
                padding:42px 34px !important;
            }
    
            .form-panel {
                padding:42px 44px !important;
            }
        }
    
        /* ==========================================================
           13 inch laptop / compact desktop - 1024px to 1199px
           ========================================================== */
        @media (min-width: 1024px) and (max-width: 1199px) {
            #wrapper {
                padding:18px !important;
            }
    
            .login-shell {
                max-width:940px !important;
                border-radius:20px !important;
            }
    
            .brand-panel {
                flex:0.9 !important;
                min-height:500px !important;
                padding:34px 28px !important;
            }
    
            .form-panel {
                flex:1.05 !important;
                padding:34px 34px !important;
            }
    
            .brand-panel h1 {
                font-size:29px !important;
                margin-bottom:12px !important;
            }
    
            .brand-panel p {
                font-size:14.5px !important;
                line-height:1.55 !important;
            }
    
            .form-panel h2 {
                font-size:25px !important;
            }
    
            .form-panel > div:first-child {
                margin-bottom:22px !important;
            }
    
            .form-group {
                margin-bottom:16px !important;
            }
    
            .form-control,
            .form-panel button {
                height:49px !important;
                font-size:14.5px !important;
                border-radius:12px !important;
            }
    
            .form-panel a button {
                margin-bottom:18px !important;
            }
        }
    
        /* ==========================================================
           11 inch screen / small laptop - 900px to 1023px
           Keep 2-column but make everything compact
           ========================================================== */
        @media (min-width: 900px) and (max-width: 1023px) {
            #wrapper {
                align-items:center !important;
                padding:14px !important;
            }
    
            .login-shell {
                max-width:880px !important;
                border-radius:18px !important;
            }
    
            .brand-panel {
                flex:0.82 !important;
                min-height:470px !important;
                padding:28px 22px !important;
            }
    
            .form-panel {
                flex:1.08 !important;
                padding:28px 30px !important;
            }
    
            .brand-panel h1 {
                font-size:26px !important;
                line-height:1.16 !important;
                margin-bottom:10px !important;
            }
    
            .brand-panel p {
                font-size:13.8px !important;
                line-height:1.5 !important;
                max-width:320px !important;
            }
    
            .brand-panel > div:first-child {
                gap:14px !important;
                margin-bottom:22px !important;
            }
    
            .brand-panel > div:first-child > div {
                width:120px !important;
                height:70px !important;
                padding:10px !important;
                border-radius:14px !important;
            }
    
            .brand-panel > div:nth-child(2) {
                font-size:12px !important;
                padding:7px 12px !important;
                margin-bottom:14px !important;
            }
    
            .form-panel h2 {
                font-size:24px !important;
            }
    
            .form-panel p {
                font-size:14px !important;
            }
    
            .form-panel > div:first-child {
                margin-bottom:20px !important;
            }
    
            .form-group {
                margin-bottom:14px !important;
            }
    
            .form-group label {
                font-size:13.5px !important;
                margin-bottom:6px !important;
            }
    
            .form-control,
            .form-panel button {
                height:47px !important;
                font-size:14px !important;
                border-radius:11px !important;
            }
    
            .form-panel a button {
                margin-bottom:16px !important;
            }
    
            .form-panel a {
                font-size:13.5px !important;
            }
        }
    
        /* ==========================================================
           Tablet / small screen - below 900px
           Stack layout
           ========================================================== */
        @media (max-width: 899px) {
            #wrapper {
                align-items:flex-start !important;
                padding:18px !important;
            }
    
            .login-shell {
                flex-direction:column !important;
                max-width:620px !important;
                border-radius:20px !important;
            }
    
            .brand-panel {
                min-height:auto !important;
                padding:32px 26px !important;
            }
    
            .form-panel {
                padding:32px 26px !important;
            }
    
            .brand-panel h1 {
                font-size:28px !important;
            }
    
            .brand-panel p {
                font-size:14.5px !important;
            }
    
            .brand-panel > div:first-child {
                margin-bottom:24px !important;
            }
        }
    
        /* ==========================================================
           Mobile - below 576px
           ========================================================== */
        @media (max-width: 575px) {
            #wrapper {
                padding:12px !important;
            }
    
            .login-shell {
                border-radius:18px !important;
            }
    
            .brand-panel {
                padding:26px 18px !important;
            }
    
            .form-panel {
                padding:26px 18px !important;
            }
    
            .brand-panel h1 {
                font-size:24px !important;
            }
    
            .brand-panel p {
                font-size:13.5px !important;
                line-height:1.5 !important;
            }
    
            .brand-panel > div:first-child {
                gap:12px !important;
                margin-bottom:20px !important;
            }
    
            .brand-panel > div:first-child > div {
                width:118px !important;
                height:68px !important;
                padding:10px !important;
                border-radius:14px !important;
            }
    
            .form-panel h2 {
                font-size:24px !important;
            }
    
            .form-panel > div:first-child {
                margin-bottom:22px !important;
            }
    
            .form-control,
            .form-panel button {
                height:50px !important;
                font-size:14px !important;
            }
        }
    
        /* ==========================================================
           Short height laptop screen
           Example: 1366x768 / 1280x720
           ========================================================== */
        @media (max-height: 760px) and (min-width: 900px) {
            #wrapper {
                align-items:center !important;
                padding-top:12px !important;
                padding-bottom:12px !important;
            }
    
            .brand-panel {
                min-height:460px !important;
                padding-top:26px !important;
                padding-bottom:26px !important;
            }
    
            .form-panel {
                padding-top:26px !important;
                padding-bottom:26px !important;
            }
    
            .brand-panel h1 {
                font-size:26px !important;
                margin-bottom:10px !important;
            }
    
            .brand-panel p {
                font-size:13.5px !important;
                line-height:1.45 !important;
            }
    
            .brand-panel > div:first-child {
                margin-bottom:18px !important;
            }
    
            .brand-panel > div:first-child > div {
                width:118px !important;
                height:66px !important;
                padding:9px !important;
            }
    
            .form-panel > div:first-child {
                margin-bottom:18px !important;
            }
    
            .form-group {
                margin-bottom:12px !important;
            }
    
            .form-group label {
                margin-bottom:5px !important;
                font-size:13.5px !important;
            }
    
            .form-control,
            .form-panel button {
                height:45px !important;
                font-size:14px !important;
            }
    
            .form-panel a button {
                margin-bottom:14px !important;
            }
        }
    
        /* ==========================================================
           Very short height screen
           Let page scroll instead of content being trapped
           ========================================================== */
        @media (max-height: 620px) and (min-width: 900px) {
            #wrapper {
                align-items:flex-start !important;
                min-height:auto !important;
                padding-top:14px !important;
                padding-bottom:14px !important;
            }
    
            body {
                overflow-y:auto !important;
            }
        }
    </style>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!--Custom JavaScript -->
    <script type="text/javascript">
        $(function() {
            $(".preloader").fadeOut();
        });
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
        // ============================================================== 
        // Login and Recover Password 
        // ============================================================== 
        $('#to-recover').on("click", function() {
            $("#loginform").slideUp();
            $("#recoverform").fadeIn();
        });
        // Password toggle functionality
        function togglePassword() {
            const passwordField = document.getElementById('passwordField');
            const passwordToggle = document.getElementById('passwordToggle');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordToggle.innerHTML = `
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                `;
            } else {
                passwordField.type = 'password';
                passwordToggle.innerHTML = `
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                `;
            }
        }
    </script>
</body>
</html>