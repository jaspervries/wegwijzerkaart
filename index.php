<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Wegwijzerkaart</title>
<link rel="stylesheet" type="text/css" href="bundled/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="bundled/leaflet/leaflet.css">
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="icon" type="image/png" href="favicon.png">
<script src="bundled/jquery/jquery.min.js"></script>
<script src="bundled/jquery-ui/jquery-ui.min.js"></script>
<script src="bundled/js-cookie/js.cookie.min.js"></script>
<script src="bundled/leaflet/leaflet.js"></script>
<script src="bundled/leaflet-tilelayer-colorfilter/src/leaflet-tilelayer-colorfilter.min.js"></script>
<script src="map.js"></script>
<script src="wwk.js"></script>
</head>
<body>

<div style="position: fixed; left: 0; top: 0; width: 100%; height: 100%">
	<div id="map" style="width: 100%; min-height: 100%"></div>
</div>

<div id="navigation">
    <ul class="toolbartab">
    	<li id="li-autozoom" title="Schakel deze optie in om automatisch in te zoomen en te centreren bij het selecteren van een kruispunt op de kaart."><input type="checkbox" checked id="autozoom"><label for="autozoom">automatisch zoomen</label></li>
        <li><span class="searchbox"><input type="text" id="searchbox" placeholder="Zoeken"></span></li>
        <!--<li><a href="help.php" rel="index" id="advancedsearch">Geavanceerd zoeken</a></li>-->
        <li><span id="help">Help</span></li>
    </ul>
</div>

<div id="map-options-container">
    <fieldset>
    <legend>Kaartachtergrond</legend>
        <ul id="map-tile"></ul>
    </fieldset>
    <fieldset>
    <legend>Kaartweergave</legend>	
		<ul id="map-style">
            <li><input type="radio" name="map-style" id="map-style-default"><label for="map-style-default">Standaard</label><br></li>
            <li><input type="radio" name="map-style" id="map-style-lighter"><label for="map-style-lighter">Lichter</label><br></li>
            <li><input type="radio" name="map-style" id="map-style-grayscale"><label for="map-style-grayscale">Grijswaarden</label><br></li>
            <li><input type="radio" name="map-style" id="map-style-dark"><label for="map-style-dark">Donker</label><br></li>
            <li><input type="radio" name="map-style" id="map-style-oldskool"><label for="map-style-oldskool">Vergeeld</label></li>
        </ul>
    </fieldset>
</div>

<div id="map-loading">
    <span>Bezig met laden...</span>
</div>

</body>
</html>