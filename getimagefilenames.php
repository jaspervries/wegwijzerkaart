<?php
/*
This file is part of Wegwijzerkaart
Copyright (C) 2016-2017, 2019 Jasper Vries

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

set_time_limit(0);
$start_time = time();
include('config.cfg.php');
if (!isset($db['link'])) {
	$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
	mysqli_set_charset($db['link'], "latin1");
}

//function to convert filesizes in K, M or G to bytes
function KMGtoBytes($val) {
	$factor = 1000; //use 1000 for SI-bytes or 1024 for binary bytes
	if (is_numeric($val)) {
		return $val;
	}
	$suffix = substr($val, -1);
	$val = substr($val, 0, -1);
	if (!is_numeric($val)) {
		return FALSE;
	}
	if ($suffix == 'K') {
		return $val * $factor;
	}
	elseif ($suffix == 'M') {
		return $val * pow($factor, 2);
	}
	elseif ($suffix == 'G') {
		return $val * pow($factor, 3);
	}	
	else {
		return FALSE;
	}
}

function curl_get_contents($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$contents = curl_exec($ch);
	curl_close($ch);
	return $contents;
}

//bepaal waar begonnen moet worden
$startat = 0;
if (isset($current_item) && ($current_item > 0)) {
	$startat = $current_item;
}
$loop_terminated_immaturely = FALSE;
//haal hoofdpagina op
//$html = curl_get_contents($cfg_resource['image_base']);
$html = file_get_contents($cfg_resource['image_base']);
//verkrijg alle mappen op pagina
preg_match_all('#<a href="([0-9]{5}/)"#U', $html, $main_dir_folders);
foreach($main_dir_folders[1] as $folder) {
	//controleer of deze moet worden overgeslagen
	if (substr($folder, 0, -1) < (floor($startat/1000)*1000)) continue;
	//haal mappagina op
	//$html = curl_get_contents($cfg_resource['image_base'].$folder);
	$html = file_get_contents($cfg_resource['image_base'].$folder);
	//verkrijg alle kruispunten in map
	preg_match_all('#<a href="([0-9]{5}/)"#U', $html, $kruispunten);
	foreach($kruispunten[1] as $kruispunt) {
		$current_item = substr($kruispunt, 0, -1);
		//controleer of deze moet worden overgeslagen
		if ($current_item < $startat) continue;
		//haal kruispuntpagina op
		//$html = curl_get_contents($cfg_resource['image_base'].$folder.$kruispunt);
		$html = file_get_contents($cfg_resource['image_base'].$folder.$kruispunt);
		//kruispuntschets
		if (preg_match('#<a href="((\d{5})K\.png)".*(\d{2}-[a-z]+-\d{4} \d{2}:\d{2}).*(\d+\.?\d*[KMG])#Ui', $html, $res) > 0) {
						$kp_nr = $res[2];
			$img = $res[1];
			$date = $res[3];
			$size = $res[4];
			$totalfilesize += KMGtoBytes($size);
			echo $kp_nr . "\t\t" . $img . "\t" . $date . "\t" . $size . PHP_EOL;
			//update database
			$qry = "UPDATE `kp` SET 
			`afbeelding` = '".mysqli_real_escape_string($db['link'], $img)."',
			`afbeelding_datum` = '".date('Y-m-d H:i:s', strtotime($date))."'
			WHERE
			`kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."'
			AND
			`actueel` = 1";
			mysqli_query($db['link'], $qry);
		}
		//specificatie wegwijzers
		if (preg_match_all('#<a href="(([0-9]{5})([0-9]{3})S\.png)".*(\d{2}-[a-z]+-\d{4} \d{2}:\d{2}).*(\d+\.?\d*[KMG])#Ui', $html, $res, PREG_SET_ORDER) > 0) {
			foreach($res as $item) {
				$kp_nr = $item[2];
				$ww_nr = $item[3];
				$img = $item[1];
				$date = $item[4];
				$size = $item[5];
				$totalfilesize += KMGtoBytes($size);
				echo $kp_nr.'/'.$ww_nr . "\t" . $img . "\t" . $date . "\t" . $size . PHP_EOL;
				//update database
				$qry = "UPDATE `ww` SET 
				`afbeelding` = '".mysqli_real_escape_string($db['link'], $img)."',
				`afbeelding_datum` = '".date('Y-m-d H:i:s', strtotime($date))."'
				WHERE
				`kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."'
				AND
				`ww_nr` = '".mysqli_real_escape_string($db['link'], $ww_nr)."'
				AND
				`actueel` = 1";
				mysqli_query($db['link'], $qry);
			}
		}
		//bekijk of lus moet worden beeindigd
		if (isset($time_left) && (($time_left - (time() - $start_time)) < 5)) {
			//minder dan 5 seconden over, beeindig lus
			$loop_terminated_immaturely = TRUE;
			break 2;
		}
	}
}

if ($loop_terminated_immaturely == FALSE) {
	$current_task_done = TRUE;
}

echo 'Verwerkingstijd: '.floor((time()-$start_time)/60).':'.str_pad(((time()-$start_time)%60), 2, '0', STR_PAD_LEFT) . PHP_EOL;
echo 'Totale bestandsgrootte afbeeldingen: ' . $totalfilesize . ' bytes (' . round($totalfilesize/pow(1000,3),3) . 'G)';

?>