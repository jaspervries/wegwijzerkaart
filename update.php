<?php
/*
This file is part of Wegwijzerkaart
Copyright (C) 2016-2017 Jasper Vries

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
set_time_limit(0);
$start_time = time();

include('convertRD.inc.php');

//haal bestanden op, bronlocaties gedefinieerd in config
$resource_kp = file_get_contents($cfg_resource['KP']);
$resource_ww = file_get_contents($cfg_resource['WW']);
$resource_kpxy = file_get_contents($cfg_resource['kpxy']);
$resource_wwxy = file_get_contents($cfg_resource['wwxy']);

if (($resource_kp === FALSE) || ($resource_ww === FALSE) || ($resource_kpxy === FALSE) || ($resource_wwxy === FALSE)) {
	exit;
}

//KP
echo '[[[KP]]]' . PHP_EOL;
$qry = "UPDATE `kp` SET `actueel` = 0";
mysqli_query($db['link'], $qry);

$resource_kp = explode("\r\n", $resource_kp);
foreach ($resource_kp as $line) {
	$fields = str_getcsv($line);
	if (!empty($fields[0])) {
		$qry = "INSERT INTO `kp` SET 
		`kp_nr` = '".mysqli_real_escape_string($db['link'], $fields[0])."',
		`naam` = '".mysqli_real_escape_string($db['link'], $fields[1])."',
		`provincie` = '".mysqli_real_escape_string($db['link'], $fields[2])."',
		`gemeente` = '".mysqli_real_escape_string($db['link'], $fields[3])."',
		`stand` = NOW()
		ON DUPLICATE KEY UPDATE
		`actueel` = 1";
		mysqli_query($db['link'], $qry);
		echo $fields[0] . "\t" . $fields[3] . "\t" . $fields[1] . PHP_EOL;
	}
}

//kpxy
echo '[[[kpxy]]]' . PHP_EOL;
$xy = array();
$resource_kpxy = explode("\r\n", $resource_kpxy);
foreach ($resource_kpxy as $line) {
	$fields = str_getcsv($line);
	if (!empty($fields[0])) {
		$latlng = array();
		$latlng = rd2wgs84($fields[1], $fields[2]);
		$qry = "UPDATE `kp` SET 
		`lat` = '".mysqli_real_escape_string($db['link'], $latlng[0])."',
		`lng` = '".mysqli_real_escape_string($db['link'], $latlng[1])."'
		WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $fields[0])."'
		AND `actueel` = 1";
		mysqli_query($db['link'], $qry);
		echo $fields[0] . "\t" . $latlng[0] . "\t" . $latlng[1] . PHP_EOL;
	}
}

//WW en wwxy
echo '[[[WW/wwxy]]]' . PHP_EOL;
$qry = "UPDATE `ww` SET `actueel` = 0";
mysqli_query($db['link'], $qry);

$xy = array();
$resource_wwxy = explode("\r\n", $resource_wwxy);
foreach ($resource_wwxy as $line) {
	$fields = str_getcsv($line);
	if (!empty($fields[0])) {
		$latlng = array();
		$latlng = rd2wgs84($fields[1], $fields[2]);
		$xy[$fields[0].$fields[3]] = array('lat' => $latlng[0], 'lng' => $latlng[1]);
	}
}

$resource_ww = explode("\r\n", $resource_ww);
foreach ($resource_ww as $line) {
	$fields = str_getcsv($line);
	if (!empty($fields[0])) {
		$qry = "INSERT INTO `ww` SET 
		`kp_nr` = '".mysqli_real_escape_string($db['link'], $fields[0])."',
		`ww_nr` = '".mysqli_real_escape_string($db['link'], $fields[1])."',
		`provincie` = '".mysqli_real_escape_string($db['link'], $fields[3])."',
		`gemeente` = '".mysqli_real_escape_string($db['link'], $fields[2])."',
		`uitvoering` = '".mysqli_real_escape_string($db['link'], substr($fields[4], 0, 1))."',
		`type_wegwijzer` = '".mysqli_real_escape_string($db['link'], substr($fields[4], 1, 1))."',
		`type_constructie` = '".mysqli_real_escape_string($db['link'], substr($fields[4], 2, 1))."',
		`stand` = NOW()";
		if (array_key_exists($fields[0].$fields[1], $xy)) {
			$qry .= ",
			`lat` = '".mysqli_real_escape_string($db['link'], $xy[$fields[0].$fields[1]]['lat'])."',
			`lng` = '".mysqli_real_escape_string($db['link'], $xy[$fields[0].$fields[1]]['lng'])."'";
		}
		$qry .= "
		ON DUPLICATE KEY UPDATE
		`actueel` = 1";
		mysqli_query($db['link'], $qry);
		echo $fields[0] . "\t" . $fields[1] . ((array_key_exists($fields[0].$fields[1], $xy)) ? "\t" .$xy[$fields[0].$fields[1]]['lat'] . "\t" . $xy[$fields[0].$fields[1]]['lng'] : '') . PHP_EOL;
	}
}

echo 'Verwerkingstijd: '.floor((time()-$start_time)/60).':'.str_pad(((time()-$start_time)%60), 2, '0', STR_PAD_LEFT);
?>