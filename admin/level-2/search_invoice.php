<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include '../../db_connect/db_connect.php';

$keyword = $_POST['keyword'] ?? '';
$project_id = $_POST['project_id'] ?? '';

$keyword = mysqli_real_escape_string($db, $keyword);
$project_id = mysqli_real_escape_string($db, $project_id);

$query = "
    SELECT 
        i.id,
        i.invoice_no,
        i.total_invoice,
        i.paid_amount,
        i.outstanding_amount,
        i.payment_status,
        i.invoice_status,
        p.client_company_name
    FROM invoices i
    INNER JOIN project p ON p.id = i.project_id
    WHERE i.invoice_status NOT LIKE '%Pending%'
      AND i.invoice_status NOT LIKE '%Rejected%'
      AND i.invoice_status NOT LIKE '%Returned%'
      AND i.invoice_status NOT LIKE '%project leader%'
      AND (
            i.invoice_status LIKE '%Approved%'
            OR i.invoice_status LIKE '%Waiting Payment%'
            OR i.payment_status IN ('Unpaid', 'Partial')
          )
";

if (!empty($project_id)) {
    $query .= " AND i.project_id = '$project_id'";
}

if (!empty($keyword)) {
    $query .= "
        AND (
            i.invoice_no LIKE '%$keyword%'
            OR p.client_company_name LIKE '%$keyword%'
        )
    ";
}

$query .= "
    ORDER BY i.invoice_no ASC
    LIMIT 20
";

$result = mysqli_query($db, $query);

$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total_invoice = (float)$row['total_invoice'];
        $paid_amount = (float)$row['paid_amount'];

        /*
            Use calculated outstanding for safety.
            If stored outstanding_amount is wrong, this still shows correct value.
        */
        $outstanding_amount = $total_invoice - $paid_amount;

        if ($outstanding_amount < 0) {
            $outstanding_amount = 0;
        }

        // Hide fully paid invoices from dropdown
        if ($row['payment_status'] === 'Paid' || $outstanding_amount <= 0) {
            continue;
        }

        $invoice_no_display = !empty($row['invoice_no']) ? $row['invoice_no'] : 'Invoice ID: ' . $row['id'];

        $data[] = [
            'id' => $row['id'],
            'invoice_no' => $invoice_no_display,
            'total_invoice' => number_format($total_invoice, 2, '.', ''),
            'paid_amount' => number_format($paid_amount, 2, '.', ''),
            'outstanding_amount' => number_format($outstanding_amount, 2, '.', ''),
            'payment_status' => $row['payment_status'],
            'invoice_status' => $row['invoice_status'],
            'client_company_name' => $row['client_company_name']
        ];
    }
}

echo json_encode($data);
exit;
?>