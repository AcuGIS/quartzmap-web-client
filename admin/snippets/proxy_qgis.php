<?php
	const PERMALINK_DONT_COUNT = true;
	const QGIS_FILENAME = 'QGIS_FILE_VALUE';
	include('../../admin/incl/index_prefix.php');

	// https://demo.qualgis.com/cgi-bin/qgis_mapserv.fcgi?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities&map=/home/david1/firstname/front.qgs
	// https://demo.qualgis.com/apps/17/proxy_pdf.php?SERVICE=WMS&REQUEST=GetPrint&CRS=EPSG:4326&FORMAT=pdf&CRS=EPSG:3857&TEMPLATE=newbee&map0:EXTENT=1029311,5909796,1032022,5911468
	
	$format = '';
	if(preg_match('/FORMAT=([a-z]+)&/',$_SERVER['QUERY_STRING'], $matches)){
		$format = $matches[1];
	}

	if(strcasecmp($format, 'pdf') == 0){
		header("Content-type: application/pdf");
		header('Content-Disposition: attachment; filename="'.str_replace('.qgs', '.pdf', basename(QGIS_FILENAME)).'"');
	}else if(strcasecmp($format, 'png') == 0){
		header("Content-type: application/png");
		header('Content-Disposition: attachment; filename="'.str_replace('.qgs', '.png', basename(QGIS_FILENAME)).'"');
	}else{
		header("Content-type: text/xml");
	}
	readfile('http://localhost/cgi-bin/qgis_mapserv.fcgi?VERSION=1.3.0&map='.urlencode(QGIS_FILENAME).'&'.$_SERVER['QUERY_STRING']);
?>