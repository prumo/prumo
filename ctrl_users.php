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

pProtect('prumo_users');

require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php');

class prumoUsers extends prumoCrud {
	function sqlCreate() {
		$tableName = $this->param['tablename'];
		$schema = $this->pConnection->getSchema($this->param['schema']);
		$password = md5($_POST['new_username']);
		$sql = 'INSERT INTO '.$schema.$tableName.' (username,fullname,password,enabled) VALUES (:new_username:,:new_fullname:,\''.$password.'\',:new_enabled:);';
		return $sql;
	}
}

$schema = $GLOBALS['pConfig']['loginSchema_prumo'];
$xmlFile = $GLOBALS['pConfig']['prumoWebPath'].'/ctrl_users.php?prumo_appIdent='.$GLOBALS['pConfig']['appIdent'];

$crudUsers = new prumoUsers('objName=crudUsers,xmlFile='.$xmlFile.',schema='.$schema.',tableName=syslogin,routine=prumo_users');
$crudUsers->setConnection($pConnectionPrumo);
$crudUsers->addField('name=username,label='._('Usuário').',pk');
$crudUsers->addField('name=fullname,label='._('Nome completo'));
$crudUsers->addField('name=enabled,label='._('Ativo').',type=boolean,notNull,default=t');

$crudUsers->autoInit();
