<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');

		function update_img($imgk, $dst_path){
			if(			file_exists($_FILES[$imgk]['tmp_name']) &&
				 is_uploaded_file($_FILES[$imgk]['tmp_name']) && 
				 								 ($_FILES[$imgk]['size'] < 4194304) ){ // if image file and is less than 4 MB
					 
				$image = null;
							if($_FILES[$imgk]["type"] == 'image/png'){		$image = imagecreatefrompng( $_FILES[$imgk]["tmp_name"]);
				}else if($_FILES[$imgk]["type"] == 'image/jpeg'){		$image = imagecreatefromjpeg($_FILES[$imgk]["tmp_name"]);
				}
				
				if($image){
					//$imgResized = imagescale($image , 200, 300);
					imagepng($image, $dst_path);
				}
			}
		}

    if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->id == SUPER_ADMIN_ID) {
			
			if(isset($_FILES['login_box_img']) ){
				update_img('login_box_img', '../../assets/images/login_box.png');
			}
			
			if(isset($_FILES['login_page_img']) ){	// only updates
				update_img('login_page_img', '../../assets/images/login_page.png');
			}
			
			if(isset($_POST['custom_css'])){
				file_put_contents('../../assets/dist/css/custom.css', $_POST['custom_css']);
			}
			
			if(isset($_POST['map_css'])){
				file_put_contents('../../assets/dist/css/map.css', $_POST['map_css']);
			}
    }

    header('Location: ../settings.php');
?>
