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
 * Recupera as configurações do framework no banco de dados
 */
function pGetConfigDb()
{
    global $pConfig;
    global $pConnectionPrumo;
    
    $dbSingle = (isset($pConfig['dbSingle']) && $pConfig['dbSingle']);
    
    require_once __DIR__.'/ctrl_connection_admin.php';
    
    if ($pConnectionPrumo->connected(true) == false) {
        
        echo $pConnectionPrumo->getErr();
        
        if ($pConnectionPrumo->sgdb() != 'sqlite3') {
            echo ' ('.$pConnectionPrumo->getParam('dbname').' - '.$pConnectionPrumo->sgdb().')';
        }
        
        exit;
    }
    
    $sql = 'SELECT * FROM '.$pConnectionPrumo->getSchema().'config;';
    $sqlArray = $pConnectionPrumo->sql2Array($sql);
    
    $pConfigDb = array();
    for ($i = 0; $i < count($sqlArray); $i++) {
        $pConfigDb[$sqlArray[$i]['config_name']] = $sqlArray[$i]['config_value'];
    }
    
    if (count($pConfigDb) > 0) {
        $pConfigNew = array_replace($pConfig, $pConfigDb);
        $pConfig = $pConfigNew;
    }
    
    if ($dbSingle) {
        $pConfig['sgdb']       = $pConfig['sgdb_prumo'];
        $pConfig['dbHost']     = $pConfig['dbHost_prumo'];
        $pConfig['dbPort']     = $pConfig['dbPort_prumo'];
        $pConfig['dbName']     = $pConfig['dbName_prumo'];
        $pConfig['dbUserName'] = $pConfig['dbUserName_prumo'];
        $pConfig['dbPassword'] = $pConfig['dbPassword_prumo'];
    }
}

/**
 * Carrega um array com as permissões de todas as rotinas
 */
function loadPermission()
{
    global $pConnectionPrumo;
    global $prumoPermission;
    global $prumoGlobal;
    
    if (! isset($pConnectionPrumo)) {
        require_once __DIR__.'/ctrl_connection_admin.php';
    }
    
    $schema = $pConnectionPrumo->getSchema();
    
    $sql  = 'SELECT '."\n";
    $sql .= '    r.routine,'."\n";
    $sql .= '    sum(CASE WHEN c=\'t\' THEN 1 ELSE 0 END) as c,'."\n";
    $sql .= '    sum(CASE WHEN r=\'t\' THEN 1 ELSE 0 END) as r,'."\n";
    $sql .= '    sum(CASE WHEN u=\'t\' THEN 1 ELSE 0 END) as u,'."\n";
    $sql .= '    sum(CASE WHEN d=\'t\' THEN 1 ELSE 0 END) as d'."\n";
    $sql .= 'FROM '.$schema.'routines r'."\n";
    $sql .= 'JOIN '.$schema.'routines_groups rg ON rg.routine=r.routine'."\n";
    $sql .= 'JOIN '.$schema.'groups_syslogin gs ON gs.groupname=rg.groupname'."\n";
    $sql .= 'JOIN '.$schema.'groups g ON g.groupname=rg.groupname'."\n";
    $sql .= 'JOIN '.$schema.'syslogin s ON s.username=gs.username'."\n";
    $sql .= 'WHERE r.enabled=\'t\''."\n";
    $sql .= 'AND g.enabled=\'t\''."\n";
    $sql .= 'AND s.enabled=\'t\''."\n";
    $sql .= 'AND gs.username=\''.$prumoGlobal['currentUser'].'\''."\n";
    $sql .= 'GROUP BY r.routine;'."\n";
    
    $prumoPermission = $pConnectionPrumo->sql2Array($sql);
}

/**
 * Grava log de acesso negado
 *
 * @param $routine string: rotina
 * @param $permission string: permissão
 */
function pLogAcessDenied(string $routine, string $permission)
{
    global $pConnectionPrumo;
    
    $sql  = 'INSERT INTO '.$pConnectionPrumo->getSchema().'acess_denied ('."\n";
    $sql .= '    username,'."\n";
    $sql .= '    routine,'."\n";
    $sql .= '    permission'."\n";
    $sql .= ')'."\n";
    $sql .= 'VALUES ('."\n";
    $sql .= '    '.pFormatSql($GLOBALS['prumoGlobal']['currentUser'], 'string').','."\n";
    $sql .= '    '.pFormatSql($routine, 'string').','."\n";
    $sql .= '    '.pFormatSql($permission, 'string')."\n";
    $sql .= ');';
    
    $pConnectionPrumo->sqlQuery($sql);
}

