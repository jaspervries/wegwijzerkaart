<?php
/*
This file is part of Wegwijzerkaart
Copyright (C) 2017, 2019 Jasper Vries

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

/*
lijst van update taken
1 kp
2 kpxy
3 ww/wwxy
4 getimagefilenames (gebruikt ook item entry voor tussentijdse herstart)
5 downloadimages (gebruikt ook item entry voor tussentijdse herstart)
*/

set_time_limit(0);
include('config.cfg.php');

$time_start = time();

//connect met database
$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
mysqli_set_charset($db['link'], "latin1");

while(TRUE) {
	$current_task_done = FALSE;
	$current_task = 0;
	//verkrijg meest recente update status
	$qry = "SELECT * FROM `updatelog` ORDER BY `id` DESC LIMIT 1";
	$res = mysqli_query($db['link'], $qry);
	if (mysqli_num_rows($res)) {
		$updatestate = mysqli_fetch_assoc($res);
	}
	else {
		//dit is de allereerste entry
		$updatestate = array('finished' => 1);
	}

	//controleer of proces nog bezig is
	if ($updatestate['finished'] == 0) {
		//als nog geen timeout, exit
		if (($updatestate['lastupdate'] + $cfg_timeout_limit) > $time_start) {
			echo 'no timeout yet' . PHP_EOL;
			echo $updatestate['lastupdate'] . PHP_EOL;
			echo $time_start . PHP_EOL;
			exit;
		}
		//ga verder met huidige taak
		$current_task = $updatestate['task'];
		$current_item = $updatestate['item'];
	}
	else {
		//start volgende taak
		$current_task = $updatestate['task'] + 1;
		$current_item = NULL;
		//begin bij begin wanneer laatste taak klaar is
		if ($current_task == 6) {
			$current_task = 1;
		}
		elseif (($cfg_resource['uselocalimages'] == FALSE) && ($current_task == 5)) {
			$current_task = 1;
		}
		//controleer of taak 1 mag starten
		if ($current_task == 1) {
			//bepaal vorige taak 1
			$qry = "SELECT `starttime` FROM `updatelog` WHERE `task` = 1 ORDER BY `id` DESC LIMIT 1";
			$res = mysqli_query($db['link'], $qry);
			if (mysqli_num_rows($res)) {
				$res = mysqli_fetch_assoc($res);
				if (($res['starttime'] + $cfg_runonce_limit) > $time_start) {
					echo 'no allowed to start task 1' . PHP_EOL;
					echo $res['starttime'] . PHP_EOL;
					echo $time_start . PHP_EOL;
					exit;
				}
			}
		}
		//maak nieuwe entry voor nieuwe taak
		$qry = "INSERT INTO `updatelog` SET
		`lastupdate` = " . time() . ",
		`starttime` = " . $time_start . ",
		`task` = '" . $current_task . "'";
		mysqli_query($db['link'], $qry);
		$updatestate['id'] = mysqli_insert_id($db['link']);
	}

	//bepaal resterende tijd
	$time_left = $cfg_runtime_limit - 5 - (time() - $time_start); //vijf seconden marge
	//als er nog minstens twintig seconden zijn om iets te doen
	if ($time_left >= 20) {
		if (($current_task > 0) && ($current_task <= 3)) {
			include('update.php');
		}
		elseif ($current_task == 4) {
			include('getimagefilenames.php');
		}
		//download alleen afbeeldingen als we ze gebruiken
		elseif (($current_task == 5) && ($cfg_resource['uselocalimages'] == TRUE)) {
			include('downloadimages.php');	
		}
		//sluit taak af
		if ($current_task_done == TRUE) {
			$qry = "UPDATE `updatelog` SET
			`lastupdate` = " . time() . ", ";
			if (!empty($current_item)) {
				$qry .= "`item` = '" . $current_item . "', ";
			}
			$qry .= "`finished` = 1
			WHERE `id` = " . $updatestate['id'];
		}
		else {
			$qry = "UPDATE `updatelog` SET
			`lastupdate` = " . time() . ",
			`item` = '" . $current_item . "'
			WHERE `id` = " . $updatestate['id'];
		}
		mysqli_query($db['link'], $qry);
	}
	else {
		//er is niet genoeg tijd om iets nieuws te beginnen
		exit;
	}
}

?>