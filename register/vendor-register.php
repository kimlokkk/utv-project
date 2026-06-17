<?php
    include '../db_connect/db_connect.php';
    include '../function/function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms | Vendor Registration</title>

    <link rel="stylesheet" href="../assets/node_modules/dropify/dist/css/dropify.min.css">
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="dist/css/pages/file-upload.css" rel="stylesheet">
    <link href="../admin/assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body.horizontal-nav.skin-megna.fixed-layout {
            background: #eef3f8;
        }

        .page-wrapper {
            background: linear-gradient(180deg, #edf3f9 0%, #f7f9fc 100%);
            min-height: 100vh;
        }

        .container-fluid {
            max-width: 1280px;
            margin: 0 auto;
            padding-top: 24px;
            padding-bottom: 40px;
        }

        .admin-hero {
            background: linear-gradient(135deg, #163b65, #245f9b);
            border-radius: 20px;
            padding: 24px 28px;
            color: #fff;
            box-shadow: 0 18px 40px rgba(22, 59, 101, 0.18);
            margin-bottom: 24px;
        }

        .admin-hero-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .brand-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .brand-logo {
            background: #fff;
            border-radius: 14px;
            padding: 10px 14px;
        }

        .brand-logo img {
            max-height: 52px;
            width: auto;
            display: block;
        }

        .hero-text small {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            opacity: 0.8;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .hero-text h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .hero-text p {
            margin: 6px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .hero-badge {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 13px;
        }

        .intro-card,
        .section-card,
        .action-card {
            background: #fff;
            border: 1px solid #e6edf5;
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .intro-card {
            padding: 24px 28px;
            margin-bottom: 24px;
        }

        .intro-card h1 {
            font-size: 30px;
            font-weight: 700;
            color: #20324a;
            margin-bottom: 10px;
        }

        .intro-card p {
            color: #64748b;
            margin: 0;
            line-height: 1.7;
        }

        .section-card {
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-header {
            padding: 18px 24px;
            border-bottom: 1px solid #edf2f7;
            background: #f8fbff;
        }

        .section-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1f3b63;
        }

        .section-header p {
            margin: 6px 0 0;
            color: #7a889b;
            font-size: 13px;
        }

        .section-body {
            padding: 24px;
        }

        .form-group label {
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

        .form-control:focus {
            border-color: #2f80ed;
        }

        textarea.form-control {
            min-height: auto;
        }

        .subheading {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 16px;
        }

        .helper-text {
            font-size: 12px;
            color: #7a889b;
            margin-top: 6px;
        }

        .declaration-box {
            background: #f8fbff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 18px;
        }

        .declaration-box p {
            margin-bottom: 14px;
            color: #475569;
            line-height: 1.8;
        }

        .action-card {
            padding: 18px 22px;
        }

        .action-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .action-note {
            font-size: 14px;
            color: #64748b;
        }

        .btn-admin-primary {
            background: linear-gradient(135deg, #1d4f91, #2f80ed);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.18);
        }

        .btn-admin-primary:hover,
        .btn-admin-primary:focus {
            color: #fff;
        }

        .btn-admin-light {
            background: #fff;
            border: 1px solid #d7dee8;
            color: #334155;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
        }

        .dropify-wrapper {
            border-radius: 12px;
            border: 1px solid #d8e2ee;
        }

        @media (max-width: 767px) {
            .intro-card, .section-body, .section-header {
                padding-left: 18px;
                padding-right: 18px;
            }

            .intro-card h1 {
                font-size: 24px;
            }

            .hero-text h2 {
                font-size: 22px;
            }
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
                            <p>Vendor registration and onboarding</p>
                        </div>
                    </div>
                    <div class="hero-badge">Vendor Registration Module</div>
                </div>
            </div>

            <div class="intro-card">
                <h1>Vendor Registration Form</h1>
                <p>
                    Complete the information below to register your vendor profile. Please ensure that all statutory, business, and banking details are entered accurately before submission.
                </p>
            </div>

            <form method="POST" enctype="multipart/form-data" id="vendorForm">

                <div class="section-card">
                    <div class="section-header">
                        <h3>Account & Company Information</h3>
                        <p>Set your login details and provide your company registration information.</p>
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

                        <div class="subheading mt-3">Company Details</div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="company_email" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registered Address <span class="text-danger">*</span></label>
                                    <textarea name="registered_address" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mailing Address</label>
                                    <textarea name="mailing_address" class="form-control" rows="3"></textarea>
                                    <div class="helper-text">Leave blank if same as registered address.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>New SSM No <span class="text-danger">*</span></label>
                                    <input type="text" name="ssm_no_new" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Old SSM No <span class="text-danger">*</span></label>
                                    <input type="text" name="ssm_no_old" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TIN No</label>
                                    <input type="text" name="tin_no" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Website</label>
                                    <input type="text" name="website" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type of Organization <span class="text-danger">*</span></label>
                                    <select class="form-control" name="org_type" required>
                                        <option disabled selected>Select Option</option>
                                        <option value="Berhad">Berhad</option>
                                        <option value="Sendirian Berhad">Sendirian Berhad</option>
                                        <option value="Partnership">Partnership</option>
                                        <option value="Sole Proprietor">Sole Proprietor</option>
                                        <option value="Koperasi">Koperasi</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Swift Code</label>
                                    <input type="text" name="swift_code" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Type of Business Activities <span class="text-danger">*</span></label>
                                    <textarea name="business_activities" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>MSIC Code</label>
                                    <input type="text" name="msic_code" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3>Contact Person</h3>
                        <p>Primary contact details for vendor communication.</p>
                    </div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Position <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_position" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone No <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_phone" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="contact_email" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3>Bank Information</h3>
                        <p>Provide the bank details for payment processing.</p>
                    </div>
                    <div class="section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Account <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Bank Address <span class="text-danger">*</span></label>
                                    <textarea name="bank_address" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bank Statement Letterhead with Account Info <span class="text-danger">*</span></label>
                                    <input type="file" name="bank_statement_file" class="dropify" required data-allowed-file-extensions="pdf jpg jpeg png" data-max-file-size="5M" />
                                    <div class="helper-text">Allowed format: PDF, JPG, JPEG, PNG only.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Copy of SSM <span class="text-danger">*</span></label>
                                    <input type="file" name="ssm_file" class="dropify" required data-allowed-file-extensions="pdf jpg jpeg png" data-max-file-size="5M" />
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
                                I consent to UiTM Technoventure under the PDPA 2010 to publish my information when appropriate.
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
                        <div class="action-note">
                            Please review all information before submitting your vendor registration.
                        </div>
                        <div>
                            <button type="reset" class="btn btn-admin-light">Reset</button>
                            <button type="submit" name="btn_registerVendor" class="btn btn-admin-primary">Submit Registration</button>
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

<script>
$(document).ready(function() {
    $('.dropify').dropify();

    $('#vendorForm').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        const $submitButton = $(form).find('button[type="submit"]');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        formData.append('btn_registerVendor', '1');

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
