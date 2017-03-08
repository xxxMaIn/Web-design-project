<?php 
	include '../settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS
	
	date_default_timezone_set('Asia/Manila'); // Set local timezone

	IF(!isset($_SESSION['managestudenttab'])): $_SESSION['managestudenttab']="removestudent"; ENDIF;
	foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
	
	$timeout = date('H:i:s'); 
	// select event
		if(isset($_POST) && array_key_exists('Update',$_POST)){		
			foreach($_POST['checkin'] as $recID){
				$id = $recID;
				$sql = "UPDATE ".DB_PREFIX."event_attendance SET `status`='0', `time_out`='".$timeout."' WHERE `student_ID` = ".$id." AND `event_ID`=".$_SESSION['selecteventID']."";
				//$sql = "DELETE FROM ".DB_PREFIX."event_attendance WHERE `student_ID` = ".$id." AND `event_ID`=".$_SESSION['selecteventID']."";
				$result = mysqli_query($link, $sql);
				$msg = "Attendance has been updated";
			}
		}

	// Signup
		if(isset($_POST) && array_key_exists('Signup',$_POST)){		
			foreach($_POST['signed'] as $recID){
				$id = $recID;
				$eventID = $_SESSION['selecteventID'];

/*				$event_time = mysqli_fetch_array(mysqli_query($link, "SELECT start_date, end_date FROM ".DB_PREFIX."events WHERE `ID`='".$_SESSION['selecteventID']."'"));

				$T_in = $event_time['start_date'];
				$T_in = strtotime($T_in);
				$timein = date('H:i:s', $T_in);

				$T_out = $event_time['end_date'];
				$T_out = strtotime($T_out);
				$timeout = date('H:i:s', $T_out);
				
				//text format date('F j, Y g:i:a  ');
*/
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
				$msg = "Attendance has been updated";
			}
		}

		IF(isset($data['arange']) && ($data['arange'] == "Name_Ascending" || $data['arange'] == "Name_Descending")): unset($_SESSION['isortting1']); unset($_SESSION['isortting']); $sorts = $_SESSION['nsortting'];
		ELSEIF(isset($data['arange']) && ($data['arange'] == "ID_Ascending" || $data['arange'] == "ID_Descending")): unset($_SESSION['nsortting1']); IF(isset($_SESSION['nsortting'])):unset($_SESSION['nsortting']);ENDIF; $sorts = $_SESSION['isortting']; ENDIF;
		IF(empty($sorts)): $sorts = ""; ENDIF;

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
						<!-- Events Section -->
						<section id="events" class="main special">
							<header class="major">
								<h2>Event Attendance</h2>
								<h3><?php echo $_SESSION['eventName']; ?></h3>
							</header>
						
							<nav id="tab_view">
								<a id="remove" onclick='$("#addstudent").hide();$("#removestudent").show("slow")' href="javascript:void(0)">Remove Attending Students</a> |
								<a id="add" onclick='$("#removestudent").hide();$("#addstudent").show("slow")' href="javascript:void(0)">Add Attending Students</a>
							</nav>						
						
							<div class="table-wrapper" id="removestudent" style="display:<?php IF($_SESSION['managestudenttab']=="removestudent"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
								<hr />
								<h3><?php if(!empty($msg)) { echo "<div class=\"msg\">"; echo $msg."</div>"; }  // Display message  ?></h3>

								<div class="12u$" style="text-align:left">
									<nav>						
										<a href="attendance.php"><span style="color:#FF0000;">Back to event list</span></a>
									</nav>
								</div>
								<br />
								<br />
								
								<?php
									$event_list = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix FROM ".DB_PREFIX."system_users WHERE `user_level`='1' AND `user_ID` IN (SELECT student_ID FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$_SESSION['selecteventID']."' AND `status`='1') ".$sorts."");
								?>
								<div class="table-wrapper" id="studentlist">
									<form name="attendform" id="attendform" method="post" action="manage_attendance.php">
										<table style="text-align:left;">
											<thead>
												<tr>
													<th>
														<div id="Lbl" style="float:left">Name</div>
														<div id="NAsc" style="float:left">
															<nav id="lname_sort_view">
																<a id="asort" href="manage_attendance.php?arange=Name_Ascending">&#x25B2;</a> 
																<a id="dsort" href="manage_attendance.php?arange=Name_Descending">&#x25BC;</a>
															</nav>
														</div>
													</th>
													<th>
														<div id="Lbl" style="float:left">ID No.</div>
														<div id="Asc" style="float:left">
															<nav id="ID_sort_view">
																<a id="Iasort" href="manage_attendance.php?arange=ID_Ascending">&#x25B2;</a> 
																<a id="Idsort" href="manage_attendance.php?arange=ID_Descending">&#x25BC;</a>
															</nav>
														</div>
													</th>
													<th style="text-align:right">Check all<input type="checkbox" class="checkAll"/></th>
												</tr>
											</thead>
											<tbody>
												<?php
												WHILE($student_name = mysqli_fetch_array($event_list)){
													$suffix = "";
													IF($student_name['suffix']!=""): $suffix = ", ".$student_name['suffix']; ENDIF;
												?>
												<tr>
													<td style="padding: 0 0.75em"><?php echo $student_name['last_name'].", ".$student_name['first_name']." ".$student_name['middle_name'].$suffix;?></td>
													<td style="padding: 0 0.75em"><?php echo $student_name['ID_number']; ?></td>
													<td style="padding: 0 0.75em;text-align:right;">
														<input type="checkbox" class="checkbox" id="checkin" name="checkin[]" value="<?php echo $student_name['user_ID']; ?>" />
													</td>
												</tr>
												<?php } ?>
											</tbody>
										</table>
										<div class="12u$" style="text-align:right">
											<button type="submit" value="Update" class="special" name="Update" id="Update">Remove from the event</button>
										</div>
									</form>
								</div>
								<br />

								<div class="12u$" style="text-align:left">
									<nav>						
										<a href="attendance.php"><span style="color:#FF0000;">Back to event list</span></a>
									</nav>
								</div>
							</div>
							

							<div class="table-wrapper" id="addstudent" style="display:<?php IF($_SESSION['managestudenttab']=="addstudent"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
								<div class="12u$" style="text-align:left">
									<nav>						
										<a href="attendance.php"><span style="color:#FF0000;">Back to event list</span></a>
									</nav>
								</div>
								<h3><?php if(!empty($_SESSION['msg'])) { echo "<div class=\"msg\">"; echo $_SESSION['msg']."</div>"; } unset($_SESSION['msg']);  // Display message  ?></h3>
								<form name="rform" action="manage_attendance_verify.php" method="post">
									<table style="text-align:left;">
										<tbody>
											<tr>
												<td style="width:25%"></td>
												<td style="width:50%;text-align:right">
													<div style="float:left">
														<input style="width:200px" type="text" name="idquerry" id="idquerry" placeholder="Student ID">
													</div>
													<div style="float:left;margin-left:20px">
														<input class="special" name="idsearch" type="submit" value="Add to Event" />
													</div>
												</td>
												<td style="width:25%">
												</td>
											</tr>
										</tbody>
									</table>				
								</form>
								<div style="clear:both"></div>	
								
								<div class="table-wrapper" id="studentlist">
									<form name="attendform2" id="attendform2" method="post" action="manage_attendance.php">
										<table style="text-align:left;">
											<thead>
												<tr>
													<th>
														<div id="Lbl" style="float:left">Name</div>
														<div id="NAsc" style="float:left">
															<nav id="lname_sort_view1">
																<a id="asort1" href="manage_attendance.php?arange=Name_Ascending">&#x25B2;</a> 
																<a id="dsort1" href="manage_attendance.php?arange=Name_Descending">&#x25BC;</a>
															</nav>
														</div>
													</th>
													<th>
														<div id="Lbl" style="float:left">ID No.</div>
														<div id="Asc" style="float:left">
															<nav id="ID_sort_view1">
																<a id="Iasort1" href="manage_attendance.php?arange=ID_Ascending">&#x25B2;</a> 
																<a id="Idsort1" href="manage_attendance.php?arange=ID_Descending">&#x25BC;</a>
															</nav>
														</div>
													</th>
													<th style="text-align:right">Check all <input type="checkbox" class="checkAll"/></th>
												</tr>
											</thead>
											<tbody>
												<?php
												
												$studentname = mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix FROM ".DB_PREFIX."system_users WHERE `user_level`='1' AND (`user_ID` NOT IN (SELECT student_ID FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$_SESSION['selecteventID']."') OR `user_ID` IN (SELECT student_ID FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$_SESSION['selecteventID']."' AND `status`='0')) ".$sorts."");

												WHILE ($student_name = mysqli_fetch_array($studentname)){

													$suffix = "";
													IF($student_name['suffix']!=""): $suffix = ", ".$student_name['suffix']; ENDIF;
												?>
												<tr>
													<td style="padding: 0 0.75em"><?php echo $student_name['last_name'].", ".$student_name['first_name']." ".$student_name['middle_name'].$suffix;?></td>
													<td style="padding: 0 0.75em"><?php echo $student_name['ID_number']; ?></td>
													<td style="padding: 0 0.75em;text-align:right;">
														<input type="checkbox" class="checkbox" id="signed" name="signed[]" value="<?php echo $student_name['user_ID']; ?>" />
													</td>
												</tr>
												<?php } ?>
											</tbody>
										</table>
										<div class="12u$" style="text-align:right">
											<button type="submit" value="Signup" class="special" name="Signup" id="Signup">Signup to this event</button>
										</div>
									</form>
								</div>
								<hr />

								<div class="12u$" style="text-align:left">
									<nav>						
										<a href="attendance.php"><span style="color:#FF0000;">Back to event list</span></a>
									</nav>
								</div>
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
				$('.checkAll').change(function() {
				  var checkboxes = $(this).closest('form').find(':checkbox');
				  if($(this).is(':checked')) {
					  checkboxes.attr('checked', 'checked');
				  } else {
					  checkboxes.removeAttr('checked');
				  }
				});
			
				//TAB VIEW
				$('#tab_view a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'remove') ? 'removestudent' : 'addstudent';
					$.post('library/fx.behaviour.php', { attend_display: view });
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
				
				//NAME SORT
				$('#lname_sort_view1 a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'asort1') ? 'ORDER BY last_name ASC' : 'ORDER BY last_name DESC';
					$.post('library/fx.behaviour.php', { slname_display1: view });
				});
				
				//ID SORT
				$('#ID_sort_view1 a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'Iasort1') ? 'ORDER BY ID_number ASC' : 'ORDER BY ID_number DESC';
					$.post('library/fx.behaviour.php', { sID_display1: view });
				});
			</script>
	</body>
</html>