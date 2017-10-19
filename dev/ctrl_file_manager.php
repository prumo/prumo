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

require_once 'prumo.php';

pProtect('prumo_devtools');

if (isset($_POST['open'])) {
    $fileName = $GLOBALS['pConfig']['appPath'].'/'.$_POST['filename'];
    
    if (file_exists($fileName)) {
        $fileContent = file_get_contents($fileName);
        echo $fileContent;
    } else {
        echo _('Arquivo não encontrado "'.$fileName.'".');
    }
}

if (isset($_POST['save'])) {
    $fileName = $GLOBALS['pConfig']['appPath'].'/'.$_POST['filename'];
    
    if (file_exists($fileName) and !is_writable($fileName)) {
        $msg = _('Sem permissão de escrita para o arquivo "%filename%".');
        echo str_replace('%filename%',$_POST['filename'],$msg);
    } elseif (! is_writable($GLOBALS['pConfig']['appPath'])) {
        echo _('Sem permissão de escrita na pasta da aplicação.');
    } else {
        file_put_contents($fileName, $_POST['code']);
        echo 'OK';
    }
}
