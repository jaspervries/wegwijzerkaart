/*
 	fietsviewer - grafische weergave van fietsdata
    Copyright (C) 2018-2019 Gemeente Den Haag, Netherlands
    assetwebsite - viewer en aanvraagformulier voor verkeersmanagementassets
    Copyright (C) 2020 Gemeente Den Haag, Netherlands
    Developed by Jasper Vries
	wegwijzerkaart (C) 2025 Jasper Vries
 
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/*
* Initialize global variables
*/
var map;
var selectedMapStyle = 'map-style-lighter';
var selectedTileLayer = 0;
var tileLayers = [
	{
		name: 'BRT Achtergrondkaart',
		layer: L.tileLayer.colorFilter('https://service.pdok.nl/brt/achtergrondkaart/wmts/v2_0/standaard/EPSG:3857/{z}/{x}/{y}.png', {
			minZoom: 6,
			maxZoom: 19,
			bounds: [[50.5, 3.25], [54, 7.6]],
			attribution: 'Kaartgegevens &copy; <a href="https://www.kadaster.nl">Kadaster</a> | <a href="https://www.verbeterdekaart.nl">Verbeter de kaart</a>'
		})
	},
	{
		name: 'Luchtfoto',
		layer: L.tileLayer.colorFilter('https://service.pdok.nl/hwh/luchtfotorgb/wmts/v1_0/Actueel_orthoHR/EPSG:3857/{z}/{x}/{y}.jpeg', {
			minZoom: 6,
			maxZoom: 19,
			bounds: [[50.5, 3.25], [54, 7.6]],
			attribution: 'Kaartgegevens &copy; <a href="https://www.kadaster.nl">Kadaster</a>'
		})
	},
	{
		name: 'OpenStreetMap',
		layer: L.tileLayer.colorFilter('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		})
	}
];
var tileFilters = {
	'map-style-default': [],
	'map-style-grayscale': ['grayscale:100%', 'brightness:110%'],
	'map-style-lighter': ['brightness:105%', 'contrast:110%', 'grayscale:10%'],
	'map-style-dark': ['invert:100%', 'grayscale:100%'],
	'map-style-oldskool': ['sepia:50%', 'brightness:105%']
};
var onloadCookie;
var markers = {};
var maplayers = {};

/*
* Initialize the map on page load
*/
function initMap() {
	//create map
	map = L.map('map');
	//add methods
	map.on('load', function() {
		$('#map-loading').hide();
		set_zoom_warning(); //added for wegwijzerkaart
	});
	map.on('moveend', function() {
		//store map position and zoom in cookie
		setMapCookie();
		updateMapLayers();
		set_zoom_warning(); //added for wegwijzerkaart
	});
	map.on('contextmenu', function(e) {
		console.log(e);
		L.popup()
		.setLatLng(e.latlng)
		.setContent('<h1>' + e.latlng.lat.toFixed(6) + ',' + e.latlng.lng.toFixed(6) + '</h1><p><a href="https://www.google.nl/maps/?q=' + e.latlng.lat + ',' + e.latlng.lng + '&amp;layer=c&cbll=' + e.latlng.lat + ',' + e.latlng.lng + '&amp;cbp=11,' + 0 + ',0,0,5" target="_blank">Open locatie in Google Street View&trade;</a></p><p><a href="https://www.bing.com/maps?cp=' + e.latlng.lat + '~' + e.latlng.lng + '&amp;style=x" target="_blank">Open locatie in Bing Streetside</a></p>')
		.openOn(map); //streetside added for wegwijzerkaart
	})
	//set map position from cookie, if any
	if ((typeof onloadCookie !== 'undefined') && ($.isNumeric(onloadCookie[1]))) {
		//get and use center and zoom from cookie
		map.setView(onloadCookie[0], onloadCookie[1]);
		//get map style from cookie
		setMapStyle(onloadCookie[2]);
	}
	else {
		//set initial map view
		map.setView([51.9918383,4.2139163],10);
	}
	//set tile layer
	setMapTileLayer(selectedTileLayer);
	//modify some map controls
	map.zoomControl.setPosition('topleft');
	L.control.scale().addTo(map);
	//set map position from url var
	var centeratid = getUrlVars()['q'];
	if (typeof centeratid !== 'undefined') {
		set_map_from_urlvars(centeratid);  //modified for wegwijzerkaart
	}
}

/*
* Set the map tileset
*/
function setMapTileLayer(tile_id) {
	for (var i = 0; i < tileLayers.length; i++) {
		if (i == tile_id) {
			map.addLayer(tileLayers[i].layer);
		}
		else {
			map.removeLayer(tileLayers[i].layer);
		}
	}
	selectedTileLayer = tile_id;
	setMapCookie();
}

/*
* Get maps style on page load
*/
function getMapStyle() {
	//get map style
	if ((typeof onloadCookie !== 'undefined') && ((onloadCookie[2] == 'map-style-grayscale') || (onloadCookie[2] == 'map-style-lighter')  || (onloadCookie[2] == 'map-style-dark') || (onloadCookie[2] == 'map-style-oldskool') || (onloadCookie[2] == 'map-style-cycle'))) {
		selectedMapStyle = onloadCookie[2];
	}
	else {
		selectedMapStyle = 'map-style-default';
	}
	//set correct radio button
	$('#' + selectedMapStyle).prop('checked', true);
}

/*
* Set the map style and store it in the cookie
* Changed to leaflet-tilelayer-colorfilter for wegwijzerkaart
*/
function setMapStyle(style_id) {
	if ((style_id == 'map-style-grayscale') || (style_id == 'map-style-lighter') || (style_id == 'map-style-dark') || (style_id == 'map-style-oldskool') || (style_id == 'map-style-cycle')) {
		selectedMapStyle = style_id;
	}
	else {
		selectedMapStyle = 'map-style-default';
	}
	for (var i = 0; i < tileLayers.length; i++) {
		tileLayers[i].layer.updateFilter(tileFilters[selectedMapStyle]);
	}
	setMapCookie();
}

/*
* Update map layers
*/
function updateMapLayers() {
	draw_kruispunten();
	draw_wegwijzers();
}

/*
* Load/update markers for map layer
*/
/*function loadMarkers(layer) {
	$('#map-loading').show();
	//check if layer has entry in makers object and add it if not
	if (!markers.hasOwnProperty(layer)) {
		markers[layer] = [];
	}
	//draw new markers if they are not already drawn
	var visibleMarkerIds = [];
	$.getJSON('maplayer.php', { layer: layer, bounds: map.getBounds().toBBoxString(), zoom: map.getZoom() })
	.done( function(json) {
		$.each(json, function(index, v) {
			visibleMarkerIds.push(v.id);
			//find if marker is already drawn
			var markerfound = false;
			for (var i = 0; i < markers[layer].length; i++) {
				if (markers[layer][i].options.x_id == v.id) {
					markerfound = true;
					break;
				}
			}
			//add new marker
			if (markerfound == false) {
				var marker;
				if (layer == 'hecto') {
					marker = L.marker([v.lat, v.lon], {
						x_id: v.id,
						icon: L.icon({	iconUrl: 'style/milemarker.png', iconSize: [4,4] }),
						title: v.id
					}).bindTooltip(v.id, {
						permanent: true, 
						direction: 'right',
						className: 'hectolabel'
					});
				}
				else {
					marker = L.marker([v.lat, v.lon], {
						x_id: v.id,
						icon: L.icon({	iconUrl: 'image.php?t=' + layer + '&w=' + v.icon + '&i=' + v.itype, iconSize: [16,16], className: ((v.status == 1) ? 'L-icon-def' : 'L-icon-trans') }),
						zIndexOffset: ((layer == 2) ? 1000: 0), //TODO manage this from database, this assumes layer 2 is CAM layer, which is the case in the default install
						rotationAngle: v.heading,
						rotationOrigin: 'center',
						title: v.code
					});
					marker.on('click', function(e) {
						openMapPopup(e, v.id);
					});
					marker.on('contextmenu', function(e) {
						openDetailsWindow(v.id);
					});
				}
				marker.addTo(map);
				markers[layer].push(marker);
			}
		});

		//remove markers that should not be drawn (both out of bound and as a result of filtering)
		for (var i = markers[layer].length - 1; i >= 0; i--) {
			if (visibleMarkerIds.indexOf(markers[layer][i].options.x_id) === -1) {
				markers[layer][i].remove();
				markers[layer].splice(i, 1);
				
			}
		}
		//remove loading indicator
		$('#map-loading').hide();
	});
}*/

/*
* Load marker's popup content
*/
/*function openMapPopup(e, id) {
	$.getJSON('maplayer.php', { get: 'popup', id: id })
	.done( function(json) {
        e.target.bindPopup(json.html).openPopup();
		e.target._popup.update();
		//bind onclick to details window link
		$( "#popup_details" ).bind( "click", function() {
			openDetailsWindow(id);
		  });
	})
	.fail( function() {
		e.target.bindPopup('Fout: kan gegevens niet laden').openPopup();
	});
}*/

/*
* remove all markers for map layer
*/
function unloadMarkers(layer) {
	//check if layer has markers
	if (markers.hasOwnProperty(layer)) {
		for (var i = markers[layer].length - 1; i >= 0; i--) {
			markers[layer][i].remove();
			markers[layer].splice(i, 1);
		}
	}
}

/*
* center map at location of provided id
*/
/*function centerMapAtId(id) {
	//get coordinates from database
	$.getJSON('maplayer.php', { get: 'coordinates', id: id })
	.done( function(json) {
		//enable layer if necessary
		maplayers[json['layer']].active = true;
		$('#map-layer-' + json['layer']).prop('checked', true);
		updateMapLayers();
		//center map and set zoom
		map.setView([json['latitude'], json['longitude']], 16);
		setMapCookie();
	});
}*/

/*
* Set the cookie to remember map center, zoom, style and active layers
*/
function setMapCookie() {
	var activeMapLayers = [];
	$.each(maplayers, function(layer, options) {
		if (options.active == true) {
			activeMapLayers.push(layer);
		}
	});
	Cookies.set('assetwebsite_map', [map.getCenter(), map.getZoom(), selectedMapStyle, activeMapLayers, selectedTileLayer], {expires: 1000});
}

/*
* initialize layer GUI
*/
function initLayerGUI() {
    //get map layers
    $('#map-layers input[type=checkbox]').each(function() {
		var layer = this.id.substr(10);
		if (typeof onloadCookie !== 'undefined') {
			if (onloadCookie[3].indexOf(layer) >= 0) {
                maplayers[layer] = {active: true};
				$('#map-layer-' + layer).prop('checked', true);
			}
			else {
				maplayers[layer] = {active: false};
			}
		}
		else {
			maplayers[layer] = {active: false};
		}
	});
	$('#map-layers input[type=checkbox]').change( function() {
		var layer = this.id.substr(10);
		var enableState = $(this).prop('checked');
		maplayers[layer].active = enableState;
		updateMapLayers();
		setMapCookie();
    });
	updateMapLayers();
	setMapCookie();
}

/*
* Get maps tileset on page load
*/
function getMapTileLayer() {
	//get map style
	if ((typeof onloadCookie !== 'undefined') && (typeof onloadCookie[4] == 'number')) {
		selectedTileLayer = onloadCookie[4];
	}
	//set correct radio button
	$('#map-tile-' + selectedTileLayer).prop('checked', true);
}

/*
* draw tilelayer GUI
*/
function drawTileLayerGUI() {
	$.each(tileLayers, function(id, options) {
		$('#map-tile').append('<li><input type="radio" name="map-tile" id="map-tile-' + id + '"><label for="map-tile-' + id + '">' + options.name + '</label></li>');
	});
	$('#map-tile input[type=radio]').change( function() {
		var tile_id = this.id.substr(9);
		setMapTileLayer(parseInt(tile_id));
		$(this).prop('checked');
	});
}

/*
* details window, same as in tabel.js, except for map display and request from tabel.php
*/
function openDetailsWindow(id) {
    if ($('#detailsdialog').length == 0) {
        $('html').append('<div id="detailsdialog"></div>');
    }
    $('#detailsdialog').html('');
    $('#detailsdialog').dialog({
        autoOpen: false,
        title: 'laden...',
        height: 'auto',
        width: $(window).width() - 60,
        height: $(window).height() - 60,
        position: { my: 'center', at: 'center', of: window }
    });
    $("#detailsdialog").parent().css({position : 'fixed'}).end().dialog('open');
    $.getJSON('tabel.php', { data: 'details', id: id } )
    .done (function(json) {
        $('#detailsdialog').html(json.html);
        $('#detailsdialog').dialog('option', 'title', json.title);
        //open map
        initMiniMap();
    })
    .fail( function() {
        $('#detailsdialog').html('Kan gegevens niet laden');
        $('#detailsdialog').dialog('option', 'title', 'Fout');
    });
}
/* minimap for contrent from tabel.php*/
function initMiniMap() {
    var minimapposition = [$('#latitude').val(), $('#longitude').val()];

	var minimap = L.map('minimap').setView(minimapposition, 13);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(minimap);
    
    L.marker(minimapposition, {
		draggable: true, 
		rotationAngle: $('#heading').val(),
		rotationOrigin: 'center',
		icon: L.icon({	
			iconUrl: 'image.php?t=' + $('#assettype').val() + '&w=' + $('#aansturing').val(), 
			iconSize: [16,16], 
			className: 'L-icon-def' }),
	}).addTo(minimap);
}

/*
* document.ready
*/
$(function() {
	onloadCookie = Cookies.getJSON('assetwebsite_map');
	//initialize map
	drawTileLayerGUI();
	getMapTileLayer();
	initMap();
	getMapStyle();
	//handle to change map style
	$('#map-style input').change( function() {
		setMapStyle(this.id);
	});
	initLayerGUI();
});


// Read a page's GET URL variables and return them as an "associative array."
// from https://snipplr.com/view/19838/get-url-parameters
// 
function getUrlVars() {
	var map = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		map[key] = value;
	});
	return map;
}
