<?php 
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS
	
	IF(!isset($_SESSION['addusertab'])): $_SESSION['addusertab']="manageusers"; ENDIF;

		$err = array();
		if(isset($_POST) && array_key_exists('Register',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}
				
			// Validate all required Fields
	
			if (!isEmail($data['email'])) { $err[] = "ERROR - Invalid email address."; } // Validate Email
			if (!checkPwd($data['pword1'],$data['pword2'])) { $err[] = "ERROR - Invalid Password or mismatch. Enter 8 chars or more"; } // Validate Passwords
			
			//Validate Email
			$useremail = $data['email'];
			$username = $data['username'];
			
			$userduplicate = mysqli_query($link, "SELECT count(*) AS totaluser FROM ".DB_PREFIX."system_users WHERE username='$username'") or die(mysqli_error());
			list($totalu) = mysqli_fetch_row($userduplicate);
			
			$emailduplicate = mysqli_query($link, "SELECT count(*) AS total FROM ".DB_PREFIX."system_users WHERE email='$useremail'") or die(mysqli_error());
			list($total) = mysqli_fetch_row($emailduplicate);
			
			if ($totalu > 0){
				$err[] = "ERROR - User already exists. Please try again with different username.";
			}
			
			if ($total > 0){
				$err[] = "ERROR - Email already exists. Please try again with different email.";
			}

			if(empty($data['student_ID']) || strlen($data['student_ID']) < 2) { $err[] = "ERROR - Invalid User ID. Please enter atleast 2 or more characters";}
			if(empty($data['fname']) || strlen($data['fname']) < 2) { $err[] = "ERROR - Invalid first name. Please enter atleast 2 or more characters";}
			if(empty($data['mname']) || strlen($data['mname']) < 1) { $err[] = "ERROR - Invalid middle name. Please enter atleast 2 or more characters";}
			if(empty($data['lname']) || strlen($data['lname']) < 2) { $err[] = "ERROR - Invalid last name. Please enter atleast 2 or more characters";}
			if($data['question'] == "null") {$err[] = "ERROR - Please select security question";}
			if(empty($data['answer']) || strlen($data['answer']) < 2) { $err[] = "ERROR - Please enter atleast 2 or more characters to your answer";}
			
			// Validate Student ID
			$studentID = $data['student_ID'];
			$IDduplicate = mysqli_query($link, "SELECT count(*) AS totalID FROM ".DB_PREFIX."system_users WHERE ID_number='$studentID'") or die(mysqli_error());
			list($totalID) = mysqli_fetch_row($IDduplicate);
			
			if ($totalID > 0){
				$err[] = "ERROR - The User ID is already exists.";
			}
			
			// Validate Student Name
			$firstn = $data['fname']; $middlen = $data['mname']; $lastn = $data['lname'];
			$recordduplicate = mysqli_query($link, "SELECT count(*) AS totalrecords FROM ".DB_PREFIX."system_users WHERE first_name='$firstn' AND middle_name='$middlen' AND last_name='$lastn' AND birthdate='$dob'") or die(mysqli_error());
			list($total_records) = mysqli_fetch_row($recordduplicate);
			
			if ($total_records > 0){
				$err[] = "ERROR - The User already exists.";
			}
				
			//All required form has been validated
			if(empty($err)) {

				$passwrd = $data['pword'];
				$sha1pass = PwdHash($passwrd); // stores sha1 of password
	
				$sql_insert = "INSERT into ".DB_PREFIX."system_users (`ID_number`,`first_name`,`middle_name`,`last_name`,`suffix`,`email`,`username`,`password`,`user_level`,`approval`,`question`,`answer`)
				VALUES ('$data[student_ID]','$data[fname]','$data[mname]','$data[lname]','$data[suffix]','$useremail','$data[username]','$sha1pass','2','1','$data[question]','$data[answer]')";
				
				mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
				$user_id = mysqli_insert_id($link);
				
				// Remeber id
				$_SESSION['student_id'] = $user_id;
				
				$_SESSION['msg'] = "New User has been added to the record";

				$_SESSION['addusertab'] = "manageusers";
				header("location:usermanagement.php");
				exit();
			} // No Error
		} // Submitted	
		
		// Delete Permanently (Individual Record)
		if(isset($_POST) && array_key_exists('delete',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}

			mysqli_query($link, "DELETE FROM ".DB_PREFIX."system_users WHERE `user_ID`='".$data['recordid']."'") or die(mysqli_error());
			$msg = "User record has been deleted";
		}
	
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

				<div id="main">
					<section id="registered" class="main special">
						<header class="major">
							<h4>User Management</h4>
						</header>							

						<nav id="tab_view">						
							<a id="manageu" onclick='$("#adduser").hide();$("#studentlist").show("slow")' href="javascript:void(0)">Manage Users</a> <span style="color:#FF0000;font-weight:bold">|</span>  
							<a id="addu" onclick='$("#studentlist").hide();$("#adduser").show("slow")' href="javascript:void(0)">Add User</a>
						</nav>
						<br />
						<br />
						
						<p>
							<?php // Display message 
								if(!empty($_SESSION['msg'])) {
									echo "<div class=\"msg\">"; 
										echo $_SESSION['msg']; 
									echo "</div>";
									unset($_SESSION['msg']);
								} 
							?>
							</p>
						
						<?php
							$user_profile_list = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix, username, email, photo FROM ".DB_PREFIX."system_users WHERE `user_level`='2'");
							$user_list = mysqli_num_rows($user_profile_list);
							IF($user_list <= 0):
								echo "<h3>No Record Found</h3>";
							ELSE:
						?>
						<div class="table-wrapper" id="studentlist" style="display:<?php IF($_SESSION['addusertab']=="manageusers"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<table style="text-align:left;">
								<thead>
									<tr>
										<th>Name</th>
										<th>ID No.</th>
										<th>Username</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										WHILE($S_list = mysqli_fetch_array($user_profile_list)){
										$suffix = "";
										IF($S_list['suffix']!=""): $suffix = ", ".$S_list['suffix']; ENDIF;
									?>
									<tr>
										<td><?php echo $S_list['first_name']." ".$S_list['middle_name']." ".$S_list['last_name'].$suffix;?></td>
										<td><?php echo $S_list['ID_number']; ?></td>
										<td><?php echo $S_list['username']; ?></td>
										<td>
											<nav>						
												<a href="user_edit.php?refer=<?php echo $S_list['ID_number']; ?>"><span style="color:#00FF00;">EDIT</span></a> |
												<a href="javascript:void(0)" onclick="$('#confirm<?php echo $S_list['user_ID']; ?>').show('slow')"><span style="color:#FF0000;">DELETE</span></a>
											</nav>
											<div id="confirm<?php echo $S_list['user_ID']; ?>" style="display:none;padding:6px;">
												<span style="color:#FF0000;font-weight:bold">Delete this record permanently?</span><br>
												<span style="color:#0080A9;font-weight:bold">User ID: </span><span style="color:#0080A9;font-weight:bold"><?php echo $S_list['ID_number']; ?></span>
												<form name="deleterestore2" id="deleterestore2" action="usermanagement.php" method="post">
													<input type="text" style="display:none" name="recordid" value="<?php echo $S_list['user_ID']; ?>">
													<button style="width:60px;font-size:xx-small;float:left;margin-right:10px" type="submit" class="clean-gray" name="delete" id="delete" value="Delete">
													<span style="color:#FF0000">Yes</span></button>
													<button style="width:60px;font-size:xx-small;float:left" type="button" onclick="$('#confirm<?php echo $S_list['user_ID']; ?>').hide()">No</button>
												</form>
											</div>
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<?php ENDIF; ?> 		
						
						
						<div class="table-wrapper" id="adduser" style="display:<?php IF($_SESSION['addusertab']=="addusers"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<p><?php if(!empty($err)) { echo "<div class=\"msg\">"; foreach ($err as $e) { echo "$e <br>"; } echo "</div>"; }  // Display error message  ?></p>
							<form action="usermanagement.php" method="post" name="RegForm" id="RegForm" >
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
									<select name="gender" id="gender" style="width:130px">
										<option value="null">-Gender-</option>
										<option value="1">Male</option>
										<option value="0">Female</option>
									</select>
								</div>
								<br />
								<br />
								
								<div class="6u 12u$">
									<input type="text" name="username" id="username" placeholder="User Name" style="width:300px"/>
								</div>
								<br />
								
								<div class="6u 12u$">
									<input type="password" name="pword1" id="pword1" placeholder="Password" style="width:300px"/>
								</div>
								<br />
								
								<div class="6u 12u$">
									<input type="password" name="pword2" id="pword2" placeholder="Confirm Password" style="width:300px"/>
								</div>
								<br />
							
								<div class="6u 12u$">
									<input type="text" name="email" id="email" placeholder="email" />
								</div>
								<br />
								
								<div class="6u 12u$">
									<select name="question" id="question">
										<option value="null">--Select Security Question--</option>
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
								
								<div class="6u 12u$">
									<input type="text" name="answer" id="answer" placeholder="Answer" />
								</div>
								<br />

								<div class="12u$">
									<ul class="actions">
										<li><input type="submit" value="Register" class="special" name="Register" id="Register"/></li>
									</ul>
								</div>
							</form>
						</div>
					</section>
				</div>
				<!-- Footer -->
					<footer id="footer">
						<p class="copyright">&copy; Attendance, Event and Store Inventory System : <a href="your link here">your link here</a>.</p>
					</footer>
			</div>
			<?php include('includes/footertag'); ?>
			<script type="text/javascript">
				$('#tab_view a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'manageu') ? 'manageusers' : 'addusers';
					$.post('library/fx.behaviour.php', { userdisplay: view });
				});
			</script>
				
	</body>
</html>