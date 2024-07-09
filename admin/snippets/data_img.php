<?php
const PERMALINK_DONT_COUNT = true;
include('../../admin/incl/index_prefix.php');

if(!empty($_GET['img']) && is_file(DATA_DIR.'/'.MAP_ID.'/images/'.$_GET['img'])){
	header('Content-Type: application/javascript');
	readfile(DATA_DIR.'/'.MAP_ID.'/images/'.$_GET['img']);
}else{
	header("HTTP/1.1 400 Bad Request");
}
?>