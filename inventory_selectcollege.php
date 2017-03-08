<?php
include('../settings/connect.php');
if(session_id() == '') { page_protect(); } // START SESSIONS	

IF(isset($_POST) && array_key_exists('select_col',$_POST)){
	foreach($_POST as $key => $value) {
		$data[$key] = filter($value);
	}

	$colname=$data['ITEM'];
	
	IF($data['smonth'] == "" || $data['emonth'] == "" || $data['sdate'] == "" || $data['edate'] == "" || $data['syear'] == "" || $data['eyear'] == ""): unset($_SESSION['startdate']); unset($_SESSION['enddate']); 
	
	ELSE:
		//Get START and END DATE
		IF($data['smonth'] < 10) { $smonthdata = '0'.$data['smonth']; }else{ $smonthdata = $data['smonth']; }
		IF($data['emonth'] < 10) { $emonthdata = '0'.$data['emonth']; }else{ $emonthdata = $data['emonth']; }

		$sdate = $data['syear']."-".$smonthdata."-".$data['sdate'];
		$edate = $data['eyear']."-".$emonthdata."-".$data['edate'];
	ENDIF;
	
	IF (!isset($colname)): $_SESSION['colmessage'] = "Please select College";
	ELSEIF(isset($colname) && $colname == 99999 ):
		$_SESSION['collegename'] = "All Colleges";
		$_SESSION['college'] = $colname;
	ELSE:
		$college_cat = mysqli_fetch_array(mysqli_query($link, "SELECT ID, name FROM ".DB_PREFIX."colleges_category WHERE `ID` = '".$colname."'"));
		$_SESSION['collegename'] = $college_cat['name'];
		$_SESSION['college'] = $college_cat['ID'];
		$_SESSION['startdate'] = $sdate;
		$_SESSION['enddate'] = $edate;
	ENDIF;
	header("location: inventory.php");
}
?>