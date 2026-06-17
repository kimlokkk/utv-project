<?php

    include '../db_connect/db_connect.php';
    include '../function/function.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <link rel="stylesheet" href="../assets/node_modules/dropify/dist/css/dropify.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <!-- page css -->
    <link href="dist/css/pages/file-upload.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<style>
    .center222 {
        text-align: center;
    }
    .justify {
        text-align: justify;
    }
    .div2 {
        height: 400px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    .form-check-label {
        padding-left: 5px;
    }
</style>
<body class="horizontal-nav skin-megna fixed-layout">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'include/preloader.php'; ?>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper" style="background-image:url(../assets/images/bg.jpg); background-size: contain; position: relative;">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <!-- row -->
                <div class="row">
                    <!-- Column -->
                    <div class="col-md-12 text-center">
                        <img src="../assets/images/1.-UTV_Logo_Full.png" width="400" height="320">
                        <img src="../assets/images/UiTM-Logo.png" width="400" height="220">
                    </div>
                </div>
                <!-- Registration Success Modal -->
                <div class="modal fade" id="registrationSuccessModal" tabindex="-1" role="dialog" aria-labelledby="registrationSuccessLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="registrationSuccessLabel">Registration Successful</h5>
                      </div>
                      <div class="modal-body">
                        Thank you! Your registration has been successfully submitted.
                      </div>
                      <div class="modal-footer">
                        <button type="button" id="successModalOkBtn" class="btn btn-success" data-dismiss="modal">OK</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-12 m-t-10 m-b-30">
                        <div class="center222 text-white">
                            <h1>Admin Registration Page</h1>
                            <br>
                            <h4>Welcome to our registration portal. Please complete the form below to create your account</h4>
                            <h4>We value your privacy and will protect your information in accordance with our privacy policy.</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Registration Form <span class="text-danger">*</span></h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Password <span class="text-danger">*</span></label>
                                                        <input type="password" name="password" value="<?php echo isset($_POST['password']) ? $_POST['password'] : '' ?>" class="form-control" required data-validation-required-message="This field is required">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Re-Type Password <span class="text-danger">*</span></label>
                                                        <input type="password" name="password_confirm" data-validation-match-match="password" value="<?php echo isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '' ?>" class="form-control" required data-validation-required-message="This field is required">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="full_name" value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : '' ?>" class="form-control" required data-validation-required-message="This field is required">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label class="control-label">Staff ID <span class="text-danger">*</span></label>
                                                        <input type="text" name="staff_id" value="<?php echo isset($_POST['staff_id']) ? $_POST['staff_id'] : '' ?>" class="form-control" required data-validation-required-message="This field is required">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Department <span class="text-danger">*</span></label>
                                                        <select name="department" class="form-control" required data-validation-required-message="This field is required">
                                                            <option value="" disabled selected>Select Level</option>
                                                            <option value="Project Registration">Project Registration</option>
                                                            <option value="Project Financial">Project Financial</option>
                                                            <option value="RA/RO Appointment">RA/RO Appointment</option>
                                                            <option value="Project Management">Project Management</option>
                                                            <option value="Project Agreement">Project Agreement</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Role <span class="text-danger">*</span></label>
                                                        <select name="role" class="form-control" required data-validation-required-message="This field is required">
                                                            <option value="" disabled selected>Select Role</option>
                                                            <option value="Admin System">Admin System</option>
                                                            <option value="Project Management">Project Management</option>
                                                            <option value="Project Registration">Project Registration</option>
                                                            <option value="Project Agreement">Project Agreement</option>
                                                            <option value="RA Appointment">RA Appointment</option>
                                                            <option value="Project Financial">Project Financial</option>
                                                            <option value="Financial">Financial</option>
                                                            <option value="BUS">BUS</option>
                                                            <option value="General">General</option>
                                                            <option value="Auditor">Auditor</option>
                                                            <option value="CEO">CEO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Level <span class="text-danger">*</span></label>
                                                        <select name="level" class="form-control" required data-validation-required-message="This field is required">
                                                            <option value="" disabled selected>Select Level</option>
                                                            <option value="Level 1">Level 1</option>
                                                            <option value="Level 2">Level 2</option>
                                                            <option value="Level 3">Level 3</option>
                                                            <option value="Level 4">Level 4</option>
                                                            <option value="Level 5">Level 5</option>
                                                            <option value="Level 6">Level 6</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>Email Address <span class="text-danger">*</span></label>
                                                        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" class="form-control" required data-validation-required-message="This field is required">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Declaration <span class="text-danger">*</span></strong></h3>
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row m-t-20">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <p class="justify">
                                                            All information given is true. <br>
                                                            I have given my consent to UiTM Technoventure under the Personal Data Protection Act (PDPA) 2010 to publish my personal information in the correct place and time.
                                                        </p>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="declarationCheck" name="declaration" required data-validation-required-message="You must agree to this declaration before submitting.">
                                                            <label class="form-check-label" for="declarationCheck">I confirm the above statement.</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-t-20 m-b-30">
                                <div class="col-md-12">
                                    <button type="reset" class="btn btn-lg btn-danger"> Reset</button>&nbsp;&nbsp;
                                    <button type="submit" name="btn_registerAdmin" class="btn btn-lg btn-secondary"> Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
         <?php include 'include/footer.php'; ?>
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="../assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/node_modules/sparkline/jquery.sparkline.min.js"></script>
    <!--Custom JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script>
        $(document).ready(function () {
            // Apply input mask for Malaysian phone numbers
            $('#phone-mask-me').inputmask({
                mask: "999-9999999[9]", // Allows for 10 or 11 digits
                placeholder: "_",
                showMaskOnHover: false,
                showMaskOnFocus: true
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            function applyMask() {
                const selectedType = $('#id-type').val();
                const $input = $('#id-input');
                $input.val(''); // Clear the input on type switch
    
                if (selectedType === 'IC') {
                    $input.inputmask({
                        mask: "999999-99-9999", // Format for Malaysian IC
                        placeholder: "_",
                        showMaskOnHover: false,
                        showMaskOnFocus: true
                    });
                } else {
                    $input.inputmask('remove'); // Remove the mask for Passport
                }
            }
    
            // Apply mask on page load and on type change
            applyMask();
            $('#id-type').change(applyMask);
        });
    </script>
    <script src="dist/js/custom.min.js"></script>
    <script src="dist/js/pages/validation.js"></script>
    <script>
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();
    }(window, document, jQuery);
    </script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <script src="dist/js/pages/jasny-bootstrap.js"></script>
    <script src="../assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <script>
    $(document).ready(function() {
        // Basic
        $('.dropify').dropify();

        // Translated
        $('.dropify-fr').dropify({
            messages: {
                default: 'Glissez-déposez un fichier ici ou cliquez',
                replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                remove: 'Supprimer',
                error: 'Désolé, le fichier trop volumineux'
            }
        });

        // Used events
        var drEvent = $('#input-file-events').dropify();

        drEvent.on('dropify.beforeClear', function(event, element) {
            return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
        });

        drEvent.on('dropify.afterClear', function(event, element) {
            alert('File deleted');
        });

        drEvent.on('dropify.errors', function(event, element) {
            console.log('Has Errors');
        });

        var drDestroy = $('#input-file-to-destroy').dropify();
        drDestroy = drDestroy.data('dropify')
        $('#toggleDropify').on('click', function(e) {
            e.preventDefault();
            if (drDestroy.isDropified()) {
                drDestroy.destroy();
            } else {
                drDestroy.init();
            }
        })
    });
    </script>
    <?php if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
    <script>
        $(document).ready(function() {
            $('#registrationSuccessModal').modal('show');
    
            $('#successModalOkBtn').on('click', function () {
                window.location.href = "../admin/index.php"; // redirect lepas user tekan OK
            });
        });
    </script>
    <?php unset($_SESSION['registration_success']); ?>
    <?php endif; ?>
</body>

</html>