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
 * PrumoWindow é uma janela popuo usando divs 
 */
class PrumoWindow
{
    use PGetName;
    
    public $ind;
    
    // client property
    public $width;
    public $title;
    public $align;
    public $vAlign;
    
    public $showBtClose;
    public $commandClose;
    
    public $jsAfterHide = null;
    
    /**
     * Construtor da classe PrumoWindow
     *
     * @param $objName string: nome do objeto
     */
    function __construct(string $objName='')
    {
        $this->name        = $objName;
        $this->width       = 930;
        $this->title       = 'PrumoWindow';
        $this->align       = 'center';
        $this->vAlign      = 'top';
        $this->ind         = '';
        $this->showBtClose = true;
    }
    
    /**
     * pega o comando javascript para fechar a janela
     *
     * @return string: código js
     */
    protected function getCommandClose() : string
    {
        if (empty($this->commandClose)) {
            $this->commandClose = $this->getObjName().'.hide()';
        }
        return $this->commandClose;
    }
    
    /**
     * Gera o código HTML do topo da janela
     *
     * @param verbose boolean: quando true imprime o retorno
     *
     * @returns string;
     */
    public function drawTop(bool $verbose=true) : string
    {
        $this->getObjName();
        
        $htmlObjName = $this->getObjName();
        $htmlCommandClose = $this->getCommandClose();
        $htmlDisplayBtClose = $this->showBtClose == false ? 'style="display:none"' : '';
        
        $pWindow = <<<HTML
        {$this->ind}<div class="prumoVeil" id="{$htmlObjName}_veil"></div>
        {$this->ind}<div class="prumoWindow" id="$htmlObjName">
        {$this->ind}    <div class="prumoWindowTitle" id="{$htmlObjName}_titleBar">
                
        {$this->ind}        <div class="prumoWindowClose" id="{$htmlObjName}_close"$htmlDisplayBtClose>
        {$this->ind}            <a href="javascript:$htmlCommandClose">
        {$this->ind}                <img src="{$GLOBALS['pConfig']['prumoWebPath']}/images/close.png" alt="[X]" />
        {$this->ind}            </a>
        {$this->ind}        </div>
                    
        {$this->ind}        <div id="{$htmlObjName}_title" class="prumoWindowTitle" onmousedown="$htmlObjName.move()" onmouseup="$htmlObjName.dropMove()">titulo</div>
        {$this->ind}        <div id="{$htmlObjName}_loading" class="prumoWindowLoading"><img src="{$GLOBALS['pConfig']['prumoWebPath']}/images/loading.gif" alt="" /></div>
        {$this->ind}    </div>
        {$this->ind}    <div class="prumoWindowBody">
        
        HTML;
        
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
    public function drawFooter(bool $verbose=true) : string
    {
        $htmlObjName = $this->getObjName();
        
        $pWindow = <<<HTML
        {$this->ind}    </div>
        {$this->ind}</div>
        {$this->ind}
        {$this->ind}<script type="text/javascript">
        {$this->ind}    $htmlObjName = new PrumoWindow('$htmlObjName');
        {$this->ind}    $htmlObjName.width = {$this->width};
        {$this->ind}    $htmlObjName.title = '{$this->title}';
        {$this->ind}    $htmlObjName.align = '{$this->align}';
        {$this->ind}    $htmlObjName.vAlign = '{$this->vAlign}';
        {$this->ind}    $htmlObjName.afterHide = function() { {$this->jsAfterHide} }
        {$this->ind}    document.pWindow.push($htmlObjName);
        {$this->ind}</script>
        
        HTML;
        
        if ($verbose) {
            echo $pWindow;
        }
        
        return $pWindow;
    }
    
    /**
     * Gera o código HTML da janela
     *
     * @param verbose boolean: quando true imprime o retorno
     * @param htmlContent string: conteúdo HTML
     *
     * @returns string;
     */
    public function draw(bool $verbose, string $htmlContent) : string
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

