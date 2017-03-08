<?php 
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS
	
	$err = array();
		if(isset($_POST) && array_key_exists('Update',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}
			
			$studentID = $data['student_ID'];
			$recordID = $data['record_ID'];
				
			// Validate all required Fields
	
			if (!isEmail($data['email'])) { $err[] = "ERROR - Invalid email address."; } // Validate Email
			
			//Validate Email
			$useremail = $data['email'];
			$emailduplicate = mysqli_query($link, "SELECT count(*) AS total FROM ".DB_PREFIX."system_users WHERE email='$useremail' AND user_ID!='".$recordID."'") or die(mysqli_error());
			list($total) = mysqli_fetch_row($emailduplicate);
					
			if ($total > 0){
				$err[] = "ERROR - Email already exists. Please try again with different email.";
			}

			if(empty($data['student_ID']) || strlen($data['student_ID']) < 2) { $err[] = "ERROR - Invalid User ID. Please enter atleast 2 or more characters";}
			if(empty($data['fname']) || strlen($data['fname']) < 2) { $err[] = "ERROR - Invalid first name. Please enter atleast 2 or more characters";}
			if(empty($data['mname']) || strlen($data['mname']) < 1) { $err[] = "ERROR - Invalid middle name. Please enter atleast 2 or more characters";}
			if(empty($data['lname']) || strlen($data['lname']) < 2) { $err[] = "ERROR - Invalid last name. Please enter atleast 2 or more characters";}
			if($data['gender'] == "null") {$err[] = "ERROR - Please select gender";}
			if($data['question'] == "null") {$err[] = "ERROR - Please select security question";}
			if(empty($data['answer']) || strlen($data['answer']) < 2) { $err[] = "ERROR - Please enter atleast 2 or more characters to your answer";}

			// Validate Student ID
			$IDduplicate = mysqli_query($link, "SELECT count(*) AS totalID FROM ".DB_PREFIX."system_users WHERE ID_number='$studentID' AND user_ID!='$recordID'") or die(mysqli_error());
			list($totalID) = mysqli_fetch_row($IDduplicate);

			if ($totalID > 0){
				$err[] = "ERROR - Can't update ID, used by another user.";
			}

			// Validate Student Name
			$firstn = $data['fname']; $middlen = $data['mname']; $lastn = $data['lname'];
			$recordduplicate = mysqli_query($link, "SELECT count(*) AS totalrecords FROM ".DB_PREFIX."system_users WHERE first_name='$firstn' AND middle_name='$middlen' AND last_name='$lastn' AND birthdate='$dob' AND user_ID!='$recordID'") or die(mysqli_error());
			list($total_records) = mysqli_fetch_row($recordduplicate);
			
			if ($total_records > 0){
				$err[] = "ERROR - The user already exists.";
			}

			//All required form has been validated
			if(empty($err)) {

				$sql_insert = "UPDATE ".DB_PREFIX."system_users SET `ID_number`='$studentID', `first_name`='$data[fname]', `middle_name` = '$data[mname]', `last_name` = '$data[lname]', `suffix` = '$data[suffix]', `email` = '$useremail', `gender` = '$data[gender]', `question` = '$data[question]', `answer` = '$data[answer]' WHERE `user_ID` = $recordID";
				mysqli_query($link, $sql_insert) or die("Update Failed:" . mysqli_error());

				$_SESSION['msg'] = "Record has been updated.";
				header("Location: user_edit.php?refer=".$studentID);
				exit();
			} // No Error
			else{
				$_SESSION['errors'] = $err;
				header("Location: user_edit.php?refer=".$_SESSION['oldID']);
				exit();
			}
		} // Submitted
	foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
	$studentID = $data['refer'];
	$_SESSION['oldID'] = $data['refer'];
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include('includes/headtag'); ?>
	</head>
	<body>

		<!-- Wrapper -->
			<div id="wrapper">

				<!-- Header -->
				<?php include('includes/header'); ?>

				<!-- Main Body -->
					<div id="main">
						<!-- Student Profile Section -->
						<section id="profile" class="main special">
							<header class="major">
								<h2>User Profile</h2>
							</header>
								<p>
									<?php // Display error message 
										if(!empty($_SESSION['errors'])) { 
											echo "<div class=\"msg\">"; 
												foreach ($_SESSION['errors'] as $e) { 
													echo "$e <br>"; 
												} 
											echo "</div>";
											unset($_SESSION['errors']); 
										}  
									?>
								</p>
								<p>
									<?php // Display message 
										if(!empty($_SESSION['msg'])) {
											echo "<div class=\"msg\">"; 
												echo $_SESSION['msg']; 
											echo "</div>"; 
										} 
										unset($_SESSION['msg']);  
									?>
								</p>
								<br />
							<?php
							IF(!isset($_SESSION['user_id'])): // Logged out ?>
								<h3>Please login in order to view your profile</h3>
							<?php include('includes/login.php');
								ELSE:
										$userID = $_SESSION['user_id'];
										$user_profile = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix, username, gender, email, question, answer FROM ".DB_PREFIX."system_users WHERE `ID_number`='".$studentID."' AND `user_level`='2'");
										$userprofile = mysqli_num_rows($user_profile);
										IF($userprofile <= 0):
											echo "<h3>No Record Found</h3>";
										ELSE:
										$profile = mysqli_fetch_array($user_profile);
										
										$question_ID = $profile['question'];
										$answers = $profile['answer'];
										$question = mysqli_fetch_array(mysqli_query($link, "SELECT question FROM ".DB_PREFIX."security_question WHERE `ID`='".$question_ID."'"));
								?>
									<div>
										<div class="6u 12u$(medium)" style="text-align:left;float:left;width: 78%;">
											<h3>Basic Details</h3>
											<dl class="alt">
											<form action="user_edit.php" method="post" name="RegForm" id="RegForm" >
												<input type="text" style="display:none" name="record_ID" id="record_ID" value="<?php echo $profile['user_ID']; ?>"/>
												<table>
													<tr>
														<td>ID No.</td>
														<td><input type="text" name="student_ID" id="student_ID" placeholder="Student ID" style="float:left;width:178px;height:30px" value="<?php echo $profile['ID_number']; ?>"/></td>
													</tr>
													<tr>
														<td>Name</td>
														<td> 
															<input type="text" name="fname" id="fname" placeholder="First Name" style="float:left;width:178px;height:30px;margin-right:5px;" value="<?php echo $profile['first_name']; ?>"/>
															<input type="text" name="mname" id="mname" placeholder="Middle Name" style="float:left;width:178px;height:30px;margin-right:5px;" value="<?php echo $profile['middle_name']; ?>"/>
															<input type="text" name="lname" id="lname" placeholder="Last Name" style="float:left;width:178px;height:30px;margin-right:5px;" value="<?php echo $profile['last_name']; ?>"/>
															<select name="suffix" id="suffix" style="float:left;width:80px;height:29px" >
																<option value="" <?php IF ($profile['suffix'] == "") : echo "selected"; ENDIF; ?>>ex. Jr.</option>
																<option value="Sr." <?php IF ($profile['suffix'] == "Sr") : echo "selected"; ENDIF; ?>>Sr.</option>
																<option value="Jr." <?php IF ($profile['suffix'] == "Jr") : echo "selected"; ENDIF; ?>>Jr.</option>
																<option value="III" <?php IF ($profile['suffix'] == "III") : echo "selected"; ENDIF; ?>>III</option>
															</select>
														</td>
													</tr>
													<tr>
														<td>Gender</td>
														<td>
															<select name="gender" id="gender" style="width:100px;float:left;margin-right:5px;height:30px">
																<option value="null">-Gender-</option>
																<option value="1" <?php IF($profile['gender']==1): echo "selected"; ENDIF; ?>>Male</option>
																<option value="0" <?php IF($profile['gender']==0): echo "selected"; ENDIF; ?>>Female</option>
															</select>
														</td>
													</tr>
													<tr>
														<td>Email</td>
														<td><input type="text" name="email" id="email" style="width:300px;float:left;margin-right:5px;height:30px" value="<?php echo $profile['email']; ?>" placeholder="email" /></td>
													</tr>
													<tr>
														<td>Security Question</td>
														<td>
															<select name="question" id="question" style="float:left;margin-right:5px;height:30px">
																<option value="<?php echo $question_ID; ?>"><?php echo $question['question']; ?></option>
																<?php
																$question_cat = mysqli_query($link, "SELECT ID, question FROM ".DB_PREFIX."security_question");
																$question_item = mysqli_num_rows($question_cat);
																IF($question_item <= 0):
																	echo "<h3>No Record Found!</h3>";
																ELSE:
																	WHILE($question1 = mysqli_fetch_array($question_cat)): ?>
																	<option value="<?php echo $question1['ID']; ?>"><?php echo $question1['question']; ?></option>
																	<?php ENDWHILE;
																ENDIF;
																?>
															</select>
														</td>
													</tr>
													
													<tr>
														<td>Answer</td>
														<td><input type="text" name="answer" id="answer" placeholder="Answer" style="float:left;width:178px;height:30px" value="<?php echo $profile['answer']; ?>"/></td>
													</tr>
													
												</table>
												<button type="submit" value="Update" class="special" name="Update" id="Update">Update</button>
											</form>
											<a href="index.php"><button type="button">Back</button></a>
										</div>
										<div class="6u 12u$(medium)" style="text-align:left;float:left;width: 22%;">									
											<h3>Barcode</h3>
											<?php include '../includes/code_128.php'; ?>
											<div class="barcodepane"><?php echo '<img src="data:image/png;base64,' . base64_encode($output_img) . '" height="90" width="190"/>'; ?></div>
											<span style="text-align:center;line-height:0.5em">
												<h2 style="line-height:0.5em"><?php echo $studentID; ?></h2>
												<h6 style="line-height:0em">This barcode is auto generated.</h6>
											</span>
										</div>
									</div>
									<div style="clear:both"></div>
								<?php ENDIF; 
								ENDIF; ?>
						</section>
					</div>

				<!-- Footer -->
					<footer id="footer">
						<p class="copyright">&copy; Attendance, Event and Store Inventory System : <a href="your link here">your link here</a>.</p>
					</footer>
			</div>
			<?php include('includes/footertag'); ?>

	</body>
</html>