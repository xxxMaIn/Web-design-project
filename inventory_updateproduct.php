<?php
include('../settings/connect.php');
if(session_id() == '') { page_protect(); } // START SESSIONS	

IF(isset($_POST) && array_key_exists('add_item',$_POST)){
	foreach($_POST as $key => $value) {
		$data[$key] = filter($value);
	}
	
	$proid=$data['ITEM'];
	$itemnumber=$data['itemnumber'];
	$transcode = Genkey(7); // Generate transaction code
	$da = date("Y-m-d h:i:s");
	$collegename = $_SESSION['collegename'];
	
	$old_stock = mysqli_fetch_array(mysqli_query($link, "select * from ".DB_PREFIX."inventory WHERE `transaction_code` = '".$proid."'"));
	$new_stock = $old_stock['qtyleft'] + $itemnumber;
	$item_name = $old_stock['item'];
	$baseprice = $old_stock['base_price'];
	$markupprice = $old_stock['markup_price'];
	$college = $old_stock['college_ID'];
	$olds = $old_stock['qtyleft'];
	$stockcode = $old_stock['stock_code'];
	$capital = $baseprice * $itemnumber;
	
	
	If(!isset($proid) || !isset($itemnumber)): 
		$_SESSION['itemmessage'] = "Please fill all fields.";
		$_SESSION['inventorytab'] = "manageitems";
		header("location: inventory.php");
		exit();
	ELSE:
	
		$sql_insert = "INSERT INTO ".DB_PREFIX."inventory (item, base_price, capital, markup_price, qtyleft, college_ID, transaction_code, stock_code, last_update, status) VALUES ('$item_name','$baseprice','$capital','$markupprice','$new_stock','$college','$transcode','$stockcode','$da','1')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		
		$sql_insert = "UPDATE ".DB_PREFIX."inventory SET `status` = '0' WHERE `transaction_code` = '".$proid."'";	
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		// Create activity log
		$uid = $_SESSION['user_id'];
		$activitylog = "Stock Added";
		$_SESSION['itemmessage'] = "No. of old stock: ".$olds."<br />Total Stock in hand: ".$new_stock."<br />".$activitylog;
		$sql_insert = "INSERT into ".DB_PREFIX."inventory_activity_log (`description`,`base_price_from`,`markup_price_from`,`capital`,`transaction_type`,`qty`,`transaction_date`,`user_id`,`transaction_code`,`stock_code`)
		VALUES ('$activitylog','$baseprice','$markupprice','$capital','2','$itemnumber','$da','$uid','$transcode','$stockcode')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		$_SESSION['inventorytab'] = "manageinvent";
		header("location: inventory.php");
	ENDIF;
}
?>