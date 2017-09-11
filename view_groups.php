<?php

/* *********************************************************************
 *
 *  Prumo Framework para PHP é um framework vertical para
 *  desenvolvimento rápido de sistemas de informação web.
 *  Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *  e Odair Rubleski <orubleski@gmail.com>
 *
 *  This file is part of Prumo.
 *
 *  Prumo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3, or (at your option)
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

require_once('prumo.php');
pProtect('prumo_groups');

require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_groups.php');
?>

<fieldset>
<legend><?php echo _('Grupos de Usuários'); ?></legend>

<div id="crudGroups_form">
	<br />
	<table class="prumoFormTable">
		<tr>
			<td class="prumoFormLabel"><?php echo _('Nome do grupo'); ?>:</td>
			<td class="prumoFormFields"><input id="groupname" type="text" size=45 maxlength="30" autofocus="autofocus" />*</td>
		</tr>
		<tr>
			<td class="prumoFormLabel"></td>
			<td class="prumoFormFields"><input id="enabled" type="checkbox" checked="checked" /> <?php echo _('Ativo'); ?></td>
		</tr>
		<tr>
			<td class="prumoFormLabel"></td>
			<td class="prumoFormFields"><?php $crudGroups->drawControls(); ?></td>
		</tr>
	</table>
	
	<br />
	* <?php echo _('campos de preenchimento obrigatório'); ?>
</div>

<?php 
$crudGroups->drawCrudList(); 
?>

</fieldset>
