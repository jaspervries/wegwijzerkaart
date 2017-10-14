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

//create config
if (!is_file('config.cfg.php')) {
	$config = '<?php
/*
 * Wegwijzerkaart configuration file
*/

//Database
$cfg_db[\'host\'] = \'localhost\';
$cfg_db[\'user\'] = \'root\';
$cfg_db[\'pass\'] = \'\';
$cfg_db[\'db\'] = \'wwk\';

//Resources
$cfg_resource[\'KP\'] = \'https://www.rijkswaterstaat.nl/apps/geoservices/geodata/regios/civ/bewegwijzering_open/KP.TXT\';
$cfg_resource[\'WW\'] = \'https://www.rijkswaterstaat.nl/apps/geoservices/geodata/regios/civ/bewegwijzering_open/WW.TXT\';
$cfg_resource[\'kpxy\'] = \'https://www.rijkswaterstaat.nl/apps/geoservices/geodata/regios/civ/bewegwijzering_open/kpxy.csv\';
$cfg_resource[\'wwxy\'] = \'https://www.rijkswaterstaat.nl/apps/geoservices/geodata/regios/civ/bewegwijzering_open/wwxy.csv\';
$cfg_resource[\'image_base\'] = \'https://www.rijkswaterstaat.nl/apps/geoservices/geodata/regios/civ/bewegwijzering_open/\'; //with trailing slash
$cfg_resource[\'uselocalimages\'] = TRUE; //toon lokale kruispuntplaatjes en specificatiestaten (TRUE) of verwijs altijd naar afbeeldingen op opendataportaal (FALSE)

//name of running file
$cfg_running_file = \'running\';
$cfg_use_time_limit = FALSE; //getimagefilenames herstarten na een bepaalde tijd om tijdlimiet op shared hosting te omzeilen; vanuit cronjob wordt hiermee ook downloadimages uitgeschakeld
$cfg_time_limit = 60; //toe te passen tijdliemiet

//Google
$cfg_google[\'maps_key\'] = \'\'; //Google Maps API key
/* unused
$cfg_google[\'analytics_key\'] = \'\'; //UA-trackernummer; laat leeg om Google Analytics tracking niet te gebruiken
*/
?>
';
	file_put_contents('config.cfg.php', $config);
	echo 'created config file'.PHP_EOL;
	echo 'PLEASE EDIT CONFIG FILE AND RUN INSTALL AGAIN!'.PHP_EOL;
	exit;
}
else {
	include('config.cfg.php');
	
	$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass']);
	mysqli_set_charset($db['link'], "latin1");
	
	$qry = "CREATE DATABASE IF NOT EXISTS `".$cfg_db['db']."`
	COLLATE 'latin1_general_ci'";
	if (mysqli_query($db['link'], $qry)) echo 'database created or exists'.PHP_EOL;
	else echo 'did not create database'.PHP_EOL;
	
	mysqli_select_db($db['link'], $cfg_db['db']);
	
	$qry = "CREATE TABLE IF NOT EXISTS `kp`
	(
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`kp_nr` INT(5) UNSIGNED NOT NULL,
		`naam` VARCHAR(255) NOT NULL,
		`provincie` VARCHAR(2) NOT NULL,
		`gemeente` VARCHAR(64) NOT NULL,
		`lat` DOUBLE NOT NULL DEFAULT 0,
		`lng` DOUBLE NOT NULL DEFAULT 0,
		`afbeelding` TINYTEXT NULL,
		`actueel` BOOLEAN NOT NULL DEFAULT 1,
		`stand` DATETIME NOT NULL,
		UNIQUE KEY (`kp_nr`,`naam`,`provincie`,`gemeente`)
	)
	ENGINE `MyISAM`,
	CHARACTER SET 'latin1', 
	COLLATE 'latin1_general_ci'";
	if (mysqli_query($db['link'], $qry)) echo 'table `kp` created or exists'.PHP_EOL;
	else echo 'did not create table `kp`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	
	$qry = "ALTER TABLE `kp`
	ADD
	`afbeelding_datum` DATETIME NULL
	AFTER
	`afbeelding`";
	if (mysqli_query($db['link'], $qry)) echo 'table `kp` modified'.PHP_EOL;
	else echo 'did not modify table `kp`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	
	$qry = "ALTER TABLE `kp`
	ADD
	`md5` VARCHAR(32) NULL
	AFTER
	`afbeelding_datum`";
	if (mysqli_query($db['link'], $qry)) echo 'table `kp` modified'.PHP_EOL;
	else echo 'did not modify table `kp`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	
	$qry = "CREATE TABLE IF NOT EXISTS `ww`
	(
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`kp_nr` INT(5) UNSIGNED NOT NULL,
		`ww_nr` INT(3) UNSIGNED NOT NULL,
		`provincie` VARCHAR(2) NOT NULL,
		`gemeente` VARCHAR(64) NOT NULL,
		`uitvoering` VARCHAR(1) NOT NULL,
		`type_wegwijzer` VARCHAR(1) NOT NULL,
		`type_constructie` VARCHAR(1) NOT NULL,
		`lat` DOUBLE NOT NULL DEFAULT 0,
		`lng` DOUBLE NOT NULL DEFAULT 0,
		`afbeelding` TINYTEXT NULL,
		`actueel` BOOLEAN NOT NULL DEFAULT 1,
		`stand` DATETIME NOT NULL,
		UNIQUE KEY (`kp_nr`,`ww_nr`,`provincie`,`gemeente`,`uitvoering`,`type_wegwijzer`,`type_constructie`,`lat`,`lng`)
	)
	ENGINE `MyISAM`,
	CHARACTER SET 'latin1', 
	COLLATE 'latin1_general_ci'";
	if (mysqli_query($db['link'], $qry)) echo 'table `ww` created or exists'.PHP_EOL;
	else echo 'did not create table `ww`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	
	$qry = "ALTER TABLE `ww`
	ADD
	`afbeelding_datum` DATETIME NULL
	AFTER
	`afbeelding`";
	if (mysqli_query($db['link'], $qry)) echo 'table `ww` modified'.PHP_EOL;
	else echo 'did not modify table `ww`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	
	$qry = "ALTER TABLE `ww`
	ADD
	`md5` VARCHAR(32) NULL
	AFTER
	`afbeelding_datum`";
	if (mysqli_query($db['link'], $qry)) echo 'table `ww` modified'.PHP_EOL;
	else echo 'did not modify table `ww`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	
	//create store
	if (!is_dir('store')) {
		mkdir('store');
		$subdirs = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
		foreach ($subdirs as $subdir) {
			mkdir('store/'.$subdir);
			foreach ($subdirs as $subsubdir) {
				mkdir('store/'.$subdir.'/'.$subsubdir);
			}
		}
		echo 'created store directories'.PHP_EOL;
	}
}
?>