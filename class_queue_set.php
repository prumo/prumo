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

class prumoQueueSet {
	private $name;
	
	private $pQueueName;
	private $pQueueLabel;
	private $pQueueFilename;
	private $pQueueType;
	
	public $indent;
	public $classBt;
	public $classBtFocus;
	
	function __construct($objName='') {
		
		$this->name = $objName;
		$this->pQueueName = array();
		$this->pQueueLabel = array();
		$this->pQueueFilename = array();
		$this->pQueueType = array();
		$this->indent = '';
		$this->classBt = 'pQueueSetBt';
		$this->classBtFocus = 'pQueueSetBtFocus';
	}
	
	/**
	 * Retorna o nome da instância
	 *
	 * @return string
	 */
	private function getObjName() {
		
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
	 * Adiciona uma fila
	 *
	 * @param $pQueueName string: nome da fila (não pode conter espaços)
	 * @param $pQueueLabel string: Rótulo do tabset (pode conter espaços e acentos)
	 * @param $pQueueFilename string: nome do arquivo a ser incluído na tab (opcional)
	 * @param $routine string: quando informado, mostra a tab apenas quando o usuário logado tem permissão para a rotina
	 */
	public function addQueue($pQueueName, $pQueueLabel, $pQueueFilename='', $routine='') {
		
		if (pPermitted($routine)) {
			
			$this->pQueueName[] = $pQueueName;
			$this->pQueueLabel[] = $pQueueLabel;
			$this->pQueueFilename[] = $pQueueFilename;
			$this->pQueueType[] = 'prumoQueue';
		}
	}
	
	/**
	 * Adiciona um novo tab, semelhando ao addQueue
	 *
	 * @param $pQueueName string: nome da fila (não pode conter espaços)
	 * @param $pQueueLabel string: Rótulo do tabset (pode conter espaços e acentos)
	 * @param $include string: nome do arquivo a ser incluído na tab (opcional)
	 * @param $html string: código html a ser incluído na tab (opcional)
	 */
	public function addTab($pQueueName, $pQueueLabel, $fileName='', $routine='') {
		
		if (pPermitted($routine)) {
			
			$this->pQueueName[] = $pQueueName;
			$this->pQueueLabel[] = $pQueueLabel;
			$this->pQueueFilename[] = $fileName;
			$this->pQueueType[] = 'tab';
		}
	}
	
	/**
	 * Gera o código HTML no lado do cliente
	 */
	public function makeHtml() {
		
		$indent = $this->indent;
		echo $indent.'<fieldset class="pQueueSet">'."\n";
		
		// adiciona os botões
		echo $indent.'<legend>';
		for ($i=0; $i < count($this->pQueueLabel); $i++) {
			
			echo "\n";
			$onclick = $this->pQueueName[$i].'.lineClick(0)';
			
			if ($this->pQueueType[$i] == 'prumoQueue') {
				echo '<button class="pButton-outline" id="'.$this->getObjName().'_bt_'.$this->pQueueName[$i].'" onclick="'.$onclick.'" onmouseover="'.$this->getObjName().'BtMouseover'.$i.'()">'.$this->pQueueLabel[$i].'</button>';
			}
			
			if ($this->pQueueType[$i] == 'tab') {
				echo '<button class="pButton-outline" id="'.$this->getObjName().'_bt_'.$this->pQueueName[$i].'" onmouseover="'.$this->getObjName().'BtMouseover'.$i.'()">'.$this->pQueueLabel[$i].'</button>';
			}
			
			if ($i < count($this->pQueueLabel)-1) {
				echo ' ';
			}
		}
		echo '</legend>'."\n";
		
		// inicializa os objetos queue
		for ($i=0; $i < count($this->pQueueName); $i++) {
			
			if ($this->pQueueType[$i] == 'prumoQueue') {
				
				$pQueue = $this->pQueueName[$i];
				global $$pQueue;
				require($this->pQueueFilename[$i]);
				
				if ($i > 0) {
					echo $indent.'<script type="text/javascript">'."\n";		
					echo $indent.'	document.getElementById(\'div_'.$this->pQueueName[$i].'\').style.display = \'none\';'."\n";
					echo $indent.'</script>'."\n";
				}
			}
			
			if ($this->pQueueType[$i] == 'tab') {
				
				echo '<div id="div_'.$this->pQueueName[$i].'">'."\n";
				
				if (!empty($this->pQueueFilename[$i])) {
					include($this->pQueueFilename[$i]);
				}
				
				echo '</div>'."\n";
			}
		}
		
		echo $indent.'</fieldset>'."\n";
		
		// adiciona javascript que coloca a quantidade de itens da fila no label
		echo $indent.'<script type="text/javascript">'."\n";
		
		for ($i=0; $i < count($this->pQueueName); $i++) {
			
			if ($this->pQueueType[$i] == 'prumoQueue') {
				
				echo $indent.'	'.$this->pQueueName[$i].'.afterList = function() {'."\n";
				echo $indent.'		if (this.pGridNavigation.count == 0) {'."\n";
				echo $indent.'			document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$i].'\').innerHTML = \''.$this->pQueueLabel[$i].'\';'."\n";
				echo $indent.'		}'."\n";
				echo $indent.'		else {'."\n";
				echo $indent.'			document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$i].'\').innerHTML = \''.$this->pQueueLabel[$i].' (\'+this.pGridNavigation.count+\')\';'."\n";
				echo $indent.'		}'."\n";
				echo $indent.'	}'."\n";
				echo $indent."\n";
			}
			
			echo $indent.'	function '.$this->getObjName().'BtMouseover'.$i.'() {'."\n";
			for ($j=0; $j < count($this->pQueueName); $j++) {
				
				if ($j == $i) {
					echo $indent.'		document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').style.fontWeight = \'bold\';'."\n";
					echo $indent.'		document.getElementById(\'div_'.$this->pQueueName[$j].'\').style.display = \'block\';'."\n";
					echo $indent.'		document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').className = \'pButton-outline active\';'."\n";
				}
				else {
					echo $indent.'		document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').style.fontWeight = \'normal\';'."\n";
					echo $indent.'		document.getElementById(\'div_'.$this->pQueueName[$j].'\').style.display = \'none\';'."\n";
					echo $indent.'		document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').className = \'pButton-outline\';'."\n";
				}
			}
			
			if ($this->pQueueType[$i] == 'prumoQueue') {
				echo $indent.'		'.$this->pQueueName[$i].'.pFilter.focus();'."\n";
			}
			
			echo $indent.'	}'."\n";
			echo $indent."\n";
		}
		
		echo $indent.'</script>'."\n";
	}
	
	/**
	 * Gera um javascript que dispara o goSearch() em todos os objetos prumoQueue filhos
	 */
	public function goSearchAll() {
		
		$indent = $this->indent;
		echo $indent.'<script type="text/javascript">'."\n";
		
		for ($i=0; $i < count($this->pQueueName); $i++) {
			if ($this->pQueueType[$i] == 'prumoQueue') {
				echo $indent.$this->pQueueName[$i].'.goSearch();	'."\n";
			}
		}
		
		echo $indent.'</script>'."\n";
	}
}
