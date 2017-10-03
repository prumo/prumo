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

class prumoFilter {
	private $indentation;
	private $countVisible;
	public $field;
	public $pConfig;
	public $shortcut;
	
	function __construct($name, $field, $params) {
		global $pConfig;
		$this->pConfig = $pConfig;

		$this->param = pParameters($params);
		$this->name = $name;

		$this->field = $field;
		$this->shortcut = '';
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
		
		$htmlFilters  = $this->indentation.'<div class="prumoFilter" align="center" id="pFilter_'.$this->name.'">'."\n";
		$htmlFilters .= $this->indentation.'	<table>'."\n";
		$htmlFilters .= $this->indentation.'		<tr>'."\n";
		$htmlFilters .= $this->indentation.'			<td id="pFilter_'.$this->name.'_filters" style="text-align:left">'."\n";
		$htmlFilters .= $this->indentation.'			</td>'."\n";
		$htmlFilters .= $this->indentation.'			<td id="pFilter_'.$this->name.'_controls">'."\n";
		$htmlFilters .= $this->indentation.'				<div style="text-align:center;">'."\n";
		$htmlFilters .= $this->indentation.'					<button class="pButton" id="'.$this->name.'_btSearch" onclick="'.$this->name.'.cmdSearch()">'._('Pesquisar').'</button>'."\n";
		$htmlFilters .= $this->indentation.'					<button class="pButton" id="'.$this->name.'_btSearchAll" onclick="'.$this->name.'.cmdSearchAll()">'._('Todos').'</button>'."\n";
		if (!empty($this->shortcut)) {
			$htmlFilters .= $this->indentation.'					'.$this->shortcut."\n";
		}
		if (isset($this->param['subscribe']) && $this->param['subscribe'] != '') {
			$htmlFilters .= $this->indentation.'					<button id="'.$this->name.'_btAdd" onclick="">'._('Cadastrar').'</button>'."\n";
		}
		$htmlFilters .= $this->indentation.'				</div>'."\n";
		$htmlFilters .= $this->indentation.'			</td>'."\n";
		$htmlFilters .= $this->indentation.'		</tr>'."\n";
		$htmlFilters .= $this->indentation.'	</table>'."\n";
		$htmlFilters .= $this->indentation.'</div>'."\n";
		
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
		
		$jsFilters  = $this->indentation.'<script type="text/javascript">'."\n";
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.' = new prumoFilter(\'pFilter_'.$this->name.'\');'."\n";
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.'.prumoWebPath = \''.$GLOBALS['pConfig']['prumoWebPath'].'\';'."\n";
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.'.parent = '.$this->name.';'."\n";

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
		
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.'.fieldName  = new Array('.$filterName.");\n";
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.'.fieldLabel = new Array('.$filterLabel.");\n";
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.'.fieldType  = new Array('.$filterType.");\n";
		$jsFilters .= $this->indentation.'	pFilter_'.$this->name.'.draw();'."\n";
		$jsFilters .= $this->indentation.'</script>'."\n";
		
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

