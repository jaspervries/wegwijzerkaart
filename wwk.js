/*
This file is part of Wegwijzerkaart
Copyright (C) 2016, 2025 Jasper Vries

Wegwijzerkaart is free software: you can redistribute it and/or 
modify it under the terms of version 3 of the GNU General Public 
License as published by the Free Software Foundation.

Wegwijzerkaart is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Wegwijzerkaart. If not, see <http://www.gnu.org/licenses/>.
*/
var kruispunten = [];
var wegwijzers = [];

$( document ).ready(function() {
	
	//sluit alle dialogs wanneer op delete (46) wordt gedrukt
	$(document).keyup(function( event ) {
		if (event.which == 46) {
			//behalve wanneer in input veld
			if (!($('input').is(':focus'))) {
				$('.dialog').dialog('close');
			}
		}
	});
	
	//zoekfunctie
	$('#searchbox').autocomplete({
		source: 'ajax.php?type=search',
		minLength: 1,
		autoFocus: true,
		select: function( event, ui ) {
			if (typeof(ui.item.type)!=='undefined') {
				open_dialog(ui.item.type, ui.item.id);
				set_map_center(ui.item.latlng);
			}
			else {
				$('#searchbox').autocomplete('close');
				return false;
			}
		}
	})
	
	//help
	$('#help').click( function() {
		open_dialog('help', 0)
	});
});

//waarschuwing wanneer niet ver genoeg ingezoomd
function set_zoom_warning() {
	if (map.getZoom() < 14) {
		if($('#zoomwarning').length == 0) {
			$('body').append('<div class="warning" id="zoomwarning">Zoom verder in om kruispunten te bekijken.</div>');
		}
		$('#zoomwarning').show();
	}
	else {
		$('#zoomwarning').hide();
	}
}

//centreren van kaart obv zoekfunctie
function set_map_center(strlatlng) {
	if (typeof(strlatlng) !== 'undefined') {
		centerlatlng = strlatlng.match(/^(\d+\.\d+),(\d+\.\d+)$/);
		if ((centerlatlng != null) && (centerlatlng[1] != 0) && (centerlatlng[2] != 0)) {
			map.setView(L.latLng(parseFloat(centerlatlng[1]), parseFloat(centerlatlng[2])), 18);
		}
	}
}

function draw_kruispunten() {
	let layer = 'kp';
	$('#map-loading').show();
	//check if layer has entry in makers object and add it if not
	if (!markers.hasOwnProperty(layer)) {
		markers[layer] = [];
	}
	//draw new markers if they are not already drawn
	var visibleMarkerIds = [];
	if ((map.getZoom() >= 14) && (map.getZoom() < 18)) {
		$.getJSON( "ajax.php", {type: "kp", bounds: map.getBounds().toBBoxString()} )
		.done( function(json) {
			$.each(json, function(index, v) {
				visibleMarkerIds.push(v[0]);
				//find if marker is already drawn
				var markerfound = false;
				for (var i = 0; i < markers[layer].length; i++) {
					if (markers[layer][i].options.x_id == v[0]) {
						markerfound = true;
						break;
					}
				}
				//add new marker
				if (markerfound == false) {
					var marker;
					marker = L.marker([v[2], v[3]], {
						x_id: v[0],
						icon: L.icon({	iconUrl: 'marker.png', iconSize: [6,6] }),
						title: v[1]
					}).bindTooltip(v[1], {
						permanent: true, 
						interactive: true,
						direction: 'right',
						className: 'markerlabel'
					}).on('click', function(e) {
						open_dialog('kp', marker.options.x_id);
						if ($('#autozoom').prop( "checked" ) == true) {
							map.setView(marker.getLatLng(), 18); //latlng, zoom
						}
					});
					marker.addTo(map);
					markers[layer].push(marker);
				}
			});
		});
	}
	//remove markers that should not be drawn (both out of bound and as a result of filtering)
	for (var i = markers[layer].length - 1; i >= 0; i--) {
		if (visibleMarkerIds.indexOf(markers[layer][i].options.x_id) === -1) {
			markers[layer][i].remove();
			markers[layer].splice(i, 1);
			
		}
	}
	//remove loading indicator
	$('#map-loading').hide();
}

