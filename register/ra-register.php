<?php
    include '../db_connect/db_connect.php';
    include '../function/function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms | Research Assistant / Research Officer Registration</title>

    <link rel="stylesheet" href="../assets/node_modules/dropify/dist/css/dropify.min.css">
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="dist/css/pages/file-upload.css" rel="stylesheet">
    <link href="../admin/assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body.horizontal-nav.skin-megna.fixed-layout { background: #eef3f8; }
        .page-wrapper { background: linear-gradient(180deg, #edf3f9 0%, #f7f9fc 100%); min-height: 100vh; }
        .container-fluid { max-width: 1280px; margin: 0 auto; padding-top: 24px; padding-bottom: 40px; }

        .admin-hero {
            background: linear-gradient(135deg, #163b65, #245f9b);
            border-radius: 20px;
            padding: 24px 28px;
            color: #fff;
            box-shadow: 0 18px 40px rgba(22, 59, 101, 0.18);
            margin-bottom: 24px;
        }

        .admin-hero-inner { display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; }
        .brand-group { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .brand-logo { background: #fff; border-radius: 14px; padding: 10px 14px; }
        .brand-logo img { max-height: 52px; width: auto; display: block; }

        .hero-text small { display: block; font-size: 11px; text-transform: uppercase; opacity: 0.8; letter-spacing: 1px; margin-bottom: 4px; }
        .hero-text h2 { margin: 0; font-size: 28px; font-weight: 700; }
        .hero-text p { margin: 6px 0 0; opacity: 0.9; font-size: 14px; }
        .hero-badge {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 13px;
        }

        .intro-card, .section-card, .action-card {
            background: #fff;
            border: 1px solid #e6edf5;
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .intro-card { padding: 24px 28px; margin-bottom: 24px; }
        .intro-card h1 { font-size: 30px; font-weight: 700; color: #20324a; margin-bottom: 10px; }
        .intro-card p { color: #64748b; margin: 0; line-height: 1.7; }

        .section-card { margin-bottom: 24px; overflow: hidden; }
        .section-header { padding: 18px 24px; border-bottom: 1px solid #edf2f7; background: #f8fbff; }
        .section-header h3 { margin: 0; font-size: 20px; font-weight: 700; color: #1f3b63; }
        .section-header p { margin: 6px 0 0; color: #7a889b; font-size: 13px; }
        .section-body { padding: 24px; }

        .form-group label, .control-label {
            font-weight: 600;
            color: #334155;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .form-control {
            min-height: 44px;
            border-radius: 10px;
            border: 1px solid #d8e2ee;
            box-shadow: none !important;
            font-size: 14px;
        }

        .form-control:focus { border-color: #2f80ed; }
        textarea.form-control { min-height: auto; }

        .subheading {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 16px;
        }

        .copy-box {
            background: #f8fbff;
            border: 1px dashed #bfd7ff;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .copy-box .form-check-label { font-weight: 600; color: #234268; }
        .helper-text { font-size: 12px; color: #7a889b; margin-top: 6px; }

        .declaration-box {
            background: #f8fbff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 18px;
        }

        .declaration-box p { margin-bottom: 14px; color: #475569; line-height: 1.8; }

        .action-card { padding: 18px 22px; }
        .action-wrap { display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
        .action-note { font-size: 14px; color: #64748b; }

        .btn-admin-primary {
            background: linear-gradient(135deg, #1d4f91, #2f80ed);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.18);
        }

        .btn-admin-light {
            background: #fff;
            border: 1px solid #d7dee8;
            color: #334155;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
        }

        .dropify-wrapper { border-radius: 12px; border: 1px solid #d8e2ee; }

        @media (max-width: 767px) {
            .intro-card, .section-body, .section-header { padding-left: 18px; padding-right: 18px; }
            .intro-card h1 { font-size: 24px; }
            .hero-text h2 { font-size: 22px; }
        }
    </style>
</head>

<body class="horizontal-nav skin-megna fixed-layout">
<?php include 'include/preloader.php'; ?>

<div id="main-wrapper">
    <div class="page-wrapper">
        <div class="container-fluid">

            <div class="admin-hero">
                <div class="admin-hero-inner">
                    <div class="brand-group">
                        <div class="brand-logo">
                            <img src="../assets/images/Logo.png" alt="UTV Logo">
                        </div>
                        <div class="brand-logo">
                            <img src="../assets/images/UiTM-Logo.png" alt="UiTM Logo">
                        </div>
                        <div class="hero-text">
                            <small>Institutional Admin Portal</small>
                            <h2>IProms Registration System</h2>
                            <p>Research Assistant / Research Officer registration</p>
                        </div>
                    </div>
                    <div class="hero-badge">RA / RO Registration Module</div>
                </div>
            </div>

            <div class="intro-card">
                <h1>Research Assistant / Research Officer Registration</h1>
                <p>
                    Complete the information below to register your profile. Please ensure all details are accurate and that uploaded documents follow the permitted format requirements.
                </p>
            </div>

            <form method="POST" enctype="multipart/form-data" id="raForm">

                <div class="section-card">
                    <div class="section-header">
                        <h3>Account & Personal Information</h3>
                        <p>Set your login details and complete your personal, employment and statutory information.</p>
                    </div>
                    <div class="section-body">
                        <div class="subheading">Login Credentials</div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Re-Type Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirm" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="subheading mt-3">Personal Details</div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Designation <span class="text-danger">*</span></label>
                                    <select name="designation" class="form-control" required>
                                        <option value="" disabled selected>Select Designation</option>
                                        <option value="Dr">Dr</option>
                                        <option value="Mr">Mr</option>
                                        <option value="Ms">Ms</option>
                                        <option value="Miss">Miss</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Identity Card / Passport <span class="text-danger">*</span></label>
                                    <select id="id-type" class="form-control" required>
                                        <option value="IC" selected>IC</option>
                                        <option value="Passport">Passport</option>
                                    </select>
                                    <input type="text" id="id-input" name="ic" class="form-control mt-2" value="<?php echo isset($_POST['ic']) ? $_POST['ic'] : '' ?>" placeholder="Enter IC or Passport" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mobile No <span class="text-danger">*</span></label>
                                    <input type="tel" id="phone-mask-me" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TIN No</label>
                                    <input type="text" name="tin_no" class="form-control" value="<?php echo isset($_POST['tin_no']) ? $_POST['tin_no'] : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alternate Email Address</label>
                                    <input type="email" name="email_2" class="form-control" value="<?php echo isset($_POST['email_2']) ? $_POST['email_2'] : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="subheading mt-3">PTJ & Address</div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>PTJ <span class="text-danger">*</span></label>
                                    <select name="ptj_id" id="ptj_id" class="form-control" required>
                                        <option value="" disabled selected>Loading PTJ list...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>PTJ Address (Permanent) <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="ptj_address_permanent" name="ptj_address_permanent" rows="3" required><?php echo isset($_POST['ptj_address_permanent']) ? $_POST['ptj_address_permanent'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="copy-box">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="sameAsPermanent" name="same_as_permanent" value="1">
                                <label class="form-check-label" for="sameAsPermanent">Same as Permanent Address</label>
                            </div>
                            <div class="helper-text">If checked, Current PTJ Address will be auto-filled from Permanent PTJ Address.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>PTJ Address (Current) <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="ptj_address_current" name="ptj_address_current" rows="3" required><?php echo isset($_POST['ptj_address_current']) ? $_POST['ptj_address_current'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="subheading mt-3">Employment & Statutory Information</div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender <span class="text-danger">*</span></label>
                                    <select class="form-control" name="gender" required>
                                        <option disabled selected>Select Option</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Citizenship <span class="text-danger">*</span></label>
                                    <select class="form-control" name="citizenship" required>
                                        <option disabled selected>Select Option</option>
                                        <option value="Malaysian">Malaysian</option>
                                        <option value="Non-Malaysian">Non-Malaysian</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marital Status <span class="text-danger">*</span></label>
                                    <select class="form-control" name="marital_status" required>
                                        <option disabled selected>Select Option</option>
                                        <option value="Married">Married</option>
                                        <option value="Separated">Separated</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Single">Single</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Employment Position <span class="text-danger">*</span></label>
                                    <select class="form-control" name="employment_position" required>
                                        <option disabled selected>Select Option</option>
                                        <option value="Research Assistant">Research Assistant</option>
                                        <option value="Research Officer">Research Officer</option>
                                        <option value="Postdoctoral Researcher">Postdoctoral Researcher</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>EPF No (If Any)</label>
                                    <input type="text" name="epf_no" class="form-control" value="<?php echo isset($_POST['epf_no']) ? $_POST['epf_no'] : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>SOCSO No (If Any)</label>
                                    <input type="text" name="socso_no" class="form-control" value="<?php echo isset($_POST['socso_no']) ? $_POST['socso_no'] : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Income Tax No (If Any)</label>
                                    <input type="text" name="income_tax_no" class="form-control" value="<?php echo isset($_POST['income_tax_no']) ? $_POST['income_tax_no'] : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Expertise <span class="text-danger">*</span></label>
                                    <input type="text" name="expertise" class="form-control" value="<?php echo isset($_POST['expertise']) ? $_POST['expertise'] : '' ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3>Bank Information</h3>
                        <p>Select your bank from the approved list and upload documents in PDF or image format only.</p>
                    </div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bank Name <span class="text-danger">*</span></label>
                                    <select name="bank_id" id="bank_id" class="form-control" required>
                                        <option value="" selected disabled>Loading bank list...</option>
                                    </select>
                                    <div class="helper-text" id="bankRuleText">Account number rule will appear here.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="no_account" id="no_account" class="form-control" value="<?php echo isset($_POST['no_account']) ? $_POST['no_account'] : '' ?>" required maxlength="30" inputmode="numeric">
                                    <div class="helper-text">Numbers only. Validation follows selected bank format.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bank Statement Letterhead <span class="text-danger">*</span></label>
                                    <input type="file" name="bank_statement_file" class="dropify" required data-allowed-file-extensions="pdf jpg jpeg png" data-max-file-size="5M" />
                                    <div class="helper-text">Allowed format: PDF, JPG, JPEG, PNG only.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Copy of IC <span class="text-danger">*</span></label>
                                    <input type="file" name="copy_ic_file" class="dropify" required data-allowed-file-extensions="pdf jpg jpeg png" data-max-file-size="5M" />
                                    <div class="helper-text">Allowed format: PDF, JPG, JPEG, PNG only.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Copy of Certificate / CV <span class="text-danger">*</span></label>
                                    <input type="file" name="copy_certificate_file" class="dropify" required data-allowed-file-extensions="pdf jpg jpeg png" data-max-file-size="5M" />
                                    <div class="helper-text">Allowed format: PDF, JPG, JPEG, PNG only.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3>Declaration</h3>
                        <p>Confirmation of information accuracy and consent.</p>
                    </div>
                    <div class="section-body">
                        <div class="declaration-box">
                            <p>
                                All information given is true. <br>
                                I have given my consent to UiTM Technoventure under the Personal Data Protection Act (PDPA) 2010 to publish my personal information in the correct place and time.
                            </p>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="declarationCheck" name="declaration" required>
                                <label class="form-check-label" for="declarationCheck">I confirm the above statement.</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-wrap">
                        <div class="action-note">Please review all information before submitting your registration.</div>
                        <div>
                            <button type="reset" class="btn btn-admin-light">Reset</button>
                            <button type="submit" name="btn_registerRA" class="btn btn-admin-primary">Submit Registration</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
</div>

<script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
<script src="../assets/node_modules/popper/popper.min.js"></script>
<script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
<script src="dist/js/waves.js"></script>
<script src="dist/js/sidebarmenu.js"></script>
<script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
<script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
<script src="dist/js/custom.min.js"></script>
<script src="dist/js/pages/validation.js"></script>
<script src="../admin/assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="dist/js/pages/jasny-bootstrap.js"></script>
<script src="../assets/node_modules/dropify/dist/js/dropify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

<script>
$(document).ready(function () {
    $('.dropify').dropify();

    $('#phone-mask-me').inputmask({
        mask: "999-9999999[9]",
        placeholder: "_",
        showMaskOnHover: false,
        showMaskOnFocus: true
    });

    function applyMask() {
        const selectedType = $('#id-type').val();
        const $input = $('#id-input');
        $input.val('');

        if (selectedType === 'IC') {
            $input.inputmask({
                mask: "999999-99-9999",
                placeholder: "_",
                showMaskOnHover: false,
                showMaskOnFocus: true
            });
        } else {
            $input.inputmask('remove');
        }
    }

    applyMask();
    $('#id-type').change(applyMask);

    function syncCurrentWithPermanent() {
        if ($('#sameAsPermanent').is(':checked')) {
            $('#ptj_address_current').val($('#ptj_address_permanent').val());
            $('#ptj_address_current').prop('readonly', true);
        } else {
            $('#ptj_address_current').prop('readonly', false);
        }
    }

    $('#sameAsPermanent').on('change', syncCurrentWithPermanent);
    $('#ptj_address_permanent').on('keyup change', function () {
        if ($('#sameAsPermanent').is(':checked')) {
            syncCurrentWithPermanent();
        }
    });

    let bankMap = {};

    $.ajax({
        url: 'get_bank_list.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            let html = '<option value="" disabled selected>Select Bank</option>';
            if (res.status === 'success' && res.data.length > 0) {
                res.data.forEach(function(bank) {
                    bankMap[bank.bank_id] = bank;
                    html += `<option value="${bank.bank_id}">${bank.bank_name}</option>`;
                });
            } else {
                html = '<option value="" disabled selected>No bank available</option>';
            }
            $('#bank_id').html(html);
        },
        error: function() {
            $('#bank_id').html('<option value="" disabled selected>Failed to load bank list</option>');
        }
    });

    $.ajax({
        url: 'get_ptj_list.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            let html = '<option value="" disabled selected>Select PTJ</option>';
            if (res.status === 'success' && res.data.length > 0) {
                res.data.forEach(function(ptj) {
                    html += `<option value="${ptj.ptj_id}">${ptj.ptj_name}</option>`;
                });
            } else {
                html = '<option value="" disabled selected>No PTJ available</option>';
            }
            $('#ptj_id').html(html);
        },
        error: function() {
            $('#ptj_id').html('<option value="" disabled selected>Failed to load PTJ list</option>');
        }
    });

    $('#bank_id').on('change', function() {
        const bankId = $(this).val();
        const bank = bankMap[bankId];
        if (bank) {
            $('#bankRuleText').text('Required account format: ' + bank.account_length_rule);
            $('#no_account').attr('maxlength', bank.max_length);
        } else {
            $('#bankRuleText').text('Account number rule will appear here.');
        }
    });

    $('#no_account').on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#raForm').on('submit', function (e) {
        e.preventDefault();

        const form = this;
        const $submitButton = $(form).find('button[type="submit"]');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        formData.append('btn_registerRA', '1');

        $submitButton.prop('disabled', true);
        Swal.fire({
            title: 'Submitting registration',
            text: 'Please wait while we process your details.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: function() {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        type: 'success',
                        title: 'Registration submitted',
                        text: res.message
                    }).then(() => {
                        window.location.href = '../index.php';
                    });
                } else {
                    Swal.fire('Registration error', res.message || 'Unable to submit registration.', 'error');
                }
            },
            error: function(xhr) {
                const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Server error occurred.';
                Swal.fire('Registration error', message || 'Server error occurred.', 'error');
            },
            complete: function() {
                $submitButton.prop('disabled', false);
            }
        });
    });
});
</script>

</body>
</html>
