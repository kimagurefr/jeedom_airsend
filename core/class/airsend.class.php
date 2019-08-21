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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class airsend extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */
	public static function refreshInfo(){
		$eqLogics = eqLogic::byType('airsend');
		foreach ($eqLogics as $eqLogic)
		{
            if ($eqLogic->getIsEnable() == 0) continue;
            try{
                $deviceType = intval($eqLogic->getConfiguration('device_type'));
                if($deviceType == 0){
                    $asAddr = $eqLogic->getAddress();
                    if($asAddr){
                        $res = airsendCmd::readSensors($asAddr);
                        if($res){
                            $eqLogic->checkAndUpdateCmd('temperature', $res["tmp"]);
                            $eqLogic->checkAndUpdateCmd('illuminance', $res["ill"]);
                        }
                    }else{
                        throw new Exception('Erreur de configuration : password invalide');
                    }
                }
            } catch (Exception $exc) {}
		}
    }

    public static function Fixperm() {
		log::remove('Fixperm');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/scripts/fix.sh';
		$cmd .= ' >> ' . log::getPathToLog('Fixperm') . ' 2>&1 &';
		exec($cmd);
    }

    public static function getDeviceName($name){
        $plugin = plugin::byId('airsend');
        $eqLogics = eqLogic::byType($plugin->getId());
        foreach ($eqLogics as $eqLogic) {
            $n = $eqLogic->getName();
            if($name == $n){
                return $eqLogic;
            }
        }
        return null;
    }

    public static function getBaseDevice($localip){
        $plugin = plugin::byId('airsend');
        $eqLogics = eqLogic::byType($plugin->getId());
        foreach ($eqLogics as $eqLogic) {
            $deviceType = intval($eqLogic->getConfiguration('device_type'));
            if($deviceType == 0){
                $lip = $eqLogic->getConfiguration('localip');
                if (filter_var($lip, FILTER_VALIDATE_IP)) {
                    if($lip == $localip){
                        return $eqLogic;
                    }
                }
            }
        }
        return null;
    }

    public static function importDevices($devices){
        $result = array();
        foreach ($devices as $device){
            $status = array();
            $status['name'] = $device['name'];
            $status['status'] = "error";
            $nameEq = self::getDeviceName($device['name']);
            if(!$nameEq){
                $baseEq = self::getBaseDevice($device['localip']);
                if ($baseEq) {
                    $deviceType = intval($device['type']);
                    if($deviceType >= 4096 && $deviceType <= 4098){
                        $protocol = intval($device['pid']);
                        if ($protocol > 0) {
                            try{
                                $eqLogic = new airsend();
                                $eqLogic->setEqType_name("airsend");
                                $eqLogic->setName($device['name']);
                                $eqLogic->setConfiguration('device_type', $device['type']);
                                $eqLogic->setConfiguration('localip', $device['localip']);
                                $eqLogic->setConfiguration('protocol', $device['pid']);
                                $eqLogic->setConfiguration('address', $device['addr']);
                                if(isset($device['opt'])){
                                    $eqLogic->setConfiguration('opt', $device['opt']);
                                }
                                $eqLogic->save();
                                if (method_exists($eqLogic, 'postAjax')) {
                                    $eqLogic->postAjax();
                                }
                                $status['status'] = "ok";
                            } catch (Exception $e) {
                            }
                        }
                    }
                }else{
                    $status['status'] = "localip not found";
                }
            }else{
                $status['status'] = "name exists";
            }
            $result[] = $status;
        }
        return $result;
    }

    public static function importInterfaces($interfaces){
        $result = array();
        foreach ($interfaces as $iface){
            $status = array();
            $status['name'] = $iface['name'];
            $status['status'] = "error";
            $baseEq = self::getBaseDevice($iface['localip']);
            if (!$baseEq) {
                try{
                    $eqLogic = new airsend();
                    $eqLogic->setEqType_name("airsend");
                    $eqLogic->setName($iface['name']);
                    $eqLogic->setConfiguration('device_type', '0');
                    $eqLogic->setConfiguration('localip', $iface['localip']);
                    $eqLogic->setConfiguration('password', $iface['password']);
                    $eqLogic->setConfiguration('gateway', '1');
                    $eqLogic->save();
                    if (method_exists($eqLogic, 'postAjax')) {
                        $eqLogic->postAjax();
                    }
                    $status['status'] = "ok";
                } catch (Exception $e) {
                }
            }else{
                $status['status'] = "interface exists";
            }
            $result[] = $status;
        }
        return $result;
    }

    public static function importFile($data){
        $result = array();
        if(isset($data['interfaces'])){
            $result['interfaces'] = self::importInterfaces($data['interfaces']);
        }
        if(isset($data['devices'])){
            $result['devices'] = self::importDevices($data['devices']);
        }
        return $result;
    }

    /*
     * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
     */
	public static function cron15()
	{
        self::refreshInfo();
	}

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function getAddress(){
        $localip = $this->getConfiguration('localip');
        if (filter_var($localip, FILTER_VALIDATE_IP)) {
            $addr = $localip;
            if (filter_var($localip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $addr = '['.$localip.']';
            }
            $deviceType = intval($this->getConfiguration('device_type'));
            if($deviceType == 0){
                $password = $this->getConfiguration('password');
                $gateway = $this->getConfiguration('gateway');
                $failover = $this->getConfiguration('failover');
            }else{
                //Search password
                $eqLogics = eqLogic::byType('airsend');
                foreach ($eqLogics as $eqLogic){
                    $lip = $eqLogic->getConfiguration('localip');
                    if($lip == $localip){
                        $lpw = $eqLogic->getConfiguration('password');
                        if(strlen($lpw)>0){
                            $password = $lpw;
                            $gateway = $eqLogic->getConfiguration('gateway');
                            $failover = $eqLogic->getConfiguration('failover');
                        }
                    }
                    if($password && $eqLogic->getIsEnable() <> 0)
                        break;
                }
            }
            if($password){
                $str = "\"sp://".$password."@".$addr."/?timeout=9000";
                $str .= "&gw=".$gateway;
                if(strlen($failover)>0){
                    $str .= "&rhost=".$failover;
                }
                $str .= "\"";
                return $str;
            }
        }
        return false;
    }

    public function autoGenerateCommands(){
        if($this->getId()){
            //Create commands
            $deviceType = intval($this->getConfiguration('device_type'));
            $cmd_list = $this->getCmd();
            if(!is_array($cmd_list)){
                $cmd_list = array();
            }
            if($deviceType == 4098){
                foreach ($cmd_list as $cmd){
                    if ($cmd->getLogicalId() == 'stop' && !$cmds["stop"]) {
                        $cmds["stop"] = $cmd;
                    }else if ($cmd->getLogicalId() == 'down' && !$cmds["down"]) {
                        $cmds["down"] = $cmd;
                    }else if ($cmd->getLogicalId() == 'up' && !$cmds["up"]) {
                        $cmds["up"] = $cmd;
                    }else{
                        $cmd->remove();
                    }
                }
                if(!$cmds["stop"]){
                    $cmds["stop"] = new airsendCmd();
                    $cmds["stop"]->setEqLogic_id($this->getId());
                    $cmds["stop"]->setType('action');
                    $cmds["stop"]->setSubType('other');
                    $cmds["stop"]->setEqType('airsend');
                    $cmds["stop"]->setLogicalId("stop");
                    $cmds["stop"]->setName('stop');
                    $cmds["stop"]->setValue('3');
                    $cmds["stop"]->setOrder(2);
                    $cmds["stop"]->save();
                }
                if(!$cmds["down"]){
                    $cmds["down"] = new airsendCmd();
                    $cmds["down"]->setEqLogic_id($this->getId());
                    $cmds["down"]->setType('action');
                    $cmds["down"]->setSubType('other');
                    $cmds["down"]->setEqType('airsend');
                    $cmds["down"]->setLogicalId("down");
                    $cmds["down"]->setName('down');
                    $cmds["down"]->setValue('4');
                    $cmds["down"]->setOrder(1);
                    $cmds["down"]->save();
                }
                if(!$cmds["up"]){
                    $cmds["up"] = new airsendCmd();
                    $cmds["up"]->setEqLogic_id($this->getId());
                    $cmds["up"]->setType('action');
                    $cmds["up"]->setSubType('other');
                    $cmds["up"]->setEqType('airsend');
                    $cmds["up"]->setLogicalId("up");
                    $cmds["up"]->setName('up');
                    $cmds["up"]->setValue('5');
                    $cmds["up"]->setOrder(3);
                    $cmds["up"]->save();
                }

            }else if($deviceType == 4097){
                foreach ($cmd_list as $cmd){
                    if ($cmd->getLogicalId() == 'off' && !$cmds["off"]) {
                        $cmds["off"] = $cmd;
                    }else if ($cmd->getLogicalId() == 'on' && !$cmds["on"]) {
                        $cmds["on"] = $cmd;
                    }else{
                        $cmd->remove();
                    }
                }
                if(!$cmds["off"]){
                    $cmds["off"] = new airsendCmd();
                    $cmds["off"]->setEqLogic_id($this->getId());
                    $cmds["off"]->setType('action');
                    $cmds["off"]->setSubType('other');
                    $cmds["off"]->setEqType('airsend');
                    $cmds["off"]->setLogicalId("off");
                    $cmds["off"]->setName('off');
                    $cmds["off"]->setValue('0');
                    $cmds["off"]->setOrder(1);
                    $cmds["off"]->save();
                }
                if(!$cmds["on"]){
                    $cmds["on"] = new airsendCmd();
                    $cmds["on"]->setEqLogic_id($this->getId());
                    $cmds["on"]->setType('action');
                    $cmds["on"]->setSubType('other');
                    $cmds["on"]->setEqType('airsend');
                    $cmds["on"]->setLogicalId("on");
                    $cmds["on"]->setName('on');
                    $cmds["on"]->setValue('1');
                    $cmds["on"]->setOrder(2);
                    $cmds["on"]->save();
                }

            }else if($deviceType == 4096){
                foreach ($cmd_list as $cmd){
                    if ($cmd->getLogicalId() == 'toggle' && !$cmds["toggle"]) {
                        $cmds["toggle"] = $cmd;
                    }else{
                        $cmd->remove();
                    }
                }
                if(!$cmds["toggle"]){
                    $cmds["toggle"] = new airsendCmd();
                    $cmds["toggle"]->setEqLogic_id($this->getId());
                    $cmds["toggle"]->setType('action');
                    $cmds["toggle"]->setSubType('other');
                    $cmds["toggle"]->setEqType('airsend');
                    $cmds["toggle"]->setLogicalId("toggle");
                    $cmds["toggle"]->setName('toggle');
                    $cmds["toggle"]->setValue('6');
                    $cmds["toggle"]->setOrder(1);
                    $cmds["toggle"]->save();
                }
            }else{
                foreach ($cmd_list as $cmd){
                    if ($cmd->getLogicalId() == 'temperature' && !$cmds["temperature"]) {
                        $cmds["temperature"] = $cmd;
                    }else if ($cmd->getLogicalId() == 'illuminance' && !$cmds["illuminance"]) {
                        $cmds["illuminance"] = $cmd;
                    }else if ($cmd->getLogicalId() == 'refresh' && !$cmds["refresh"]) {
                        $cmds["refresh"] = $cmd;
                    }else{
                        $cmd->remove();
                    }
                }
                if(!$cmds["illuminance"]){
                    $cmds["illuminance"] = new airsendCmd();
                    $cmds["illuminance"]->setEqLogic_id($this->getId());
                    $cmds["illuminance"]->setType('info');
                    $cmds["illuminance"]->setSubType('numeric');
                    $cmds["illuminance"]->setEqType('airsend');
                    $cmds["illuminance"]->setLogicalId("illuminance");
                    $cmds["illuminance"]->setName('illuminance');
                    $cmds["illuminance"]->setTemplate('dashboard', 'tile');
                    $cmds["illuminance"]->setTemplate('mobile', 'tile');
                    $cmds["illuminance"]->setDisplay('icon', '<i class="icon nature-weather1"></i>');
                    $cmds["illuminance"]->setDisplay('invertBinary', '0');
                    $cmds["illuminance"]->setDisplay('generic_type', 'ILLUMINANCE');
                    $cmds["illuminance"]->setUnite('lux');
                    $cmds["illuminance"]->setOrder(2);
                    $cmds["illuminance"]->save();
                }
                if(!$cmds["temperature"]){
                    $cmds["temperature"] = new airsendCmd();
                    $cmds["temperature"]->setEqLogic_id($this->getId());
                    $cmds["temperature"]->setType('info');
                    $cmds["temperature"]->setSubType('numeric');
                    $cmds["temperature"]->setEqType('airsend');
                    $cmds["temperature"]->setLogicalId("temperature");
                    $cmds["temperature"]->setName('temperature');
                    $cmds["temperature"]->setTemplate('dashboard', 'tile');
                    $cmds["temperature"]->setTemplate('mobile', 'tile');
                    $cmds["temperature"]->setDisplay('icon', '<i class="icon jeedom-thermometre-celcius"></i>');
                    $cmds["temperature"]->setDisplay('invertBinary', '0');
                    $cmds["temperature"]->setDisplay('generic_type', 'TEMPERATURE');
                    $cmds["temperature"]->setUnite('°C');
                    $cmds["temperature"]->setOrder(1);
                    $cmds["temperature"]->save();
                }
                if(!$cmds["refresh"]){
                    $cmds["refresh"] = new airsendCmd();
                    $cmds["refresh"]->setEqLogic_id($this->getId());
                    $cmds["refresh"]->setType('action');
                    $cmds["refresh"]->setSubType('other');
                    $cmds["refresh"]->setEqType('airsend');
                    $cmds["refresh"]->setLogicalId("refresh");
                    $cmds["refresh"]->setName('Rafraichir');
                    $cmds["refresh"]->setValue('refresh');
                    $cmds["refresh"]->setOrder(0);
                    $cmds["refresh"]->save();
                }
            }
        }
    }


    public function preInsert() {
    }

    public function postInsert() {
    }

    public function postAjax() {
        $this->autoGenerateCommands();
    }

    public function preSave() {
        //Prevent error on creation
        $localip = $this->getConfiguration('localip');
        if($this->getId() || $localip<>""){
            if (!filter_var($localip, FILTER_VALIDATE_IP)) {
                throw new Exception('Erreur de configuration : localip invalide');
            }
            $deviceType = intval($this->getConfiguration('device_type'));
            if($deviceType >= 4096 && $deviceType <= 4098){
                $protocol = intval($this->getConfiguration('protocol'));
                if ($protocol <= 0) {
                    throw new Exception('Erreur de configuration : protocole invalide');
                }
            }
        }else{
            $this->setConfiguration('device_type', '0');
        }
    }

    public function postSave() {
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
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class airsendCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */
    public static function readSensors($device){
        $ret = false;
        $cmd_path = dirname(__FILE__) . '/../../ressources/scripts/`dpkg --print-architecture`/AirSendWrite';
        $command = $cmd_path . " ". $device;
        $request_shell = new com_shell("sudo" . " " . $command . ' 2>&1');
        $result = $request_shell->exec();
        preg_match('/T: ([0-9\.]+) ; I: ([0-9]+)\nOK/', $result, $matches, PREG_OFFSET_CAPTURE);
        if(count($matches) > 2){
            $ret = array("tmp" => "".floatval($matches[1][0]), "ill" => "".intval($matches[2][0]));
        }
        return $ret;
    }

    public static function writeProtocol($device, $protocol, $address, $command){
        $ret = false;
        $cmd_path = dirname(__FILE__) . '/../../ressources/scripts/`dpkg --print-architecture`/AirSendWrite';
        $command = $cmd_path . " ". $device." ".intval($protocol).' '.$address.' '.intval($command);
        $request_shell = new com_shell("sudo" . " " . $command . ' 2>&1');
        $request_shell->exec();
    }


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
     */
    public function dontRemoveCmd() {
        return false;
    }
     

    public function execute($_options = array()) {
        if ($this->getType() == 'action'){
            $eqLogic = $this->getEqLogic();
            $asAddr = $eqLogic->getAddress();
            if($asAddr){
                $cmd_path = dirname(__FILE__) . '/../../ressources/scripts/`dpkg --print-architecture`/AirSendWrite';
                if($this->getLogicalId() == 'refresh'){
                    $res = airsendCmd::readSensors($asAddr);
                    if($res){
                        $eqLogic->checkAndUpdateCmd('temperature', $res["tmp"]);
                        $eqLogic->checkAndUpdateCmd('illuminance', $res["ill"]);
                    }
                }else{
                    $protocol = intval($eqLogic->getConfiguration('protocol'));
                    if($protocol > 0){
                        $address = intval($eqLogic->getConfiguration('address'));
                        $command = $this->getValue();
                        if($command == 6){
                            $opt = $eqLogic->getConfiguration('opt', null);
                            if(isset($opt)){
                                $command = $opt;
                            }
                        }
                        airsendCmd::writeProtocol($asAddr, $protocol, $address, $command);
                    }else{
                        throw new Exception('Erreur de configuration : protocole invalide');
                    }
                }
            }else{
				throw new Exception('Erreur de configuration : localip ou password invalide');
            }
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}


