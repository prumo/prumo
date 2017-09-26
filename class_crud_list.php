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

class prumoCrudList extends prumoBasic {
	
	private $pGrid;
	private $fieldReturn;
	private $indentation;
	public $pConnection;
	public $pFilter;
	private $fixedSqlSearch;
	private $constructedGrid;
	private $orderby;
	
	public $page;
	
	function __construct($params) {
		
		parent::__construct($params);
		$this->constructedGrid = false;
		
		if (!isset($this->param['xmlfile'])) {
			pError(_('Propriedade xmlFile não especificada para objeto prumoSearch'), 'js');
		}
		
		$this->page = 1;
		$this->fieldReturn = array();
		
		$this->orderby = '';
	}
	
	/**
	 * Seta a indentação para organizar o código gerado no lado do cliente
	 *
	 * @param $indentation string: tabs para indentação no lado do cliente
	 */
	public function setIndentation($indentation) {
		
		$this->indentation = $indentation;
		$this->pGrid->indentation = $indentation . '		';
	}
	
	/**
	 * Desenha o GRID
     */
	protected function constructGrid() {
		
		$this->getObjName();
		$lines = $this->pageLines();
		
		if (isset($this->param['fastcreate']) and $this->param['fastcreate']) {
			$lines++;
		}
		
		$this->pGrid = new prumoGrid($this->name, $lines);
		$this->pGrid->indentation = $this->indentation . '		';
		$this->pGrid->width = '98%';
		$this->pGrid->lineEventOnData = $this->name.'.lineClick(%)';
		$this->pGrid->pointerCursorOnData = true;
	}
	
	/**
	 * Decide a quantidade de linhas do grid de acordo com os parametros e arquivo de configuração do framework
	 *
	 * @return integer: número de linhas do grid
	 */
	protected function pageLines() {
		
		$lines = isset($this->param['pagelines']) ? $this->param['pagelines']: $GLOBALS['pConfig']['searchLines'];
		
		return $lines;
	}
	
	/**
	 * Adiciona um campo
	 *
	 * @param $params string: string de configuração do campo no formato do framework
	 */
	public function addField($params) {
		
		if ($this->constructedGrid == false) {
			
			$this->constructedGrid = true;
			
			if (!isset($this->param['tablename'])) {
				$this->param['tablename'] = $this->name;
			}
			
			$this->constructGrid();
		}
		
		parent::addField($params);
		
		$this->pGrid->addColumn($params);
		$this->pFilter = new prumoFilter($this->name, $this->field, '');
		$this->pFilter->setIndentation($this->indentation.'		');
		
		$param = pParameters($params);
		
		$this->field[count($this->field)-1]['sqlname'] = isset($param['sqlname']) ? $param['sqlname'] : $param['name'];
		$this->pFilter->permC = (isset($this->param['permc']) and $this->param['permc'] == 'false') ? false : true;
	}
	
	/**
	 * Adiciona um campo onde o registro escolhido deve ser retornado
	 *
	 * @param $fieldName string: nome do campo
	 * @param $idReturn string: id do input html. Quando não informado, copia do $fieldName
	 * @param $verbose boolean: indica se deve dar eco no código javascript gerado
	 * @param $noRetrieve boolean: quando true não participa do retrieve (busca implicita disparada pelo crud pai)
	 *
	 * @return string: código javascript gerado
	 */
	public function addFieldReturn($fieldName, $idReturn='', $verbose=true) {
		
		if (empty($idReturn)) {
			$idReturn = $fieldName;
		}
		
		// valida duplicidade
		for ($i=0; $i < count($this->fieldReturn); $i++) {
		    if ($this->fieldReturn[$i][0] == $fieldName) {
		    	$msg = _('Campo ":fieldName:" duplicado em :objName:->addFieldReturn.');
		    	$msg = str_replace(':fieldName:', $fieldName, $msg);
		    	$msg = str_replace(':objName:', $this->name, $msg);
		    	throw new Exception($msg);
		    }
		}
		
		$this->fieldReturn[] = array($fieldName, $idReturn);
		
		$field = $this->fieldByName($fieldName);
		$fieldType = $field['type'];
		
		$fieldReturn  = $this->indentation.'<script type="text/javascript">'."\n";
		$fieldReturn .= $this->indentation. '	'.$this->name.'.addFieldReturn(\''.$fieldName.'\',\''.$idReturn.'\',\''.$fieldType.'\''.");\n";
		$fieldReturn .= $this->indentation.'</script>'."\n";
		
		if ($verbose) {
			echo $fieldReturn;
		}
		
		return $fieldReturn;
	}
	
