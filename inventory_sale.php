<?php
include('../settings/connect.php');
if(session_id() == '') { page_protect(); } // START SESSIONS	

IF(isset($_POST) && array_key_exists('sellitem',$_POST)){
	foreach($_POST as $key => $value) {
		$data[$key] = filter($value);
	}

	$proid=$data['ITEM'];
	$quantity=$data['quantity'];
	
	$old_stock = mysqli_fetch_array(mysqli_query($link, "select * from ".DB_PREFIX."inventory WHERE `transaction_code` = '".$proid."'"));
	$item_name = $old_stock['item'];
	$baseprice = $old_stock['base_price'];
	$markupprice = $old_stock['markup_price'];
	$itemprice = $baseprice + $markupprice;
	$transcode = Genkey(7); // Generate transaction code
	$olds = $old_stock['qtyleft'];
	$stockcode = $old_stock['stock_code'];
	
	$da = date("Y-m-d h:i:s");
	$col = $_SESSION['college'];
	$collegename = $_SESSION['collegename'];
	
	If($quantity > $olds): 
		$_SESSION['salemessage'] = "Insuficient item stock.";
		$_SESSION['inventorytab'] = "managesale";
		header("location: inventory.php");
		exit();
	ELSE:
		$qtyleft = $olds - $quantity;
		$sales = $itemprice * $quantity;
		$sql_insert = "INSERT INTO ".DB_PREFIX."inventory (item, base_price, markup_price, qtyleft, college_ID, transaction_code, stock_code, last_update, status) 
		VALUES ('$item_name','$baseprice','$markupprice','$qtyleft','$col','$transcode','$stockcode','$da','1')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());

		$sql_insert = "UPDATE ".DB_PREFIX."inventory SET `status` = '0' WHERE `transaction_code` = '".$proid."'";	
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		$sql_insert = "INSERT INTO ".DB_PREFIX."sales (qty, date, sales, transaction_code, stock_code) 
		VALUES ('$quantity','$da','$sales','$transcode','$stockcode')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		// Create activity log
		$uid = $_SESSION['user_id'];
		$activitylog = "Item Sold";
		$_SESSION['salemessage'] = "Item sold: Item Name: <b>".$item_name."</b><br />Quantity: <b>".$quantity."</b><br />Total Sale: <b>".$sales."</b>";
		$sql_insert = "INSERT into ".DB_PREFIX."inventory_activity_log (`description`,`base_price_from`,`markup_price_from`,`transaction_type`,`qty`,`transaction_date`,`user_id`,`transaction_code`,`stock_code`)
		VALUES ('$activitylog','$baseprice','$markupprice','3','$quantity','$da','$uid','$transcode','$stockcode')";
		mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());
		
		$_SESSION['inventorytab'] = "manageinvent";
		header("location: inventory.php");
	ENDIF;
}
?>