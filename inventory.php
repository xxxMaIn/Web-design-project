<?php 
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS	
	
	IF(!isset($_SESSION['inventorytab'])): $_SESSION['inventorytab']="manageinvent"; ENDIF;
	
	// PAGINATION
	$page = 0;
	foreach($_GET as $key => $value) { $data[$key] = filter($value); } // Filter Get data
		
	$rpp = 20; // results per page
	$adjacents = 4;
		
	IF(isset($data)):
		$page = intval($data["page"]);
		if($page<=0) $page = 1;
	ENDIF;

	$reload = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include('includes/headtag'); ?>
		<script type="text/javascript">
			var popupWindow=null;
			function child_open(){ 
				popupWindow =window.open('inventory_printreport.php',"_blank","directories=no, status=no, menubar=no, scrollbars=yes, resizable=no");
			}
		</script>
	</head>
	<body>
		
		<!-- Wrapper -->
			<div id="wrapper">
			
			
				<!-- Header -->
				<?php include('includes/header'); ?>

				<div id="main">
					<section id="registered" class="main special">

						<header class="major">
							<h2>Inventory</h2>
						</header>
						<p><?php if(!empty($_SESSION['pricemessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['pricemessage']; echo "</div>"; } unset($_SESSION['pricemessage']); // Display message  ?></p>
						<p><?php if(!empty($_SESSION['colmessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['colmessage']; echo "</div>"; } unset($_SESSION['colmessage']); // Display message  ?></p>
						<p><?php if(!empty($_SESSION['prodmessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['prodmessage']; echo "</div>"; } unset($_SESSION['prodmessage']); // Display message  ?></p>
						<p><?php if(!empty($_SESSION['itemmessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['itemmessage']; echo "</div>"; } unset($_SESSION['itemmessage']); // Display message  ?></p>
						<p><?php if(!empty($_SESSION['salemessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['salemessage']; echo "</div>"; } unset($_SESSION['salemessage']); // Display message  ?></p>
						<form name="additemform" action="inventory_selectcollege.php" method="post">
						
							<table style="text-align:left;">
								<thead>
									<tr>
										<th>College</th>
										<th>Start Date</th>
										<th>End date</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<?php 
												$college= mysqli_query($link, "select ID, name from ".DB_PREFIX."colleges_category");
												echo '<select name="ITEM" id="ITEM">';
													echo '<option value="">-Select College-</option>';
													echo '<option value="99999">All Colleges</option>';
													while($collcat= mysqli_fetch_assoc($college)){
														echo '<option value="'.$collcat['ID'].'">';
														echo $collcat['name'];
														echo'</option>';
													}
												echo'</select>';
											?>
										</td>
										<td>
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
										</td>
										<td>
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
										</td>
										<td><input class="special" name="select_col" type="submit" value="Select" /></td>
									</tr>
								</tbody>
							</table>				
						</form>
						<div style="clear:both"></div>
						<hr />

						<?php 
							If (!isset($_SESSION['college'])):
						?>
							<h6>Please Select College</h6>
						<?php
							ELSE:
							IF ($_SESSION['college']==99999): $condition = "WHERE status = 1"; $app = "AND"; ELSE: $condition = "WHERE `college_ID` = '".$_SESSION['college']."' AND status = 1"; $app = "AND"; ENDIF;
							IF ((isset($_SESSION['startdate']) && $_SESSION['startdate']!="") && (isset($_SESSION['enddate']) && $_SESSION['enddate']!="")): 
								$datecond = "AND `last_update` BETWEEN '".$_SESSION['startdate']."' AND '".$_SESSION['enddate']."' ORDER BY `id` ASC"; 
								$datefromcover = $_SESSION['startdate'];
								$datetocover = $_SESSION['enddate'];
							ELSE: 
								$datecond = ""; 
								$datefromcover = date('m d, Y');
								$datetocover = date('m d, Y');
							ENDIF;
						?>
						<header class="major">
							<h3>College Store: <?php echo $_SESSION['collegename']; ?></h3>
						</header>
						<nav>						
							<a id="invent" onclick='$("#inventory").show("slow");$("#sales").hide();$("#toorder").hide();$("#additem").hide();$("#addprod").hide();$("#editprice").hide()' href="javascript:void(0)">Inventory</a> <span style="color:#FF0000;font-weight:bold">|</span> 
							<a id="sale" onclick='$("#inventory").hide();$("#sales").show("slow");$("#toorder").hide();$("#additem").hide();$("#addprod").hide();$("#editprice").hide()' href="javascript:void(0)">Sell Items</a> <span style="color:#FF0000;font-weight:bold">|</span> 
							<a id="order" onclick='$("#inventory").hide();$("#sales").hide();$("#toorder").show("slow");$("#additem").hide();$("#addprod").hide();$("#editprice").hide()' href="javascript:void(0)">To Order</a> <span style="color:#FF0000;font-weight:bold">|</span> 
							<a id="items" onclick='$("#inventory").hide();$("#sales").hide();$("#toorder").hide();$("#additem").show("slow");$("#addprod").hide();$("#editprice").hide()' href="javascript:void(0)">Add Items</a> <span style="color:#FF0000;font-weight:bold">|</span> 
							<a id="products" onclick='$("#inventory").hide();$("#sales").hide();$("#toorder").h;$("#additem").hide();$("#addprod").show("slow");$("#editprice").hide()' href="javascript:void(0)">Add Products</a> <span style="color:#FF0000;font-weight:bold">|</span> 
							<a id="prices" onclick='$("#inventory").hide();$("#sales").hide();$("#toorder").hide();$("#additem").hide();$("#addprod").hide();$("#editprice").show("slow")' href="javascript:void(0)">Edit Price</a> <span style="color:#FF0000;font-weight:bold">|</span> 
							<a id="report" href="javascript:void(0)" onclick="javascript:child_open()"><span style="color:#0000FF">Generate Inventory Report</span> </a>
						</nav>
						<br />
						<br />
						
						<!-- INVENTORY -->
						<div class="table-wrapper" id="inventory" style="display:<?php IF($_SESSION['inventorytab']=="manageinvent"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<table style="text-align:left;">
								<thead>
									<tr>
										<th>Date</th>
										<th>Item</th>
										<th>Qty. Left</th>
										<th>Qty. Sold as of <?php echo $datefromcover." to ".$datetocover; ?></th>
										<th>Base Price</th>
										<th>Markup Price</th>
										<!--<th>Sales</th>-->
									</tr>
								</thead>
								<tbody>
									<?php
									
										$sql = mysqli_query($link, "select * from ".DB_PREFIX."inventory ".$condition." ".$datecond."");
										// Begin pagination variable
										// count total number of appropriate listings: --- Pagination
										$tcount = mysqli_num_rows($sql);
										
										// count number of pages:
										$tpages = ($tcount) ? ceil($tcount/$rpp) : 1; // total pages, last page number
										
										$count = 0;
										$i = ($page-1)*$rpp;
										// End pagination variable
										
										IF($tcount <= $rpp): $rpp = $tcount; ENDIF;
										WHILE(($count<$rpp) && ($i<$tcount)) {
										mysqli_data_seek($sql,$i);
										$row = mysqli_fetch_array($sql);

										//while($row = mysqli_fetch_array($sql)){
											$trans_code = $row['stock_code'];
											$id=$row['id'];
											$item=$row['item'];
											$qtyleft=$row['qtyleft'];
											$base_price=$row['base_price'];
											$markup_price=$row['markup_price'];
											$date = $row['last_update'];
											$sales = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM ".DB_PREFIX."sales WHERE stock_code='".$trans_code."' ORDER BY id DESC"));
									?>
									<tr>
										<td><?php echo $date." - ".$tcount; ?></td>
										<td><?php echo $item; ?></td>
										<td style="text-align:center"><?php echo $qtyleft; ?></td>
										<td style="text-align:center"><?php echo $sales['qty']; ?></td>
										<td style="text-align:right">₱<?php echo number_format($base_price,2,".",","); ?></td>
										<td style="text-align:right">₱<?php echo number_format($markup_price,2,".",","); ?></td>
										<!--<td style="text-align:right"><?php // echo number_format($sales['sales'],2,".",","); ?></td>-->
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
						
						<!-- SALES -->
						<div class="table-wrapper" id="sales" style="display:<?php IF($_SESSION['inventorytab']=="managesale"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<p><?php if(!empty($_SESSION['salemessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['salemessage']; echo "</div>"; } unset($_SESSION['salemessage']); // Display message  ?></p>
							<div style="width:400px;margin:0 auto">
								<form action="inventory_sale.php" method="post">
									<div>
										Product name
										<?php
											$name= mysqli_query($link, "select * from ".DB_PREFIX."inventory ".$condition."");
											
											echo '<select name="ITEM" id="user" class="textfield1">';
											echo '<option value="">-Select-</option>';
											while($res= mysqli_fetch_assoc($name)){
												echo '<option value="'.$res['transaction_code'].'">';
												echo $res['item']." -- Qty on Hand: ".$res['qtyleft'];
												echo'</option>';
											}
											echo'</select>';
										?>
									</div>
									<div style="margin-top: 10px;">Quantity<input style="width:100px;margin:0 auto" name="quantity" type="text" value="" /></div>
									<div style="margin-top: 10px;"><input class="special" name="sellitem" type="submit" value="Sell" /></div>
								</form>
							</div>
						</div>
						
						<!-- ORDER -->
						<div class="table-wrapper" id="toorder" style="display:<?php IF($_SESSION['inventorytab']=="manageorder"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<div style="width:700px;margin:0 auto">
								<table style="text-align:left;">
									<thead>
										<tr>
											<th>Item Name</th>
											<th>Qty. Left</th>
											<th>College Store</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$CRITICAL=10;
											$criticalstock=mysqli_query($link, "SELECT * FROM ".DB_PREFIX."inventory ".$condition." ".$app." qtyleft<='$CRITICAL'");
											while($restock=mysqli_fetch_array($criticalstock)){
												$collegeID = $restock['college_ID']; 
												$college_cat = mysqli_fetch_array(mysqli_query($link, "SELECT name FROM ".DB_PREFIX."colleges_category WHERE `ID` = '".$collegeID."'"));
										?>
										<tr>
											<th><?php echo $restock['item']; ?></th>
											<th><?php echo $restock['qtyleft']; ?></th>
											<th><?php echo $college_cat['name']; ?></th>
										</tr>
										<?php
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
						
						<!-- ADD ITEMS -->
						<div class="table-wrapper" id="additem" style="display:<?php IF($_SESSION['inventorytab']=="manageitems"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<p><?php if(!empty($_SESSION['itemmessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['itemmessage']; echo "</div>"; } unset($_SESSION['itemmessage']); // Display message  ?></p>
							<form name="additemform" action="inventory_updateproduct.php" method="post">
								<div style="width:200px;margin:0 auto">
									<div>
										Product name
										<?php 
											$name= mysqli_query($link, "SELECT * FROM ".DB_PREFIX."inventory ".$condition."");
											echo '<select name="ITEM" id="ITEM">';
												echo '<option value="">-Select-</option>';
												while($res= mysqli_fetch_assoc($name)){
													echo '<option value="'.$res['transaction_code'].'">';
													echo $res['item'];
													echo'</option>';
												}
											echo'</select>';
										?>
									</div>
									<div style="margin-top: 10px;">Number of Item To Add<input name="itemnumber" type="text" /></div>
									<div style="margin-top: 10px;"><input class="special" name="add_item" type="submit" value="Add Item" /></div>
								</div>
							</form>
						</div>
						
						<!-- ADD PRODUCTS -->
						<div class="table-wrapper" id="addprod" style="display:<?php IF($_SESSION['inventorytab']=="manageproduct"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<p><?php if(!empty($_SESSION['prodmessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['prodmessage']; echo "</div>"; } unset($_SESSION['prodmessage']); // Display message  ?></p>
							<div style="width:200px;margin:0 auto">
								<form action="inventory_saveproduct.php" method="post">
									<div>Product name<input name="proname" type="text" /></div>
									<div style="margin-top: 10px;">Base Price<input name="baseprice" type="text" /></div>
									<div style="margin-top: 10px;">Markup Price<input name="markupprice" type="text" /></div>
									<div style="margin-top: 10px;">Quantity<input name="qty" type="text" /></div>
									<div style="margin-top: 10px;"><input class="special" name="addprod" type="submit" value="Add Product" /></div>
								</form>
							</div>
						</div>
						
						<!-- EDIT PRICE -->
						<div class="table-wrapper" id="editprice" style="display:<?php IF($_SESSION['inventorytab']=="manageprice"): echo "block"; ELSE: echo "none"; ENDIF; ?>">
							<p><?php if(!empty($_SESSION['pricemessage'])) { echo "<div class=\"msg\">"; echo $_SESSION['pricemessage']; echo "</div>"; } unset($_SESSION['pricemessage']); // Display message  ?></p>
							<div style="width:200px;margin:0 auto">
								<form action="inventory_updateprice.php" method="post">
									<div>
										Product name
										<?php
											$name= mysqli_query($link, "select * from ".DB_PREFIX."inventory ".$condition."");
											
											echo '<select name="ITEM" id="user" class="textfield1">';
											echo '<option value="">-Select-</option>';
											while($res= mysqli_fetch_assoc($name)){
												echo '<option value="'.$res['transaction_code'].'">';
												echo $res['item'];
												echo'</option>';
											}
											echo'</select>';
										?>
									</div>
									<div style="margin-top: 10px;">Base Price<input name="baseprice" type="text" value="" /></div>
									<div style="margin-top: 10px;">Markup Price<input name="markupprice" type="text" value="" /></div>
									<div style="margin-top: 10px;"><input class="special" name="updateprice" type="submit" value="Update" /></div>
								</form>
							</div>
						</div>
						<?php ENDIF; ?>
					</section>
				</div>
				<!-- Footer -->
					<footer id="footer">
						<p class="copyright">&copy; Attendance, Event and Store Inventory System : <a href="your link here">your link here</a>.</p>
					</footer>
			</div>
			<?php include('includes/footertag'); ?>
	</body>
</html>