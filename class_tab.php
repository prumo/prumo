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
 * Um tabset genérico
 */
class PrumoTab
{
    private $name;
    private $tab;
    private $tabLabel;
    private $tabInclude;
    private $tabHtml;
    
    public $ind;
    public $visible;
    
    /**
     * Constritor da classe PrumoTab
     */
    function __construct()
    {
        $this->visible = true;
    }
    
    /**
     * Adiciona um novo tab
     *
     * @param $tabName string: nome do tabset (não pode conter espaços)
     * @param $tabLabel string: Rótulo do tabset (pode conter espaços e acentos)
     * @param $include string: nome do arquivo a ser incluído na tab (opcional)
     * @param $html string: código html a ser incluído na tab (opcional)
     * @param $routine string: quando informado, mostra a tab apenas quando o usuário logado tem permissão para a rotina
     */
    public function addTab($tabName, $tabLabel, $include='', $html='', $routine='')
    {
        $this->getObjName();
        
        $this->tab[] = $tabName;
        $this->tabLabel[] = $tabLabel;
        $this->tabInclude[] = $include;
        $this->tabHtml[] = $html;
    }
    
    /**
     * Retorna o nome da instância
     *
     * @return string
     */
    public function getObjName()
    {
        if (! isset($this->name) or $this->name == '') {
            
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
     * Inicializa o objeto cliente
     */
    public function init()
    {
        $this->htmlOpen(true);
        
        for ($i = 0; $i < count($this->tab); $i++) {
            
            $this->htmlOpenTab(true,$this->tabLabel[$i]);
            
            if ($this->tabHtml[$i] != '') {
                echo $this->tabHtml[$i];
            }
            
            if ($this->tabInclude[$i] != '') {
                include($this->tabInclude[$i]);
            }
            
            $this->htmlCloseTab(true);
        }
        
        $this->htmlClose(true);
    }
    
    /**
     * Gera html do inicio do tabset incluindo os botões do topo
     *
     * @param verbose boolean: quando true imprime o retorno
     *
     * @return string;
     */
    public function htmlOpen($verbose)
    {
        require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php';
        
        $visible = $this->visible ? ' style="display:block"' : ' style="display:none"';
        
        $top = ''."\n";
        $top .= $this->ind.'<script type="text/javascript">'."\n";
        $top .= $this->ind.'    var '.$this->name.' = new PrumoTab(\''.$this->name.'\');'."\n";
        
        for ($i = 0; $i < count($this->tab); $i++) {
            $top .= $this->ind.'    '.$this->name.'.addTab(\''.$this->tab[$i].'\');'."\n";
        }

        $top .= $this->ind.'</script>'."\n";

        $top .= $this->ind.'<fieldset id="'.$this->name.'"'.$visible.'>'."\n";
        $top .= $this->ind.'    <legend>'."\n";
        
        for ($i = 0; $i < count($this->tab); $i++) {
            $top .= $this->ind.'        <button class="pButton-outline" id="'.$this->name.'_bt_'.$this->tab[$i].'" onClick="'.$this->name.'.showTab(\''.$this->tab[$i].'\')">'.$this->tabLabel[$i].'</button>'."\n";
        }
        
        $top .= $this->ind.'    </legend>'."\n";
        
        if ($verbose) {
            echo $top;
        }
        
        return $top;
    }
    
    /**
     * Gera html do fim do tabset
     *
     * @param verbose boolean: quando true imprime o retorno
     *
     * @return string;
     */
    public function htmlClose($verbose)
    {
        $html = $this->ind.'</fieldset>';
        
        if ($verbose) {
            echo $html;
        }
        
        return $html;
    }
    
    /**
     * Gera html do inicio do tab
     *
     * @param verbose boolean: quanto true imprime o retorno
     *
     * @returns string;
     */
    public function htmlOpenTab($verbose,$tabLabel)
    {
        for ($i = 0; $i < count($this->tab); $i++) {
            if ($this->tabLabel[$i] == $tabLabel) {
                $html = $this->ind.'        <div id="'.$this->name.'_tab_'.$this->tab[$i].'" style="display:none">'."\n";
            }
        }
        
        if ($verbose) {
            echo $html;
        }
        
        return $html;
    }
    
    /**
     * Gera html do fim do tab
     *
     * @param verbose boolean: quando true imprime o retorno
     *
     * @returns string;
     */
    public function htmlCloseTab($verbose)
    {
        $html = $this->ind.'        </div>'."\n";
        
        if ($verbose) {
            echo $html;
        }
        
        return $html;
    }
    
    /**
     * Gera um javascript que dispara o evento click em determinada tab
     *
     * @param verbose boolean: quando true imprime o retorno
     *
     * @returns string;
     */
    public function showTab($verbose, $tabName)
    {
        $js = ''."\n";
        $js .= $this->ind.'<script type="text/javascript">'."\n";
        $js .= $this->ind.'    '.$this->name.'.showTab(\''.$tabName.'\');'."\n";
        $js .= $this->ind.'</script>'."\n";
        
        if ($verbose) {
            echo $js;
        }
        
        return $js;
    }
}
