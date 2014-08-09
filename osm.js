var map;
var osmdata = false;
var geojson = new OpenLayers.Format.GeoJSON({
	internalProjection: new OpenLayers.Projection("EPSG:900913"),
	externalProjection: new OpenLayers.Projection("EPSG:4326")
});
var dataExtent;
var setExtent = function()
{
	if(dataExtent)
		dataExtent.extend(this.getDataExtent());
	else
		dataExtent = this.getDataExtent();
	map.zoomToExtent(dataExtent);
};

function init()
{
	if(!document.getElementById("map"))
		return;

	map = new OpenLayers.Map ("map", {
		controls:[
			new OpenLayers.Control.Navigation(),
			new OpenLayers.Control.PanZoomBar(),
			new OpenLayers.Control.LayerSwitcher(),
			new OpenLayers.Control.ScaleLine(),
			new OpenLayers.Control.Attribution()],
		maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
		maxResolution: 156543.0399,
		numZoomLevels: 20,
		units: 'm',
		projection: new OpenLayers.Projection("EPSG:900913"),
		displayProjection: new OpenLayers.Projection("EPSG:4326"),
		zoomMethod: null
	} );

	map.addLayers([
		new OpenLayers.Layer.OSM.Mapnik("Mapnik"),
		new OpenLayers.Layer.XYZ("Mapnik B/W", ["http://a.www.toolserver.org/tiles/bw-mapnik/${z}/${x}/${y}.png", "http://b.www.toolserver.org/tiles/bw-mapnik/${z}/${x}/${y}.png", "http://c.www.toolserver.org/tiles/bw-mapnik/${z}/${x}/${y}.png"], {numZoomLevels: 19}),
		new OpenLayers.Layer.OSM.TransportMap("TransportMap"),
		new OpenLayers.Layer.OSM.CycleMap("CycleMap"),
		new OpenLayers.Layer.XYZ("OSM German", ["http://a.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png", "http://b.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png", "http://c.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png", "http://d.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png"], {numZoomLevels: 19})
	]);

	function createPopup(feature) {
		var html = '<table class="popup">';
		for(var key in feature.attributes)
		{
			var value = feature.attributes[key];
			html += '<tr><th>' + key + '</th><td>' + value + '</td></tr>';
		}
		html += '</table>';
		if(selcontrol.handlers.feature.evt.layerX && selcontrol.handlers.feature.evt.layerY)
			lonlat = map.getLonLatFromPixel(new OpenLayers.Pixel(selcontrol.handlers.feature.evt.layerX, selcontrol.handlers.feature.evt.layerY));
		else
			lonlat = feature.geometry.getBounds().getCenterLonLat();
		feature.popup = new OpenLayers.Popup.FramedCloud("gpx",
			lonlat,
			null,
			html,
			null,
			true,
			function() { selcontrol.unselectAll(); }
		);
		map.addPopup(feature.popup);
	}

	function destroyPopup(feature) {
		feature.popup.destroy();
		feature.popup = null;
	}

	if(osmdata)
	{
		var losm = new OpenLayers.Layer.Vector("OSM Data", {
			style: {strokeColor: "#0000ff", strokeWidth: 2}
		});
		losm.events.register("featuresadded", losm, setExtent);
		losm.addFeatures(geojson.read(osmdata));
		map.addLayer(losm);

		var selcontrol = new OpenLayers.Control.SelectFeature(losm, {
			onSelect: createPopup,
			onUnselect: destroyPopup
		});
		map.addControl(selcontrol);
		selcontrol.activate();
	}

	if(!map.getCenter())
		map.setCenter(null, null);
}

function load_and_zoom()
{
	var bounds = map.getExtent().transform(new OpenLayers.Projection("EPSG:900913"), new OpenLayers.Projection("EPSG:4326"));
	var ba = bounds.toArray();
	document.getElementById('josm').src = "http://localhost:8111/load_and_zoom?left=" + ba[0] + "&bottom=" + ba[1] + "&right=" + ba[2] + "&top=" + ba[3];
}
