<?php 
	include 'settings/connect.php';
	if(session_id() == '') { page_protect(); } // Session start with redirection to login section
	
	if (Admin()) {
	
		$err = array();
		if(isset($_POST) && array_key_exists('Register',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}
			
			//Get DOB
			if ($data['mdob'] < 10) { $monthdata = '0'.$data['mdob']; }else{ $monthdata = $data['mdob']; }
			$dob = ($data['ydob'].'-'.$monthdata.'-'.$data['ddob']);
				
			// Validate all required Fields
	
			if (!isEmail($data['email'])) { $err[] = "ERROR - Invalid email address."; } // Validate Email
			
			//Validate Email
			$useremail = $data['email'];
			$emailduplicate = mysqli_query($link, "SELECT count(*) AS total FROM ".DB_PREFIX."system_users WHERE email='$useremail'") or die(mysqli_error());
			list($total) = mysqli_fetch_row($emailduplicate);
					
			if ($total > 0){
				$err[] = "ERROR - Email already exists. Please try again with different email.";
			}

			if(empty($data['student_ID']) || strlen($data['student_ID']) < 2) { $err[] = "ERROR - Invalid student ID. Please enter atleast 2 or more characters";}
			if(empty($data['fname']) || strlen($data['fname']) < 2) { $err[] = "ERROR - Invalid first name. Please enter atleast 2 or more characters";}
			if(empty($data['mname']) || strlen($data['mname']) < 1) { $err[] = "ERROR - Invalid middle name. Please enter atleast 2 or more characters";}
			if(empty($data['lname']) || strlen($data['lname']) < 2) { $err[] = "ERROR - Invalid last name. Please enter atleast 2 or more characters";}
			if($data['gender'] == "null") {$err[] = "ERROR - Please select gender";}
			if(!isset($data['mdob']) || $data['mdob'] == "") {$err[] = "ERROR - Please select Birth Month";}
			if(!isset($data['ddob']) || $data['ddob'] == "") {$err[] = "ERROR - Please select Birth Date";}
			if(!isset($data['ydob']) || $data['ydob'] == "") {$err[] = "ERROR - Please select Year of Birth";}
			
			// Validate Student ID
			$studentID = $data['student_ID'];
			$IDduplicate = mysqli_query($link, "SELECT count(*) AS totalID FROM ".DB_PREFIX."system_users WHERE ID_number='$studentID'") or die(mysqli_error());
			list($totalID) = mysqli_fetch_row($IDduplicate);
			
			if ($totalID > 0){
				$err[] = "ERROR - The student ID is already exists.";
			}
			
			// Validate Student Name
			$firstn = $data['fname']; $middlen = $data['mname']; $lastn = $data['lname'];
			$recordduplicate = mysqli_query($link, "SELECT count(*) AS totalrecords FROM ".DB_PREFIX."system_users WHERE first_name='$firstn' AND middle_name='$middlen' AND last_name='$lastn' AND birthdate='$dob'") or die(mysqli_error());
			list($total_records) = mysqli_fetch_row($recordduplicate);
			
			if ($total_records > 0){
				$err[] = "ERROR - The student already exists.";
			}
				
			//All required form has been validated
			if(empty($err)) {

				$passwrd = $data['student_ID'];
				$sha1pass = PwdHash($passwrd); // stores sha1 of password
	
				$sql_insert = "INSERT into ".DB_PREFIX."system_users (`ID_number`,`first_name`,`middle_name`,`last_name`,`suffix`,`email`,`scholarship`,`college`,`birthdate`,`gender`,`password`,`user_level`,`approval`)
				VALUES ('$data[student_ID]','$data[fname]','$data[mname]','$data[lname]','$data[suffix]','$useremail','$data[scholarship]','$data[college]','$dob','$data[gender]','$sha1pass','1','1')";
				
				mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
				$user_id = mysqli_insert_id($link);
				
				// Remeber id
				$_SESSION['student_id'] = $user_id;
				
				
				// Send Email to new registered user/client
				$message = "
					Hello \n
					Thank you for registering with us. Here are your login details...\n
	
						User ID: $user_id \n
						Login: $username \n
						Email: $useremail \n 
						Passwd: $data[student_ID] \n
		
						$a_link
	
					Thank You
	
					Administrator
					$host_upper
					______________________________________________________
					THIS IS AN AUTOMATED RESPONSE. 
					***DO NOT RESPOND TO THIS EMAIL****
				";
	
				mail($usr_email, "Login Details", $message,
				"From: \"Member Registration\" <auto-reply@$host>\r\n" .
				"X-Mailer: PHP/" . phpversion());
				
				header("Location: registered.php");
				exit();
			} // No Error
		} // Submitted	
		
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>School Record System</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="assets/css/main.css" />
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
	</head>
	<body>

		<!-- Wrapper -->
			<div id="wrapper">
				<!-- Navigations -->
					<nav id="nav">
						<ul>
							<li><a href="index.php" class="active" onclick='$("#events").hide();$("#registers").show("slow")'>Home</a></li>
							<li><a href="index.php#events" onclick='$("#registers").hide();$("#events").show("slow")'>Events</a></li>
							<?php if(isset($_SESSION['user_id'])): ?> 
							<li><a href="logout.php">Logout</a></li>
							<?php if(Admin()): ?>
							<li><a href="backend/index.php">Administration</a></li>
							<?php endif; endif; ?>
						</ul>
					</nav>

				<!-- Main Body -->
					<div id="main">
						<!-- Student Registration Section -->
						<section id="registers" class="main special">
							<header class="major">
								<h2>New Student Registration</h2>
							</header>
							
							<p><?php if(!empty($err)) { echo "<div class=\"msg\">"; foreach ($err as $e) { echo "$e <br>"; } echo "</div>"; }  // Display error message  ?></p>
							
							<br>
							
							<form action="register.php" method="post" name="RegForm" id="RegForm" >
								<div class="6u 12u$">
									<input type="text" name="student_ID" id="student_ID" placeholder="Student ID" style="width:300px"/>
								</div>
								<br />

								<input name="fname" type="text" id="fname" value="" PLACEHOLDER="First Name" style="width:250px;margin-right:10px;float:left">
								<input name="mname" type="text" id="mname" value="" PLACEHOLDER="Middle Name" style="width:250px;margin-right:10px;float:left">
								<input name="lname" type="text" id="lname" value="" PLACEHOLDER="Last Name" style="width:250px;margin-right:10px;float:left">
								<div style="float:left">
									<select name="suffix" id="suffix" >
										<option value="">ex. Jr.</option>
										<option value="Sr.">Sr.</option>
										<option value="Jr.">Jr.</option>
										<option value="III">III</option>
									</select>
								</div>
								<br />
								<br />
								<br />
								
								<div class="6u 12u$">
									
									<div style="text-align:left;float:left;color:#003399">
										<select id="mdob" name="mdob" style="width:100px;float:left;margin-right:10px">
											<option value="" selected="selected">Month</option>										
											<?php for( $m=1 ; $m<=12 ; $m++): echo '<option value="'.$m.'">'.date("F", mktime(0, 0, 0, $m, 10)).'</option>'; endfor; ?>
										</select>
										<select id="ddob" name="ddob" style="width:90px;float:left;margin-right:10px">
											<option value="" selected="selected">Date</option>
											<?php for( $i=1 ; $i<32 ; $i++): ?> 
											<option value="<?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?>"><?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?></option>
											<?php endfor;?>
										</select>
										<select id="ydob" name="ydob" style="width:90px;float:left;margin-right:10px">
											<option value="" selected="selected">Year</option>
											<?php $curYear = date('Y'); $oldYear = $curYear - 75; for( $i=$oldYear ; $i<=$curYear ; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
										</select>
										
										<select name="gender" id="gender" style="width:130px">
											<option value="null">-Gender-</option>
											<option value="1">Male</option>
											<option value="0">Female</option>
										</select>
									</div>
									
								</div>
								<br />
								<br />
							
								<div class="6u 12u$">
									<input type="text" name="email" id="email" placeholder="email" />
								</div>
								<br />

								<div class="6u 12u$">
									<select name="scholarship" id="scholarship">
										<option value="null">-Scholarship-</option>
										<?php
										$scholarship_cat = mysqli_query($link, "SELECT ID, name FROM ".DB_PREFIX."scholarship_category");
										$displayscholarship_cat = mysqli_num_rows($scholarship_cat);
										IF($displayscholarship_cat <= 0):
											echo "<h3>No Record Found!</h3>";
										ELSE:
											WHILE($scholarship = mysqli_fetch_array($scholarship_cat)): ?>
											<option value="<?php echo $scholarship['name']; ?>"><?php echo $scholarship['name']; ?></option>
											<?php ENDWHILE;
										ENDIF;
										?>
									</select>
								</div>

								<br />

								<div class="6u 12u$">
									<select name="college" id="college">
										<option value="null">-College-</option>
										<?php
										$college_cat = mysqli_query($link, "SELECT ID, name FROM ".DB_PREFIX."colleges_category");
										$display_cat = mysqli_num_rows($college_cat);
										IF($display_cat <= 0):
											echo "<h3>No Record Found!</h3>";
										ELSE:
											WHILE($college = mysqli_fetch_array($college_cat)): ?>
											<option value="<?php echo $college['ID']; ?>"><?php echo $college['name']; ?></option>
											<?php ENDWHILE;
										ENDIF;
										?>
									</select>
								</div>
								<br />

								<div class="12u$">
									<ul class="actions">
										<li><button type="submit" value="Register" class="special" name="Register" id="Register">Register</button></li>
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