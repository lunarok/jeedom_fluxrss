<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'fluxrss')) {
    echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (fluxrss)', __FILE__);
    die();
}

$addr = init('id');
$eqLogic = fluxrss::byLogicalId($addr,'fluxrss');
 if (!is_object($eqLogic)) {
 	echo json_encode(array('text' => __('Id inconnu : ', __FILE__) . init('addr')));
 	die();
 }

$rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
$rssfeed .= '<rss version="2.0">';
$rssfeed .= '<channel>';
$rssfeed .= '<title>Jeedom RSS</title>';
$rssfeed .= '<link>http://www.mywebsite.com</link>';
$rssfeed .= '<description>This is an example RSS feed</description>';
$rssfeed .= '<language>fr</language>';
$rssfeed .= '<copyright>Copyright (C) 2017 Jeedom</copyright>';

while($row = mysql_fetch_array($result)) {
    extract($row);

    $rssfeed .= '<item>';
    $rssfeed .= '<title>' . $title . '</title>';
    $rssfeed .= '<description>' . $description . '</description>';
    $rssfeed .= '<link>' . $link . '</link>';
    $rssfeed .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime($date)) . '</pubDate>';
    $rssfeed .= '</item>';
}

$rssfeed .= '</channel>';
$rssfeed .= '</rss>';

echo $rssfeed;

?>