/**
 * Verifica se determinado arquivo de atualização sql já foi executado
 *
 * @param $fileName string: nome do arquivo que contém os comandos SQL de atualizaçãp do banco no formato do Prumo
 * @param $connection PrumoConnection: a conexão com o banco de dados a ser usada
 * @param $db string: 'framework' para banco de dados do framework e 'app' para o banco de dados da aplicação
 *
 * @returns boolean
 */
function upToDate(string $fileName, PrumoConnection $connection, string $db='framework') : bool
{
    global $pConnectionPrumo;
    
    if ($db == 'framework') {
        $table = 'update_framework';
    } else {
        $table = 'update_db_app';
        writeAppUpdate('');
    }    
    
    $sql = 'SELECT count(*) FROM '.$pConnectionPrumo->getSchema().$table.' WHERE file_name='.pFormatSql($fileName, 'string').';';
    
    return $connection->sqlQuery($sql);
}

/**
 * Grava no banco de dados da aplicação os scripts de atualização que já foram executados
 *
 * @param $fileName string: nome do script, se informado '', apenas irá verificar e criar a tabela de atualização no
 * banco da app
 */
function writeAppUpdate(string $fileName)
{
    global $pConnection;
    global $pConnectionPrumo;
    global $prumoGlobal;
    
    //verifica se a tabela existe na base de dados, caso não existe, cria
    $sql  = 'SELECT'."\n";
    $sql .= '    count(*)'."\n";
    $sql .= 'FROM information_schema.tables'."\n";
    $sql .= 'WHERE table_schema='.pFormatSql($GLOBALS['pConfig']['loginSchema_prumo'],'string')."\n";
    $sql .= 'AND table_name=\'update_db_app\';';
    $table = $pConnection->sqlQuery($sql);
    
    if (! $table) {
        
        $sql  = 'CREATE TABLE '.$pConnectionPrumo->getSchema().'update_db_app'."\n";
        $sql .= '('."\n";
        $sql .= '  file_name character varying(100) NOT NULL,'."\n";
        $sql .= '  usr_login character varying(40),'."\n";
        $sql .= '  date_time timestamp without time zone NOT NULL DEFAULT now(),'."\n";
        $sql .= '  CONSTRAINT update_db_app_pkey PRIMARY KEY (file_name)'."\n";
        $sql .= ')'."\n";
        $sql .= 'WITH ('."\n";
        $sql .= '  OIDS=FALSE'."\n";
        $sql .= ');';
        
        $pConnection->sqlQuery($sql);
    }
    
    if ($fileName != '') {
        
        $sql  = 'INSERT INTO '.$pConnectionPrumo->getSchema().'update_db_app ('."\n";
        $sql .= '    file_name,'."\n";
        $sql .= '    usr_login,'."\n";
        $sql .= '    date_time'."\n";
        $sql .= ')'."\n";
        $sql .= 'VALUES ('."\n";
        $sql .= '    '.pFormatSql($fileName,'string').','."\n";
        $sql .= '    '.pFormatSql($prumoGlobal['currentUser'],'string').','."\n";
        $sql .= '    now()'."\n";
        $sql .= ');'."\n";
        
        $pConnection->sqlQuery($sql);
    }
}

/**
 * Exibe o prumoInfo na página do desenvolvedor
 */
