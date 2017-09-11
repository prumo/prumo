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

require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php');

echo '<div id="pLoading" class="loading">';
echo '<img src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/loading_bar.gif" alt="" /><br />'._('Carregando').'...';
echo '</div>'."\n\n";
echo '<script type="text/javascript">'."\n";
echo '	pLoading = new prumoLoading(\'pLoading\');'."\n";
echo '</script>'."\n";

