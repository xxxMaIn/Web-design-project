<?php
	include 'settings/connect.php';
	if(session_id() == '') { session_start(); } // START SESSIONS
		
		// Begin Login
	
	foreach($_POST as $key => $value) { $data[$key] = filter($value); }
	
	// Begin log process
	if(isset($_POST) && array_key_exists('Login',$_POST)){
		// Filter POST Variables
	
		$user_name = $data['username'];
		$pass = $data['password'];
	
		// Can use email, lastname or username to login
		if (strpos($user_name,'@') === false) {
			$user_cond = "(username='$user_name' OR last_name='$user_name')"; 
		}else{
			$user_cond = "email='$user_name'"; 
		}
	
		$result = mysqli_query($link, "SELECT `user_ID`,`password`,`first_name`,`username`,`email`,`middle_name`,`last_name`,`user_level`,`approval` FROM ".DB_PREFIX."system_users WHERE $user_cond") or die (mysqli_error()); 
		$num = mysqli_num_rows($result);

		// Match row found with more than 1 results  - the user is authenticated. 
		if ( $num > 0 ) { 
			list($id,$pwd,$first_name,$username,$email,$middle_name,$last_name,$userlevel,$approved) = mysqli_fetch_row($result);	
			if(!$approved) {
				$_SESSION['error_msg'] = "Account not activated. Please check your email for activation code or contact the administrator";
				header("Location: index.php");
			}

			//check against salt
			if ($pwd === PwdHash($pass,substr($pwd,0,9))) { 
				if($_SESSION['error_msg'] == ""){ 
					// this sets session and logs user in
					session_regenerate_id (true); //prevent against session fixation attacks.
					
					$full_name = $first_name." ".$middle_name." ".$last_name;

				   // this sets variables in the session
					$_SESSION['user_id']= $id;
					$_SESSION['full_name'] = $full_name;
					$_SESSION['user_level'] = $userlevel;
					$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
					
					//update the timestamp and key for cookie
					$stamp = time();
					$ckey = GenKey();
					mysqli_query($link, "update ".DB_PREFIX."system_users set `ctime`='$stamp', `ckey` = '$ckey' where user_ID='$id'") or die(mysqli_error());
				
					//set a cookie 
				   if(isset($_POST['remember'])){
						setcookie("user_id", $_SESSION['user_id'], time()+60*60*24*COOKIE_TIME_OUT, "/");
						setcookie("user_key", sha1($ckey), time()+60*60*24*COOKIE_TIME_OUT, "/");
						setcookie("user_name",$_SESSION['user_name'], time()+60*60*24*COOKIE_TIME_OUT, "/");
					}
					header("Location: index.php");
				}
			}else{ $_SESSION['error_msg'] = "Invalid Login. Please try again with correct user name and password."; header("Location: index.php"); }
		}else{ $_SESSION['error_msg'] = "Error - Invalid login. No such user exists"; header("Location: index.php"); }
	} // End log process
?>