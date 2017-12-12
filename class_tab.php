<?php
/**
 * Copyright (c) 2010 Emerson Casas Salvador <salvaemerson@gmail.com> e Odair Rubleski <orubleski@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the “Software”), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 * 
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Um tabset genérico
 */
class PrumoTab
{
    use PGetName;
    
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
                include $this->tabInclude[$i];
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
        $visible = $this->visible ? ' style="display:block"' : ' style="display:none"';
        
        $top  = $this->ind.'<script type="text/javascript">'."\n";
        $top .= $this->ind.'    var '.$this->getObjName().' = new PrumoTab(\''.$this->getObjName().'\');'."\n";
        
        for ($i = 0; $i < count($this->tab); $i++) {
            $top .= $this->ind.'    '.$this->getObjName().'.addTab(\''.$this->tab[$i].'\');'."\n";
        }
        
        $top .= $this->ind.'</script>'."\n";
        $top .= $this->ind.'<fieldset id="'.$this->getObjName().'"'.$visible.'>'."\n";
        $top .= $this->ind.'    <legend>'."\n";
        
        for ($i = 0; $i < count($this->tab); $i++) {
            $top .= $this->ind.'        <button class="pButton-outline" id="'.$this->getObjName().'_bt_'.$this->tab[$i].'" onClick="'.$this->getObjName().'.showTab(\''.$this->tab[$i].'\')">'.$this->tabLabel[$i].'</button>'."\n";
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
                $html = $this->ind.'        <div id="'.$this->getObjName().'_tab_'.$this->tab[$i].'" style="display:none">'."\n";
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
        $js .= $this->ind.'    '.$this->getObjName().'.showTab(\''.$tabName.'\');'."\n";
        $js .= $this->ind.'</script>'."\n";
        
        if ($verbose) {
            echo $js;
        }
        
        return $js;
    }
}
