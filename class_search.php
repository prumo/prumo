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

class prumoSearch extends prumoBasic {
	private $pGrid;
	private $fieldReturn;
	protected $indentation;
	public $pConnection;
	public $pFilter;
	private $fixedSqlSearch;
	private $constructedGrid;
	
	public $page;
	
	private $orderby;
	
	public $autoFilter; // quando true (default), prepara um filtro autometicamente quando o usuário altera qualquer campo que participe do fieldReturn
	
	function __construct($params) {
		
		$this->orderby = '';
		
		$files = get_included_files();
		$lastInclusion = $files[count($files)-1];
		
		parent::__construct($params);
		$this->constructedGrid = false;
		
		if (!isset($this->param['xmlfile'])) {
			if (dirname($_SERVER["SCRIPT_FILENAME"]) == dirname($lastInclusion)) {
				$this->param['xmlfile'] = basename($lastInclusion);
			}
			else {
				$this->param['xmlfile'] = $GLOBALS['pConfig']['appWebPath'] . str_replace($GLOBALS['pConfig']['appPath'], '', $lastInclusion);
			}
		}
		
		$autoFilter = (!isset($this->param['autofilter']) or $this->param['autofilter'] != 'false');
		
		$this->page = 1;
		$this->fieldReturn = array();
		
		$this->autoFilter = $autoFilter;
	}
	
	/**
	 * Permite ao desenvolvedor da aplicação explicitar qual prumoConnection usar
	 */
	public function setConnection($connecion) {
		$this->pConnection = $connecion;
	}
	
	/**
	 * Seta a indentação para organizar o código gerado no lado do cliente
	 *
	 * @param $indentation string: tabs para indentação no lado do cliente
	 */
	public function setIndentation($indentation) {
		$this->indentation = $indentation;
	}
	
	/**
	 * Gera o código JS que faz o link entre o search e o crud para bloquear/desbloquear o botão de acordo com o estado do crud
	 *
	 * @param $crudName string: nome do prumoCRUD
	 * @verbose boolean: quando true imprime o código gerado
	 *
	 * @return string: código gerado
	 */
	public function crudState($crudName, $verbose=true) {
		
		$state  = '<script type="text/javascript">'."\n";
		$state .= '	'.$crudName.'.addSonSearch('.$this->name.');'."\n";
		$state .= '</script>'."\n";
		
		if ($verbose) {
			echo $state;
		}
		
		return $state;
	}
	
	/**
	 * Constrói o objeto GRID
	 */
	private function constructGrid() {
		
		$this->getObjName();
		$lines = $this->pageLines();
		$this->pGrid = new prumoGrid($this->name, $lines);
		$this->pGrid->indentation = $this->indentation . '		';
		$this->pGrid->lineEventOnData = $this->name.'.lineClick(%)';
		$this->pGrid->pointerCursorOnData = true;
	}
	
	/**
	 * Verifica quantas linhas tem por página
	 *
	 * @return integer: quantidade de linhas
	 */
	protected function pageLines() {
		return isset($this->param['pagelines']) ? $this->param['pagelines'] : $GLOBALS['pConfig']['searchLines'];
	}
	
