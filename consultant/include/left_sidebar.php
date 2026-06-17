<?php
    session_start(); // Start the session
    include 'auth_check.php';
    include '../db_connect/db_connect.php';
    
    $userData = $_SESSION['user_data'];
?>

<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="user-pro">
                    <a class="has-arrow waves-effect waves-dark d-flex align-items-center" href="javascript:void(0)" aria-expanded="false">
                        <img src="../assets/images/admin.jpg" alt="user-img" class="img-circle">
                        <span class="hide-menu" style="display: inline-block;"><?php echo htmlspecialchars($userData['full_name']); ?></span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="#" data-toggle="modal" data-target="#logoutmodal_medium"><i class="fa fa-power-off"></i> Logout</a></li>
                    </ul>
                </li>
				<li class="nav-small-cap">--- <strong>DASHBOARD</strong></li>
                <li> <a href="index.php" ><i class="icon-home"></i><span class="hide-menu">Home</a>
                </li>
				<li class="nav-small-cap">--- <strong>MENU</strong></li>
				<li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-user"></i><span class="hide-menu">Personal Info</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="profile.php">Update Profile</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-thumb-up"></i><span class="hide-menu">PTJ Approval</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="ptj-approval-form.php">PTJ Approval Form</a></li>
                    </ul>
                </li>
                <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-plus"></i><span class="hide-menu">New Project</span> </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="#" id="newConsultancyBtn">New Consultancy Project</a></li>
                        <li><a href="#" id="newTrainingBtn">New Training Project</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-agenda"></i><span class="hide-menu">Consultancy Project</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="consultancy-project.php">Consultancy Project List</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-book"></i><span class="hide-menu">Training Project</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="training-project.php">Training Project List</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-money"></i><span class="hide-menu">Financial Request</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="invoice-application.php">Invoice Application</a></li>
                        <li><a href="procurement.php">Procurement Application</a></li>
                        <li><a href="professional-fee.php">Professional Fee/Honorarium Application</a></li>
                        <li><a href="reconciliation-claim.php">Advance & Reconciliation/Claim Application</a></li>
                        <li><a href="allowances-wages.php">Allowance/Wages Application</a></li>
                        <li><a href="project-funding.php">Project Funding Assistance Application</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-clipboard"></i><span class="hide-menu">Honorarium Statement</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="appointment-letter.php">Appointment Letter</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-check"></i><span class="hide-menu">RA/RO Listing</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="ra-listing.php">RA/RO List</a></li>
                        <li><a href="ra-application.php">RA/RO Application</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-file"></i><span class="hide-menu">Consultant Feedback</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="appointment-letter.php">Appointment Letter</a></li>
                    </ul>
                </li>
		    </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>

<!-- Declaration Modal -->
<div class="modal fade" id="declarationModal" tabindex="-1" role="dialog" aria-labelledby="declarationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="declarationModalLabel">Declaration</h5>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y:auto;">
                <p>I hereby affirm that the information provided herein is true, accurate, and complete to the best of my knowledge.</p>
                <p>I agree to be bound by and shall adhere to all rules, regulations, and directives as may be prescribed by UiTM Technoventure.</p>
                <p>I acknowledge and accept that the registration of the project is subject to the applicable administrative fee as determined by UiTM Technoventure.</p>
                <p>I further understand and agree that upon registration in the system, a binding agreement shall be deemed to exist between myself and UiTM Technoventure.</p>
                <p>UiTM Technoventure reserves the absolute right to reject my application at its sole discretion and under any circumstances it deems appropriate.</p>
                <p><strong>The Project Leader shall be responsible for the following:</strong></p>
                <ul>
                    <li>Administering the financial operations of the project;</li>
                    <li>Supervising and managing the execution of project activities;</li>
                    <li>Ensuring the timely and successful delivery of the project to the client in accordance with the agreed terms.</li>
                </ul>
                <p><strong>Each team member shall be obligated to:</strong></p>
                <ul>
                    <li>Extend full cooperation and support to the Project Leader to ensure the successful and timely completion of the project in accordance with the stipulated timeframe.</li>
                </ul>
                <label>
                    I hereby acknowledge and agree to comply with all rules, regulations, and requirements established by UiTM Technoventure.
                </label>
                <!--<div class="form-check mt-3">
                    <input type="checkbox" class="form-check-input" id="agreeDeclaration">
                    <label class="form-check-label" for="agreeDeclaration">
                        <strong>(* System requirement – Compulsory to tick)</strong> I hereby acknowledge and agree to comply with all rules, regulations, and requirements established by UiTM Technoventure.
                    </label>
                </div>-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="proceedBtn">Proceed</button>
            </div>
        </div>
    </div>
</div>

<!-- REQUIRED JS LIBRARIES (Add these if not already in your layout file) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        let targetPage = '';
    
        $('#newConsultancyBtn').on('click', function (e) {
          e.preventDefault();
          targetPage = 'create-consultancy.php';
          $('#proceedBtn').prop('disabled', false).text('Proceed');
          $('#declarationModal').modal('show');
        });
    
        $('#newTrainingBtn').on('click', function (e) {
          e.preventDefault();
          targetPage = 'create-training.php';
          $('#proceedBtn').prop('disabled', false).text('Proceed');
          $('#declarationModal').modal('show');
        });
    
        $('#proceedBtn').on('click', function () {
          if (targetPage) {
            $(this).prop('disabled', true).text('Redirecting...');
            window.location.href = targetPage;
          }
        });
    });
</script>