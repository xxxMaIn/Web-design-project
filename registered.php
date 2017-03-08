<?php 
	include 'settings/connect.php';
	if(session_id() == '') { page_protect(); } // Session start with redirection to login section
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>School Record System</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="assets/css/main.css" />
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
	</head>
	<body>

		<!-- Wrapper -->
			<div id="wrapper">
				<!-- Navigations -->
					<nav id="nav">
						<ul>
							<li><a href="index.php" class="active" onclick='$("#events").hide();$("#profile").show("slow")'>Student Profile</a></li>
							<li><a href="index.php#events" onclick='$("#profile").hide();$("#events").show("slow")'>Events</a></li>
							<?php if(isset($_SESSION['user_id'])): ?> 
							<li><a href="logout.php">Logout</a></li>
							<?php if(Admin()): ?>
							<li><a href="backend/index.php">Administration</a></li>
							<?php endif; endif; ?>
						</ul>
					</nav>

				<!-- Main Body -->
					<div id="main">
						
						<section id="registered" class="main special">
							<header class="major">
								<h2>New Student has been added</h2>
							</header>

							<div id="home_body" style="padding:0 70px">
								<h3>Continue to register another student?</h3>
								<button type="button" onclick="parent.location='register.php'" class="green-button">Yes</button>
								<button type="button" class="clean-gray" onclick="parent.location='index.php'">No</button>
							</div>
							
							
							
						</section>
					</div>

				<!-- Footer -->
					<footer id="footer">
						<p class="copyright">&copy; Attendance, Event and Store Inventory System : <a href="your link here">your link here</a>.</p>
					</footer>
			</div>
		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrollex.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>

	</body>
</html>