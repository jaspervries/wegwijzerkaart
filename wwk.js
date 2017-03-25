/*
This file is part of Wegwijzerkaart
Copyright (C) 2016 Jasper Vries

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
var map;
var kruispunten = [];
var wegwijzers = [];

$( document ).ready(function() {
	initMap();
	
	//set position from cookie, if any
	if (typeof(Cookies.get('wwk_map')) !== 'undefined') {
		var cookievalues = Cookies.getJSON('wwk_map');
		map.setCenter(cookievalues[0]);
		map.setZoom(cookievalues[1]);
	}
	
	//update map content, set cookie with position and view settings
	map.addListener('idle', function() { 
		draw_kruispunten();
		draw_wegwijzers(); 
		set_map_cookie();
		set_zoom_warning();
	});
	
	//get and set center location
	var centerlatlng = getUrlVars()['latlng'];
	if (typeof(centerlatlng) !== 'undefined') {
		set_map_center(centerlatlng);
	}
	
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

function initMap() {
	var mapOptions = {
		center: new google.maps.LatLng(52.132633,5.291266),
		zoom: 8,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		disableDefaultUI: false,
		scrollwheel: true,
		draggable: true,
		clickableIcons: false,
		disableDoubleClickZoom: true
	};
	map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
}

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

function set_map_center(strlatlng) {
	if (typeof(strlatlng) !== 'undefined') {
		centerlatlng = strlatlng.match(/^(\d+\.\d+),(\d+\.\d+)$/);
		if ((centerlatlng != null) && (centerlatlng[1] != 0) && (centerlatlng[2] != 0)) {
			var mapcenter = new google.maps.LatLng(parseFloat(centerlatlng[1]), parseFloat(centerlatlng[2]));
			map.setCenter(mapcenter);
			map.setZoom(18);
		}
	}
}

function draw_kruispunten() {
	var pointskept = [];
	for (var i = kruispunten.length - 1; i >= 0; i--) {
		if ((map.getBounds().contains(kruispunten[i].getPosition())) && (map.getZoom() >= 14) && (map.getZoom() < 18)) {
			pointskept.push(kruispunten[i].x_id);
		}
		else {
			kruispunten[i].setMap(null);
			kruispunten.splice(i, 1);
		}
	}
	if ((map.getZoom() >= 14) && (map.getZoom() < 18)) {
		//draw markers
		$.getJSON( "ajax.php", {type: "kp", bounds: map.getBounds().toString()} )
		.done(function( json ) {
			if (json != null) {
				$.each( json, function(i, value) {
					if (pointskept.indexOf(value[0]) == -1) {
						//add marker
						var marker = new MarkerWithLabel({
							map: map,
							position: new google.maps.LatLng(value[2], value[3]),
							title: value[1],
							x_id: value[0],
							icon: 'marker.png',
							labelContent: value[1],
							labelAnchor: new google.maps.Point(-8, 8),
							labelClass: 'markerlabel',
							labelVisible: true
						});
						//bind click event
						google.maps.event.addListener(marker, 'click', function() {
							map.setCenter(marker.position);
							map.setZoom(18);
							open_dialog('kp', marker.x_id);
						});
						kruispunten.push(marker);
					}
				});
			}
		});
	}
}

function draw_wegwijzers() {
	var pointskept = [];
	for (var i = wegwijzers.length - 1; i >= 0; i--) {
		if ((map.getBounds().contains(wegwijzers[i].getPosition())) && (map.getZoom() >= 18)) {
			pointskept.push(wegwijzers[i].x_id);
		}
		else {
			wegwijzers[i].setMap(null);
			wegwijzers.splice(i, 1);
		}
	}
	if (map.getZoom() >= 18) {
		//draw markers
		$.getJSON( "ajax.php", {type: "ww", bounds: map.getBounds().toString()} )
		.done(function( json ) {
			if (json != null) {
				$.each( json, function(i, value) {
					if (pointskept.indexOf(value[0]) == -1) {
						//add marker
						var marker = new MarkerWithLabel({
							map: map,
							position: new google.maps.LatLng(value[2], value[3]),
							title: value[4],
							x_id: value[0],
							icon: 'marker.png',
							labelContent: value[1],
							labelAnchor: new google.maps.Point(-8, 8),
							labelClass: 'markerlabel',
							labelVisible: true
						});
						//bind click event
						google.maps.event.addListener(marker, 'click', function() {
							open_dialog('ww', marker.x_id);
						});
						wegwijzers.push(marker);
					}
				});
			}
		});
	}
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

function set_map_cookie() {
	Cookies.set('wwk_map', [map.getCenter(), map.getZoom()], {expires: 1000});
}

// Read a page's GET URL variables and return them as an associative array.
//from http://jquery-howto.blogspot.nl/2009/09/get-url-parameters-values-with-jquery.html
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

/*! js-cookie v2.1.2 | MIT | https://github.com/js-cookie/js-cookie */
!function(a){if("function"==typeof define&&define.amd)define(a);else if("object"==typeof exports)module.exports=a();else{var b=window.Cookies,c=window.Cookies=a();c.noConflict=function(){return window.Cookies=b,c}}}(function(){function a(){for(var a=0,b={};a<arguments.length;a++){var c=arguments[a];for(var d in c)b[d]=c[d]}return b}function b(c){function d(b,e,f){var g;if("undefined"!=typeof document){if(arguments.length>1){if(f=a({path:"/"},d.defaults,f),"number"==typeof f.expires){var h=new Date;h.setMilliseconds(h.getMilliseconds()+864e5*f.expires),f.expires=h}try{g=JSON.stringify(e),/^[\{\[]/.test(g)&&(e=g)}catch(i){}return e=c.write?c.write(e,b):encodeURIComponent(String(e)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),b=encodeURIComponent(String(b)),b=b.replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent),b=b.replace(/[\(\)]/g,escape),document.cookie=[b,"=",e,f.expires&&"; expires="+f.expires.toUTCString(),f.path&&"; path="+f.path,f.domain&&"; domain="+f.domain,f.secure?"; secure":""].join("")}b||(g={});for(var j=document.cookie?document.cookie.split("; "):[],k=/(%[0-9A-Z]{2})+/g,l=0;l<j.length;l++){var m=j[l].split("="),n=m.slice(1).join("=");'"'===n.charAt(0)&&(n=n.slice(1,-1));try{var o=m[0].replace(k,decodeURIComponent);if(n=c.read?c.read(n,o):c(n,o)||n.replace(k,decodeURIComponent),this.json)try{n=JSON.parse(n)}catch(i){}if(b===o){g=n;break}b||(g[o]=n)}catch(i){}}return g}}return d.set=d,d.get=function(a){return d(a)},d.getJSON=function(){return d.apply({json:!0},[].slice.call(arguments))},d.defaults={},d.remove=function(b,c){d(b,"",a(c,{expires:-1}))},d.withConverter=b,d}return b(function(){})});
