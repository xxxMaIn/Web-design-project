<?php
	include '../settings/connect.php';
	if(session_id() == '') { page_protect(); } // START SESSIONS
	
		$student_ID = $_SESSION['currentSID'];
		$getID = mysqli_fetch_array(mysqli_query($link, "SELECT user_ID, last_name FROM ".DB_PREFIX."system_users WHERE ID_number='".$student_ID."'" ));
		 
		 
		// Photo
		if(isset($_POST) && array_key_exists('doUpload',$_POST)){
		
			// file needs to be jpg,gif,bmp,x-png and 400 KB max
			if (($_FILES["photofile"]["type"] == "image/jpeg" || $_FILES["photofile"]["type"] == "image/pjpeg" || $_FILES["photofile"]["type"] == "image/gif" || $_FILES["photofile"]["type"] == "image/png" || $_FILES["photofile"]["type"] == "image/x-png") && ($_FILES["photofile"]["size"] < 4000000))
			{
				// some settings
				$max_upload_width = 360;
				$max_upload_height = 360;
				
				// if uploaded image was JPG/JPEG
				if($_FILES["photofile"]["type"] == "image/jpeg" || $_FILES["photofile"]["type"] == "image/pjpeg"){	
					$image_source = imagecreatefromjpeg($_FILES["photofile"]["tmp_name"]);
				}		
				// if uploaded image was GIF
				if($_FILES["photofile"]["type"] == "image/gif"){	
					$image_source = imagecreatefromgif($_FILES["photofile"]["tmp_name"]);
				}	
				// BMP doesn't seem to be supported so remove it form above image type test (reject bmps)	
				// if uploaded image was BMP
				if($_FILES["photofile"]["type"] == "image/bmp"){	
					$image_source = imagecreatefromwbmp($_FILES["photofile"]["tmp_name"]);
				}			
				
				// if uploaded image was PNG
				if($_FILES["photofile"]["type"] == "image/png"){
					$image_source = imagecreatefrompng($_FILES["photofile"]["tmp_name"]);
				}
		
				// if uploaded image was PNG
				if($_FILES["photofile"]["type"] == "image/x-png"){
					$image_source = imagecreatefrompng($_FILES["photofile"]["tmp_name"]);
				}

				//Delete old file
				$query = 'SELECT `photo` FROM '.DB_PREFIX.'system_users WHERE ID_number="'.$student_ID.'"';
				$result = mysqli_query($link, $query) or die("Failed: does the table exist?");
				while($photofile_result=mysqli_fetch_array($result, MYSQL_BOTH))
				{
					if ($photofile_result['photo'] == '') {
					}
					else {
					unlink("../images/users/".$getID['user_ID']."/".$photofile_result['photo']);
					}
				}
				
				//Test if path exist
				$path = "../images/users/".$getID['user_ID'];
				$path_db = "../".$getID['user_ID'];
				if (!is_dir($path) || !file_exists($path)) {
				//No, create it
				mkdir($path, 0777, true);
				}
				
				$remote_file = $path."/".$_FILES["photofile"]["name"];
				$remote_filedb = $_FILES["photofile"]["name"];
				imagejpeg($image_source,$remote_file,100);
				chmod($remote_file,0644);
				
				// get width and height of original image
				list($image_width, $image_height) = getimagesize($remote_file);
			
				if($image_width>$max_upload_width || $image_height >$max_upload_height){
					$proportions = $image_width/$image_height;
					
					if($image_width>$image_height){
						$new_width = $max_upload_width;
						$new_height = round($max_upload_width/$proportions);
					}		
					else{
						$new_height = $max_upload_height;
						$new_width = round($max_upload_height*$proportions);
					}		
					
					$new_image = imagecreatetruecolor($new_width , $new_height);
					$image_source = imagecreatefromjpeg($remote_file);
					
					imagecopyresampled($new_image, $image_source, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height);
					imagejpeg($new_image,$remote_file,70);
					
					imagedestroy($new_image);
				}
				
				imagedestroy($image_source);
				
				mysqli_query($link, "UPDATE ".DB_PREFIX."system_users SET `photo` = '$remote_filedb'  WHERE ID_number='".$student_ID."'") or die(mysql_error());
				$_SESSION['messages'] = "Photo sucessfully uploaded.";
				header("location: student_edit.php?refer=".$student_ID."");
			}
			else{
				?>
					<script>
					alert('error while uploading file!, invalid format or too large');
					window.location.href='photo_upload.php?fail';
					</script>
				<?php
			
			}
		} 
	
	
?>