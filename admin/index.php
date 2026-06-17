<?php
  
  ob_start();
  include '../db_connect/db_connect.php';
  include '../function/function.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms - Admin Portal</title>
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

<body style="margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
    
    <!-- Subtle background elements -->
    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 80px 80px; animation: slowFloat 30s infinite linear; opacity: 0.3;"></div>
    <div style="position: absolute; top: 20%; left: 10%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.1), transparent); border-radius: 50%; animation: gentleFloat 8s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: 20%; right: 15%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.08), transparent); border-radius: 50%; animation: gentleFloat2 10s ease-in-out infinite;"></div>
    
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'include/preloader.php'; ?>
    
    <!-- ============================================================== -->
    <!-- Main wrapper -->
    <!-- ============================================================== -->
    <section id="wrapper" style="width: 100%; max-width: 450px; padding: 20px; position: relative; z-index: 2;">
        
        <!-- Clean Admin Login Card -->
        <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 16px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.2);">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 30px; text-align: center;">
                
                <!-- Logo -->
                <div style="margin-bottom: 20px;">
                    <img src="../assets/images/1.-UTV_Logo_Full.png" alt="UTV Logo" width="70" height="55" style="margin-bottom: 8px;" />
                    <br/>
                    <img src="../assets/images/UiTM-Logo.png" alt="UiTM Logo" width="160" height="80" />
                </div>
                
                <!-- Title -->
                <h1 style="color: white; font-size: 24px; font-weight: 700; margin: 0 0 8px 0;">Admin Portal</h1>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 14px; margin: 0;">Secure Administrative Access</p>
                
            </div>
            
            <!-- Form -->
            <div style="padding: 40px 30px;">
                
                <form class="form-horizontal form-material" method="POST">
                    
                    <!-- Email -->
                    <div style="margin-bottom: 20px;">
                        <label style="color: #374151; font-weight: 500; font-size: 14px; margin-bottom: 6px; display: block;">Email Address</label>
                        <input class="form-control" type="text" name="email" required="" placeholder="admin@uitm.edu.my"
                               style="width: 100%; height: 48px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 16px; font-size: 15px; background: #f9fafb; transition: all 0.2s ease;">
                    </div>
                    
                    <!-- Password -->
                    <div style="margin-bottom: 25px;">
                        <label style="color: #374151; font-weight: 500; font-size: 14px; margin-bottom: 6px; display: block;">Password</label>
                        <div style="position: relative;">
                            <input class="form-control" type="password" name="password" id="adminPasswordField" required="" placeholder="Enter your password"
                                   style="width: 100%; height: 48px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 40px 0 16px; font-size: 15px; background: #f9fafb; transition: all 0.2s ease;">
                            <div id="adminPasswordToggle" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; cursor: pointer; transition: color 0.2s ease;" onclick="toggleAdminPassword()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Login Button -->
                    <button class="btn" name="btn_loginAdmin" type="submit"
                            style="width: 100%; height: 48px; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; margin-bottom: 16px;">
                        Sign In
                    </button>
                    
                    <!-- Register Button -->
                    <a href="https://utv.domei.io/register/admin.php" style="text-decoration: none; display: block;">
                        <button type="button"
                                style="width: 100%; height: 48px; background: white; color: #1e40af; border: 1px solid #1e40af; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; margin-bottom: 20px;">
                            Create Account
                        </button>
                    </a>
                    
                    <!-- Links -->
                    <div style="text-align: center; display: flex; justify-content: space-between; font-size: 14px;">
                        <a href="forgot_password.php" id="to-recover" style="color: #6b7280; text-decoration: none; transition: color 0.2s ease;">
                            Forgot Password?
                        </a>
                        <a href="contact_us.php" style="color: #6b7280; text-decoration: none; transition: color 0.2s ease;">
                            Contact Support
                        </a>
                    </div>
                    
                </form>
            </div>
        </div>
    </section>
    
    <!-- Styles -->
    <style>
        @keyframes slowFloat {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }
        
        @keyframes gentleFloat {
            0%, 100% { transform: translateY(0px); opacity: 0.3; }
            50% { transform: translateY(-20px); opacity: 0.5; }
        }
        
        @keyframes gentleFloat2 {
            0%, 100% { transform: translateY(0px); opacity: 0.2; }
            50% { transform: translateY(-15px); opacity: 0.4; }
        }
        
        .form-control:focus {
            border-color: #1e40af !important;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1) !important;
            outline: none;
            background: white !important;
        }
        
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        #adminPasswordToggle:hover {
            color: #1e40af !important;
        }
        
        a:hover {
            color: #1e40af !important;
        }
        
        @media (max-width: 768px) {
            #wrapper {
                padding: 15px;
            }
            
            #wrapper > div > div:last-child {
                padding: 30px 25px;
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
        
        // Password toggle
        function toggleAdminPassword() {
            const passwordField = document.getElementById('adminPasswordField');
            const passwordToggle = document.getElementById('adminPasswordToggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordToggle.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                `;
            } else {
                passwordField.type = 'password';
                passwordToggle.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                `;
            }
        }
    </script>
    
</body>

</html>