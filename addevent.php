<?php 
	include '../settings/connect.php';
	IF(session_id() == '') { page_protect(); } // Session start with redirection to login section
	
	IF (Admin()) {
	
		$err = array();
		IF(isset($_POST) && array_key_exists('AddEvent',$_POST)){
			foreach($_POST as $key => $value) {
				$data[$key] = filter($value);
			}
			
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

				$sql_insert = "INSERT into ".DB_PREFIX."events (`event_name`,`start_date`,`end_date`,`venue`,`event_status`)
				VALUES ('$data[eventname]','$sdate','$edate','$data[venue]','$publish')";
				
				mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
				$user_id = mysqli_insert_id($link);
				
				$_SESSION['msg'] = "New event has been added.";
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
		
	}
?>
