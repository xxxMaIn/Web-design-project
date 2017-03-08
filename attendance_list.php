<?php 
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include('includes/headtag'); ?>
	</head>
	<body bgcolor="#dedede">
		<!-- Wrapper -->
			<div id="wrapper">
				<br />
				<div id="gendoc">
				<?php $event_list = mysqli_query($link, "SELECT student_ID, time_in, time_out FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$_SESSION['selecteventID']."' AND `status`='1'"); ?>
					<div class="table-wrapper" id="studentlist">
						<table style="text-align:left;" class='CSSTableGenerator'>
							<tr>
								<td>Title</td>
								<td>What</td>
								<td>Where</td>
								<td>When</td>
							</tr>
							<tr>
								<td style="padding: 0 0.75em">Event Attendance</td>
								<td style="padding: 0 0.75em"><?php echo $_SESSION['eventName']; ?></td>
								<td style="padding: 0 0.75em"><?php echo $_SESSION['eventVenue']; ?></td>
								<td style="padding: 0 0.75em"><?php $phpdate = strtotime( $_SESSION['eventStart'] ); $eventdate = date( 'Y-m-d H:i:s', $phpdate ); echo date("M d Y H:i A", strtotime($eventdate)); ?></td>
							</tr>
						</table>
						<br />
						
						<table style="text-align:left;" class='CSSTableGenerator'>
							<tr>
								<td>Name</td>
								<td>ID No.</td>
								<td>Time in</td>
								<td>Time out</td>
							</tr>
							<?php 
							WHILE($E_list = mysqli_fetch_array($event_list)){
								$student_name = mysqli_fetch_array(mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix FROM ".DB_PREFIX."system_users WHERE `user_ID`='".$E_list['student_ID']."'"));
								$suffix = "";
								IF($student_name['suffix']!=""): $suffix = ", ".$student_name['suffix']; ENDIF;
							?>
							<tr>
								<td style="padding: 0 0.75em"><?php echo $student_name['first_name']." ".$student_name['middle_name']." ".$student_name['last_name'].$suffix;?></td>
								<td style="padding: 0 0.75em"><?php echo $student_name['ID_number']; ?></td>
								<td style="padding: 0 0.75em"><?php echo $E_list['time_in']; ?></td>
								<td style="padding: 0 0.75em"><?php echo $E_list['time_out']; ?></td>
							</tr>
							<?php } ?>
						</table>

					</div>
					<br />
				</div>

				<nav style="text-align:left;">
					<a href="javascript:void(0)" onclick="javascript:window.print()">Back to event list</a><span style="color:#330022;font-weight:bold">  |  </span>
					<a href="attendance_report.php" target="_self">Generate PDF Report</a>
				</nav>

			</div>
			
			<?php include('includes/footertag'); ?>

	</body>
</html>