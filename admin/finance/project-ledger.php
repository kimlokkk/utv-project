<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data']; // Ambil data pengguna semasa
    $user_id = $userData['id']; // ID pengguna semasa

    // Get project ID from URL
    $project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($project_id <= 0) {
        echo "<script>alert('Invalid project ID'); window.location.href='project-ledger-list.php';</script>";
        exit;
    }

    // Get project information
    $project_query = "SELECT * FROM project WHERE id = $project_id";
    $project_result = mysqli_query($db, $project_query);
    $project_data = mysqli_fetch_assoc($project_result);

    if (!$project_data) {
        echo "<script>alert('Project not found'); window.location.href='project-ledger-list.php';</script>";
        exit;
    }

    $project_assets_exists = false;
    $project_assets_check = mysqli_query($db, "SHOW TABLES LIKE 'project_assets'");
    if ($project_assets_check && mysqli_num_rows($project_assets_check) > 0) {
        $project_assets_exists = true;
    }

    // Handle form submissions
    if (isset($_POST['add_transaction'])) {
        $transaction_date = mysqli_real_escape_string($db, $_POST['transaction_date']);
        $transaction_category = mysqli_real_escape_string($db, $_POST['transaction_category']);
        $transaction_type = mysqli_real_escape_string($db, $_POST['transaction_type']);
        $amount = (float)$_POST['amount'];
        $transaction_desc = mysqli_real_escape_string($db, $_POST['transaction_desc']);
        $invoice_number = mysqli_real_escape_string($db, $_POST['invoice_number']);
        
        // Create full description with category
        $full_desc = $transaction_category . " - " . $transaction_desc;
        
        // Insert into database
        $insert_query = "INSERT INTO project_ledger (project_id, transaction_desc, transaction_type, amount, created_at) 
                         VALUES ($project_id, '$full_desc', '$transaction_type', $amount, NOW())";
        
        if (mysqli_query($db, $insert_query)) {
            $success_message = "Transaction added successfully";
        } else {
            $error_message = "Failed to add transaction: " . mysqli_error($db);
        }
    }

    // Handle delete transaction
    if (isset($_POST['delete_transaction'])) {
        $transaction_id = (int)$_POST['transaction_id'];
        $delete_query = "DELETE FROM project_ledger WHERE id = $transaction_id AND project_id = $project_id";
        
        if (mysqli_query($db, $delete_query)) {
            $success_message = "Transaction deleted successfully";
        } else {
            $error_message = "Failed to delete transaction";
        }
    }

    // Handle add asset
    if (isset($_POST['add_asset'])) {
        if (!$project_assets_exists) {
            $error_message = "Asset/equipment tracking is not available because the project_assets table has not been created.";
        } else {
        $asset_date = mysqli_real_escape_string($db, $_POST['asset_date']);
        $asset_name = mysqli_real_escape_string($db, $_POST['asset_name']);
        $asset_category = mysqli_real_escape_string($db, $_POST['asset_category']);
        $asset_location = mysqli_real_escape_string($db, $_POST['asset_location']);
        $asset_value = (float)$_POST['asset_value'];
        
        $insert_asset_query = "INSERT INTO project_assets (project_id, asset_date, asset_name, asset_category, location, value, created_at) 
                               VALUES ($project_id, '$asset_date', '$asset_name', '$asset_category', '$asset_location', $asset_value, NOW())";
        
        if (mysqli_query($db, $insert_asset_query)) {
            $success_message = "Asset added successfully";
        } else {
            $error_message = "Failed to add asset: " . mysqli_error($db);
        }
        }
    }

    // Handle delete asset
    if (isset($_POST['delete_asset'])) {
        if (!$project_assets_exists) {
            $error_message = "Asset/equipment tracking is not available because the project_assets table has not been created.";
        } else {
        $asset_id = (int)$_POST['asset_id'];
        $delete_asset_query = "DELETE FROM project_assets WHERE id = $asset_id AND project_id = $project_id";
        
        if (mysqli_query($db, $delete_asset_query)) {
            $success_message = "Asset deleted successfully";
        } else {
            $error_message = "Failed to delete asset";
        }
        }
    }

    // Get ledger transactions
    $ledger_query = "SELECT * FROM project_ledger WHERE project_id = $project_id ORDER BY created_at ASC";
    $ledger_result = mysqli_query($db, $ledger_query);

    // Calculate totals
    $total_revenue = 0;
    $total_expenses = 0;
    $running_balance = 0;

    // Store transactions for display
    $transactions = [];
    while ($row = mysqli_fetch_assoc($ledger_result)) {
        $transactions[] = $row;
        if ($row['transaction_type'] == 'Debit') {
            $total_revenue += $row['amount'];
        } else {
            $total_expenses += $row['amount'];
        }
    }

    $final_balance = $total_revenue - $total_expenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms - Project Ledger</title>
    <!-- This page CSS -->
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
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
                        <h4 class="text-themecolor">Project Ledger</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="project-ledger-list.php">Ledger</a></li>
                                <li class="breadcrumb-item active">Project Ledger</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- CONTENT LEDGER -->
                <!-- ============================================================== -->
                
                <!-- 1. PROJECT HEADER INFO -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-book"></i> PROJECT LEDGER - <?php echo htmlspecialchars($project_data['project_no']); ?>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="35%"><strong>FILE NO</strong></td>
                                                <td width="5%">:</td>
                                                <td><?php echo htmlspecialchars($project_data['project_no']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>PROJECT TITLE</strong></td>
                                                <td>:</td>
                                                <td><?php echo htmlspecialchars($project_data['project_title']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>PROJECT LEADER</strong></td>
                                                <td>:</td>
                                                <td><?php echo htmlspecialchars($project_data['project_leader']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>PROJECT TYPE</strong></td>
                                                <td>:</td>
                                                <td><?php echo htmlspecialchars($project_data['project_type']); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="35%"><strong>CLIENT</strong></td>
                                                <td width="5%">:</td>
                                                <td><?php echo htmlspecialchars($project_data['client_company_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>PROJECT VALUE</strong></td>
                                                <td>:</td>
                                                <td>RM <?php echo number_format($project_data['registered_project_value'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>UTV FEE (8%)</strong></td>
                                                <td>:</td>
                                                <td>RM <?php echo number_format($project_data['registered_project_value'] * 0.08, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>PROJECT PERIOD</strong></td>
                                                <td>:</td>
                                                <td><?php echo date('d/m/Y', strtotime($project_data['project_start'])); ?> - <?php echo date('d/m/Y', strtotime($project_data['project_end'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. SUMMARY CARDS -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-up fa-2x mb-2"></i>
                                <h4>RM <?php echo number_format($total_revenue, 2); ?></h4>
                                <p class="mb-0">Total Revenue</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-down fa-2x mb-2"></i>
                                <h4>RM <?php echo number_format($total_expenses, 2); ?></h4>
                                <p class="mb-0">Total Expenses</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card <?php echo $final_balance >= 0 ? 'bg-primary' : 'bg-warning'; ?> text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fa-2x mb-2"></i>
                                <h4>RM <?php echo number_format($final_balance, 2); ?></h4>
                                <p class="mb-0">Current Balance</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-list fa-2x mb-2"></i>
                                <h4><?php echo count($transactions); ?></h4>
                                <p class="mb-0">Total Transactions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. ASSET/EQUIPMENT LISTING -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-tools"></i> ASSET/EQUIPMENT LISTING
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!$project_assets_exists) { ?>
                                <div class="alert alert-info">
                                    Asset/equipment tracking is optional and is currently unavailable because the <code>project_assets</code> table has not been created.
                                </div>
                                <?php } else { ?>
                                <!-- Add Asset Form -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <form method="POST" id="addAssetForm">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <input type="date" class="form-control form-control-sm" name="asset_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control form-control-sm" name="asset_name" placeholder="Asset Brand/Name" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <select class="form-control form-control-sm" name="asset_category" required>
                                                        <option value="">Category</option>
                                                        <option value="Equipment">Equipment</option>
                                                        <option value="Software">Software</option>
                                                        <option value="Furniture">Furniture</option>
                                                        <option value="Vehicle">Vehicle</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control form-control-sm" name="asset_location" placeholder="Location" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" class="form-control form-control-sm" name="asset_value" placeholder="Value (RM)" step="0.01" required>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="submit" name="add_asset" class="btn btn-sm btn-success">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php } ?>

                                <!-- Asset Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="5%">NO</th>
                                                <th width="12%">DATE</th>
                                                <th width="30%">ASSET BRAND/NAME</th>
                                                <th width="15%">ASSET CATEGORY</th>
                                                <th width="15%">LOCATION</th>
                                                <th width="13%">VALUE (RM)</th>
                                                <th width="10%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($project_assets_exists) {
                                                $assets_query = "SELECT * FROM project_assets WHERE project_id = $project_id ORDER BY asset_date DESC";
                                                $assets_result = mysqli_query($db, $assets_query);

                                                if ($assets_result && mysqli_num_rows($assets_result) > 0) {
                                                $asset_counter = 1;
                                                while ($asset = mysqli_fetch_assoc($assets_result)) {
                                                    echo "<tr>";
                                                    echo "<td class='text-center'>" . $asset_counter . "</td>";
                                                    echo "<td>" . date('d/m/Y', strtotime($asset['asset_date'])) . "</td>";
                                                    echo "<td>" . htmlspecialchars($asset['asset_name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($asset['asset_category']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($asset['location']) . "</td>";
                                                    echo "<td class='text-right'>" . number_format($asset['value'], 2) . "</td>";
                                                    echo "<td class='text-center'>";
                                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteAsset(" . $asset['id'] . ")' title='Delete'>";
                                                    echo "<i class='fas fa-trash'></i>";
                                                    echo "</button>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                    $asset_counter++;
                                                }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>No assets/equipment recorded</td></tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center text-muted'>Asset/equipment tracking table is not available.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. ADD TRANSACTION FORM -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle"></i> ADD NEW TRANSACTION
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="addTransactionForm">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Transaction Date</strong></label>
                                                <input type="date" class="form-control" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Transaction Category</strong></label>
                                                <select class="form-control" name="transaction_category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Invoice Application">Invoice Application</option>
                                                    <option value="Payment Received">Payment Received</option>
                                                    <option value="Professional Fee">Professional Fee</option>
                                                    <option value="Allowance/Wages">Allowance/Wages</option>
                                                    <option value="Advance">Advance</option>
                                                    <option value="Claim">Claim</option>
                                                    <option value="Procurement">Procurement</option>
                                                    <option value="Project Funding Assistance">Project Funding Assistance</option>
                                                    <option value="SST">SST</option>
                                                    <option value="Others">Others</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Transaction Type</strong></label>
                                                <select class="form-control" name="transaction_type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Debit">Revenue (Debit)</option>
                                                    <option value="Credit">Expenses (Credit)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Amount (RM)</strong></label>
                                                <input type="number" class="form-control" name="amount" step="0.01" min="0" placeholder="0.00" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Description</strong></label>
                                                <input type="text" class="form-control" name="transaction_desc" placeholder="Enter transaction description" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Invoice Number (Optional)</strong></label>
                                                <input type="text" class="form-control" name="invoice_number" placeholder="INV-001">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" name="add_transaction" class="btn btn-success form-control">
                                                    <i class="fas fa-save"></i> Add Transaction
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. TRANSACTION HISTORY TABLE -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history"></i> TRANSACTION HISTORY
                                    </h5>
                                    <div>
                                        <a href="export-ledger.php?id=<?php echo $project_id; ?>" class="btn btn-light btn-sm" target="_blank">
                                            <i class="fas fa-file-excel"></i> Export Excel
                                        </a>
                                        <button class="btn btn-light btn-sm" onclick="window.print()">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="transactionTable" class="table table-bordered table-striped">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="5%">NO</th>
                                                <th width="10%">DATE</th>
                                                <th width="15%">CATEGORY</th>
                                                <th width="25%">DESCRIPTION</th>
                                                <th width="12%">REVENUE (RM)</th>
                                                <th width="12%">EXPENSES (RM)</th>
                                                <th width="13%">BALANCE (RM)</th>
                                                <th width="8%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $counter = 1;
                                            $running_balance = 0;
                                            
                                            foreach ($transactions as $transaction) {
                                                // Calculate running balance
                                                if ($transaction['transaction_type'] == 'Debit') {
                                                    $running_balance += $transaction['amount'];
                                                    $revenue = $transaction['amount'];
                                                    $expenses = 0;
                                                } else {
                                                    $running_balance -= $transaction['amount'];
                                                    $revenue = 0;
                                                    $expenses = $transaction['amount'];
                                                }
                                                
                                                $balance_class = $running_balance >= 0 ? 'text-success' : 'text-danger';
                                                $transaction_date = date('d/m/Y', strtotime($transaction['created_at']));
                                                
                                                // Extract category from description
                                                $category = 'Others';
                                                $description = $transaction['transaction_desc'];
                                                
                                                if (strpos($description, 'Payment received') !== false) {
                                                    $category = 'Payment Received';
                                                } elseif (strpos($description, 'Professional') !== false) {
                                                    $category = 'Professional Fee';
                                                } elseif (strpos($description, 'Allowance') !== false) {
                                                    $category = 'Allowance/Wages';
                                                } elseif (strpos($description, 'SST') !== false) {
                                                    $category = 'SST';
                                                } elseif (strpos($description, 'Advance') !== false) {
                                                    $category = 'Advance';
                                                } elseif (strpos($description, 'Claim') !== false) {
                                                    $category = 'Claim';
                                                } elseif (strpos($description, 'Invoice Application') !== false) {
                                                    $category = 'Invoice Application';
                                                } elseif (strpos($description, 'Procurement') !== false) {
                                                    $category = 'Procurement';
                                                } elseif (strpos($description, 'Project Funding') !== false) {
                                                    $category = 'Project Funding Assistance';
                                                }
                                                
                                                // Clean description (remove category prefix if exists)
                                                $clean_desc = str_replace($category . ' - ', '', $description);
                                            ?>
                                            <tr>
                                                <td class="text-center"><?php echo $counter; ?></td>
                                                <td><?php echo $transaction_date; ?></td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $category; ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($clean_desc); ?></td>
                                                <td class="text-right">
                                                    <?php echo $revenue > 0 ? '<span class="text-success">+' . number_format($revenue, 2) . '</span>' : '-'; ?>
                                                </td>
                                                <td class="text-right">
                                                    <?php echo $expenses > 0 ? '<span class="text-danger">-' . number_format($expenses, 2) . '</span>' : '-'; ?>
                                                </td>
                                                <td class="text-right <?php echo $balance_class; ?>">
                                                    <strong><?php echo number_format($running_balance, 2); ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-danger" onclick="deleteTransaction(<?php echo $transaction['id']; ?>)" title="Delete Transaction">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                                $counter++;
                                            }
                                            
                                            // Show message if no transactions
                                            if (count($transactions) == 0) {
                                                echo '<tr><td colspan="8" class="text-center text-muted">No transactions found</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th colspan="4" class="text-right">TOTAL / BALANCE:</th>
                                                <th class="text-right text-success">
                                                    <strong>+RM <?php echo number_format($total_revenue, 2); ?></strong>
                                                </th>
                                                <th class="text-right text-danger">
                                                    <strong>-RM <?php echo number_format($total_expenses, 2); ?></strong>
                                                </th>
                                                <th class="text-right <?php echo $final_balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <strong>RM <?php echo number_format($final_balance, 2); ?></strong>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End CONTENT LEDGER -->
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
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>

    <script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#transactionTable').DataTable({
            "order": [[ 1, "desc" ]],
            "pageLength": 25,
            "responsive": true,
            "dom": 'Bfrtip',
            "buttons": [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
        
        // Add button styling
        $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary btn-sm mr-1');
        
        // Show success/error messages
        <?php if (isset($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $success_message; ?>',
                timer: 2000,
                showConfirmButton: false
            });
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?php echo $error_message; ?>'
            });
        <?php endif; ?>
    });

    // Delete transaction function
    function deleteTransaction(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this transaction!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                var form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                var input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'delete_transaction';
                input1.value = '1';
                
                var input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'transaction_id';
                input2.value = id;
                
                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Delete asset function
    function deleteAsset(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this asset!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                var form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                var input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'delete_asset';
                input1.value = '1';
                
                var input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'asset_id';
                input2.value = id;
                
                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>
</body>
</html>
