<?php
/*
This file is part of Wegwijzerkaart
Copyright (C) 2016, 2025 Jasper Vries

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

//bron: https://bewegwijzeringsdienst.nl/wp-content/uploads/2024/04/nomenclatuur-nbd.pdf
$ww_types['uitvoering']['A'] = 'aangestraald, opschriften retroreflecterend, ondergroend niet-retroreflecterend';
$ww_types['uitvoering']['K'] = 'wisselbaar, handbediend';
$ww_types['uitvoering']['L'] = 'aangestraald, opschriften en ondergrond retroreflecterend';
$ww_types['uitvoering']['N'] = 'onverlicht, opschriften en ondergrond niet-retroreflecterend';
$ww_types['uitvoering']['O'] = 'onverlicht, opschriften retroreflecterend, ondergrond niet-retroreflecterend';
$ww_types['uitvoering']['P'] = 'portaalconstructie';
$ww_types['uitvoering']['T'] = 'transparant, inwendig verlicht';
$ww_types['uitvoering']['U'] = 'uithouder';
$ww_types['uitvoering']['V'] = 'onverlicht, opschriften en ondergrond retroreflecterend';
$ww_types['uitvoering']['W'] = 'wisselbaar, elektrisch';

$ww_types['type_wegwijzer']['A'] = 'aftakkingsbord';
$ww_types['type_wegwijzer']['B'] = 'laag richtingbord';
$ww_types['type_wegwijzer']['C'] = 'chevronbord';
$ww_types['type_wegwijzer']['D'] = 'voorsorteerbord';
$ww_types['type_wegwijzer']['E'] = 'groot model handwijzer';
$ww_types['type_wegwijzer']['F'] = 'fietshandwijzer, fietsbord';
$ww_types['type_wegwijzer']['G'] = 'land- of provinciegrensbord';
$ww_types['type_wegwijzer']['H'] = 'klein model handwijzer';
$ww_types['type_wegwijzer']['I'] = 'informatiepaneel';
$ww_types['type_wegwijzer']['J'] = 'RVV-borden (restaurant, P+R)';
$ww_types['type_wegwijzer']['K'] = 'riviernaambord';
$ww_types['type_wegwijzer']['L'] = 'lichtwegwijzer';
$ww_types['type_wegwijzer']['M'] = 'richting- of routebord, doelen- of servicebord (NBA)';
$ww_types['type_wegwijzer']['N'] = 'routenummerbord, uitwijkroutebord';
$ww_types['type_wegwijzer']['O'] = 'knooppuntnaambord, aankondigingsbord knooppunt/afrit (NBA)';
$ww_types['type_wegwijzer']['P'] = 'verzorgingsplaatsbord';
$ww_types['type_wegwijzer']['Q'] = 'voetgangershandwijzer';
$ww_types['type_wegwijzer']['R'] = 'rijstrookbord';
$ww_types['type_wegwijzer']['S'] = 'stapelbord';
$ww_types['type_wegwijzer']['T'] = 'toeristisch routebord';
$ww_types['type_wegwijzer']['U'] = 'uit-bord';
$ww_types['type_wegwijzer']['V'] = 'voorwegwijzer';
$ww_types['type_wegwijzer']['W'] = 'wijk- of objectbord';
$ww_types['type_wegwijzer']['X'] = 'straatnaambord';
$ww_types['type_wegwijzer']['Y'] = 'straatnaam/objectbord (combinatie)';
$ww_types['type_wegwijzer']['Z'] = 'diversen';

$ww_types['type_constructie']['A'] = 'gecombineerd met mast met verkeerslicht';
$ww_types['type_constructie']['B'] = 'gecombineerd met uitleggermast van verkeerslichten';
$ww_types['type_constructie']['C'] = 'gecombineerd met mast openbare verlichting';
$ww_types['type_constructie']['D'] = 'gecombineerd met mast openbare verlichting met verkeerslicht';
$ww_types['type_constructie']['E'] = 'gecombineerd met uitleggermast van verkeerslichten en openbare verlichting';
$ww_types['type_constructie']['F'] = 'flespaal, kleine verzinkte mast';
$ww_types['type_constructie']['G'] = 'paddenstoelvoet';
$ww_types['type_constructie']['H'] = 'houten palen';
$ww_types['type_constructie']['I'] = 'infozuil';
$ww_types['type_constructie']['J'] = 'aluminiumconstructie';
$ww_types['type_constructie']['K'] = 'kolom portaal of uithouder';
$ww_types['type_constructie']['M'] = 'geschilderde stalen mast';
$ww_types['type_constructie']['O'] = 'onbekend (nog nader te bepalen)';
$ww_types['type_constructie']['P'] = 'portaal';
$ww_types['type_constructie']['S'] = 'staalconstructie';
$ww_types['type_constructie']['T'] = 'frame (toeristisch)';
$ww_types['type_constructie']['U'] = 'uithouder/zweepmast';
$ww_types['type_constructie']['V'] = 'viaduct';
$ww_types['type_constructie']['Z'] = 'bestaande ondersteuningsconstructie';

$ww_types['provincie']['DR'] = 'Drenthe';
$ww_types['provincie']['FL'] = 'Flevoland';
$ww_types['provincie']['FR'] = 'Fryslân';
$ww_types['provincie']['GE'] = 'Gelderland';
$ww_types['provincie']['GR'] = 'Groningen';
$ww_types['provincie']['LI'] = 'Limburg';
$ww_types['provincie']['NB'] = 'Noord-Brabant';
$ww_types['provincie']['NH'] = 'Noord-Holland';
$ww_types['provincie']['OV'] = 'Overijssel';
$ww_types['provincie']['UT'] = 'Utrecht';
$ww_types['provincie']['ZE'] = 'Zeeland';
$ww_types['provincie']['ZH'] = 'Zuid-Holland';

$ww_types['voet_type'] = array('Q');
$ww_types['fiets_type'] = array('F', 'T');
$ww_types['auto_type'] = array('L', 'V', 'B', 'R', 'D', 'E', 'A', 'H', 'S', 'M');

?>