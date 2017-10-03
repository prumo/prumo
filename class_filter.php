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
 * prumoFilter é um sistema de filtros genérico que serve para prumoSearch, prumoCrudList e prumoQueue
 */
class prumoFilter {
	
	private $parentName;
	private $ind;
	private $countVisible;
	
	public $field;
	public $pConfig;
	public $shortcut;
	
	/**
	 * Construtor da classe prumoFilter
	 *
	 * @param $parentName string: nome do objeto pai, usado para manipular os objetos em js
	 * @param $field array: parametros do field
	 */
	function __construct($parentName, $field) {
		global $pConfig;
		$this->pConfig = $pConfig;
		
		$this->parentName = $parentName;
		
		$this->field = $field;
		$this->shortcut = '';
	}
	
	/**
	 * Seta a indentação para organizar o código gerado no lado do cliente
	 *
	 * @param $ind string: tabs para indentação no lado do cliente
	 */
	public function setIndentarion($ind) {
		$this->ind = $ind;
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
	 * Gera o HTML e JS dos filtros
	 *
	 * @param verbose boolean: quando true imprime o código
	 *
	 * @return string: código HTML e JS
	 */
	public function draw($verbose) {
		
		$htmlFilters = $this->makeHtml();
		$htmlFilters .= $this->makeJs();
	
		if ($verbose) {
			echo $htmlFilters;
		}
		
		return $htmlFilters;
	}
	
	/**
	 * Gera o HTML dos filtros
	 *
	 * @param verbose boolean: quando true imprime o código
	 *
	 * @return string: código HTML
	 */
	public function makeHtml() {
		
		$htmlFilters  = $this->ind.'<div class="prumoFilter" align="center" id="pFilter_'.$this->parentName.'">'."\n";
		$htmlFilters .= $this->ind.'	<table>'."\n";
		$htmlFilters .= $this->ind.'		<tr>'."\n";
		$htmlFilters .= $this->ind.'			<td id="pFilter_'.$this->parentName.'_filters" style="text-align:left">'."\n";
		$htmlFilters .= $this->ind.'			</td>'."\n";
		$htmlFilters .= $this->ind.'			<td id="pFilter_'.$this->parentName.'_controls">'."\n";
		$htmlFilters .= $this->ind.'				<div style="text-align:center;">'."\n";
		$htmlFilters .= $this->ind.'					<button class="pButton" id="'.$this->parentName.'_btSearch" onclick="'.$this->parentName.'.cmdSearch()">'._('Pesquisar').'</button>'."\n";
		$htmlFilters .= $this->ind.'					<button class="pButton" id="'.$this->parentName.'_btSearchAll" onclick="'.$this->parentName.'.cmdSearchAll()">'._('Todos').'</button>'."\n";
		if (!empty($this->shortcut)) {
			$htmlFilters .= $this->ind.'					'.$this->shortcut."\n";
		}
		$htmlFilters .= $this->ind.'				</div>'."\n";
		$htmlFilters .= $this->ind.'			</td>'."\n";
		$htmlFilters .= $this->ind.'		</tr>'."\n";
		$htmlFilters .= $this->ind.'	</table>'."\n";
		$htmlFilters .= $this->ind.'</div>'."\n";
		
		return $htmlFilters;
	}
	
	/**
	 * Gera o JS dos filtros
	 *
	 * @param verbose boolean: quando true imprime o código
	 *
	 * @return string: código JS
	 */
	private function makeJs() {
		
		$jsFilters  = $this->ind.'<script type="text/javascript">'."\n";
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.' = new prumoFilter(\'pFilter_'.$this->parentName.'\');'."\n";
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.'.prumoWebPath = \''.$GLOBALS['pConfig']['prumoWebPath'].'\';'."\n";
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.'.parent = '.$this->parentName.';'."\n";

		// passa informação dos fields do servidor para o filter
		$filterName = '';
		$filterLabel = '';
		$filterType = '';
		
		for ($i=0; $i < $this->fieldCount(); $i++) {
			
			$filterName .= '"'.$this->field[$i]['name'].'"';
			if ($i < $this->fieldCount() -1) {
				$filterName .= ',';
			}
			
			$filterLabel .= '"'.$this->field[$i]['label'].'"';
			if ($i < $this->fieldCount() -1) {
				$filterLabel .= ',';
			}
			
			$filterType .= '"'.$this->field[$i]['type'].'"';
			if ($i < $this->fieldCount() -1) {
				$filterType .= ',';
			}
		}
		
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.'.fieldName  = new Array('.$filterName.");\n";
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.'.fieldLabel = new Array('.$filterLabel.");\n";
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.'.fieldType  = new Array('.$filterType.");\n";
		$jsFilters .= $this->ind.'	pFilter_'.$this->parentName.'.draw();'."\n";
		$jsFilters .= $this->ind.'</script>'."\n";
		
		return $jsFilters;
	}
	
	/**
	 * Prepara a carga de uma consulta
	 */
	public function loadQuery() {
		
		$this->filter = array();
		$this->filter['fieldName'] = isset($_POST['fField']) ? $_POST['fField'] : array();
		
		//adiciona um filtro vazio e visivel caso não haja nenhum
		if (count($this->filter['fieldName']) == 0) {
			$this->filter['fieldName'][0] = $this->field[0]['name'];
			$this->filter['operator'][0]  = '';
			$this->filter['value'][0]     = '';
			$this->filter['value2'][0]    = '';
			$this->filter['visible'][0]   = 'true';
		}
		else {
			$this->filter['operator']  = $_POST['fOperator'];
			$this->filter['value']     = $_POST['fValue'];
			$this->filter['value2']    = $_POST['fValue2'];
			$this->filter['visible']   = $_POST['fVisible'];
		}
		
		//conta filtros visíveis
		$this->countVisible = 0;
		for ($i = 0; $i < count($this->filter['visible']); $i++) {
			if ($this->filter['visible'][$i] != 'false' && $this->filter['visible'][$i] != false) {
				$this->countVisible++;
			}
		}
		
		//se não possui nenhum filtro visível adiciona
		if ($this->countVisible == 0) {
			$nextFilterIndex = count($this->filter['fieldName']);
			$this->filter['fieldName'][$nextFilterIndex] = $this->field[0]['name'];
			$this->filter['operator'][$nextFilterIndex]  = '';
			$this->filter['value'][$nextFilterIndex]     = '';
			$this->filter['value2'][$nextFilterIndex]    = '';
			$this->filter['visible'][$nextFilterIndex]   = true;
		}
	}
	
	/**
	 * Gera o código XML dos filtros
	 *
	 * @return string: código XML
	 */
	public function makeXmlFilter() {
		$this->loadQuery();
		$xmlFilters = '';
		for ($i = 0; $i < count($this->filter['fieldName']); $i++) {
			$xmlFilter  = '<fieldName>'.$this->formatXmlData($this->filter['fieldName'][$i]).'</fieldName>'."\n";
			$xmlFilter .= '<operator>'.$this->formatXmlData($this->filter['operator'][$i]).'</operator>'."\n";
			$xmlFilter .= '<value>'.$this->formatXmlData($this->filter['value'][$i]).'</value>'."\n";
			$xmlFilter .= '<value2>'.$this->formatXmlData($this->filter['value2'][$i]).'</value2>'."\n";
			$xmlFilter .= '<visible>'.$this->formatXmlData($this->filter['visible'][$i]).'</visible>';
			$xmlFilter = pXmlAddParent($xmlFilter,'pFilter');
			$xmlFilters .= $xmlFilter;
		}
		return $xmlFilters;
	}
	
	/**
	 * Formata dados XML (substitui vazio por NULL)
	 *
	 * @param $text string: texto a ser formatado
	 *
	 * @return string: xml formatado
	 */
	private function formatXmlData($text) {
		$formatedText = $text;
		$formatedText = str_replace('&', '&amp;', $formatedText);
		return $text == '' ? 'NULL' : $formatedText;
	}
	
}

