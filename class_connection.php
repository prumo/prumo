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
 * PrumoConnection faz a conexão com o banco de dados de acordo com o sgdb escolhido nas configurações
 */
class PrumoConnection
{
    use PGetName;
    
    private $connection;
    private $param;
    private $defaultSchema = '';
    private $logType;
    
    /**
     * Construtor da classe PrumoConnection
     *
     * @params string: parametros de conxão (é configurado em ctrl_connection.php)
     * @param $logType string: tipo de log (é configurado em ctrl_connection.php)
     */
    function __construct($params, $logType=array())
    {
        $this->param = pParameters($params);
        $this->err = '';
        $this->logType = $logType;
        
        switch ($this->param['sgdb']) {
            
            case 'pgsql':
                $this->connection = new PrumoPgConnection($params);
                break;
            case 'sqlite3':
                $this->connection = new PrumoSqlite3Connection($params);
                break;
        }
    }
    
    /**
     * Zera alguns parametros ao clonar
     */
    function __clone()
    {
        $this->name = '';
        $this->err = '';
        $this->logType = array();
        $this->defaultSchema = '';
    }
    
    /**
     * Define o valor default para o schema do banco de dados
     */
    public function setDefaultSchema($schema)
    {
        $this->defaultSchema = $schema;
    }
    
    /**
     * Ativa o log SQL para determinado tipo de operação
     *
     * @param $type string: tipo de log 'insert', 'select', 'update', 'delete'
     */
    public function setLogType($type)
    {
        if (! in_array(strtolower($type), $this->logType)) {
            $this->logType[] = strtolower($type);
        }
    }
    
    /**
     * Retorna o schema do banco de dados formatado em SQL de acordo com o SGDB que está sendo usado
     *
     * @param $schema string: nome do schema
     *
     * @return string
     */
    public function getSchema($schema='')
    {
        $sgdb = $this->sgdb();
        $auxSchema = empty($schema) ? $this->defaultSchema : $schema;
        
        if (empty($auxSchema)) {
            return '';
        } else {
            switch ($sgdb) {
                case 'pgsql':
                    return $auxSchema.'.';
                    break;
                case 'sqlite3':
                    return $auxSchema.'_';
                    break;
                default:
                    return $auxSchema;
                    break;
            }
        }
    }
    
    /**
     * Grava o log do comando SQL nas tabelas do framework
     *
     * @param $sql string: comando SQL a ser gravado
     * @param $method string: o nome do método que executou o comando SQL
     */
    private function logSql($sql, $method)
    {
        global $prumoGlobal;
        global $pConnectionPrumo;
        
        if (count($this->logType) > 0) {
            
            $this->getObjName();
            
            require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php';
            
            $logInsert = false;
            $logRetrieve = false;
            $logUpdate = false;
            $logDelete = false;
            
            for ($i = 0; $i < count($this->logType); $i++) {
                
                switch ($this->logType[$i]) {
                    
                    case 'insert':
                        $logInsert = true;
                        break;
                        
                    case 'select':
                        $logRetrieve = true;
                        break;
                        
                    case 'update':
                        $logUpdate = true;
                        break;
                        
                    case 'delete':
                        $logDelete = true;
                        break;
                }
                
            }
            
            if (
                ($logInsert and stristr($sql, 'insert')) or 
                ($logRetrieve and stristr($sql, 'select')) or
                ($logUpdate and stristr($sql, 'update')) or
                ($logDelete and stristr($sql, 'delete'))
            ) {
                
                if ($pConnectionPrumo->sgdb() == 'sqlite3') {
                    $now = 'datetime(\'now\')';
                }
                
                if ($pConnectionPrumo->sgdb() == 'pgsql') {
                    $now = 'now()';
                }
                
                $sqlLog  = 'INSERT INTO '.$pConnectionPrumo->getSchema().'log_sql ('."\n";
                $sqlLog .= '    log_timestamp,'."\n";
                $sqlLog .= '    log_obj_name,'."\n";
                $sqlLog .= '    usr_login,'."\n";
                $sqlLog .= '    log_prumo_method,'."\n";
                $sqlLog .= '    log_statement'."\n";
                $sqlLog .= ')'."\n";
                $sqlLog .= 'VALUES('."\n";
                $sqlLog .= '    '.$now.','."\n";
                $sqlLog .= '    '.pFormatSql($this->getObjName(), 'string').','."\n";
                $sqlLog .= '    '.pFormatSql($prumoGlobal['currentUser'], 'string').','."\n";
                $sqlLog .= '    '.pFormatSql($method, 'string').','."\n";
                $sqlLog .= '    '.pFormatSql($sql, 'string')."\n";
                $sqlLog .= ');'."\n";
                
                $pConnectionPrumo->sqlQuery($sqlLog, true);
            }
        }
    }
    
    /**
     * Retorna o array de erros da conexão com o SGDB
     *
     * @return array
     */
    public function getErr()
    {
        return $this->connection->getErr();
    }
    
