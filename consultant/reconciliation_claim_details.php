<?php
function reconciliation_details_column_exists($db, $table, $column) {
    $table = mysqli_real_escape_string($db, $table);
    $column = mysqli_real_escape_string($db, $column);
    $result = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function reconciliation_details_table_exists($db, $table) {
    $table = mysqli_real_escape_string($db, $table);
    $result = mysqli_query($db, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function reconciliation_details_format_label($key) {
    $labels = [
        'event_name' => 'Event / Meeting Name',
        'date' => 'Date',
        'venue' => 'Venue',
        'participants' => 'Participants / Attendees',
        'purpose' => 'Purpose / Justification',
        'hotel_name' => 'Hotel Name',
        'guest_name' => 'Guest Name',
        'check_in' => 'Check-in',
        'check_out' => 'Check-out',
        'nights' => 'No. of Nights',
        'travel_date' => 'Travel Date',
        'vehicle_no' => 'Vehicle No.',
        'from' => 'From',
        'to' => 'To',
        'km' => 'Distance (KM)'
    ];

    return $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
}

function reconciliation_details_render_value($value) {
    if ($value === null || trim((string)$value) === '') {
        return "<span class='text-muted'>-</span>";
    }

    return nl2br(htmlspecialchars((string)$value));
}

function render_reconciliation_claim_details($db, $application_id, $receipt_base_path = "../consultant/project-documents/reconciliation-claim-receipts/") {
    $application_id = mysqli_real_escape_string($db, $application_id);
    $has_total = reconciliation_details_column_exists($db, 'reconciliation_claim_applications', 'total_amount');
    $has_adjustment = reconciliation_details_column_exists($db, 'reconciliation_claim_applications', 'adjustment_amount');
    $has_remark = reconciliation_details_column_exists($db, 'reconciliation_claim_applications', 'remark_return');
    $has_item_adjustment = reconciliation_details_column_exists($db, 'reconciliation_claim_items', 'adjustment_amount');
    $has_proof = reconciliation_details_column_exists($db, 'reconciliation_claim_items', 'proof_file');
    $has_appendix_data = reconciliation_details_column_exists($db, 'reconciliation_claim_items', 'appendix_data');

    $select_total = $has_total ? ", rc.total_amount" : "";
    $select_adjustment = $has_adjustment ? ", rc.adjustment_amount" : "";
    $select_remark = $has_remark ? ", rc.remark_return" : "";

    $query = "SELECT 
                rc.application_id,
                rc.application_type,
                rc.status,
                rc.receipt_file,
                rc.date_applied,
                rc.applicant_id
                $select_total
                $select_adjustment
                $select_remark,
                us.full_name AS applicant_name,
                p.project_no,
                p.project_title,
                p.project_leader
              FROM reconciliation_claim_applications rc
              INNER JOIN project p ON rc.project_id = p.id
              LEFT JOIN uitm_staff us ON rc.applicant_id = us.id
              WHERE rc.application_id = '$application_id'";

    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo "<div class='alert alert-danger'>No details found for this application.</div>";
        return;
    }

    $data = mysqli_fetch_assoc($result);
    $dateFormatted = date('d M Y', strtotime($data['date_applied']));
    $items_title = $data['application_type'] === 'Claim' ? 'Claim Items' : 'Items';

    echo "<style>
        .arc-details { color: #2f3d4a; }
        .arc-section { border: 1px solid #e9edf2; border-radius: 6px; margin-bottom: 16px; background: #fff; }
        .arc-section-title { padding: 12px 16px; border-bottom: 1px solid #e9edf2; font-weight: 600; }
        .arc-section-body { padding: 14px 16px; }
        .arc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 18px; }
        .arc-field-label { display: block; color: #6c757d; font-size: 12px; margin-bottom: 3px; }
        .arc-field-value { font-weight: 500; word-break: break-word; }
        .arc-item { border-top: 1px solid #eef1f4; padding: 14px 0; }
        .arc-item:first-child { border-top: 0; padding-top: 0; }
        .arc-item:last-child { padding-bottom: 0; }
        .arc-item-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 10px; }
        .arc-category { display: inline-block; padding: 3px 8px; border: 1px solid #d9e2ec; border-radius: 4px; color: #2f3d4a; font-size: 12px; font-weight: 600; background: #f8fafc; }
        .arc-amount { white-space: nowrap; font-weight: 600; }
        .arc-mini-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px 16px; }
        .arc-appendix { margin-top: 12px; padding: 12px; border-radius: 6px; background: #f8fafc; border: 1px solid #edf1f5; }
        .arc-appendix-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-bottom: 10px; letter-spacing: 0; }
        .arc-documents { margin-top: 10px; }
        @media (max-width: 767px) {
            .arc-grid, .arc-mini-grid { grid-template-columns: 1fr; }
            .arc-item-head { display: block; }
            .arc-amount { margin-top: 8px; }
        }
    </style>";

    echo "<div class='arc-details'>";
    echo "<div class='arc-section'>";
    echo "<div class='arc-section-title'>Application Details</div><div class='arc-section-body'><div class='arc-grid'>";
    echo "<div><span class='arc-field-label'>Application ID</span><div class='arc-field-value'>" . htmlspecialchars($data['application_id']) . "</div></div>";
    echo "<div><span class='arc-field-label'>Application Type</span><div class='arc-field-value'>" . htmlspecialchars($data['application_type']) . "</div></div>";
    echo "<div><span class='arc-field-label'>Status</span><div class='arc-field-value'>" . htmlspecialchars($data['status']) . "</div></div>";
    echo "<div><span class='arc-field-label'>Date Applied</span><div class='arc-field-value'>" . htmlspecialchars($dateFormatted) . "</div></div>";
    echo "<div><span class='arc-field-label'>Applicant</span><div class='arc-field-value'>" . htmlspecialchars($data['applicant_name']) . "</div></div>";
    if ($has_total) {
        echo "<div><span class='arc-field-label'>Total Amount</span><div class='arc-field-value'>RM " . number_format((float)$data['total_amount'], 2) . "</div></div>";
    }
    if ($has_adjustment && $data['application_type'] === 'Reconciliation') {
        echo "<div><span class='arc-field-label'>Total Adjustment</span><div class='arc-field-value'>RM " . number_format((float)$data['adjustment_amount'], 2) . "</div></div>";
    }
    if ($has_remark && !empty($data['remark_return'])) {
        echo "<div><span class='arc-field-label'>Remark</span><div class='arc-field-value'>" . htmlspecialchars($data['remark_return']) . "</div></div>";
    }
    echo "</div></div></div>";

    echo "<div class='arc-section'>";
    echo "<div class='arc-section-title'>Project Details</div><div class='arc-section-body'><div class='arc-grid'>";
    echo "<div><span class='arc-field-label'>Project Number</span><div class='arc-field-value'>" . htmlspecialchars($data['project_no']) . "</div></div>";
    echo "<div><span class='arc-field-label'>Project Leader</span><div class='arc-field-value'>" . htmlspecialchars($data['project_leader']) . "</div></div>";
    echo "<div style='grid-column: 1 / -1;'><span class='arc-field-label'>Project Title</span><div class='arc-field-value'>" . htmlspecialchars($data['project_title']) . "</div></div>";
    echo "</div></div></div>";

    echo "<div class='arc-section'>";
    echo "<div class='arc-section-title'>" . htmlspecialchars($items_title) . "</div><div class='arc-section-body'>";

    $item_select = "claim_category, claim_item, claim_quantity, claim_amount";
    if ($has_item_adjustment) $item_select .= ", adjustment_amount";
    if ($has_proof) $item_select .= ", proof_file";
    if ($has_appendix_data) $item_select .= ", appendix_data";

    $items_query = "SELECT $item_select
                    FROM reconciliation_claim_items
                    WHERE application_id = '$application_id'
                    ORDER BY id ASC";
    $items_result = mysqli_query($db, $items_query);

    if ($items_result && mysqli_num_rows($items_result) > 0) {
        while ($item = mysqli_fetch_assoc($items_result)) {
            echo "<div class='arc-item'>";
            echo "<div class='arc-item-head'>";
            echo "<div><span class='arc-category'>" . htmlspecialchars($item['claim_category']) . "</span><div class='arc-field-value m-t-5'>" . htmlspecialchars($item['claim_item']) . "</div></div>";
            echo "<div class='arc-amount'>RM " . htmlspecialchars(number_format((float)$item['claim_amount'], 2)) . "</div>";
            echo "</div>";
            echo "<div class='arc-mini-grid'>";
            echo "<div><span class='arc-field-label'>Quantity</span><div class='arc-field-value'>" . htmlspecialchars($item['claim_quantity']) . "</div></div>";
            if ($has_item_adjustment) {
                echo "<div><span class='arc-field-label'>Adjustment</span><div class='arc-field-value'>RM " . htmlspecialchars(number_format((float)$item['adjustment_amount'], 2)) . "</div></div>";
            }
            echo "</div>";
            if ($has_appendix_data) {
                $appendix = json_decode((string)($item['appendix_data'] ?? ''), true);
                if (is_array($appendix) && !empty(array_filter($appendix))) {
                    echo "<div class='arc-appendix'>";
                    echo "<div class='arc-appendix-title'>Category Form</div>";
                    echo "<div class='arc-grid'>";
                    foreach ($appendix as $key => $value) {
                        echo "<div><span class='arc-field-label'>" . htmlspecialchars(reconciliation_details_format_label($key)) . "</span><div class='arc-field-value'>" . reconciliation_details_render_value($value) . "</div></div>";
                    }
                    echo "</div></div>";
                }
            }
            if ($has_proof) {
                echo "<div class='arc-documents'>";
                echo "<span class='arc-field-label'>Supporting Documents</span>";
                if (!empty($item['proof_file'])) {
                    $proof_files = json_decode((string)$item['proof_file'], true);
                    if (!is_array($proof_files)) {
                        $proof_files = [$item['proof_file']];
                    }

                    foreach (array_filter($proof_files) as $index => $proof_file) {
                        echo "<a href='" . htmlspecialchars($receipt_base_path . $proof_file) . "' target='_blank' class='btn btn-sm btn-outline-primary m-r-5 m-b-5'>Document " . ($index + 1) . "</a>";
                    }
                } else {
                    echo "<div class='text-muted'>Not uploaded</div>";
                }
                echo "</div>";
            }
            echo "</div>";
        }
    } else {
        echo "<div class='text-center text-muted'>No items found.</div>";
    }
    echo "</div></div>";
    echo "</div>";
}
?>
