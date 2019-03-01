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
 * PrumoPgConnection faz a conexão com banco de dados SQLite3
 */
class PrumoSqlite3Connection
{
    private $connection;
    private $err;
    
    public $connected;
    public $param;
    public $sqlOperator;
    
    /**
     * Construtor da classe PrumoSqlite3Connection
     *
     * @param $params string: string de parametros (verificar o ctrl_connection.php para exemplo)
     */
    function __construct(string $params)
    {
        $this->param = pParameters($params);
        $this->connected = false;
        $this->err = '';
        
        $this->sqlOperator = array();
        if ($GLOBALS['pConfig']['useSimilaritySearch'] == 't') {
            $this->sqlOperator['similarity'] = 'similarity(:field:, \'%:value:\') > ' . $GLOBALS['pConfig']['similarityThreshold'];
        }
        $this->sqlOperator['like']                  = ':field: like \'%:value:%\'';
        $this->sqlOperator['not like']              = 'NOT :field: like \'%:value:%\'';
        $this->sqlOperator['begins with']           = ':field: like \':value:%\'';
        $this->sqlOperator['ends with']             = ':field: like \'%:value:\'';
        $this->sqlOperator['not begins with']       = 'NOT :field: like \':value:%\'';
        $this->sqlOperator['not ends with']         = 'NOT :field: like \'%:value:\'';
        $this->sqlOperator['equal']                 = ':field: = \':value:\'';
        $this->sqlOperator['not equal']             = 'NOT :field: = \':value:\'';
        $this->sqlOperator['numeric equal']         = ':field: = :value:';
        $this->sqlOperator['numeric not equal']     = 'NOT :field: = :value:';
        $this->sqlOperator['less than']             = ':field: < \':value:\'';
        $this->sqlOperator['greater than']          = ':field: > \':value:\'';
        $this->sqlOperator['less than or equal']    = ':field: <= \':value:\'';
        $this->sqlOperator['greater than or equal'] = ':field: >= \':value:\'';
        $this->sqlOperator['is null']               = ':field: IS NULL';
        $this->sqlOperator['not is null']           = 'NOT :field: IS NULL';
        $this->sqlOperator['date_time equal']                 = ':field: = \':value:\'';
        $this->sqlOperator['date_time not equal']             = 'NOT :field: = \':value:\'';
        $this->sqlOperator['date_time less than']             = ':field: < \':value:\'';
        $this->sqlOperator['date_time greater than']          = ':field: > \':value:\'';
        $this->sqlOperator['date_time less than or equal']    = ':field: <= \':value:\'';
        $this->sqlOperator['date_time greater than or equal'] = ':field: >= \':value:\'';
        $this->sqlOperator['date_time between']               = ':field: BETWEEN \':value:\' AND \':value2:\'';
    }
    
    /**
     * Conecta no banco de dados SQLite3
     *
     * @return boolean: sucesso ou fracasso na conexão
     */
    private function connect() : bool
    {
        $dbName = $this->param['dbname'];
        
        if (! extension_loaded('sqlite3')) {
            $msg = _('É necessário ativar a extensão %extension% no PHP, ou alterar o banco de dados do framework para PostgreSQL editando o arquivo %file%.');
            $msg = str_replace('%extension%', 'sqlite3', $msg);
            $this->err = str_replace('%file%', dirname(__DIR__).'/prumo.php', $msg);
            $this->connected = false;
            return false;
        }
        
        if ($this->connected()) {
            $this->disconnect();
        }
        
        $exists = file_exists($dbName);
        if ($exists) {
            if (is_writable($dbName)) {
                $this->connection = new SQLite3($dbName);
            } else {
                $msg = _('O arquivo "%fileName%" não possui permissão de escrita');
                $this->err = str_replace('%fileName%', $dbName, $msg);
                return false;
            }
        } else {
            $msg = _('O arquivo "%fileName%" nao existe');
            $this->err = str_replace('%fileName%', $dbName, $msg);
            return false;
        }
        
        if ($exists && $this->connection) {
            $this->connected = true;
            $this->err = '';
            return true;
        } else {
            $this->connected = false;
            $err = _('Problema de conectividade com o servidor de banco de dados');
            $this->err = str_replace(':db_name:', $dbName, $err);
            return false;
        }
    }
    
    /**
     * Retorna o array de erros da conexão com o SQLite3
     *
     * @return array
     */
    function getErr() : string
    {
        return $this->err;
    }
    
    /**
     * Verifica se a conexão com o SQLite3 está estabelecida
     *
     * @param reconnect boolean: indica se deve tentar reconectar caso esteja desconectado
     *
     * @return boolean
     */
    function connected($reconnect=false)
    {
        if ($reconnect && $this->connected == false) {
            $this->connect();
        }
        
        return $this->connected;
    }
    
