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
set_time_limit(0);
$start_time = time();

//haal hoofdpagina op
$html = file_get_contents($cfg_resource['image_base']);
//verkrijg alle mappen op pagina
preg_match_all('#<tr>.*\[DIR].*href="([0-9]{5}/)"#U', $html, $main_dir_folders);
foreach($main_dir_folders[1] as $folder) {
	//haal mappagina op
	$html = file_get_contents($cfg_resource['image_base'].$folder);
	//verkrijg alle kruispunten in map
	preg_match_all('#<tr>.*\[DIR].*href="([0-9]{5}/)"#U', $html, $kruispunten);
	foreach($kruispunten[1] as $kruispunt) {
		//haal kruispuntpagina op
		$html = file_get_contents($cfg_resource['image_base'].$folder.$kruispunt);
		//kruispuntschets
		if (preg_match('#<tr>.*\[IMG].*href="(([0-9]{5})K\.png)"#Ui', $html, $res) > 0) {
			$kp_nr = $res[2];
			$img = $res[1];
			echo $kp_nr.':'.$img.PHP_EOL;
			//update database
			$qry = "UPDATE `kp` SET 
			`afbeelding` = '".mysqli_real_escape_string($db['link'], $img)."'
			WHERE
			`kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."'
			AND
			`actueel` = 1";
			mysqli_query($db['link'], $qry);
		}
		//specificatie wegwijzers
		if (preg_match_all('#<tr>.*\[IMG].*href="(([0-9]{5})([0-9]{3})S\.png)"#Ui', $html, $res, PREG_SET_ORDER) > 0) {
			foreach($res as $item) {
				$kp_nr = $item[2];
				$ww_nr = $item[3];
				$img = $item[1];
				echo $kp_nr.'/'.$ww_nr.':'.$img.PHP_EOL;
				//update database
				$qry = "UPDATE `ww` SET 
				`afbeelding` = '".mysqli_real_escape_string($db['link'], $img)."'
				WHERE
				`kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."'
				AND
				`ww_nr` = '".mysqli_real_escape_string($db['link'], $ww_nr)."'
				AND
				`actueel` = 1";
				mysqli_query($db['link'], $qry);
			}
		}
	}
}
echo 'Verwerkingstijd: '.floor((time()-$start_time)/60).':'.((time()-$start_time)%60);
?>