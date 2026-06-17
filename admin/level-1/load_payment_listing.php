<?php
    include '../db_connect/db_connect.php';
    $sql = "SELECT p.*, pr.project_no, i.invoice_no 
            FROM payments p
            LEFT JOIN projects pr ON p.project_id = pr.id
            LEFT JOIN invoices i ON p.invoice_id = i.id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['project_no']}</td>
                <td>{$row['invoice_no']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['sst_amount']}</td>
                <td>{$row['payment_date']}</td>
                <td>{$row['payment_method']}</td>
                <td>{$row['purpose']}</td>
              </tr>";
    }
?>