<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id = $_GET['id']; // Get the ID from the URL parameter

    // Query to fetch data from the vendor table
    $query = "SELECT * FROM vendor WHERE id = '$id'";
    $result = mysqli_query($db, $query);

    // Fetch data into variables
    while ($row = mysqli_fetch_array($result)) {
        $email = $row['email'];
        $company_name = $row['company_name'];
        $registered_address = $row['registered_address'];
        $mailing_address = $row['mailing_address'];
        $ssm_no = $row['ssm_no'];
        $tin_no = $row['tin_no'];
        $website = $row['website'];
        $org_type = $row['org_type'];
        $iban_swift = $row['iban_swift'];
        $contact_name = $row['contact_name'];
        $contact_position = $row['contact_position'];
        $contact_phone = $row['contact_phone'];
        $contact_email = $row['contact_email'];
        $bank_name = $row['bank_name'];
        $bank_account = $row['bank_account'];
        $bank_address = $row['bank_address'];
        $bank_statement_file = $row['bank_statement_file'];
        $ssm_file = $row['ssm_file'];
        $created_at = $row['created_at'];
        $status = $row['status'];
        $password = $row['password'];
        $rejection_reason = $row['rejection_reason'];
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms - Vendor Information</title>
    <!-- This page CSS -->
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">
    
    <style>
        .vendor-info-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .vendor-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 25px;
            border-radius: 8px 8px 0 0;
            position: relative;
        }
        
        .vendor-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            border-radius: 8px 8px 0 0;
        }
        
        .vendor-header-content {
            position: relative;
            z-index: 1;
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-verified {
            background: #28a745;
            color: white;
        }
        
        .status-pending {
            background: #fd7e14;
            color: white;
        }
        
        .status-reject {
            background: #dc3545;
            color: white;
        }
        
        .info-section {
            padding: 0;
        }
        
        .section-header {
            background: #f8f9fa;
            padding: 15px 25px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-header i {
            color: #28a745;
            font-size: 18px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 0;
        }
        
        .info-item {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f3f4;
            border-right: 1px solid #f1f3f4;
            transition: background-color 0.3s ease;
        }
        
        .info-item:hover {
            background-color: #f8fff8;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #495057;
            font-size: 15px;
            word-wrap: break-word;
        }
        
        .info-value.empty {
            color: #adb5bd;
            font-style: italic;
        }
        
        .document-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border: 1px solid #28a745;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .document-link:hover {
            background: #28a745;
            color: white;
            text-decoration: none;
        }
        
        .document-link i {
            font-size: 14px;
        }
        
        .action-buttons {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-custom {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-verify {
            background: #28a745;
            color: white;
            border: 1px solid #28a745;
        }
        
        .btn-verify:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }
        
        .btn-reject:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            border: 1px solid #6c757d;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .company-logo i {
            font-size: 24px;
            color: white;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .company-type {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .created-date {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .status-badge {
                position: static;
                margin-top: 15px;
                display: inline-block;
            }
            
            .vendor-header {
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body class="skin-green fixed-layout">
    <!-- Preloader -->
    <?php include 'include/preloader.php'; ?>
    
    <div id="main-wrapper">
        <!-- Topbar header -->
        <?php include 'include/topbar.php'; ?>
        
        <!-- Left Sidebar -->
        <?php include 'include/left_sidebar.php'; ?>
        
        <!-- Page wrapper -->
        <div class="page-wrapper">
            <!-- Container fluid -->
            <div class="container-fluid">
                <!-- Breadcrumb -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Vendor Information</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Vendor Management</a></li>
                                <li class="breadcrumb-item active">Vendor Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="row">
                    <div class="col-12">
                        <div class="vendor-info-card">
                            <!-- Vendor Header -->
                            <div class="vendor-header">
                                <div class="vendor-header-content">
                                    <div class="company-logo">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
                                    <div class="company-type"><?php echo htmlspecialchars($org_type); ?></div>
                                    <div class="created-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        Registered: <?php echo date('F j, Y', strtotime($created_at)); ?>
                                    </div>
                                </div>
                                <div class="status-badge status-<?php echo strpos($status, 'Reject') === 0 ? 'reject' : strtolower($status); ?>">
                                    <?php echo strpos($status, 'Reject') === 0 ? 'Reject' : htmlspecialchars($status); ?>
                                </div>
                            </div>
                            
                            <!-- Company Information -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-info-circle"></i>
                                    Company Information
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Company Email</div>
                                        <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">SSM Registration No.</div>
                                        <div class="info-value"><?php echo htmlspecialchars($ssm_no); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">TIN Number</div>
                                        <div class="info-value"><?php echo $tin_no ? htmlspecialchars($tin_no) : '<span class="empty">Not provided</span>'; ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Website</div>
                                        <div class="info-value">
                                            <?php if($website): ?>
                                                <a href="<?php echo htmlspecialchars($website); ?>" target="_blank" class="document-link">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    Visit Website
                                                </a>
                                            <?php else: ?>
                                                <span class="empty">Not provided</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Information -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Address Information
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Registered Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($registered_address); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Mailing Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($mailing_address); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-user-tie"></i>
                                    Contact Person
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Contact Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($contact_name); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Position</div>
                                        <div class="info-value"><?php echo htmlspecialchars($contact_position); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value"><?php echo htmlspecialchars($contact_phone); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($contact_email); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Banking Information -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-university"></i>
                                    Banking Information
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Bank Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($bank_name); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Account Number</div>
                                        <div class="info-value"><?php echo htmlspecialchars($bank_account); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Bank Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($bank_address); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">IBAN/SWIFT Code</div>
                                        <div class="info-value"><?php echo $iban_swift ? htmlspecialchars($iban_swift) : '<span class="empty">Not provided</span>'; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Documents -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-file-alt"></i>
                                    Documents
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">SSM Certificate</div>
                                        <div class="info-value">
                                            <?php if($ssm_file): ?>
                                                <a href="../../registration-documents/vendor-file/<?php echo htmlspecialchars($ssm_file); ?>" target="_blank" class="document-link">
                                                    <i class="fas fa-download"></i>
                                                    Download SSM Certificate
                                                </a>
                                            <?php else: ?>
                                                <span class="empty">Not uploaded</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Bank Statement</div>
                                        <div class="info-value">
                                            <?php if($bank_statement_file): ?>
                                                <a href="../../registration-documents/vendor-file/<?php echo htmlspecialchars($bank_statement_file); ?>" target="_blank" class="document-link">
                                                    <i class="fas fa-download"></i>
                                                    Download Bank Statement
                                                </a>
                                            <?php else: ?>
                                                <span class="empty">Not uploaded</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include 'include/footer.php'; ?>
        
        <!-- Logout Modal -->
        <?php include 'include/logoutmodal.php'; ?>
    </div>
    
    <!-- Scripts -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <script src="dist/js/waves.js"></script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
</body>

</html>