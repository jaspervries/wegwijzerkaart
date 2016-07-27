<?php
/*
 * Functie om coordinaten volgens de Rijksdriehoeksmeting (RD) te transformeren naar WGS84-coordinaten.
 * Volgens "Benaderingsformules voor de transformatie tussen RD- en WGS84-kaartcoordinaten", Schreutelkamp en Strang van Hees.
 * Bron: http://home.solcon.nl/pvanmanen/Download/Transformatieformules.pdf
 * Deze implementatie in PHP (C) 2013 Gemeente Den Haag
*/
function rd2wgs84($X,$Y) {
	//vervang komma door punt en cast als double
	$X = (double) str_replace(',','.',$X);
	$Y = (double) str_replace(',','.',$Y);
	//coefficienten
	$pqK = array(
		array(0, 1, 3235.65389),
		array(2, 0, -32.58297),
		array(0, 2, -0.24750),
		array(2, 1, -0.84978),
		array(0, 3, -0.06550),
		array(2, 2, -0,01709),
		array(1, 0, -0.00738),
		array(4, 0, 0.00530),
		array(2, 3, -0,00039),
		array(4, 1, 0.00033),
		array(1, 1, -0,00012)
	);
	$pqL = array (
		array(1, 0, 5260.52916),
		array(1, 1, 105.94684),
		array(1, 2, 2.45656),
		array(3, 0, -0.81885),
		array(1, 3, 0.05594),
		array(3, 1, -0.05607),
		array(0, 1, 0.01199),
		array(3, 2, -0.00256),
		array(1, 4, 0.00128),
		array(0, 2, 0.00022),
		array(2, 0, -0.00022),
		array(5, 0, 0.00026)
	);
	$X0 = 155000;
	$Y0 = 463000;
	$phi0 = 52.15517440;
	$labda0 = 5.38720621;
	//delta RD
	$dX = ($X - $X0) * pow(10,-5);
	$dY = ($Y - $Y0) * pow(10,-5);
	//phi
	$phi = 0;
	foreach ($pqK as $c) {
		$phi += $c[2] * pow($dX,$c[0]) * pow($dY,$c[1]);
	}
	$phi = $phi0 + $phi / 3600;
	//labda
	$labda = 0;
	foreach ($pqL as $c) {
		$labda += $c[2] * pow($dX,$c[0]) * pow($dY,$c[1]);
	}
	$labda = $labda0 + $labda / 3600;
	//return
	return array($phi,$labda);
}
?>