	/**
	 * Instancia e configura o objeto javascript no lado do cliente
	 *
	 * @return string: javascript pronto
	 */
	private function initClientObject() {
		
		// trata o caminho do xmlFile (relativo e absoluto)
		$ajaxFile = substr($this->param['xmlfile'], 0, 1) == '/' ? $this->param['xmlfile'] : $GLOBALS['pConfig']['appWebPath'].'/'.$this->param['xmlfile'];
		
		// instancia o objeto prumoCrudList no cliente
		$clientObject  = $this->indentation. '<script type="text/javascript">'."\n";
		$clientObject .= $this->indentation. '	'.$this->name.' = new prumoCrudList(\''.$this->name.'\',\''.$ajaxFile.'\');'."\n";
		$clientObject .= $this->indentation. '	'.$this->name.'.objName = \''.$this->name.'\';'."\n";
		$clientObject .= $this->indentation. '	'.$this->name.'.parent = '.$this->param['crudname'].';'."\n";
		
		// repassa condicionalmente o pog debug para o objeto ajax
		if (isset($this->param['debug']) and $this->param['debug']) {
			$clientObject .= $this->indentation. '	'.$this->name.'.pAjax.debug = true;'."\n";
		}
		
		// repassa parametro auto click
		$clientObject .= $this->indentation . '	'. $this->name.'.autoClick = ';
		$clientObject .= (isset($this->param['autoclick']) and $this->param['autoclick']) ? 'true;'."\n" : 'false;'."\n";
		
		//fastCreate
		if (isset($this->param['fastcreate']) and $this->param['fastcreate']) {
			$clientObject .= $this->indentation . '	'. $this->name.'.fastCreate = true;'."\n";
		}
		
		//fastUpdate
		if (isset($this->param['fastupdate']) and $this->param['fastupdate']) {
			$clientObject .= $this->indentation . '	'. $this->name.'.fastUpdate = true;'."\n";
		}
		
		//fastDelete
		if (isset($this->param['fastdelete']) and $this->param['fastdelete']) {
			$clientObject .= $this->indentation . '	'. $this->name.'.fastDelete = true;'."\n";
		}
		
		$clientObject .= $this->indentation. '</script>'."\n";
		
		return $clientObject;
	}
	
	/**
	 * Gera o código HTML dos filtros
	 *
	 * @return string: código HTML dos filtros
	 */
	protected function makeFilters() {
		
		$useBtNew = (isset($this->param['routine']) && !empty($this->param['routine'])) ? pPermitted($this->param['routine'], 'c') : true;
		if ($useBtNew) {
			$this->pFilter->btNew = $this->param['crudname'].'.bt_new()';
		}
		$htmlFilters = $this->pFilter->draw(false);
		
		// vinculo com o prumoFilter
		$htmlFilters .= $this->indentation. '		<script type="text/javascript">'."\n";
		$htmlFilters .= $this->indentation. '			'.$this->name.'.pFilter = pFilter_'.$this->name.";\n";
		$htmlFilters .= $this->indentation. '		</script>'."\n";
		
		return $htmlFilters;
	}
	
