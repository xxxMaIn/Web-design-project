<?php
include('../settings/connect.php');
if(session_id() == '') { page_protect(); } // START SESSIONS

IF(isset($_POST) && array_key_exists('addprod',$_POST)){
	foreach($_POST as $key => $value) {
		$data[$key] = filter($value);
	}

	$proname=$data['proname'];
	$baseprice=$data['baseprice'];
	$markupprice=$data['markupprice'];
	$qty=$data['qty'];
	$transcode = Genkey(7); // Generate transaction code
	$stockcode = Genkey(7); // Generate transaction code
	$da = date("Y-m-d h:i:s");
	$col = $_SESSION['college'];
	$collegename = $_SESSION['collegename'];
	$sellingprice = $baseprice + $markupprice;
	$capital = $baseprice * $qty;
	
	If((!isset($proname) || $proname == "") || (!isset($baseprice) || $baseprice == "") || (!isset($markupprice) || $markupprice == "") || (!isset($qty) || $qty == "")): 
		$_SESSION['prodmessage'] = "<span style='color:#FF0000;font-weight:strong'>Please fill all required fields.</span>";
		$_SESSION['inventorytab'] = "manageproduct";
		header("location: inventory.php");
		exit();
	ELSE:
		$search_item = mysqli_query($link, "SELECT item FROM ".DB_PREFIX."inventory WHERE `college_ID` = '".$col."' AND `item` = '".$proname."'");
		$count_item = mysqli_num_rows($search_item);
		IF($count_item > 0): 
			$_SESSION['prodmessage'] = "<span style='color:#FF0000;font-weight:strong'>Product with the same name is already exist in this store,<br />please add additional details to the item name in order to add new product or choose ADD ITEMS from the menu to add stocks to this items.</span>";
			$_SESSION['inventorytab'] = "manageproduct";
			header("location: inventory.php");
			exit();
		ELSE:
			$sql_insert = "INSERT INTO ".DB_PREFIX."inventory (item, base_price, capital, markup_price, qtyleft, college_ID, transaction_code, stock_code, last_update, status) VALUES ('$proname', '$baseprice', '$capital', '$markupprice', '$qty','$col','$transcode','$stockcode','$da','1')";
			mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());

			$baseprice = number_format($baseprice,2,".",",");
			$markupprice = number_format($markupprice,2,".",",");
			$sellingprice = number_format($sellingprice,2,".",",");

			// Create activity log
			$uid = $_SESSION['user_id'];
			$activitylog = "Add Product";
			$_SESSION['prodmessage'] = "New product has been added.<br />Product Name: ".$proname."<br />Base Price: ".$baseprice."<br />Markup Price: ".$markupprice."<br />Selling Price: ".$sellingprice."<br />Quantity:".$qty;
			$sql_insert = "INSERT into ".DB_PREFIX."inventory_activity_log (`description`,`transaction_type`,`qty`,`base_price_from`,`capital`,`markup_price_from`,`transaction_date`,`user_id`,`transaction_code`,`stock_code`)
			VALUES ('$activitylog','1','$qty','$baseprice','$capital','$markupprice','$da','$uid','$transcode','$stockcode')";
			mysqli_query($link, $sql_insert) or die("Insertion Failed:" . mysqli_error());

			$_SESSION['inventorytab'] = "manageinvent";
			header("location: inventory.php");
		ENDIF;
	ENDIF;
}
?>