<?php

include('../settings/connect.php');
if(session_id() == '') { page_protect(); } // START SESSIONS

	IF ($_SESSION['college']==99999): $condition = "WHERE status = 1"; $app = "AND"; ELSE: $condition = "WHERE `college_ID` = '".$_SESSION['college']."' AND status = 1"; $app = "AND"; ENDIF;
	IF (!empty($_SESSION['startdate']) && !empty($_SESSION['enddate'])): $datecond = "AND `last_update` BETWEEN '".$_SESSION['startdate']."' AND '".$_SESSION['enddate']."' ORDER BY `id` ASC"; $datecond2 = "AND `transaction_date` BETWEEN '".$_SESSION['startdate']."' AND '".$_SESSION['enddate']."' ORDER BY `id` ASC"; ELSE: $datecond = ""; $datecond2 = ""; ENDIF;

	IF (!empty($_SESSION['startdate']) && !empty($_SESSION['enddate'])):
		$inventory_report_date = '<h2>Inventory Report from '.$_SESSION['startdate'].' TO '.$_SESSION['enddate'].'</h2>';
	ENDIF;

	// Get active product
	$product = mysqli_query($link, "SELECT * FROM ".DB_PREFIX."inventory ".$condition." ".$datecond."");

	$results = mysqli_num_rows($product);
	IF ($results <= 0): echo "<h1>No Record Found</h1>"; ELSE:

	WHILE($p_result = mysqli_fetch_array($product)){
		$item_name=$p_result['item'];
		$stock_code = $p_result['stock_code'];
		$trans_code = $p_result['transaction_code'];

		// Get Number of transaction and details from activity log
		$items = mysqli_query($link, "SELECT description, transaction_type, qty, price_from, price_to, transaction_date, transaction_code FROM ".DB_PREFIX."inventory_activity_log WHERE `stock_code` = '".$stock_code."' ".$datecond2."");


		$product_name .= '

		<thead>
			<tr class="head">
				<th colspan="7"><h3>Item Name: <b>'.$item_name.'</b></h3></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="edit_td">DATE</td>
				<td class="edit_td">TRANSACTION TYPE</td>
				<td class="edit_td" style="text-align:center">QUANTITY IN</td>
				<td class="edit_td" style="text-align:center">QUANTITY OUT</td>
				<td class="edit_td" style="text-align:center">PRICE FROM</td>
				<td class="edit_td" style="text-align:center">PRICE TO</td>
				<td class="edit_td" style="text-align:center">SALE</td>
			</tr>';
			
			$total_sales = 0;
			WHILE ($activity = mysqli_fetch_array($items)){ 
				$date = $activity['transaction_date'];
				$transaction_description = $activity['description'];
				$transaction_type = $activity['transaction_type'];
				
				$qty = $activity['qty'];
				
				IF($transaction_type == 1 || $transaction_type == 2): $var1 = $qty; ENDIF;
				IF($transaction_type == 3): $var2 = $qty; ENDIF;
				IF($transaction_type == 4): $var3 = number_format($price_from,2,'.',','); ENDIF;
				IF($transaction_type == 4): $var4 = number_format($price_to,2,'.',','); ENDIF;
				IF($transaction_type == 3): $var5 = number_format($sales['sales'],2,'.',','); ENDIF;
				
				$price_from = $activity['price_from'];
				$price_to = $activity['price_to'];
				$transcode = $activity['transaction_code'];
				$sales = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM ".DB_PREFIX."sales WHERE `transaction_code` = '".$transcode."'"));
				$total_sales += $sales['sales'];
				
				$transactions .=  
				'<tr>
					<td class="edit_td">'.$date.'</td>
					<td class="edit_td" style="text-align:left">'.$transaction_description.'</td>
					<td class="edit_td" style="text-align:center">'.$var1.'</td>
					<td class="edit_td" style="text-align:center">'.$var2.'</td>
					<td class="edit_td" style="text-align:right">'.$var3.'</td>
					<td class="edit_td" style="text-align:right">'.$var4.'</td>
					<td class="edit_td" style="text-align:right">'.$var5.'</td>
				</tr>';
			}
		
			$total_sales .= 
			'<tr>
				<td colspan="6">TOTAL SALES :</td>
				<td><b>'.number_format($total_sales,2,'.',',').'</b></td>
			</tr>
			<tr>
				<td colspan="7"></td>
			</tr>
		</tbody>';
		} 
	ENDIF;	


		
$html = $inventory_report_date.
	'
	<h2>College Store : '.$_SESSION['collegename'].'</h2>
	<table style="text-align:left;" class="CSSTableGenerator">
	
	

'.$product_name.$transactions.$total_sales;

//==============================================================

include("includes/mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->SetDisplayMode('fullpage');

$mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list

// LOAD a stylesheet
$stylesheet = file_get_contents('mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->WriteHTML($html,2);

$mpdf->Output('mpdf.pdf','I');
exit;
//==============================================================

?>