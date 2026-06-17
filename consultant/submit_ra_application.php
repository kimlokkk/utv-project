<?php
include '../db_connect/db_connect.php';

$response = ['success' => false, 'message' => ''];

function ra_application_column_exists($db, $column) {
    $column = mysqli_real_escape_string($db, $column);
    $result = mysqli_query($db, "SHOW COLUMNS FROM research_assistant_application LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function ra_application_ensure_end_date_column($db) {
    if (ra_application_column_exists($db, 'end_date')) {
        return true;
    }

    return mysqli_query($db, "ALTER TABLE research_assistant_application ADD COLUMN end_date date DEFAULT NULL AFTER start_date");
}

function ra_application_payable_months($start_date, $end_date) {
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ra_ids = $_POST['ra_id'] ?? [];
    $names = $_POST['name'] ?? [];
    $ics = $_POST['ic_passport'] ?? [];
    $ptjs = $_POST['ptj_address'] ?? [];
    $expertises = $_POST['expertise'] ?? [];
    $start_dates = $_POST['start_date'] ?? [];
    $end_dates = $_POST['end_date'] ?? [];
    $payment_types = $_POST['payment_type'] ?? [];
    $budgets = $_POST['budget'] ?? [];
    $project_ids = $_POST['project_id'] ?? [];
    $has_end_date_column = ra_application_ensure_end_date_column($db);

    if (!$has_end_date_column) {
        $response['message'] = 'Unable to prepare End Date storage: ' . mysqli_error($db);
        echo json_encode($response);
        exit();
    }

    if (empty($ra_ids)) {
        $response['message'] = 'Please add at least one RA/RO appointment.';
        echo json_encode($response);
        exit();
    }

    for ($i = 0; $i < count($ra_ids); $i++) {
        $required_values = [
            $project_ids[$i] ?? '',
            $ra_ids[$i] ?? '',
            $names[$i] ?? '',
            $ics[$i] ?? '',
            $ptjs[$i] ?? '',
            $expertises[$i] ?? '',
            $start_dates[$i] ?? '',
            $end_dates[$i] ?? '',
            $payment_types[$i] ?? '',
            $budgets[$i] ?? ''
        ];

        foreach ($required_values as $value) {
            if (trim((string)$value) === '') {
                $response['message'] = 'Please complete all required fields before submitting.';
                echo json_encode($response);
                exit();
            }
        }

        $start_date_raw = $start_dates[$i];
        $end_date_raw = $end_dates[$i];
        $duration = ra_application_payable_months($start_date_raw, $end_date_raw);

        if ($duration <= 0) {
            $response['message'] = 'End Date must be on or after Start Date.';
            echo json_encode($response);
            exit();
        }

        $budget_value = (float)$budgets[$i];
        if ($budget_value <= 0) {
            $response['message'] = 'Monthly allowance/wage must be greater than 0.';
            echo json_encode($response);
            exit();
        }

        $ra_id = mysqli_real_escape_string($db, $ra_ids[$i]);
        $name = mysqli_real_escape_string($db, $names[$i]);
        $ic = mysqli_real_escape_string($db, $ics[$i]);
        $ptj = mysqli_real_escape_string($db, $ptjs[$i]);
        $expertise = mysqli_real_escape_string($db, $expertises[$i]);
        $start_date = mysqli_real_escape_string($db, $start_date_raw);
        $end_date = mysqli_real_escape_string($db, $end_date_raw);
        $payment_type = mysqli_real_escape_string($db, $payment_types[$i]);
        $budget = number_format($budget_value, 2, '.', '');
        $project_id = mysqli_real_escape_string($db, $project_ids[$i]);

        $columns = [
            'project_id',
            'ra_id',
            'name',
            'ic',
            'ptj_address',
            'expertise',
            'duration',
            'start_date',
            'payment_type',
            'budget',
            'status'
        ];

        $values = [
            "'$project_id'",
            "'$ra_id'",
            "'$name'",
            "'$ic'",
            "'$ptj'",
            "'$expertise'",
            "'$duration'",
            "'$start_date'",
            "'$payment_type'",
            "'$budget'",
            "'Pending Verification'"
        ];

        if ($has_end_date_column) {
            $columns[] = 'end_date';
            $values[] = "'$end_date'";
        }

        $query = "INSERT INTO research_assistant_application (" . implode(', ', $columns) . ")
                  VALUES (" . implode(', ', $values) . ")";

        if (!mysqli_query($db, $query)) {
            $response['message'] = 'DB Error: ' . mysqli_error($db);
            echo json_encode($response);
            exit();
        }
    }

    $response['success'] = true;
    $response['message'] = 'RA/RO successfully submitted.';
}

echo json_encode($response);
?>
