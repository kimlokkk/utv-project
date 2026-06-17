<?php
    session_start();
    include '../../db_connect/db_connect.php';
    
    header('Content-Type: application/json');

    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $error['message']]);
        }
    });

    function level3_ra_edit_column_exists($db, $column) {
        $column = mysqli_real_escape_string($db, $column);
        $result = mysqli_query($db, "SHOW COLUMNS FROM research_assistant_application LIKE '$column'");
        return $result && mysqli_num_rows($result) > 0;
    }

    function level3_ra_edit_ensure_end_date_column($db) {
        if (level3_ra_edit_column_exists($db, 'end_date')) {
            return true;
        }

        return mysqli_query($db, "ALTER TABLE research_assistant_application ADD COLUMN end_date date DEFAULT NULL AFTER start_date");
    }

    function level3_ra_edit_payable_months($start_date, $end_date) {
        $start = DateTime::createFromFormat('Y-m-d', $start_date);
        $end = DateTime::createFromFormat('Y-m-d', $end_date);

        if (!$start || !$end || $end < $start) {
            return 0;
        }

        $months = (((int)$end->format('Y') - (int)$start->format('Y')) * 12) + ((int)$end->format('n') - (int)$start->format('n'));

        if ((int)$end->format('j') > (int)$start->format('j')) {
            $months++;
        }

        if (
            $months === 0 &&
            (int)$start->format('Y') === (int)$end->format('Y') &&
            (int)$start->format('n') === (int)$end->format('n')
        ) {
            $months = 1;
        }

        return $months;
    }
    
    if (
        isset($_POST['raa_id'], $_POST['start_date'], $_POST['end_date'], $_POST['payment_type'], $_POST['budget'])
    ) {
        $raa_id = mysqli_real_escape_string($db, $_POST['raa_id']);
        $start_date_raw = $_POST['start_date'];
        $end_date_raw = $_POST['end_date'];
        $duration = level3_ra_edit_payable_months($start_date_raw, $end_date_raw);
        $payment_type = mysqli_real_escape_string($db, $_POST['payment_type']);
        $budget_value = (float)$_POST['budget'];

        if ($duration <= 0) {
            echo json_encode(['success' => false, 'message' => 'End Date must be on or after Start Date.']);
            exit;
        }

        if ($budget_value <= 0) {
            echo json_encode(['success' => false, 'message' => 'Monthly allowance/wage must be greater than 0.']);
            exit;
        }

        if (!level3_ra_edit_ensure_end_date_column($db)) {
            echo json_encode(['success' => false, 'message' => 'Unable to prepare End Date storage: ' . mysqli_error($db)]);
            exit;
        }

        $start_date = mysqli_real_escape_string($db, $start_date_raw);
        $end_date = mysqli_real_escape_string($db, $end_date_raw);
        $budget = number_format($budget_value, 2, '.', '');
    
        $update = "UPDATE research_assistant_application SET 
            start_date = '$start_date',
            end_date = '$end_date',
            duration = '$duration',
            payment_type = '$payment_type',
            budget = '$budget',
            return_remark = ''
            WHERE id = '$raa_id'";
    
        if (mysqli_query($db, $update)) {
            echo json_encode(['success' => true, 'message' => 'Application updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    }
?>
