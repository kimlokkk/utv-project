<?php
    session_start(); // Start the session
    include '../../db_connect/db_connect.php';
    include 'auth_check.php';
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
                            <span class="hide-menu" style="display: inline-block;"><?php echo htmlspecialchars($userData['name']); ?></span>
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
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-plus"></i><span class="hide-menu">Project</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="consultancy.php">Consultancy Project</a></li>
                        <li><a href="training.php">Training Project</a></li>
                        <!--<li><a href="create-consultancy.php">New Consultancy Project</a></li>
                        <li><a href="create-training.php">New Training Project</a></li>-->
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-money"></i><span class="hide-menu">Financial Request</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="invoice-application.php">Invoice Application</a></li>
                        <li><a href="procurement-application.php">Procurement Application</a></li>
                        <li><a href="professional-fee.php">Professional Fee/Honorarium Application</a></li>
                        <li><a href="reconciliation-claim.php">Advance & Reconciliation/Claim Application</a></li>
                        <li><a href="allowances-wages.php">Allowance/Wages Application</a></li>
                        <li><a href="project-funding.php">Project Funding Assistance Application</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-briefcase"></i><span class="hide-menu">Honorarium Statement</span></a>
                    <ul aria-expanded="false" class="collapse">
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-check"></i><span class="hide-menu">Research Assistant</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="research-status.php">RA/RO Registration</a></li>
                        <li><a href="ra-application.php">RA/RO Application</a></li>
                        <li><a href="appointment-letter.php">Appointment Letter</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-book"></i><span class="hide-menu">Ledger</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="project-ledger-list.php">Project Ledger</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-archive"></i><span class="hide-menu">Payment Listing</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="invoice-listing.php">Invoice Listing</a></li>
                        <li><a href="payment-listing.php">Payment Listing</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-id-badge"></i><span class="hide-menu">Vendor Registration</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="vendor.php">Vendor Status</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-clipboard"></i><span class="hide-menu">Report</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="report.php">Report</a></li>
                    </ul>
                </li>
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-file"></i><span class="hide-menu">Consultant Feedback Form</span></a>
                    <ul aria-expanded="false" class="collapse">
                    </ul>
                </li>
		    </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>