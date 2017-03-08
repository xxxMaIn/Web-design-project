<?php 
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS

		IF(!isset($_SESSION['addstudenttab'])): $_SESSION['addstudenttab'] = "managestudent"; ENDIF;
		foreach($_POST as $key => $value) {	$data[$key] = filter($value); }
	
		$err = array();
		$s_err = array();
		if(isset($_POST) && array_key_exists('Register',$_POST)){
			
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
			if($data['question'] == "null") {$err[] = "ERROR - Please select security question";}
			if(empty($data['answer']) || strlen($data['answer']) < 2) { $err[] = "ERROR - Please enter atleast 2 or more characters to your answer";}
			
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
	
				$sql_insert = "INSERT into ".DB_PREFIX."system_users (`ID_number`,`first_name`,`middle_name`,`last_name`,`suffix`,`email`,`scholarship`,`college`,`birthdate`,`gender`,`password`,`user_level`,`approval`,`question`,`answer`)
				VALUES ('$data[student_ID]','$data[fname]','$data[mname]','$data[lname]','$data[suffix]','$useremail','$data[scholarship]','$data[college]','$dob','$data[gender]','$sha1pass','1','1','$data[question]','$data[answer]')";
				
				mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
				$user_id = mysqli_insert_id($link);
				
				// Remeber id
				$_SESSION['student_id'] = $user_id;
				
				$_SESSION['msg'] = "New student has been added to the record";
				
				// Send Email to new registered user/client	
				$message = "
					Hello \n
					Thank you for registering with us. Here are your login details...\n
	
						User ID: $user_id \n
						Email: $useremail \n 
						Passwd: $data[student_ID] \n
		
	
					Thank You
	
					______________________________________________________
					THIS IS AN AUTOMATED RESPONSE. 
					***DO NOT RESPOND TO THIS EMAIL****
				";
	
				mail($useremail, "Login Details", $message,
				"From: \"Member Registration\" <auto-reply@$host>\r\n" .
				"X-Mailer: PHP/" . phpversion());
				
				$_SESSION['addstudenttab'] = "managestudent";
				header("location:index.php");
				exit();
			} // No Error
		} // Submitted	
		
		// Delete Permanently (Individual Record)
		if(isset($_POST) && array_key_exists('delete',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}

			mysqli_query($link, "DELETE FROM ".DB_PREFIX."system_users WHERE `user_ID`='".$data['recordid']."'") or die(mysqli_error());
			mysqli_query($link, "DELETE FROM ".DB_PREFIX."event_attendane WHERE `student_ID`='".$data['recordid']."'") or die(mysqli_error());
			$msg = "Student record has been deleted";
		}

		if(isset($_POST) && array_key_exists('search',$_POST)){
			if(preg_match("/^[  a-zA-Z]+/", $data['squery'])){ 
				$squery=$data['squery'];
				
				$sql="SELECT user_ID FROM ".DB_PREFIX."system_users WHERE (first_name LIKE '%" . $squery .  "%' OR middle_name LIKE '%" . $squery ."%' OR last_name LIKE '%" . $squery ."%') AND user_level='1'"; 
				$result=mysqli_query($link, $sql); 
				
				$ID_list = mysqli_num_rows($result);
				IF(ID_list < 0):
					$s_err[] = "No record found!";
				ELSE:
					$results = array();
					while($row=mysqli_fetch_array($result)){ 
						$s_result = "Search Result for <b>".$squery."</b>";
						$results[]  = $row['user_ID'];
					}
				ENDIF;
			}else{ 
				$s_err[] = "Please enter a search query"; 
			}
		} 
		
		// PAGINATION
		$page = 0;
		foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
		
		$rpp = 20; // results per page
		$adjacents = 4;
		
		IF(isset($data["page"])):
			$page = intval($data["page"]);
			if($page<=0) $page = 1;
		ENDIF;

		$reload = $_SERVER['PHP_SELF'];
		
		IF(isset($data['arange']) && ($data['arange'] == "Name_Ascending" || $data['arange'] == "Name_Descending")): unset($_SESSION['isortting']); $sorts = $_SESSION['nsortting'];
		ELSEIF(isset($data['arange']) && ($data['arange'] == "ID_Ascending" || $data['arange'] == "ID_Descending")): unset($_SESSION['nsortting']); $sorts = $_SESSION['isortting']; ENDIF;
		
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
							<h4>Student Record</h4>
						</header>							

						<nav id="tab_view">						
							<a id="manage" onclick='$("#addstudent").hide();$("#studentlist").show("slow")' href="javascript:void(0)">Manage Students</a> <span style="color:#FF0000;font-weight:bold">|</span>  
							<a id="add" onclick='$("#studentlist").hide();$("#addstudent").show("slow")' href="javascript:void(0)">Add Student</a>
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
						<form name="searchform" action="index.php" method="post">
							<table style="text-align:left;">
								<tbody>
									<tr>
										<td style="text-align:right">
											<input type="text" name="squery" id="squery" placeholder="Search">
										</td>
										<td style="text-align:left"><input class="special" name="search" type="submit" value="Search" /></td>
									</tr>
								</tbody>
							</table>				
						</form>
						<div style="clear:both"></div>
						<hr />

						<?php
						
							IF(empty($sorts)): $sorts = ""; ENDIF;
							IF(!empty($results)): 
								$ids = join(",",$results);
								$student_profile_list = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix, email, scholarship, college, birthdate, gender, barcode, photo FROM ".DB_PREFIX."system_users WHERE `user_ID` IN (".$ids.") ".$sorts."");
							ELSE:
								$student_profile_list = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix, email, scholarship, college, birthdate, gender, barcode, photo FROM ".DB_PREFIX."system_users WHERE `user_level`='1' ".$sorts."");
							ENDIF;

							$student_list = mysqli_num_rows($student_profile_list);
							IF($student_list < 0):
								echo "<h3>No Record Found</h3>";
							ELSE:

							IF(!empty($s_err)) {
						?>
						<h3><?php if(!empty($s_err)) { echo "<div class=\"msg\">"; foreach ($s_err as $e) { echo "$e <br>"; } echo "</div>"; }  // Display error message  ?></h3>
						<?php 
							}ELSE{

							// count total number of appropriate listings: --- Pagination
							$tcount = mysqli_num_rows($student_profile_list);
							
							// count number of pages:
							$tpages = ($tcount) ? ceil($tcount/$rpp) : 1; // total pages, last page number
							
							$count = 0;
							$i = ($page-1)*$rpp;

						?>
						<h3><?php if(!empty($s_result)) { echo "<div class=\"msg\">"; echo "$s_result <br>"; echo "</div>"; }  // Display error message  ?></h3>
						<div class="table-wrapper" id="studentlist" style="display:<?php IF($_SESSION['addstudenttab']=="managestudent"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<table style="text-align:left;color:#000000;">
								<thead>
									<tr>
										<th>
										<div id="Lbl" style="float:left">Name</div>
											<div id="NAsc" style="float:left">
												<nav id="lname_sort_view">
													<a id="asort" href="index.php?arange=Name_Ascending">&#x25B2;</a> 
													<a id="dsort" href="index.php?arange=Name_Descending">&#x25BC;</a>
												</nav>
											</div>
										</th>
										<th>
											<div id="Lbl" style="float:left">ID No.</div>
											<div id="Asc" style="float:left">
												<nav id="ID_sort_view">
													<a id="Iasort" href="index.php?arange=ID_Ascending">&#x25B2;</a> 
													<a id="Idsort" href="index.php?arange=ID_Descending">&#x25BC;</a>
												</nav>
											</div>
										</th>
										<th>Scholarship</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										IF($tcount <= $rpp): $rpp = $tcount; ENDIF;
										WHILE(($count<$rpp) && ($i<$tcount)) {
										mysqli_data_seek($student_profile_list,$i);
										$S_list = mysqli_fetch_array($student_profile_list);
									
										//WHILE($S_list = mysqli_fetch_array($student_profile_list)){
										$suffix = "";
										IF($S_list['suffix']!=""): $suffix = ", ".$S_list['suffix']; ENDIF;
									?>
									<tr>
										<td><?php echo $S_list['last_name'].", ".$S_list['first_name']." ".$S_list['middle_name'].$suffix;?></td>
										<td><?php echo $S_list['ID_number']; ?></td>
										<td><?php echo $S_list['scholarship']; ?></td>
										<td>
											<nav>						
					<!-- 						<a href="student_view.php?refer=<?php echo $S_list['ID_number']; ?>"><span style="color:#0000FF;">VIEW</span></a> |  -->
												<a href="student_edit.php?refer=<?php echo $S_list['ID_number']; ?>"><span style="color:#00FF00;">EDIT</span></a> |
												<a href="javascript:void(0)" onclick="$('#confirm<?php echo $S_list['user_ID']; ?>').show('slow')"><span style="color:#FF0000;">DELETE</span></a>
											</nav>
											<div id="confirm<?php echo $S_list['user_ID']; ?>" style="display:none;padding:6px;">
												<span style="color:#FF0000;font-weight:bold">Delete this record permanently?</span><br>
												<span style="color:#0080A9;font-weight:bold">Student ID: </span><span style="color:#0080A9;font-weight:bold"><?php echo $S_list['ID_number']; ?></span>
												<form name="deleterestore2" id="deleterestore2" action="index.php" method="post">
													<input type="text" style="display:none" name="recordid" value="<?php echo $S_list['user_ID']; ?>">
													<button style="width:60px;font-size:xx-small;float:left;margin-right:10px" type="submit" class="clean-gray" name="delete" id="delete" value="Delete">
													<span style="color:#FF0000">Yes</span></button>
													<button style="width:60px;font-size:xx-small;float:left" type="button" onclick="$('#confirm<?php echo $S_list['user_ID']; ?>').hide()">No</button>
												</form>
											</div>
										</td>
									</tr>
									<?php 
											$i++;
											$count++;
										} 
									?>
								</tbody>
							</table>
							<?php
								// call pagination function from the appropriate file: pagination1.php, pagination2.php or pagination3.php
								include("../includes/pagination.php");
								echo paginate_three($reload, $page, $tpages, $adjacents);
							?>
						</div>
						<?php } ENDIF; ?> 		
						
						
						<div class="table-wrapper" id="addstudent" style="display:<?php IF($_SESSION['addstudenttab']=="addstudent"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<p><?php if(!empty($err)) { echo "<div class=\"msg\">"; foreach ($err as $e) { echo "$e <br>"; } echo "</div>"; }  // Display error message  ?></p>
							<form action="index.php" method="post" name="RegForm" id="RegForm" >
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
					var view = (type === 'manage') ? 'managestudent' : 'addstudent';
					$.post('library/fx.behaviour.php', { display: view });
				});
				
				//NAME SORT
				$('#lname_sort_view a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'asort') ? 'ORDER BY last_name ASC' : 'ORDER BY last_name DESC';
					$.post('library/fx.behaviour.php', { slname_display: view });
				});
				
				//ID SORT
				$('#ID_sort_view a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'Iasort') ? 'ORDER BY ID_number ASC' : 'ORDER BY ID_number DESC';
					$.post('library/fx.behaviour.php', { sID_display: view });
				});
			</script>
				
	</body>
</html>