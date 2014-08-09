<?php
include_once("osm.php");

function get_file($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	$data = curl_exec($ch);
	curl_close($ch);

	$bom = pack('CCC', 239, 187, 191);
	while(0 === strpos($data, $bom))
		$data = substr($data, 3);

	return $data;
}

function get_overpass($query)
{
	$opdata = get_file("http://overpass-api.de/api/interpreter?data=" . rawurlencode($query));

	if($opdata === false)
		$opdata = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<osm version=\"0.6\" generator=\"Overpass API\">\n</osm>";

	return new OSM($opdata);
}
?>