	/**
	 * Adiciona um campo
	 *
	 * @param $params array: array com os parâmetros do campo
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
		$param = pParameters($params);
		
		$lastField = count($this->field)-1;
		
		$this->field[$lastField]['sqlname'] = isset($param['sqlname']) ? $param['sqlname'] : $param['name'];
		$this->field[$lastField]['pk'] = isset($param['pk']) ? true : false;
		
		$this->pGrid->addColumn($params);
		$this->pFilter = new prumoFilter($this->name,$this->field,'');
		$this->pFilter->setIndentation($this->indentation.'		');
	}
	
	/**
	 * Adiciona um campo onde o registro escolhido deve ser retornado
	 *
	 * @param $fieldName string: nome do campo
	 * @param $idReturn string: id do input html. Quando não informado, copia do $fieldName
	 * @param $verbose boolean: quando true imprime o código gerado na tela
	 * @param $linkInput boolean: quando true vincula o campo HTML com o search
	 *
	 * @return string: código javascript gerado
	 */
	public function addFieldReturn($fieldName, $idReturn='', $verbose=true, $linkInput=true, $noRetrieve=false) {
		
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
		$noRetrieve = $noRetrieve ? 'true' : 'false';
		
		$fieldReturn  = $this->indentation.'<script type="text/javascript">'."\n";
		$fieldReturn .= $this->indentation. '	'.$this->name.'.addFieldReturn(\''.$fieldName.'\',\''.$idReturn.'\',\''.$fieldType.'\', '.$noRetrieve.");\n";
		if ($linkInput and $this->autoFilter) {
			$fieldReturn .= $this->indentation. '	inputField = document.getElementById(\''.$idReturn.'\');'."\n";
			$fieldReturn .= $this->indentation. '	inputField.pSearch = '.$this->name.';'."\n";
			$fieldReturn .= $this->indentation. '	'."\n";
			
			$fieldReturn .= $this->indentation. '	if (inputField.getAttribute(\'onFocus\') == null) {'."\n";
			$fieldReturn .= $this->indentation. '		inputField.setAttribute(\'onFocus\',\'this.pSearch.fieldFocus(this)\');'."\n";
			$fieldReturn .= $this->indentation. '	}'."\n";
			$fieldReturn .= $this->indentation. '	'."\n";
			
			$fieldReturn .= $this->indentation. '	if (inputField.getAttribute(\'onBlur\') == null) {'."\n";
			$fieldReturn .= $this->indentation. '		inputField.setAttribute(\'onBlur\',\'this.pSearch.fieldBlur(this)\');'."\n";
			$fieldReturn .= $this->indentation. '	}'."\n";
			
			$fieldReturn .= $this->indentation. '	'."\n";
			$fieldReturn .= $this->indentation. '	if (inputField.getAttribute(\'onKeyDown\') == null) {'."\n";
			$fieldReturn .= $this->indentation. '		inputField.setAttribute(\'onKeyDown\',\'this.pSearch.fieldKeyDown(event)\');'."\n";
			$fieldReturn .= $this->indentation. '	}'."\n";
		}
		
		$fieldReturn .= $this->indentation.'</script>'."\n";
		
		if ($verbose) {
			echo $fieldReturn;
		}
		
		return $fieldReturn;
	}
	
	/**
	 * Inicializa o objeto no lado do cliente
	 *
	 * @return string: código gerado
	 */
	protected function initClientObject() {
		
		// trata o caminho do xmlFile (relativo e absoluto)
		if (substr($this->param['xmlfile'],0,1) == '/') {
			$ajaxFile = $this->param['xmlfile'];
		}
		else {
			$ajaxFile = $GLOBALS['pConfig']['appWebPath'].'/'.$this->param['xmlfile'];
		}
		
		// instancia o objeto prumoSearch no cliente
		$clientObject = $this->indentation. '<script type="text/javascript">'."\n";
		$clientObject .= $this->indentation. '	'.$this->name.' = new prumoSearch(\''.$this->name.'\',\''.$ajaxFile.'\');'."\n";
		// repassa condicionalmente o debug para o objeto ajax
		if (isset($this->param['debug']) && $this->param['debug']) {
			$clientObject .= $this->indentation. '	'.$this->name.'.pAjax.debug = true;'."\n";
		}

		// repassa fields para o objeto cliente
		$fieldName = '';
		for ($i = 0; $i < count($this->field); $i++) {
			if ($fieldName != '')
				$fieldName .= ',';
			$fieldName .= '"'.$this->field[$i]['name'].'"';
		}
		
		$fieldPk = '';
		for ($i = 0; $i < count($this->field); $i++) {
			
			if ($fieldPk != '') {
				$fieldPk .= ',';
			}
			
			$fieldPk .= $this->field[$i]['pk'] ? 'true' : 'false';
		}
		
		$clientObject .= $this->indentation.'	'.$this->name.'.fieldName = Array('.$fieldName.');'."\n";
		$clientObject .= $this->indentation.'	'.$this->name.'.fieldPk = Array('.$fieldPk.');'."\n";
		
		$clientObject .= $this->indentation. '</script>'."\n";
		
		return $clientObject;
	}
	
	/**
	 * Gera o código HTML dos filtros
	 *
	 * @return string: código HTML dos filtros
	 */
	protected function makeFilters() {
		
		$this->pFilter->shortcut = $this->makeShortcut();
		
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
		
		// vinculo do prumoGrid com o prumoSearch
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
		
		// vinculo do prumoGridNavigation com o prumoSearch
		$htmlGridNavigation .= $this->indentation.'			'.$this->name.'.pGridNavigation = pGridNavigation_'.$this->name.';'."\n";

		$htmlGridNavigation .= $this->indentation.'		</script>'."\n";
		
		return $htmlGridNavigation;
	}
	
