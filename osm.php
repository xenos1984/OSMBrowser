<?php
abstract class Element
{
	private $id, $version, $changeset, $timestamp, $uid, $user, $tags;

	function __construct($xml)
	{
		$this->id = (int)($xml->getAttribute('id'));
		$this->version = (int)($xml->getAttribute('version'));
		$this->changeset = (int)($xml->getAttribute('changeset'));
		$this->timestamp = strtotime($xml->getAttribute('timestamp'));
		$this->uid = (int)($xml->getAttribute('uid'));
		$this->user = $xml->getAttribute('user');
		$this->tags = array();
		foreach($xml->childNodes as $child)
		{
			if($child->localName == 'tag')
				$this->tags[$child->getAttribute('k')] = $child->getAttribute('v');
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getChangeset()
	{
		return $this->changeset;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getUid()
	{
		return $this->uid;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function getTag($key)
	{
		return (array_key_exists($key, $this->tags) ? $this->tags[$key] : "");
	}
}

class Node extends Element
{
	private $lat, $lon;

	function __construct($xml)
	{
		parent::__construct($xml);
		$this->lat = (float)($xml->getAttribute('lat'));
		$this->lon = (float)($xml->getAttribute('lon'));
	}

	public function getLat()
	{
		return $this->lat;
	}

	public function getLon()
	{
		return $this->lon;
	}

	public function getCoords()
	{
		return array('lat' => $this->lat, 'lon' => $this->lon);
	}
}

class Way extends Element
{
	private $nodes;

	function __construct($xml)
	{
		parent::__construct($xml);
		$this->nodes = array();
		foreach($xml->childNodes as $child)
		{
			if($child->localName == 'nd')
				$this->nodes[] = (int)($child->getAttribute('ref'));
		}
	}

	public function getNodes()
	{
		return $this->nodes;
	}

	public function isClosed()
	{
		return($this->nodes[0] == $this->nodes[count($this->nodes) - 1]);
	}
}

class Relation extends Element
{
	private $members;

	function __construct($xml)
	{
		parent::__construct($xml);
		$this->members = array();
		foreach($xml->childNodes as $child)
		{
			if($child->localName == 'member')
				$this->members[] = array('role' => $child->getAttribute('role'), 'type' => $child->getAttribute('type'), 'ref' => (int)($child->getAttribute('ref')));
		}
	}

	public function getMembers()
	{
		return $this->members;
	}

	public function getMembersByRole($role)
	{
		return array_filter($this->members, function ($x) use($role) { return preg_match($role, $x['role']); });
	}

	public function getMembersByType($type)
	{
		return array_filter($this->members, function ($x) use($type) { return($x['type'] == $type); });
	}

	public function getMembersByRoleAndType($role, $type)
	{
		return array_filter($this->members, function ($x) use($role, $type) { return(preg_match($role, $x['role']) && ($x['type'] == $type)); });
	}
}

class OSM
{
	private $nodes, $ways, $relations;

	function __construct($data)
	{
		$this->nodes = array();
		$this->ways = array();
		$this->relations = array();

		$xml = new DOMDocument;
		$xml->formatOutput = false;
		$xml->loadXML($data);

		$xmlnodes = $xml->getElementsByTagName("node");
		foreach($xmlnodes as $xmlnode)
		{
			$node = new Node($xmlnode);
			$this->nodes[$node->getId()] = $node;
		}

		$xmlways = $xml->getElementsByTagName("way");
		foreach($xmlways as $xmlway)
		{
			$way = new Way($xmlway);
			$this->ways[$way->getId()] = $way;
		}

		$xmlrelations = $xml->getElementsByTagName("relation");
		foreach($xmlrelations as $xmlrelation)
		{
			$relation = new Relation($xmlrelation);
			$this->relations[$relation->getId()] = $relation;
		}
	}

	public function getNode($id)
	{
		if(array_key_exists($id, $this->nodes))
			return $this->nodes[$id];
		else
			return false;
	}

	public function getWay($id)
	{
		if(array_key_exists($id, $this->ways))
			return $this->ways[$id];
		else
			return false;
	}

	public function getRelation($id)
	{
		if(array_key_exists($id, $this->relations))
			return $this->relations[$id];
		else
			return false;
	}

	public function getElement($type, $id)
	{
		switch($type)
		{
		case 'node':
			return $this->getNode($id);
			break;
		case 'way':
			return $this->getWay($id);
			break;
		case 'relation':
			return $this->getRelation($id);
			break;
		default:
			return false;
			break;
		}
	}
}
?>
