<?php
	const PERMALINK_DONT_COUNT = true;
	include('../../admin/incl/index_prefix.php');

	// https://shop.chicagotvguides.com/geoserver/wms?service=WMS&request=GetFeatureInfo&version=1.1.1&layers=topp%3Astates&styles=&format=image%2Fpng&transparent=true&continuousWorld=true&tiled=true&info_format=text%2Fhtml&width=1374&height=852&srs=EPSG%3A3857&bbox=-12983287.876406899%2C1633917.916623927%2C-6261721.357121639%2C5801876.194958019&query_layers=topp%3Astates&X=653&Y=285
	const BASE_URL = 'BASE_URL_VALUE';
	readfile(BASE_URL.'?'.$_SERVER['QUERY_STRING']);
?>