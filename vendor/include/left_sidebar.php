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
                        <span class="hide-menu" style="display: inline-block;"><?php echo htmlspecialchars($userData['company_name']); ?></span>
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
                <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-file"></i><span class="hide-menu">Report</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="report.php">Report</a></li>
                    </ul>
                </li>
		    </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>