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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$REQUEST_PROTOCOL = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $REQUEST_PROTOCOL = 'https';
}
$easyimport = array('url'=>$REQUEST_PROTOCOL.'://'.$_SERVER["SERVER_ADDR"].':'.$_SERVER["SERVER_PORT"].'/plugins/airsend/core/ajax/airsend.ajax.php?action=importfile', 'cookie'=>'PHPSESSID='.$_COOKIE['PHPSESSID']);
?>

<div style="display: none;width : 100%" id="div_modal"></div>
<div>
    <p>
    <label>{{Veuillez scanner ce QRCode depuis Paramètres->Export->EasyScan dans l'application mobile :}}</label>
    <div id="qrcode" style="width:3000px;height:300px;margin-top:15px;"></div>
    <br />
    <label>{{Vous devez être sur le même réseau local pour que l'application puisse se connecter à votre jeedom.}}</label>
    <br />
    [ <a onClick="$('#md_modal').dialog('close')">{{ Fermer }}</a> ]
</div>

<script type="text/javascript" src="/plugins/airsend/desktop/js/qrcode.min.js"></script>
<script type="text/javascript">
var qrcode = new QRCode("qrcode");
qrcode.makeCode('<?php echo json_encode($easyimport);?>');

function onCloseRefresh() {
    location.reload();
    $('#md_modal').off('dialogclose', onCloseRefresh);
}

$('#md_modal').on('dialogclose', onCloseRefresh);
</script>

