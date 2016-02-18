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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class trafictransilien extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom */
      public static function cron($_eqlogic_id = null) {
	if($_eqlogic_id !== null){
		$eqLogics = array(eqLogic::byId($_eqlogic_id));
	}else{
		$eqLogics = eqLogic::byType('trafictransilien');
	}
	foreach($eqLogics as $trafic) {	
		if ($trafic->getIsEnable() == 1) {
			log::add('trafictransilien', 'debug', 'Pull Cron pour trafictrain' );
			//$lignetrain =  $trafictransilien->getConfiguration('train');
			$trafic->getTrainStatus();
			$trafic->toHtml('dashboard');
			$trafic->refreshWidget();
		}
	}
	return;
      }
      public function getTrainStatus(){
	try {
		$ligne = $this->getConfiguration('ligne');
		$url = "http://www.transilien.com/flux/rss/traficLigne?codeLigne=" . $ligne;
		$xml = simplexml_load_file($url);
		foreach($xml as $ligne){ //RSS
		foreach($ligne as $channel){ //CHANNEL
		// lecture des tags description du flux rss
			$item = $channel->item;
			if (trim($channel->description)!=""){
				$probleme=$channel->description;
			}
		}
		if(!$probleme) {
			$probleme = "Trafic normal";
		}
		$problemeCmd = $this->getCmd(null, 'probleme');
		$problemeCmd->event($probleme);
		log::add('trafictransilien', 'debug', $probleme);
	}
		
        } catch (Exception $e) {
			return '';
		}
		return;
	}

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
	$probleme = $this->getCmd(null, 'probleme');
	if (!is_object($probleme)) {
		$probleme = new trafictransilienCmd();
		$probleme->setLogicalId('probleme');
		$probleme->setIsVisible(1);
                $probleme->setIsHistorized(0);
		$probleme->setName(__('Probleme sur la ligne', __FILE__));
	}
        $probleme->setType('info');
	$probleme->setSubType('string');
	$probleme->setEventOnly(1);
	$probleme->setEqLogic_id($this->getId());
	$probleme->save();
        $this->getTrainStatus();
        $this->toHtml('dashboard');
        $this->refreshWidget();

    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin */
      public function toHtml($_version = 'dashboard') {
			
			$replace = array(
			'#name#' => $this->getName(),
			'#id#' => $this->getId(),
			'#background_color#' => $this->getBackgroundColor(jeedom::versionAlias($_version)),
			'#eqLink#' => $this->getLinkToConfiguration(),
			'#ligne#' => $this->getConfiguration('ligne')
		);
		foreach ($this->getCmd() as $cmd) {
			if ($cmd->getType() == 'info') {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
				$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				$replace['#' . $cmd->getLogicalId() . '_collectDate#'] = $cmd->getCollectDate();
				if ($cmd->getIsHistorized() == 1) {
					$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
				}
			} else {
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			}
		}
	$html = template_replace($replace, getTemplate('core', $_version, 'transilienwidget', 'trafictransilien'));
	return $html;

      }

    /*     * **********************Getteur Setteur*************************** */
}

class trafictransilienCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
