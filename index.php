<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Wegwijzerkaart</title>
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php include('config.cfg.php'); echo $cfg_google['maps_key']; ?>"></script>
<script type="text/javascript" src="markerwithlabel_packed.js"></script>
<script type="text/javascript" src="wwk.js"></script>
<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="icon" type="image/png" href="favicon.png">
</head>
<body>

<div style="position: fixed; left: 0; top: 0; width: 100%; height: 100%">
	<div class="map-canvas" id="map-canvas"></div>
</div>

<div id="navigation">
    <ul class="toolbartab">
        <li><span class="searchbox"><input type="text" id="searchbox" placeholder="Zoeken"></span></li>
        <!--<li><a href="help.php" rel="index" id="advancedsearch">Geavanceerd zoeken</a></li>-->
        <li><span id="help">Help</span></li>
    </ul>
</div>

</body>
</html>