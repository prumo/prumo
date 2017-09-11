<?php

/* *********************************************************************
 *
 *	Prumo Framework para PHP é um framework vertical para
 *	desenvolvimento rápido de sistemas de informação web.
 *	Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *	e Odair Rubleski <orubleski@gmail.com>
 *
 *	This file is part of Prumo.
 *
 *	Prumo is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 3, or (at your option)
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */


////////////////////////////////// configurações padrão ////////////////////////////////////

$pConfig['version'] = '1.5.3';

// Configurações de path e identificação
if (!isset($pConfig['appIdent']))              $pConfig['appIdent']              = 'Prumo';
if (!isset($pConfig['appName']))               $pConfig['appName']               = 'Framework para PHP';
if (!isset($pConfig['appPath']))               $pConfig['appPath']               = isset($_SERVER['REMOTE_ADDR']) ? dirname($_SERVER['SCRIPT_FILENAME']) : getcwd();
if (!isset($pConfig['appWebPath']))            $pConfig['appWebPath']            = isset($_SERVER['REMOTE_ADDR']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
if ($pConfig['appWebPath'] == '/')             $pConfig['appWebPath']            = '';
if (!isset($pConfig['prumoPath']))             $pConfig['prumoPath']             = $pConfig['appPath'].'/prumo';
if (!isset($pConfig['prumoWebPath']))          $pConfig['prumoWebPath']          = $pConfig['appWebPath'].'/prumo';
if ($pConfig['prumoWebPath'] == '/')           $pConfig['prumoWebPath']          = '';

// Conectividade com Banco de dados Prumo
if (!isset($pConfig['dbSingle']))              $pConfig['dbSingle']              = false;
if (!isset($pConfig['sgdb_prumo']))            $pConfig['sgdb_prumo']            = 'sqlite3';
if (!isset($pConfig['dbHost_prumo']))          $pConfig['dbHost_prumo']          = '';
if (!isset($pConfig['dbPort_prumo']))          $pConfig['dbPort_prumo']          = '';
if (!isset($pConfig['dbUserName_prumo']))      $pConfig['dbUserName_prumo']      = '';
if (!isset($pConfig['dbPassword_prumo'] ))     $pConfig['dbPassword_prumo']      = '';
if (!isset($pConfig['loginSchema_prumo']))     $pConfig['loginSchema_prumo']     = 'prumo';
if (!isset($pConfig['dbName_prumo']))          $pConfig['dbName_prumo']          = $pConfig['prumoPath'].'/db/db_prumo.sqlite3';

// Conectividade com Banco de dados da aplicação
if (!isset($pConfig['sgdb']))                  $pConfig['sgdb']                  = 'pgsql';
if (!isset($pConfig['dbHost']))                $pConfig['dbHost']                = 'localhost';
if (!isset($pConfig['dbPort']))                $pConfig['dbPort']                = '5432';
if (!isset($pConfig['dbName']))                $pConfig['dbName']                = 'db_prumo';
if (!isset($pConfig['dbUserName']))            $pConfig['dbUserName']            = 'prumo';
if (!isset($pConfig['dbPassword']))            $pConfig['dbPassword']            = 'prumo';
if (!isset($pConfig['appSchema']))             $pConfig['appSchema']             = 'public';
if (!isset($pConfig['theme']))                 $pConfig['theme']                 = 'default';
if (!isset($pConfig['searchLines']))           $pConfig['searchLines']           = 14;
if (!isset($pConfig['afterLogin']))            $pConfig['afterLogin']            = 'index.php';
if (!isset($pConfig['scriptUpdateFramework'])) $pConfig['scriptUpdateFramework'] = '/bin/false';
if (!isset($pConfig['scriptUpdateApp']))       $pConfig['scriptUpdateApp']       = '/bin/false';

if (!isset($pConfig['logInsert']))             $pConfig['logInsert']             = 'f';
if (!isset($pConfig['logSelect']))             $pConfig['logSelect']             = 'f';
if (!isset($pConfig['logUpdate']))             $pConfig['logUpdate']             = 'f';
if (!isset($pConfig['logDelete']))             $pConfig['logDelete']             = 'f';

if (!isset($pConfig['logInsert_prumo']))       $pConfig['logInsert_prumo']       = 'f';
if (!isset($pConfig['logSelect_prumo']))       $pConfig['logSelect_prumo']       = 'f';
if (!isset($pConfig['logUpdate_prumo']))       $pConfig['logUpdate_prumo']       = 'f';
if (!isset($pConfig['logDelete_prumo']))       $pConfig['logDelete_prumo']       = 'f';

if (!isset($pConfig['preferHttps']))           $pConfig['preferHttps']           = true;
////////////////////////////////// fim da configurações padrão //////////////////////////////

//carrega todas as classes
require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_all_models.php');

session_start();

if (!extension_loaded('gettext')) {
	function _($text) {
		return $text;
	}
}

//verifica se as configurações estão salvas no banco e busca as informações
if ($GLOBALS['pConfig']['sgdb_prumo'] == 'pgsql' or $GLOBALS['pConfig']['sgdb_prumo'] == 'sqlite3') {
	pGetConfigDb();
}

if (isset($_SESSION[$GLOBALS['pConfig']['appIdent'].'_prumoUserName'])) {
	$prumoGlobal['currentUser'] = $_SESSION[$GLOBALS['pConfig']['appIdent'].'_prumoUserName'];
	$prumoGlobal['currentFullName'] = $_SESSION[$GLOBALS['pConfig']['appIdent'].'_prumoFullName'];
}
else {
	$prumoGlobal['currentUser'] = '';
	$prumoGlobal['currentFullName'] = '';
}

if (isset($_SERVER['REMOTE_ADDR'])) {
	$prumoGlobal['computerId'] = $_SERVER["REMOTE_ADDR"];
}
else {
	$prumoGlobal['computerId'] = isset($_SERVER["WINDOWID"]) ? $_SERVER["WINDOWID"] : 'local';
}


// seta as configuraçãoes de log da conexão com o banco do framework $pConnectionPrumo
if ($GLOBALS['pConfig']['logInsert_prumo'] == 't') {
	$pConnectionPrumo->setLogType('insert');
}

if ($GLOBALS['pConfig']['logSelect_prumo'] == 't') {
	$pConnectionPrumo->setLogType('select');
}

if ($GLOBALS['pConfig']['logUpdate_prumo'] == 't') {
	$pConnectionPrumo->setLogType('update');
}

if ($GLOBALS['pConfig']['logDelete_prumo'] == 't') {
	$pConnectionPrumo->setLogType('delete');
}

// desativado gettext
/*
//seta o idioma do usuário
setlocale(LC_ALL, $GLOBALS['pConfig']['locale']);

//pasta das tabelas de tradução para gettext
bindtextdomain('Prumo', $GLOBALS['pConfig']['prumoPath'].'/locale');

//seta o dominio Prumo
textdomain('Prumo');

//codificação
//bind_textdomain_codeset('Prumo', 'UTF-8');
*/

//configura o prumoPage, buscando no banco de dados a tabela "routines"
require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php');

// quando em modo dbSingle, levanta a conexão com o banco de dados da aplicação
if ($GLOBALS['pConfig']['dbSingle']) {
	require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection.php');
}

$sqlPrumoRoutines = 'SELECT routine,link FROM '.$pConnectionPrumo->getSchema().'routines;';
$prumoRoutines = $pConnectionPrumo->sql2Array($sqlPrumoRoutines);
for ($i=0; $i<count($prumoRoutines); $i++) {
	if ($prumoRoutines[$i]['link'] != '') {
		$prumoPage[$prumoRoutines[$i]['routine']] = $prumoRoutines[$i]['link'];
	}
}
