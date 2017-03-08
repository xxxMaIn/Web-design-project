<?php 
	include '../settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS

	// Delete Permanently (Individual Record)
	if(isset($_POST) && array_key_exists('delete',$_POST)){
		foreach($_POST as $key => $value) {
			$data[$key] = filter($value);
		}

		mysqli_query($link, "DELETE FROM ".DB_PREFIX."events WHERE `ID`='".$data['recordid']."'") or die(mysqli_error());
		mysqli_query($link, "DELETE FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$data['recordid']."'") or die(mysqli_error());
		$msg = "Event has been deleted and all attendance under it.";
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
								<h2>Events</h2>
							</header>
							<nav id="tab_view">
								<a id="manageevent" onclick='$("#addevents").hide();$("#eventlist").show("slow")' href="javascript:void(0)">Manage Events</a> <span style="color:#FF0000;font-weight:bold">|</span> 
								<a id="addevent" onclick='$("#eventlist").hide();$("#addevents").show("slow")' href="javascript:void(0)">Add Event</a>
							</nav>

							<?php
								$current_events = mysqli_query($link, "SELECT ID, event_name, start_date, end_date, venue, event_status FROM ".DB_PREFIX."events");
								$display_events = mysqli_num_rows($current_events);
								
								// count total number of appropriate listings: --- Pagination
								$tcount = mysqli_num_rows($current_events);
										
								// count number of pages:
								$tpages = ($tcount) ? ceil($tcount/$rpp) : 1; // total pages, last page number
										
								$count = 0;
								$i = ($page-1)*$rpp;

								IF($display_events <= 0):
									echo "<h3>No events at the moment</h3>";
								ELSE:

							?>
								
								<div class="table-wrapper" id="eventlist" style="display:<?php IF($_SESSION['eventtab'] == "manageevents"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
									
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

									<table style="text-align:left;font-size:12px">
										<thead>
											<tr>
												<th>Published</th>
												<th>Event</th>
												<th>Start Date</th>
												<th>End Date</th>
												<th>Venue</th>
												<th># of Attendee</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<?php 
											
											IF($tcount <= $rpp): $rpp = $tcount; ENDIF;
											WHILE(($count<$rpp) && ($i<$tcount)) {
											mysqli_data_seek($current_events,$i);
											$events = mysqli_fetch_array($current_events);
											$student_profile_list = mysqli_query($link, "SELECT COUNT(student_ID) AS total_attendee FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$events['ID']."'");
											$S_list = mysqli_fetch_array($student_profile_list);
											?>
											<tr>
												<td style="text-align:center;">
													<?php IF($events['event_status'] == 1): echo "<span style='color:#00FF00;'>Yes</span>"; ELSE: echo "<span style='color:#FF0000;'>No</span>"; ENDIF; ?></span>
												</td>
												<td><?php echo $events['event_name']; ?></td>
												<td><?php echo $events['start_date']; ?></td>
												<td><?php echo $events['end_date']; ?></td>
												<td><?php echo $events['venue']; ?></td>
												<td style="text-align:center;<?php IF($S_list['total_attendee'] <= 0): echo 'color:#FF0000;'; ELSE: echo 'color:#00FF00;'; ENDIF; ?>"><?php echo $S_list['total_attendee']; ?></td>
												<td>
													<nav>
														<a href="edit_event.php?activityID=<?php echo $events['ID']; ?>"><span style="color:#00FF00;">EDIT</span></a> |
														<a href="javascript:void(0)" onclick="$('#confirm<?php echo $events['ID']; ?>').show('slow')"><span style="color:#FF0000;">DELETE</span></a>
													</nav>
													<div id="confirm<?php echo $events['ID']; ?>" style="display:none;padding:6px;">
														<span style="color:#FF0000;font-weight:bold">Delete this event permanently?</span><br>
														<form name="deleterestore2" id="deleterestore2" action="events.php" method="post">
															<input type="text" style="display:none" name="recordid" value="<?php echo $events['ID']; ?>">
															<button style="width:60px;font-size:xx-small;float:left;margin-right:10px" type="submit" class="clean-gray" name="delete" id="delete" value="Delete">
															<span style="color:#FF0000">Yes</span></button>
															<button style="width:60px;font-size:xx-small;float:left" type="button" onclick="$('#confirm<?php echo $events['ID']; ?>').hide()">No</button>
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
							<?php ENDIF; ?>
	
								<div class="table-wrapper" id="addevents" style="display:<?php IF($_SESSION['eventtab'] == "addnewevent"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
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
									<br />

										<h2>Add New Event</h2>

										<form action="addevent.php" method="post" name="newEventForm" id="newEventForm" >
											<h4>Event Name</h4>
											<div class="6u 12u$">
												<input name="eventname" type="text" id="eventname" value="" PLACEHOLDER="Event Name" style="margin-right:10px;float:left">
											</div>
											<br />
											<br />

											<div class="6u 12u$" style="width:100%">
												<h4>Start Date</h4>
												<div style="text-align:left;float:left;color:#003399">
													<select id="smonth" name="smonth" style="width:100px;float:left;margin-right:10px">
														<option value="" selected="selected">Month</option>										
														<?php for( $m=1 ; $m<=12 ; $m++): echo '<option value="'.$m.'">'.date("F", mktime(0, 0, 0, $m, 10)).'</option>'; endfor; ?>
													</select>
													<select id="sdate" name="sdate" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Date</option>
														<?php for( $i=1 ; $i<32 ; $i++): ?> 
														<option value="<?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?>"><?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?></option>
														<?php endfor;?>
													</select>
													<select id="syear" name="syear" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Year</option>
														<?php $curYear = date('Y'); $oldYear = $curYear + 10; for( $i=$oldYear ; $i>=$curYear ; $i--): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
													</select>

													<select id="shour" name="shour" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Hour</option>
														<?php $etime = 1; for( $i=$etime ; $i<=12; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
													</select>

													<select id="sminute" name="sminute" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Minute</option>
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
														<option value="" selected="selected">Month</option>										
														<?php for( $m=1 ; $m<=12 ; $m++): echo '<option value="'.$m.'">'.date("F", mktime(0, 0, 0, $m, 10)).'</option>'; endfor; ?>
													</select>
													<select id="edate" name="edate" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Date</option>
														<?php for( $i=1 ; $i<32 ; $i++): ?> 
														<option value="<?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?>"><?php if ($i < '10'){ echo ('0'.$i); } else { echo $i; } ?></option>
														<?php endfor;?>
													</select>
													<select id="eyear" name="eyear" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Year</option>
														<?php $curYear = date('Y'); $oldYear = $curYear + 10; for( $i=$oldYear ; $i>=$curYear ; $i--): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
													</select>

													<select id="ehour" name="ehour" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Hour</option>
														<?php $etime = 1; for( $i=$etime ; $i<=12; $i++): echo '<option value="'.$i.'">'.$i.'</option>'; endfor; ?>
													</select>

													<select id="eminute" name="eminute" style="width:90px;float:left;margin-right:10px">
														<option value="" selected="selected">Minute</option>
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
												<input type="text" name="venue" id="venue" placeholder="Venue" />
											</div>
											<br />
											
											<div class="6u 12u$">
												<input type="checkbox" id="publish" name="publish" value="1">Publish
											</div>
											<br />

											<div class="12u$">
												<ul class="actions">
													<li><button type="submit" value="Add Event" class="special" name="AddEvent" id="AddEvent">Add Event</button></li>
												</ul>
											</div>
										</form>

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
				//TAB VIEW
				$('#tab_view a').click(function(e) {
					var type = $(this).attr('id');
					var view = (type === 'manageevent') ? 'manageevents' : 'addnewevent';
					$.post('library/fx.behaviour.php', { event_display: view });
				});
			</script>
	</body>
</html>