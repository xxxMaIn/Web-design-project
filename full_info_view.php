<?php 
	include 'settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS
	foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
	
	$studentID = $data['refer'];
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
								<h2>Student Profile</h2>
							</header>
							<?php IF(!isset($_SESSION['user_id'])): // Logged out ?>
								<h3>Please login in order to view your profile</h3>
							<?php include('includes/login.php');
								ELSE:
										$userID = $_SESSION['user_id'];
										$student_profile = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, email, scholarship, college, birthdate, gender, photo, question, answer FROM ".DB_PREFIX."system_users WHERE `ID_number`='".$studentID."'");
										$studentprofile = mysqli_num_rows($student_profile);
										IF($studentprofile <= 0):
											echo "<h3>No Record Found</h3>";
										ELSE:
										$profile = mysqli_fetch_array($student_profile);
										$college_ID = $profile['college'];
										$question_ID = $profile['question'];
										$college = mysqli_fetch_array(mysqli_query($link, "SELECT name FROM ".DB_PREFIX."colleges_category WHERE `ID`='".$college_ID."'"));
										$question = mysqli_fetch_array(mysqli_query($link, "SELECT question FROM ".DB_PREFIX."security_question WHERE `ID`='".$question_ID."'"));
								?>
									<div>
										<div class="6u 12u$(medium)" style="text-align:left;float:left">
											<h3>Basic Details</h3>
											<a href="full_info_edit.php?refer=<?php echo $profile['ID_number']; ?>"><span style="color:#00FF00;">EDIT</span></a>
											<dl class="alt">
												<dt>ID No.</dt>
												<dd>: <?php echo $profile['ID_number']; ?></dd>
												<dt>Name</dt>
												<dd>: <?php echo $profile['first_name']." ".$profile['middle_name']." ".$profile['last_name']; ?></dd>
												<dt>Birthdate</dt>
												<dd>: <?php echo $profile['birthdate']; ?></dd>
												<dt>Gender</dt>
												<dd>: <?php IF($profile['gender']==1): echo "Male"; ELSE: echo "Female"; ENDIF; ?></dd>
												<dt>Email</dt>
												<dd>: <?php echo $profile['email']; ?></dd>
												<dt>Scholarship</dt>
												<dd>: <?php echo $profile['scholarship']; ?></dd>
												<dt>College</dt>
												<dd>: <?php echo $college['name']; ?></dd>
												<dt>Secret Question</dt>
												<dd>: <?php echo $question['question']; ?>  <b>Answer :</b> <?php echo $profile['answer']; ?></dd>
											</dl>
											<input action="action" type="button" value="Back" class="special" onclick="history.go(-1);" />
										</div>
										<div class="6u 12u$(medium)" style="text-align:left;float:left">									
											<h3>Photo</h3>
											<?php
												$photo = $profile['photo'];
												IF ($photo == "") : $photo = "student.jpg"; ELSE: $photo = $profile['user_ID']."/".$photo; ENDIF;
											?>
											<div class="profile_pic" style="background-image: url(images/users/<?php echo $photo; ?>);"></div>
											<br />
											<?php include 'includes/code_128.php'; ?>
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