	/**
	 * Gera o código HTML do grid
	 *
	 * @return string: código HTML do grid
	 */
	protected function makeGrid() {
		
		$htmlGrid = $this->pGrid->draw(false);
		
		// passa informação dos fields do servidor para o grid
		$htmlGrid .= $this->indentation.'		<script type="text/javascript">'."\n";
		
		$htmlGrid .= $this->indentation. '			pGrid_'.$this->name.'.field = new Array(';
		for ($i=0; $i < $this->fieldCount(); $i++) {
			$htmlGrid .= '"'.$this->field[$i]['name'].'"';
			if ($i < $this->fieldCount() -1) {
				$htmlGrid .= ',';
			}
		}
		$htmlGrid .= ');'."\n";
		$htmlGrid .= $this->indentation. '			pGrid_'.$this->name.'.fieldType = new Array(';
		for ($i=0; $i < $this->fieldCount(); $i++) {
			$htmlGrid .= '"'.$this->field[$i]['type'].'"';
			if ($i < $this->fieldCount() -1) {
				$htmlGrid .= ',';
			}
		}
		$htmlGrid .= ');'."\n";
		$htmlGrid .= $this->indentation. '			pGrid_'.$this->name.'.fieldVisible = new Array(';
		for ($i=0; $i < $this->fieldCount(); $i++) {
			$fieldVisible = $this->field[$i]['visible'] == true ? 'true' : 'false';
			$htmlGrid .= $fieldVisible;
			if ($i < $this->fieldCount() -1) {
				$htmlGrid .= ',';
			}
		}
		$htmlGrid .= ');'."\n";
		
		$htmlGrid .= $this->indentation. '			pGrid_'.$this->name.'.xmlIdentification = \''.$this->name.'\';'."\n";
		$htmlGrid .= $this->indentation. '			pGrid_'.$this->name.'.lineEventOnData = \''.$this->pGrid->lineEventOnData.'\';'."\n";
		if ($this->pGrid->pointerCursorOnData) {
			$htmlGrid .= $this->indentation. '			pGrid_'.$this->name.'.pointerCursorOnData = true;'."\n";
		}
		
		// vinculo com o prumoGrid
		$htmlGrid .= $this->indentation. '			'.$this->name.'.pGrid = pGrid_'.$this->name.";\n";
		
		$htmlGrid .= $this->indentation.'		</script>'."\n";
		
		return $htmlGrid;
	}
	
	/**
	 * Gera o código HTML da barra de navegação
	 *
	 * @return string: código HTML da barra de navegação
	 */
	protected function makeGridNavigation() {
		
		$htmlGridNavigation = $this->indentation.'		<div id="pGridNavigation_'.$this->name.'" class="prumoGridNavigation"></div>'."\n";
		$htmlGridNavigation .= $this->indentation.'		<br />'."\n";
		$htmlGridNavigation .= $this->indentation.'		<script type="text/javascript">'."\n";
		$htmlGridNavigation .= $this->indentation.'			pGridNavigation_'.$this->name.' = new prumoGridNavigation(\''.$this->name.'\');'."\n";
		
		// vinculo com o prumoGridNavigation
		$htmlGridNavigation .= $this->indentation.'			'.$this->name.'.pGridNavigation = pGridNavigation_'.$this->name.';'."\n";

		$htmlGridNavigation .= $this->indentation.'		</script>'."\n";
		
		return $htmlGridNavigation;
	}
	
	/**
	 * Cria o código JS que associa o cridList a CRUD
	 *
	 * @return string: código JS
	 */
	private function makeCrudLink() {
		
		$htmlCrudLink  = $this->indentation.'		<script type="text/javascript">'."\n";
		$htmlCrudLink .= $this->indentation.'			'.$this->param['crudname'].'.pCrudList = '.$this->name.';'."\n";
		$htmlCrudLink .= $this->indentation.'		</script>'."\n";
		
		return $htmlCrudLink;
	}
	