	/**
	 * Adiciona um container ao código, sendo o id o nome do objeto
	 *
	 * @param $pSearch string: código de entrada
	 *
	 * @return string: código de saída
	 */
	private function addWindow($pSearch) {
		
		$title = isset($this->param['title']) ? $this->param['title'] : $this->name;
		
		$pWindow = new prumoWindow('pWindow_'.$this->name);
		$pWindow->title = $title;
		$pWindow->indentation = $this->indentation;
		$pWindow->commandClose = $this->name.'.cancel()';
		$pSearchReturn = $pWindow->draw(false,$pSearch);
		
		// vinculo do prumoWindow com o prumoSearch
		$pSearchReturn .= $this->indentation.'<script type="text/javascript">'."\n";
		$pSearchReturn .= $this->indentation.'	'.$this->name.'.pWindow = pWindow_'.$this->name.";\n";
		
		// repassa parametro modal do prumoSearch (que só faz sentido se tiver pWindow, por isso o codigo esta aqui)
		if (isset($this->param['modal'])) {
			$pSearchReturn .= $this->indentation. '			'.$this->name.'.modal = '.$this->param['modal'].";\n";
		}
		$pSearchReturn .= $this->indentation. '	'.$this->name.'.pAjax.pLoading = new prumoLoading(\'pWindow_'.$this->name.'_loading\');'."\n";
		$pSearchReturn .= $this->indentation.'</script>'."\n";
		
		return $pSearchReturn;
	}
	
	/**
	 * Gera o link 'Inserir novo' para ser adicionado ao lado dos filtros
	 *
	 * @return string: código html do link
	 */
	private function makeShortcut() {
		global $pConnectionPrumo;
		
		if (isset($this->param['menushortcut'])) {
			
			$schema = $pConnectionPrumo->getSchema();
			
			$sql  = 'SELECT'."\n";
			$sql .= '	routine,'."\n";
			$sql .= '	link'."\n";
			$sql .= 'FROM '.$schema.'routines'."\n";
			$sql .= 'WHERE routine='.pFormatSql($this->param['menushortcut'], 'string').';';
			
			$query = $pConnectionPrumo->fetchAssoc($sql);
			
			if (pPermitted($query['routine'], 'c')) {
				$explode = explode(':', $query['link']);
				$protocol = $explode[0];
				if ($protocol == 'http') {
					//$link = '<button onclick="popup(\''.$query['routine'].'\')">'._('Cadastrar Novo').'</button>';
					$link = '<a href="'.$query['routine'].'" target="_blank">'._('Cadastrar Novo').'</a>';
				}
				else {
					//$link = '<button onclick="popup(\'index.php?page='.$query['routine'].'\')">'._('Cadastrar Novo').'</button>';
					$link = '<a href="index.php?page='.$query['routine'].'" target="_blank">'._('Cadastrar Novo').'</a>';
				}
				
				return $link;
			}
			
		}
		else {
			return '';
		}
	}
	
	/**
	 * Gera o código completo
	 *
	 * @param $verbose boolean: quando true imprime o código gerado
	 *
	 * @return string: código gerado
	 */
	public function draw($verbose) {
		
		require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php');

		// junta os objetos
		$pSearchInit = $this->initClientObject();
		$pSearchChids = $this->makeFilters();
		$pSearchChids .= $this->makeGrid();
		$pSearchChids .= $this->makeGridNavigation();		
		$pSearchChids = $this->addWindow($pSearchChids);

		$pSearch = $pSearchInit . $pSearchChids;
		
		if ($verbose) {
			echo $pSearch;
		}
		
		return $pSearch;
	}
	
	/**
	 * Adiciona um filtro na pesquisa
	 *
	 * @param $field string: nome do campo
	 * @param $operator string: operador lógico
	 * @param $value string: valor do campo
	 * @param $visible boolean: indica se o filtro deve aparecer na view
	 */
	public function addFilter($field, $operator, $value, $visible) {
		
		$booVisible = $visible == false ? 'false' : 'true';
		
		echo '<script type="text/javascript">'."\n";
		echo '	'.$this->name.'.pFilter.addFilter(null,\''.$field.'\',\''.$operator.'\',\''.$value.'\',\'\','.$booVisible.');'."\n";
		echo '	'.$this->name.'.pFilter.draw();'."\n";
		echo '</script>'."\n";
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
				$condition = str_replace(':field:',$field['sqlname'],$condition);
				$condition = str_replace(':value:',pFormatSql($value[$i],$field['type'],false,false),$condition);
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
		
		$orderbyOut = $this->orderby == '' ? ' ORDER BY ' .$orderbyOut : ' ORDER BY ' .$this->orderby;
		
		return $orderbyOut;
	}
	
