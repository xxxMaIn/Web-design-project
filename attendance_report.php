<?php

	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS
	
	$event_list = mysqli_query($link, "SELECT student_ID, time_in, time_out FROM ".DB_PREFIX."event_attendance WHERE `event_ID`='".$_SESSION['selecteventID']."' AND `status`='1'");

	$phpdate = strtotime( $_SESSION['eventStart'] ); 
	$eventdate = date( 'Y-m-d H:i:s', $phpdate ); 
	$startdate = date("M d Y H:i A", strtotime($eventdate));

	WHILE($E_list = mysqli_fetch_array($event_list)){
		$student_name = mysqli_fetch_array(mysqli_query($link, "SELECT user_ID, ID_number, first_name, middle_name, last_name, suffix FROM ".DB_PREFIX."system_users WHERE `user_ID`='".$E_list['student_ID']."'"));
		$suffix = "";
		IF($student_name['suffix']!=""): $suffix = ", ".$student_name['suffix']; ENDIF;

		$datas .= '<tr><td style="padding: 0 0.75em">'.$student_name['first_name'].' '.$student_name['middle_name'].' '.$student_name['last_name'].$suffix.'</td><td style="padding: 0 0.75em">'.$student_name['ID_number'].'</td><td style="padding: 0 0.75em">'.$E_list['time_in'].'</td><td style="padding: 0 0.75em">'.$E_list['time_out'].'</td></tr>';

	}

				$html1 = '
						<div id="gendoc">
								<div class="table-wrapper" id="studentlist">
									<table style="text-align:left;" class="CSSTableGenerator">
										<tr>
											<td>Title</td>
											<td>What</td>
											<td>Where</td>
											<td>When</td>
										</tr>
										<tr>
											<td style="padding: 0 0.75em"><h4>Event Attendance</h4></td>
											<td style="padding: 0 0.75em"><h4>'.$_SESSION['eventName'].'</h4></td>
											<td style="padding: 0 0.75em"><h4>'.$_SESSION['eventVenue'].'</h4></td>
											<td style="padding: 0 0.75em"><h4>'.$startdate.'</h4></td>
										</tr>
									</table>
									<br />
									
									<table style="text-align:left;" class="CSSTableGenerator">
										<tr>
											<td>Name</td>
											<td>ID No.</td>
											<td>Time in</td>
											<td>Time out</td>
										</tr>';
									$html2 = '
									</table>
								</div>
							</div>
						';
						$html = $html1.$datas.$html2;
//==============================================================

include("includes/mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->SetDisplayMode('fullpage');

$mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list

// LOAD a stylesheet
$stylesheet = file_get_contents('assets/css/table.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->WriteHTML($html,2);

$mpdf->Output('attendance_report.pdf','I');
exit;
//==============================================================


?>