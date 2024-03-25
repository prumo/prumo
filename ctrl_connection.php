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

/**
 * Este arquivo contém o objeto de conexão com o banco de dados da aplicação.
 */

if ($GLOBALS['pConfig']['dbSingle']) {
    $pConnection = clone $pConnectionPrumo;
} else {
    $pConnection = new PrumoConnection(
        'sgdb='.$GLOBALS['pConfig']['sgdb']
        .',dbHost='.$GLOBALS['pConfig']['dbHost']
        .',dbPort='.$GLOBALS['pConfig']['dbPort']
        .',dbName='.$GLOBALS['pConfig']['dbName']
        .',dbUserName='.$GLOBALS['pConfig']['dbUserName']
        .',dbPassword='.$GLOBALS['pConfig']['dbPassword']
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

