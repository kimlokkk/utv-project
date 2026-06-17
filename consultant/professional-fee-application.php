<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    include '../function/function.php';
    
    $userData = $_SESSION['user_data'];
?>
<?php
    $id                 =   $_GET['projectId'];
    $query              =   "SELECT * FROM project WHERE id = '$id' ";  
    $result             =   mysqli_query($db, $query);
    while($row          =   mysqli_fetch_array($result))  
    {
        $project_leader                 = $row['project_leader'];
        $project_no                     = $row['project_no'];
        $project_title                  = $row['project_title'];
        $project_leader                 = $row['project_leader'];
        $leader_id                      = $row['leader_id']; 
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'include/meta.php'; ?>
    <title>IProms</title>
    <!-- This page CSS -->
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
        href="../assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Custom CSS -->
    <link href="dist/css/style.css" rel="stylesheet">
    <link href="../assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- Dashboard 1 Page CSS -->
    <link href="dist/css/pages/dashboard1.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="skin-blue fixed-layout">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <?php include 'include/preloader.php'; ?>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php include 'include/topbar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php include 'include/left_sidebar.php'; ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Professional Fee Application</h4>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Financial Request</a></li>
                                <li class="breadcrumb-item active">Professional Fee Application</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Info box -->
                <!-- ============================================================== -->
                <!-- Row -->
                <div class="row">
                    <div class="col-md-12">
                        <form id="professionalForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="project_no" value="<?php echo $project_no; ?>">
                            <!-- Project Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Project Details</h3>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Number</strong></label>
                                                <input type="text" name="project_number" value="<?php echo $project_no; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Title</strong></label>
                                                <input type="text" name="project_title" value="<?php echo $project_title; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Project Leader Name</strong></label>
                                                <input type="text" name="project_leader_name" value="<?php echo $project_leader; ?>" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Recipients Details -->
                            <div class="card">
                                <h3 class="card-header bg-info text-white">Recipients Details</h3>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dynamic_field">
                                            <thead>
                                                <tr>
                                                    <th>Member Name</th>
                                                    <th>Amount (RM)</th>
                                                    <th>Add</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 60%;">
                                                        <select name="member_id[]" class="select2 form-control custom-select" required>
                                                            <option value="" disabled selected>Select Member</option>
                                                            <?php
                                                                // Masukkan query ini sebelum form untuk fetch staff
                                                                $staff_query = "
                                                                    SELECT id, full_name 
                                                                    FROM uitm_staff 
                                                                    WHERE id = (SELECT leader_id FROM project WHERE id = '$id')
                                                                    OR id IN (SELECT member_id FROM project_members_consultant WHERE project_id = '$id')
                                                                    ORDER BY full_name ASC
                                                                ";
                                                                $staff_result = mysqli_query($db, $staff_query);
                                                                while ($staff = mysqli_fetch_assoc($staff_result)) {
                                                                    echo "<option value='" . $staff['id'] . "'>" . htmlspecialchars($staff['full_name']) . "</option>";
                                                                }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="amount[]" class="form-control" placeholder="Enter amount" required></td>
                                                    <td class="text-center" style="width: 10%;">
                                                        <button type="button" name="add" id="add" class="btn btn-info">Add</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit Buttons -->
                            <?php
                            $isLeader = ($userData['id'] == $leader_id);
                            ?>
                            <div class="row m-t-30 m-b-30">
                                <div class="col-md-12">
                                    <button type="button" id="submitToProjectLeader" class="btn btn-lg btn-info" <?php echo $isLeader ? 'disabled' : ''; ?>>
                                        Submit to Project Leader
                                    </button>
                                    <button type="button" id="submitByLeader" class="btn btn-lg btn-success" <?php echo !$isLeader ? 'disabled' : ''; ?>>
                                        Submit
                                    </button>
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
        <!-- ============================================================== -->
        <!-- Logout Modal -->
        <!-- ============================================================== -->
        <?php include 'include/logoutmodal.php'; ?>
        <!-- ============================================================== -->
        <!-- End Logout Modal -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap popper Core JavaScript -->
    <script src="../assets/node_modules/popper/popper.min.js"></script>
    <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="dist/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!-- Sweet-Alert  -->
    <script src="../assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/node_modules/sweetalert2/sweet-alert.init.js"></script>
    <script src="../assets/node_modules/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="assets/node_modules/dropify/dist/js/dropify.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- jQuery peity -->
    <script src="../assets/node_modules/peity/jquery.peity.min.js"></script>
    <script src="../assets/node_modules/peity/jquery.peity.init.js"></script>
    <script src="dist/js/dashboard1.js"></script>
    <!-- This is data table -->
    <script src="../assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script>
        $(document).on('click', '#submitToProjectLeader', function () {
            const $submitBtn = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "Once submit, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons
        
                if (result.isConfirmed || result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");
        
                    // Ambil borang menggunakan ID borang
                    const form = $('#professionalForm')[0]; // Gantikan #invoiceForm dengan ID borang anda
                    const formData = new FormData(form);
        
                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }
        
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'process_professional.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        timeout: 30000,
                        beforeSend: function () {
                            $submitBtn.prop('disabled', true).text('Submitting...');
                            Swal.fire({
                                title: 'Submitting Application',
                                text: 'Please wait while your professional fee application is being submitted.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                                onOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function (response) {
                            console.log("AJAX success response:", response);
        
                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'professional-fee.php'; // Alihkan ke halaman yang diinginkan
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            const message = status === 'timeout'
                                ? 'Submission timed out. Please try again.'
                                : 'An error occurred during submission. Please check the console for details.';
                            Swal.fire(
                                'Error!',
                                message,
                                'error'
                            );
                        },
                        complete: function () {
                            $submitBtn.prop('disabled', false).text('Submit to Project Leader');
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Professional fee application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#submitByLeader', function () {
            const $submitBtn = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "Once submit, you cannot edit this project!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log("Result object:", result); // Debug respons
        
                if (result.isConfirmed || result.value) { // Jika pengguna mengesahkan
                    console.log("User confirmed submission");
        
                    // Ambil borang menggunakan ID borang
                    const form = $('#professionalForm')[0]; // Gantikan #invoiceForm dengan ID borang anda
                    const formData = new FormData(form);
        
                    // Tambah log untuk debug data yang dihantar
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`); // Debug semua data
                    }
        
                    // Hantar permintaan AJAX ke server
                    $.ajax({
                        url: 'submit_leader_professional.php',
                        method: 'POST',
                        data: formData, // Hantar data borang
                        processData: false, // Jangan proses data
                        contentType: false, // Jangan set header Content-Type
                        dataType: 'json',
                        timeout: 30000,
                        beforeSend: function () {
                            $submitBtn.prop('disabled', true).text('Submitting...');
                            Swal.fire({
                                title: 'Submitting Application',
                                text: 'Please wait while your professional fee application is being submitted.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                                onOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function (response) {
                            console.log("AJAX success response:", response);
        
                            if (response.success) {
                                Swal.fire(
                                    'Submitted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = 'professional-fee.php'; // Alihkan ke halaman yang diinginkan
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText || error);
                            const message = status === 'timeout'
                                ? 'Submission timed out. Please try again.'
                                : 'An error occurred during submission. Please check the console for details.';
                            Swal.fire(
                                'Error!',
                                message,
                                'error'
                            );
                        },
                        complete: function () {
                            $submitBtn.prop('disabled', false).text('Submit');
                        }
                    });
                } else {
                    console.log("User cancelled submission"); // Jika "Cancel" ditekan
                    Swal.fire(
                        'Cancelled',
                        'Professional fee application submission has been cancelled.',
                        'info'
                    );
                }
            });
        });
    </script>
    <script>
        $(function () {
            // Switchery
            if (typeof Switchery !== 'undefined') {
                $('.js-switch').each(function () {
                    new Switchery($(this)[0], $(this).data());
                });
            }
            // For select 2
            $(".select2").select2({
                width: '100%'
            });
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker();
            }
            //Bootstrap-TouchSpin
            if ($.fn.TouchSpin) {
                $(".vertical-spin").TouchSpin({
                    verticalbuttons: true
                });
                var vspinTrue = $(".vertical-spin").TouchSpin({
                    verticalbuttons: true
                });
                if (vspinTrue) {
                    $('.vertical-spin').prev('.bootstrap-touchspin-prefix').remove();
                }
                $("input[name='tch1']").TouchSpin({
                    min: 0,
                    max: 100,
                    step: 0.1,
                    decimals: 2,
                    boostat: 5,
                    maxboostedstep: 10,
                    postfix: '%'
                });
                $("input[name='tch2']").TouchSpin({
                    min: -1000000000,
                    max: 1000000000,
                    stepinterval: 50,
                    maxboostedstep: 10000000,
                    prefix: '$'
                });
                $("input[name='tch3']").TouchSpin();
                $("input[name='tch3_22']").TouchSpin({
                    initval: 40
                });
                $("input[name='tch5']").TouchSpin({
                    prefix: "pre",
                    postfix: "post"
                });
            }
            // For multiselect
            if ($.fn.multiSelect) {
                $('#pre-selected-options').multiSelect();
                $('#optgroup').multiSelect({
                    selectableOptgroup: true
                });
                $('#public-methods').multiSelect();
                $('#select-all').click(function () {
                    $('#public-methods').multiSelect('select_all');
                    return false;
                });
                $('#deselect-all').click(function () {
                    $('#public-methods').multiSelect('deselect_all');
                    return false;
                });
                $('#refresh').on('click', function () {
                    $('#public-methods').multiSelect('refresh');
                    return false;
                });
                $('#add-option').on('click', function () {
                    $('#public-methods').multiSelect('addOption', {
                        value: 42,
                        text: 'test 42',
                        index: 0
                    });
                    return false;
                });
            }
            $(".ajax").select2({
                ajax: {
                    url: "https://api.github.com/search/repositories",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                }, // let our custom formatter work
                minimumInputLength: 1,
                //templateResult: formatRepo, // omitted for brevity, see the source of this page
                //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            let i = 1;
            $('#add').click(function () {
                i++;
                const newRow = `
                    <tr id="row${i}">
                        <td>
                            <select name="member_id[]" class="form-control select2" required>
                                <option value="" disabled selected>Select Member</option>
                                <?php
                                    mysqli_data_seek($staff_result, 0); // Reset pointer
                                    while ($staff = mysqli_fetch_assoc($staff_result)) {
                                        echo "<option value='" . $staff['id'] . "'>" . htmlspecialchars($staff['full_name']) . "</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td><input type="number" name="amount[]" class="form-control" placeholder="Enter amount" required></td>
                        <td class="text-center">
                            <button type="button" name="remove" id="${i}" class="btn btn-danger btn_remove">Remove</button>
                        </td>
                    </tr>`;
                $('#dynamic_field tbody').append(newRow);
                $('.select2').select2({
                    width: '100%'
                });
            });
            $(document).on('click', '.btn_remove', function () {
                const button_id = $(this).attr("id");
                $('#row' + button_id).remove();
            });
        });
    </script>
</body>

</html>
