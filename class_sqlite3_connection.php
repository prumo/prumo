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
 * prumoPgConnection faz a conexão com banco de dados SQLite3
 */
class prumoSqlite3Connection {
	
	private $connection;
	private $err;
	
	public $connected;
	public $param;
	public $sqlOperator;
	
	/**
	 * Construtor da classe prumoSqlite3Connection
	 *
	 * @param $params string: string de parametros (verificar o ctrl_connection.php para exemplo)
	 */
	function __construct($params) {
		$this->param = pParameters($params);
		$this->connected = false;
		$this->err = '';
		
		$this->sqlOperator = array();
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
	}
	
	/**
	 * Conecta no banco de dados SQLite3
	 *
	 * @return boolean: sucesso ou fracasso na conexão
	 */
	private function connect() {
		$dbName = $this->param['dbname'];
		
		if (!extension_loaded('sqlite3')) {
			$msg = _('É necessário ativar a extensão %extension% no PHP, ou alterar o banco de dados do framework para PostgreSQL editando o arquivo %file%.');
			$msg = str_replace('%extension%', 'sqlite3', $msg);
			$this->err = str_replace('%file%', $GLOBALS['pConfig']['appPath'].'/prumo.php', $msg);
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
			}
			else {
				$msg = _('O arquivo "%fileName%" não possui permissão de escrita');
				$this->err = str_replace('%fileName%', $dbName, $msg);
				return false;
			}
		}
		else {
			$msg = _('O arquivo "%fileName%" nao existe');
			$this->err = str_replace('%fileName%', $dbName, $msg);
			return false;
		}
		
		if ($exists and $this->connection) {
			$this->connected = true;
			$this->err = '';
			return true;
		}
		else {
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
	function getErr() {
		return $this->err;
	}
	
	/**
	 * Verifica se a conexão com o SQLite3 está estabelecida
	 *
	 * @param reconnect boolean: indica se deve tentar reconectar caso esteja desconectado
	 *
	 * @return boolean
	 */
	function connected($reconnect=false) {
		
		if ($reconnect and $this->connected == false) {
			$this->connect();
		}
		
		return $this->connected;
	}
	
	/**
	 * Retorna a conexão com o banco de dados SQLite3, se não estiver conectado conecta antes
	 *
	 * @return object
	 */
	function getConnection() {
		
		if ($this->connected) {
			return $this->connection;
		}
		else {
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
	function sqlQuery($sql) {
		
		if ($this->getConnection()) {
			$result = $this->connection->querySingle($sql);
			return $result;
		}
		else {
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
	function fetchAssoc($sql) {
		
		if ($this->getConnection()) {
			
			$res = $this->connection->query($sql);
			$row = $res->fetchArray(SQLITE3_ASSOC);
			
			return $row;
		}
		else {
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
	function sql2Array($sql) {
		
		$connection = $this->getConnection();
		if (!$connection) {
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
	 */
	function sqlXml($sql, $tableName) {
		
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
				
				$xml .= $fieldValue == '' ? "	<$fieldName>NULL</$fieldName>\n" : "	<$fieldName>$fieldValue</$fieldName>\n";
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
	function dbType($type) {
		
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
	function disconnect() {
		
		if ($this->connection) {
			$this->connection->close();
		}
		
		$this->connected = false;
	}
}
