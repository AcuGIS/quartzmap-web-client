<?php 
require('incl/const.php');
require('class/database.php');
require('class/map.php');
require('class/app.php');

function export_data_scripts($src, $data_dir){

	$js_name = substr(basename($src), 0, -5);

	$doc = new DOMDocument();

	libxml_use_internal_errors(true);	// hide warnings

	// load the HTML string we want to strip
	$doc->loadHTMLFile($src, LIBXML_BIGLINES | LIBXML_PARSEHUGE);

	// get all the script tags
	$script_tags = $doc->getElementsByTagName('script');

	$length = $script_tags->length;

	// for each tag, remove it from the DOM
	for ($i = $length - 1; $i >= 0; $i--) {
		$itm = $script_tags->item($i);
		if(($itm != null) && $itm->hasAttribute('data-for') &&
				preg_match('/htmlwidget-([0-9a-f]+)/', $itm->getAttribute('data-for'), $matches)){
	
				$js_filename = $data_dir.'/'.$js_name.'_'.$i.'.js';
				
				# overwrite file, only if different
				if(!is_file($js_filename) || (sha1($itm->nodeValue) != sha1_file($js_filename)) ){
					file_put_contents($js_filename, $itm->nodeValue);
					# allow group to rewrite data files
					# chmod($data_dir.'/'.$js_name.'_'.$i.'.js', 0660);
					echo "[rmap_update_data] Updated ".$js_filename."\n";
				}else{
					echo "[rmap_update_data] No change in ".$js_filename."\n";
				}
		}
	}
}

if(count($argv) != 2){
	exit("Error: Missing map id\n");
}

$newId = $argv[1];

$data_dir = DATA_DIR.'/'.$newId;

$tmp_dir = tempnam('/tmp', 'rmaps_update');
unlink($tmp_dir);
mkdir($tmp_dir);

App::copy_r($html_dir, $tmp_dir);

$r_files = App::getRfiles($data_dir);
foreach($r_files as $rf){
	
	# compile the .R file
	list($rv, $out, $err) = AppR::runR($tmp_dir, $data_dir.'/'.$rf);
	if($rv != 0){
		echo "Error: Failed to compile ".$data_dir.'/'.$rf."\n";
		continue;
	}

	list($map_files,$plot_files, $plotly_files, $knitr_tbls, $rmarkdown) = AppR::r_get_html_files($data_dir.'/'.$rf);
	foreach($map_files as $f){
		export_data_scripts($tmp_dir.'/'.$f, $data_dir);
		unlink($tmp_dir.'/'.$f);
	}

	// move updated Rmarkdown output files
	foreach($rmarkdown as $rmd_source){
		$output_formats = AppR::rmd_get_formats($data_dir.'/index.R', $rmd_source);
		foreach($output_formats as $fmt => $f){
			if(sha1_file($tmp_dir.'/'.$f) != sha1_file($data_dir.'/'.$f)){
				rename($tmp_dir.'/'.$f, $data_dir.'/'.$f);
			}
		}
	}
}

# cleanup
App::rrmdir($tmp_dir);

?>
