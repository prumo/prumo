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

header('Content-type: text/html; charset=utf-8');

$prumoStyle       = pGetTheme('style.css', true);

echo '<!DOCTYPE HTML>'."\n";
echo '<html>'."\n\n";

echo '<head>'."\n";
echo '    <title>' . $GLOBALS['pConfig']['appIdent'] . ' ' . $GLOBALS['pConfig']['appName'].'</title>'."\n";

if (file_exists($GLOBALS['pConfig']['appPath'].'/favicon.ico')) {
    echo '    <link rel="shortcut icon" href="'.$GLOBALS['pConfig']['appWebPath'].'/favicon.ico" type="image/x-icon" />'."\n";
}

echo '    <link type="text/css" rel="stylesheet" media="screen" href="'.$prumoStyle.'" />'."\n";

if (file_exists($GLOBALS['pConfig']['appPath'].'/style.css')) {
    echo '    <link type="text/css" rel="stylesheet" media="screen" href="'.$GLOBALS['pConfig']['appWebPath'].'/style.css" />'."\n";
}

echo '</head>'."\n";
echo '<script type="text/javascript" src="'.$GLOBALS['pConfig']['prumoWebPath'].'/prumo.js"></script>'."\n";
echo '<body>'."\n";

