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
 * PrumoPgConnection faz a conexão com banco de dados PostgreSQL
 */
class PrumoPgConnection
{
    private $connection;
    private $err;
    
    public $connected;
    public $param;
    public $sqlOperator;
    
    /**
     * Construtor da classe PrumoPgConnection
     *
     * @param $params string: string de parametros (verificar o ctrl_connection.php para exemplo)
     */
    function __construct(string $params)
    {
        $this->param = pParameters($params);
        $this->connected = false;
        $this->err = '';
        
        $this->sqlOperator = array();
        
        global $pConfig;
        if ($pConfig['useUnaccent'] == 't') {
            $this->sqlOperator['like']                  = 'unaccent(:field:) ilike unaccent(\'%:value:%\')';
            $this->sqlOperator['not like']              = 'NOT unaccent(:field:) ilike unaccent(\'%:value:%\')';
            $this->sqlOperator['begins with']           = 'unaccent(:field:) ilike unaccent(\':value:%\')';
            $this->sqlOperator['ends with']             = 'unaccent(:field:) ilike unaccent(\'%:value:\')';
            $this->sqlOperator['not begins with']       = 'NOT unaccent(:field:) ilike unaccent(\':value:%\')';
            $this->sqlOperator['not ends with']         = 'NOT unaccent(:field:) ilike unaccent(\'%:value:\')';
        } else {
            $this->sqlOperator['like']                  = ':field: ilike \'%:value:%\'';
            $this->sqlOperator['not like']              = 'NOT :field: ilike \'%:value:%\'';
            $this->sqlOperator['begins with']           = ':field: ilike \':value:%\'';
            $this->sqlOperator['ends with']             = ':field: ilike \'%:value:\'';
            $this->sqlOperator['not begins with']       = 'NOT :field: ilike \':value:%\'';
            $this->sqlOperator['not ends with']         = 'NOT :field: ilike \'%:value:\'';
        }
        if ($pConfig['useSimilaritySearch'] == 't') {
            $this->sqlOperator['similarity'] = 'similarity(:field:, \'%:value:\') > ' . $pConfig['similarityThreshold'];
        }
        $this->sqlOperator['equal']                 = ':field: = \':value:\'';
        $this->sqlOperator['not equal']             = 'NOT :field: = \':value:\'';
        $this->sqlOperator['numeric equal']         = ':field: = :value:';
        $this->sqlOperator['numeric not equal']     = 'NOT :field: = :value:';
        $this->sqlOperator['less than']             = ':field: < :value:';
        $this->sqlOperator['greater than']          = ':field: > :value:';
        $this->sqlOperator['less than or equal']    = ':field: <= :value:';
        $this->sqlOperator['greater than or equal'] = ':field: >= :value:';
        $this->sqlOperator['between']               = ':field: BETWEEN :value: AND :value2:';
        $this->sqlOperator['is null']               = ':field: IS NULL';
        $this->sqlOperator['not is null']           = 'NOT :field: IS NULL';
    }
    
