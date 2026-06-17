<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id = $_GET['projectId'];

    // Guna session userData
    $userData = $_SESSION['user_data'];
    $applicant_name        = $userData['full_name'];
    $applicant_ic        = $userData['ic'];
    $applicant_email     = $userData['email'];
    $applicant_id        = $userData['id'];
    $applicatnt_staff_id = $userData['staff_id'];
    $bank_name           = $userData['bank_name'];
    $no_account          = $userData['no_account'];

    // Ambil maklumat projek sahaja
    $query = "SELECT * FROM project WHERE id = '$id'";
    $result = mysqli_query($db, $query);

    while($row = mysqli_fetch_array($result)) {
        $project_leader = $row['project_leader'];
        $project_no     = $row['project_no'];
        $project_title  = $row['project_title'];
        $leader_id      = $row['leader_id'];
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
    <link rel="stylesheet" href="../assets/node_modules/dropzone-master/dist/dropzone.css">
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .proof-dropzone {
            min-height: 92px;
            padding: 10px;
            border: 2px dashed #90a4ae;
            border-radius: 4px;
            background: #f8fbfc;
        }
        .proof-dropzone .dz-message {
            margin: 1.2em 0;
            font-size: 12px;
            color: #607d8b;
        }
        .proof-dropzone .dz-preview {
            margin: 6px;
        }
        .appendix-not-required {
            display: none;
            color: #6c757d;
            font-size: 12px;
            line-height: 1.3;
        }
    </style>
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
                        <h4 class="text-themecolor">Advance & Reconciliation/Claim Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Advance & Reconciliation/Claim Application</li>
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
                        <form id="reconciliationForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="applicant_id" value="<?php echo $applicant_id; ?>">
                            <!-- Project Details -->
                            <div class="card" id="receiptCard">
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
                                                <input type="text" name="project_leader" value="<?php echo $project_leader; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Applicant Details-->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Applicant Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Applicant Name</strong></label>
                                                <input type="text" name="applicant_name" value="<?php echo $applicant_name; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Email Address</strong></label>
                                                <input type="text" name="applicant_email" value="<?php echo $applicant_email; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Applicant IC</strong></label>
                                                <input type="text" name="applicant_ic" value="<?php echo $applicant_ic; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Staff ID</strong></label>
                                                <input type="text" name="applicant_staff_id" value="<?php echo $applicatnt_staff_id; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Bank Name</strong></label>
                                                <input type="text" name="bank_name" value="<?php echo $bank_name; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Bank Account Number</strong></label>
                                                <input type="text" name="no_account" value="<?php echo $no_account; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Application Type -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Application Type</h3>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label><strong>Type of Application</strong></label>
                                        <select name="application_type" id="applicationType" class="form-control">
                                            <option value="Advance">Advance</option>
                                            <option value="Reconciliation">Reconciliation</option>
                                            <option value="Claim">Claim</option>
                                        </select>
                                    </div>
                                    <div id="reconciliationSelection" style="display: none;">
                                        <label><strong>Choose an Advance to Reconcile</strong></label>
                                        <select name="advance_id" id="advanceSelect" class="form-control select2">
                                            <option value="" disabled selected>Select an Advance</option>
                                            <?php 
                                            $advanceQuery = "
                                                SELECT 
                                                    rc.application_id,
                                                    rc.date_applied,
                                                    rc.status,
                                                    us.full_name AS applicant_name,
                                                    COALESCE(SUM(rci.claim_amount), 0) AS advance_total,
                                                    COUNT(rci.id) AS item_count,
                                                    GROUP_CONCAT(
                                                        DISTINCT NULLIF(TRIM(rci.claim_category), '')
                                                        ORDER BY rci.claim_category
                                                        SEPARATOR ', '
                                                    ) AS category_summary
                                                FROM reconciliation_claim_applications rc
                                                LEFT JOIN reconciliation_claim_items rci ON rc.application_id = rci.application_id
                                                LEFT JOIN uitm_staff us ON rc.applicant_id = us.id
                                                WHERE rc.application_type = 'Advance'
                                                  AND (rc.status = 'Approved' OR rc.status = 'Send to bank')
                                                  AND rc.project_id = '$id'
                                                GROUP BY rc.application_id, rc.date_applied, rc.status, us.full_name
                                                ORDER BY rc.date_applied DESC, rc.application_id DESC";
                                            $advanceResult = mysqli_query($db, $advanceQuery);
                                            if ($advanceResult && mysqli_num_rows($advanceResult) > 0) {
                                                while ($advance = mysqli_fetch_assoc($advanceResult)) {
                                                    $advanceDate = !empty($advance['date_applied']) ? date('d M Y', strtotime($advance['date_applied'])) : 'Date not recorded';
                                                    $applicantName = !empty($advance['applicant_name']) ? $advance['applicant_name'] : 'Applicant not recorded';
                                                    $itemCount = (int)($advance['item_count'] ?? 0);
                                                    $itemLabel = $itemCount === 1 ? '1 item' : $itemCount . ' items';
                                                    $categorySummary = !empty($advance['category_summary']) ? ' - ' . $advance['category_summary'] : '';
                                                    $optionLabel = $advanceDate . ' | ' . $applicantName . ' | RM ' . number_format((float)($advance['advance_total'] ?? 0), 2) . ' | ' . $itemLabel . $categorySummary;
                                            ?>
                                                <option value="<?php echo htmlspecialchars($advance['application_id']); ?>">
                                                    <?php echo htmlspecialchars($optionLabel); ?>
                                                </option>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                                <option value="" disabled>No approved advance available for this project</option>
                                            <?php } ?>
                                        </select>
                                        <div id="advanceItemsPreview" class="m-t-15"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- item details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white" id="itemDetailsTitle">Item Details</h3>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label><strong id="itemDetailsLabel">Items</strong></label>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="dynamic_field">
                                                <thead>
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Appendix</th>
                                                        <th>Item Description</th>
                                                        <th>Quantity</th>
                                                        <th>Amount (RM)</th>
                                                        <th class="reconcile-only" style="display:none;">Adjustment (RM)</th>
                                                        <th class="proof-column">Proof of Payment</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <select class="form-control claim-category" name="claim_category[]">
                                                                <option value="F&B">F&amp;B</option>
                                                                <option value="Hotel">Hotel</option>
                                                                <option value="Travelling">Travelling</option>
                                                                <option value="Printing">Printing</option>
                                                                <option value="Project Materials">Project Materials</option>
                                                                <option value="Others">Others</option>
                                                            </select>
                                                            <input type="hidden" name="appendix_type[]" class="appendix-type" value="food">
                                                            <input type="hidden" name="appendix_data[]" class="appendix-data" value="">
                                                            <input type="hidden" name="advance_item_id[]" value="">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-outline-info btn-sm appendix-btn" data-form="food">Fill Form</button>
                                                            <span class="appendix-not-required">No need to fill the form</span>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="claim_item[]" class="form-control" placeholder="Item Description">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="claim_quantity[]" class="form-control" placeholder="Quantity" min="1">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="claim_amount[]" step="0.01" class="form-control amount-input" placeholder="Amount (RM)">
                                                        </td>
                                                        <td class="reconcile-only" style="display:none;">
                                                            <input type="number" name="adjustment_amount[]" step="0.01" class="form-control adjustment-input" placeholder="Adjustment (RM)" value="0.00">
                                                        </td>
                                                        <td class="proof-column">
                                                            <div id="proofDropzone0" class="proof-dropzone dropzone" data-row-index="0">
                                                                <div class="dz-message">Drop files here or click to upload.</div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" name="add" id="add" class="btn btn-info">Add More</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="4" class="text-right">Total Amount (RM)</th>
                                                        <th><input type="text" id="totalAmount" class="form-control" value="0.00" readonly></th>
                                                        <th class="reconcile-only" style="display:none;"><input type="text" id="totalAdjustment" class="form-control" value="0.00" readonly></th>
                                                        <th colspan="2"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit Buttons -->
                            <?php
                            $isLeader = ($userData['id'] == $leader_id);
                            ?>
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="button" id="submitToProjectLeader" class="btn btn-lg btn-info" <?php echo $isLeader ? 'disabled' : ''; ?>>
                                        Submit to Project Leader
                                    </button>
                                    <button type="button" id="submitByProjectLeader" class="btn btn-lg btn-success" <?php echo !$isLeader ? 'disabled' : ''; ?>>
                                        Submit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Row -->
                <div class="modal fade" id="appendixModal" tabindex="-1" role="dialog" aria-labelledby="appendixModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="appendixModalLabel">Appendix Form</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="appendixRowIndex">
                                <div class="appendix-section" data-section="food">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Event / Meeting Name</label>
                                                <input type="text" class="form-control appendix-input" data-key="event_name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date</label>
                                                <input type="date" class="form-control appendix-input" data-key="date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Venue</label>
                                        <input type="text" class="form-control appendix-input" data-key="venue">
                                    </div>
                                    <div class="form-group">
                                        <label>Participants / Attendees</label>
                                        <textarea class="form-control appendix-input" data-key="participants" rows="4"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Purpose / Justification</label>
                                        <textarea class="form-control appendix-input" data-key="purpose" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="appendix-section" data-section="hotel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Hotel Name</label>
                                                <input type="text" class="form-control appendix-input" data-key="hotel_name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Guest Name</label>
                                                <input type="text" class="form-control appendix-input" data-key="guest_name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Check-in</label>
                                                <input type="date" class="form-control appendix-input" data-key="check_in">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Check-out</label>
                                                <input type="date" class="form-control appendix-input" data-key="check_out">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>No. of Nights</label>
                                                <input type="number" class="form-control appendix-input" data-key="nights" min="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Purpose / Justification</label>
                                        <textarea class="form-control appendix-input" data-key="purpose" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="appendix-section" data-section="mileage">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Travel Date</label>
                                                <input type="date" class="form-control appendix-input" data-key="travel_date">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Vehicle No.</label>
                                                <input type="text" class="form-control appendix-input" data-key="vehicle_no">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>From</label>
                                                <input type="text" class="form-control appendix-input" data-key="from">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>To</label>
                                                <input type="text" class="form-control appendix-input" data-key="to">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Distance (KM)</label>
                                                <input type="number" step="0.01" class="form-control appendix-input" data-key="km">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Purpose / Justification</label>
                                        <textarea class="form-control appendix-input" data-key="purpose" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" id="saveAppendixForm">Save Form</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
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
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="../assets/node_modules/dropzone-master/dist/dropzone.js"></script>
    <script>
        Dropzone.autoDiscover = false;
        let itemRowIndex = 1;

        function appendixInfo(category) {
            if (category === 'Hotel') {
                return { type: 'hotel', title: 'Hotel Appendix Form' };
            }
            if (category === 'Travelling') {
                return { type: 'mileage', title: 'Mileage Appendix Form' };
            }
            if (category === 'F&B') {
                return { type: 'food', title: 'Food & Beverages Appendix Form' };
            }
            return { type: '', title: 'Proof of Payment Form' };
        }

        function makeItemRow(isFirst, rowIndex) {
            return `
                <tr data-row-index="${rowIndex}">
                    <td>
                        <select class="form-control claim-category" name="claim_category[]">
                            <option value="F&B">F&amp;B</option>
                            <option value="Hotel">Hotel</option>
                            <option value="Travelling">Travelling</option>
                            <option value="Printing">Printing</option>
                            <option value="Project Materials">Project Materials</option>
                            <option value="Others">Others</option>
                        </select>
                        <input type="hidden" name="appendix_type[]" class="appendix-type" value="food">
                        <input type="hidden" name="appendix_data[]" class="appendix-data" value="">
                        <input type="hidden" name="advance_item_id[]" value="">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-info btn-sm appendix-btn" data-form="food">Fill Form</button>
                        <span class="appendix-not-required">No need to fill the form</span>
                    </td>
                    <td><input type="text" name="claim_item[]" class="form-control" placeholder="Item Description"></td>
                    <td><input type="number" name="claim_quantity[]" class="form-control" placeholder="Quantity" min="1"></td>
                    <td><input type="number" name="claim_amount[]" step="0.01" class="form-control amount-input" placeholder="Amount (RM)"></td>
                    <td class="reconcile-only" style="display:none;"><input type="number" name="adjustment_amount[]" step="0.01" class="form-control adjustment-input" value="0.00" placeholder="Adjustment (RM)"></td>
                    <td class="proof-column">
                        <div id="proofDropzone${rowIndex}" class="proof-dropzone dropzone" data-row-index="${rowIndex}">
                            <div class="dz-message">Drop files here or click to upload.</div>
                        </div>
                    </td>
                    <td class="text-center">
                        ${isFirst ? '<button type="button" name="add" id="add" class="btn btn-info">Add More</button>' : '<button type="button" class="btn btn-danger btn_remove">Remove</button>'}
                    </td>
                </tr>`;
        }

        function calculateTotals() {
            let total = 0;
            let adjustment = 0;

            $('.amount-input').each(function () {
                total += parseFloat($(this).val()) || 0;
            });
            $('.adjustment-input').each(function () {
                adjustment += parseFloat($(this).val()) || 0;
            });

            $('#totalAmount').val(total.toFixed(2));
            $('#totalAdjustment').val(adjustment.toFixed(2));
        }

        function applyApplicationType() {
            const type = $('#applicationType').val();
            const isReconciliation = type === 'Reconciliation';
            const isAdvance = type === 'Advance';

            $('#itemDetailsTitle').text(type === 'Claim' ? 'Claim Details' : 'Item Details');
            $('#itemDetailsLabel').text(type === 'Claim' ? 'Claim Items' : 'Items');
            $('#reconciliationSelection').toggle(isReconciliation);
            $('.reconcile-only').toggle(isReconciliation);
            $('.proof-column').toggle(!isAdvance);
        }

        function initProofDropzone(row) {
            const zoneElement = row.find('.proof-dropzone')[0];
            if (!zoneElement || zoneElement.dropzone) {
                return;
            }

            new Dropzone(zoneElement, {
                url: '#',
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 10,
                maxFiles: 10,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.doc,.docx',
                addRemoveLinks: true,
                dictDefaultMessage: 'Drop files here or click to upload.',
                dictRemoveFile: 'Remove',
                init: function () {
                    this.on('maxfilesexceeded', function (file) {
                        this.removeFile(file);
                        Swal.fire('Too Many Files', 'You can upload up to 10 files for each item.', 'warning');
                    });
                }
            });
        }

        function updateAppendixButton(row) {
            const category = row.find('.claim-category').val();
            const info = appendixInfo(category);
            const needsAppendix = ['F&B', 'Hotel', 'Travelling'].includes(category);

            row.find('.appendix-type').val(info.type);
            row.find('.appendix-btn').data('form', info.type).toggle(needsAppendix);
            row.find('.appendix-not-required').toggle(!needsAppendix);

            if (!needsAppendix) {
                row.find('.appendix-data').val('');
                row.find('.appendix-btn').removeClass('btn-info').addClass('btn-outline-info').text('Fill Form');
            }
        }

        function normalizeItemRows() {
            $('#dynamic_field tbody tr').each(function (index) {
                $(this).attr('data-row-index', index);
                $(this).find('.proof-dropzone').attr('data-row-index', index);
            });
        }

        function appendProofFiles(formData) {
            formData.delete('proof_file');
            formData.delete('proof_file[]');

            if ($('#applicationType').val() === 'Advance') {
                return;
            }

            $('#dynamic_field tbody tr').each(function (index) {
                const dropzoneElement = $(this).find('.proof-dropzone')[0];
                if (!dropzoneElement || !dropzoneElement.dropzone) {
                    return;
                }

                dropzoneElement.dropzone.getAcceptedFiles().forEach(function (file) {
                    formData.append('proof_file[' + index + '][]', file, file.name);
                });
            });
        }

        function submitReconciliation(url) {
            normalizeItemRows();
            const form = $('#reconciliationForm')[0];
            const formData = new FormData(form);
            appendProofFiles(formData);

            Swal.fire({
                title: 'Submitting Application...',
                text: 'Please wait while the application is being submitted.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire('Submitted!', response.message, 'success').then(() => {
                            window.location.href = 'reconciliation-claim.php';
                        });
                    } else {
                        Swal.fire('Failed!', response.message || 'Unable to submit application.', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText || error);
                    Swal.fire('Error!', 'An error occurred during submission. Please check the console for details.', 'error');
                }
            });
        }

        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            $('#dynamic_field tbody tr').each(function () {
                $(this).attr('data-row-index', 0);
                updateAppendixButton($(this));
                initProofDropzone($(this));
            });
            applyApplicationType();
            calculateTotals();

            $(document).on('click', '#add', function() {
                const rowIndex = itemRowIndex++;
                $('#dynamic_field tbody').append(makeItemRow(false, rowIndex));
                initProofDropzone($('#dynamic_field tbody tr').last());
                applyApplicationType();
                calculateTotals();
            });

            $(document).on('click', '.btn_remove', function() {
                const row = $(this).closest('tr');
                const dropzoneElement = row.find('.proof-dropzone')[0];
                if (dropzoneElement && dropzoneElement.dropzone) {
                    dropzoneElement.dropzone.destroy();
                }
                row.remove();
                calculateTotals();
            });

            $(document).on('input', '.amount-input, .adjustment-input', calculateTotals);

            $(document).on('change', '.claim-category', function () {
                updateAppendixButton($(this).closest('tr'));
            });

            $(document).on('click', '.appendix-btn', function () {
                const row = $(this).closest('tr');
                const info = appendixInfo(row.find('.claim-category').val());
                const data = row.find('.appendix-data').val();

                $('#appendixModalLabel').text(info.title);
                $('#appendixRowIndex').val(row.index());
                $('.appendix-section').hide();
                $('.appendix-section[data-section="' + info.type + '"]').show();
                $('.appendix-input').val('');

                if (data) {
                    try {
                        const parsed = JSON.parse(data);
                        Object.keys(parsed).forEach(function (key) {
                            $('.appendix-section[data-section="' + info.type + '"] .appendix-input[data-key="' + key + '"]').val(parsed[key]);
                        });
                    } catch (e) {}
                }

                $('#appendixModal').modal('show');
            });

            $(document).on('click', '#saveAppendixForm', function () {
                const rowIndex = parseInt($('#appendixRowIndex').val(), 10);
                const row = $('#dynamic_field tbody tr').eq(rowIndex);
                const section = $('.appendix-section:visible');
                const data = {};

                section.find('.appendix-input').each(function () {
                    data[$(this).data('key')] = $(this).val();
                });

                row.find('.appendix-data').val(JSON.stringify(data));
                row.find('.appendix-btn').removeClass('btn-outline-info').addClass('btn-info').text('Edit Form');
                $('#appendixModal').modal('hide');
            });

            $('#applicationType').on('change', applyApplicationType);

            $('#advanceSelect').on('change', function () {
                const advanceId = $(this).val();
                if (!advanceId) {
                    $('#advanceItemsPreview').empty();
                    return;
                }

                $('#advanceItemsPreview').html('<p class="text-muted">Loading advance items...</p>');
                $.ajax({
                    url: 'get-reconciliation-advance-items.php',
                    method: 'POST',
                    data: { advance_id: advanceId },
                    dataType: 'html',
                    success: function (response) {
                        $('#advanceItemsPreview').html(response);
                    },
                    error: function (xhr) {
                        const message = xhr.responseText || '<p class="text-danger">Unable to load advance items.</p>';
                        $('#advanceItemsPreview').html(message);
                    }
                });
            });
        });

        $(document).on('click', '#submitToProjectLeader', function () {
            Swal.fire({
                title: 'Submit application?',
                text: 'The project leader will verify this application.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    submitReconciliation('process_reconciliation.php');
                }
            });
        });

        $(document).on('click', '#submitByProjectLeader', function () {
            Swal.fire({
                title: 'Submit application?',
                text: 'This application will be sent to Level 3 for verification.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    submitReconciliation('submit_leader_reconciliation.php');
                }
            });
        });
    </script>
</body>

</html>
