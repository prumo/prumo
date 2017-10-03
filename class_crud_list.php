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
 * prumoCrudList é a listagem de um objeto crud
 */
class prumoCrudList extends prumoSearch {
	
	/**
	 * Desenha o GRID
	 */
	protected function constructGrid() {
		
		$lines = $this->pageLines();
		
		if (isset($this->param['fastcreate']) and $this->param['fastcreate']) {
			$lines++;
		}
		
		parent::constructGrid($lines);
	}
	
	/**
	 * Adiciona um campo onde o registro escolhido deve ser retornado
	 *
	 * @param $fieldName string: nome do campo
	 * @param $idReturn string: id do input html. Quando não informado, copia do $fieldName
	 * @param $verbose boolean: quando true imprime o código gerado na tela
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
		
		$fieldReturn  = $this->ind.'<script type="text/javascript">'."\n";
		$fieldReturn .= $this->ind. '	'.$this->name.'.addFieldReturn(\''.$fieldName.'\',\''.$idReturn.'\',\''.$fieldType.'\''.", false);\n";
		$fieldReturn .= $this->ind.'</script>'."\n";
		
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
	private function initClientObject() {
		
		// instancia o objeto prumoCrudList no cliente
		$clientObject  = $this->ind. '<script type="text/javascript">'."\n";
		$clientObject .= $this->ind. '	'.$this->name.' = new prumoCrudList(\''.$this->name.'\',\''.$this->ajaxFile.'\');'."\n";
		$clientObject .= $this->ind. '	'.$this->name.'.objName = \''.$this->name.'\';'."\n";
		$clientObject .= $this->ind. '	'.$this->name.'.parent = '.$this->param['crudname'].';'."\n";
		
		// repassa condicionalmente o pog debug para o objeto ajax
		if (isset($this->param['debug']) and $this->param['debug']) {
			$clientObject .= $this->ind. '	'.$this->name.'.pAjax.debug = true;'."\n";
		}
		
		// repassa parametro auto click
		$clientObject .= $this->ind . '	'. $this->name.'.autoClick = ';
		$clientObject .= (isset($this->param['autoclick']) and $this->param['autoclick']) ? 'true;'."\n" : 'false;'."\n";
		
		//fastCreate
		if (isset($this->param['fastcreate']) and $this->param['fastcreate']) {
			$clientObject .= $this->ind . '	'. $this->name.'.fastCreate = true;'."\n";
		}
		
		//fastUpdate
		if (isset($this->param['fastupdate']) and $this->param['fastupdate']) {
			$clientObject .= $this->ind . '	'. $this->name.'.fastUpdate = true;'."\n";
		}
		
		//fastDelete
		if (isset($this->param['fastdelete']) and $this->param['fastdelete']) {
			$clientObject .= $this->ind . '	'. $this->name.'.fastDelete = true;'."\n";
		}
		
		$clientObject .= $this->ind. '</script>'."\n";
		
		return $clientObject;
	}
	
	/**
	 * Gera o botão 'Inserir novo' para ser adicionado ao lado dos filtros
	 *
	 * @return string: código html do botão
	 */
	protected function makeShortcut() {
		if (!isset($this->param['routine']) || empty($this->param['routine']) || pPermitted($this->param['routine'], 'c')) {
			$onClick = $this->pFilter->btNew = $this->param['crudname'].'.bt_new()';
			return $this->ind.'					<button class="pButton" id="'.$this->name.'_btNew" onclick="'.$onClick.'">'._('Inserir Novo').'</button>'."\n";
		}
		else {
			return 'aa';
		}
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
	 * Cria o código JS que associa o crudList a CRUD
	 *
	 * @return string: código JS
	 */
	private function makeCrudLink() {
		
		$htmlCrudLink  = $this->ind.'		<script type="text/javascript">'."\n";
		$htmlCrudLink .= $this->ind.'			'.$this->param['crudname'].'.pCrudList = '.$this->name.';'."\n";
		$htmlCrudLink .= $this->ind.'		</script>'."\n";
		
		return $htmlCrudLink;
	}
}
