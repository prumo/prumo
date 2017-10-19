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
 * Este arquivo contém o objeto de conexão com o banco de dados.
 */

$pConnectionPrumo = new PrumoConnection('sgdb='.$GLOBALS['pConfig']['sgdb_prumo'].',dbHost='.$GLOBALS['pConfig']['dbHost_prumo']
                                        .',dbPort='.$GLOBALS['pConfig']['dbPort_prumo'].',dbName='.$GLOBALS['pConfig']['dbName_prumo']
                                        .',dbUserName='.$GLOBALS['pConfig']['dbUserName_prumo'].',dbPassword='
                                        .$GLOBALS['pConfig']['dbPassword_prumo']
                                       );

$pConnectionPrumo->setDefaultSchema($GLOBALS['pConfig']['loginSchema_prumo']);