function prumoInfo()
{
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="pt_br" xml:lang="pt_br">'."\n";
    echo '<head>'."\n";
    echo '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'."\n";
    echo '    <title>prumoInfo()</title><meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />'."\n";
    
    echo '<style type="text/css">'."\n";
    echo 'body {background-color: #fff; color: #222; font-family: sans-serif;}'."\n";
    echo 'pre {margin: 0; font-family: monospace;}'."\n";
    echo 'a:link {color: #009; text-decoration: none; background-color: #fff;}'."\n";
    echo 'a:hover {text-decoration: underline;}'."\n";
    echo 'table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}'."\n";
    echo '.center {text-align: center;}'."\n";
    echo '.center table {margin: 1em auto; text-align: left;}'."\n";
    echo '.center th {text-align: center !important;}'."\n";
    echo 'td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}'."\n";
    echo 'h1 {font-size: 150%;}'."\n";
    echo 'h2 {font-size: 125%;}'."\n";
    echo '.p {text-align: left;}'."\n";
    echo '.e {background-color: #ccf; width: 300px; font-weight: bold;}'."\n";
    echo '.h {background-color: #99c; font-weight: bold;}'."\n";
    echo '.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}'."\n";
    echo '.v i {color: #999;}'."\n";
    echo 'img {float: right; border: 0;}'."\n";
    echo 'hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}'."\n";
    echo '</style>'."\n";
    
    echo '</head>'."\n";
    echo '<body>'."\n";
    echo '<div class="center">'."\n";
    echo '<table border="0" width="800">'."\n";
    echo '    <tr class="h">'."\n";
    echo '        <td>'."\n";
    echo '            <a href="https://github.com/prumo/framework">'."\n";
    echo '                <img border="0" src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/logo_small.png" alt="Prumo Logo" />'."\n";
    echo '            </a>'."\n";
    echo '        <h1 class="p">Prumo Framework Version: '.$GLOBALS['pConfig']['version'].'</h1></td>'."\n";
    echo '    </tr>'."\n";
    echo '</table>'."\n";

    // $GLOBALS['pConfig']
    echo '<table border="0" cellpadding="3" width="800">'."\n";
    foreach($GLOBALS['pConfig'] as $param => $value) {
        if ($param == 'dbPassword' || $param == 'dbPassword_prumo') {
            echo '<tr><td class="e">$GLOBALS[\'pConfig\'][\''.$param.'\']</td><td class="v">******</td></tr>'."\n";
        } else {
            echo '<tr><td class="e">$GLOBALS[\'pConfig\'][\''.$param.'\']</td><td class="v">'.$value.'</td></tr>'."\n";
        }
    }
    echo '</table>'."\n";
    
    echo '<h2>prumoPage</h2>'."\n";

    // $GLOBALS['prumoPage']
    echo '<table border="0" cellpadding="3" width="800">'."\n";
    echo '<tr class="h"><th>Page</th><th>File to include</th></tr>'."\n";
    foreach($GLOBALS['prumoPage'] as $param => $value) {
        echo '<tr><td class="e">$GLOBALS[\'prumoPage\'][\''.$param.'\']</td><td class="v">'.$value.'</td></tr>'."\n";
    }
    echo '</table>'."\n";
    
    echo '<h2>prumoGlobal</h2>'."\n";
    
    // $GLOBALS['prumoGlobal']
    echo '<table border="0" cellpadding="3" width="800">'."\n";
    foreach($GLOBALS['prumoGlobal'] as $param => $value) {
        echo '<tr><td class="e">$GLOBALS[\'prumoGlobal\'][\''.$param.'\']</td><td class="v">'.$value.'</td></tr>'."\n";
    }
    echo '</table>'."\n";
    
    echo '<h2>Prumo License</h2>'."\n";
    echo '<table>'."\n";
    echo '<tr class="v"><td>'."\n";
    
    echo '<p>'."\n";
    echo 'Copyright (c) 2010 Emerson Casas Salvador <salvaemerson@gmail.com> e Odair Rubleski <orubleski@gmail.com>'."\n";
    echo '</p>'."\n";
    echo '<p>Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:'."\n";
    echo '</p>'."\n";
    echo '<p>The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.'."\n";
    echo '</p>'."\n";
    echo '<p>THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.'."\n";
    echo '</p>'."\n";
    echo '</td></tr>'."\n";
    echo '</table>'."\n";
}

/**
 * Formata um array de parametros no formato do Prumo
 *
 * @param $params string: string de parametros no formato do prumo (parametro=valor,outroParametro=valor,parametroBoleano)
 *
 * @return array: array formatado
 */
function pParameters(string $params)
{
    $escapeParams = str_replace('\\,', ':.:', $params);
    
    $arrParams = array();
    $theArg = explode(',', $escapeParams);
    
    for ($i = 0; $i < count($theArg); ++$i) {
        
        $theRow = explode('=', $theArg[$i]);
        
        if (count($theRow) == 1) {
            
            // bolean true
            $arrParams[strtolower($theRow[0])] = true;
        } else if (count($theRow) == 2) {
            
            // normal parameter
            $arrParams[strtolower($theRow[0])] = str_replace(':.:', ',', $theRow[1]);
        } else {
            
            // send by GET method
            $arrParams[strtolower($theRow[0])] = '';
            for ($j=1; $j < count($theRow);$j++) {
                $arrParams[strtolower($theRow[0])] .= $j == 1 ? str_replace(':.:', ',', $theRow[$j]) : '='.str_replace(':.:', ',', $theRow[$j]);
            }
        }
    }
    
    return $arrParams;
}

