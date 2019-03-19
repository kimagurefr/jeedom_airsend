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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>

<form class="form-horizontal">
    <div class="form-group">
		<div class="col-lg-3">
	        <a class="btn btn-danger" id="bt_airsend_fixscript" data-slaveid="-1" data-log="Fixperm"><i class="fa fa-cog"></i> {{Attribution des droits aux scripts}}</a>
	    </div>
	</div>
</form>

<script>
$('#bt_airsend_fixscript').on('click',function() {
$.ajax({
		type: 'POST',
		url: 'plugins/airsend/core/ajax/airsend.ajax.php',
		data: {
			action: 'Fixperm',
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
		
		},
		success: function () {
			
		}
	});    
		  $('#md_modal').dialog({title: "{{Attribution des droits aux scripts}}"});
          $('#md_modal').load('index.php?v=d&modal=log.display&log='+$(this).attr('data-log')+'&slaveId='+$(this).attr('data-slaveId')).dialog('open');
  
});
</script>
