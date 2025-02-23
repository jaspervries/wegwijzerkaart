<?php
/*
 	fietsviewer - grafische weergave van fietsdata
    Copyright (C) 2018 Jasper Vries, Gemeente Den Haag
 
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/*
* function to check map's bbox string and convert to sql component
* $bounds in format (str) southwest_lng,southwest_lat,northeast_lng,northeast_lat
*/
function bounds_to_sql($bounds) {
	$bounds = explode(',', $bounds);
	for ($i = 0; $i < 4; $i++) {
		if (!is_numeric($bounds[$i]) || ($bounds[$i] < -180) || ($bounds[$i] > 180)) {
			$bounds[$i] = 0;
		}
	}
	$lat_lower = min($bounds[1], $bounds[3]);
	$lat_upper = max($bounds[1], $bounds[3]);
	$lon_lower = min($bounds[0], $bounds[2]);
	$lon_upper = max($bounds[0], $bounds[2]);
	
	return ' `lat` BETWEEN '. $lat_lower . ' AND '. $lat_upper . ' 
	AND `lng` BETWEEN '. $lon_lower . ' AND '. $lon_upper . ' ';
}
?>