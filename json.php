<?php
include_once("osm.php");

function json_combine($k, $v)
{
	return "\"$k\": $v";
}

function json_coord($node)
{
	return array($node->getLon(), $node->getLat());
}

function json_node($osm, $node)
{
	return array(
		'type' => 'Feature',
		'properties' => array_merge($node->getMeta(), array('tags' => $node->getTags())),
		'geometry' => array('type' => 'Point', 'coordinates' => json_coord($node))
	);
}

function json_way($osm, $way)
{
	return array(
		'type' => 'Feature',
		'properties' => array_merge($way->getMeta(), array('tags' => $way->getTags())),
		'geometry' => array('type' => 'LineString', 'coordinates' => array_map(function ($x) use ($osm) { return json_coord($osm->getNode($x)); }, $way->getNodes()))
	);
}

function json_relation($osm, $relation)
{
	return array(
		'type' => 'FeatureCollection',
		'features' => array_map(function ($x) use($osm) {
			switch($x['type'])
			{
			case 'node':
				return json_node($osm, $osm->getNode($x['ref']));
				break;
			case 'way':
				return json_way($osm, $osm->getWay($x['ref']));
				break;
			default:
				break;
			}
		}, $relation->getMembers())
	);
}

function json_string($array)
{
	if(is_numeric($array))
		return $array;

	if(is_string($array))
		return '"' . addslashes($array) . '"';

	if(count(array_filter(array_keys($array), 'is_string')))
		return '{' . implode(", ", array_map("json_combine", array_keys($array), array_map("json_string", $array))) . '}';
	else
		return '[' . implode(", ", array_map("json_string", $array)) . ']';
}
?>
