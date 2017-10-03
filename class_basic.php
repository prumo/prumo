<?php

/* *********************************************************************
 *
 *	Prumo Framework para PHP é um framework vertical para
 *	desenvolvimento rápido de sistemas de informação web.
 *	Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *	e Odair Rubleski <orubleski@gmail.com>
 *
 *	This file is part of Prumo.
 *
 *	Prumo is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 3, or (at your option)
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

/**
 * Classe básica que contém a estrutura mais simples dos CRUD com os parâmetros básicos do objeto e os fields
 */
class prumoBasic {
	
	public $name;
	public $field = array();
	public $error = '';
	public $param;
	
	protected $pConnection;
	protected $ajaxFile;
	protected $ind = '';
	
	/**
	 * Construtor da classe prumoBasic
	 *
	 * @param $params string: parâmetros principais
	 */
	function __construct($params) {
		global $pConnection;
		require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection.php');
		
		$this->pConnection = $pConnection;
		
		$this->param = pParameters($params);
		$this->param['schema'] = isset($this->param['schema']) ? $this->param['schema'] : $GLOBALS['pConfig']['appSchema'];
		$this->ajaxFile = $this->getAjaxFileName();
		$this->name = isset($this->param['objname']) ? $this->param['objname'] : '';
	}
	
	/**
	 * Pega o nome do arquivo XML (controller da aplicação)
	 */
	private function getAjaxFileName() {
		
		$files = get_included_files();
		$lastInclusion = $files[count($files)-1];
		
		if (!isset($this->param['xmlfile'])) {
			if (dirname($_SERVER["SCRIPT_FILENAME"]) == dirname($lastInclusion)) {
				$this->param['xmlfile'] = basename($lastInclusion);
			}
			else {
				$this->param['xmlfile'] = $GLOBALS['pConfig']['appWebPath'] . str_replace($GLOBALS['pConfig']['appPath'], '', $lastInclusion);
			}
		}
		
		// transforma o caminho do arquivo ajax de relatovo para absoluto
		$ajaxFileName = substr($this->param['xmlfile'], 0, 1) == '/' ? $this->param['xmlfile'] : $GLOBALS['pConfig']['appWebPath'].'/'.$this->param['xmlfile'];
		
		return $ajaxFileName;
	}
	
	/**
	 * Retorna o nome da instância
	 *
	 * @return string
	 */
	public function getObjName() {
		
		if (!isset($this->name) or $this->name == '') {
			
			$className = get_class($this);
			$instance = array();
			
			foreach ($GLOBALS as $key => $value) {
				if (is_object($value) and get_class($value) == $className) {
					$instance[] = $key;
				}
			}
			
			$this->name = array_pop($instance);
		}
		
		return $this->name;
	}
	
	/**
	 * Quantidade de campos
	 *
	 * @return integer: quantidade de campos
	 */
	public function fieldCount() {
		return count($this->field);
	}
	
	/**
	 * Adiciona um campo
	 *
	 * @param $params array: array associativo com os parâmetros do campo
	 *
	 * @return array: campo formatado
	 */
	public function addField($params) {
		
		$this->getObjName();
	
		$param   = pParameters($params);
		$name    = $param['name'];
		$label   = isset($param['label']) ? $param['label'] : $param['name'];
		$size    = isset($param['size']) ? $param['size'] : null;
		$default = isset($param['default']) ? $param['default'] : null;
		$type    = isset($param['type']) ? $param['type'] : 'string';
		$notNull = (isset($param['notnull']) and $param['notnull']);
		$visible = (!isset($param['visible']) or $param['visible'] != 'false');
		
		$field = array(
		            'name' => $name,
		            'label' => $label,
		            'size' => $size,
		            'default' => $default,
		            'type' => $type,
		            'notnull' => $notNull,
		            'visible' => $visible
		         );
		
		if (isset($param['search'])) {
			$field['search'] = $param['search'];
		}
		
		$this->field[] = $field;
		
		return $field;
	}
	
	/**
	 * Pega um campo pelo nome
	 *
	 * @param $name string: nome do campo
	 *
	 * @return array: campo completo
	 */
	public function fieldByName($name) {
		
		for ($i=0; $i < $this->fieldCount(); $i++) {
			if ($this->field[$i]['name'] == $name) {
				return $this->field[$i];
			}
		}
		
		return null;
	}
	
	/**
	 * Adiciona um filtro no pFilter no lado do cliente (javascript)
	 *
	 * @param $fieldName string: nome do campo
	 * @param $filterOperator string: operador (verificar operadores do banco em class_pg_connection.php)
	 * @param $fieldValue string: valor
	 */
	public function setFilter($fieldName, $filterOperator, $fieldValue, $fieldValue2='') {
		echo '<script type="text/javascript">'."\n";
		echo "	".$this->name.".setFilter('$fieldName', '$filterOperator', '$fieldValue', '$fieldValue2');\n";
		echo '</script>'."\n";
	}
	
	/**
	 * Adiciona um filtro invisibel no pFilter no lado do cliente (javascript)
	 *
	 * @param $fieldName string: nome do campo
	 * @param $filterOperator string: operador (verificar operadores do banco em class_pg_connection.php)
	 * @param $fieldValue string: valor
	 */
	public function setInvisibleFilter($fieldName, $filterOperator, $fieldValue, $fieldValue2='') {
		echo '<script type="text/javascript">'."\n";
		echo "	".$this->name.".setInvisibleFilter('$fieldName', '$filterOperator', '$fieldValue', '$fieldValue2');\n";
		echo '</script>'."\n";
	}
	
	/**
	 * Seta a indentação para organizar o código gerado no lado do cliente
	 *
	 * @param $ind string: tabs para indentação no lado do cliente
	 */
	public function setIndentation($ind) {
		$this->ind = $ind;
	}
}

