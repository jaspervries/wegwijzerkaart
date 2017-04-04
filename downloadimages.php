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
$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
mysqli_set_charset($db['link'], "latin1");
set_time_limit(0);
$start_time = time();
$targetimagewidth = 1920;

//definieer typen
$types = array('kp', 'ww');
foreach ($types as $type) {
	//download kruispuntplaatjes
	if ($type == 'kp') {
		$qry = "SELECT `id`, `kp_nr`, `afbeelding`
		FROM `kp`";
	}
	elseif ($type == 'ww') {
		$qry = "SELECT `id`, `kp_nr`, `ww_nr`, `afbeelding`
		FROM `ww`";
	}
	$qry .= " WHERE `afbeelding` IS NOT NULL
	AND `afbeelding_datum` IS NOT NULL
	AND `md5` IS NULL
	AND `actueel` = 1";
	$res = mysqli_query($db['link'], $qry);
	while ($data = mysqli_fetch_assoc($res)) {
		//get file
		$kp_nr = str_pad($data['kp_nr'], 5, '0', STR_PAD_LEFT);
		echo $kp_nr . "\t";
		if ($type == 'ww') {
			$ww_nr = str_pad($data['ww_nr'], 3, '0', STR_PAD_LEFT);
			echo $ww_nr . "\t";
		}
		$url = $cfg_resource['image_base'].substr($kp_nr, 0, 2).'000/'.$kp_nr.'/'.urlencode($data['afbeelding']);
		$image = @file_get_contents($url);
		if ($image !== FALSE) {
			//convert file
			$gd_image = imagecreatefromstring($image);
			if ($gd_image !== FALSE) {
				//resize image if width > target width
				$x = imagesx($gd_image);
				$y = imagesy($gd_image);
				if ($x > $targetimagewidth) {
					
					//calculate target height
					$h = round($y * $targetimagewidth / $x);
					$w = $targetimagewidth;
				}
				else {
					$h = $y;
					$w = $x;
				}
				$gd_image2 = imagecreatetruecolor($w, $h);
				//note: imagescale doesn't work, as it throws away color, it also appears slower. imagecopyresampled is significantly slower
				imagecopyresized($gd_image2 , $gd_image , 0 , 0 , 0 , 0 , $w , $h , $x , $y);
				imagedestroy($gd_image);
				//convert to 8 bit
				imagetruecolortopalette($gd_image2, false, 255);
				//output image to string
				ob_start();
				ob_clean();
				imagepng($gd_image2, NULL, 9);
				//imagejpeg($gd_image, NULL, 70);
				$image_data = ob_get_contents();
				ob_end_clean();
				imagedestroy($gd_image2);
				//get image hash
				$md5 = strtoupper(md5($image_data));
				echo $md5 . "\t";
				//check if image exists and store it
				$file = 'store/' . substr($md5,0,1) . '/' . substr($md5,1,1) . '/' . $md5 . '.png';
				if (!file_exists($file)) {
					file_put_contents($file, $image_data);
					echo 'STORED';
				}
				else {
					echo 'EXISTS';
				}
				//add to database
				if ($type == 'kp') {
					$qry = "UPDATE `kp` SET";
				}
				elseif ($type == 'ww') {
					$qry = "UPDATE `ww` SET";
				}
				$qry .= " `md5` = '".$md5."'
				WHERE `id` = '".$data['id']."'";
				mysqli_query($db['link'], $qry);
			}
		}
		else {
			echo 'CANNOT DOWNLOAD IMAGE FILE';
		}
		echo PHP_EOL;
	}
}

echo 'Verwerkingstijd: '.floor((time()-$start_time)/60).':'.str_pad(((time()-$start_time)%60), 2, '0', STR_PAD_LEFT) . PHP_EOL;
?>