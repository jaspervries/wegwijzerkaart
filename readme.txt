========================================================================
                       Wegwijzerkaart README
========================================================================

Wegwijzerkaart is een grafische interface voor het bekijken van de open 
dataset "Nationale bewegwijzering" uit het Nationaal Georegister.
http://nationaalgeoregister.nl/geonetwork/srv/search/?uuid=LBZ3E7J2-0TEG-LF3H-OU23-VXS0CUO0UPY3
Deze dataset bevat utilitaire bewegwijzering, kruispunten, 
wegwijzerlocaties, beheerders en situatieschetsen uit heel Nederland.

Wegwijzerkaart kopieert de data uit de dataset naar een lokale database 
en presenteert de inhoud van deze database op een Google Maps 
ondergrond. Afbeeldingen van kruispunten en wegwijzers worden direct 
geladen vanaf de open data server, of kunnen worden gekopieerd naar de
lokale server.


========================================================================
0. Inhoudsopgave
========================================================================

1. Systeemvereisten en benodigdheden
3. Installatie
4. Dataformaat
    4.1 KP.TXT
    4.2 WW.TXT
    4.3 kpxy.csv
    4.4 wwxy.csv
6. Licentie
7. Verkrijgen van de broncode


========================================================================
1. Systeemvereisten en benodigdheden
========================================================================

Voor de grafische interface is een recente webbrowser met 
ondersteuning voor HTML5 nodig. Primaire ontwikkeling vindt plaats 
voor Mozilla Firefox en releases worden getest in Chromium en Firefox 
Mobile.

Voor de backend is een webserver met PHP (5.3+) en MySQL (5+) of 
MariaDB (5+) nodig. Optioneel kan Cron of een vergelijkbare toepassing 
worden ingezet om de database automatisch periodiek bij te werken.

URLs:
Mozilla Firefox: https://www.mozilla.org/firefox
Chromium: https://www.chromium.org
Chromium (Windows build): http://chromium.woolyss.com
PHP: http://php.net
MySQL: https://www.mysql.com
MariaDB: https://mariadb.org


========================================================================
3. Installatie
========================================================================

De installatie maakt de databasetabellen en mappenstructuur aan. 
Voer install.php uit vanuit een opdrachtregel. Als er nog 
geen configuratiebestand aanwezig is, wordt dit aangemaakt. Open dit 
met een teksteditor en vul de juiste databasecredentials is. Voer 
hierna install.php nogmaals uit om de databasetabellen en 
mappenstructuur aan te maken.

Wanneer de installatie gereed is kunnen de tabellen worden gevuld. Voer 
hiervoor update.php uit. Hiermee worden vier tekstbestanden van de open 
data server gedownload en samengevoegd in de database.

Om situatietekeningen van kruispunten en specificaties van wegwijzers 
te kunnen bekijken moeten de bestandsnamen van deze afbeeldingen op de 
open data server geindexeerd worden. Deze zijn namelijk niet van alle 
kruispunten en wegwijzers beschikbaar. Voer hiervoor 
getimagefilenames.php uit. Het indexeren duurt tussen 20 en 35 minuten.

Tot slot kunnen de situatietekeningen van kruispunten en specificaties 
van wegwijzers naar de lokale server worden gedownload. Dit is een 
optionele stap. Wegwijzerkaart werkt ook zonder deze lokale kopie van 
alle afbeeldingen, maar biedt dan vanzelfsprekend geen garantie op 
continuiteit. Voer downloadimages.php uit om alle afbeeldingen te 
downloaden. Om schrijfruimte te besparen worden de afbeeldingen 
verkleind en geconverteerd naar 8bpp PNG opgeslagen. Bij de initiele 
populatie is dit een langdurig proces dat enkele dagen in beslag kan 
nemen. Hierbij wordt ongeveer 35 GB aan data gedownload en benodigd 
ongeveer 15 GB vrije schijfruimte. Bij opvolgende updates worden enkel
gewijzigde en nieuwe bestanden gedownload.


========================================================================
4. Dataformaat
========================================================================
Dit hoofdstuk bevat een beschrijving van het dataformaat van het open
data portaal. 

4.1 KP.TXT
------------------------------------------------------------------------
Bestand met nummering en locatieomschrijving van alle kruispunten.
Kolommen:
[0] kruispuntnummer
[1] straatnamen en/of wegnummers van de kruisende wegen
[2] afkorting van de provincie
[3] plaats
de betekenis van de verdere kolommen is onbekend

4.2 WW.TXT
------------------------------------------------------------------------
Bestand met nummering, locatieomschrijving en typering van alle 
wegwijzers.
Kolommen:
[0] kruispuntnummer
[1] wegwijzernummer
[2] plaats
[3] afkorting van de provincie
[4] drie letters voor achtereenvolgens de uitvoering van de wegwijzer, 
    het type wegwijzer en het type constructie. Zie types.cfg.php voor 
    een lijst met bekende betekenissen per letter.
de betekenis van de verdere kolommen is onbekend

4.3 kpxy.csv
------------------------------------------------------------------------
Bestand met coordinaten van kruispunten.
Kolommen:
[0] kruispuntnummer
[1] x-coordinaat in het stelsel van de Rijksdriehoeksmeting
[2] y-coordinaat in het stelsel van de Rijksdriehoeksmeting

Noot: wegwijzerkaart converteert de RD coordinaten naar WGS 84.

4.4 wwxy.csv
------------------------------------------------------------------------
Bestand met coordinaten van wegwijzers.
Kolommen:
[0] kruispuntnummer
[1] x-coordinaat in het stelsel van de Rijksdriehoeksmeting
[2] y-coordinaat in het stelsel van de Rijksdriehoeksmeting
[3] wegwijzernummer
de betekenis van de verdere kolommen is onbekend

Noot: wegwijzerkaart converteert de RD coordinaten naar WGS 84.


========================================================================
6. Licentie
========================================================================

De broncode van wegwijzerkaart is vrijgegeven als open source software 
onder de GNU General Public License versie 3. 
Dit geeft iedereen het recht om de software te gebruiken en 
te kunnen beschikken over de broncode. Het maken van aanpassingen is 
eveneens toegestaan, zolang auteursrechtvermeldingen intact blijven, en 
vallen automatisch onder dezelfde licentievoorwaarden. Gebruikers van 
een aangepaste versie hebben daardoor ook het recht om te beschikken 
over de broncode van de aangepaste versie. Voor meer informatie zie de 
volledige licentietekst in license.txt.


Wegwijzerkaart - grafische interface voor nationale bewegwijzering open data
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


========================================================================
7. Verkrijgen van de broncode
========================================================================

De broncode van wegwijzerkaart is gepubliceerd op Bitbucket.
https://bitbucket.org/jaspervries/wegwijzerkaart
