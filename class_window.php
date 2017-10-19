<?php

/* *********************************************************************
 *
 *  Prumo Framework para PHP é um framework vertical para
 *  desenvolvimento rápido de sistemas de informação web.
 *  Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *  e Odair Rubleski <orubleski@gmail.com>
 *
 *  This file is part of Prumo.
 *
 *  Prumo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3, or (at your option)
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

/**
 * PrumoWindow é uma janela popuo usando divs 
 */
class PrumoWindow
{
    private $objName;
    public $ind;
    
    // client property
    public $width;
    public $title;
    public $align;
    public $vAlign;
    
    public $showBtClose;
    public $commandClose;
    
    /**
     * Construtor da classe PrumoWindow
     *
     * @param $objName string: nome do objeto
     */
    function __construct($objName='')
    {
        $this->objName     = $objName;
        $this->width       = 930;
        $this->title       = 'PrumoWindow';
        $this->align       = 'center';
        $this->vAlign      = 'top';
        $this->ind = '';
        $this->showBtClose   = true;
    }
    
    /**
     * Retorna o nome da instância
     *
     * @return string
     */
    public function getObjName()
    {
        if (! isset($this->objName) or $this->objName == '') {
            
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
    public function drawTop($verbose=true)
    {
        $this->getObjName();
        
        $pWindow  = $this->ind . '<div class="prumoVeil" id="'.$this->objName.'_veil"></div>'."\n";
        $pWindow .= $this->ind . '<div class="prumoWindow" id="'.$this->objName.'">'."\n";
        $pWindow .= $this->ind . '    <div class="prumoWindowTitle" id="'.$this->objName.'_titleBar">'."\n";
        
        $pWindow .= $this->ind . '        <div class="prumoWindowClose" id="'.$this->objName.'_close"' . ($this->showBtClose == false ? 'style="display:none"' : '') . '>'."\n";
        $pWindow .= $this->ind . '            <a href="javascript:'.$this->commandClose.'">'."\n";
        $pWindow .= $this->ind . '                <img src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/close.png" alt="[X]" />'."\n";
        $pWindow .= $this->ind . '            </a>'."\n";
        $pWindow .= $this->ind . '        </div>'."\n";
            
        $pWindow .= $this->ind . '        <div id="'.$this->objName.'_title" class="prumoWindowTitle" onmousedown="'.$this->objName.'.move()" onmouseup="'.$this->objName.'.dropMove()">titulo</div>'."\n";
        $pWindow .= $this->ind . '        <div id="'.$this->objName.'_loading" class="prumoWindowLoading"><img src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/loading.gif" alt="" /></div>'."\n";
        $pWindow .= $this->ind . '    </div>'."\n";
        $pWindow .= $this->ind . '    <div class="prumoWindowBody">'."\n";
        
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
    public function drawFooter($verbose=true)
    {
        $this->getObjName();
        
        $pWindow  = $this->ind . '    </div>'."\n";
        $pWindow .= $this->ind . '</div>'."\n";
        
        $pWindow .= $this->ind . '<script type="text/javascript">'."\n";
        $pWindow .= $this->ind . '    '.$this->objName.' = new PrumoWindow(\''.$this->objName.'\');'."\n";
        $pWindow .= $this->ind . '    '.$this->objName.'.width = '.$this->width.';'."\n";
        $pWindow .= $this->ind . '    '.$this->objName.'.title = \''.$this->title.'\';'."\n";
        $pWindow .= $this->ind . '    '.$this->objName.'.align = \''.$this->align.'\';'."\n";
        $pWindow .= $this->ind . '    '.$this->objName.'.vAlign = \''.$this->vAlign.'\';'."\n";        
        $pWindow .= $this->ind . '</script>'."\n";
        
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
    public function draw($verbose, $htmlContent)
    {
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

