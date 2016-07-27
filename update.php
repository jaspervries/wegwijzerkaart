<?php
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

include('config.cfg.php');
$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
mysqli_set_charset($db['link'], "latin1");

//haal bestanden op, bronlocaties gedefinieerd in config
$resource_kp = file_get_contents($cfg_resource['KP.txt']);
$resource_ww = file_get_contents($cfg_resource['WW.txt']);
$resource_xy = file_get_contents($cfg_resource['kpxy.txt']);

if (($resource_kp === FALSE) || ($resource_ww === FALSE) || ($resource_xy === FALSE)) {
	exit;
}

//KP.txt
$qry = "UPDATE `kp` SET `actueel` = 0";
mysqli_query($db['link'], $qry);

$resource_kp = explode("\r\n", $resource_kp);
foreach ($resource_kp as $line) {
	$fields = str_getcsv($line);
	$qry = "INSERT INTO `kp` SET 
	`kp_nr` = '".mysqli_real_escape_string($db['link'], $fields[0])."',
	`naam` = '".mysqli_real_escape_string($db['link'], $fields[1])."',
	`provincie` = '".mysqli_real_escape_string($db['link'], $fields[2])."',
	`gemeente` = '".mysqli_real_escape_string($db['link'], $fields[3])."',
	`stand` = NOW()
	ON DUPLICATE KEY UPDATE
	`actueel` = 1";
	mysqli_query($db['link'], $qry);
}

//WW.txt en kpxy.txt
$qry = "UPDATE `ww` SET `actueel` = 0";
mysqli_query($db['link'], $qry);

include('convertRD.inc.php');
$xy = array();
$resource_xy = explode("\r\n", $resource_xy);
foreach ($resource_xy as $line) {
	$fields = str_getcsv($line);
	if (!empty($fields)) {
		$latlng = array();
		//formaat kan vanalles zijn:
		//kpww,x,a,y,b,,N
		//kpww,x,y,,N
		//kpww,x,a,y,,N
		//kpww,x,y,b,,N
		//wat a en b zijn is niet helder, kunnen decimalen zijn van x en y, voor nu genegeerd
		$x = $fields[1]; //altijd
		//voor y het gegeven dat de laagste waarde 289000 is, dus minstens 6 tekens
		if (strlen($fields[2]) >= 6) $y = $fields[2];
		else $y = $fields[3];
		$latlng = rd2wgs84($x, $y);
		$xy[$fields[0]] = array('lat' => $latlng[0], 'lng' => $latlng[1]);
	}
}

$resource_ww = explode("\r\n", $resource_ww);
foreach ($resource_ww as $line) {
	$fields = str_getcsv($line);
	if (!empty($fields)) {
		$qry = "INSERT INTO `ww` SET 
		`kp_nr` = '".mysqli_real_escape_string($db['link'], substr($fields[0], 0, 5))."',
		`ww_nr` = '".mysqli_real_escape_string($db['link'], substr($fields[0], 5, 3))."',
		`provincie` = '".mysqli_real_escape_string($db['link'], $fields[2])."',
		`gemeente` = '".mysqli_real_escape_string($db['link'], $fields[1])."',
		`uitvoering` = '".mysqli_real_escape_string($db['link'], substr($fields[3], 0, 1))."',
		`type_wegwijzer` = '".mysqli_real_escape_string($db['link'], substr($fields[3], 1, 1))."',
		`type_constructie` = '".mysqli_real_escape_string($db['link'], substr($fields[3], 2, 1))."',
		`stand` = NOW()";
		if (array_key_exists($fields[0], $xy)) {
			$qry .= ",
			`lat` = '".mysqli_real_escape_string($db['link'], $xy[$fields[0]]['lat'])."',
			`lng` = '".mysqli_real_escape_string($db['link'], $xy[$fields[0]]['lng'])."'";
		}
		$qry .= "
		ON DUPLICATE KEY UPDATE
		`actueel` = 1";
		mysqli_query($db['link'], $qry);
	}
}

//bepaal coordinaten van kruispunten op basis van wegwijzers
$qry = "UPDATE `kp` SET 
`lat` = (SELECT AVG(`ww`.`lat`) FROM `ww` WHERE `ww`.`kp_nr` = `kp`.`kp_nr` AND `ww`.`lat` != 0 AND `actueel` = 1),
`lng` = (SELECT AVG(`ww`.`lng`) FROM `ww` WHERE `ww`.`kp_nr` = `kp`.`kp_nr` AND `ww`.`lng` != 0 AND `actueel` = 1)
WHERE
`actueel` = 1";
mysqli_query($db['link'], $qry);

?>