    /**
     * Retorna a conexão com o banco de dados SQLite3, se não estiver conectado conecta antes
     *
     * @return object
     */
    function getConnection()
    {
        if ($this->connected) {
            return $this->connection;
        } else {
            return $this->connect() ? $this->connection : false;
        }
    }
    
    /**
     * Executa uma consulta no banco de dados
     *
     * @param $sql string: consulta SQL
     *
     * @return mixed: apenas o primeiro valor da primeira coluna retornada pela consulta SQL
     * @return boolean false: em caso de falha na consulta SQL
     */
    function sqlQuery(string $sql)
    {
        if ($this->getConnection()) {
            $result = $this->connection->querySingle($sql);
            return $result;
        } else {
            return false;
        }
    }
    
    /**
     * Executa uma consulta no banco de dados e retorna em um array associativo (um único registro sem índice)
     *
     * @param $sql string: consulta SQL
     *
     * @return array: array associativo com o registro retornado pela consulta SQL sendo o nome da coluna a chave do array
     * @return boolean false: em caso de falha na consulta SQL
     */
    function fetchAssoc(string $sql)
    {
        if ($this->getConnection()) {
            
            $res = $this->connection->query($sql);
            $row = $res->fetchArray(SQLITE3_ASSOC);
            
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Executa uma consulta no banco de dados e retorna em um array associativo com vários registros (semelhante a fetchAssoc)
     *
     * @param $sql string: consulta SQL
     *
     * @return array: array associativo com os registros retornados pela consulta SQL sendo o nome da coluna a chave do array
     * @return boolean false: em caso de falha na consulta SQL
     */
    function sql2Array(string $sql)
    {
        $connection = $this->getConnection();
        if (! $connection) {
            return false;
        }
        
        $res = $this->connection->query($sql);
        $ncols = $res->numColumns();

        $arrayRerurn = array();
        $i = 0;
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            
            $thisRow = array();
            
            for ($j = 0; $j < $ncols; ++$j) {
                
                $fieldName = $res->columnName($j);
                $fieldValue = $row[$fieldName];
                
                $thisRow[$fieldName] = $fieldValue;                        
            }
            
            $arrayRerurn[$i] = $thisRow;
            $i++;
        }
        
        return $arrayRerurn;
    }
    
    /**
     * Retorna o resultado de uma consulta SQL em formato XML
     *
     * @param $sql string: consulta SQL
     * @param $tableName string: nome da tag container
     *
     * @param string: xml com os dados retornados pela consulta SQL
     * @return bool: false em caso de falha
     */
    function sqlXml(string $sql, string $tableName)
    {
        $connection = $this->getConnection();
        $res = $this->connection->query($sql);
        
        $xmlTableName = $tableName == '' ? 'name_less_table' : $tableName;
        
        $ncols = $res->numColumns();
        $xml = '';
        
        $i = 0;
        
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            
            $xml .= "<$xmlTableName>\n";
            
            for ($j = 0; $j < $ncols; ++$j) {
                
                $fieldName = $res->columnName($j);
                $fieldValue = htmlspecialchars($row[$fieldName]);
                
                //transforma quebra de linha em marcação de quebra para ser interpretada pelo cliente
                $fieldValue = str_replace("\r",'',$fieldValue);
                $fieldValue = str_replace("\n",'\n',$fieldValue);
                
                $xml .= $fieldValue == '' ? "    <$fieldName>NULL</$fieldName>\n" : "    <$fieldName>$fieldValue</$fieldName>\n";
            }
            
            $xml .= "</$xmlTableName>\n";
            $i++;
        }
        
        return $xml;
    }
    
    /**
     * Converte um determinado tipo no formato do SQLite3, ex: 'string' => 'text'
     *
     * @type string: tipo de dado
     *
     * @return string: tipo de dados de acordo com o SQLite3
     */
    function dbType(string $type) : string
    {
        $types = array(
            'string'    => 'text',
            'text'      => 'text',
            'integer'   => 'integer',
            'serial'    => 'integer',
            'numeric'   => 'numeric',
            'date'      => 'date',
            'time'      => 'time',
            'timestamp' => 'timestamp',
            'boolean'   => 'boolean'
        );
        
        return isset($types[$type]) ? $types[$type] : $type;
    }
    
    /**
     * Fecha a conexão com o SQLite3
     */
    function disconnect()
    {
        if ($this->connection) {
            $this->connection->close();
        }
        
        $this->connected = false;
    }
}
