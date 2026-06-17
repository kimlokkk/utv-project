<?php
session_start();
include 'auth_check.php';
include '../db_connect/db_connect.php';

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id <= 0) {
    echo "<script>alert('Invalid project ID'); window.close();</script>";
    exit;
}

// Get project information
$project_query = "SELECT * FROM project WHERE id = $project_id";
$project_result = mysqli_query($db, $project_query);
$project_data = mysqli_fetch_assoc($project_result);

if (!$project_data) {
    echo "<script>alert('Project not found'); window.close();</script>";
    exit;
}

// Get ledger transactions
$ledger_query = "SELECT * FROM project_ledger WHERE project_id = $project_id ORDER BY created_at ASC";
$ledger_result = mysqli_query($db, $ledger_query);

// Calculate totals
$total_revenue = 0;
$total_expenses = 0;
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

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Project_Ledger_' . $project_data['project_no'] . '_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Function to extract category from description
function extractCategory($description) {
    if (strpos($description, 'Payment received') !== false) {
        return 'Payment Received';
    } elseif (strpos($description, 'Professional') !== false) {
        return 'Professional Fee';
    } elseif (strpos($description, 'Allowance') !== false) {
        return 'Allowance/Wages';
    } elseif (strpos($description, 'SST') !== false) {
        return 'SST';
    } elseif (strpos($description, 'Advance') !== false) {
        return 'Advance';
    } elseif (strpos($description, 'Claim') !== false) {
        return 'Claim';
    } elseif (strpos($description, 'Invoice Application') !== false) {
        return 'Invoice Application';
    } elseif (strpos($description, 'Procurement') !== false) {
        return 'Procurement';
    } elseif (strpos($description, 'Project Funding') !== false) {
        return 'Project Funding Assistance';
    }
    return 'Others';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { font-weight: bold; font-size: 14px; text-align: center; margin-bottom: 20px; }
        .project-info { margin-bottom: 20px; }
        .project-info table { border: none; }
        .project-info td { border: none; padding: 2px 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        VIEW FOR INDIVIDUAL PROJECT LEDGER
    </div>
    
    <div class="project-info">
        <table>
            <tr>
                <td><strong>FILE NO</strong></td>
                <td>:</td>
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
                <td><strong>CLIENT</strong></td>
                <td>:</td>
                <td><?php echo htmlspecialchars($project_data['client_company_name']); ?></td>
            </tr>
            <tr>
                <td><strong>PROJECT VALUE</strong></td>
                <td>:</td>
                <td>RM <?php echo number_format($project_data['registered_project_value'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>UTV FEE</strong></td>
                <td>:</td>
                <td>RM <?php echo number_format($project_data['registered_project_value'] * 0.08, 2); ?></td>
            </tr>
            <tr>
                <td><strong>SPECIAL NOTE (IF ANY)</strong></td>
                <td>:</td>
                <td>Project Period: <?php echo date('d/m/Y', strtotime($project_data['project_start'])); ?> - <?php echo date('d/m/Y', strtotime($project_data['project_end'])); ?></td>
            </tr>
        </table>
    </div>
    
    <br><br>
    
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>PROCESSED DATE</th>
                <th>TRANSACTION CATEGORY/ITEMS</th>
                <th>DETAILS / INVOICE NUMBER</th>
                <th>DETAILS 2</th>
                <th>INVOICE AMOUNT (RM)</th>
                <th>LOAN/ADJUSTMENT VALUE (RM)</th>
                <th>REVENUE/PAYMENT RECEIVED (RM)</th>
                <th>EXPENSES (RM)</th>
                <th>BALANCE (RM)</th>
                <th>NOTES/REMARKS/PO VALUE</th>
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
                
                $transaction_date = date('d/m/Y', strtotime($transaction['created_at']));
                $category = extractCategory($transaction['transaction_desc']);
                
                // Clean description (remove category prefix if exists)
                $clean_desc = str_replace($category . ' - ', '', $transaction['transaction_desc']);
                
                echo "<tr>";
                echo "<td class='text-center'>" . $counter . "</td>";
                echo "<td class='text-center'>" . $transaction_date . "</td>";
                echo "<td>" . htmlspecialchars($category) . "</td>";
                echo "<td>" . htmlspecialchars($clean_desc) . "</td>";
                echo "<td>-</td>";
                echo "<td class='text-right'>" . ($revenue > 0 ? number_format($revenue, 2) : '-') . "</td>";
                echo "<td class='text-right'>-</td>";
                echo "<td class='text-right'>" . ($revenue > 0 ? number_format($revenue, 2) : '-') . "</td>";
                echo "<td class='text-right'>" . ($expenses > 0 ? number_format($expenses, 2) : '-') . "</td>";
                echo "<td class='text-right'>" . number_format($running_balance, 2) . "</td>";
                echo "<td>-</td>";
                echo "</tr>";
                
                $counter++;
            }
            
            // Show message if no transactions
            if (count($transactions) == 0) {
                echo '<tr><td colspan="11" class="text-center">No transactions found</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL/BALANCE</strong></td>
                <td class="text-right"><strong>RM <?php echo number_format($total_revenue, 2); ?></strong></td>
                <td class="text-right"><strong>-</strong></td>
                <td class="text-right"><strong>RM <?php echo number_format($total_revenue, 2); ?></strong></td>
                <td class="text-right"><strong>RM <?php echo number_format($total_expenses, 2); ?></strong></td>
                <td class="text-right"><strong>RM <?php echo number_format($final_balance, 2); ?></strong></td>
                <td class="text-right"><strong>-</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <br><br>
    
    <div class="project-info">
        <table>
            <tr>
                <td><strong>Export Date:</strong></td>
                <td><?php echo date('d/m/Y H:i:s'); ?></td>
            </tr>
            <tr>
                <td><strong>Export By:</strong></td>
                <td><?php echo htmlspecialchars($_SESSION['user_data']['name']); ?></td>
            </tr>
            <tr>
                <td><strong>Total Records:</strong></td>
                <td><?php echo count($transactions); ?> transactions</td>
            </tr>
        </table>
    </div>
</body>
</html>