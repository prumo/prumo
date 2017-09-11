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

pProtect('prumo_controlPanel');

$configToWrite[] = 'appIdent';
$configToWrite[] = 'appName';

if (!isset($GLOBALS['pConfig']['dbSingle']) or !$GLOBALS['pConfig']['dbSingle']) {
	$configToWrite[] = 'sgdb';
	$configToWrite[] = 'dbHost';
	$configToWrite[] = 'dbPort';
	$configToWrite[] = 'dbName';
	$configToWrite[] = 'dbUserName';
	$configToWrite[] = 'dbPassword';
}

$configToWrite[] = 'appSchema';
$configToWrite[] = 'language';
$configToWrite[] = 'locale';
$configToWrite[] = 'theme';
$configToWrite[] = 'searchLines';
$configToWrite[] = 'logInsert';
$configToWrite[] = 'logSelect';
$configToWrite[] = 'logUpdate';
$configToWrite[] = 'logDelete';
$configToWrite[] = 'logInsert_prumo';
$configToWrite[] = 'logSelect_prumo';
$configToWrite[] = 'logUpdate_prumo';
$configToWrite[] = 'logDelete_prumo';
$configToWrite[] = 'scriptUpdateFramework';
$configToWrite[] = 'scriptUpdateApp';

function writeConfig($configName, $configValue) {
	global $pConnectionPrumo;
	
	$sqlRetrieve = 'SELECT count(*) FROM '.$pConnectionPrumo->getSchema().'config WHERE config_name=\''.$configName.'\';';
	if ($pConnectionPrumo->sqlQuery($sqlRetrieve) == '0') {
		$sqlWrite = 'INSERT INTO '.$pConnectionPrumo->getSchema().'config (config_name,config_value) VALUES (\''.$configName.'\',\''.$configValue.'\');';
	}
	else {
		$sqlWrite = 'UPDATE '.$pConnectionPrumo->getSchema().'config SET config_value=\''.$configValue.'\' WHERE config_name=\''.$configName.'\';';
	}
	
	$pConnectionPrumo->sqlQuery($sqlWrite);
}

// Grava as configurações no banco de dados do framework
for ($i = 0; $i < count($configToWrite); $i++) {
	
	$config = $configToWrite[$i];
	$value = isset($_POST[$configToWrite[$i]]) ? $_POST[$configToWrite[$i]] : '0';
	
	writeConfig($config, $value);
}
echo 'OK';

