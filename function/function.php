<?php

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

	if(isset($_POST['btn_login']))
	{
		doLogin();
	}
	
	if(isset($_POST['btn_loginAdmin']))
	{
		doLoginAdmin();
	}

	if(isset($_GET['logout']))
	{
		session_destroy();
		unset($_SESSION['Admin']);
		header('Location: index.php');
	}
	
	if (isset($_POST['btn_registerRA'])) {
        btn_RegisterRA();
        exit; // STOP rendering HTML after JSON output
    }

	
	if (isset($_POST['btn_registerConsultant'])) 
	{
		btn_RegisterConsultant();
        exit;
	}
	
	if (isset($_POST['btn_registerNonUitmStaff'])) 
	{
	    btn_RegisterNonUitmStaff();
        exit;
	}
	
	if (isset($_POST['btn_registerAdmin'])) 
	{
		btn_RegisterAdmin();
	}
	
	if (isset($_POST['btn_registerVendor'])) 
	{
		btn_RegisterVendor();
        exit;
	}
	
	
	if (isset($_POST['btn_updateProfileConsultant'])) 
	{
		btn_UpdateProfileConsultant();
	}
	
	if (isset($_POST['btn_updateProfileResearch'])) 
	{
		btn_UpdateProfileResearch();
	}
	
	//--------------Consultancy Project Part-----------------------------------
	
	if (isset($_POST['btn_saveConsultancyProject']) || (isset($_POST['action']) && $_POST['action'] === 'save')) 
	{
		btn_SaveConsultancyProject();
	}
	
	if (isset($_POST['btn_updateConsultancyProject'])) 
	{
		btn_UpdateConsultancyProject();
	}
	
	if (isset($_POST['btn_submitConsultancyProject'])) 
	{
		btn_SubmitConsultancyProject();
	}
	
	if (isset($_POST['btn_addMembersConsultancyProject'])) 
	{
		btn_AddMembersConsultancyProject();
	}
	
	if (isset($_POST['btn_addProjectTimeline'])) 
	{
		btn_AddProjectTimeline();
	}
	
	if (isset($_POST['btn_addProjectMembers'])) 
	{
		btn_AddProjectMembers();
	}
	
	//--------------Training Project Part-----------------------------------
	
	if (isset($_POST['btn_saveTrainingProject'])) 
	{
		btn_SaveTrainingProject();
	}
	
	if (isset($_POST['btn_updateTrainingProject'])) 
	{
		btn_UpdateTrainingProject();
	}
	
	if (isset($_POST['btn_submitTrainingProject'])) 
	{
		btn_SubmitTrainingProject();
	}
	
	if (isset($_POST['btn_addMembersTrainingProject'])) 
	{
		btn_AddMembersTrainingProject();
	}
	
	//--------------Research Approval----------------------------------------
	
	if (isset($_POST['btn_rejectResearchAssistant'])) {
        btn_RejectResearchAssistant();
    }
    
    if (isset($_POST['btn_approveResearchAssistant'])) {
        btn_ApproveResearchAssistant();
    }
    
    if (isset($_POST['btn_verifyResearchAssistant'])) {
        btn_VerifyResearchAssistant();
    }
    
    if (isset($_POST['btn_verifyResearchAssistantAppointment'])) {
        btn_VerifyResearchAssistantAppointment();
    }
    
    if (isset($_POST['btn_approveResearchAssistantAppointment'])) {
        btn_ApproveResearchAssistantAppointment();
    }
    
    if (isset($_POST['btn_rejectResearchAssistantAppointment'])) {
        btn_RejectResearchAssistantAppointment();
    }
	
	/*--------------------------------------------------------------------------*/

	function doLogin()
    {
        global $db;
    
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $login_option = trim($_POST['login_option']);
    
        if ($email == '' || $password == '') {
            echo "<script>alert('Fill up the empty fields');</script>";
            return;
        }
    
        if ($login_option === 'Research Assistant') {
            $query = "SELECT * FROM research_assistant WHERE email='$email'";
            $redirect_path = 'research/';
            $session_role = 'Research Assistant';
        } elseif ($login_option === 'Consultant') {
            $query = "SELECT * FROM uitm_staff WHERE email='$email'";
            $redirect_path = 'consultant/';
            $session_role = 'Consultant';
        } elseif ($login_option === 'Vendor') {
            $query = "SELECT * FROM vendor WHERE email='$email'";
            $redirect_path = 'vendor/';
            $session_role = 'Vendor';
        } else {
            echo "<script>alert('Invalid login option selected: $login_option');</script>";
            return;
        }
    
        $result = mysqli_query($db, $query);
    
        if (!$result) {
            echo "<script>alert('Database query failed: " . mysqli_error($db) . "');</script>";
            return;
        }
    
        $user = mysqli_fetch_assoc($result);
    
        if (!$user) {
            echo "<script>alert('No user found with that email');</script>";
            return;
        }
    
        // Password verification
        if (password_verify($password, $user['password'])) {
            $_SESSION[$session_role] = $user['email'];
            echo "<script>alert('Login successful as $session_role'); window.location.href = '$redirect_path';</script>";
            exit();
        } else {
            echo "<script>alert('Wrong password');</script>";
        }
    }
    
    function doLoginAdmin()
    {
        global $db;
    
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
    
        if ($email == '' || $password == '') {
            echo "<script>alert('Fill up the empty fields');</script>";
            return;
        }
    
        $query = "SELECT * FROM admin WHERE email='$email'";
        $result = mysqli_query($db, $query);
    
        if (!$result) {
            echo "<script>alert('Database error: " . mysqli_error($db) . "');</script>";
            return;
        }
    
        $user = mysqli_fetch_assoc($result);
    
        if (!$user) {
            echo "<script>alert('No admin found with that email');</script>";
            return;
        }
    
        if (password_verify($password, $user['password'])) {
            $level = $user['level'];
            $role = $user['role'];
            
            // Set session with level-specific keys
            $_SESSION['Admin_Level'] = $level;
            $_SESSION['Admin_Email'] = $user['email'];
            $_SESSION['user_data_' . $level] = $user;
            
            // Redirect ikut level & role
            switch ($level) {
                case 'Level 1':
                    $redirect_path = 'level-1/index.php';
                    break;
                case 'Level 2':
                    $redirect_path = 'level-2/index.php';
                    break;
                case 'Level 3':
                    $redirect_path = 'level-3/index.php';
                    break;
                case 'Level 4':
                    if ($role === 'Financial') {
                        $redirect_path = 'finance/index.php';
                    } else {
                        $redirect_path = 'level-4/index.php';
                    }
                    break;
                case 'Level 5':
                    $redirect_path = 'level-5/index.php';
                    break;
                case 'Level 6':
                    $redirect_path = 'level-6/index.php';
                    break;
                default:
                    $redirect_path = 'index.php';
                    break;
            }
    
            echo "<script>alert('Login successful ($level)'); window.location.href = '$redirect_path';</script>";
            exit();
        } else {
            echo "<script>alert('Wrong password');</script>";
        }
    }

    function btn_RegisterRA()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y-m-d");
    
        try {
            $full_name             = mysqli_real_escape_string($db, trim($_POST['full_name']));
            $designation           = mysqli_real_escape_string($db, trim($_POST['designation']));
            $ic                    = mysqli_real_escape_string($db, trim($_POST['ic']));
            $phone                 = mysqli_real_escape_string($db, trim($_POST['phone']));
            $email                 = mysqli_real_escape_string($db, trim($_POST['email']));
            $email_2               = mysqli_real_escape_string($db, trim($_POST['email_2']));
            $tin_no                = mysqli_real_escape_string($db, trim($_POST['tin_no']));
            $ptj_id                = (int) $_POST['ptj_id'];
            $ptj_address_permanent = mysqli_real_escape_string($db, trim($_POST['ptj_address_permanent']));
            $ptj_address_current   = mysqli_real_escape_string($db, trim($_POST['ptj_address_current']));
            $gender                = mysqli_real_escape_string($db, trim($_POST['gender']));
            $citizenship           = mysqli_real_escape_string($db, trim($_POST['citizenship']));
            $marital_status        = mysqli_real_escape_string($db, trim($_POST['marital_status']));
            $epf_no                = mysqli_real_escape_string($db, trim($_POST['epf_no']));
            $socso_no              = mysqli_real_escape_string($db, trim($_POST['socso_no']));
            $income_tax_no         = mysqli_real_escape_string($db, trim($_POST['income_tax_no']));
            $employment_position   = mysqli_real_escape_string($db, trim($_POST['employment_position']));
            $expertise             = mysqli_real_escape_string($db, trim($_POST['expertise']));
            $password              = $_POST['password'];
            $password_confirm      = $_POST['password_confirm'];
    
            $same_as_permanent     = isset($_POST['same_as_permanent']) ? 1 : 0;
            $bank_id               = (int) $_POST['bank_id'];
            $account_number        = preg_replace('/\D/', '', $_POST['no_account']);
    
            if ($password !== $password_confirm) {
                throw new Exception("Password and confirmation password do not match.");
            }
    
            if ($same_as_permanent) {
                $ptj_address_current = $ptj_address_permanent;
            }
    
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
            // Validate PTJ
            $ptjSql = "SELECT ptj_id, ptj_name FROM ptj_list WHERE ptj_id = $ptj_id AND is_active = 1 LIMIT 1";
            $ptjRes = mysqli_query($db, $ptjSql);
            if (!$ptjRes || mysqli_num_rows($ptjRes) === 0) {
                throw new Exception("Selected PTJ is invalid.");
            }
            $ptj_address = $ptj_address_current;
    
            // Validate bank
            $bankSql = "SELECT bank_id, bank_name, account_length_rule, min_length, max_length
                        FROM bank_list
                        WHERE bank_id = $bank_id AND is_active = 1
                        LIMIT 1";
            $bankRes = mysqli_query($db, $bankSql);
    
            if (!$bankRes || mysqli_num_rows($bankRes) === 0) {
                throw new Exception("Selected bank is invalid.");
            }
    
            $bankData = mysqli_fetch_assoc($bankRes);
            $bank_name = mysqli_real_escape_string($db, $bankData['bank_name']);
            $accLen = strlen($account_number);
            $valid = false;
    
            if ($bankData['bank_name'] === 'CIMB Bank Berhad') {
                $valid = in_array($accLen, [10, 14]);
            } elseif ($bankData['bank_name'] === 'Hong Leong Bank Berhad') {
                $valid = in_array($accLen, [11, 13]);
            } elseif ($bankData['bank_name'] === 'United Overseas Bank (Malaysia) Berhad (UOB)') {
                $valid = in_array($accLen, [10, 11]);
            } elseif ((int)$bankData['min_length'] === (int)$bankData['max_length']) {
                $valid = ($accLen === (int)$bankData['min_length']);
            } else {
                $valid = ($accLen >= (int)$bankData['min_length'] && $accLen <= (int)$bankData['max_length']);
            }
    
            if (!$valid) {
                throw new Exception("Invalid account number length for " . $bankData['bank_name'] . ". Expected " . $bankData['account_length_rule']);
            }
    
            // Upload files
            $upload_errors = [];
            $files_to_upload = [
                'bank_statement_file'   => '../registration-documents/bank-statement/',
                'copy_ic_file'          => '../registration-documents/ic-folder/',
                'copy_certificate_file' => '../registration-documents/certificate-folder/',
            ];
    
            $uploaded_files = [];
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
            foreach ($files_to_upload as $file_key => $upload_path) {
                if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== 0) {
                    $upload_errors[] = "File upload failed for {$file_key}.";
                    continue;
                }
    
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
    
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp  = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension. Only PDF, JPG, JPEG and PNG are allowed.";
                    continue;
                }
    
                $safe_ic = preg_replace('/[^A-Za-z0-9]/', '', $ic);
                $new_file_name = time() . "_" . $safe_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
    
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            }
    
            if (!empty($upload_errors)) {
                throw new Exception(implode("\n", $upload_errors));
            }
    
            $query = "INSERT INTO `research_assistant` (
                        `full_name`,
                        `designation`,
                        `ic`,
                        `phone`,
                        `email`,
                        `email_2`,
                        `tin_no`,
                        `ptj_id`,
                        `ptj_address`,
                        `ptj_address_permanent`,
                        `ptj_address_current`,
                        `same_as_permanent`,
                        `gender`,
                        `citizenship`,
                        `marital_status`,
                        `epf_no`,
                        `socso_no`,
                        `income_tax_no`,
                        `employment_position`,
                        `expertise`,
                        `bank_id`,
                        `bank_name`,
                        `no_account`,
                        `bank_statement_file`,
                        `copy_ic_file`,
                        `copy_certificate_file`,
                        `password`,
                        `date_register`,
                        `status`
                    ) VALUES (
                        '$full_name',
                        '$designation',
                        '$ic',
                        '$phone',
                        '$email',
                        '$email_2',
                        '$tin_no',
                        '$ptj_id',
                        '$ptj_address',
                        '$ptj_address_permanent',
                        '$ptj_address_current',
                        '$same_as_permanent',
                        '$gender',
                        '$citizenship',
                        '$marital_status',
                        '$epf_no',
                        '$socso_no',
                        '$income_tax_no',
                        '$employment_position',
                        '$expertise',
                        '$bank_id',
                        '$bank_name',
                        '$account_number',
                        '" . $uploaded_files['bank_statement_file'] . "',
                        '" . $uploaded_files['copy_ic_file'] . "',
                        '" . $uploaded_files['copy_certificate_file'] . "',
                        '$hashed_password',
                        '$today',
                        'Pending Verification'
                    )";
    
            if (mysqli_query($db, $query)) {
                echo json_encode(['success' => true, 'message' => 'Registration successful!']);
            } else {
                throw new Exception('Database error: ' . mysqli_error($db));
            }
    
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    function btn_RegisterConsultant()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y-m-d");
    
        try {
            $full_name                = mysqli_real_escape_string($db, trim($_POST['full_name']));
            $designation              = mysqli_real_escape_string($db, trim($_POST['designation']));
            $staff_id                 = mysqli_real_escape_string($db, trim($_POST['staff_id']));
            $ic                       = mysqli_real_escape_string($db, trim($_POST['ic']));
            $phone                    = mysqli_real_escape_string($db, trim($_POST['phone']));
            $email                    = mysqli_real_escape_string($db, trim($_POST['email']));
            $email_2                  = mysqli_real_escape_string($db, trim($_POST['email_2']));
            $tin_no                    = mysqli_real_escape_string($db, trim($_POST['tin_no']));
            $uitm_state_permanent     = mysqli_real_escape_string($db, trim($_POST['uitm_state_permanent']));
            $ptj_id                   = (int) $_POST['ptj_id'];
            $campus_address_permanent = mysqli_real_escape_string($db, trim($_POST['campus_address_permanent']));
            $uitm_state_current       = mysqli_real_escape_string($db, trim($_POST['uitm_state_current']));
            $campus_address_current   = mysqli_real_escape_string($db, trim($_POST['campus_address_current']));
            $gender                   = mysqli_real_escape_string($db, trim($_POST['gender']));
            $citizenship              = mysqli_real_escape_string($db, trim($_POST['citizenship']));
            $employment_position      = mysqli_real_escape_string($db, trim($_POST['employment_position']));
            $expertise                = mysqli_real_escape_string($db, trim($_POST['expertise']));
            $password                 = $_POST['password'];
            $password_confirm         = $_POST['password_confirm'];
    
            $bank_id                  = (int) $_POST['bank_id'];
            $account_number           = preg_replace('/\D/', '', $_POST['no_account']);
            $same_as_permanent        = isset($_POST['same_as_permanent']) ? 1 : 0;
    
            if ($password !== $password_confirm) {
                throw new Exception("Password and confirmation password do not match.");
            }
    
            if ($same_as_permanent) {
                $uitm_state_current = $uitm_state_permanent;
                $campus_address_current = $campus_address_permanent;
            }
    
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Validate PTJ
            $ptjSql = "SELECT ptj_id, ptj_name FROM ptj_list WHERE ptj_id = $ptj_id AND is_active = 1 LIMIT 1";
            $ptjRes = mysqli_query($db, $ptjSql);
            if (!$ptjRes || mysqli_num_rows($ptjRes) === 0) {
                throw new Exception("Selected PTJ is invalid.");
            }
            $ptjData = mysqli_fetch_assoc($ptjRes);
            $ptj = mysqli_real_escape_string($db, $ptjData['ptj_name']);
    
            // Validate bank
            $bankSql = "SELECT bank_id, bank_name, account_length_rule, min_length, max_length 
                        FROM bank_list 
                        WHERE bank_id = $bank_id AND is_active = 1 
                        LIMIT 1";
            $bankRes = mysqli_query($db, $bankSql);
    
            if (!$bankRes || mysqli_num_rows($bankRes) === 0) {
                throw new Exception("Selected bank is invalid.");
            }
    
            $bankData = mysqli_fetch_assoc($bankRes);
            $bank_name = mysqli_real_escape_string($db, $bankData['bank_name']);
            $accLen = strlen($account_number);
            $valid = false;
    
            if ($bankData['bank_name'] === 'CIMB Bank Berhad') {
                $valid = in_array($accLen, [10, 14]);
            } elseif ($bankData['bank_name'] === 'Hong Leong Bank Berhad') {
                $valid = in_array($accLen, [11, 13]);
            } elseif ($bankData['bank_name'] === 'United Overseas Bank (Malaysia) Berhad (UOB)') {
                $valid = in_array($accLen, [10, 11]);
            } elseif ((int)$bankData['min_length'] === (int)$bankData['max_length']) {
                $valid = ($accLen === (int)$bankData['min_length']);
            } else {
                $valid = ($accLen >= (int)$bankData['min_length'] && $accLen <= (int)$bankData['max_length']);
            }
    
            if (!$valid) {
                throw new Exception("Invalid account number length for " . $bankData['bank_name'] . ". Expected " . $bankData['account_length_rule']);
            }
    
            // Upload files
            $upload_errors = [];
            $files_to_upload = [
                'bank_statement_file' => '../registration-documents/bank-statement/',
                'copy_ic_file'        => '../registration-documents/ic-folder/',
            ];
    
            $uploaded_files = [];
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
            foreach ($files_to_upload as $file_key => $upload_path) {
                if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== 0) {
                    $upload_errors[] = "File upload failed for {$file_key}.";
                    continue;
                }
    
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
    
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp  = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension. Only PDF, JPG, JPEG and PNG are allowed.";
                    continue;
                }
    
                $safe_ic = preg_replace('/[^A-Za-z0-9]/', '', $ic);
                $new_file_name = time() . "_" . $safe_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
    
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            }
    
            if (!empty($upload_errors)) {
                throw new Exception(implode("\\n", $upload_errors));
            }
    
            $query = "INSERT INTO `uitm_staff` (
                        `full_name`,
                        `designation`,
                        `staff_id`,
                        `ic`,
                        `phone`,
                        `email`,
                        `email_2`,
                        `tin_no`,
                        `uitm_state_permanent`,
                        `ptj_id`,
                        `ptj`,
                        `campus_address_permanent`,
                        `same_as_permanent`,
                        `uitm_state_current`,
                        `campus_address_current`,
                        `company_name`,
                        `company_address`,
                        `gender`,
                        `citizenship`,
                        `employment_position`,
                        `expertise`,
                        `bank_name`,
                        `bank_id`,
                        `no_account`,
                        `bank_statement_file`,
                        `copy_ic_file`,
                        `password`,
                        `date_register`,
                        `uitm_staff`
                    ) VALUES (
                        '$full_name',
                        '$designation',
                        '$staff_id',
                        '$ic',
                        '$phone',
                        '$email',
                        '$email_2',
                        '$tin_no',
                        '$uitm_state_permanent',
                        '$ptj_id',
                        '$ptj',
                        '$campus_address_permanent',
                        '$same_as_permanent',
                        '$uitm_state_current',
                        '$campus_address_current',
                        '',
                        '',
                        '$gender',
                        '$citizenship',
                        '$employment_position',
                        '$expertise',
                        '$bank_name',
                        '$bank_id',
                        '$account_number',
                        '" . $uploaded_files['bank_statement_file'] . "',
                        '" . $uploaded_files['copy_ic_file'] . "',
                        '$hashed_password',
                        '$today',
                        '1'
                    )";
    
            if (mysqli_query($db, $query)) {
                echo json_encode(['success' => true, 'message' => 'Registration successful!']);
            } else {
                throw new Exception("Database error: " . mysqli_error($db));
            }
    
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    function btn_RegisterNonUitmStaff()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y-m-d");
    
        try {
            $full_name                 = mysqli_real_escape_string($db, trim($_POST['full_name']));
            $designation               = mysqli_real_escape_string($db, trim($_POST['designation']));
            $ic                        = mysqli_real_escape_string($db, trim($_POST['ic']));
            $phone                     = mysqli_real_escape_string($db, trim($_POST['phone']));
            $email                     = mysqli_real_escape_string($db, trim($_POST['email']));
            $email_2                   = mysqli_real_escape_string($db, trim($_POST['email_2']));
            $tin_no                    = mysqli_real_escape_string($db, trim($_POST['tin_no']));
            $company_name              = mysqli_real_escape_string($db, trim($_POST['company_name']));
            $company_address_permanent = mysqli_real_escape_string($db, trim($_POST['company_address_permanent']));
            $company_address_current   = mysqli_real_escape_string($db, trim($_POST['company_address_current']));
            $gender                    = mysqli_real_escape_string($db, trim($_POST['gender']));
            $citizenship               = mysqli_real_escape_string($db, trim($_POST['citizenship']));
            $employment_position       = mysqli_real_escape_string($db, trim($_POST['employment_position']));
            $expertise                 = mysqli_real_escape_string($db, trim($_POST['expertise']));
            $password                  = $_POST['password'];
            $password_confirm          = $_POST['password_confirm'];
    
            $same_as_permanent         = isset($_POST['same_as_permanent']) ? 1 : 0;
            $bank_id                   = (int) $_POST['bank_id'];
            $account_number            = preg_replace('/\D/', '', $_POST['no_account']);
    
            if ($password !== $password_confirm) {
                throw new Exception("Password and confirmation password do not match.");
            }
    
            if ($same_as_permanent) {
                $company_address_current = $company_address_permanent;
            }
    
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
            // Validate selected bank
            $bankSql = "SELECT bank_id, bank_name, account_length_rule, min_length, max_length
                        FROM bank_list
                        WHERE bank_id = $bank_id AND is_active = 1
                        LIMIT 1";
            $bankRes = mysqli_query($db, $bankSql);
    
            if (!$bankRes || mysqli_num_rows($bankRes) === 0) {
                throw new Exception("Selected bank is invalid.");
            }
    
            $bankData = mysqli_fetch_assoc($bankRes);
            $bank_name = mysqli_real_escape_string($db, $bankData['bank_name']);
            $accLen = strlen($account_number);
            $valid = false;
    
            if ($bankData['bank_name'] === 'CIMB Bank Berhad') {
                $valid = in_array($accLen, [10, 14]);
            } elseif ($bankData['bank_name'] === 'Hong Leong Bank Berhad') {
                $valid = in_array($accLen, [11, 13]);
            } elseif ($bankData['bank_name'] === 'United Overseas Bank (Malaysia) Berhad (UOB)') {
                $valid = in_array($accLen, [10, 11]);
            } elseif ((int)$bankData['min_length'] === (int)$bankData['max_length']) {
                $valid = ($accLen === (int)$bankData['min_length']);
            } else {
                $valid = ($accLen >= (int)$bankData['min_length'] && $accLen <= (int)$bankData['max_length']);
            }
    
            if (!$valid) {
                throw new Exception("Invalid account number length for " . $bankData['bank_name'] . ". Expected " . $bankData['account_length_rule']);
            }
    
            // Upload files
            $upload_errors = [];
            $files_to_upload = [
                'bank_statement_file' => '../registration-documents/bank-statement/',
                'copy_ic_file'        => '../registration-documents/ic-folder/',
            ];
    
            $uploaded_files = [];
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
            foreach ($files_to_upload as $file_key => $upload_path) {
                if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== 0) {
                    $upload_errors[] = "File upload failed for {$file_key}.";
                    continue;
                }
    
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
    
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp  = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension. Only PDF, JPG, JPEG and PNG are allowed.";
                    continue;
                }
    
                $safe_ic = preg_replace('/[^A-Za-z0-9]/', '', $ic);
                $new_file_name = time() . "_" . $safe_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
    
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            }
    
            if (!empty($upload_errors)) {
                throw new Exception(implode("\\n", $upload_errors));
            }
    
            $query = "INSERT INTO `uitm_staff` (
                        `full_name`,
                        `designation`,
                        `staff_id`,
                        `ic`,
                        `phone`,
                        `email`,
                        `email_2`,
                        `tin_no`,
                        `uitm_state_permanent`,
                        `ptj_id`,
                        `ptj`,
                        `campus_address_permanent`,
                        `same_as_permanent`,
                        `uitm_state_current`,
                        `campus_address_current`,
                        `company_name`,
                        `company_address`,
                        `gender`,
                        `citizenship`,
                        `employment_position`,
                        `expertise`,
                        `bank_name`,
                        `bank_id`,
                        `no_account`,
                        `bank_statement_file`,
                        `copy_ic_file`,
                        `password`,
                        `date_register`,
                        `uitm_staff`
                    ) VALUES (
                        '$full_name',
                        '$designation',
                        '',
                        '$ic',
                        '$phone',
                        '$email',
                        '$email_2',
                        '$tin_no',
                        '',
                        '0',
                        '',
                        '',
                        '$same_as_permanent',
                        '',
                        '',
                        '$company_name',
                        '$company_address_current',
                        '$gender',
                        '$citizenship',
                        '$employment_position',
                        '$expertise',
                        '$bank_name',
                        '$bank_id',
                        '$account_number',
                        '" . $uploaded_files['bank_statement_file'] . "',
                        '" . $uploaded_files['copy_ic_file'] . "',
                        '$hashed_password',
                        '$today',
                        '0'
                    )";
    
            if (mysqli_query($db, $query)) {
                echo json_encode(['success' => true, 'message' => 'Registration successful!']);
            } else {
                throw new Exception("Database error: " . mysqli_error($db));
            }
    
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    function btn_RegisterAdmin()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y-m-d H:i:s");
        
        // Sanitize and retrieve POST data
        $full_name                  = mysqli_real_escape_string($db, $_POST['full_name']);
        $staff_id                   = mysqli_real_escape_string($db, $_POST['staff_id']);
        $department                 = mysqli_real_escape_string($db, $_POST['department']);
        $role                       = mysqli_real_escape_string($db, $_POST['role']);
        $email                      = mysqli_real_escape_string($db, $_POST['email']);
        $level                      = mysqli_real_escape_string($db, $_POST['level']);
        $password                   = mysqli_real_escape_string($db, $_POST['password']);
    
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        // Construct SQL query
        $query = "INSERT INTO `admin` (
                    `name`, 
                    `staff_id`,
                    `department`,
                    `role`,
                    `email`, 
                    `level`, 
                    `password`,
                    `date_created`
                ) VALUES (
                    '$full_name', 
                    '$staff_id',
                    '$department',
                    '$role',
                    '$email', 
                    '$level', 
                    '$hashed_password',
                    '$today'
                )";
    
        // Execute query
        if (mysqli_query($db, $query)) {
            $_SESSION['registration_success'] = true;
            return;
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_RegisterVendor()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y-m-d H:i:s");
    
        try {
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];
    
            if ($password !== $password_confirm) {
                throw new Exception("Passwords do not match.");
            }
    
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
            $company_name        = mysqli_real_escape_string($db, trim($_POST['company_name']));
            $email               = mysqli_real_escape_string($db, trim($_POST['company_email']));
            $registered_address  = mysqli_real_escape_string($db, trim($_POST['registered_address']));
            $mailing_address     = mysqli_real_escape_string($db, trim($_POST['mailing_address']));
            $ssm_no_new          = mysqli_real_escape_string($db, trim($_POST['ssm_no_new']));
            $ssm_no_old          = mysqli_real_escape_string($db, trim($_POST['ssm_no_old']));
            $tin_no              = mysqli_real_escape_string($db, trim($_POST['tin_no']));
            $website             = mysqli_real_escape_string($db, trim($_POST['website']));
            $org_type            = mysqli_real_escape_string($db, trim($_POST['org_type']));
            $swift_code          = mysqli_real_escape_string($db, trim($_POST['swift_code']));
            $business_activities = mysqli_real_escape_string($db, trim($_POST['business_activities']));
            $msic_code           = mysqli_real_escape_string($db, trim($_POST['msic_code']));
            $contact_name        = mysqli_real_escape_string($db, trim($_POST['contact_name']));
            $contact_position    = mysqli_real_escape_string($db, trim($_POST['contact_position']));
            $contact_phone       = mysqli_real_escape_string($db, trim($_POST['contact_phone']));
            $contact_email       = mysqli_real_escape_string($db, trim($_POST['contact_email']));
            $bank_name           = mysqli_real_escape_string($db, trim($_POST['bank_name']));
            $bank_account        = mysqli_real_escape_string($db, trim($_POST['bank_account']));
            $bank_address        = mysqli_real_escape_string($db, trim($_POST['bank_address']));
            $declaration         = isset($_POST['declaration']) ? 1 : 0;
    
            if ($tin_no === '') {
                throw new Exception("TIN No is required.");
            }
    
            if ($ssm_no_new === '' || $ssm_no_old === '') {
                throw new Exception("Both New SSM No and Old SSM No are required.");
            }
    
            if ($business_activities === '') {
                throw new Exception("Type of Business Activities is required.");
            }
    
            $target_dir = __DIR__ . "/../registration-documents/vendor-file/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
    
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
            $files = [
                'bank_statement_file' => $_FILES['bank_statement_file'] ?? null,
                'ssm_file' => $_FILES['ssm_file'] ?? null
            ];
    
            $uploaded = [];
    
            foreach ($files as $key => $file) {
                if (!$file || $file['error'] !== 0) {
                    throw new Exception("Upload failed for {$key}.");
                }
    
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_extensions)) {
                    throw new Exception("Invalid file format for {$key}. Only PDF, JPG, JPEG and PNG are allowed.");
                }
    
                $new_name = uniqid($key . '_') . '.' . $ext;
                $target_path = $target_dir . $new_name;
    
                if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                    throw new Exception("Failed to upload file for {$key}.");
                }
    
                $uploaded[$key] = $new_name;
            }
    
            $query = "INSERT INTO vendor (
                password,
                company_name,
                email,
                registered_address,
                mailing_address,
                ssm_no_new,
                ssm_no_old,
                tin_no,
                website,
                org_type,
                swift_code,
                business_activities,
                msic_code,
                contact_name,
                contact_position,
                contact_phone,
                contact_email,
                bank_name,
                bank_account,
                bank_address,
                bank_statement_file,
                ssm_file,
                declaration,
                created_at,
                status
            ) VALUES (
                '$hashed_password',
                '$company_name',
                '$email',
                '$registered_address',
                '$mailing_address',
                '$ssm_no_new',
                '$ssm_no_old',
                '$tin_no',
                '$website',
                '$org_type',
                '$swift_code',
                '$business_activities',
                '$msic_code',
                '$contact_name',
                '$contact_position',
                '$contact_phone',
                '$contact_email',
                '$bank_name',
                '$bank_account',
                '$bank_address',
                '{$uploaded['bank_statement_file']}',
                '{$uploaded['ssm_file']}',
                '$declaration',
                '$today',
                'Pending Verification'
            )";
    
            if (mysqli_query($db, $query)) {
                echo json_encode(['success' => true, 'message' => 'Vendor registered successfully.']);
            } else {
                throw new Exception("Database error: " . mysqli_error($db));
            }
    
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    //-------------------------------------------------------------------------------//
    
    function btn_UpdateProfileConsultant()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        
        // Sanitize and retrieve POST data
        $full_name                  = mysqli_real_escape_string($db, $_POST['full_name']);
        $id                         = mysqli_real_escape_string($db, $_POST['id']);
        $designation                = mysqli_real_escape_string($db, $_POST['designation']);
        $staff_id                   = mysqli_real_escape_string($db, $_POST['staff_id']); // assuming staff_id is unique and used for identification
        $ic                         = mysqli_real_escape_string($db, $_POST['ic']);
        $phone                      = mysqli_real_escape_string($db, $_POST['phone']);
        $email                      = mysqli_real_escape_string($db, $_POST['email']);
        $email_2                    = mysqli_real_escape_string($db, $_POST['email_2']);
        $uitm_state_permanent       = mysqli_real_escape_string($db, $_POST['uitm_state_permanent']);
        $ptj                        = mysqli_real_escape_string($db, $_POST['ptj']);
        $campus_address_permanent   = mysqli_real_escape_string($db, $_POST['campus_address_permanent']);
        $uitm_state_current         = mysqli_real_escape_string($db, $_POST['uitm_state_current']);
        $campus_address_current     = mysqli_real_escape_string($db, $_POST['campus_address_current']);
        $gender                     = mysqli_real_escape_string($db, $_POST['gender']);
        $citizenship                = mysqli_real_escape_string($db, $_POST['citizenship']);
        $employment_position        = mysqli_real_escape_string($db, $_POST['employment_position']);
        $expertise                  = mysqli_real_escape_string($db, $_POST['expertise']);
        $bank_name                  = mysqli_real_escape_string($db, $_POST['bank_name']);
        $no_account                 = mysqli_real_escape_string($db, $_POST['no_account']);
    
        // Construct SQL query for updating
        $query = "UPDATE `uitm_staff` SET 
                    `full_name` = '$full_name', 
                    `designation` = '$designation',
                    `ic` = '$ic', 
                    `phone` = '$phone', 
                    `email` = '$email', 
                    `email_2` = '$email_2',
                    `uitm_state_permanent` = '$uitm_state_permanent',
                    `ptj` = '$ptj',
                    `campus_address_permanent` = '$campus_address_permanent',
                    `uitm_state_current` = '$uitm_state_current',
                    `campus_address_current` = '$campus_address_current',
                    `gender` = '$gender', 
                    `citizenship` = '$citizenship', 
                    `employment_position` = '$employment_position',
                    `expertise` = '$expertise',
                    `bank_name` = '$bank_name',
                    `no_account` = '$no_account'
                  WHERE `id` = '$id'";
    
        // Execute query
        if (mysqli_query($db, $query)) {
            header("Location: profile.php?update=success");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_UpdateProfileResearch()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        
        // Sanitize and retrieve POST data
        $full_name              = mysqli_real_escape_string($db, $_POST['full_name']);
        $id                     = mysqli_real_escape_string($db, $_POST['id']);
        $designation            = mysqli_real_escape_string($db, $_POST['designation']);
        $ic                     = mysqli_real_escape_string($db, $_POST['ic']);
        $phone                  = mysqli_real_escape_string($db, $_POST['phone']);
        $email                  = mysqli_real_escape_string($db, $_POST['email']);
        $email_2                = mysqli_real_escape_string($db, $_POST['email_2']);
        $ptj_address            = mysqli_real_escape_string($db, $_POST['ptj_address']);
        $gender                 = mysqli_real_escape_string($db, $_POST['gender']);
        $citizenship            = mysqli_real_escape_string($db, $_POST['citizenship']);
        $marital_status         = mysqli_real_escape_string($db, $_POST['marital_status']);
        $epf_no                 = mysqli_real_escape_string($db, $_POST['epf_no']);
        $socso_no               = mysqli_real_escape_string($db, $_POST['socso_no']);
        $income_tax_no          = mysqli_real_escape_string($db, $_POST['income_tax_no']);
        $employment_position    = mysqli_real_escape_string($db, $_POST['employment_position']);
        $expertise              = mysqli_real_escape_string($db, $_POST['expertise']);
        $bank_name              = mysqli_real_escape_string($db, $_POST['bank_name']);
        $no_account             = mysqli_real_escape_string($db, $_POST['no_account']);
        
        // Retrieve existing file paths
        $existing_files_query = "SELECT bank_statement_file, copy_ic_file, copy_certificate_file
                                 FROM research_assistant WHERE id = '$id'";
        $existing_files_result = mysqli_query($db, $existing_files_query);
        $existing_files = mysqli_fetch_assoc($existing_files_result);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'bank_statement_file' => '../registration-documents/bank-statement/',
            'copy_ic_file' => '../registration-documents/ic-folder/',
            'copy_certificate_file' => '../registration-documents/certificate_folder/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                // Delete existing file if it exists
                if (!empty($existing_files[$file_key]) && file_exists($upload_path . $existing_files[$file_key])) {
                    unlink($upload_path . $existing_files[$file_key]); // Delete the old file
                }
    
                // Rename and upload the new file
                $new_file_name = $ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            } else {
                // Use existing file path if no new file is uploaded
                $uploaded_files[$file_key] = $existing_files[$file_key] ?? null;
            }
        }

        // Construct SQL query for updating
        $query = "UPDATE `research_assistant` SET 
                    `full_name` = '$full_name', 
                    `designation` = '$designation',
                    `ic` = '$ic', 
                    `phone` = '$phone', 
                    `email` = '$email', 
                    `email_2` = '$email_2', 
                    `ptj_address` = '$ptj_address', 
                    `gender` = '$gender', 
                    `citizenship` = '$citizenship', 
                    `marital_status` = '$marital_status', 
                    `epf_no` = '$epf_no', 
                    `socso_no` = '$socso_no', 
                    `income_tax_no` = '$income_tax_no', 
                    `employment_position` = '$employment_position',
                    `expertise` = '$expertise',
                    `bank_name` = '$bank_name',
                    `no_account` = '$no_account',
                    `bank_statement_file` = '".($uploaded_files['bank_statement_file'])."',
                    `copy_ic_file` = '".($uploaded_files['copy_ic_file'])."',
                    `copy_certificate_file` = '".($uploaded_files['copy_certificate_file'])."'
                  WHERE `id` = '$id'";
    
        // Execute query
        if (mysqli_query($db, $query)) {
            header("Location: profile.php?update=success");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    //-------------------------------------------------------------------------------//

    function btn_SaveConsultancyProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Alternative project_no generation using leader IC and sequence
        $prefix = "TEMP";
        $date_code = date("Ym"); // Format: YYYYMM for project_no
        $initial_sequence = 1;
        $project_no = $prefix . $date_code . str_pad($initial_sequence, 4, '0', STR_PAD_LEFT);
    
        // Check the last inserted project_no for the current prefix and date_code to increment the sequence
        $check_query = "SELECT project_no FROM project WHERE project_no LIKE '$prefix$date_code%' ORDER BY project_no DESC LIMIT 1";
        $result = mysqli_query($db, $check_query);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $last_project_no = mysqli_fetch_assoc($result)['project_no'];
            $last_sequence = (int)substr($last_project_no, -4); // Get last 4 digits
            $new_sequence = $last_sequence + 1;
            $project_no = $prefix . $date_code . str_pad($new_sequence, 4, '0', STR_PAD_LEFT);
        }
    
        // Sanitize and retrieve POST data
        $leader_id                   = mysqli_real_escape_string($db, $_POST['leader_id']);
        $leader_ic                   = mysqli_real_escape_string($db, $_POST['leader_ic']);
        $project_leader              = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_title               = mysqli_real_escape_string($db, $_POST['project_title']);
        $project_type                = mysqli_real_escape_string($db, $_POST['project_type']);
        $project_start               = mysqli_real_escape_string($db, $_POST['project_start']);
        $project_end                 = mysqli_real_escape_string($db, $_POST['project_end']);
        $registered_project_value    = mysqli_real_escape_string($db, $_POST['registered_project_value']);
        $adjusted_project_value      = mysqli_real_escape_string($db, $_POST['adjusted_project_value']);
        $quotation_ref_no            = mysqli_real_escape_string($db, $_POST['quotation_ref_no']);
        $client_company_name         = mysqli_real_escape_string($db, $_POST['client_company_name']);
        $client_address              = mysqli_real_escape_string($db, $_POST['client_address']);
        $client_contact              = mysqli_real_escape_string($db, $_POST['client_contact']);
        $client_business_type        = mysqli_real_escape_string($db, $_POST['client_business_type']);
        $client_pic                  = mysqli_real_escape_string($db, $_POST['client_pic']);
        $client_pic_email            = mysqli_real_escape_string($db, $_POST['client_pic_email']);
        $client_pic_contact          = mysqli_real_escape_string($db, $_POST['client_pic_contact']);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'appointment_letter' => '../consultant/project-documents/consultancy-project/appointment-letter/',
            'approval_external_work' => '../consultant/project-documents/consultancy-project/approval-external-work-letter/',
            'quotation_doc' => '../consultant/project-documents/consultancy-project/quotation/',
            'agreement_doc' => '../consultant/project-documents/consultancy-project/agreement-MoA/',
            'project_proposal' => '../consultant/project-documents/consultancy-project/project-proposal/',
            'other_doc_1' => '../consultant/project-documents/consultancy-project/other-docs/',
            'other_doc_2' => '../consultant/project-documents/consultancy-project/other-docs/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                // Rename file based on project_no
                $new_file_name = $project_title . "_" . $leader_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            }
        }
    
        // Construct SQL query for project table
        $query = "INSERT INTO `project` (
                    `leader_id`,
                    `project_leader`,
                    `leader_ic`,
                    `project_source`,
                    `project_no`,
                    `project_title`, 
                    `project_type`,
                    `project_start`,
                    `project_end`, 
                    `registered_project_value`, 
                    `adjusted_project_value`, 
                    `quotation_ref_no`, 
                    `appointment_letter`, 
                    `approval_external_work`, 
                    `quotation_doc`, 
                    `agreement_doc`, 
                    `project_proposal`,
                    `other_doc_1`,
                    `other_doc_2`,
                    `client_company_name`, 
                    `client_address`, 
                    `client_contact`, 
                    `client_business_type`, 
                    `client_pic`, 
                    `client_pic_email`, 
                    `client_pic_contact`, 
                    `date_create`,
                    `project_status`
                ) VALUES (
                    '$leader_id',
                    '$project_leader',
                    '$leader_ic',
                    'Consultancy',
                    '$project_no',
                    '$project_title', 
                    '$project_type',
                    '$project_start',
                    '$project_end', 
                    '$registered_project_value', 
                    '$adjusted_project_value', 
                    '$quotation_ref_no', 
                    '".($uploaded_files['appointment_letter'] ?? null)."', 
                    '".($uploaded_files['approval_external_work'] ?? null)."', 
                    '".($uploaded_files['quotation_doc'] ?? null)."', 
                    '".($uploaded_files['agreement_doc'] ?? null)."', 
                    '".($uploaded_files['project_proposal'] ?? null)."',
                    '".($uploaded_files['other_doc_1'] ?? null)."',
                    '".($uploaded_files['other_doc_2'] ?? null)."',
                    '$client_company_name', 
                    '$client_address', 
                    '$client_contact', 
                    '$client_business_type', 
                    '$client_pic', 
                    '$client_pic_email', 
                    '$client_pic_contact', 
                    '$today',
                    'Draft'
                )";
    
        // Execute query and check for success
        if (mysqli_query($db, $query)) {
            $project_id = mysqli_insert_id($db); // Get the last inserted ID for project_id
            $project_timeline = $_POST['project_timeline']; // Array containing all member data
        
            if (!empty($project_timeline) && is_array($project_timeline)) {
                foreach ($project_timeline as $timeline) {
                    // Validate required fields
                    if (empty($timeline['title']) || empty($timeline['description']) || empty($timeline['value']) || empty($timeline['date_start']) || empty($timeline['date_end'])) {
                        error_log("Incomplete project timeline data: " . json_encode($timeline));
                        continue;
                    }
                    // Sanitize additional fields
                    $title = mysqli_real_escape_string($db, $timeline['title']);
                    $description = mysqli_real_escape_string($db, $timeline['description']);
                    $value = mysqli_real_escape_string($db, $timeline['value']);
                    $date_start = mysqli_real_escape_string($db, $timeline['date_start']);
                    $date_end = mysqli_real_escape_string($db, $timeline['date_end']);
            
                    // Insert into database
                    $query = "INSERT INTO project_timeline (
                                project_id, title, description, value, date_start, date_end
                              ) VALUES (
                                '$project_id', '$title', '$description', '$value', '$date_start', '$date_end'
                              )";
            
                    if (!mysqli_query($db, $query)) {
                        error_log("Error adding project timeline: " . mysqli_error($db));
                    } else {
                        error_log("Project timeline added successfully");
                    }
                }
            } else {
                error_log("No project_timeline were selected or project_timeline array is empty.");
            }
            
            // Insert into project_tracker table
            $remark = "Project has been created ($leader_ic)";
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
            mysqli_query($db, $tracker_query); // Insert into tracker table
            
            // *******************************
            // Insert project members into project_members_consultant table
            // *******************************
            if(isset($_POST['project_members']) && is_array($_POST['project_members'])) {
                $members = $_POST['project_members'];
                foreach ($members as $member_id) {
                    // Sanitize each member id
                    $member_id = mysqli_real_escape_string($db, $member_id);
                    // Insert record into project_members_consultant table
                    $member_query = "INSERT INTO `project_members_consultant` (
                                        `project_id`, 
                                        `project_no`, 
                                        `project_leader`, 
                                        `member_id`
                                     ) VALUES (
                                        '$project_id', 
                                        '$project_no', 
                                        '$project_leader', 
                                        '$member_id'
                                     )";
                    mysqli_query($db, $member_query);
                }
            }
            // *******************************
            
            header("Location: create-consultancy.php?update=save-success");
            exit();
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_UpdateConsultancyProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id                          = mysqli_real_escape_string($db, $_POST['id']);
        $leader_id                   = mysqli_real_escape_string($db, $_POST['leader_id']);
        $project_leader              = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_no                  = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_title               = mysqli_real_escape_string($db, $_POST['project_title']);
        $project_type                = mysqli_real_escape_string($db, $_POST['project_type']);
        $project_start               = mysqli_real_escape_string($db, $_POST['project_start']);
        $project_end                 = mysqli_real_escape_string($db, $_POST['project_end']);
        $registered_project_value    = mysqli_real_escape_string($db, $_POST['registered_project_value']);
        $adjusted_project_value      = mysqli_real_escape_string($db, $_POST['adjusted_project_value']);
        $quotation_ref_no            = mysqli_real_escape_string($db, $_POST['quotation_ref_no']);
        $client_company_name         = mysqli_real_escape_string($db, $_POST['client_company_name']);
        $client_address              = mysqli_real_escape_string($db, $_POST['client_address']);
        $client_contact              = mysqli_real_escape_string($db, $_POST['client_contact']);
        $client_business_type        = mysqli_real_escape_string($db, $_POST['client_business_type']);
        $client_pic                  = mysqli_real_escape_string($db, $_POST['client_pic']);
        $client_pic_email            = mysqli_real_escape_string($db, $_POST['client_pic_email']);
        $client_pic_contact          = mysqli_real_escape_string($db, $_POST['client_pic_contact']);
    
        // Retrieve existing file paths
        $existing_files_query = "SELECT appointment_letter, approval_external_work, quotation_doc, 
                                        agreement_doc, project_proposal, other_doc_1, other_doc_2 
                                 FROM project WHERE id = '$id'";
        $existing_files_result = mysqli_query($db, $existing_files_query);
        $existing_files = mysqli_fetch_assoc($existing_files_result);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'appointment_letter' => '../consultant/project-documents/consultancy-project/appointment-letter/',
            'approval_external_work' => '../consultant/project-documents/consultancy-project/approval-external-work-letter/',
            'quotation_doc' => '../consultant/project-documents/consultancy-project/quotation/',
            'agreement_doc' => '../consultant/project-documents/consultancy-project/agreement-MoA/',
            'project_proposal' => '../consultant/project-documents/consultancy-project/project-proposal/',
            'other_doc_1' => '../consultant/project-documents/consultancy-project/other-docs/',
            'other_doc_2' => '../consultant/project-documents/consultancy-project/other-docs/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                if (!empty($existing_files[$file_key]) && file_exists($upload_path . $existing_files[$file_key])) {
                    unlink($upload_path . $existing_files[$file_key]);
                }
    
                $safe_project_title = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project_title);
                $new_file_name = $safe_project_title . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
    
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            } else {
                $uploaded_files[$file_key] = $existing_files[$file_key] ?? null;
            }
        }
    
        // Start transaction
        mysqli_begin_transaction($db);
    
        try {
            // 1. Update main project
            $query = "UPDATE `project` SET
                        `leader_id` = '$leader_id',
                        `project_leader` = '$project_leader',
                        `project_title` = '$project_title', 
                        `project_type` = '$project_type',
                        `project_start` = '$project_start',
                        `project_end` = '$project_end', 
                        `registered_project_value` = '$registered_project_value', 
                        `adjusted_project_value` = '$adjusted_project_value', 
                        `quotation_ref_no` = '$quotation_ref_no', 
                        `appointment_letter` = '".($uploaded_files['appointment_letter'])."', 
                        `approval_external_work` = '".($uploaded_files['approval_external_work'])."', 
                        `quotation_doc` = '".($uploaded_files['quotation_doc'])."', 
                        `agreement_doc` = '".($uploaded_files['agreement_doc'])."', 
                        `project_proposal` = '".($uploaded_files['project_proposal'])."',
                        `other_doc_1` = '".($uploaded_files['other_doc_1'])."',
                        `other_doc_2` = '".($uploaded_files['other_doc_2'])."',
                        `client_company_name` = '$client_company_name', 
                        `client_address` = '$client_address', 
                        `client_contact` = '$client_contact', 
                        `client_business_type` = '$client_business_type', 
                        `client_pic` = '$client_pic', 
                        `client_pic_email` = '$client_pic_email', 
                        `client_pic_contact` = '$client_pic_contact', 
                        `date_create` = '$today'
                      WHERE `id` = '$id'";
    
            if (!mysqli_query($db, $query)) {
                throw new Exception("Failed to update project: " . mysqli_error($db));
            }
    
            // 2. Reset and reinsert project members
            mysqli_query($db, "DELETE FROM project_members_consultant WHERE project_id = '$id'");
    
            if (isset($_POST['project_members']) && is_array($_POST['project_members'])) {
                $member_ids_seen = [];
    
                foreach ($_POST['project_members'] as $member_id) {
                    $member_id = mysqli_real_escape_string($db, trim($member_id));
    
                    if ($member_id === '') {
                        continue;
                    }
    
                    if (in_array($member_id, $member_ids_seen)) {
                        continue;
                    }
    
                    $member_ids_seen[] = $member_id;
    
                    $member_query = "INSERT INTO project_members_consultant (project_id, member_id, project_no)
                                     VALUES ('$id', '$member_id', '$project_no')";
    
                    if (!mysqli_query($db, $member_query)) {
                        throw new Exception("Failed to insert project member: " . mysqli_error($db));
                    }
                }
            }
    
            // 3. Reset and reinsert project timeline
            mysqli_query($db, "DELETE FROM project_timeline WHERE project_id = '$id'");
    
            if (isset($_POST['project_timeline']) && is_array($_POST['project_timeline'])) {
                foreach ($_POST['project_timeline'] as $timeline) {
                    if (
                        empty($timeline['title']) ||
                        empty($timeline['description']) ||
                        $timeline['value'] === '' ||
                        empty($timeline['date_start']) ||
                        empty($timeline['date_end'])
                    ) {
                        continue;
                    }
    
                    $title = mysqli_real_escape_string($db, trim($timeline['title']));
                    $description = mysqli_real_escape_string($db, trim($timeline['description']));
                    $value = mysqli_real_escape_string($db, trim($timeline['value']));
                    $date_start = mysqli_real_escape_string($db, trim($timeline['date_start']));
                    $date_end = mysqli_real_escape_string($db, trim($timeline['date_end']));
    
                    $timeline_query = "INSERT INTO project_timeline (
                                            project_id, title, description, value, date_start, date_end
                                       ) VALUES (
                                            '$id', '$title', '$description', '$value', '$date_start', '$date_end'
                                       )";
    
                    if (!mysqli_query($db, $timeline_query)) {
                        throw new Exception("Failed to insert project timeline: " . mysqli_error($db));
                    }
                }
            }
    
            mysqli_commit($db);
            header("Location: consultancy-project-edit.php?update=save-success&id=$id");
            exit();
    
        } catch (Exception $e) {
            mysqli_rollback($db);
            echo "Error: " . $e->getMessage();
        }
    }
    
    function btn_SubmitConsultancyProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id                          = mysqli_real_escape_string($db, $_POST['id']);
        $leader_id                   = mysqli_real_escape_string($db, $_POST['leader_id']);
        $leader_ic                   = mysqli_real_escape_string($db, $_POST['leader_ic']);
        $project_leader              = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_no                  = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_title               = mysqli_real_escape_string($db, $_POST['project_title']);
        $project_type                = mysqli_real_escape_string($db, $_POST['project_type']);
        $project_start               = mysqli_real_escape_string($db, $_POST['project_start']);
        $project_end                 = mysqli_real_escape_string($db, $_POST['project_end']);
        $registered_project_value    = mysqli_real_escape_string($db, $_POST['registered_project_value']);
        $adjusted_project_value      = mysqli_real_escape_string($db, $_POST['adjusted_project_value']);
        $quotation_ref_no            = mysqli_real_escape_string($db, $_POST['quotation_ref_no']);
        $client_company_name         = mysqli_real_escape_string($db, $_POST['client_company_name']);
        $client_address              = mysqli_real_escape_string($db, $_POST['client_address']);
        $client_contact              = mysqli_real_escape_string($db, $_POST['client_contact']);
        $client_business_type        = mysqli_real_escape_string($db, $_POST['client_business_type']);
        $client_pic                  = mysqli_real_escape_string($db, $_POST['client_pic']);
        $client_pic_email            = mysqli_real_escape_string($db, $_POST['client_pic_email']);
        $client_pic_contact          = mysqli_real_escape_string($db, $_POST['client_pic_contact']);
    
        // Retrieve existing file paths
        $existing_files_query = "SELECT appointment_letter, approval_external_work, quotation_doc, 
                                        agreement_doc, project_proposal, other_doc_1, other_doc_2 
                                 FROM project WHERE id = '$id'";
        $existing_files_result = mysqli_query($db, $existing_files_query);
        $existing_files = mysqli_fetch_assoc($existing_files_result);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'appointment_letter' => '../consultant/project-documents/consultancy-project/appointment-letter/',
            'approval_external_work' => '../consultant/project-documents/consultancy-project/approval-external-work-letter/',
            'quotation_doc' => '../consultant/project-documents/consultancy-project/quotation/',
            'agreement_doc' => '../consultant/project-documents/consultancy-project/agreement-MoA/',
            'project_proposal' => '../consultant/project-documents/consultancy-project/project-proposal/',
            'other_doc_1' => '../consultant/project-documents/consultancy-project/other-docs/',
            'other_doc_2' => '../consultant/project-documents/consultancy-project/other-docs/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                // Delete existing file if it exists
                if (!empty($existing_files[$file_key]) && file_exists($upload_path . $existing_files[$file_key])) {
                    unlink($upload_path . $existing_files[$file_key]); // Delete the old file
                }
    
                // Rename and upload the new file
                $new_file_name =  $project_title . "_" . $leader_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            } else {
                // Use existing file path if no new file is uploaded
                $uploaded_files[$file_key] = $existing_files[$file_key] ?? null;
            }
        }
        
        $project_timeline = $_POST['project_timeline']; // Array containing all member data
        
        if (!empty($project_timeline) && is_array($project_timeline)) {
            foreach ($project_timeline as $timeline) {
                // Validate required fields
                if (empty($timeline['title']) || empty($timeline['description']) || empty($timeline['value']) || empty($timeline['date_start']) || empty($timeline['date_end'])) {
                    error_log("Incomplete project timeline data: " . json_encode($timeline));
                    continue;
                }
                // Sanitize additional fields
                $title = mysqli_real_escape_string($db, $timeline['title']);
                $description = mysqli_real_escape_string($db, $timeline['description']);
                $value = mysqli_real_escape_string($db, $timeline['value']);
                $date_start = mysqli_real_escape_string($db, $timeline['date_start']);
        
                // Insert into database
                $query = "INSERT INTO project_timeline (
                            project_id, title, description, value, date_start
                          ) VALUES (
                            '$id', '$title', '$description', '$value', '$date_start'
                          )";
        
                if (!mysqli_query($db, $query)) {
                    error_log("Error adding project timeline: " . mysqli_error($db));
                } else {
                    error_log("Project timeline added successfully");
                }
            }
        } else {
            error_log("No project_timeline were selected or project_timeline array is empty.");
            echo "No project_timeline were selected.";
        }
    
        // Construct SQL query for consultancy_project table
        $query = "UPDATE `project` SET
                    `leader_id` = '$leader_id',
                    `project_leader` = '$project_leader',
                    `project_title` = '$project_title', 
                    `project_type` = '$project_type',
                    `project_start` = '$project_start',
                    `project_end` = '$project_end', 
                    `registered_project_value` = '$registered_project_value', 
                    `adjusted_project_value` = '$adjusted_project_value', 
                    `quotation_ref_no` = '$quotation_ref_no', 
                    `appointment_letter` = '".($uploaded_files['appointment_letter'])."', 
                    `approval_external_work` = '".($uploaded_files['approval_external_work'])."', 
                    `quotation_doc` = '".($uploaded_files['quotation_doc'])."', 
                    `agreement_doc` = '".($uploaded_files['agreement_doc'])."', 
                    `project_proposal` = '".($uploaded_files['project_proposal'])."',
                    `other_doc_1` = '".($uploaded_files['other_doc_1'])."',
                    `other_doc_2` = '".($uploaded_files['other_doc_2'])."',
                    `client_company_name` = '$client_company_name', 
                    `client_address` = '$client_address', 
                    `client_contact` = '$client_contact', 
                    `client_business_type` = '$client_business_type', 
                    `client_pic` = '$client_pic', 
                    `client_pic_email` = '$client_pic_email', 
                    `client_pic_contact` = '$client_pic_contact', 
                    `date_create` = '$today',
                    `project_status` = 'Pending Verification',
                    `return_remark` = ''
                WHERE `id` = '$id'";
                
        // Execute query and check for success
        if (mysqli_query($db, $query)) {
            $project_id = mysqli_insert_id($db); // Get the last inserted ID for project_id
            
            // Insert into consultancy_project_tracker
            $remark = "Project has been submit for verification and approval. ($leader_ic)";
            $tracker_query = "INSERT INTO `project_tracker` (
                                `project_id`, 
                                `project_no`, 
                                `remark`, 
                                `date`
                              ) VALUES (
                                '$id', 
                                '$project_no', 
                                '$remark', 
                                '$today'
                              )";
    
            mysqli_query($db, $tracker_query); // Insert into tracker table
            header("Location: create-consultancy.php?update=submit-success");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_AddMembersConsultancyProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $leader_id = mysqli_real_escape_string($db, $_POST['leader_id']);
        $project_leader = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_title = mysqli_real_escape_string($db, $_POST['project_title']);
        $members = $_POST['members']; // Array containing all member data
    
        if (!empty($members) && is_array($members)) {
            foreach ($members as $member) {
                // Validate required fields
                if (empty($member['name_ic_id']) || empty($member['duration']) || empty($member['start_date']) || empty($member['payment_type']) || empty($member['budget'])) {
                    error_log("Incomplete member data: " . json_encode($member));
                    continue;
                }
        
                // Split and sanitize the id, name, and IC
                list($member_id, $member_name, $member_ic) = explode('|', $member['name_ic_id']);
                $member_id = mysqli_real_escape_string($db, $member_id);
                $member_name = mysqli_real_escape_string($db, $member_name);
                $member_ic = mysqli_real_escape_string($db, $member_ic);
        
                // Sanitize additional fields
                $duration = mysqli_real_escape_string($db, $member['duration']);
                $start_date = mysqli_real_escape_string($db, $member['start_date']);
                $payment_type = mysqli_real_escape_string($db, $member['payment_type']);
                $budget = mysqli_real_escape_string($db, $member['budget']);
        
                // Insert into database
                $query = "INSERT INTO project_members (
                            project_id, leader_id, project_leader, project_no, project_source, project_title, 
                            member_id, member_name, member_ic, duration, start_date, payment_type, budget, date_added, status
                          ) VALUES (
                            '$id', '$leader_id', '$project_leader', '$project_no', 'Consultancy', '$project_title', 
                            '$member_id', '$member_name', '$member_ic', '$duration', '$start_date', '$payment_type', '$budget', '$today', 'Pending Verification'
                          )";
        
                if (!mysqli_query($db, $query)) {
                    error_log("Error adding member: " . mysqli_error($db));
                } else {
                    error_log("Member added successfully: $member_name ($member_ic)");
                }
            }
        
            // Redirect after successful insertion
            header("Location: consultancy-project-add-members.php?update=save-success&id=$id");
        } else {
            error_log("No members were selected or members array is empty.");
            echo "No members were selected.";
        }
    }
    
    function btn_AddProjectMembers()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $members = $_POST['project_members'];
    
        // Dapatkan maklumat project berdasarkan ID
        $project_query = "SELECT project_no, project_leader FROM project WHERE id = '$id'";
        $project_result = mysqli_query($db, $project_query);
    
        if (!$project_result || mysqli_num_rows($project_result) == 0) {
            error_log("Project not found or error in query: " . mysqli_error($db));
            echo "Project not found.";
            return;
        }
    
        $project = mysqli_fetch_assoc($project_result);
        $project_no = mysqli_real_escape_string($db, $project['project_no']);
        $project_leader = mysqli_real_escape_string($db, $project['project_leader']);
    
        if (!empty($members) && is_array($members)) {
            foreach ($members as $member_id) {
                $member_id = mysqli_real_escape_string($db, $member_id);
                // Insert into database
                $member_query = "INSERT INTO `project_members_consultant` (
                                    `project_id`, 
                                    `project_no`, 
                                    `project_leader`, 
                                    `member_id`
                                 ) VALUES (
                                    '$id', 
                                    '$project_no', 
                                    '$project_leader', 
                                    '$member_id'
                                 )";
                if (!mysqli_query($db, $member_query)) {
                    error_log("Error adding project members: " . mysqli_error($db));
                } else {
                    error_log("Project member added successfully");
                }
            }
    
            // Redirect after successful insertion
            header("Location: add-project-members.php?update=save-success&id=$id");
        } else {
            error_log("No project_members were selected or project_members array is empty.");
            echo "No project_members were selected.";
        }
    }
    
    function btn_AddProjectTimeline()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $project_timeline = $_POST['project_timeline']; // Array containing all member data
    
        if (!empty($project_timeline) && is_array($project_timeline)) {
            foreach ($project_timeline as $timeline) {
                // Validate required fields
                if (empty($timeline['title']) || empty($timeline['description']) || empty($timeline['value']) || empty($timeline['date_start']) || empty($timeline['date_end'])) {
                    error_log("Incomplete project timeline data: " . json_encode($timeline));
                    continue;
                }
                // Sanitize additional fields
                $title = mysqli_real_escape_string($db, $timeline['title']);
                $description = mysqli_real_escape_string($db, $timeline['description']);
                $value = mysqli_real_escape_string($db, $timeline['value']);
                $date_start = mysqli_real_escape_string($db, $timeline['date_start']);
                $date_end = mysqli_real_escape_string($db, $timeline['date_end']);
        
                // Insert into database
                $query = "INSERT INTO project_timeline (
                            project_id, title, description, value, date_start, date_end
                          ) VALUES (
                            '$id', '$title', '$description', '$value', '$date_start', '$date_end'
                          )";
        
                if (!mysqli_query($db, $query)) {
                    error_log("Error adding project timeline: " . mysqli_error($db));
                } else {
                    error_log("Project timeline added successfully");
                }
            }
        
            // Redirect after successful insertion
            header("Location: add-project-timeline.php?update=save-success&id=$id");
        } else {
            error_log("No project_timeline were selected or project_timeline array is empty.");
            echo "No project_timeline were selected.";
        }
    }
    
    //-------------------------------------------------------------------------------//
    
    function btn_SaveTrainingProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Alternative project_no generation using leader IC and sequence
        $prefix = "TEMP";
        $date_code = date("Ym"); // Format: YYYYMM for project_no
        $initial_sequence = 1;
        $project_no = $prefix . $date_code . str_pad($initial_sequence, 4, '0', STR_PAD_LEFT);
    
        // Check the last inserted project_no for the current prefix and date_code to increment the sequence
        $check_query = "SELECT project_no FROM project WHERE project_no LIKE '$prefix$date_code%' ORDER BY project_no DESC LIMIT 1";
        $result = mysqli_query($db, $check_query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $last_project_no = mysqli_fetch_assoc($result)['project_no'];
            $last_sequence = (int)substr($last_project_no, -4); // Get last 4 digits
            $new_sequence = $last_sequence + 1;
            $project_no = $prefix . $date_code . str_pad($new_sequence, 4, '0', STR_PAD_LEFT);
        }
    
        // Sanitize and retrieve POST data
        $leader_id                   = mysqli_real_escape_string($db, $_POST['leader_id']);
        $leader_ic                   = mysqli_real_escape_string($db, $_POST['leader_ic']);
        $project_leader              = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_title               = mysqli_real_escape_string($db, $_POST['project_title']);
        $project_type                = mysqli_real_escape_string($db, $_POST['project_type']);
        $project_start               = mysqli_real_escape_string($db, $_POST['project_start']);
        $project_end                 = mysqli_real_escape_string($db, $_POST['project_end']);
        $registered_project_value    = mysqli_real_escape_string($db, $_POST['registered_project_value']);
        $adjusted_project_value      = mysqli_real_escape_string($db, $_POST['adjusted_project_value']);
        $client_company_name         = mysqli_real_escape_string($db, $_POST['client_company_name']);
        $client_address              = mysqli_real_escape_string($db, $_POST['client_address']);
        $client_contact              = mysqli_real_escape_string($db, $_POST['client_contact']);
        $client_business_type        = mysqli_real_escape_string($db, $_POST['client_business_type']);
        $client_pic                  = mysqli_real_escape_string($db, $_POST['client_pic']);
        $client_pic_email            = mysqli_real_escape_string($db, $_POST['client_pic_email']);
        $client_pic_contact          = mysqli_real_escape_string($db, $_POST['client_pic_contact']);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'appointment_letter' => '../consultant/project-documents/training-project/appointment-letter/',
            'approval_external_work' => '../consultant/project-documents/training-project/approval-external-work-letter/',
            'project_proposal' => '../consultant/project-documents/training-project/project-proposal/',
            'other_doc_1' => '../consultant/project-documents/training-project/other-docs/',
            'other_doc_2' => '../consultant/project-documents/training-project/other-docs/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                // Rename file based on project_no
                $new_file_name =  $project_title . "_" . $leader_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            }
        }
    
        // Construct SQL query for consultancy_project table
        $query = "INSERT INTO `project` (
                    `leader_id`,
                    `project_leader`,
                    `leader_ic`,
                    `project_source`,
                    `project_no`,
                    `project_title`, 
                    `project_type`,
                    `project_start`,
                    `project_end`, 
                    `registered_project_value`, 
                    `adjusted_project_value`,
                    `appointment_letter`, 
                    `approval_external_work`,
                    `project_proposal`,
                    `other_doc_1`,
                    `other_doc_2`,
                    `client_company_name`, 
                    `client_address`, 
                    `client_contact`, 
                    `client_business_type`, 
                    `client_pic`, 
                    `client_pic_email`, 
                    `client_pic_contact`, 
                    `date_create`,
                    `project_status`
                ) VALUES (
                    '$leader_id',
                    '$project_leader',
                    '$leader_ic',
                    'Training',
                    '$project_no',
                    '$project_title', 
                    '$project_type',
                    '$project_start',
                    '$project_end', 
                    '$registered_project_value', 
                    '$adjusted_project_value',
                    '".($uploaded_files['appointment_letter'] ?? null)."', 
                    '".($uploaded_files['approval_external_work'] ?? null)."',
                    '".($uploaded_files['project_proposal'] ?? null)."',
                    '".($uploaded_files['other_doc_1'] ?? null)."',
                    '".($uploaded_files['other_doc_2'] ?? null)."',
                    '$client_company_name', 
                    '$client_address', 
                    '$client_contact', 
                    '$client_business_type', 
                    '$client_pic', 
                    '$client_pic_email', 
                    '$client_pic_contact', 
                    '$today',
                    'Draft'
                )";
    
        // Execute query and check for success
        if (mysqli_query($db, $query)) {
            $project_id = mysqli_insert_id($db); // Get the last inserted ID for project_id
            
            // Insert into consultancy_project_tracker
            $remark = "Project has been created ($leader_ic)";
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
    
            mysqli_query($db, $tracker_query); // Insert into tracker table
            header("Location: create-training.php?update=save-success");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_UpdateTrainingProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id                          = mysqli_real_escape_string($db, $_POST['id']);
        $leader_id                   = mysqli_real_escape_string($db, $_POST['leader_id']);
        $leader_ic                   = mysqli_real_escape_string($db, $_POST['leader_ic']);
        $project_leader              = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_no                  = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_title               = mysqli_real_escape_string($db, $_POST['project_title']);
        $project_type                = mysqli_real_escape_string($db, $_POST['project_type']);
        $project_start               = mysqli_real_escape_string($db, $_POST['project_start']);
        $project_end                 = mysqli_real_escape_string($db, $_POST['project_end']);
        $registered_project_value    = mysqli_real_escape_string($db, $_POST['registered_project_value']);
        $adjusted_project_value      = mysqli_real_escape_string($db, $_POST['adjusted_project_value']);
        $client_company_name         = mysqli_real_escape_string($db, $_POST['client_company_name']);
        $client_address              = mysqli_real_escape_string($db, $_POST['client_address']);
        $client_contact              = mysqli_real_escape_string($db, $_POST['client_contact']);
        $client_business_type        = mysqli_real_escape_string($db, $_POST['client_business_type']);
        $client_pic                  = mysqli_real_escape_string($db, $_POST['client_pic']);
        $client_pic_email            = mysqli_real_escape_string($db, $_POST['client_pic_email']);
        $client_pic_contact          = mysqli_real_escape_string($db, $_POST['client_pic_contact']);
    
        // Retrieve existing file paths
        $existing_files_query = "SELECT appointment_letter, approval_external_work, project_proposal, other_doc_1, other_doc_2 
                                 FROM project WHERE id = '$id'";
        $existing_files_result = mysqli_query($db, $existing_files_query);
        $existing_files = mysqli_fetch_assoc($existing_files_result);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'appointment_letter' => '../consultant/project-documents/training-project/appointment-letter/',
            'approval_external_work' => '../consultant/project-documents/training-project/approval-external-work-letter/',
            'project_proposal' => '../consultant/project-documents/training-project/project-proposal/',
            'other_doc_1' => '../consultant/project-documents/training-project/other-docs/',
            'other_doc_2' => '../consultant/project-documents/training-project/other-docs/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                // Delete existing file if it exists
                if (!empty($existing_files[$file_key]) && file_exists($upload_path . $existing_files[$file_key])) {
                    unlink($upload_path . $existing_files[$file_key]); // Delete the old file
                }
    
                // Rename and upload the new file
                $new_file_name =  $project_title . "_" . $leader_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            } else {
                // Use existing file path if no new file is uploaded
                $uploaded_files[$file_key] = $existing_files[$file_key] ?? null;
            }
        }
    
        // Construct SQL query for consultancy_project table
        $query = "UPDATE `project` SET
                    `leader_id` = '$leader_id',
                    `project_leader` = '$project_leader',
                    `project_title` = '$project_title', 
                    `project_type` = '$project_type',
                    `project_start` = '$project_start',
                    `project_end` = '$project_end', 
                    `registered_project_value` = '$registered_project_value', 
                    `adjusted_project_value` = '$adjusted_project_value',
                    `appointment_letter` = '".($uploaded_files['appointment_letter'])."', 
                    `approval_external_work` = '".($uploaded_files['approval_external_work'])."',
                    `project_proposal` = '".($uploaded_files['project_proposal'])."',
                    `other_doc_1` = '".($uploaded_files['other_doc_1'])."',
                    `other_doc_2` = '".($uploaded_files['other_doc_2'])."',
                    `client_company_name` = '$client_company_name', 
                    `client_address` = '$client_address', 
                    `client_contact` = '$client_contact', 
                    `client_business_type` = '$client_business_type', 
                    `client_pic` = '$client_pic', 
                    `client_pic_email` = '$client_pic_email', 
                    `client_pic_contact` = '$client_pic_contact', 
                    `date_create` = '$today'
                WHERE `id` = '$id'";
    
        // Execute query and check for success
        if (mysqli_query($db, $query)) {
            header("Location: training-project-edit.php?update=save-success&id=$id");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_SubmitTrainingProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id                          = mysqli_real_escape_string($db, $_POST['id']);
        $leader_id                   = mysqli_real_escape_string($db, $_POST['leader_id']);
        $leader_ic                   = mysqli_real_escape_string($db, $_POST['leader_ic']);
        $project_leader              = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_no                  = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_title               = mysqli_real_escape_string($db, $_POST['project_title']);
        $project_type                = mysqli_real_escape_string($db, $_POST['project_type']);
        $project_start               = mysqli_real_escape_string($db, $_POST['project_start']);
        $project_end                 = mysqli_real_escape_string($db, $_POST['project_end']);
        $registered_project_value    = mysqli_real_escape_string($db, $_POST['registered_project_value']);
        $adjusted_project_value      = mysqli_real_escape_string($db, $_POST['adjusted_project_value']);
        $client_company_name         = mysqli_real_escape_string($db, $_POST['client_company_name']);
        $client_address              = mysqli_real_escape_string($db, $_POST['client_address']);
        $client_contact              = mysqli_real_escape_string($db, $_POST['client_contact']);
        $client_business_type        = mysqli_real_escape_string($db, $_POST['client_business_type']);
        $client_pic                  = mysqli_real_escape_string($db, $_POST['client_pic']);
        $client_pic_email            = mysqli_real_escape_string($db, $_POST['client_pic_email']);
        $client_pic_contact          = mysqli_real_escape_string($db, $_POST['client_pic_contact']);
    
        // Retrieve existing file paths
        $existing_files_query = "SELECT appointment_letter, approval_external_work, project_proposal, other_doc_1, other_doc_2 
                                 FROM project WHERE id = '$id'";
        $existing_files_result = mysqli_query($db, $existing_files_query);
        $existing_files = mysqli_fetch_assoc($existing_files_result);
    
        // File Uploads
        $upload_errors = [];
        $files_to_upload = [
            'appointment_letter' => '../consultant/project-documents/training-project/appointment-letter/',
            'approval_external_work' => '../consultant/project-documents/training-project/approval-external-work-letter/',
            'project_proposal' => '../consultant/project-documents/training-project/project-proposal/',
            'other_doc_1' => '../consultant/project-documents/training-project/other-docs/',
            'other_doc_2' => '../consultant/project-documents/training-project/other-docs/',
        ];
    
        $uploaded_files = [];
        foreach ($files_to_upload as $file_key => $upload_path) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $file_name = $_FILES[$file_key]['name'];
                $file_tmp = $_FILES[$file_key]['tmp_name'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'doc', 'docx'];
    
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_errors[] = "$file_name has an invalid file extension.";
                    continue;
                }
    
                // Delete existing file if it exists
                if (!empty($existing_files[$file_key]) && file_exists($upload_path . $existing_files[$file_key])) {
                    unlink($upload_path . $existing_files[$file_key]); // Delete the old file
                }
    
                // Rename and upload the new file
                $new_file_name =  $project_title . "_" . $leader_ic . "_" . str_replace('_file', '', $file_key) . "." . $file_extension;
                if (!move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                    $upload_errors[] = "Failed to upload $file_name.";
                } else {
                    $uploaded_files[$file_key] = $new_file_name;
                }
            } else {
                // Use existing file path if no new file is uploaded
                $uploaded_files[$file_key] = $existing_files[$file_key] ?? null;
            }
        }
    
        // Construct SQL query for consultancy_project table
        $query = "UPDATE `project` SET
                    `leader_id` = '$leader_id',
                    `project_leader` = '$project_leader',
                    `project_title` = '$project_title', 
                    `project_type` = '$project_type',
                    `project_start` = '$project_start',
                    `project_end` = '$project_end', 
                    `registered_project_value` = '$registered_project_value', 
                    `adjusted_project_value` = '$adjusted_project_value',
                    `appointment_letter` = '".($uploaded_files['appointment_letter'])."', 
                    `approval_external_work` = '".($uploaded_files['approval_external_work'])."',
                    `project_proposal` = '".($uploaded_files['project_proposal'])."',
                    `other_doc_1` = '".($uploaded_files['other_doc_1'])."',
                    `other_doc_2` = '".($uploaded_files['other_doc_2'])."',
                    `client_company_name` = '$client_company_name', 
                    `client_address` = '$client_address', 
                    `client_contact` = '$client_contact', 
                    `client_business_type` = '$client_business_type', 
                    `client_pic` = '$client_pic', 
                    `client_pic_email` = '$client_pic_email', 
                    `client_pic_contact` = '$client_pic_contact', 
                    `date_create` = '$today',
                    `project_status` = 'Pending Verification'
                WHERE `id` = '$id'";
                
        // Execute query and check for success
        if (mysqli_query($db, $query)) {
            $project_id = mysqli_insert_id($db); // Get the last inserted ID for project_id
            
            // Insert into consultancy_project_tracker
            $remark = "Project has been submit for verification and approval. ($leader_ic)";
            $tracker_query = "INSERT INTO `project_tracker` (
                                `project_id`, 
                                `project_no`, 
                                `remark`, 
                                `date`
                              ) VALUES (
                                '$id', 
                                '$project_no', 
                                '$remark', 
                                '$today'
                              )";
    
            mysqli_query($db, $tracker_query); // Insert into tracker table
            header("Location: create-training.php?update=submit-success");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_AddMembersTrainingProject()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
    
        // Sanitize and retrieve POST data
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $leader_id = mysqli_real_escape_string($db, $_POST['leader_id']);
        $project_leader = mysqli_real_escape_string($db, $_POST['project_leader']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_title = mysqli_real_escape_string($db, $_POST['project_title']);
        $members = $_POST['members']; // Array containing all member data
    
        if (!empty($members) && is_array($members)) {
            foreach ($members as $member) {
                // Validate required fields
                if (empty($member['name_ic_id']) || empty($member['duration']) || empty($member['start_date']) || empty($member['payment_type']) || empty($member['budget'])) {
                    error_log("Incomplete member data: " . json_encode($member));
                    continue;
                }
    
                // Split and sanitize the id, name, and IC
                list($member_id, $member_name, $member_ic) = explode('|', $member['name_ic_id']);
                $member_id = mysqli_real_escape_string($db, $member_id);
                $member_name = mysqli_real_escape_string($db, $member_name);
                $member_ic = mysqli_real_escape_string($db, $member_ic);
    
                // Sanitize additional fields
                $duration = mysqli_real_escape_string($db, $member['duration']);
                $start_date = mysqli_real_escape_string($db, $member['start_date']);
                $payment_type = mysqli_real_escape_string($db, $member['payment_type']);
                $budget = mysqli_real_escape_string($db, $member['budget']);
    
                // Insert into database
                $query = "INSERT INTO project_members (
                            project_id, leader_id, project_leader, project_no, project_source, project_title, 
                            member_id, member_name, member_ic, duration, start_date, payment_type, budget, date_added, status
                          ) VALUES (
                            '$id', '$leader_id', '$project_leader', '$project_no', 'Training', '$project_title', 
                            '$member_id', '$member_name', '$member_ic', '$duration', '$start_date', '$payment_type', '$budget', '$today', 'Pending Verification'
                          )";
    
                if (!mysqli_query($db, $query)) {
                    error_log("Error adding member: " . mysqli_error($db));
                } else {
                    error_log("Member added successfully: $member_name ($member_ic)");
                }
            }
    
            // Redirect after successful insertion
            header("Location: training-project-add-members.php?update=save-success&id=$id");
        } else {
            error_log("No members were selected or members array is empty.");
            echo "No members were selected.";
        }
    }
    
    //-------------------------------------------------------------------------------//
    
    function btn_RejectResearchAssistant()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        $formattedDate = date("j F Y", strtotime($today));
    
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $remark = mysqli_real_escape_string($db, $_POST['rejection_remark']);
    
        // Update the research_assistant status to 'Rejected'
        $updateQuery = "UPDATE `research_assistant` SET `status` = 'Rejected' WHERE `id` = '$id'";
    
        // Insert into research_assistant_reject_remark table
        $insertRemarkQuery = "INSERT INTO `research_assistant_registration_remark` (`research_id`, `reject_flag`, `remarks`, `remark_by`, `date_added`) 
                              VALUES ('$id', 1, 'Rejected by Admin on $formattedDate with remark; $remark', 'Admin', '$today')";
    
        // Execute both queries
        if (mysqli_query($db, $updateQuery) && mysqli_query($db, $insertRemarkQuery)) {
            header("Location: research-status.php");
        } else {
            // Handle errors if queries fail
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_ApproveResearchAssistant()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        $formattedDate = date("j F Y", strtotime($today));
    
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $query = "UPDATE `research_assistant` SET `status` = 'Approved' WHERE `id` = '$id'";
        
        // Insert into research_assistant_reject_remark table
        $insertRemarkQuery = "INSERT INTO `research_assistant_registration_remark` (`research_id`, `remarks`, `remark_by`, `date_added`) 
                              VALUES ('$id', 'Approved by Admin on $formattedDate', 'Admin', '$today')";
    
        if (mysqli_query($db, $query) && mysqli_query($db, $insertRemarkQuery)) {
            header("Location: research-status.php");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_VerifyResearchAssistant()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        $formattedDate = date("j F Y", strtotime($today));
    
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $query = "UPDATE `research_assistant` SET `status` = 'Pending Approval' WHERE `id` = '$id'";
        
        // Insert into research_assistant_reject_remark table
        $insertRemarkQuery = "INSERT INTO `research_assistant_registration_remark` (`research_id`, `remarks`, `remark_by`, `date_added`) 
                              VALUES ('$id', 'Verified by Admin on $formattedDate', 'Admin', '$today')";
    
        if (mysqli_query($db, $query) && mysqli_query($db, $insertRemarkQuery)) {
            header("Location: research-status.php");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_VerifyResearchAssistantAppointment()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        $formattedDate = date("j F Y", strtotime($today));
    
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_source = mysqli_real_escape_string($db, $_POST['project_source']);
        $member_name = mysqli_real_escape_string($db, $_POST['member_name']);
        $member_ic = mysqli_real_escape_string($db, $_POST['member_ic']);
    
        $query = "UPDATE project_members SET `status` = 'Pending Approval' WHERE `id` = '$id'";
        
        // Insert into research_assistant_reject_remark table
        $insertRemarkQuery = "INSERT INTO project_tracker (`project_id`, `project_no`, `remark`, `date`) 
                              VALUES ('$project_id', '$project_no', '$member_name ($member_ic) verified by Admin on $formattedDate', '$today')";
    
        if (mysqli_query($db, $query) && mysqli_query($db, $insertRemarkQuery)) {
            header("Location: ../admin/index.php");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_ApproveResearchAssistantAppointment()
    {
        global $db;
        
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        $formattedDate = date("j F Y", strtotime($today));
    
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_source = mysqli_real_escape_string($db, $_POST['project_source']);
        $member_name = mysqli_real_escape_string($db, $_POST['member_name']);
        $member_ic = mysqli_real_escape_string($db, $_POST['member_ic']);
    
        $query = "UPDATE project_members SET `status` = 'Appointed' WHERE `id` = '$id'";
        
        // Insert into research_assistant_reject_remark table
        $insertRemarkQuery = "INSERT INTO project_tracker (`project_id`, `project_no`, `remark`, `date`) 
                              VALUES ('$project_id', '$project_no', '$member_name ($member_ic) approved and appointed by Admin on $formattedDate', '$today')";
    
        if (mysqli_query($db, $query) && mysqli_query($db, $insertRemarkQuery)) {
            header("Location: ../admin/index.php");
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    
    function btn_RejectResearchAssistantAppointment()
    {
        global $db;
    
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $today = date("Y/m/d");
        $formattedDate = date("j F Y", strtotime($today));
    
        $id = mysqli_real_escape_string($db, $_POST['id']);
        $project_id = mysqli_real_escape_string($db, $_POST['project_id']);
        $project_no = mysqli_real_escape_string($db, $_POST['project_no']);
        $project_source = mysqli_real_escape_string($db, $_POST['project_source']);
        $member_name = mysqli_real_escape_string($db, $_POST['member_name']);
        $member_ic = mysqli_real_escape_string($db, $_POST['member_ic']);
        $remark = mysqli_real_escape_string($db, $_POST['rejection_remark']);

    
        // Update the research_assistant status to 'Rejected'
        $updateQuery = "UPDATE `project_members` SET `status` = 'Rejected' WHERE `id` = '$id'";
    
        // Insert into research_assistant_reject_remark table
        $insertRemarkQuery = "INSERT INTO project_tracker (`project_id`, `project_no`, `remark`, `date`) 
                              VALUES ('$project_id', '$project_no', '$member_name ($member_ic) rejected by Admin; $remark', '$today')";
    
        // Execute both queries
        if (mysqli_query($db, $updateQuery) && mysqli_query($db, $insertRemarkQuery)) {
            header("Location: ../admin/index.php");
        } else {
            // Handle errors if queries fail
            echo "Error: " . mysqli_error($db);
        }
    }
?>
