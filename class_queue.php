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


class prumoQueue extends prumoSearch {
	private $orderby;
	
	public $htmlTop = '';
	public $htmlBottom = '';
	
	/**
	 * Adiciona um container ao código, sendo o id o nome do objeto
	 *
	 * @param $pSearch string: código de entrada
	 *
	 * @return string: código de saída
	 */
	protected function addWindow($pSearch) {
		$pQueue  = '<div id="div_'.$this->name.'">';
		$pQueue .= $pSearch;
		$pQueue .= '</div>';
		
		return $pQueue;
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
		$pSearchChids = '<div id="' . $this->name . '_header">' . $this->htmlTop . '</div>';
		$pSearchChids .= '<div id="' . $this->name . '_body">';
		$pSearchChids .= parent::makeFilters();
		$pSearchChids .= parent::makeGrid();
		$pSearchChids .= parent::makeGridNavigation();
		$pSearchChids .= '</div>';
		$pSearchChids .= '<div id="' . $this->name . '_footer">' . $this->htmlBottom . '</div>';
		$pSearchChids = $this->addWindow($pSearchChids);
		
		// muda o evento click
		$pQueue = "\n";
		$pQueue .= '<script type="text/javascript">'."\n";
		$pQueue .= '	pGrid_'.$this->name.'.lineEventOnData = \''.$this->name.'.lineClick(%)\';'."\n";
		$pQueue .= '</script>'."\n";
		
		$pSearch = $pSearchInit . $pSearchChids . $pQueue;
		
		if ($verbose) {
			echo $pSearch;
		}
		
		return $pSearch;
	}
	
	/**
	 * Gera um jsvascript que chama o goSearch no lado do cliente
	 */
	public function goSearch() {
		echo '<script type="text/javascript">'.$this->name.'.goSearch(1);</script>'."\n";
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
	 * Inicializa o objeto no lado do cliente
	 *
	 * @return string: código gerado
	 */
	protected function initClientObject() {
		
		// trata o caminho do xmlFile (relativo e absoluto)
		$ajaxFile = substr($this->param['xmlfile'],0,1) == '/' ? $this->param['xmlfile'] : $GLOBALS['pConfig']['appWebPath'].'/'.$this->param['xmlfile'];
		
		// instancia o objeto prumoSearch no cliente
		$clientObject = $this->indentation. '<script type="text/javascript">'."\n";
		$clientObject .= $this->indentation. '	'.$this->name.' = new prumoQueue(\''.$this->name.'\',\''.$ajaxFile.'\');'."\n";
		
		// repassa condicionalmente o pog debug para o objeto ajax
		if (isset($this->param['debug']) && $this->param['debug']) {
			$clientObject .= $this->indentation. '	'.$this->name.'.pAjax.debug = true;'."\n";
		}
		
		$clientObject .= $this->indentation. '</script>'."\n";
		
		return $clientObject;
	}
	
	/**
	 * Adiciona um campo
	 *
	 * @param $params array: array com os parâmetros do campo
	 */
	public function addField($params) {
		parent::addField($params);
		
		$param = pParameters($params);
		if (!isset($this->param['priorityfield']) or $this->param['priorityfield'] == '') {
			$this->param['priorityfield'] = $param['name'];
		}
	}

	/**
	 * Define o campo de ordenação da consulta
	 *
	 * @param $orderby string: nome do campo de ordenação
	 */
	public function setOrderby($orderby) {
		$this->orderby = $orderby;
	}
	
	/**
	 * Gera o código SQL da ordenação da consulta
	 *
	 * @return string: SQL da ordenação da consulta
	 */
	public function sqlOrderby() {
		return $this->orderby == '' ? ' ORDER BY ' .$this->param['priorityfield']: ' ORDER BY ' .$this->param['priorityfield'] . ',' . $this->orderby;
	}
	
}
