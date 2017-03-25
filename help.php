<p>Wegwijzerkaart is een grafische interface voor het bekijken van de open dataset &quot;<a href="http://nationaalgeoregister.nl/geonetwork/srv/search/?uuid=LBZ3E7J2-0TEG-LF3H-OU23-VXS0CUO0UPY3">Nationale bewegwijzering</a>&quot; uit het Nationaal Georegister. Deze dataset bevat utilitaire bewegwijzering, kruispunten, wegwijzerlocaties, beheerders en situatieschetsen uit heel Nederland. De dataset wordt beheerd door de <a href="http://www.bewegwijzeringsdienst.nl/">Nationale Bewegwijzeringsdienst</a>.</p>

<h1>Werking</h1>
<p>Afhankelijk van het zoomniveau worden kruispunten (vijfcijferig) of wegwijzers (driecijferig) op de kaart getoond. Door op een kruispuntnummer of wegwijzernummer te klikken wordt een venster geopend met een situatieschets van het kruispunt of specificatie van de wegwijzer.</p>
<p>Via de zoekfunctie kan gezocht worden op kruispuntnummer en wegwijzernummer, door het opgeven van het betreffende nummer. Voorloopnullen zijn hierbij niet noodzakelijk. Er wordt automatisch onderscheid gemaakt tussen kruispunten en wegwijzers. Bij het zoeken naar wegwijzers dient zowel het kruispuntnummer als het wegwijzernummer gegeven te worden, gescheiden door een slash, spatie, koppelteken, punt of komma. Wanneer een achtcijferig nummer wordt opgegeven, wordt dit beschouwd als combinatie van vijfcijferig kruispuntnummer en driecijferig wegwijzernummer. In dit gevallen zijn voorloopnullen wel vereist. In alle andere gevallen wordt alleen gezocht op kruispuntnummer. Klik op een zoekresultaat uit de lijst om de bijbehorende kruispuntschets of wegwijzerspecificatie te openen. De kaart wordt automatisch gecentreerd op de locatie van het gekozen object.</p>

<h2>Kruispuntschets</h2>
<p>In dit venster wordt de informatie over een kruispunt uit de open dataset weergegeven. Tevens wordt een lijst gegeven van wegwijzers in de open dataset die bij dit kruispunt horen. Door op een dergelijk wegwijzernummer te klikken wordt de specificatie van deze wegwijzer geopend in een nieuw venster.</p>

<h2>Specificatie wegwijzer</h2>
<p>In dit venster wordt de informatie over een wegwijzer uit de open dataset weergegeven. Door op het kruispuntnummer (in de tabel achter <em>wegwijzernummer</em> te klikken wordt de kruispuntschets van het kruispunt horende bij deze wegwijzer geopend in een nieuw venster.</p>

<h2>Vensters sluiten</h2>
<p>Vensters kunnen middels het kruisje rechtsboven in het venster weer gesloten worden. Door op de <em>Delete</em> toets op het toetsenbord te drukken kunnen alle openstaande vensters in &eacute;&eacute;n keer worden gesloten.</p>

<h1>Compleetheid dataset</h1>
<p>De compleetheid van Wegwijzerkaart hangt samen met de compleetheid van de open dataset. Wat er niet in de open dataset zit, kan Wegwijzerkaart ook niet tonen. De actuele compleetheid van Wegwijzerkaart wordt in onderstaande tabel weergegeven.</p>

<?php
include('config.cfg.php');
$db['link'] = mysqli_connect($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
mysqli_set_charset($db['link'], "latin1");
//kruispunten
$qry = "SELECT (SELECT count(*) FROM `kp` WHERE `actueel` = 1), (SELECT count(*) FROM `kp` WHERE `actueel` = 1 AND `lat` != 0), (SELECT count(*) FROM `kp` WHERE `actueel` = 1 AND `afbeelding` IS NOT NULL)";
$res = mysqli_query($db['link'], $qry);
$kp = mysqli_fetch_row($res);
//wegwijzers
$qry = "SELECT (SELECT count(*) FROM `ww` WHERE `actueel` = 1), (SELECT count(*) FROM `ww` WHERE `actueel` = 1 AND `lat` != 0), (SELECT count(*) FROM `ww` WHERE `actueel` = 1 AND `afbeelding` IS NOT NULL)";
$res = mysqli_query($db['link'], $qry);
$ww = mysqli_fetch_row($res);
?>
<table>
<tr><th>Aantal kruispunten in database</th><td><?php echo number_format($kp[0], 0, ',', '.'); ?></td></tr>
<tr><th>Kruispunten met co&ouml;rdinaten</th><td><?php echo number_format($kp[1], 0, ',', '.'); ?> (<?php echo number_format($kp[1]/$kp[0]*100, 0, ',', '.'); ?>%)</td></tr>
<tr><th>Kruispunten met kruispuntschets</th><td><?php echo number_format($kp[2], 0, ',', '.'); ?> (<?php echo number_format($kp[2]/$kp[0]*100, 0, ',', '.'); ?>%)</td></tr>
<tr><th>Aantal wegwijzers in database</th><td><?php echo number_format($ww[0], 0, ',', '.'); ?></td></tr>
<tr><th>Wegwijzers met co&ouml;rdinaten</th><td><?php echo number_format($ww[1], 0, ',', '.'); ?> (<?php echo number_format($ww[1]/$ww[0]*100, 0, ',', '.'); ?>%)</td></tr>
<tr><th>Wegwijzers met specificatietekening</th><td><?php echo number_format($ww[2], 0, ',', '.'); ?> (<?php echo number_format($ww[2]/$ww[0]*100, 0, ',', '.'); ?>%)</td></tr>
</table>
<p>Voor meer informatie over de open dataset wordt verwezen naar de <a href="https://www.bewegwijzeringsdienst.nl/home/producten-en-diensten/open-data/">Nationale Bewegwijzeringsdienst</a>.</p>

<h1>Contact</h1>
<p>Stuur bij vragen, opmerkingen, suggesties of klachten over deze website een e-mail naar <img src="contact.png" width="103" height="18" alt="contact" style="vertical-align:text-top;">.</p>