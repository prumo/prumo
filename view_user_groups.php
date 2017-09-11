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
require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php');

$schema = $pConnectionPrumo->getSchema();

pProtect('prumo_users');


$sql  = 'SELECT'."\n";
$sql .= '	groupname'."\n";
$sql .= 'FROM '.$schema.'groups'."\n";
$sql .= 'WHERE NOT groupname IN ('."\n";
$sql .= '	SELECT'."\n";
$sql .= '		groupname'."\n";
$sql .= '	FROM '.$schema.'groups_syslogin'."\n";
$sql .= '	WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
$sql .= ')'."\n";
$sql .= 'ORDER BY groupname;';
$availableGroup = $pConnectionPrumo->sql2Array($sql);

$sql  = 'SELECT'."\n";
$sql .= '	groupname'."\n";
$sql .= 'FROM '.$schema.'groups_syslogin'."\n";
$sql .= 'WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
$sql .= 'ORDER BY groupname;';
$activeGroup = $pConnectionPrumo->sql2Array($sql);

echo '<table align="center" width="600">'."\n";
echo '	<tr>'."\n";
echo '		<td align="right">'._('Grupos disponíveis').'</td>'."\n";
echo '		<td align="center"><br /></td>'."\n";
echo '		<td align="left">'._('Grupos deste usuário').'</td>'."\n";
echo '	</tr>'."\n";
echo '	<tr>'."\n";
echo '		<td align="right">'."\n";
echo '			<select id="available_group" style="width:200px; height:323px" multiple'.$readonly.'>'."\n";
for ($i=0; $i < count($availableGroup); $i++) {
	echo '				<option value="'.$availableGroup[$i]['groupname'].'">'.$availableGroup[$i]['groupname'].'</option>'."\n";
}
echo '			</select>'."\n";
echo '		</td>'."\n";
echo '		<td align="center">'."\n";
echo '			<button id="bt_add" onclick="btAdd_Click()" style="width:50px; height:31px"'.$readonly.'> > </button>'."\n";
echo '			<br />'."\n";
echo '			<button id="bt_remove" onclick="btRemove_Click()" style="width:50px; height:31px"'.$readonly.'> < </button>'."\n";
echo '			<br />'."\n";
echo '			<br />'."\n";
echo '			<button id="bt_add_all" onclick="btAddAll_Click()" style="width:50px; height:31px"'.$readonly.'> >> </button>'."\n";
echo '			<br />'."\n";
echo '			<button id="bt_remove_all" onclick="btRemoveAll_Click()" style="width:50px; height:31px"'.$readonly.'> << </button>'."\n";
echo '		</td>'."\n";
echo '		<td align="left">'."\n";
echo '			<select id="active_group" style="width:200px; height:323px" multiple'.$readonly.'>'."\n";
for ($i=0; $i < count($activeGroup); $i++) {
	echo '				<option value="'.$activeGroup[$i]['groupname'].'">'.$activeGroup[$i]['groupname'].'</option>'."\n";
}
echo '			</select>'."\n";
echo '		</td>'."\n";
echo '	</tr>'."\n";
echo '</table>'."\n";