/**
 * Parser para tratamento de Sql Injection
 *
 * @param $value string: o valor a ser tratado
 * @param $type string: o tipo de dado [string,text,longtext,integer,serial,numeric,date,time,timestamp,boolean]
 *
 * @return string
 */
function pSqlNoInjection($value, string $type, bool $formatSqlNull=false)
{
    $valueNoInjection = $value;
    
    if (! in_array($type, array('string', 'text', 'longtext', 'integer', 'serial', 'numeric', 'date', 'time', 'timestamp', 'boolean'))) {
        $msg = _('Prumo: Tipo "%type%" desconhecido em pSqlNoInjection!');
        throw new Exception(str_replace('%type%', $type, $msg));
    }
    
    switch ($type) {
        
        case "string":
        case "text":
        case "longtext":
            $valueNoInjection = str_replace('\'', '\'\'', $valueNoInjection);
        break;
        
        case "integer":
        case "serial":
            $valueNoInjection = preg_replace("/[^0-9\-\+\*\/]/", "", $valueNoInjection);
        break;
        
        case "numeric":
            $valueNoInjection = preg_replace("/[^0-9\.\,\-\+\*\/]/", "", $valueNoInjection);
        break;
        
        case "date":
            $valueNoInjection = preg_replace("/[^0-9\-\/\/]/", "", $valueNoInjection);
        break;
        
        case "time":
            $valueNoInjection = preg_replace("/[^0-9:]/", "", $valueNoInjection);
        break;
        
        case "timestamp":
            $valueNoInjection = preg_replace("/[^0-9\T\:\+\-\/\ ]/", "", $valueNoInjection);
        break;
    }
    
    return $formatSqlNull ? pFormatSqlNull($valueNoInjection, $type) : $valueNoInjection;
}

/**
 * Traduz '' para NULL usado em instuções SQL
 *
 * @param $value string: valor
 * @param $type string: tipo de dado
 *
 * @return string: dado formatado
 */
function pFormatSqlNull($value, string $type)
{
    return ($type != 'boolean' && $value == '') ? 'NULL' : $value;
}

/**
 * Carrega as permissões para uma rotina específica
 *
 * @param $routine string: nome da rotina
 *
 * @return array: permissões da rotina informada
 */
function getPermission(string $routine) : array
{
    global $prumoPermission;
    
    if (! isset($prumoPermission)) {
        loadPermission();
    }
    
    $permission = array('routine'=>$routine,'c'=>false,'r'=>false,'u'=>false,'d'=>false);
    
    for ($i = 0; $i < count($prumoPermission); $i++) {
        if ($routine == $prumoPermission[$i]['routine']) {
            $permission = $prumoPermission[$i];
            
            $permission['c'] = $permission['c'] > 0;
            $permission['r'] = $permission['r'] > 0;
            $permission['u'] = $permission['u'] > 0;
            $permission['d'] = $permission['d'] > 0;
        }
    }
    
    return $permission;
}

/**
 * Grava log de auditoria
 *
 * @param $routine string: nome da rotina
 * @param $objName string: nome do objeto
 * @param $sqlCommand string: comando SQL
 * @param $crud string: ação do crud
 */
