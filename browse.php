<?php
include_once("osm.php");
include_once("overpass.php");
include_once("json.php");

header("Content-Type: text/html; charset=utf-8");

$osmid = (int)$_REQUEST['id'];
$osmtype = strtolower($_REQUEST['type']);

$osm = get_overpass("($osmtype($osmid);>;);out meta;");
$element = $osm->getElement($osmtype, $osmid);

if($element)
{
	switch($osmtype)
	{
	case 'node':
		$json = json_node($osm, $element);
		break;
	case 'way':
		$json = json_way($osm, $element);
		break;
	case 'relation':
		$json = json_relation($osm, $element);
		break;
	default:
		$json = array();
		break;
	}
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title><?php echo "OSM $osmtype $osmid"; ?></title>
<link rel="stylesheet" type="text/css" href="osm.css"/>
<script src="http://www.openlayers.org/api/OpenLayers.js"></script>
<script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
<script src="osm.js"></script>
<?php
if($element)
{
	echo "<script type=\"text/javascript\">\n";
	echo "osmdata = " . json_string($json) . ";\n";
	echo "</script>\n";
}
?>
<body onload="init();">
<div id="map" style="position: fixed; top: 12px; bottom: 12px; left: 480px; right: 12px"></div>
<div id="list" style="position: absolute; left: 12px; top: 12px; bottom: 12px; width: 456px; overflow: auto">
<?php
echo "<h1>OSM $osmtype $osmid</h1>\n";
if($element)
{
	echo "<h2>Metadata</h2>\n";
	echo "<table class=\"meta\">\n";
	echo "<tr><th>ID</th><td class=\"id\">{$element->getId()}</td></tr>\n";
	echo "<tr><th>Version</th><td class=\"version\">{$element->getVersion()}</td></tr>\n";
	echo "<tr><th>Changeset</th><td class=\"changeset\">{$element->getChangeset()}</td></tr>\n";
	echo "<tr><th>Timestamp</th><td class=\"timestamp\">" . gmdate("d M Y H:i:s T", $element->getTimestamp()) . "</td></tr>\n";
	echo "<tr><th>UID</th><td class=\"uid\">{$element->getUid()}</td></tr>\n";
	echo "<tr><th>User</th><td class=\"user\">{$element->getUser()}</td></tr>\n";
	echo "</table>\n";

	echo "<h2>Tags</h2>";
	echo "<table class=\"tags\">\n";
	echo "<tr><th>Key</th><th>Value</th></tr>\n";
	foreach($element->getTags() as $key => $value)
		echo "<tr><td class=\"key\">$key</td><td class=\"value\">$value</td></tr>\n";
	echo "</table>\n";

	switch($osmtype)
	{
	case 'node':
		echo "<h2>Coordinates</h2>";
		echo "<table class=\"coords\">\n";
		echo "<tr><th>Latitude</th><td class=\"coord\">{$element->getLat()}</td></tr>\n";
		echo "<tr><th>Longitude</th><td class=\"coord\">{$element->getLon()}</td></tr>\n";
		echo "</table>\n";
		break;
	case 'way':
		echo "<h2>Nodes</h2>";
		echo "<table class=\"nodes\">\n";
		echo "<tr><th>ID</th></tr>\n";
		foreach($element->getNodes() as $node)
			echo "<tr><td class=\"id\"><a href=\"browse.php?type=node&amp;id=$node\">$node</a></td></tr>\n";
		echo "</table>\n";
		break;
	case 'relation':
		echo "<h2>Members</h2>";
		echo "<table class=\"members\">\n";
		echo "<tr><th>Role</th><th>Type</th><th>ID</th></tr>\n";
		foreach($element->getMembers() as $member)
			echo "<tr><td class=\"role\">{$member['role']}</td><td class=\"type\">{$member['type']}</td><td class=\"id\"><a href=\"browse.php?type={$member['type']}&amp;id={$member['ref']}\">{$member['ref']}</a></td></tr>\n";
		echo "</table>\n";
		break;
	default:
		break;
	}
}
else
{
	echo "<p>OSM $osmtype $osmid not found.</p>\n";
}
?>
</div>
</body>
</html>
