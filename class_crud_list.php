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
 * PrumoCrudList é a listagem de um objeto crud
 */
class PrumoCrudList extends PrumoSearch
{
    /**
     * Desenha o GRID
     *
     * @param $pageLines integer: numero de linhas do grid (quando não informado pega do arquivo de configuração)
     */
    protected function constructGrid($pageLines=false)
    {
		$lines = $pageLines ? $pageLines : $this->pageLines();
        
        if (isset($this->param['fastcreate']) && $this->param['fastcreate']) {
            $lines++;
        }
        
        parent::constructGrid($lines);
    }
    
    /**
     * Inicializa o objeto no lado do cliente
     *
     * @return string: código gerado
     */
    private function initClientObject() : string
    {
        // instancia o objeto PrumoCrudList no cliente
        $clientObject  = "{$this->ind}<script type=\"text/javascript\">\n";
        $clientObject .= "{$this->ind}\t{$this->name} = new PrumoCrudList('{$this->name}','{$this->ajaxFile}');\n";
        $clientObject .= "{$this->ind}\t{$this->name}.objName = '{$this->name}';\n";
        $clientObject .= "{$this->ind}\t{$this->name}.parent = {$this->param['crudname']};\n";
        
        // repassa condicionalmente o pog debug para o objeto ajax
        if (isset($this->param['debug']) && $this->param['debug']) {
            $clientObject .= "{$this->ind}\t{$this->name}.pAjax.debug = true;\n";
        }
        
        // repassa parametro auto click
        $clientObject .= "{$this->ind}\t{$this->name}.autoClick = ";
        $clientObject .= (isset($this->param['autoclick']) && $this->param['autoclick'] != 'false') ? "true;\n" : "false;\n";
        
        //fastCreate
        if (isset($this->param['fastcreate']) && $this->param['fastcreate']) {
            $clientObject .= "{$this->ind}\t{$this->name}.fastCreate = true;\n";
        }
        
        //fastUpdate
        if (isset($this->param['fastupdate']) && $this->param['fastupdate']) {
            $clientObject .= "{$this->ind}\t{$this->name}.fastUpdate = true;\n";
        }
        
        //fastDelete
        if (isset($this->param['fastdelete']) && $this->param['fastdelete']) {
            $clientObject .= "{$this->ind}\t{$this->name}.fastDelete = true;\n";
        }
        $clientObject .= "{$this->ind}\tdocument.pCrudList.push({$this->name});\n";
        $clientObject .= "{$this->ind}</script>\n";
        
        return $clientObject;
    }
    
    /**
     * Gera o botão 'Inserir novo' para ser adicionado ao lado dos filtros
     *
     * @return string: código html do botão
     */
    protected function makeShortcut() : string
    {
        if (! isset($this->param['routine']) || empty($this->param['routine']) || pPermitted($this->param['routine'], 'c')) {
            $onClick = $this->pFilter->btNew = $this->param['crudname'].'.bt_new()';
            return "{$this->ind}\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_btNew\" onclick=\"$onClick\">"._('Inserir Novo')."</button>\n";
        } else {
            return '';
        }
    }
    
    /**
     * Gera o código HTML completo
     *
     * @param verbose boolean: quando true imprime o código
     *
     * @return string: código gerado
     */
    public function draw(bool $verbose) : string
    {
        // adiciona uma columa a mais para os controles do fastCreate, fastupdate ou fastdelete
        if (
            (isset($this->param['fastcreate']) && $this->param['fastcreate']) ||
            (isset($this->param['fastupdate']) && $this->param['fastupdate']) ||
            (isset($this->param['fastdelete']) && $this->param['fastdelete'])
        ) {
            $this->pGrid->addColumn('name=prumoControls,label=,align=center');
        }
        
        // junta os objetos
        $pCrudListInit = $this->initClientObject();
        
        $pCrudList  = $this->makeFilters();
        $pCrudList .= $this->makeGrid();
        $pCrudList .= $this->makeGridNavigation();
        $pCrudList .= $this->makeCrudLink();
        
        $pCrudList = $pCrudListInit . $pCrudList;
        
        if ($verbose) {
            echo $pCrudList;
        }
        
        return $pCrudList;
    }
    
    /**
     * Cria o código JS que associa o crudList a CRUD
     *
     * @return string: código JS
     */
    private function makeCrudLink() : string
    {
        $htmlCrudLink  = "{$this->ind}\t\t<script type=\"text/javascript\">\n";
        $htmlCrudLink .= "{$this->ind}\t\t\t{$this->param['crudname']}.pCrudList = {$this->name};\n";
        $htmlCrudLink .= "{$this->ind}\t\t</script>\n";
        
        return $htmlCrudLink;
    }
}
