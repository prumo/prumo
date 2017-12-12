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
 * PrumoQueueSet é tabset com 1 ou mais listagems PrumoQueue
 */
class PrumoQueueSet
{
    use PGetName;
    
    private $ind;
    private $pQueueName;
    private $pQueueLabel;
    private $pQueueFilename;
    private $pQueueType;
    
    public $classBt;
    public $classBtFocus;
    
    /**
     * Construtor da classe PrumoQueueSet
     */
    function __construct()
    {
        $this->pQueueName = array();
        $this->pQueueLabel = array();
        $this->pQueueFilename = array();
        $this->pQueueType = array();
        $this->ind = '';
        $this->classBt = 'pQueueSetBt';
        $this->classBtFocus = 'pQueueSetBtFocus';
    }
    
    /**
     * Adiciona uma fila
     *
     * @param $pQueueName string: nome da fila (não pode conter espaços)
     * @param $pQueueLabel string: Rótulo do tabset (pode conter espaços e acentos)
     * @param $pQueueFilename string: nome do arquivo a ser incluído na tab (opcional)
     * @param $routine string: quando informado, mostra a tab apenas quando o usuário logado tem permissão para a rotina
     */
    public function addQueue($pQueueName, $pQueueLabel, $pQueueFilename='', $routine='')
    {
        if (pPermitted($routine)) {
            $this->pQueueName[] = $pQueueName;
            $this->pQueueLabel[] = $pQueueLabel;
            $this->pQueueFilename[] = $pQueueFilename;
            $this->pQueueType[] = 'PrumoQueue';
        }
    }
    
    /**
     * Adiciona um novo tab, semelhando ao addQueue
     *
     * @param $pQueueName string: nome da fila (não pode conter espaços)
     * @param $pQueueLabel string: Rótulo do tabset (pode conter espaços e acentos)
     * @param $fileName string: nome do arquivo a ser incluído na tab (opcional)
     * @param $routine string: nome da routine para verificar as permissões
     */
    public function addTab($pQueueName, $pQueueLabel, $fileName='', $routine='')
    {
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
    public function makeHtml()
    {
        echo $this->ind.'<fieldset class="pQueueSet">'."\n";
        
        // adiciona os botões
        echo $this->ind.'<legend>';
        for ($i = 0; $i < count($this->pQueueLabel); $i++) {
            
            echo "\n";
            $onclick = $this->pQueueName[$i].'.lineClick(0)';
            
            if ($this->pQueueType[$i] == 'PrumoQueue') {
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
        for ($i = 0; $i < count($this->pQueueName); $i++) {
            
            if ($this->pQueueType[$i] == 'PrumoQueue') {
                
                $pQueue = $this->pQueueName[$i];
                global $$pQueue;
                require($this->pQueueFilename[$i]);
                
                if ($i > 0) {
                    echo $this->ind.'<script type="text/javascript">'."\n";        
                    echo $this->ind.'    document.getElementById(\'div_'.$this->pQueueName[$i].'\').style.display = \'none\';'."\n";
                    echo $this->ind.'</script>'."\n";
                }
            }
            
            if ($this->pQueueType[$i] == 'tab') {
                
                echo '<div id="div_'.$this->pQueueName[$i].'">'."\n";
                
                if (! empty($this->pQueueFilename[$i])) {
                    include $this->pQueueFilename[$i];
                }
                
                echo '</div>'."\n";
            }
        }
        
        echo $this->ind.'</fieldset>'."\n";
        
        // adiciona javascript que coloca a quantidade de itens da fila no label
        echo $this->ind.'<script type="text/javascript">'."\n";
        
        for ($i = 0; $i < count($this->pQueueName); $i++) {
            
            if ($this->pQueueType[$i] == 'PrumoQueue') {
                
                echo $this->ind.'    '.$this->pQueueName[$i].'.afterList = function() {'."\n";
                echo $this->ind.'        if (this.pGridNavigation.count == 0) {'."\n";
                echo $this->ind.'            document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$i].'\').innerHTML = \''.$this->pQueueLabel[$i].'\';'."\n";
                echo $this->ind.'        } else {'."\n";
                echo $this->ind.'            document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$i].'\').innerHTML = \''.$this->pQueueLabel[$i].' (\'+this.pGridNavigation.count+\')\';'."\n";
                echo $this->ind.'        }'."\n";
                echo $this->ind.'    }'."\n";
                echo $this->ind."\n";
            }
            
            echo $this->ind.'    function '.$this->getObjName().'BtMouseover'.$i.'() {'."\n";
            for ($j=0; $j < count($this->pQueueName); $j++) {
                
                if ($j == $i) {
                    echo $this->ind.'        document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').style.fontWeight = \'bold\';'."\n";
                    echo $this->ind.'        document.getElementById(\'div_'.$this->pQueueName[$j].'\').style.display = \'block\';'."\n";
                    echo $this->ind.'        document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').className = \'pButton-outline active\';'."\n";
                } else {
                    echo $this->ind.'        document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').style.fontWeight = \'normal\';'."\n";
                    echo $this->ind.'        document.getElementById(\'div_'.$this->pQueueName[$j].'\').style.display = \'none\';'."\n";
                    echo $this->ind.'        document.getElementById(\''.$this->getObjName().'_bt_'.$this->pQueueName[$j].'\').className = \'pButton-outline\';'."\n";
                }
            }
            
            if ($this->pQueueType[$i] == 'PrumoQueue') {
                echo $this->ind.'        '.$this->pQueueName[$i].'.pFilter.focus();'."\n";
            }
            
            echo $this->ind.'    }'."\n";
            echo $this->ind."\n";
        }
        
        echo $this->ind.'</script>'."\n";
    }
    
    /**
     * Gera um javascript que dispara o goSearch() em todos os objetos PrumoQueue filhos
     */
    public function goSearchAll()
    {
        echo $this->ind.'<script type="text/javascript">'."\n";
        
        for ($i = 0; $i < count($this->pQueueName); $i++) {
            if ($this->pQueueType[$i] == 'PrumoQueue') {
                echo $this->ind.$this->pQueueName[$i].'.goSearch();    '."\n";
            }
        }
        
        echo $this->ind.'</script>'."\n";
    }
}
