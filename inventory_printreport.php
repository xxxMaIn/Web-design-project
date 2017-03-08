<?php 
	include('../settings/connect.php');
	if(session_id() == '') { page_protect(); } // START SESSIONS

		IF ($_SESSION['college']==99999): $condition = "WHERE status = 1"; $app = "AND"; ELSE: $condition = "WHERE `college_ID` = '".$_SESSION['college']."' AND status = 1"; $app = "AND"; ENDIF;
		IF (!empty($_SESSION['startdate']) && !empty($_SESSION['enddate'])): $datecond = "AND `last_update` BETWEEN '".$_SESSION['startdate']."' AND '".$_SESSION['enddate']."' ORDER BY `id` ASC"; $datecond2 = "AND `transaction_date` BETWEEN '".$_SESSION['startdate']."' AND '".$_SESSION['enddate']."' ORDER BY `id` ASC"; ELSE: $datecond = ""; $datecond2 = ""; ENDIF;

?>

<!DOCTYPE HTML>
<html>
	<head>
		<?php include('includes/headtag'); ?>
	</head>

	<body bgcolor="#dedede">
		<!-- Wrapper -->
			<div id="wrapper">
				<br />
				<div id="gendoc">

					<?php IF (!empty($_SESSION['startdate']) && !empty($_SESSION['enddate'])): ?>
					<h2>Inventory Report from <?php echo $_SESSION['startdate']." TO ".$_SESSION['enddate']; ?></h2>
					<?php ENDIF; ?>
					<h2>College Store : <?php echo $_SESSION['collegename']; ?></h2>

					<table style="text-align:left;" class="CSSTableGenerator">
								
						<?php
							// Get active product
							$product = mysqli_query($link, "SELECT * FROM ".DB_PREFIX."inventory ".$condition." ".$datecond."");
										
							$results = mysqli_num_rows($product);
							IF ($results <= 0): echo "<h1>No Record Found</h1>"; ELSE:
										
							WHILE($p_result = mysqli_fetch_array($product)){
								$item_name=$p_result['item'];
								$stock_code = $p_result['stock_code'];
								$trans_code = $p_result['transaction_code'];

								// Get Number of transaction and details from activity log
								$items = mysqli_query($link, "SELECT description, transaction_type, capital, qty, base_price_from, base_price_to, markup_price_from, markup_price_to, transaction_date, transaction_code FROM ".DB_PREFIX."inventory_activity_log WHERE `stock_code` = '".$stock_code."' ".$datecond2."");?>
						<thead>
							<tr class="head">
								<th colspan="11" style="vertical-align: baseline;font-size:20px">Item Name: <b><?php echo $item_name; ?></b></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="edit_td" style="font-size:11px">DATE</td>
								<td class="edit_td" style="font-size:11px">TRANSACTION TYPE</td>
								<td class="edit_td" style="text-align:center;font-size:11px">QUANTITY IN HAND</td>
								<td class="edit_td" style="text-align:center;font-size:11px">QUANTITY IN</td>
								<td class="edit_td" style="text-align:center;font-size:11px">QUANTITY OUT</td>
								<td class="edit_td" style="text-align:center;font-size:11px">CURRENT BASE PRICE</td>
								<td class="edit_td" style="text-align:center;font-size:11px">NEW BASE PRICE</td>
								<td class="edit_td" style="text-align:center;font-size:11px">CAPITAL (base price x capital)</td>
								<td class="edit_td" style="text-align:center;font-size:11px">CURRENT MARKUP PRICE</td>
								<td class="edit_td" style="text-align:center;font-size:11px">NEW MARKUP PRICE</td>
								<td class="edit_td" style="text-align:center;font-size:11px">SALE</td>
							</tr>
										
						<?php
							$total_sales = 0;
							$total_capital = 0;
							$total_income = 0;
							WHILE ($activity = mysqli_fetch_array($items)){ 
								$date = $activity['transaction_date'];
								$transaction_description = $activity['description'];
								$transaction_type = $activity['transaction_type'];
								$qty = $activity['qty'];
								$baseprice_from = $activity['base_price_from'];
								$baseprice_to = $activity['base_price_to'];
								$markupprice_from = $activity['markup_price_from'];
								$markupprice_to = $activity['markup_price_to'];
								$transcode = $activity['transaction_code'];
								$capital = $activity['capital'];
								IF($transaction_type == 1 || 2): $total_capital += $capital; ENDIF;
								IF($transaction_type == 3 ): $total_income += $qty * $markupprice_from; ENDIF;
								$sales = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM ".DB_PREFIX."sales WHERE `transaction_code` = '".$transcode."'"));
								$iteminhand = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM ".DB_PREFIX."inventory WHERE `transaction_code` = '".$transcode."'"));
								$total_sales += $sales['sales'];?>
								
							<tr>
								<td class="edit_td" nowrap><?php echo $date; ?></td>
								<td class="edit_td" style="text-align:left" nowrap><?php echo $transaction_description; ?></td>
								<td class="edit_td" style="text-align:center" nowrap><?php IF($transaction_type == 1 || 2 || 3): echo $iteminhand['qtyleft']; ENDIF; ?></td>
								<td class="edit_td" style="text-align:center" nowrap><?php IF($transaction_type == 2): echo $qty; ENDIF; ?></td>
								<td class="edit_td" style="text-align:center" nowrap><?php IF($transaction_type == 3): echo $qty; ENDIF; ?></td>
								<td class="edit_td" style="text-align:right" nowrap><?php IF($transaction_type == 4 || 1 || 3 || 2): echo "₱ ".number_format($baseprice_from,2,'.',','); ENDIF; ?></td>
								<td class="edit_td" style="text-align:right" nowrap><?php IF($transaction_type == 4): echo "₱ ".number_format($baseprice_to,2,'.',','); ENDIF; ?></td>
								<td class="edit_td" style="text-align:right" nowrap><?php IF($transaction_type == 1 || 2): echo "₱ ".number_format($capital,2,'.',','); ENDIF; ?></td>
								<td class="edit_td" style="text-align:right" nowrap><?php IF($transaction_type == 4 || 1 || 3 || 2): echo "₱ ".number_format($markupprice_from,2,'.',','); ENDIF; ?></td>
								<td class="edit_td" style="text-align:right" nowrap><?php IF($transaction_type == 4): echo "₱ ".number_format($markupprice_to,2,'.',','); ENDIF; ?></td>
								<td class="edit_td" style="text-align:right" nowrap><?php IF($transaction_type == 3): echo "₱ ".number_format($sales['sales'],2,'.',','); ENDIF; ?></td>
							</tr>
							<?php } ?>
							<tr>
								<td style="color:#000;font-weight:bold;text-align:right;" colspan="10">TOTAL SALES </td>
								<td style="color:#000;font-weight:bold;text-align:right;" nowrap><?php echo "₱ ".number_format($total_sales,2,'.',','); ?></td>
							</tr>
							<tr>
								<td style="color:#000;font-weight:bold;text-align:right;" colspan="10">TOTAL CAPITAL </td>
								<td style="color:#000;font-weight:bold;text-align:right;" nowrap><?php echo "₱ ".number_format($total_capital,2,'.',','); ?></td>
							</tr>
							<tr>
								<td style="color:#000;font-weight:bold;text-align:right;" colspan="10">TOTAL INCOME (sales - capital) </td>
								<td style="color:#000;font-weight:bold;text-align:right;" nowrap><?php echo "₱ ".number_format($total_income,2,'.',','); ?></td>
							</tr>
							<tr>
								<td colspan="11" style="background:none;"></td>
							</tr>
						</tbody>
						<?php } ?>
					</table>
					<?php ENDIF; ?>
				</div>
				<br />
				<div style="float:left;margin-right:50px">
					<button class="special" onclick="javascript:window.print()">Print</button>
				</div>
				<div style="float:left">
					<div id="editor"></div>
					<button id="cmd" class="special">generate PDF</button>
				</div>
				<br /><br /><br />
			</div>

		<?php include('includes/footertag'); ?>
			
			<script type="text/javascript">
			
				var doc = new jsPDF();
				var specialElementHandlers = {
					'#editor': function (element, renderer) {
						return true;
					}
				};

				$('#cmd').click(function () {
					doc.fromHTML($('#gendoc').html(), 30, 30, {
						'width': 170,
							'elementHandlers': specialElementHandlers
					});
					doc.save('inventory_report.pdf');
				});
			</script>
	</body>
</html>
