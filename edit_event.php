<?php 
	include '../settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS

	$err = array();
		IF(isset($_POST) && array_key_exists('EditEvent',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}
			
			$recordID = $data['record_ID'];
			
			//Get START and END DATE
			IF($data['smonth'] < 10) { $smonthdata = '0'.$data['smonth']; }else{ $smonthdata = $data['smonth']; }
			IF($data['emonth'] < 10) { $emonthdata = '0'.$data['emonth']; }else{ $emonthdata = $data['emonth']; }
			
			IF($data['sAMPM'] == 1) { $sAMPM = "AM"; }ELSE{ $sAMPM = "PM"; } 
			IF($data['eAMPM'] == 1) { $eAMPM = "AM"; }ELSE{ $eAMPM = "PM"; } 
			
			$stime = $data['shour'].":".$data['sminute']." ".$sAMPM;
			$starttime = date("G:i", strtotime($stime));
			
			$etime = $data['ehour'].":".$data['eminute']." ".$eAMPM;
			$endtime = date("G:i", strtotime($etime));
			
			$sdate = $data['syear']."-".$smonthdata."-".$data['sdate']." ".$starttime;
			$edate = $data['eyear']."-".$emonthdata."-".$data['edate']." ".$endtime;

			// Validate all required Fields
			IF(empty($data['eventname']) || strlen($data['eventname']) < 2) { $err[] = "ERROR - Invalid event name. Please enter atleast 2 or more characters";}
			IF(!isset($data['smonth']) || $data['smonth'] == "") {$err[] = "ERROR - Please select start month";}
			IF(!isset($data['sdate']) || $data['sdate'] == "") {$err[] = "ERROR - Please select start date";}
			IF(!isset($data['syear']) || $data['syear'] == "") {$err[] = "ERROR - Please select start year";}
			IF(!isset($data['shour']) || $data['shour'] == "") {$err[] = "ERROR - Please select start time";}

			IF(!isset($data['emonth']) || $data['emonth'] == "") {$err[] = "ERROR - Please select end month";}
			IF(!isset($data['edate']) || $data['edate'] == "") {$err[] = "ERROR - Please select end date";}
			IF(!isset($data['eyear']) || $data['eyear'] == "") {$err[] = "ERROR - Please select end year";}
			IF(!isset($data['ehour']) || $data['ehour'] == "") {$err[] = "ERROR - Please select end time";}
			
			IF(empty($data['venue']) || strlen($data['venue']) < 2) { $err[] = "ERROR - Invalid event venue. Please enter atleast 2 or more characters";}
			IF(isset($data['publish'])){ $publish = 1; }ELSE{ $publish = 0; }
				
			//All required form has been validated
			IF(empty($err)) {

				$sql_insert = "UPDATE ".DB_PREFIX."events SET `event_name` = '".$data['eventname']."', `start_date` = '".$sdate."', `end_date` = '".$edate."', `venue` = '".$data['venue']."', `event_status` = '".$publish."' WHERE `ID` = ".$recordID."";	
				mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
				$user_id = mysqli_insert_id($link);
				
				$_SESSION['msg'] = "Event has been updated.";
				$_SESSION['eventtab'] = "manageevents";
				header("Location: events.php");
				exit();

			} // NOV ERROR
				ELSE {
				$_SESSION['errors'] = $err;
				header("Location: events.php");
				exit();
			}
		} // Submitted	
	
	foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
	$eventID = $data['activityID'];
	$_SESSION['eventID'] = $data['activityID'];
	
	
	

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
												
										?>
										
										<div class="6u 12u$(medium)" style="text-align:left;float:left;width: 65%;">

											<form action="edit_event.php" method="post" name="newEventForm" id="editEventForm" >
												<h4>Event Name</h4>
												<div class="6u 12u$">
													<input type="text" style="display:none" name="record_ID" id="record_ID" value="<?php echo $events['ID']; ?>"/>
													<input name="eventname" type="text" id="eventname" value="<?php echo $events['event_name']; ?>" PLACEHOLDER="Event Name" style="margin-right:10px;float:left">
												</div>
												<br />
												<br />

												<div class="6u 12u$" style="width:100%">
													<h4>Start Date</h4>
													<div style="text-align:left;float:left;color:#003399">
														<select id="smonth" name="smonth" style="width:100px;float:left;margin-right:10px">
															<?php
																$sd = date_parse_from_format("Y-m-d H:s:i", $sdatestring);
																$smonth = $sd["month"];
																$sdateObj   = DateTime::createFromFormat('!m', $smonth);
																$smonthName = $sdateObj->format('F');
															?>
															<option value="<?php echo $smonth; ?>" selected="selected"><?php echo $smonthName; ?></option>										
															<?php for( $m=1 ; $m<=12 ; $m++): echo '<option value="'.$m.'">'.date("F", mktime(0, 0, 0, $m, 10)).'</option>'; endfor; ?>
														</select>
														<select id="sdate" name="sdate" style="width:90px;float:left;margin-right:10px">
															<?php
																$sdate = DateTime::createFromFormat("Y-m-d H:s:i", $sdatestring);
																$scdate = $sdate->format("d");
															?>
															<option value="<?php echo $scdate; ?>" selected="selected"><?php echo $scdate; ?></option>
															<?php for( $i=1 ; $i<32 ; $i++): ?> 
															<option value="<?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?>"><?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?></option>
															<?php endfor;?>
														</select>
														<select id="syear" name="syear" style="width:90px;float:left;margin-right:10px">
															<?php $syear = strtok($sdatestring, '-'); echo $syear; ?>
															<option value="<?php echo $syear; ?>" selected="selected"><?php echo $syear; ?></option>
															<?php $curYear = date('Y'); $oldYear = $curYear + 10; for( $i=$oldYear ; $i>=$curYear ; $i--): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
														</select>

														<select id="shour" name="shour" style="width:90px;float:left;margin-right:10px">
															<option value="<?php echo $shour; ?>" selected="selected"><?php echo $shour; ?></option>
															<?php $etime = 1; for( $i=$etime ; $i<=12; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
														</select>

														<select id="sminute" name="sminute" style="width:90px;float:left;margin-right:10px">
															<option value="<?php echo $sminute; ?>" selected="selected"><?php echo $sminute; ?></option>
															<?php $emin = 0; for( $i=$emin ; $i<=60 ; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
														</select>

														<select id="sAMPM" name="sAMPM" style="width:90px;float:left;margin-right:10px">
															
															<option value="1" selected="selected">AM</option>
															<option value="2">PM</option>
														</select>
													</div>
													<br />
													<br />
													<br />

													<h4>End Date</h4>
													<div style="text-align:left;float:left;color:#003399">
														<select id="emonth" name="emonth" style="width:100px;float:left;margin-right:10px">
															<?php
																$ed = date_parse_from_format("Y-m-d H:s:i", $edatestring);
																$emonth = $ed["month"];
																$edateObj   = DateTime::createFromFormat('!m', $emonth);
																$emonthName = $edateObj->format('F');
															?>
															<option value="<?php echo $emonth; ?>" selected="selected"><?php echo $emonthName; ?></option>										
															<?php for( $m=1 ; $m<=12 ; $m++): echo '<option value="'.$m.'">'.date("F", mktime(0, 0, 0, $m, 10)).'</option>'; endfor; ?>
														</select>
														<select id="edate" name="edate" style="width:90px;float:left;margin-right:10px">
															<?php
																$edate = DateTime::createFromFormat("Y-m-d H:s:i", $edatestring);
																$ecdate = $edate->format("d");
															?>
															<option value="<?php echo $ecdate; ?>" selected="selected"><?php echo $ecdate; ?></option>
															<?php for( $i=1 ; $i<32 ; $i++): ?> 
															<option value="<?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?>"><?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?></option>
															<?php endfor;?>
														</select>
														<select id="eyear" name="eyear" style="width:90px;float:left;margin-right:10px">
															<?php $eyear = strtok($edatestring, '-'); echo $eyear; ?>
															<option value="<?php echo $eyear; ?>" selected="selected"><?php echo $eyear; ?></option>
															<?php $curYear = date('Y'); $oldYear = $curYear + 10; for( $i=$oldYear ; $i>=$curYear ; $i--): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
														</select>

														<select id="ehour" name="ehour" style="width:90px;float:left;margin-right:10px">
															<option value="<?php echo $ehour; ?>" selected="selected"><?php echo $ehour; ?></option>
															<?php $etime = 1; for( $i=$etime ; $i<=12; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
														</select>

														<select id="eminute" name="eminute" style="width:90px;float:left;margin-right:10px">
															<option value="<?php echo $eminute; ?>" selected="selected"><?php echo $eminute; ?></option>
															<?php $emin = 0; for( $i=$emin ; $i<=60 ; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
														</select>

														<select id="eAMPM" name="eAMPM" style="width:90px;float:left;margin-right:10px">
															<option value="1" selected="selected">AM</option>
															<option value="2">PM</option>
														</select>
													</div>

												</div>
												<br />
												<br />

												<h4>Event Venue</h4>
												<div class="6u 12u$">
													<input type="text" name="venue" id="venue" value="<?php echo $events['venue']; ?>" placeholder="Venue" />
												</div>
												<br />
												
												<div class="6u 12u$">
													<input type="checkbox" id="publish" name="publish" value="1" <?php IF($events['event_status'] == 1): echo "checked"; ENDIF; ?>>Publish
												</div>
												<br />

												<div class="12u$">
													<ul class="actions">
														<li><button type="submit" value="Add Event" class="special" name="EditEvent" id="EditEvent">Update Event</button></li>
													</ul>
												</div>
											</form>
										</div>
										<div class="6u 12u$(medium)" style="text-align:left;float:left;width: 35%;">
										<h3>Event Poster</h3>
											<?php
												$poster = $events['poster'];
												IF ($poster == "") : $poster = "poster.jpg"; ELSE: $poster = $events['ID']."/".$poster; ENDIF;
											?>
											<div class="poster_pic" style="background-image: url(../images/events/<?php echo $poster; ?>);"></div>
											<nav style="text-align:center;">
												<a href="poster_upload.php">Change/Upload Poster</a>
											</nav>
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
			<?php include('includes/footertag'); ?>
	</body>
</html>