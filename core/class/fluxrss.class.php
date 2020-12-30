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
        $fluxrssCmd = fluxrssCmd::byEqLogicIdAndLogicalId($this->getId(),'element');
        if (!is_object($fluxrssCmd)) {
            log::add('fluxrss', 'debug', 'Création de la commande element');
            $fluxrssCmd = new fluxrssCmd();
            $fluxrssCmd->setName(__('Nouvel Article', __FILE__));
            $fluxrssCmd->setEqLogic_id($this->getId());
            $fluxrssCmd->setEqType('fluxrss');
            $fluxrssCmd->setLogicalId('element');
            $fluxrssCmd->setType('action');
            $fluxrssCmd->setSubType('message');
            $fluxrssCmd->save();
        }
	    
	$fluxrssCmd = fluxrssCmd::byEqLogicIdAndLogicalId($this->getId(),'reset');
        if (!is_object($fluxrssCmd)) {
            log::add('fluxrss', 'debug', 'Création de la commande reset');
            $fluxrssCmd = new fluxrssCmd();
            $fluxrssCmd->setName(__('Reset du Flux', __FILE__));
            $fluxrssCmd->setEqLogic_id($this->getId());
            $fluxrssCmd->setEqType('fluxrss');
            $fluxrssCmd->setLogicalId('reset');
            $fluxrssCmd->setType('action');
            $fluxrssCmd->setSubType('other');
            $fluxrssCmd->save();
        }

        /*if (!file_exists(dirname(__FILE__) . '/../../data' . $this->getId())) {
			$this->updateRss('');
		}*/
    }

    public function preSave() {
        $url = network::getNetworkAccess('external') . '/plugins/fluxrss/data/' . $this->getId();
        $this->setConfiguration('url',$url);
    }

    public function updateRss($item) {
        log::add('fluxrss', 'debug', 'Item : ' . $item);
        $rssfeed = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $rssfeed .= '<rss version="2.0">' . PHP_EOL;
        $rssfeed .= '<channel>' . PHP_EOL;
        $rssfeed .= '<title>' . $this->getConfiguration('title') . '</title>' . PHP_EOL;
        $rssfeed .= '<link>' . $this->getConfiguration('link') . '</link>' . PHP_EOL;
        $rssfeed .= '<description>' . $this->getConfiguration('description') . '</description>' . PHP_EOL;
        $rssfeed .= '<language>fr</language>' . PHP_EOL;
        $rssfeed .= '<copyright>Copyright (C) 2017 Jeedom</copyright>' . PHP_EOL;

        $items = '';
		
		$nbitems = (int)$this->getConfiguration('nbitems',1);
      	log::add('fluxrss', 'debug', 'NbItems:'.$nbitems);
	
        for ($i=0; $i < $nbitems; $i++) {
            $index = $nbitems - $i;
            $previous = $index - 1;
            if ($index != 1) {
                if ($this->getConfiguration('item'.$previous,'') != '') {
                    $items = $this->getConfiguration('item'.$previous,'') . $items;
                    log::add('fluxrss', 'debug', 'Items : ' . $this->getConfiguration('item'.$previous,''));
                    $this->setConfiguration('item'.$index,$this->getConfiguration('item'.$previous,''));
                }
            } else {
                $items = $item . $items;
                $this->setConfiguration('item1',$item);
            }
            //log::add('fluxrss', 'debug', 'Index : ' . $index . ' - Items : ' . $items);
        }
        $this->save();
        $rssfeed .= $items;

        $rssfeed .= '</channel>' . PHP_EOL;
        $rssfeed .= '</rss>';

        if (!file_exists(dirname(__FILE__) . '/../../data')) {
			mkdir(dirname(__FILE__) . '/../../data');
		}
        $myfile = fopen(dirname(__FILE__) . '/../../data/' . $this->getId(), "w") or die("Unable to create file!");
        fwrite($myfile, $rssfeed);
        fclose($myfile);
    }
	
public function resetRss() {
        log::add('fluxrss', 'debug', 'Reset');
        unlink(dirname(__FILE__) . '/../../data/' . $this->getId());
    }

}

class fluxrssCmd extends cmd {

    public function execute($_options = null) {
            $eqLogic = $this->getEqLogic();
	    if ($this->getLogicalId() == 'reset') {
		    $eqLogic->resetRss();
	    } else {
		    $message = explode("|", trim($_options['message']));
		    $description = (isset($message[0])) ? $message[0]:'';
		    $link = (isset($message[1])) ? $message[1]:'';
		    $item = '';
		    $item .= '<item>' . PHP_EOL;
		    $item .= '<title>' . trim($_options['title']) . '</title>' . PHP_EOL;
		    $item .= '<description><![CDATA[' . $description . ']]></description>' . PHP_EOL;
		    $item .= '<link>' . $link . '</link>' . PHP_EOL;
		    $item .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime('now')) . '</pubDate>' . PHP_EOL;
		    $item .= '</item>' . PHP_EOL;
		    $eqLogic->updateRss($item);
	    }
    }

}
