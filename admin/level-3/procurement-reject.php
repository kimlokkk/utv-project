<?php
    // Start the session and include required files
    session_start();
    include '../../db_connect/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gunakan POST
        // Validasi input
        if (empty($_GET['project_id']) || empty($_GET['procurement_id']) || empty($_GET['project_no']) || empty($_GET['remark'])|| empty($_GET['staff_id'])) {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data. Please check all required fields.']);
            exit;
        }
    
        // Ambil data dari POST
        $project_id = mysqli_real_escape_string($db, $_GET['project_id']);
        $project_no = mysqli_real_escape_string($db, $_GET['project_no']);
        $procurement_id = mysqli_real_escape_string($db, $_GET['procurement_id']);
        $staff_id = mysqli_real_escape_string($db, $_GET['staff_id']);
        $remark = mysqli_real_escape_string($db, $_GET['remark']);
        $date = date('Y-m-d H:i:s'); // Tarikh dan masa semasa
    
        // Mulakan transaksi
        mysqli_begin_transaction($db);
    
        try {
            // Kemas kini status dalam jadual 'procurement'
            $update_query = "UPDATE procurement 
                             SET status = 'Rejected - $remark'
                             WHERE id = '$procurement_id'";
            $update_result = mysqli_query($db, $update_query);
    
            if (!$update_result) {
                throw new Exception('Failed to update procurement status.');
            }
    
            // Masukkan rekod ke dalam jadual 'project_tracker'
            $tracker_query = "INSERT INTO project_tracker (project_id, project_no, remark, date) 
                              VALUES ('$project_id', '$project_no', 'Procurement application has been rejected by admin ($staff_id); $remark', '$date')";
            $tracker_result = mysqli_query($db, $tracker_query);
    
            if (!$tracker_result) {
                throw new Exception('Failed to insert record into project tracker.');
            }
    
            // Commit transaksi
            mysqli_commit($db);
    
            echo json_encode(['success' => true, 'message' => 'Procurement has been rejected successfully!']);
        } catch (Exception $e) {
            // Rollback transaksi jika berlaku ralat
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
?>
