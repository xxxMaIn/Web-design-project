<?php 
	include 'settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS

	foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
	$eventID = $data['activityID'];
	$_SESSION['eventID'] = $data['activityID'];

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include("includes/headtag"); ?>
		<script type="text/javascript">
			var popupWindow=null;
			function child_open(){ 
				popupWindow =window.open('attendance_list.php',"_blank","directories=no, status=no, menubar=no, scrollbars=yes, resizable=no");
			}
		</script>
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
						<!-- Events Section -->
						<section id="events" class="main special">
							<header class="major">
								<h2>Edit Events</h2>
							</header>
	
								<div class="table-wrapper" id="EditEvents">
									<div class="12u$" style="text-align:left">
									
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
									<nav style="text-align:left;">
										<a href="events.php" style="color:#0000FF">Back to event list</a>
									</nav>
										<?php 

											$current_events = mysqli_query($link, "SELECT ID, event_name, start_date, end_date, venue, poster, event_status FROM ".DB_PREFIX."events WHERE ID='".$eventID."'" );
											$display_events = mysqli_num_rows($current_events);
											IF($display_events <= 0):
												echo "<h3>Event does not exist.</h3>";
											ELSE:
												$events = mysqli_fetch_array($current_events);

												$sdatestring = $events['start_date'];
												list($sdate, $stime) = explode(' ', $sdatestring);
												list($shour, $sminute, $ssec) = explode(':', $stime);

												$edatestring = $events['end_date'];
												list($edate, $etime) = explode(' ', $edatestring);
												list($ehour, $eminute, $esec) = explode(':', $etime);

												$S_list = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(student_ID) AS total_attendee FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$events['ID']."' AND `status`='1'"));

										?>
										<div class="6u 12u$(medium)" style="text-align:left;float:left;width: 60%;margin-left:50px">
											<h3><b>Event Details</b></h3>
											<h4>Event Name</h4>
											<div class="6u 12u$">
												<h3 style="color:#0000FF"><?php echo $events['event_name']; $_SESSION['ceventname'] = $events['event_name']; ?></h3>
											</div>
											<hr />

											<div class="6u 12u$" style="width:100%">
												<h4>Start Date</h4>
												<div style="text-align:left;float:left;color:#003399">
													<h3 style="color:#0000FF">
													<?php
														$sd = date_parse_from_format("Y-m-d H:s:i", $sdatestring);
														$smonth = $sd["month"];
														$sdateObj   = DateTime::createFromFormat('!m', $smonth);
														$smonthName = $sdateObj->format('F');
													
														echo $smonthName." ";
														$sdate = DateTime::createFromFormat("Y-m-d H:s:i", $sdatestring);
														$scdate = $sdate->format("d");
														$stimedate = $sdate->format("A");
															
														echo $scdate.", ";
														
														$syear = strtok($sdatestring, '-'); echo $syear." ".$shour.":".$sminute." ".$stimedate;
													?>
													</h3>
												</div>
											</div>
											<hr />

											<div class="6u 12u$" style="width:100%">
												<h4>End Date</h4>
												<div style="text-align:left;float:left;color:#003399">
													<h3 style="color:#0000FF">
													<?php
														$ed = date_parse_from_format("Y-m-d H:s:i", $edatestring);
														$emonth = $ed["month"];
														$edateObj   = DateTime::createFromFormat('!m', $emonth);
														$emonthName = $edateObj->format('F');

														echo $emonthName." ";

														$edate = DateTime::createFromFormat("Y-m-d H:s:i", $edatestring);
														$ecdate = $edate->format("d");
														$etimedate = $edate->format("A");

														echo $ecdate.", ";

														$eyear = strtok($edatestring, '-'); echo $eyear." ".$ehour.":".$eminute." ".$etimedate;
													?>
													</h3>
												</div>
											</div>
											<hr />

											<h4>Event Venue</h4>
											<div class="6u 12u$">
												<h3 style="color:#0000FF"><?php echo $events['venue']; $_SESSION['ceventvenue'] = $events['venue']; ?></h3>
											</div>
											<hr />
											
											<h4>Total Number of Attendees</h4>
											<div class="6u 12u$">
												<?php IF(Student()): ?>
												<h3 style="color:#0000FF"><?php echo $S_list['total_attendee']; ?></h3>
												<?php ELSE: ?>
												<a href="javascript:void(0)" onclick="javascript:child_open()">
													<button style="width:60px;font-size:small;float:left" type="button">
														<h3 style="color:#0000FF"><?php echo $S_list['total_attendee']; ?></h3>
													</button>
												</a>
												<?php ENDIF; ?>
											</div>

										</div>
										<div class="6u 12u$(medium)" style="text-align:left;float:left;width: 35%;">
										<h3><b>Event Poster</b></h3>
											<?php
												$poster = $events['poster'];
												IF ($poster == "") : $poster = "poster.jpg"; ELSE: $poster = $events['ID']."/".$poster; ENDIF;
											?>
											<div class="poster_pic" style="background-image: url(../images/events/<?php echo $poster; ?>);"></div>
										</div>
										<?php ENDIF; ?>

									</div>
								</div>

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