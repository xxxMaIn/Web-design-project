<?php 
	include '../settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS
	
	foreach($_POST as $key => $value) { $data[$key] = filter($value); }
	$continue_add = 0;
	
	// Verify
	if(isset($_POST) && array_key_exists('idsearch',$_POST)){	
		$id = $data['idquerry'];
		$eventID = $_SESSION['selecteventID'];
		
		$verify_ID = mysqli_query($link, "SELECT CONCAT(last_name,', ',first_name,' ',middle_name) AS fullname, user_ID, ID_number FROM ".DB_PREFIX."system_users WHERE `user_level`='1' AND `ID_number`='".$id."'");
		$verified = mysqli_num_rows($verify_ID);
		IF($verified > 0):
			$name = mysqli_fetch_array($verify_ID);
			$user_ID = $name['user_ID'];
			$student_name = $name['fullname'];
			$check_event_list = mysqli_query($link, "SELECT status FROM ".DB_PREFIX."event_attendance WHERE `student_ID`='".$user_ID."' AND `event_ID`='".$_SESSION['selecteventID']."' AND `status`='1'");
			$event_checked = mysqli_num_rows($check_event_list);
			IF($event_checked > 0):
				$msg = "Student is already checked-in to this event!";
			ELSE:
				$continue_add = 1;
			ENDIF;
		ELSE:
			$msg = "No record found, Student ID does not exist!";
		ENDIF;
	}
		
	// Signup
	if(isset($_POST) && array_key_exists('addtoevent',$_POST)){
		$id = $data['studentID'];
		$eventID = $_SESSION['selecteventID'];

		$timein = date('H:i:s');
		$timeout = "00:00:00";
			
		$check_record = mysqli_query($link, "SELECT student_ID FROM ".DB_PREFIX."event_attendance WHERE `student_ID`='".$id."' AND `event_ID`='".$_SESSION['selecteventID']."'");
		$records = mysqli_num_rows($check_record);
		IF($records > 0):
			$sql = "UPDATE ".DB_PREFIX."event_attendance SET `status` = '1', `time_out`='".$timeout."' WHERE `student_ID` = ".$id." AND `event_ID`=".$_SESSION['selecteventID']."";
			$result = mysqli_query($link, $sql);
		ELSE:
			$sql_insert = "INSERT into ".DB_PREFIX."event_attendance (`event_ID`,`student_ID`,`time_in`,`status`)
			VALUES ('$eventID','$id','$timein','1')";
			mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		ENDIF;
		$_SESSION['msg'] = "Attendance has been updated, Add another student to for this event.";
		header('location: manage_attendance.php');
	}

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
				<?php include('includes/header'); ?>

				<!-- Main Body -->
					<div id="main">
						<!-- Student Profile Section -->
						<section id="profile" class="main special">
							<header class="major">
								<h2>Attendance Management Manual Student Input</h2>
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
									
								IF($continue_add == 1):
								?>

								<form action="manage_attendance_verify.php" method="post" name="addForm" id="addForm" >
									<input type="hidden" name="studentID" id="studentID" value="<?php echo $user_ID; ?>" />
									
									<div class="6u 12u$" style="margin:0 auto">
										<h2>Name : <?php echo $student_name; ?></h2>
										<h2>ID No. : <?php echo $id; ?></h2>
										
										<div class="12u$">
											<ul class="actions">
												<li><input type="submit" value="Add to event" class="special" name="addtoevent" id="addtoevent"/></li>
											</ul>
										</div>
									</div>
								</form>
								
								<?php
								$continue_add = 0;
								ELSE:
								?>
								<h3><?php if(!empty($msg)) { echo "<div class=\"msg\">"; echo $msg."</div>"; }  // Display message  ?></h3>
								<hr />
								<div class="12u$" style="margin:0 auto;">
									<nav>						
										<a href="manage_attendance.php"><span style="color:#FF0000;">Back to event list</span></a>
									</nav>
								</div>
								<?php
								ENDIF;
								?>
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