function draw_wegwijzers() {
	let layer = 'ww';
	$('#map-loading').show();
	//check if layer has entry in makers object and add it if not
	if (!markers.hasOwnProperty(layer)) {
		markers[layer] = [];
	}
	//draw new markers if they are not already drawn
	var visibleMarkerIds = [];
	if (map.getZoom() >= 18) {
		$.getJSON( "ajax.php", {type: "ww", bounds: map.getBounds().toBBoxString()} )
		.done( function(json) {
			$.each(json, function(index, v) {
				visibleMarkerIds.push(v[0]);
				//find if marker is already drawn
				var markerfound = false;
				for (var i = 0; i < markers[layer].length; i++) {
					if (markers[layer][i].options.x_id == v[0]) {
						markerfound = true;
						break;
					}
				}
				//add new marker
				if (markerfound == false) {
					var marker;
					marker = L.marker([v[2], v[3]], {
						x_id: v[0],
						icon: L.icon({	iconUrl: 'marker.png', iconSize: [6,6] }),
						title: v[1]
					}).bindTooltip(v[1], {
						permanent: true, 
						interactive: true,
						direction: 'right',
						className: 'markerlabel'
					}).on('click', function(e) {
						open_dialog('ww', marker.options.x_id);
					});
					marker.addTo(map);
					markers[layer].push(marker);
				}
			});
		});
	}
	//remove markers that should not be drawn (both out of bound and as a result of filtering)
	for (var i = markers[layer].length - 1; i >= 0; i--) {
		if (visibleMarkerIds.indexOf(markers[layer][i].options.x_id) === -1) {
			markers[layer][i].remove();
			markers[layer].splice(i, 1);
			
		}
	}
	//remove loading indicator
	$('#map-loading').hide();
}

//kruispunt en wegwijzer dialog
function open_dialog(type, id, alt_method) {
	if (typeof(alt_method)==='undefined') alt_method = false;
	else alt_method = true;
	var div_id = 'dialog' + type + id;
	//add html div
	if($("#" + div_id).length == 0) {
		$('body').append('<div class="dialog" id="' + div_id + '">laden...</div>');
	}
	var position = { my: "left center", at: "left center", of: window };
	if (type == 'ww') position = { my: "right center", at: "right center", of: window };
	else if (type == 'help') position = { my: "center center", at: "center center", of: window };
	//create dialog
	$('#' + div_id).dialog({
		height: $(window).height() - 30,
		width: $(window).width() / 3,
		position: position,
		title: 'Laden...',
		close: function() {
			//remove dialog on close
			$(this).remove();
		}
	});
	//load dialog content
	$.getJSON( "ajax.php", {type: 'dialog' + type, id: id, alt: alt_method} )
	.done(function( json ) {
		var titletype = 'Wegwijzerkaart';
		if (type == 'kp') titletype = 'Kruispunt';
		else if (type == 'ww') titletype = 'Wegwijzer'
		$('#' + div_id).dialog({
			title: titletype + ' ' + json.title
		});
		$('#' + div_id).html(json.html);
	})
	.fail( function (jqxhr, textStatus, error) {
		$('#' + div_id).dialog({
			title: 'Oeps!'
		});
		$('#' + div_id).html('Kan venster niet laden.');
		console.log(type + id);
		console.log(jqxhr);
		console.log(textStatus);
		console.log(error);
	});
}

//open wegwijzer dialog vanuit kruispunt dialog
$(document).on('click', '.ww-nr-dialog', function() {
	var id = $(this).parent().attr('rel') + '' + $(this).html();
	open_dialog('ww', id, true);
});
//open kruispunt dialog vanuit wegwijzer dialog
$(document).on('click', '.kp-nr-dialog', function() {
	var id = $(this).html();
	open_dialog('kp', id, true);
});
