<?php 
	include 'settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS
	
	foreach($_POST as $key => $value) { $data[$key] = filter($value); }

	$mess = array();
	// Recovery
	IF(isset($_POST) && array_key_exists('Recover',$_POST)):

		$username = $data['username'];
		$question_ID = $data['question'];
		$answers = $data['answer'];

		IF($username == ""): $mess[] = "ERROR - Invalid username. Please enter atleast 2 or more characters"; ENDIF;
		IF($question_ID == 0): $mess[] = "ERROR - Please select question"; ENDIF;
		IF($answers == ""): $mess[] = "ERROR - Please enter your answer"; ENDIF;

		// Can use email, lastname or username to login
		IF (strpos($username,'@') === false):
			$user_cond = "(username='$username' OR last_name='$username')";
		ELSE:
			$user_cond = "email='$username'";
		ENDIF;

		$user_cond2 = "AND question='$question_ID'";

		$users = mysqli_query($link, "SELECT `last_name`,`username`,`email` FROM ".DB_PREFIX."system_users WHERE $user_cond") or die (mysqli_error()); 
		$num_user = mysqli_num_rows($users);
		IF ( $num_user <= 0 ):
			$mess[] = "ERROR - No such user exist"; 
		ELSE:

			$result = mysqli_query($link, "SELECT `user_ID`,`username`,`last_name`,`email`,`question`,`answer`,`user_level`,`approval` FROM ".DB_PREFIX."system_users WHERE $user_cond $user_cond2") or die (mysqli_error()); 
			$num = mysqli_num_rows($result);

			// Match row found with more than 1 results  - the user is authenticated.
			IF ( $num > 0 ):
				list($id,$user,$last_name,$email,$question,$answer,$userlevel,$approved) = mysqli_fetch_row($result);	
				IF(!$approved):
					$mess[] = "Account not activated. Please contact the administrator".$user;
				ELSE:
					IF($answer != $answers): $mess[] = "Your answer is incorrect!"; 
					ELSEIF($question_ID == $question && $answers == $answer && ($username == $user OR $username == $last_name OR $username == $email)):
						$mess[] = "Your account has been verified, you can now reset your password";
						$_SESSION['resetID'] = $id;
						
							$_SESSION['resetuname'] = $username;

					ENDIF;
				ENDIF;
			ENDIF; 
		ENDIF;
		$_SESSION['msg'] = $mess;
	ENDIF;
	
	
	// Reset Password
	IF(isset($_POST) && array_key_exists('UpdateP',$_POST)):
		
		if (!checkPwd($data['npass1'],$data['npass2'])) { $mess[] = "ERROR - Invalid Password or mismatch. Enter 8 chars or more"; } // Validate Passwords
		
		//All required form has been validated
			if(empty($mess)) {

				$passwrd = $data['npass2'];
				$user_name = $data['username'];
				$sha1pass = PwdHash($passwrd); // stores sha1 of password

				// Can use email, lastname or username to login
				if (strpos($user_name,'@') === false) {
					$user_cond = "(username='$user_name' OR last_name='$user_name')"; 
				}else{
					$user_cond = "email='$user_name'"; 
				}
				
				mysqli_query($link, "UPDATE ".DB_PREFIX."system_users SET `password` = '$sha1pass' WHERE ".$user_cond." AND user_ID='".$_SESSION['resetID']."'") or die(mysqli_error());

				$result = mysqli_query($link, "SELECT `user_ID`,`password`,`first_name`,`username`,`email`,`middle_name`,`last_name`,`user_level`,`approval` FROM ".DB_PREFIX."system_users WHERE $user_cond") or die (mysqli_error()); 
				$num = mysqli_num_rows($result);

				// Match row found with more than 1 results  - the user is authenticated. 
				if ( $num > 0 ) { 
					list($id,$pwd,$first_name,$username,$email,$middle_name,$last_name,$userlevel,$approved) = mysqli_fetch_row($result);

					//check against salt
					if ($pwd === PwdHash($passwrd,substr($pwd,0,9))) {

						// this sets session and logs user in
						session_regenerate_id (true); //prevent against session fixation attacks.
							
						$full_name = $first_name." ".$middle_name." ".$last_name;

					   // this sets variables in the session
						$_SESSION['user_id']= $id;
						$_SESSION['full_name'] = $full_name;
						$_SESSION['user_level'] = $userlevel;
						$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
							
						//update the timestamp and key for cookie
						$stamp = time();
						$ckey = GenKey();
						mysqli_query($link, "update ".DB_PREFIX."system_users set `ctime`='$stamp', `ckey` = '$ckey' where user_ID='$id'") or die(mysqli_error());
						
						//set a cookie 
					   if(isset($_POST['remember'])){
							setcookie("user_id", $_SESSION['user_id'], time()+60*60*24*COOKIE_TIME_OUT, "/");
							setcookie("user_key", sha1($ckey), time()+60*60*24*COOKIE_TIME_OUT, "/");
							setcookie("user_name",$_SESSION['user_name'], time()+60*60*24*COOKIE_TIME_OUT, "/");
						}
						$mess[] = "Your Password has been reset"; 
						unset($_SESSION['resetID']);
						unset($_SESSION['resetuname']);

					}
				}else{ $mess[] = "Error - Invalid login. No such user exists"; }

			} // No Error
		$_SESSION['msg'] = $mess;
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
								<h2>Account Recovery</h2>
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
							
								<?php IF (!isset($_SESSION['resetID']) && !isset($_SESSION['resetuname']) && !isset($_SESSION['user_id'])): ?>

								<form action="precovery.php" method="post" name="logForm" id="logForm" >
									<div class="6u 12u$" style="margin:0 auto">
										<input type="text" name="username" id="username" placeholder="User Name / Last Name / e-mail" />
									</div>
									<br />
									
									<div class="6u 12u$" style="margin:0 auto">
									<select name="question" id="question" style="float:left;margin-right:5px">
										<option value="">-- Select Security Question--</option>
										<?php
										$question_cat = mysqli_query($link, "SELECT ID, question FROM ".DB_PREFIX."security_question");
										$count_item = mysqli_num_rows($question_cat);
										IF($count_item <= 0):
											echo "<h3>No Question Found!</h3>";
										ELSE:
											WHILE($question = mysqli_fetch_array($question_cat)): ?>
											<option value="<?php echo $question['ID']; ?>"><?php echo $question['question']; ?></option>
											<?php ENDWHILE;
										ENDIF;
										?>
									</select>
									</div>
									<br />
									<br />

									<div class="6u 12u$" style="margin:0 auto">
										<input type="text" name="answer" id="answer" placeholder="Your Answer" />
									</div>
									<br />

									<div class="12u$">
										<ul class="actions">
											<li><input type="submit" value="Recover" class="special" name="Recover" id="Recover"/></li>
										</ul>
									</div>
								</form>
								
								<?php ELSEIF (isset($_SESSION['resetID']) && isset($_SESSION['resetuname'])): ?>
								
								<form action="precovery.php" method="post" name="ResetForm" id="ResetForm" >
									<div class="6u 12u$" style="margin:0 auto">
										<input type="text" value="<?php echo $_SESSION['resetuname']; ?>" readonly name="username" id="username"/>
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
											<li><input type="submit" value="Update Password" class="special" name="UpdateP" id="UpdateP"/></li>
										</ul>
									</div>
								</form>
								
								<?php ENDIF; ?>
								
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