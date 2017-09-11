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

class prumoWindow {
	private $objName;
	public $indentation;
	
	// client property
	public $width;
	public $title;
	public $align;
	public $vAlign;
	
	public $showBtClose;
	public $commandClose;
	
	function __construct($objName='') {
		require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php');		
		
		$this->objName     = $objName;
		$this->width       = 930;
		$this->title       = 'prumoWindow';
		$this->align       = 'center';
		$this->vAlign      = 'top';
		$this->indentation = '';
		$this->showBtClose   = true;
	}
	
	/**
	 * Retorna o nome da instância
	 *
	 * @return string
	 */
	public function getObjName() {
		
		if (!isset($this->objName) or $this->objName == '') {
			
			$className = get_class($this);
			$instance = array();
			
			foreach ($GLOBALS as $key => $value) {
				if (is_object($value) and get_class($value) == $className) {
					$instance[] = $key;
				}
			}
			
			$this->objName = array_pop($instance);
		}
		
		if ($this->commandClose == '') {
			$this->commandClose = $this->objName.'.hide()';
		}
		
		return $this->objName;
	}
	
	/**
	 * Gera o código HTML do topo da janela
	 *
	 * @param verbose boolean: quando true imprime o retorno
	 *
	 * @returns string;
	 */
	public function drawTop($verbose=true) {
		
		$this->getObjName();
		
		$pWindow  = $this->indentation . '<div class="prumoVeil" id="'.$this->objName.'_veil"></div>'."\n";
		$pWindow .= $this->indentation . '<div class="prumoWindow" id="'.$this->objName.'">'."\n";
		$pWindow .= $this->indentation . '	<div class="prumoWindowTitle" id="'.$this->objName.'_titleBar">'."\n";
		
		$pWindow .= $this->indentation . '		<div class="prumoWindowClose" id="'.$this->objName.'_close"' . ($this->showBtClose == false ? 'style="display:none"' : '') . '>'."\n";
		$pWindow .= $this->indentation . '			<a href="javascript:'.$this->commandClose.'">'."\n";
		$pWindow .= $this->indentation . '				<img src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/close.png" alt="[X]" />'."\n";
		$pWindow .= $this->indentation . '			</a>'."\n";
		$pWindow .= $this->indentation . '		</div>'."\n";
    		
		$pWindow .= $this->indentation . '		<div id="'.$this->objName.'_title" class="prumoWindowTitle" onmousedown="'.$this->objName.'.move()" onmouseup="'.$this->objName.'.dropMove()">titulo</div>'."\n";
		$pWindow .= $this->indentation . '		<div id="'.$this->objName.'_loading" class="prumoWindowLoading"><img src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/loading.gif" alt="" /></div>'."\n";
		$pWindow .= $this->indentation . '	</div>'."\n";
		$pWindow .= $this->indentation . '	<div class="prumoWindowBody">'."\n";
		
		if ($verbose) {
			echo $pWindow;
		}
		
		return $pWindow;
	}
	
	/**
	 * Gera o código HTML do rodapé da janela
	 *
	 * @param verbose boolean: quando true imprime o retorno
	 *
	 * @returns string;
	 */
	public function drawFooter($verbose=true) {
		
		$this->getObjName();
		
		$pWindow  = $this->indentation . '	</div>'."\n";
		$pWindow .= $this->indentation . '</div>'."\n";
		
		$pWindow .= $this->indentation . '<script type="text/javascript">'."\n";
		$pWindow .= $this->indentation . '	'.$this->objName.' = new prumoWindow(\''.$this->objName.'\');'."\n";
		$pWindow .= $this->indentation . '	'.$this->objName.'.width = '.$this->width.';'."\n";
		$pWindow .= $this->indentation . '	'.$this->objName.'.title = \''.$this->title.'\';'."\n";
		$pWindow .= $this->indentation . '	'.$this->objName.'.align = \''.$this->align.'\';'."\n";
		$pWindow .= $this->indentation . '	'.$this->objName.'.vAlign = \''.$this->vAlign.'\';'."\n";		
		$pWindow .= $this->indentation . '</script>'."\n";
		
		if ($verbose) {
			echo $pWindow;
		}
		
		return $pWindow;
	}
	
	/**
	 * Gera o código HTML da janela
	 *
	 * @param verbose boolean: quando true imprime o retorno
	 * @param
	 *
	 * @returns string;
	 */
	public function draw($verbose, $htmlContent) {
		
		$this->getObjName();
		
		$pWindow  = $this->drawTop(false);
		$pWindow .= $htmlContent."\n";
		$pWindow .= $this->drawFooter(false);
		
		if ($verbose) {
			echo $pWindow;
		}
		
		return $pWindow;
	}
}

