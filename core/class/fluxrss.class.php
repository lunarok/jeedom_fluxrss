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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class fluxrss extends eqLogic {

    public function postUpdate() {
        $stockCmd = stockCmd::byEqLogicIdAndLogicalId($this->getId(),'element');
        if (!is_object($stockCmd)) {
            log::add('stock', 'debug', 'Création de la commande element');
            $stockCmd = new stockCmd();
            $stockCmd->setName(__('Nouvel Article', __FILE__));
            $stockCmd->setEqLogic_id($this->getId());
            $stockCmd->setEqType('fluxrss');
            $stockCmd->setLogicalId('element');
            $stockCmd->setType('action');
            $stockCmd->setSubType('message');
            $stockCmd->save();
        }
    }

    public static function updateRss($item) {

        $rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
        $rssfeed .= '<rss version="2.0">';
        $rssfeed .= '<channel>';
        $rssfeed .= '<title>' . $this->getConfiguration('title') . '</title>';
        $rssfeed .= '<link>' . $this->getConfiguration('link') . '</link>';
        $rssfeed .= '<description>' . $this->getConfiguration('description') . '</description>';
        $rssfeed .= '<language>fr</language>';
        $rssfeed .= '<copyright>Copyright (C) 2017 Jeedom</copyright>';

        $items = '';
        for ($i=0; $i < 10; $i++) {
            $index = 10 - $i;
            $previous = $index - 1;
            if ($index != 0) {
                if ($this->getConfiguration('item'.$previous,'') != '') {
                    $items = $this->getConfiguration('item'.$previous,'') . $items;
                    $this->setConfiguration('item'.$index,$this->getConfiguration('item'.$previous,''));
                }
            } else {
                $items = $item . $items;
                $this->setConfiguration('item0',$item);
            }
        }
        $rssfeed .= $items;
        $this->save();

        $rssfeed .= '</channel>';
        $rssfeed .= '</rss>';

        if (!file_exists(dirname(__FILE__) . '/../../data')) {
			mkdir(dirname(__FILE__) . '/../../data');
		}
        $myfile = fopen(dirname(__FILE__) . '/../../data/' . $this->getId(), "w") or die("Unable to create file!");
        fwrite($myfile, $rssfeed);
        fclose($myfile);
    }

}

class fluxrssCmd extends cmd {

    public function execute($_options = null) {
            $eqLogic = $this->getEqLogic();
            $message = explode("|", trim($_options['message']));
            $description = $message[0];
            $link = $message[1];
            $item .= '<item>';
            $item .= '<title>' . trim($_options['title']) . '</title>';
            $item .= '<description>' . $description . '</description>';
            $item .= '<link>' . $link . '</link>';
            $item .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime('now')) . '</pubDate>';
            $item .= '</item>';
            $eqLogic->updateRss($item);
    }

}
