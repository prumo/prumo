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

class prumoGrid {
	
	private $name;
	private $lines;
	private $column;
	public $width;
	public $indentation;
	public $lineEventOnData;
	public $pointerCursorOnData;
	
	function __construct($name,$lines) {
		
		$this->name = $name;
		$this->lines = $lines;
		$column = array();
		$this->width = 0;
		$this->lineEventOnData = false;
		$this->pointerCursorOnData = '';
	}
	
	/**
	 * Adiciona uma coluna no GRID
	 *
	 * @param $params array: array com os parametros da coluna
	 */
	public function addColumn($params) {
		
		$param = pParameters($params);
		$name = $param['name'];
		
		$label = isset($param['label']) ? $param['label'] : $param['name'];
		$labelAlign = isset($param['labelalign']) ? $param['labelalign'] : 'center';
		$align = isset($param['align']) ? $param['align'] : 'left';
		
		//visible - default true
		if (!isset($param['visible'])) {
			$visible = true;
		}
		else {
			$visible = $param['visible'] == 'false' ? false : true;
		}
		
		$this->column[] = array(
			'name' => $name,
			'label' => $label,
			'labelalign' => $labelAlign,
			'align' => $align,
			'visible' => $visible
		);
	}
	
	/**
	 * Gera o código de início da tabela
	 *
	 * @return string: código HTML
	 */
	private function tableGridHeader() {
		return "\n".$this->indentation.'<table id="pGrid_'.$this->name.'" class="prumoGridTable">'."\n";
	}
	
	/**
	 * Gera o código do fechamento da tabela
	 *
	 * @return string: código HTML
	 */
	private function tableClose() {
		return $this->indentation.'</table>'."\n";
	}
	
	/**
	 * Gera o código do cabecalho da tabela
	 *
	 * @return string: código HTML
	 */
	private function dataHeader() {
		
		$header  = $this->indentation.'	<tr class="prumoGridTh">'."\n";
		
		for ($i=0; $i < count($this->column); $i++) {
			
			if ($this->column[$i]['visible'] != false) {
				$header .= $this->indentation.'		<th class="prumoGridTh" id="prumoGridTh_'.$this->name .'_'.$this->column[$i]['name'].'" style="text-align:'.$this->column[$i]['labelalign']
				                                              .'" onclick="' . $this->name . '.sort(\'' . $this->column[$i]['name'] . '\')'
	                                                          .'">'.$this->column[$i]['label'].'</th>'."\n";
			}
		}
		
		$header .= $this->indentation.'	</tr>'."\n";
		
		return $header;
	}
	
	/**
	 * Gera o código de cada linha da tabela
	 *
	 * @param $index integer: indice da linha
	 *
	 * @return string: código HTML
	 */
	private function line($index) {
		
		$line = is_int($index/2) ? $this->indentation.'	<tr class="prumoGridTrEven">'."\n" : $this->indentation.'	<tr class="prumoGridTrOdd">'."\n";
		
		for ($i=0; $i < count($this->column); $i++) {
			
			if ($this->column[$i]['visible'] != false) {
				$line .= $this->indentation.'		<td class="prumoGridTd" style="text-align:'.$this->column[$i]['align']
    	                                                                                          .'"><br /></td>'."\n";
			}
		}
		
		$line .= $this->indentation.'	</tr>'."\n";
		
		return $line;
	}
	
	/**
	 * Gera o código de todas as linhas
	 *
	 * @return string: código HTML
	 */
	private function lines() {	
		
		$lines = '';
		for ($i = 0; $i < $this->lines; $i++) {
			$lines .= $this->line($i);
		}
		
		return $lines;
	}
	
	/**
	 * Gera o código JS do grid no lado do cliente
	 *
	 * @return string: código JS
	 */
	private function clientObject() {
		
		$client  = $this->indentation.'<script type="text/javascript">'."\n";
		$client .= $this->indentation.'	pGrid_'.$this->name.' = new prumoGrid(\'pGrid_'.$this->name.'\');'."\n";
		$client .= $this->indentation.'	pGrid_'.$this->name.'.lines = '.$this->lines.';'."\n";
		$client .= $this->indentation.'</script>'."\n";
		
		return $client;
	}
	
	/**
	 * Gera o código completo com HTML e JS
	 *
	 * @param $verbose boolean: quando true imprime o código gerado
	 *
	 * @return string: código HTML e JS
	 */
	public function draw($verbose) {
		
		require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php');
		
		$htmlGrid  = $this->tableGridHeader();
		$htmlGrid .= $this->dataHeader();
		$htmlGrid .= $this->lines();
		$htmlGrid .= $this->tableClose();
		$htmlGrid .= $this->clientObject();
		
		if ($verbose) {
			echo $htmlGrid;
		}
		
		return $htmlGrid;
	}
}
