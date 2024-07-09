<?php
const PERMALINK_DONT_COUNT = true;
include('../../admin/incl/index_prefix.php');

$fpath = DATA_DIR.'/'.MAP_ID.'/'.$_GET['f'];

if(!empty($_GET['f']) && is_file($fpath)){
	header('Content-Type: ').mime_content_type($fpath);
	readfile($fpath);
}else{
	header("HTTP/1.1 400 Bad Request");
}
?>