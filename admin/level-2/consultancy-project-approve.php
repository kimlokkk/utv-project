<?php
session_start();

include '../../db_connect/db_connect.php';
include 'auth_check.php';
include '../../function/function.php';

// PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set("Asia/Kuala_Lumpur");

function generateFinalProjectNo($db, $prefix, $tempProjectNo)
{
    $date_code = date('Ym');
    if (preg_match('/^TEMP(\d{6})\d{4}$/', $tempProjectNo, $matches)) {
        $date_code = $matches[1];
    }

    $like = mysqli_real_escape_string($db, $prefix . $date_code . '%');
    $sequence_query = "
        SELECT MAX(CAST(RIGHT(project_no, 4) AS UNSIGNED)) AS last_sequence
        FROM project
        WHERE project_no LIKE '$like'
          AND project_no REGEXP '^{$prefix}{$date_code}[0-9]{4}$'
    ";
    $sequence_result = mysqli_query($db, $sequence_query);
    $sequence_data = $sequence_result ? mysqli_fetch_assoc($sequence_result) : null;
    $next_sequence = ((int)($sequence_data['last_sequence'] ?? 0)) + 1;

    do {
        $project_no = $prefix . $date_code . str_pad($next_sequence, 4, '0', STR_PAD_LEFT);
        $escaped_project_no = mysqli_real_escape_string($db, $project_no);
        $exists_result = mysqli_query($db, "SELECT id FROM project WHERE project_no = '$escaped_project_no' LIMIT 1");
        $exists = $exists_result && mysqli_num_rows($exists_result) > 0;
        $next_sequence++;
    } while ($exists);

    return $project_no;
}

function sendProjectApprovedEmail($leaderEmail, $leaderName, $projectTitle, $projectNo)
{
    if (empty($leaderEmail)) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // SMTP config - adjust ikut setting email server kau
        $mail->isSMTP();
        $mail->Host       = 'mail.domei.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'developer@domei.io';
        $mail->Password   = 'domei@1234';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('noreply@domei.io', 'Project Management System');
        $mail->addAddress($leaderEmail, $leaderName);

        $mail->isHTML(true);
        $mail->Subject = "Project Approved - {$projectNo}";

        $safeName  = htmlspecialchars($leaderName ?: 'Project Leader');
        $safeTitle = htmlspecialchars($projectTitle);
        $safeNo    = htmlspecialchars($projectNo);

        $mail->Body = "
            <p>Dear {$safeName},</p>

            <p>Your project has been approved.</p>

            <table cellpadding='6' cellspacing='0' border='0'>
                <tr>
                    <td><strong>Project No</strong></td>
                    <td>: {$safeNo}</td>
                </tr>
                <tr>
                    <td><strong>Project Title</strong></td>
                    <td>: {$safeTitle}</td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>: Approved</td>
                </tr>
            </table>

            <p>You may now proceed with the next process in the system.</p>

            <p>Thank you.</p>
        ";

        $mail->AltBody = "Dear {$safeName},\n\nYour project has been approved.\n\nProject No: {$safeNo}\nProject Title: {$safeTitle}\nStatus: Approved\n\nThank you.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Jangan stop approve flow kalau email gagal
        error_log("Project approval email failed: " . $mail->ErrorInfo);
        return false;
    }
}

if (isset($_GET['id'])) {
    $project_id = mysqli_real_escape_string($db, $_GET['id']);

    // Update project status
    $update_query = "UPDATE project SET project_status = 'Approved' WHERE id = '$project_id'";
    $update_result = mysqli_query($db, $update_query);

    /*
        IMPORTANT:
        Adjust column/table name ikut DB kau.

        Assumption:
        - project.project_leader stores staff_id/user id of leader
        - user_profile.user_id or user_profile.staff_id matches project_leader
        - user_profile has user_full_name and user_email
    */
    $select_query = "
        SELECT 
            p.project_no,
            p.project_title,
            p.project_leader,
            p.leader_id,
            u.full_name AS leader_name,
            u.email AS leader_email
        FROM project p
        LEFT JOIN uitm_staff u 
            ON u.id = p.leader_id
        WHERE p.id = '$project_id'
        LIMIT 1
    ";

    $select_result = mysqli_query($db, $select_query);

    if ($update_result && $select_result && mysqli_num_rows($select_result) > 0) {
        $project_data = mysqli_fetch_assoc($select_result);

        $project_no    = $project_data['project_no'];
        $project_title = $project_data['project_title'] ?? '';
        $leader_name   = $project_data['leader_name'] ?? '';
        $leader_email  = $project_data['leader_email'] ?? '';

        // Check if project_no starts with TEMP and update to CC
        if (strpos($project_no, 'TEMP') === 0) {
            $new_project_no = generateFinalProjectNo($db, 'CC', $project_no);

            $update_project_no_query = "
                UPDATE project 
                SET project_no = '$new_project_no' 
                WHERE id = '$project_id'
            ";

            mysqli_query($db, $update_project_no_query);
            mysqli_query($db, "UPDATE project_members_consultant SET project_no = '$new_project_no' WHERE project_id = '$project_id'");
            $project_no = $new_project_no;
        }

        // Insert into project_tracker
        $staff_id = $userData['staff_id'] ?? 'Unknown';
        $remark = "Project has been approved ({$staff_id})";
        $date = date('Y-m-d');

        $insert_query = "
            INSERT INTO project_tracker 
            (project_id, project_no, remark, date) 
            VALUES 
            ('$project_id', '$project_no', '$remark', '$date')
        ";

        $insert_result = mysqli_query($db, $insert_query);

        if ($insert_result) {
            // Send email to project leader
            sendProjectApprovedEmail(
                $leader_email,
                $leader_name,
                $project_title,
                $project_no
            );

            header("Location: consultancy-project-info.php?update=approve-success&id=$project_id");
            exit();
        }
    }

    header("Location: consultancy-project-info.php?update=approve-fail&id=$project_id");
    exit();

} else {
    header("Location: consultancy-project-list.php?update=invalid-id");
    exit();
}
?>
