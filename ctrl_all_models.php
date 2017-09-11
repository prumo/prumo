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

require_once($GLOBALS['pConfig']['prumoPath'].'/functions_private.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/functions_public.php');
 
require_once($GLOBALS['pConfig']['prumoPath'].'/class_basic.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_pg_connection.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_sqlite3_connection.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_connection.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_grid.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_login.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_search.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_queue.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_queue_set.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_window.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_filter.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_menu.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_crud.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_crud_list.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/class_tab.php');

