<?php 
	include '../settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS

	// select event
	if(isset($_POST) && array_key_exists('selecteventbutton',$_POST)){
		foreach($_POST as $key => $value) {
			$data[$key] = filter($value);
		}
		$get_event_data = mysqli_query($link, "SELECT ID, event_name, venue, start_date FROM ".DB_PREFIX."events WHERE `ID`='".$data['eventID']."'");
		$event_details = mysqli_fetch_array($get_event_data);
		$_SESSION['selecteventID'] = $event_details['ID'];
		$_SESSION['eventName'] = $event_details['event_name'];
		$_SESSION['eventVenue'] = $event_details['venue'];
		$_SESSION['eventStart'] = $event_details['start_date'];
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include('includes/headtag'); ?>
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
				<?php include('includes/header'); ?>

				<!-- Main Body -->
					<div id="main">
						<!-- Events Section -->
						<section id="events" class="main special">
							<header class="major">
								<h2>Event Attendance</h2>
							</header>
								<div class="table-wrapper" id="eventlist">
									<form name="select_event" id="select_event" method="post" action="attendance.php">
										<div style="float:left;">
											<select name="eventID" id="eventID" style="width:300px;height:2em;margin-right:10px;">
												<option value="null">-Select Event-</option>
												<?php
												$events = mysqli_query($link, "SELECT ID, event_name FROM ".DB_PREFIX."events WHERE event_status=1");
												$events_cat = mysqli_num_rows($events);
												IF($events_cat <= 0):
													echo "<h3>No Record Found!</h3>";
												ELSE:
													WHILE($eventName = mysqli_fetch_array($events)): ?>
													<option value="<?php echo $eventName['ID']; ?>" <?php IF(isset($_SESSION['selecteventID']) && $_SESSION['selecteventID'] == $eventName['ID']): echo "selected"; ENDIF; ?>><?php echo $eventName['event_name']; ?></option>
													<?php ENDWHILE;
												ENDIF;
												?>
											</select>
										</div>
										<div style="text-align:left;float:left;width:30%;">
											<button type="submit" value="Select Event" style="width:1.75em" class="special" name="selecteventbutton" id="selecteventbutton">Select Event</button>
										</div>
									</form>
								</div>
							<?php							
								IF (isset($_SESSION['selecteventID'])):
							?>
								<div class="table-wrapper" id="eventlist">
									
									<?php 
										$student_profile_list = mysqli_query($link, "SELECT COUNT(student_ID) AS total_attendee FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$_SESSION['selecteventID']."' AND `status`='1'");
										$S_list = mysqli_fetch_array($student_profile_list);
									?>									
									<div class="table-wrapper">
										<table style="text-align:left;">
											<thead>
												<tr>
													<th>Event Name</th>
													<th>Venue</th>
													<th>No. of Attending</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo $_SESSION['eventName']; ?></td>
													<td><?php echo $_SESSION['eventVenue']; ?></td>
													<td style="text-align:center;">
														<a href="javascript:void(0)" onclick="javascript:child_open()">
															<button style="width:60px;font-size:small;float:left" type="button"><?php echo $S_list['total_attendee']; ?></button>
														</a>
													</td>
													<td>
														<nav>						
															<a href="manage_attendance.php"><span style="color:#0000FF;">MANAGE ATTENDANCE</span></a>
														</nav>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							<?php	
								ELSE:
							?>
								<div class="table-wrapper" id="eventlist">
									<h2>No Event Selected</h2>
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
			<?php include('includes/footertag'); ?>

	</body>
</html>