	/**
	 * Gera o código HTML completo
	 *
	 * @param verbose boolean: quando true imprime o código
	 *
	 * @return string: código gerado
	 */
	public function draw($verbose) {
		
		require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php');
		
		// adiciona uma columa a mais para os controles do fastCreate, fastupdate ou fastdelete
		if (
			(isset($this->param['fastcreate']) and $this->param['fastcreate']) or
			(isset($this->param['fastupdate']) and $this->param['fastupdate']) or
			(isset($this->param['fastdelete']) and $this->param['fastdelete'])
		) {
			$this->pGrid->addColumn('name=prumoControls,label=,align=center');
		}
		
		// junta os objetos
		$pCrudListInit = $this->initClientObject();

		$pCrudList  = $this->makeFilters();
		$pCrudList .= $this->makeGrid();
		$pCrudList .= $this->makeGridNavigation();
		$pCrudList .= $this->makeCrudLink();
		
		$pCrudList = $pCrudListInit . $pCrudList;
		
		if ($verbose) {
			echo $pCrudList;
		}
		
		return $pCrudList;
	}
	
	/**
	 * Gera código SQL da condição de pesquisa
	 *
	 * @return string: código SQL
	 */
	public function sqlCondition() {
		
		$fieldName = $this->pFilter->filter['fieldName'];
		$operator  = $this->pFilter->filter['operator'];
		$value     = $this->pFilter->filter['value'];
		$value2    = $this->pFilter->filter['value2'];
		
		$arrCondition = array();
		$iValue = 0;
		for ($i = 0; $i < count($fieldName); $i++) {
			if ($value[$i] != '' or $operator[$i] == 'is null' or $operator[$i] == 'not is null') {
				$field = $this->fieldByName($fieldName[$i]);
				$condition = $this->pConnection->getSqlOperator($operator[$i]);
				$condition = str_replace(':field:', $field['sqlname'], $condition);
				$condition = str_replace(':value:', pFormatSql($value[$i], $field['type'], false, false), $condition);
				$condition = str_replace(':value2:', pFormatSql($value2[$i], $field['type'], false, false), $condition);
				$arrCondition[$iValue] = $condition;
				$iValue++;
			}
		}
		
		$conditionOut = '';
		for ($i = 0; $i < count($arrCondition); $i++) {
			$conditionOut .= $i == 0 ? ' WHERE '.$arrCondition[$i] : ' AND '.$arrCondition[$i];
		}
		
		return $conditionOut;
	}
	
	/**
	 * Define o campo de ordenação da consulta
	 *
	 * @param $orderby string: nome do campo de ordenação
	 */
	public function setOrderby($orderby) {
		$this->orderby = pFormatSql($orderby, 'string', false, false);
	}
	
	/**
	 * Gera o código SQL da ordenação da consulta
	 *
	 * @return string: SQL da ordenação da consulta
	 */
	public function sqlOrderby() {
		
		$fieldName = $this->pFilter->filter['fieldName'];
		$visible   = $this->pFilter->filter['visible'];
		
		$orderbyOut = '';
		$iVisible = 0;
		for ($i = 0; $i < count($fieldName); $i++) {
			
			$field = $this->fieldByName($fieldName[$i]);
			if ($visible[$i]) {
				$orderbyOut .= $iVisible == 0 ? $field['sqlname'] : ','.$field['sqlname'];
				$iVisible++;
			}
		}
		
		$orderbyOut = empty($this->orderby) ? ' ORDER BY ' .$orderbyOut : ' ORDER BY ' .$this->orderby;
		
		return $orderbyOut;
	}
	
