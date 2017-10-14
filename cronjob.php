<?php
/*
This file is part of Wegwijzerkaart
Copyright (C) 2017 Jasper Vries

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

//change working directory to script path
chdir(dirname(__FILE__));

//check if job is running, abort
if (is_file($cfg_running_file)) {
	exit;
}
else {
	file_put_contents($cfg_running_file, '');
	include('update.php');	
	include('getimagefilenames.php');	
	include('downloadimages.php');	
	unlink($cfg_running_file);
}
?>