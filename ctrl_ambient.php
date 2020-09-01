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


////////////////////////////////// configurações padrão ////////////////////////////////////

$pConfig['version'] = '2.8.2';

// Configurações de path e identificação
if (! isset($pConfig['appIdent']))              $pConfig['appIdent']              = 'Prumo';
if (! isset($pConfig['appName']))               $pConfig['appName']               = 'Framework para PHP';
if (! isset($pConfig['appPath']))               $pConfig['appPath']               = dirname(__DIR__);
if (! isset($pConfig['appWebPath']))            $pConfig['appWebPath']            = isset($_SERVER['REMOTE_ADDR']) ? getAppWebPath() : '';
if (! isset($pConfig['prumoPath']))             $pConfig['prumoPath']             = __DIR__;
if (! isset($pConfig['prumoWebPath']))          $pConfig['prumoWebPath']          = $pConfig['appWebPath'].'/prumo';

// Conectividade com Banco de dados Prumo
if (! isset($pConfig['dbSingle']))              $pConfig['dbSingle']              = false;
if (! isset($pConfig['sgdb_prumo']))            $pConfig['sgdb_prumo']            = 'sqlite3';
if (! isset($pConfig['dbHost_prumo']))          $pConfig['dbHost_prumo']          = '';
if (! isset($pConfig['dbPort_prumo']))          $pConfig['dbPort_prumo']          = '';
if (! isset($pConfig['dbUserName_prumo']))      $pConfig['dbUserName_prumo']      = '';
if (! isset($pConfig['dbPassword_prumo'] ))     $pConfig['dbPassword_prumo']      = '';
if (! isset($pConfig['loginSchema_prumo']))     $pConfig['loginSchema_prumo']     = 'prumo';
if (! isset($pConfig['dbName_prumo']))          $pConfig['dbName_prumo']          = __DIR__.'/db/db_prumo.sqlite3';
if (! isset($pConfig['dbIgnoreCommits']))       $pConfig['dbIgnoreCommits']       = false;

// Conectividade com Banco de dados da aplicação
if (! isset($pConfig['sgdb']))                  $pConfig['sgdb']                  = 'pgsql';
if (! isset($pConfig['dbHost']))                $pConfig['dbHost']                = 'localhost';
if (! isset($pConfig['dbPort']))                $pConfig['dbPort']                = '5432';
if (! isset($pConfig['dbName']))                $pConfig['dbName']                = 'db_prumo';
if (! isset($pConfig['dbUserName']))            $pConfig['dbUserName']            = 'prumo';
if (! isset($pConfig['dbPassword']))            $pConfig['dbPassword']            = 'prumo';
if (! isset($pConfig['appSchema']))             $pConfig['appSchema']             = 'public';
if (! isset($pConfig['useUnaccent']))           $pConfig['useUnaccent']           = 'f';
if (! isset($pConfig['useSimilaritySearch']))   $pConfig['useSimilaritySearch']   = 'f';
if (! isset($pConfig['similarityThreshold']))   $pConfig['similarityThreshold']   = '0';
if (! isset($pConfig['theme']))                 $pConfig['theme']                 = 'default';
if (! isset($pConfig['searchLines']))           $pConfig['searchLines']           = 14;
if (! isset($pConfig['afterLogin']))            $pConfig['afterLogin']            = 'index.php';
if (! isset($pConfig['scriptUpdateFramework'])) $pConfig['scriptUpdateFramework'] = '/bin/false';
if (! isset($pConfig['scriptUpdateApp']))       $pConfig['scriptUpdateApp']       = '/bin/false';

if (! isset($pConfig['logInsert']))             $pConfig['logInsert']             = 'f';
if (! isset($pConfig['logSelect']))             $pConfig['logSelect']             = 'f';
if (! isset($pConfig['logUpdate']))             $pConfig['logUpdate']             = 'f';
if (! isset($pConfig['logDelete']))             $pConfig['logDelete']             = 'f';

if (! isset($pConfig['logInsert_prumo']))       $pConfig['logInsert_prumo']       = 'f';
if (! isset($pConfig['logSelect_prumo']))       $pConfig['logSelect_prumo']       = 'f';
if (! isset($pConfig['logUpdate_prumo']))       $pConfig['logUpdate_prumo']       = 'f';
if (! isset($pConfig['logDelete_prumo']))       $pConfig['logDelete_prumo']       = 'f';

if (! isset($pConfig['preferHttps']))           $pConfig['preferHttps']           = true;
////////////////////////////////// fim da configurações padrão //////////////////////////////

