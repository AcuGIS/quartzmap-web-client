<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/map.php');
		require('../class/app.php');
		
function unzip_me($zipname){
	$ext_dir = tempnam('/tmp', 'upload');
	unlink($ext_dir);
	mkdir($ext_dir);

	$zip = new ZipArchive;
	$res = $zip->open($zipname);
	if ($res === TRUE) {
		$zip->extractTo($ext_dir);
		$zip->close();
	} else {
		echo 'Error: Failed to open'.$zipname;
	}
	return $ext_dir;
}

function find_html_dir($unzip_dir, $name){
	
	if(is_file($unzip_dir.'/index.html')){
		$html_dir = $unzip_dir;
	}else if(is_file($unzip_dir.'/'.$name.'/index.html')){
		$html_dir = $unzip_dir.'/'.$name;
	}else{
		echo 'Error: index.html not found';
		$html_dir = null;
	}
	return $html_dir;
}

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    	$obj			= new map_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			
				if(($id > 0) && !$obj->isOwnedByUs($id)){
					$result = ['success' => false, 'message' => 'Action not allowed!'];
		
				}else if(isset($_POST['save'])) {
            $newId = 0;
						
            if($id) { // update
              
							$newId = $obj->update($_POST) ? $id : 0;
							if($newId > 0){
								$html_dir = APPS_DIR.'/'.$newId;
								
								
								if(!empty($_POST['qgis_remove'])){
									unlink($html_dir.'/proxy_qgis.php');
									unlink(DATA_DIR.'/'.$newId.'/'.$_POST['qgis_remove']);
									
									#remove any uploaded .gpkg files
									if(is_file($html_dir.'/qgis_files.txt')){
										$qgis_files = explode("\n", file_get_contents($html_dir.'/qgis_files.txt'));
										foreach($qgis_files as $qf){
											if(str_ends_with($qf, '.gpkg') && is_file(DATA_DIR.'/'.$newId.'/'.$qf)){
												unlink(DATA_DIR.'/'.$newId.'/'.$qf);
											}
										}
										unlink($html_dir.'/qgis_files.txt');
									}
									unset($_POST['qgis_layout']);
								}
								App::updateIndex($_POST, $html_dir, DATA_DIR, APPS_DIR);
							}
							
            } else if(!empty($_POST['app']) || !empty($_FILES['archive'])){ // insert

              $newId = $obj->create($_POST);
							
							if(!is_dir(CACHE_DIR.'/'.$newId)){
								mkdir(CACHE_DIR.'/'.$newId, 0770);
							}
							
							if($newId > 0){
								$upload_dir = App::upload_dir($_SESSION[SESS_USR_KEY]->ftp_user);
								$html_dir = null;
								$unzip_dir = null;
								// html dir can be in /var/www/upload or in /tmp, if its an upload
								if(isset($_POST['app'])){
									$html_dir = $upload_dir.'/'.$_POST['app'];
								}else if(!empty($_FILES["archive"]["tmp_name"])){	// if we have uploaded file
									
									$unzip_dir = unzip_me($_FILES["archive"]["tmp_name"]);
									$name = basename($_FILES["archive"]["name"]);
									$name = explode('.', $name)[0];
									
									$html_dir = find_html_dir($unzip_dir, $name);
								}
								
								if($html_dir){
									App::installApp($newId, $_POST, $html_dir, DATA_DIR, APPS_DIR);	// process map data files
									if($unzip_dir){
										App::rrmdir($unzip_dir);
									}
								}else{
									$obj->delete($newId);
									$newId = 0;
								}
							}
            }

						if($newId > 0){
							if(						isset($_FILES["image"]) &&
											file_exists($_FILES['image']['tmp_name']) &&
								 is_uploaded_file($_FILES['image']['tmp_name']) && 
								 ($_FILES['image']['size'] < 10485760)){ // if image file and is less than 10 MB
								$image = null;
								// scale image to 200x150
								if($_FILES["image"]["type"] == 'image/png'){
									$image = imagecreatefrompng($_FILES["image"]["tmp_name"]);
								}else if($_FILES["image"]["type"] == 'image/jpeg'){
									$image = imagecreatefromjpeg($_FILES["image"]["tmp_name"]);
								}
								
								if($image){
									$imgResized = imagescale($image , 200, 150);
									imagepng($imgResized, "../../assets/maps/".$newId.'.png');
								}
							}
							
							if(isset($_FILES["qgis_file"])){
								$html_dir = APPS_DIR.'/'.$newId;
								
								$total = count($_FILES['qgis_file']['name']);
								$qgis_files = array();
								
								for($i=0; $i < $total; $i++) {
									$name 		= $_FILES["qgis_file"]["name"][$i];
									$tmp_name = $_FILES['qgis_file']['tmp_name'][$i];
									
									if(		 file_exists($tmp_name) &&
										is_uploaded_file($tmp_name) ){ // if its an uploaded file
										
										rename($tmp_name, DATA_DIR.'/'.$newId.'/'.$name);
										
										if(str_ends_with($name, '.qgs')){
											$vars = [ 'MAP_ID' => $newId, 'QGIS_FILE_VALUE' => DATA_DIR.'/'.$newId.'/'.$name ];
											App::update_template('../snippets/proxy_qgis.php', $html_dir.'/proxy_qgis.php', $vars);
										}else{
											array_push($qgis_files, $name);
										}
									}
								}
								
								if(count($qgis_files) > 0){
									file_put_contents($html_dir.'/qgis_files.txt', implode("\n", $qgis_files));
								}
							}

							# update thismap.css
							if(isset($_POST['thismap_css'])){
								$html_dir = APPS_DIR.'/'.$newId;
								file_put_contents($html_dir.'/thismap.css', $_POST['thismap_css']);
							}
							
							$result = ['success' => true, 'message' => 'Map successfully created!', 'id' => $newId];
						}else{
							$result = ['success' => false, 'message' => 'Failed to save Map!'];
						}
        } else if(isset($_POST['delete'])) {
					
					
					$ref_ids = array();
					$ref_name = null;
					$tbls = array('permalink');
					
					foreach($tbls as $k){
						$rows = $database->getAll('public.'.$k, 'map_id = '.$id);							
						foreach($rows as $row){
							$ref_ids[] = $row['map_id'];
						}
						
						if(count($ref_ids) > 0){
							$ref_name = $k;
							break;
						}
					}
					
					if(count($ref_ids) > 0){
						$result = ['success' => false, 'message' => 'Error: Can\'t delete because map has '.count($ref_ids).' '.$ref_name.' with ID(s) ' . implode(',', array_unique($ref_ids)) . '!' ];
					}else {
						$result = $obj->getById($_POST['id']);
						$row = pg_fetch_assoc($result);
						pg_free_result($result);
						
						if($obj->delete(intval($_POST['id']))){
							
							App::uninstallApp($row['id'], DATA_DIR, APPS_DIR);
							
							$result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
						}else{
							$result = ['success' => false, 'message' => 'Error: Data Not Deleted!'];
						}
					}
        
				} else if(isset($_POST['clear'])) {
					$map_cache_dir = CACHE_DIR.'/'.$_POST['id'];
					
					if(is_dir($map_cache_dir)){
						$dir_size = 0;
						
						$files = scandir($map_cache_dir);
						foreach($files as $f){
							if(is_file($map_cache_dir.'/'.$f) && str_ends_with($f, '.js')){
								$dir_size += filesize($map_cache_dir.'/'.$f);
								unlink($map_cache_dir.'/'.$f);
							}
						}
						
						rmdir($map_cache_dir);
						
						$unit = 'bytes';
						if($dir_size > (1024*1024)){
							$dir_size = $dir_size / (1024*1024);
							$unit = 'Mbytes';
						} else if($dir_size > 1024){
							$dir_size = $dir_size / 1024;
							$unit = 'kbytes';
						}

						$result = ['success' => true, 'message' => 'Successfully removed '.sprintf("%.2f %s", $dir_size, $unit)];
					}else{
						$result = ['success' => false, 'message' => 'Error: No cache!'];
					}
					
				} else if(isset($_POST['features'])) {
					
					$html_dir = APPS_DIR.'/'.$id;
					
					if(is_file($html_dir.'/proxy_qgis.php')){
						$content = file_get_contents($html_dir.'/proxy_qgis.php');
						if(preg_match('/const QGIS_QUERY = \'VERSION=1\.3\.0&map=(.*)\';/', $content, $matches)){
							$html = App::qgis_features_html($id, $matches[1]);
						}else{
							$html = '<p>QGIS File not found</p>';
						}

					}else{
						$html = '<p>Not found</p>';
					}
					
					$result = ['success' => true, 'html' => $html];
				}
    }

    echo json_encode($result);
?>
