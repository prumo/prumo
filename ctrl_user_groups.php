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
pCheckPOST('username');

$readonly = pPermitted('prumo_users', 'u') ? '' : ' readonly="readonly" disabled="disabled"';

///////////////////// grava alterações ///////////////////
if (isset($_POST['action']) and $_POST['action'] == 'write') {
	
	if (pPermitted('prumo_users', 'u')) {
		
		$groupName = isset($_POST['groupname']) ? $_POST['groupname'] : array();
	
		// remove os grupos que não estão na nova lista
		$sql  = 'DELETE FROM '.$schema.'groups_syslogin'."\n";
		$sql .= 'WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
		for ($i=0; $i < count($groupName); $i++) {
			$sql .= 'AND NOT groupname='.pFormatSql($groupName[$i], 'string')."\n";
		}
		$sql .= ';';
		$pConnectionPrumo->sqlQuery($sql);
	
		// adiciona os novos
		for ($i=0; $i < count($groupName); $i++) {
			$sql  = 'SELECT'."\n";
			$sql .= '	count(*)'."\n";
			$sql .= 'FROM '.$schema.'groups_syslogin'."\n";
			$sql .= 'WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
			$sql .= 'AND groupname='.pFormatSql($groupName[$i], 'string')."\n";
			if ($pConnectionPrumo->sqlQuery($sql) == 0) {
				$sql  = 'INSERT INTO '.$schema.'groups_syslogin (username, groupname) VALUES ('."\n";
				$sql .= '	'.pFormatSql($_POST['username'], 'string').','."\n";
				$sql .= '	'.pFormatSql($groupName[$i], 'string')."\n";
				$sql .= ');';
				$pConnectionPrumo->sqlQuery($sql);
			}
		}
	}
	else {
		echo _('Acesso Negado!');
	}
}

include($GLOBALS['pConfig']['prumoPath'].'/view_user_groups.php');