	/**
	 * Define uma consulta SQL fixa para o Search
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
	 * Gera o código da consulta
	 *
	 * @return string: consulta SQL gerada
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
	 * Monta um comando SQL para fazer a contagem de registro de acordo com o filtro
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
	 * Monta um comando SQL de consulta
	 *
	 * @return string: comando SQL
	 */
	public function sqlRetrieve() {
		
		// monta condicao
		$condition = '';
		for ($i = 0; $i < count($this->field); $i++) {
			$fieldName = $this->field[$i]['name'];
			if ($this->field[$i]['pk']) {
				$condition .= empty($condition) ? ' WHERE ' : ' AND ';
				$value = pFormatSql($_POST[$this->field[$i]['name']],$this->field[$i]['type']);
				$condition .= $fieldName.'='.$value;
			}
		}
		
		// monta campos
		$fields = '';
		for ($i = 0; $i < count($this->field); $i++) {
			if (!isset($this->field[$i]['virtual']) or $this->field[$i]['virtual'] == false) {
				if ($fields != '') {
					$fields .= ',';
				}
				$fields .= $this->field[$i]['name'];
			}
		}
		
		$tableName = $this->param['tablename'];
		
		if ($this->fixedSqlSearch != '') {
			$sql = 'SELECT '.$fields.' FROM ('.$this->fixedSqlSearch.') fixed '.$condition.';';
		}
		else {
			$sql = 'SELECT '.$fields.' FROM '.$this->pConnection->getSchema($this->param['schema']).$tableName.$condition.';';
		}
		
		return $sql;
	}
	
	/**
	 * Executa a rotina RETRIEVE
	 *
	 * @param $verbose boolean: mostra resultado na tela
	 *
	 * @return string: resultado em XML
	 */
	private function doRetrieve($verbose) {
		global $prumoGlobal;
		
		if ($prumoGlobal['currentUser'] == '') {
			$xml = pXmlError('session expires',_('Sua sessão expirou, faça login novamente.'));
		}
		else {
			
			$xml = $this->pConnection->sqlXml($this->sqlRetrieve(), $this->name);
			if ($xml === false) {
				pXmlError('SqlError', $this->pConnection->getErr(), true);
				exit;
			}
			
			$xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);
		}
		
		if ($verbose) {
			Header('Content-type: application/xml; charset=UTF-8');		
			echo $xml;
		}
		
		return $xml;
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
			$xml = pXmlError('session expires',_('Sua sessão expirou, faça login novamente.'));
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
			
			$xml = $this->pConnection->sqlXml($this->sqlSearch(),$this->name);
			if ($xml === false) {
				pXmlError('SqlError', $this->pConnection->getErr(), true);
				exit;
			}
			
			$xmlStatus  = '<count>'.$count.'</count>'."\n";
			$xmlStatus .= '<pageLines>'.$this->pageLines().'</pageLines>'."\n";
			$xmlStatus .= '<page>'.$this->page.'</page>';
			$xmlStatus = pXmlAddParent($xmlStatus,'pGridStatus');
			
			$xml .= $xmlStatus;
			
			$xml .= $this->pFilter->makeXmlFilter();
			
			$debugSql  = '<sql>'.$this->sqlSearch().'</sql>'."\n";
			$debugSql .= '<sqlCount>'.$this->sqlCount().'</sqlCount>';
			$debugSql = pXmlAddParent($debugSql,'debugSql');
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
	
	/**
	 * Desenha o botão "pesquisar"
	 * 
	 * @param $verbose boolean: quando true imprime o html gerado
	 *
	 * @returns string: html dos controles
	 */
	public function makeButton($verbose=true) {
		
		$iconSearch = pGetTheme('icons/prumoSearch.png',true);
		$button = '<button class="pButton-outline" type="button" id="'.$this->name.'Bt" onClick="javascript:'.$this->name.'.goSearch();"><img src="'.$iconSearch.'" alt="prumoSearch" /></button>';
		
		if ($verbose) {
			echo $button;
		}
		
		return $button;
	}
	
	/**
	 * Decide qual ação tomar de acordo com os parametros passados via GET ou POST
	 */
	public function autoInit() {
		if (isset($_POST[$this->name.'_action']) && $_POST[$this->name.'_action'] == 'makeXml') {
			$this->makeXml(true);
		}
		else if (isset($_POST[$this->name.'_action']) && $_POST[$this->name.'_action'] == 'r') {
			$this->doRetrieve(true);
		}
		else {
			$this->draw(true);
		}
	}
}
