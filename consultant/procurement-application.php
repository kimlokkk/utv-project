<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id                 =   $_GET['projectId'];
    $query              =   "SELECT * FROM project WHERE id = '$id' ";  
    $result             =   mysqli_query($db, $query);
    while($row          =   mysqli_fetch_array($result))  
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
    
    $leader_query = "SELECT email FROM uitm_staff WHERE id = '$leader_id' LIMIT 1";
    $leader_result = mysqli_query($db, $leader_query);
    if ($leader_result && mysqli_num_rows($leader_result) > 0) {
        $leader_row = mysqli_fetch_assoc($leader_result);
        $leader_email = $leader_row['email'];
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
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                        <h4 class="text-themecolor">Procurement Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Procurement Application</li>
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
                        <form id="procurementForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="member_id" value="<?php echo $userData['id']; ?>">
                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Number</strong></label>
                                                <input type="text" name="project_number" value="<?php echo $project_no; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Title</strong></label>
                                                <input type="text" name="project_title" value="<?php echo $project_title; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Leader Name</strong></label>
                                                <input type="text" name="project_leader_name" value="<?php echo $project_leader; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Leader Email Address</strong></label>
                                                <input type="email" name="project_leader_email" value="<?php echo $leader_email; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Procurement Guideline Selection -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Procurement Guideline</h3>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">Procurement Value</th>
                                                    <th class="text-center">Requirement</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Less than RM10,000</td>
                                                    <td>
                                                        <ul class="m-b-0">
                                                            <li>Claims or invoice</li>
                                                            <li>Minimum 1 quotation</li>
                                                        </ul>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>RM10,001 – RM50,000</td>
                                                    <td>Minimum 1 quotation</td>
                                                </tr>
                                                <tr>
                                                    <td>RM50,001 – RM100,000</td>
                                                    <td>Minimum 2 quotations</td>
                                                </tr>
                                                <tr>
                                                    <td>More than RM100,000</td>
                                                    <td>
                                                        <ul class="m-b-0">
                                                            <li>Pre-approved by UTV Committee</li>
                                                            <li>Minimum 2 quotations</li>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Note:</strong> Pre-approved means consultants must obtain vendor approval from the <strong>UTV committee</strong> before proceeding.
                                    </div>
                                </div>
                            </div>
                            <!-- Goods or Services -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Goods or Services</h3>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label><strong>Choose Type</strong></label>
                                        <select class="form-control" name="goods_or_services" required>
                                            <option value="" disabled selected>Select Option</option>
                                            <option value="Goods">Goods</option>
                                            <option value="Services">Services</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Application Type</strong></label>
                                        <select class="form-control" id="applicationType" name="application_type" required>
                                            <option value="" disabled selected>Select Option</option>
                                            <option value="Purchase Order Application">Purchase Order Application</option>
                                            <option value="Vendor Payment">Vendor Payment</option>
                                        </select>
                                    </div>
                                    <div id="vendorPaymentOptions" style="display: none;">
                                        <label><strong>Vendor Payment Options</strong></label>
                                        <select class="form-control" name="payment_type">
                                            <option value="" disabled selected>Select Option</option>
                                            <option value="PO Vendor Payment">PO Vendor Payment</option>
                                            <option value="Vendor Direct Payment">Vendor Direct Payment</option>
                                        </select>
                                    </div>
                                    <div class="m-t-20" id="poNumberField" style="display: none;">
                                        <label><strong>PO Number</strong></label>
                                        <input type="text" name="po_number" class="form-control" placeholder="Enter PO Number">
                                    </div>
                                </div>
                            </div>
                            <div id="purchaseOrderForm" class="form-section" style="display:none;">
                                <!-- Vendor Selection -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Select Vendor</h3>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Select Vendor</strong></label>
                                            <div>
                                                <select id="vendorSelectPO" name="vendor_id" class="select2 form-control custom-select" required>
                                                    <option value="" disabled selected>Select vendor</option>
                                                    <?php
                                                        $vendorQuery = "SELECT id, company_name, ssm_no FROM vendor WHERE status = 'Verified' ORDER BY company_name ASC";
                                                        $vendorResult = mysqli_query($db, $vendorQuery);
                                                        while ($vendor = mysqli_fetch_assoc($vendorResult)) {
                                                            echo '<option value="' . $vendor['id'] . '">' . htmlspecialchars($vendor['company_name']) . ' - ' . $vendor['ssm_no'] . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Evaluation Criteria -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Evaluation Criteria</h3>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Select Criteria</strong></label>
                                            <div class="checkbox">
                                                <?php
                                                $criteria = [
                                                    'According to the specifications',
                                                    'Not according to specification',
                                                    'Reasonable price',
                                                    'The lowest price',
                                                    'Price not reasonable',
                                                    'Acceptable Quality product/service',
                                                    'Minimum delivery period',
                                                    'Bad quality product/service',
                                                    'Higher price',
                                                    'Longer delivery period',
                                                    'Good vendor track record',
                                                    'No vendor track record',
                                                    'No warranty',
                                                    'Provide warranty',
                                                    'Quotation not accurate',
                                                    'Recommended vendor',
                                                    'UTV listed vendor',
                                                    'UTV new vendor',
                                                    'Provide catalog',
                                                    'Not recommended vendor'
                                                ];
                                                foreach ($criteria as $index => $criterion) {
                                                    echo "<label><input type='checkbox' name='criteria[]' value='{$criterion}'> {$criterion}</label><br>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional Details -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Additional Details</h3>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Type of Goods/Services</strong></label>
                                            <input type="text" name="goods_service_type" class="form-control" placeholder="Enter Goods/Services Type">
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Reason for Purchase</strong></label>
                                            <textarea name="purchase_reason" class="form-control" rows="3" placeholder="Enter Reason for Purchase"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Location</strong></label>
                                            <input type="text" name="location" class="form-control" placeholder="Enter Location">
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Quotation Document</strong></label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="quotation_file" class="custom-file-input" id="inputGroupFile01">
                                                    <label class="custom-file-label" for="inputGroupFile01">Choose File</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="vendorPaymentForm" class="form-section" style="display:none;">
                                <!-- Vendor Details -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Select Vendor</h3>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Select Vendor</strong></label>
                                            <div>
                                                <select id="vendorSelectVP" name="vendor_id" class="select2 form-control custom-select" required>
                                                    <option value="" disabled selected>Select vendor</option>
                                                    <?php
                                                        $vendorQuery = "SELECT id, company_name, ssm_no FROM vendor WHERE status = 'Verified' ORDER BY company_name ASC";
                                                        $vendorResult = mysqli_query($db, $vendorQuery);
                                                        while ($vendor = mysqli_fetch_assoc($vendorResult)) {
                                                            echo '<option value="' . $vendor['id'] . '">' . htmlspecialchars($vendor['company_name']) . ' - ' . $vendor['ssm_no'] . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Evaluation Criteria -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Evaluation Criteria</h3>
                                    <div class="card-body">
                                        <label><strong>Criteria Evaluation</strong></label>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Criteria</th>
                                                    <th>1</th>
                                                    <th>2</th>
                                                    <th>3</th>
                                                    <th>4</th>
                                                    <th>5</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Pricing and Term of Payment</td>
                                                    <?php for ($i = 1; $i <= 5; $i++) {
                                                        echo "<td><input type='radio' name='pricing_term_payment' value='{$i}'></td>";
                                                    } ?>
                                                </tr>
                                                <tr>
                                                    <td>Delivery Time</td>
                                                    <?php for ($i = 1; $i <= 5; $i++) {
                                                        echo "<td><input type='radio' name='delivery_time' value='{$i}'></td>";
                                                    } ?>
                                                </tr>
                                                <tr>
                                                    <td>Products/Services Quality</td>
                                                    <?php for ($i = 1; $i <= 5; $i++) {
                                                        echo "<td><input type='radio' name='product_quality' value='{$i}'></td>";
                                                    } ?>
                                                </tr>
                                                <tr>
                                                    <td>Response Time</td>
                                                    <?php for ($i = 1; $i <= 5; $i++) {
                                                        echo "<td><input type='radio' name='response_time' value='{$i}'></td>";
                                                    } ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="form-group">
                                            <label><strong>Approve for Payment?</strong></label><br>
                                            <div class="m-r-10">
                                                <label><input type="radio" name="approve_payment" value="Yes" required> Yes</label>
                                            </div>
                                            <label><input type="radio" name="approve_payment" value="No" required> No</label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Attachments -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Attachments</h3>
                                    <div class="card-body">
                                        <div class="form-group" id="goodsAttachment" style="display: none;">
                                            <label><strong>Goods Received Notes Form</strong></label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="goods_received_notes" class="custom-file-input" id="inputGroupFile02">
                                                    <label class="custom-file-label" for="inputGroupFile02">Choose File</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group" id="serviceAttachment" style="display: none;">
                                            <label><strong>Work/Service Confirmation Form</strong></label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="service_confirmation_form" class="custom-file-input" id="inputGroupFile03">
                                                    <label class="custom-file-label" for="inputGroupFile03">Choose File</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Delivery Order (DO)</strong></label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="delivery_order" class="custom-file-input" id="inputGroupFile04">
                                                    <label class="custom-file-label" for="inputGroupFile04">Choose File</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Supplier Invoice</strong></label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="supplier_invoice" class="custom-file-input" id="inputGroupFile05">
                                                    <label class="custom-file-label" for="inputGroupFile05">Choose File</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional Details -->
                                <div class="card">
                                    <h3 class="card-header bg-info text-white">Additional Details</h3>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Type of Goods/Services Purchased</strong></label>
                                            <input type="text" name="goods_service_type" class="form-control" placeholder="Enter Goods/Services Type">
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Reason for Purchase</strong></label>
                                            <textarea name="purchase_reason" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Total to Pay (RM)</strong></label>
                                            <input type="text" name="total_to_pay" class="form-control" placeholder="Enter Total Payment">
                                        </div>
                                        <div class="form-group">
                                            <label><strong>% to Pay</strong></label>
                                            <input type="text" name="percentage_to_pay" class="form-control" placeholder="Enter Percentage to Pay">
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Custodian of Asset</strong></label>
                                            <input type="text" name="custodian_of_asset" class="form-control" placeholder="Enter Custodian of Asset">
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Location</strong></label>
                                            <input type="text" name="location" class="form-control" placeholder="Enter Location">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Declaration Selection -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Procurement Declaration</h3>
                                <div class="card-body">
                                    <p class="font-weight-bold">I hereby declare that:</p>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            The vendor is a registered vendor with <strong>UTV</strong>.
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            The vendor has obtained approval from the <strong>UTV committee</strong> (if applicable).
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            I have <strong>no personal interest</strong> with the vendor.
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            All the information provided above is <strong>true and correct</strong>.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="button" id="submitToProjectLeader" class="btn btn-lg btn-success"> Submit to Project Leader</button>
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
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
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
        // Tunjukkan/hide bahagian attachment berdasarkan pilihan Goods/Services
        document.querySelector("select[name='goods_or_services']").addEventListener("change", function() {
            const goodsAttachment = document.getElementById("goodsAttachment");
            const serviceAttachment = document.getElementById("serviceAttachment");
    
            if (this.value === "Goods") {
                goodsAttachment.style.display = "block";
                serviceAttachment.style.display = "none";
            } else if (this.value === "Services") {
                goodsAttachment.style.display = "none";
                serviceAttachment.style.display = "block";
            } else {
                goodsAttachment.style.display = "none";
                serviceAttachment.style.display = "none";
            }
        });
    </script>
    <script>
        // Tunjuk/hide pilihan vendor payment options
        document.querySelector("select[name='application_type']").addEventListener("change", function() {
            const vendorPaymentOptions = document.getElementById("vendorPaymentOptions");
    
            if (this.value === "Vendor Payment") {
                vendorPaymentOptions.style.display = "block";
            } else {
                vendorPaymentOptions.style.display = "none";
            }
        });
    
        document.querySelector("select[name='payment_type']").addEventListener("change", function() {
            const poNumberField = document.getElementById("poNumberField");
    
            if (this.value === "PO Vendor Payment") {
                poNumberField.style.display = "block";
            } else {
                poNumberField.style.display = "none";
            }
        });
    </script>
    <script>
        document.getElementById('applicationType').addEventListener('change', function () {
            const applicationType = this.value;
            const purchaseOrderForm = document.getElementById('purchaseOrderForm');
            const vendorPaymentForm = document.getElementById('vendorPaymentForm');
        
            // Aktifkan form yang dipilih dan disable form yang lain
            if (applicationType === 'Purchase Order Application') {
                purchaseOrderForm.style.display = 'block';
                vendorPaymentForm.style.display = 'none';
        
                // Enable semua input dalam Purchase Order Form
                toggleFormInputs(purchaseOrderForm, true);
                // Disable semua input dalam Vendor Payment Form
                toggleFormInputs(vendorPaymentForm, false);
            } else if (applicationType === 'Vendor Payment') {
                purchaseOrderForm.style.display = 'none';
                vendorPaymentForm.style.display = 'block';
        
                // Enable semua input dalam Vendor Payment Form
                toggleFormInputs(vendorPaymentForm, true);
                // Disable semua input dalam Purchase Order Form
                toggleFormInputs(purchaseOrderForm, false);
            } else {
                purchaseOrderForm.style.display = 'none';
                vendorPaymentForm.style.display = 'none';
        
                // Disable semua input
                toggleFormInputs(purchaseOrderForm, false);
                toggleFormInputs(vendorPaymentForm, false);
            }
            
            setTimeout(() => {
                $('.select2').select2();
            }, 50);
        });
        
        // Fungsi untuk toggle enable/disable semua input dalam form
        function toggleFormInputs(formElement, enable) {
            const inputs = formElement.querySelectorAll('input, select, textarea');
            for (const input of inputs) {
                input.disabled = !enable;
            }
        }
    </script>
    <script>
        $(document).on('click', '#submitToProjectLeader', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Once submitted, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons
        
                if (result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");
        
                    // Ambil borang menggunakan ID borang
                    const form = $('#procurementForm')[0]; // Gantikan #invoiceForm dengan ID borang anda
                    const formData = new FormData(form);
        
                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }
        
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'process_procurement.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        success: function (response) {
                            console.log("AJAX success response:", response);
        
                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'procurement.php'; // Alihkan ke halaman yang diinginkan
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
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Procurement application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>

</html>