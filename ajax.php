<?php 
/*
This file is part of Wegwijzerkaart
Copyright (C) 2016-2017, 2025 Jasper Vries

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
session_start();
include('config.cfg.php');
include('types.cfg.php');
include('functions/bounds_to_sql.php');
$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
mysqli_set_charset($db['link'], "latin1");

function get_protocol() {
	if (isset($_SERVER['HTTPS']) && //from https://stackoverflow.com/questions/4503135/php-get-site-url-protocol-http-vs-https
		($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
		isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		return 'https://';
	}
	else {
		return 'http://';
	}
}

//kruispunten
if ($_GET['type'] == 'kp') {
	$qry = "SELECT `id`, `kp_nr`, `lat`, `lng` FROM `kp`
	WHERE `lat` != 0
	AND `lng` != 0
	AND " . bounds_to_sql($_GET['bounds']) . "
	AND `actueel` = 1";
	
	$res = mysqli_query($db['link'], $qry);
	if (mysqli_num_rows($res)) {
		$content = array();
		while ($data = mysqli_fetch_row($res)) {
			//add to output
			$content[] = array($data[0], str_pad($data[1], 5, '0', STR_PAD_LEFT), $data[2], $data[3]);
		}
	}
	//return json
	header('Content-type: application/json');
	echo json_encode($content);
}
//wegwijzers
if ($_GET['type'] == 'ww') {
	//extract minimum and maximum coordinates
	$coords = $_GET['bounds'];
	$coords = substr($coords, 2, -2);
	$coords = explode('), (', $coords);
	for ($i = 0; $i < count($coords); $i++) {
		$coords[$i] = explode(', ', $coords[$i]);
	}
	$qry = "SELECT `id`, `ww_nr`, `lat`, `lng`, `kp_nr` FROM `ww`
	WHERE `lat` != 0
	AND `lng` != 0
	AND " . bounds_to_sql($_GET['bounds']) . "
	AND `actueel` = 1";
	
	$res = mysqli_query($db['link'], $qry);
	if (mysqli_num_rows($res)) {
		$content = array();
		while ($data = mysqli_fetch_row($res)) {
			//add to output
			$content[] = array($data[0], str_pad($data[1], 3, '0', STR_PAD_LEFT), $data[2], $data[3], str_pad($data[4], 5, '0', STR_PAD_LEFT).'/'.str_pad($data[1], 3, '0', STR_PAD_LEFT));
		}
	}
	//return json
	header('Content-type: application/json');
	echo json_encode($content);
}
//kruipunt dialog
if ($_GET['type'] == 'dialogkp') {
	$html = 'Kan kruispuntgegevens niet laden.';
	$kp_nr = 'n/b';
	$qry = "SELECT * FROM `kp` WHERE `id` = '".mysqli_real_escape_string($db['link'], $_GET['id'])."' LIMIT 1";
	if ($_GET['alt'] == 'true') $qry = "SELECT * FROM `kp` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $_GET['id'])."' AND `actueel` = 1 LIMIT 1";
	$res = mysqli_query($db['link'], $qry);
	if (mysqli_num_rows($res)) {
		$data = mysqli_fetch_assoc($res);
		$kp_nr = str_pad($data['kp_nr'], 5, '0', STR_PAD_LEFT);
		$html = '<table>';
		$html .= '<tr><td>kruispuntnummer:</td><td>'.$kp_nr.'</td></tr>';
		$html .= '<tr><td>kruispunt:</td><td>'.htmlspecialchars($data['naam']).'</td></tr>';
		$html .= '<tr><td>gemeente:</td><td>'.htmlspecialchars(ucwords(strtolower($data['gemeente']))).'</td></tr>';
		$html .= '<tr><td>provincie:</td><td>'.(array_key_exists($data['provincie'], $ww_types['provincie']) ? $ww_types['provincie'][$data['provincie']] : htmlspecialchars($data['provincie'])).'</td></tr>';
		$html .= '<tr><td>WGS 84:</td><td>' . (!empty($data['lat']) ? '<span class="wgs84-dialog">' . round($data['lat'],7) . ',' . round($data['lng'],7) . '</span>' : 'onbekend') . '</td></tr>';
		if (!empty($data['afbeelding'])) {
			$html .= '<tr><td>publicatiedatum tekening:</td><td>'.date('d-m-Y H:i', strtotime($data['afbeelding_datum'])).'</td></tr>';
		}
		$html .= '</table>';
		$qry = "SELECT `id`, `ww_nr`, `type_wegwijzer` FROM `ww` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $data['kp_nr'])."' AND `actueel` = 1 ORDER BY `ww_nr`";
		$res = mysqli_query($db['link'], $qry);
		if (mysqli_num_rows($res)) {
			$html .= '<div rel="'.$kp_nr.'">Wegwijzers: ';
			while ($row = mysqli_fetch_row($res)) {
				$html .= '<span class="ww-nr-dialog';
				if (in_array($row[2], array('F', 'T'))) $html .= ' ww-type-fiets';
				elseif ($row[2] == 'Q') $html .= ' ww-type-voet';
				elseif (in_array($row[2], array('L', 'V', 'B', 'R', 'D', 'E', 'A', 'H', 'S', 'M'))) $html .= ' ww-type-auto';
				$html .= '">'.str_pad($row[1], 3, '0', STR_PAD_LEFT).'</span>';
			}
			$html .= '</div>';
		}
		if (($cfg_resource['uselocalimages'] == TRUE) && (!empty($data['md5']))) {
			$file = 'store/' . substr($data['md5'],0,1) . '/' . substr($data['md5'],1,1) . '/' . $data['md5'] . '.png';
			$html .= '<img src="'.$file.'" width="100%" alt="Kruispuntschets '.$kp_nr.'">';
		}
		elseif (!empty($data['afbeelding'])) {
			$html .= '<img src="'.$cfg_resource['image_base'].substr($kp_nr, 0, 2).'000/'.$kp_nr.'/'.urlencode($data['afbeelding']).'" width="100%" alt="Kruispuntschets '.$kp_nr.'">';
		}
		else {
			$html .= '<p class="warning">Geen kruispuntschets beschikbaar voor dit kruispunt.</p>';
		}
		$html .= '<table class="fw"><tr>';
		//link naar open data
		$html .= '<td><a href="'.$cfg_resource['image_base'].substr($kp_nr, 0, 2).'000/'.$kp_nr.'/" target="_blank">Opendataportaal</a></td>';
		//deeplink
		if (!empty($data['lat']) && !empty($data['lng'])) {
			$url_base = $_SERVER['SERVER_NAME'] . dirname($_SERVER["SCRIPT_NAME"]);
			$url = get_protocol() . $url_base . '?q=' . $kp_nr;
			$html .= '<td><a href="' . $url . '" class="share">' . $kp_nr . '</a></td>';
		}
		$html .= '</tr></table>';
	}
	//return json
	header('Content-type: application/json');
	echo json_encode(array('title' => $kp_nr, 'html' => utf8_encode($html)));
}
//wegwijzer dialog
if ($_GET['type'] == 'dialogww') {
	$html = 'Kan wegwijzergegevens niet laden.';
	$kp_nr = 'n/b';
	$qry = "SELECT * FROM `ww` WHERE `id` = '".mysqli_real_escape_string($db['link'], $_GET['id'])."' LIMIT 1";
	if ($_GET['alt'] == 'true') $qry = "SELECT * FROM `ww` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], substr($_GET['id'], 0, 5))."' AND `ww_nr` = '".mysqli_real_escape_string($db['link'], substr($_GET['id'], 5, 3))."' AND `actueel` = 1 LIMIT 1";
	$res = mysqli_query($db['link'], $qry);
	if (mysqli_num_rows($res)) {
		$data = mysqli_fetch_assoc($res);
		$kp_nr = str_pad($data['kp_nr'], 5, '0', STR_PAD_LEFT);
		$ww_nr = str_pad($data['ww_nr'], 3, '0', STR_PAD_LEFT);
		$html = '<table>';
		$html .= '<tr><td>wegwijzernummer:</td><td><span class="kp-nr-dialog">'.$kp_nr.'</span>/'.$ww_nr.'</td></tr>';
		$html .= '<tr><td>gemeente:</td><td>'.htmlspecialchars(ucwords(strtolower($data['gemeente']))).'</td></tr>';
		$html .= '<tr><td>provincie:</td><td>'.(array_key_exists($data['provincie'], $ww_types['provincie']) ? $ww_types['provincie'][$data['provincie']] : htmlspecialchars($data['provincie'])).'</td></tr>';
		$html .= '<tr><td>WGS 84:</td><td>' . (!empty($data['lat']) ? '<span class="wgs84-dialog">' . round($data['lat'],7) . ',' . round($data['lng'],7) . '</span>' : 'onbekend') . '</td></tr>';
		$html .= '<tr><td>uitvoering:</td><td>'.(array_key_exists($data['uitvoering'], $ww_types['uitvoering']) ? $ww_types['uitvoering'][$data['uitvoering']] : $data['uitvoering']).'</td></tr>';
		$html .= '<tr><td>type wegwijzer:</td><td>'.(array_key_exists($data['type_wegwijzer'], $ww_types['type_wegwijzer']) ? $ww_types['type_wegwijzer'][$data['type_wegwijzer']] : $data['type_wegwijzer']).'</td></tr>';
		$html .= '<tr><td>type constructie:</td><td>'.(array_key_exists($data['type_constructie'], $ww_types['type_constructie']) ? $ww_types['type_constructie'][$data['type_constructie']] : $data['type_constructie']).'</td></tr>';
		if (!empty($data['afbeelding'])) {
			$html .= '<tr><td>publicatiedatum specificatie:</td><td>'.date('d-m-Y H:i', strtotime($data['afbeelding_datum'])).'</td></tr>';
		}
		$html .= '</table>';
		if (($cfg_resource['uselocalimages'] == TRUE) && (!empty($data['md5']))) {
			$file = 'store/' . substr($data['md5'],0,1) . '/' . substr($data['md5'],1,1) . '/' . $data['md5'] . '.png';
			$html .= '<img src="'.$file.'" width="100%" alt="Specificatie wegwijzer '.$kp_nr.'/'.$ww_nr.'">';
		}
		elseif (!empty($data['afbeelding'])) {
			$html .= '<img src="'.$cfg_resource['image_base'].substr($kp_nr, 0, 2).'000/'.$kp_nr.'/'.urlencode($data['afbeelding']).'" width="100%" alt="Specificatie wegwijzer '.$kp_nr.'/'.$ww_nr.'">';
		}
		else {
			$html .= '<p class="warning">Geen specificatie beschikbaar voor deze wegwijzer.</p>';
		}
		$html .= '<table class="fw"><tr>';
		//link naar open data
		$html .= '<td><a href="'.$cfg_resource['image_base'].substr($kp_nr, 0, 2).'000/'.$kp_nr.'/" target="_blank">Opendataportaal</a></td>';
		//kijk of er een foto is
		$photo_url = $cfg_resource['photo_base'] . $kp_nr . $ww_nr . 'F.JPG';
		$headers = @get_headers($photo_url);
		if(strpos($headers[0],'200')!==false) {
			$html .= '<td><a href="' . $photo_url . '" target="_blank">Foto</a></td>';
		}
		//deeplink
		if (!empty($data['lat']) && !empty($data['lng'])) {
			$url_base = $_SERVER['SERVER_NAME'] . dirname($_SERVER["SCRIPT_NAME"]);
			$url = get_protocol() . $url_base . '?q=' . $kp_nr . '/' . $ww_nr;
			$html .= '<td><a href="' . $url . '" class="share">' . $kp_nr . '/' . $ww_nr . '</a></td>';
		}
		$html .= '</tr></table>';
	}
	//return json
	header('Content-type: application/json');
	echo json_encode(array('title' => $kp_nr.'/'.$ww_nr, 'html' => utf8_encode($html)));
}
//help dialog
if ($_GET['type'] == 'dialoghelp') {
	//return json
	header('Content-type: application/json');
	ob_start();
	include('help.php');
	$html = ob_get_clean();
	echo json_encode(array('title' => 'Help', 'html' => utf8_encode($html)));
}

//search results
if ($_GET['type'] == 'search') {
	$json = array();
	//verwerk zoekterm
	$kp_nr = 0;
	$ww_nr = 0;
	if (preg_match('#^([0-9]{1,5})[ /,\.-]([0-9]{1,3})$#U', $_GET['term'], $matches)) {
		$kp_nr = $matches[1];
		$ww_nr = $matches[2];
	}
	elseif (is_numeric($_GET['term'])) {
		if (strlen($_GET['term']) == 8) {
			$kp_nr = substr($_GET['term'], 0, 5);
			$ww_nr = substr($_GET['term'], 5, 3);
		}
		else {
			$kp_nr = $_GET['term'];
		}
	}
	//zoek wegwijzers
	if (($kp_nr > 0) && ($ww_nr > 0)) {
		$qry = "SELECT `id`, `kp_nr`, `ww_nr`, `lat`, `lng` FROM `ww` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."' AND `ww_nr` = '".mysqli_real_escape_string($db['link'], $ww_nr)."' AND `actueel` = 1 LIMIT 1";
		$res = mysqli_query($db['link'], $qry);
		if (mysqli_num_rows($res)) {
			$data = mysqli_fetch_assoc($res);
			$json[] = array('id' => $data['id'], 'label' => str_pad($data['kp_nr'], 5, '0', STR_PAD_LEFT).'/'.str_pad($data['ww_nr'], 3, '0', STR_PAD_LEFT), 'type' => 'ww', 'latlng' => $data['lat'].','.$data['lng']);
		}
	}
	//zoek kruispunten
	if ($kp_nr > 0) {
		$qry = "SELECT `id`, `kp_nr`, `lat`, `lng` FROM `kp` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."' AND `actueel` = 1 LIMIT 1";
		$res = mysqli_query($db['link'], $qry);
		if (mysqli_num_rows($res)) {
			$data = mysqli_fetch_assoc($res);
			$json[] = array('id' => $data['id'], 'label' => str_pad($data['kp_nr'], 5, '0', STR_PAD_LEFT), 'type' => 'kp', 'latlng' => $data['lat'].','.$data['lng']);
		}
	}
	//wanneer geen resultaten
	if (empty($json)) {
		$json[] = array('label' => 'Geen resultaten');
	}
	//return json
	header('Content-type: application/json');
	echo json_encode($json);
}

//urlvars (?q=)
if ($_GET['type'] == 'urlvars') {
	$json = array();
	//kruispunt
	if (isset($_GET['kp']) && ($_GET['kp'] == 1)) {
		$kp_nr = 0;
		if (is_numeric($_GET['q']) && (strlen($_GET['q']) == 5)) {
			$kp_nr = $_GET['q'];
			//zoek wegwijzers
			if ($kp_nr > 0) {
				$qry = "SELECT `id`, `kp_nr`, `lat`, `lng` FROM `kp` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."' AND `actueel` = 1 LIMIT 1";
				$res = mysqli_query($db['link'], $qry);
				if (mysqli_num_rows($res)) {
					$data = mysqli_fetch_assoc($res);
					$json['latlng'] = $data['lat'].','.$data['lng'];
				}
			}
		}
	}
	//wegwijzer
	else {
		$kp_nr = 0;
		$ww_nr = 0;
		if (is_numeric($_GET['q']) && (strlen($_GET['q']) == 8)) {
			$kp_nr = substr($_GET['q'], 0, 5);
			$ww_nr = substr($_GET['q'], 5, 3);
			//zoek wegwijzers
			if (($kp_nr > 0) && ($ww_nr > 0)) {
				$qry = "SELECT `id`, `kp_nr`, `ww_nr`, `lat`, `lng` FROM `ww` WHERE `kp_nr` = '".mysqli_real_escape_string($db['link'], $kp_nr)."' AND `ww_nr` = '".mysqli_real_escape_string($db['link'], $ww_nr)."' AND `actueel` = 1 LIMIT 1";
				$res = mysqli_query($db['link'], $qry);
				if (mysqli_num_rows($res)) {
					$data = mysqli_fetch_assoc($res);
					$json['latlng'] = $data['lat'].','.$data['lng'];
				}
			}
		}
	}
	//wanneer geen resultaten
	if (empty($json)) {
		$json['res'] = false;
	}
	//return json
	header('Content-type: application/json');
	echo json_encode($json);
}
?>