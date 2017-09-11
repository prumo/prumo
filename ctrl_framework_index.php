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
 * Este arquivo controla a interface do sistema
 */

require_once($GLOBALS['pConfig']['prumoPath'].'/view_header.php');
	
$pLogin = new prumoLogin($GLOBALS['pConfig']['appIdent'], '', '');

if (isset($_GET['action']) and $_GET['action'] == 'logoff') {
	
	$pLogin->logoff();
	pRedirect($GLOBALS['pConfig']['appWebPath'].'/index.php');
}
else {
	
	if ($pLogin->isSession()) {
		
		if ($GLOBALS['pConfig']['afterLogin'] == 'index.php') {
			
			include($GLOBALS['pConfig']['prumoPath'].'/view_loading.php');
			include($GLOBALS['pConfig']['prumoPath'].'/view_page.php');
			include($GLOBALS['pConfig']['prumoPath'].'/view_footer.php');
		}
		else {
			
			if ($GLOBALS['pConfig']['appWebPath'] == '' or $GLOBALS['pConfig']['appWebPath'] == '/') {
				pRedirect($GLOBALS['pConfig']['afterLogin']);
			}
			else {
				pRedirect($GLOBALS['pConfig']['appWebPath'].'/'.$GLOBALS['pConfig']['afterLogin']);
			}
		}
	}
	else {
		include($GLOBALS['pConfig']['prumoPath'].'/view_login.php');
	}
}