    /**
     * Conecta no servidor de banco de dados PostgreSQL
     *
     * @return bool: sucesso ou fracasso na conexão
     */
    private function connect() : bool
    {
        $dbHost     = $this->param['dbhost'];
        $dbPort     = $this->param['dbport'];
        $dbName     = $this->param['dbname'];
        $dbUserName = $this->param['dbusername'];
        $dbPassword = $this->param['dbpassword'];
        
        if (! extension_loaded('pgsql')) {
            
            $msg = _('É necessário ativar a extensão %extension% no PHP');
            $this->err = str_replace('%extension%','pgsql',$msg);
            $this->connected = false;
            
            return false;
        }
        
        if ($this->connected()) {
            $this->disconnect();
        }
        
        $pgString = "host=$dbHost port=$dbPort dbname=$dbName user=$dbUserName password=$dbPassword";
        $this->connection = pg_connect($pgString) ;
        
        if ($this->connection) {
            
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
     * Retorna o array de erros da conexão com o PostgreSQL
     *
     * @return array
     */
    public function getErr()
    {
        return $this->err;
    }
    
    /**
     * Verifica se a conexão com o PostgreSQL está estabelecida
     *
     * @param reconnect bool: indica se deve tentar reconectar caso esteja desconectado
     *
     * @return bool
     */
    public function connected(bool $reconnect=false) : bool
    {
        if ($reconnect && $this->connected == false) {
            $this->connect();
        }
        
        return $this->connected;
    }
    
    /**
     * Retorna a conexão com o banco de dados PostgreSQL, se não estiver conectado conecta antes
     *
     * @return object
     */
    public function getConnection()
    {
        if ($this->connected) {
            return $this->connection;
        } else {
            $this->connect();
            return $this->connection;
        }
    }
    
    /**
     * Executa uma consulta no banco de dados
     *
     * @param $sql string: consulta SQL
     *
     * @return mixed: apenas o primeiro valor da primeira coluna retornada pela consulta SQL
     * @return bool false: em caso de falha na consulta SQL
     */
    public function sqlQuery(string $sql)
    {
        if ($this->getConnection()) {
            
            $res = pg_query($this->getConnection(), $sql);
            
            if ($res === false) {
                $this->err = pg_last_error($this->connection);
                return false;
            }
            
            $row = pg_fetch_row($res);
            
            if ($row === false && ($error = pg_last_error($this->connection))) {
                $this->err = $error;
                return false;
            }
            
            return $row[0];
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
     * @return bool false: em caso de falha na consulta SQL
     */
    public function fetchAssoc($sql)
    {
        if ($this->getConnection()) {
            
            $res = pg_query($this->getConnection(), $sql);
            
            if ($res === false) {
                $this->err = pg_last_error($this->connection);
                return false;
            }
            
            $row = pg_fetch_assoc($res);
            
            if ($row === false && ($error = pg_last_error($this->connection))) {
                $this->err = $error;
                return false;
            }
            
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
     * @return bool false: em caso de falha na consulta SQL
     */
    public function sql2Array($sql)
    {
        $connection = $this->getConnection();
        
        if (! $connection) {
            return false;
        }
        
        $res = pg_query($connection,$sql);
        
        if ($res === false) {
            $this->err = pg_last_error($this->connection);
            return false;
        }
        
        $ncols = pg_num_fields($res);

        $arrayRerurn = array();
        $i = 0;
        
        while ($row = pg_fetch_assoc($res)) {
            
            $thisRow = array();
            
            for ($j = 0; $j < $ncols; ++$j) {
                
                $fieldName = pg_field_name($res, $j);
                $fieldValue = pg_fetch_result($res, $i, $fieldName);
                
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
     * @return string: xml com os dados retornados pela consulta SQL
     * @return bool: false em caso de falha
     */
    public function sqlXml($sql, $tableName)
    {
        $res = pg_query($this->getConnection(), $sql);
        
        if ($res === false) {
            $this->err = pg_last_error($this->connection);
            return false;
        }
        
        $xmlTableName = $tableName == '' ? pg_field_table($res, 0) : $tableName;
        
        $ncols = pg_num_fields($res);
        $xml = '';
        
        $i = 0;
        while ($row = pg_fetch_assoc($res)) {
            
            $xml .= "<$xmlTableName>\n";
            
            for ($j = 0; $j < $ncols; ++$j) {
                
                $fieldName = pg_field_name($res, $j);
                $fieldValue = htmlspecialchars(pg_fetch_result($res, $i, $fieldName));
                
                //transforma quebra de linha em marcação de quebra para ser interpretada pelo cliente
                $fieldValue = str_replace("\r", '', $fieldValue);
                $fieldValue = str_replace("\n", '\n', $fieldValue);
                
                $xml .= $fieldValue == '' ? "    <$fieldName>NULL</$fieldName>\n" : "    <$fieldName>$fieldValue</$fieldName>\n";
            }
            
            $xml .= "</$xmlTableName>\n";
            $i++;
        }
        
        return $xml;
    }
    
    /**
     * Converte um determinado tipo no formato do PostgreSQL, ex: 'string' => 'character varying'
     *
     * @type string: tipo de dado
     *
     * @return string: tipo de dados de acordo com o PostgreSQL
     */
    public function dbType(string $type)
    {
        $types = array(
            'string'    => 'character varying',
            'text'      => 'text',
            'integer'   => 'integer',
            'serial'    => 'serial',
            'numeric'   => 'numeric',
            'date'      => 'date',
            'time'      => 'time',
            'timestamp' => 'timestamp with timezone',
            'boolean'   => 'boolean'
        );
        
        return isset($types[$type]) ? $types[$type] : $type;
    }
    
    /**
     * Fecha a conexão com o PostgreSQL
     */
    public function disconnect()
    {
        if ($this->connection) {
            pg_close($this->connection);
        }
        
        $this->connected = false;
    }
}
