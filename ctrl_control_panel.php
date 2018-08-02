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

require_once dirname(__DIR__).'/prumo.php';
require_once __DIR__.'/ctrl_connection_admin.php';

pProtect('prumo_controlPanel');

$configToWrite[] = 'appIdent';
$configToWrite[] = 'appName';

if (! isset($GLOBALS['pConfig']['dbSingle']) || !$GLOBALS['pConfig']['dbSingle']) {
    $configToWrite[] = 'sgdb';
    $configToWrite[] = 'dbHost';
    $configToWrite[] = 'dbPort';
    $configToWrite[] = 'dbName';
    $configToWrite[] = 'dbUserName';
    $configToWrite[] = 'dbPassword';
}

$configToWrite[] = 'appSchema';
$configToWrite[] = 'language';
$configToWrite[] = 'locale';
$configToWrite[] = 'useUnaccent';
$configToWrite[] = 'useSimilaritySearch';
$configToWrite[] = 'similarityThreshold';
$configToWrite[] = 'theme';
$configToWrite[] = 'searchLines';
$configToWrite[] = 'logInsert';
$configToWrite[] = 'logSelect';
$configToWrite[] = 'logUpdate';
$configToWrite[] = 'logDelete';
$configToWrite[] = 'logInsert_prumo';
$configToWrite[] = 'logSelect_prumo';
$configToWrite[] = 'logUpdate_prumo';
$configToWrite[] = 'logDelete_prumo';
$configToWrite[] = 'scriptUpdateFramework';
$configToWrite[] = 'scriptUpdateApp';

function writeConfig($configName, $configValue)
{
    global $pConnectionPrumo;
    
    $sqlRetrieve = 'SELECT count(*) FROM '.$pConnectionPrumo->getSchema().'config WHERE config_name=\''.$configName.'\';';
    if ($pConnectionPrumo->sqlQuery($sqlRetrieve) == '0') {
        $sqlWrite = 'INSERT INTO '.$pConnectionPrumo->getSchema().'config (config_name,config_value) VALUES (\''.$configName.'\',\''.$configValue.'\');';
    } else {
        $sqlWrite = 'UPDATE '.$pConnectionPrumo->getSchema().'config SET config_value=\''.$configValue.'\' WHERE config_name=\''.$configName.'\';';
    }
    
    $pConnectionPrumo->sqlQuery($sqlWrite);
}

// Grava as configurações no banco de dados do framework
for ($i = 0; $i < count($configToWrite); $i++)
{
    $config = $configToWrite[$i];
    $value = isset($_POST[$configToWrite[$i]]) ? $_POST[$configToWrite[$i]] : '0';
    
    writeConfig($config, $value);
}
echo 'OK';

