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
 
/**
 * Este arquivo contém o objeto de conexão com o banco de dados da aplicação.
 */

if ($GLOBALS['pConfig']['dbSingle']) {
	$pConnection = clone $pConnectionPrumo;
}
else {
	$pConnection = new prumoConnection('sgdb='.$GLOBALS['pConfig']['sgdb'].',dbHost='.$GLOBALS['pConfig']['dbHost']
		                                    .',dbPort='.$GLOBALS['pConfig']['dbPort'].',dbName='.$GLOBALS['pConfig']['dbName']
		                                    .',dbUserName='.$GLOBALS['pConfig']['dbUserName'].',dbPassword='
		                                    .$GLOBALS['pConfig']['dbPassword']
		                                   );
}

$pConnection->setDefaultSchema($GLOBALS['pConfig']['appSchema']);

// seta as configuraçãoes de log da conexão com o banco da aplicação $pConnection
if ($GLOBALS['pConfig']['logInsert'] == 't') {
	$pConnection->setLogType('insert');
}

if ($GLOBALS['pConfig']['logSelect'] == 't') {
	$pConnection->setLogType('select');
}

if ($GLOBALS['pConfig']['logUpdate'] == 't') {
	$pConnection->setLogType('update');
}

if ($GLOBALS['pConfig']['logDelete'] == 't') {
	$pConnection->setLogType('delete');
}

