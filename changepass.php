<?php 
	include 'settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS
	
	foreach($_POST as $key => $value) { $data[$key] = filter($value); }

	$err = array();
	// Change Password
	IF(isset($_POST) && array_key_exists('ChangeP',$_POST)):
		
		$oldpasswrd = $data['oldpass'];
		if (!checkPwd($data['npass1'],$data['npass2'])) { $err[] = "ERROR - Invalid Password or mismatch. Enter 8 chars or more"; } // Validate Passwords

		$result = mysqli_query($link, "SELECT `password` FROM ".DB_PREFIX."system_users WHERE user_ID='".$_SESSION['user_id']."'") or die (mysqli_error()); 
		$num = mysqli_num_rows($result);

		// Match row found with more than 1 results  - the user is authenticated. 
		if ( $num > 0 ) {
			list($pwd) = mysqli_fetch_row($result);	

			//check against salt
			if ($pwd === PwdHash($oldpasswrd,substr($pwd,0,9))) { 

				//All required form has been validated
				if(empty($err)) {

					$passwrd = $data['npass2'];
					$sha1pass = PwdHash($passwrd); // stores sha1 of password
					mysqli_query($link, "UPDATE ".DB_PREFIX."system_users SET `password` = '$sha1pass' WHERE user_ID='".$_SESSION['user_id']."'") or die(mysqli_error());

					$err[] = "Password has been changed.";

				} // No Error
			}else{
				$err[] = "Invalid old password.";
			}
		}
		$_SESSION['msg'] = $err;
	ENDIF;
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include("includes/headtag"); ?>
	</head>
	<body>

		<!-- Wrapper -->
			<div id="wrapper">

				<!-- Header -->
					<header id="header" class="alt">
						<h1>School Record System</h1>
						<p>Attendance, Event and Store Inventory System<br />
					</header>

				<!-- Navigations -->
					<nav id="nav">
						<ul>
							<li><a href="index.php" class="active">Home</a></li>
							<li><a href="events.php">Events</a></li>
							<?php if(isset($_SESSION['user_id'])): ?> 
							<li><a href="logout.php">Logout</a></li>
							<?php if(Admin()): ?>
							<li><a href="backend/index.php">Administration</a></li>
							<?php endif; endif; ?>
						</ul>
					</nav>

				<!-- Main Body -->
					<div id="main">
						<!-- Student Profile Section -->
						<section id="profile" class="main special">
							<header class="major">
								<h2>Change Password</h2>
							</header>

								<?php // Display message 
									if(!empty($_SESSION['msg'])) { 
										echo "<div class=\"msg\">"; 
											foreach ($_SESSION['msg'] as $e) { 
												echo "$e <br>"; 
											} 
										echo "</div>";
										unset($_SESSION['msg']); 
									}
								?>

								<form action="changepass.php" method="post" name="ResetForm" id="ResetForm" >
									<div class="6u 12u$" style="margin:0 auto">
										<input type="password" name="oldpass" id="oldpass" placeholder="Old Password" />
									</div>
									<br />
									
									<div class="6u 12u$" style="margin:0 auto">
										<input type="password" name="npass1" id="npass1" placeholder="New Password" />
									</div>
									<br />

									<div class="6u 12u$" style="margin:0 auto">
										<input type="password" name="npass2" id="npass2" placeholder="Confirm Password" />
									</div>
									<br />

									<div class="12u$">
										<ul class="actions">
											<li><input type="submit" value="Change Password" class="special" name="ChangeP" id="UpdateP"/></li>
										</ul>
									</div>
								</form>

						</section>
					</div>

				<!-- Footer -->
					<footer id="footer">
						<p class="copyright">&copy; Attendance, Event and Store Inventory System : <a href="your link here">your link here</a>.</p>
					</footer>
			</div>
		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrollex.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>

	</body>
</html>