	/**
	 * Gera o código SQL da consulta completo
	 *
	 * return string: código SQL
	 */
	public function sqlSearch() {
		
		$offsetNum = ($this->page - 1) * $this->pageLines();

		$tableName = $this->param['tablename'];

		$fields = '';
		for ($i=0; $i < $this->fieldCount(); $i++) {
			
			if ($fields != '') {
				$fields .= ',';
			}
			
			$fields .= $this->field[$i]['name'];
		}
		
		$offset = ' OFFSET '.$offsetNum;
		$limit = ' LIMIT '.$this->pageLines();
		$orderby = $this->sqlOrderby();
		$condition = $this->sqlCondition();
		
		if ($this->fixedSqlSearch != '') {
			$sqlSearch = 'SELECT '.$fields.' FROM ('.$this->fixedSqlSearch.') fixed '.$condition.$orderby.$limit.$offset.';';
		}
		else {
			$sqlSearch = 'SELECT '.$fields.' FROM '.$this->pConnection->getSchema($this->param['schema']).$tableName.$condition.$orderby.$limit.$offset.';';
		}
		
		return $sqlSearch;
	}
	
	/**
	 * Define o SQL para a consulta em substituiçao ao SQL gerado automaticamente
	 *
	 * @param $sql string: consulta SQL
	 */
	public function setSqlSearch($sql) {
		
		$this->fixedSqlSearch = $sql;
		
		for ($i=0; $i < $this->fieldCount(); $i++) {
			$this->field[$i]['sqlname'] = 'fixed.'.$this->field[$i]['sqlname'];
		}
	}
	
	/**
	 * Permite ao desenvolvedor da aplicação explicitar qual prumoConnection usar
	 *
	 * @param $connecion object: conexão com o banco de dados
	 */
	public function setConnection($connecion) {
		$this->pConnection = $connecion;
	}
	
	/**
	 * Gera o comando SQL que conta os registro
	 *
	 * @return string: comando SQL
	 */
	public function sqlCount() {
		
		$tableName = $this->param['tablename'];

		$condition = $this->sqlCondition();

		if ($this->fixedSqlSearch != '') {
			$sqlCount = 'SELECT count(*) FROM ('.$this->fixedSqlSearch.') fixed '.$condition.';';
		}
		else {
			$sqlCount = 'SELECT count(*) FROM '.$this->pConnection->getSchema($this->param['schema']).$tableName.$condition.';';
		}
		
		return $sqlCount;
	}
	
	/**
	 * Gera o XML completo
	 *
	 * @param $verbose boolean: quando true imprime o XML
	 *
	 * @return string: XML completo
	 */
	public function makeXml($verbose) {
		global $prumoGlobal;
		
		if ($prumoGlobal['currentUser'] == '') {
			$xml = pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
		}
		else {
			
			$this->page = $_POST['page'];
			if (isset($_POST['orderBy'])) {
			    $this->setOrderby($_POST['orderBy']);
			}
			$this->pFilter->loadQuery();
			
			$count = $this->pConnection->sqlQuery($this->sqlCount());
			if ($count === false) {
				pXmlError('SqlError', $this->pConnection->getErr(), true);
				exit;
			}
			
			$xml = $this->pConnection->sqlXml($this->sqlSearch(), $this->name);
			if ($xml === false) {
				pXmlError('SqlError', $this->pConnection->getErr(), true);
				exit;
			}
			
			$xmlStatus  = '<count>'.$count.'</count>'."\n";
			$xmlStatus .= '<pageLines>'.$this->pageLines().'</pageLines>'."\n";
			$xmlStatus .= '<page>'.$this->page.'</page>';
			$xmlStatus = pXmlAddParent($xmlStatus, 'pGridStatus');
			
			$xml .= $xmlStatus;
			
			$xml .= $this->pFilter->makeXmlFilter();
			
			$debugSql  = '<sql>'.$this->sqlSearch().'</sql>'."\n";
			$debugSql .= '<sqlCount>'.$this->sqlCount().'</sqlCount>';
			$debugSql = pXmlAddParent($debugSql, 'debugSql');
			
			if (isset($this->param['debug']) && $this->param['debug']) {
				$xml .= $debugSql;
			}
			
			$xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);
		}
		
		if ($verbose) {
			
			Header('Content-type: application/xml; charset=UTF-8');
			echo $xml;
		}
		
		return $xml;
	}
}