    /**
     * Retorna a conexão com o banco de dados, se não estiver conectado conecta antes
     *
     * @return object
     */
    public function getConnection()
    {
        return $this->connection->getConnection();
    }
    
    /**
     * Verifica se a conexão com o SGDB está estabelecida
     *
     * @param reconnect boolean: indica se deve tentar reconectar caso esteja desconectado
     *
     * @return boolean
     */
    public function connected($reconnect=false)
    {
        $this->getObjName();
        return $this->connection->connected($reconnect);
    }
    
    /**
     * Executa uma consulta no banco de dados e retorna um único valor
     *
     * @param $sql string: consulta SQL
     * @param $ignoreLog boolean: indica se deve deixar de gravar o log mesmo que o parametro de gravação de log esteja ativado
     *
     * @return mixed: apenas o primeiro valor da primeira coluna retornada pela consulta SQL
     * @return boolean false: em caso de falha na consulta SQL
     */
    public function sqlQuery($sql, $ignoreLog=false)
    {
        $this->getObjName();
        
        $ret = $this->connection->sqlQuery($sql);
        
        if ($ignoreLog == false) {
            $this->logSql($sql, 'sqlQuery');
        }
        
        return $ret;
    }
    
    /**
     * Executa uma consulta no banco de dados e retorna em um array associativo (um único registro sem índice)
     *
     * @param $sql string: consulta SQL
     *
     * @return array: array associativo com o registro retornado pela consulta SQL sendo o nome da coluna a chave do array
     * @return boolean false: em caso de falha na consulta SQL
     */
    public function fetchAssoc($sql)
    {
        $this->getObjName();
        
        $ret = $this->connection->fetchAssoc($sql);
        $this->logSql($sql, 'fetchAssoc');
        
        return $ret;

    }
    
    /**
     * Executa uma consulta no banco de dados e retorna em um array associativo com vários registros (semelhante a fetchAssoc)
     *
     * @param $sql string: consulta SQL
     *
     * @return array: array associativo com os registros retornados pela consulta SQL sendo o nome da coluna a chave do array
     * @return boolean false: em caso de falha na consulta SQL
     */
    public function sql2Array($sql)
    {
        $this->getObjName();
        
        $ret = $this->connection->sql2Array($sql);
        $this->logSql($sql, 'sql2Array');
        
        return $ret;
    }
    
    /**
     * Retorna o resultado de uma consulta SQL em formato XML
     *
     * @param $sql string: consulta SQL
     * @param $tableName string: nome da tag container
     *
     * @param string: xml com os dados retornados pela consulta SQL
     */
    public function sqlXml($sql, $tableName)
    {
        $this->getObjName();
        
        $ret = $this->connection->sqlXml($sql, $tableName);
        $this->logSql($sql, 'sqlXml');
        
        return $ret;
    }
    
    /**
     * Formata um operador lógido de acordo com o SGDB
     *
     * @return string: operador lógico formatado ex: ':field: ilike \'%:value:%\''
     */
    public function getSqlOperator($operator)
    {
        return $this->connection->sqlOperator[$operator];
    }
    
    /**
     * Fecha a conexão com o SGDB
     */
    public function disconnect()
    {
        $this->connection->disconnect();
    }
    
    /**
     * Retorna nome do SGDB que está sendo usado
     *
     * @return string: nome do SGDB
     */
    public function sgdb()
    {
        return $this->param['sgdb'];
    }
    
    /**
     * Converte um determinado tipo no formato do SGDB, ex: 'string' => 'character varying'
     *
     * @type string: tipo de dado
     *
     * @return string: tipo de dados de acordo com o SGDB
     */
    public function dbType($type)
    {
        return $this->connection->dbType($type);
    }
    
    /**
     * Substitui as variáveis globais em um comando sql (exemplo: substitui :prumoUser: pelo usuário logado)
     *
     * @param $sqlIn string: comando SQL
     *
     * @return string string: comando SQL com as variáveis globais substituídas
     */
    public function replacePrumoGlobals($sqlIn)
    {
        $sqlOut = $sqlIn;
        
        $sessionName = $GLOBALS['pConfig']['appIdent'].'_prumoUserName';
        
        if (! isset($_SESSION[$sessionName])) {
            session_start();
        }
        
        if (isset($_SESSION[$sessionName])) {
            
            $prumoUser = $_SESSION[$sessionName];
            
            if ($prumoUser != '') {
                $prumoUser = pFormatSql($prumoUser, 'string');
                $sqlOut = str_replace(':prumoUser:', $prumoUser, $sqlOut);
                $sqlOut = str_replace(':new_prumoUser:', $prumoUser, $sqlOut);
                $sqlOut = str_replace(':old_prumoUser:', $prumoUser, $sqlOut);
            }
        }
        
        return $sqlOut;
    }
}