//carrega todas as classes
require_once __DIR__.'/functions_private.php';
require_once __DIR__.'/functions_public.php';
require_once __DIR__.'/class_basic.php';
require_once __DIR__.'/class_pg_connection.php';
require_once __DIR__.'/class_sqlite3_connection.php';
require_once __DIR__.'/class_connection.php';
require_once __DIR__.'/class_grid.php';
require_once __DIR__.'/class_login.php';
require_once __DIR__.'/class_search.php';
require_once __DIR__.'/class_list.php';
require_once __DIR__.'/class_queue.php';
require_once __DIR__.'/class_queue_set.php';
require_once __DIR__.'/class_window.php';
require_once __DIR__.'/class_filter.php';
require_once __DIR__.'/class_menu.php';
require_once __DIR__.'/class_crud.php';
require_once __DIR__.'/class_crud_list.php';
require_once __DIR__.'/class_tab.php';

session_start();

if (! extension_loaded('gettext')) {
    function _($text)
    {
        return $text;
    }
}

if (! extension_loaded('xml')) {
    $msg = _('É necessário ativar a extensão %extension% no PHP');
    echo '    <p>'.str_replace('%extension%', 'xml', $msg).'.</p>'."\n";
	exit;
}

if (! extension_loaded('mbstring')) {
    $msg = _('É necessário ativar a extensão %extension% no PHP');
    echo '    <p>'.str_replace('%extension%', 'mbstring', $msg).'.</p>'."\n";
	exit;
}

/*
if (! extension_loaded('sodium')) {
    $msg = _('É necessário ativar a extensão %extension% no PHP');
    echo '    <p>'.str_replace('%extension%', 'sodium', $msg).'.</p>'."\n";
	exit;
}
*/

//verifica se as configurações estão salvas no banco e busca as informações
if ($GLOBALS['pConfig']['sgdb_prumo'] == 'pgsql' || $GLOBALS['pConfig']['sgdb_prumo'] == 'sqlite3') {
    pGetConfigDb();
}

if (isset($_SESSION[$GLOBALS['pConfig']['appIdent'].'_prumoUserName'])) {
    $prumoGlobal['currentUser'] = $_SESSION[$GLOBALS['pConfig']['appIdent'].'_prumoUserName'];
    $prumoGlobal['currentFullName'] = $_SESSION[$GLOBALS['pConfig']['appIdent'].'_prumoFullName'];
} else {
    $prumoGlobal['currentUser'] = '';
    $prumoGlobal['currentFullName'] = '';
}

if (isset($_SERVER['REMOTE_ADDR'])) {
    $prumoGlobal['computerId'] = $_SERVER["REMOTE_ADDR"];
} else {
    $prumoGlobal['computerId'] = isset($_SERVER["WINDOWID"]) ? $_SERVER["WINDOWID"] : 'local';
}


// seta as configuraçãoes de log da conexão com o banco do framework $pConnectionPrumo
if ($GLOBALS['pConfig']['logInsert_prumo'] == 't') {
    $pConnectionPrumo->setLogType('insert');
}

if ($GLOBALS['pConfig']['logSelect_prumo'] == 't') {
    $pConnectionPrumo->setLogType('select');
}

if ($GLOBALS['pConfig']['logUpdate_prumo'] == 't') {
    $pConnectionPrumo->setLogType('update');
}

if ($GLOBALS['pConfig']['logDelete_prumo'] == 't') {
    $pConnectionPrumo->setLogType('delete');
}

// desativado gettext
/*
//seta o idioma do usuário
setlocale(LC_ALL, $GLOBALS['pConfig']['locale']);

//pasta das tabelas de tradução para gettext
bindtextdomain('Prumo', __DIR__.'/locale');

//seta o dominio Prumo
textdomain('Prumo');

//codificação
//bind_textdomain_codeset('Prumo', 'UTF-8');
*/

//configura o prumoPage, buscando no banco de dados a tabela "routines"
require_once __DIR__.'/ctrl_connection_admin.php';

// quando em modo dbSingle, levanta a conexão com o banco de dados da aplicação
if ($GLOBALS['pConfig']['dbSingle']) {
    require_once __DIR__.'/ctrl_connection.php';
}

$sqlPrumoRoutines = 'SELECT routine,link FROM '.$pConnectionPrumo->getSchema().'routines;';
$prumoRoutines = $pConnectionPrumo->sql2Array($sqlPrumoRoutines);
for ($i = 0; $i<count($prumoRoutines); $i++) {
    if (! empty($prumoRoutines[$i]['link'])) {
        $prumoPage[$prumoRoutines[$i]['routine']] = $prumoRoutines[$i]['link'];
    }
}

/**
 * Retorna o diretório da aplicação (onde tem o arquivo prumo.php)
 *
 * @param $dir string: o diretório onde deve ser procurado a aplicação
 *
 * @return string
 */
function getAppWebPath($dir=false, $dirWeb=false)
{
    if ($dir === false && $dirWeb === false) {
        $dir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : '';
        $dirWeb = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
    }
    if ($dirWeb === '/' || $dirWeb === '') {
        return '';
    }
    return file_exists($dir.'/prumo.php') ? $dirWeb : getAppWebPath(dirname($dir), dirname($dirWeb));
}

