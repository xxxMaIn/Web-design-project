<?php
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include("includes/headtag"); ?>
	</head>
	<body>

		<!-- Wrapper -->
			<div id="wrapper">
				<!-- Header -->
				<?php include('includes/header'); ?>
				
				<!-- Main Body -->
					<div id="main">
						<!-- Events Section -->
						<section id="events" class="main special">
							<h2>Student Photo Upload</h2>
							<form action="upload.php" method="post" enctype="multipart/form-data">
								<input type="file" name="photofile" />
								<button type="submit" name="doUpload">Upload</button>
							</form>
							<br /><br />
							<?php
								if(isset($_GET['success']))
								{
									?>
									<label>File Uploaded Successfully...  <a href="view.php">click here to view file.</a></label>
									<?php
								}
								else if(isset($_GET['fail']))
								{
									?>
									<label>Problem While File Uploading!, invalid format or too large</label>
									<?php
								}
								else
								{
									?>
									<label>Try to upload image files(JPG, PNG)</label>
									<?php
								}
							?>
							<input class="special" type="button" value="Cancel" onclick="window.history.back()" /> 
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