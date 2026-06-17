<?php
header('Content-Type: application/json'); // Tetapkan header untuk JSON
include '../db_connect/db_connect.php'; // Sambungkan ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? null;

    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID is missing. Please provide a valid project.']);
        exit;
    }

    $required_fields = [
        'leader_id' => 'Leader ID',
        'project_leader' => 'Project Leader',
        'leader_ic' => 'Leader IC',
        'project_source' => 'Project Source',
        'project_no' => 'Project Number',
        'project_title' => 'Project Title',
        'project_type' => 'Project Type',
        'project_start' => 'Project Start Date',
        'project_end' => 'Project End Date',
        'registered_project_value' => 'Registered Project Value',
        'approval_external_work' => 'Approval for External Work',
        'client_company_name' => 'Client Company Name',
        'client_address' => 'Client Address',
        'client_contact' => 'Client Contact Number',
        'client_business_type' => 'Client Business Type',
    ];

    // Query untuk mendapatkan data projek
    $query = "SELECT " . implode(',', array_keys($required_fields)) . " FROM project WHERE id = '$project_id'";
    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Project not found. Please ensure the project ID is valid.']);
        exit;
    }

    $project_data = mysqli_fetch_assoc($result);
    $missing_fields = [];
    foreach ($required_fields as $field_key => $field_name) {
        if (empty($project_data[$field_key])) {
            $missing_fields[] = $field_name; // Gunakan nama mesra
        }
    }

    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'The following information is missing: ' . implode(', ', $missing_fields) . '. Please complete the required fields and try again.'
        ]);
        exit;
    }

    // Update status projek ke "Pending Verification"
    $update_query = "UPDATE project SET project_status = 'Pending Verification' WHERE id = '$project_id'";
    $update_result = mysqli_query($db, $update_query);

    if ($update_result) {
        // Masukkan rekod ke dalam project_tracker
        $leader_ic = $project_data['leader_ic'];
        $project_no = $project_data['project_no'];
        $today = date('Y-m-d');
        $remark = "Project has been submitted for verification and approval. ($leader_ic)";

        $tracker_query = "INSERT INTO `project_tracker` (
                            `project_id`, 
                            `project_no`, 
                            `remark`, 
                            `date`
                          ) VALUES (
                            '$project_id', 
                            '$project_no', 
                            '$remark', 
                            '$today'
                          )";

        $tracker_result = mysqli_query($db, $tracker_query);

        if ($tracker_result) {
            echo json_encode(['success' => true, 'message' => 'Project submit successfully !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Project status updated, but failed to add tracker entry.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update project status. Please try again later.']);
    }
    exit;
}
?>