function pAuditLog(string $routine, string $objName, string $sqlCommand, string $crud)
{
    global $pConfig;
    global $pConnectionPrumo;
    
    require_once __DIR__.'/ctrl_connection_admin.php';
    
    if ($pConnectionPrumo->sgdb() == 'sqlite3') {
        $now = 'datetime(\'now\')';
    }
    
    if ($pConnectionPrumo->sgdb() == 'pgsql') {
        $now = 'now()';
    }
    
    $sqlAuditLog  = 'INSERT INTO '.$pConnectionPrumo->getSchema().'log_sql ('."\n";
    $sqlAuditLog .= '    routine,'."\n";
    $sqlAuditLog .= '    log_timestamp,'."\n";
    $sqlAuditLog .= '    log_obj_name,'."\n";
    $sqlAuditLog .= '    usr_login,'."\n";
    $sqlAuditLog .= '    log_prumo_method,'."\n";
    $sqlAuditLog .= '    log_statement'."\n";
    $sqlAuditLog .= ')'."\n";
    $sqlAuditLog .= 'VALUES('."\n";
    $sqlAuditLog .= '    '.pFormatSql($routine, 'string').','."\n";
    $sqlAuditLog .= '    '.$now.','."\n";
    $sqlAuditLog .= '    '.pFormatSql($objName, 'string').','."\n";
    $sqlAuditLog .= '    '.pFormatSql($GLOBALS['prumoGlobal']['currentUser'], 'string').','."\n";
    $sqlAuditLog .= '    '.pFormatSql($crud, 'string').','."\n";
    $sqlAuditLog .= '    '.pFormatSql($sqlCommand, 'string')."\n";
    $sqlAuditLog .= ');'."\n";
    
    $sqlOk = $pConnectionPrumo->sqlQuery($sqlAuditLog, true);
    
    if ($sqlOk === false) {
        pXmlError('SqlError', $pConnectionPrumo->getErr(), true);
        exit;
    }
}

/**
 * Separa os campos de uma data (dia, mês e ano)
 *
 * @param $date string: data
 *
 * @return array: array associativo com as partes da data
 * @return bool: false em caso de falha
 */
function pParseDate(string $date)
{
    if (substr_count($date, '/') > 0) {
        $part = explode('/', $date);
        $day   = trim($part[0]);
        $month = isset($part[1]) ? trim($part[1]) : '00';
        $year  = isset($part[2]) ? trim($part[2]) : '00';
    } else if (substr_count($date, '-') > 0) {
        $part = explode('-', $date);
        $year  = trim($part[0]);
        $month = isset($part[1]) ? trim($part[1]) : '00';
        $day   = isset($part[2]) ? trim($part[2]) : '00';
    } else {
        return false;
    }
    
    if (empty($day)) $day = '00';
    if (empty($month)) $month = '00';
    
    if (strlen($day) == 1) {
        $day = '0'.$day;
    }
    if (strlen($month) == 1) {
        $month = '0'.$month;
    }
    
    return array(
        'year'  => trim($year),
        'month' => trim($month),
        'day'   => trim($day)
    );
}

/**
 * Separa os campos de um horário (horas, minutos, segundos e timezone)
 *
 * @param $time string: horário
 *
 * @return array: array associativo com as partes do horário
 */
function pParseTime(string $date)
{
    $part = explode(':', $date);
    $hour = substr(trim($part[0]), 0, 2);
    $minute = isset($part[1]) ? trim($part[1]) : '00';
    $second = isset($part[2]) ? trim($part[2]) : '00';
    
    if (empty($hour)) $hour = '00';
    if (empty($minute)) $minute = '00';
    if (empty($second)) $second = '00';
    
    if (strlen($hour) == 1) {
        $hour = '0'.$hour;
    }
    if (strlen($minute) == 1) {
        $minute = '0'.$minute;
    }
    if (strlen($second) == 1) {
        $second = '0'.$second;
    }
    
    return array(
        'hour' => $hour,
        'minute' => $minute,
        'second' => $second
    );
}

/**
 * Verifica se determinada rotina tem audit
 *
 * @param $routine string: nome da rotina
 *
 * @return boolean
 */
function pGetAudit(string $routine) : bool
{
    global $pConnectionPrumo;
    
    if (! isset($pConnectionPrumo)) {
        require_once __DIR__.'/ctrl_connection_admin.php';
    }
    
    $sql = 'SELECT audit FROM '.$pConnectionPrumo->getSchema().'routines WHERE routine='.pFormatSql($routine, 'string').';';
    
    return $pConnectionPrumo->sqlQuery($sql) == 't';
}

/**
 * Código reaproveitável, pega o nome do objeto instanciado
 */
trait PGetName
{
    public $name = '';
    
    /**
     * Retorna o nome da instância
     *
     * @return string
     */
    public function getObjName()
    {
        if (empty($this->name)) {
            
            $className = get_class($this);
            $instance = array();
            
            foreach ($GLOBALS as $key => $value) {
                if (is_object($value) && get_class($value) == $className) {
                    $instance[] = $key;
                }
            }
            
            for ($i=0; $i < count($instance); $i++) {
                $objName = $instance[$i];
                global $$objName;
                if (empty(${$objName}->name)) {
                    ${$objName}->name = $objName;
                    break;
                }
            }
        }
        
        return $this->name;
    }
}

