<?php
/**
 * Copyright (c) 2010 Emerson Casas Salvador <salvaemerson@gmail.com> e Odair Rubleski <orubleski@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the “Software”), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 * 
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

require_once dirname(dirname(__DIR__)).'/prumo.php';

pProtect('prumo_devtools');

if (isset($_POST['open'])) {
    $fileName = dirname(dirname(__DIR__)).'/'.$_POST['filename'];
    
    if (file_exists($fileName)) {
        $fileContent = file_get_contents($fileName);
        echo $fileContent;
    } else {
        echo _('Arquivo não encontrado "'.$fileName.'".');
    }
}

if (isset($_POST['save'])) {
    $fileName = dirname(dirname(__DIR__)).'/'.$_POST['filename'];
    
    if (file_exists($fileName) && ! is_writable($fileName)) {
        $msg = _('Sem permissão de escrita para o arquivo "%filename%".');
        echo str_replace('%filename%',$_POST['filename'],$msg);
    } else if (! is_writable(dirname(dirname(__DIR__)))) {
        echo _('Sem permissão de escrita na pasta da aplicação.');
    } else {
        file_put_contents($fileName, $_POST['code']);
        echo 'OK';
    }
}
