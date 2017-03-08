<?php
include('../settings/connect.php');
if(session_id() == '') { page_protect(); } // START SESSIONS	

IF(isset($_POST) && array_key_exists('updateprice',$_POST)){
	foreach($_POST as $key => $value) {
		$data[$key] = filter($value);
	}

	$proid=$data['ITEM'];
	$baseprice=$data['baseprice'];
	$markupprice=$data['markupprice'];
	
	$old_stock = mysqli_fetch_array(mysqli_query($link, "select * from ".DB_PREFIX."inventory WHERE `transaction_code` = '".$proid."'"));
	$item_name = $old_stock['item'];
	$transcode = Genkey(7); // Generate transaction code
	$olds = $old_stock['qtyleft'];
	$old_baseprice = $old_stock['base_price'];
	$old_markupprice = $old_stock['markup_price'];
	$da = date("Y-m-d h:i:s");
	$col = $_SESSION['college'];
	$collegename = $_SESSION['collegename'];
	$stockcode = $old_stock['stock_code'];
	
	
	If((!isset($proid) || $proid == "") || ((!isset($baseprice) || $baseprice == "" || $baseprice == 0)) || (!isset($markupprice) || $markupprice == "")): 
		$_SESSION['pricemessage'] = "Please fill all required fields.";
		$_SESSION['inventorytab'] = "manageprice";
		header("location: inventory.php");
		exit();
	ELSE:
	
		$sql_insert = "INSERT INTO ".DB_PREFIX."inventory (item, base_price, markup_price, qtyleft, college_ID, transaction_code, stock_code, last_update, status) 
		VALUES ('$item_name', '$baseprice','$markupprice','$olds','$col','$transcode','$stockcode','$da','1')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());	
		
		$sql_insert = "UPDATE ".DB_PREFIX."inventory SET `status` = '0' WHERE `transaction_code` = '".$proid."'";	
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		// Create activity log
		$uid = $_SESSION['user_id'];
		$activitylog = "Update Price";
		$_SESSION['pricemessage'] = $activitylog;
		$sql_insert = "INSERT into ".DB_PREFIX."inventory_activity_log (`description`,`transaction_type`,`base_price_from`,`base_price_to`,`markup_price_from`,`markup_price_to`,`transaction_date`,`user_id`,`transaction_code`,`stock_code`)
		VALUES ('$activitylog','4','$old_baseprice','$baseprice','$old_markupprice','$markupprice','$da','$uid','$transcode','$stockcode')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		$_SESSION['inventorytab'] = "manageinvent";
		header("location: inventory.php");
	ENDIF;
}
?>