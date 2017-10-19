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
    function __construct($params)
    {
        $this->param = pParameters($params);
        $this->connected = false;
        $this->err = '';
        
        $this->sqlOperator = array();
        $this->sqlOperator['like']                  = ':field: ilike \'%:value:%\'';
        $this->sqlOperator['not like']              = 'NOT :field: ilike \'%:value:%\'';
        $this->sqlOperator['begins with']           = ':field: ilike \':value:%\'';
        $this->sqlOperator['ends with']             = ':field: ilike \'%:value:\'';
        $this->sqlOperator['not begins with']       = 'NOT :field: ilike \':value:%\'';
        $this->sqlOperator['not ends with']         = 'NOT :field: ilike \'%:value:\'';
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
     * @return boolean: sucesso ou fracasso na conexão
     */
    private function connect()
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
        $this->connection = @pg_connect($pgString) ;
        
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
     * @param reconnect boolean: indica se deve tentar reconectar caso esteja desconectado
     *
     * @return boolean
     */
    public function connected($reconnect=false)
    {
        if ($reconnect and $this->connected == false) {
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
     * @return boolean false: em caso de falha na consulta SQL
     */
    public function sqlQuery($sql)
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
     * @return boolean false: em caso de falha na consulta SQL
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
     * @return boolean false: em caso de falha na consulta SQL
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
     * @return boolean: false em caso de falha
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
    public function dbType($type)
    {
        $types = array(
            'string'    => 'character varying',
            'text'      => 'text',
            'integer'   => 'integer',
            'serial'    => 'serial',
            'numeric'   => 'numeric',
            'date'      => 'date',
            'time'      => 'time',
            'timestamp' => 'timestamp without timezone',
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
