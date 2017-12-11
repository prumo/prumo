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
        
        if (isset($this->param['fastcreate']) and $this->param['fastcreate']) {
            $lines++;
        }
        
        parent::constructGrid($lines);
    }
    
    /**
     * Adiciona um campo onde o registro escolhido deve ser retornado
     *
     * @param $fieldName string: nome do campo
     * @param $idReturn string: id do input html. Quando não informado, copia do $fieldName
     * @param $verbose boolean: quando true imprime o código gerado na tela
     * @param $linkInput boolean: sem efeito, apenas para manter a compatibilidade com o prumoSearch
     * @param $noRetrieve boolean: sem efeito, apenas para manter a compatibilidade com o prumoSearch
     *
     * @return string: código javascript gerado
     */
    public function addFieldReturn($fieldName, $idReturn='', $verbose=true, $linkInput=true, $noRetrieve=false)
    {
        if (empty($idReturn)) {
            $idReturn = $fieldName;
        }
        
        // valida duplicidade
        for ($i = 0; $i < count($this->fieldReturn); $i++) {
            if ($this->fieldReturn[$i][0] == $fieldName) {
                $msg = _('Campo ":fieldName:" duplicado em :objName:->addFieldReturn.');
                $msg = str_replace(':fieldName:', $fieldName, $msg);
                $msg = str_replace(':objName:', $this->name, $msg);
                throw new Exception($msg);
            }
        }
        
        $this->fieldReturn[] = array($fieldName, $idReturn);
        
        $field = $this->fieldByName($fieldName);
        $fieldType = $field['type'];
        
        $fieldReturn  = $this->ind.'<script type="text/javascript">'."\n";
        $fieldReturn .= $this->ind. '    '.$this->name.'.addFieldReturn(\''.$fieldName.'\',\''.$idReturn.'\',\''.$fieldType.'\''.", false);\n";
        $fieldReturn .= $this->ind.'</script>'."\n";
        
        if ($verbose) {
            echo $fieldReturn;
        }
        
        return $fieldReturn;
    }
    
    /**
     * Inicializa o objeto no lado do cliente
     *
     * @return string: código gerado
     */
    private function initClientObject()
    {
        // instancia o objeto PrumoCrudList no cliente
        $clientObject  = $this->ind. '<script type="text/javascript">'."\n";
        $clientObject .= $this->ind. '    '.$this->name.' = new PrumoCrudList(\''.$this->name.'\',\''.$this->ajaxFile.'\');'."\n";
        $clientObject .= $this->ind. '    '.$this->name.'.objName = \''.$this->name.'\';'."\n";
        $clientObject .= $this->ind. '    '.$this->name.'.parent = '.$this->param['crudname'].';'."\n";
        
        // repassa condicionalmente o pog debug para o objeto ajax
        if (isset($this->param['debug']) and $this->param['debug']) {
            $clientObject .= $this->ind. '    '.$this->name.'.pAjax.debug = true;'."\n";
        }
        
        // repassa parametro auto click
        $clientObject .= $this->ind . '    '. $this->name.'.autoClick = ';
        $clientObject .= (isset($this->param['autoclick']) and $this->param['autoclick']) ? 'true;'."\n" : 'false;'."\n";
        
        //fastCreate
        if (isset($this->param['fastcreate']) and $this->param['fastcreate']) {
            $clientObject .= $this->ind . '    '. $this->name.'.fastCreate = true;'."\n";
        }
        
        //fastUpdate
        if (isset($this->param['fastupdate']) and $this->param['fastupdate']) {
            $clientObject .= $this->ind . '    '. $this->name.'.fastUpdate = true;'."\n";
        }
        
        //fastDelete
        if (isset($this->param['fastdelete']) and $this->param['fastdelete']) {
            $clientObject .= $this->ind . '    '. $this->name.'.fastDelete = true;'."\n";
        }
        
        $clientObject .= $this->ind. '</script>'."\n";
        
        return $clientObject;
    }
    
    /**
     * Gera o botão 'Inserir novo' para ser adicionado ao lado dos filtros
     *
     * @return string: código html do botão
     */
    protected function makeShortcut()
    {
        if (! isset($this->param['routine']) || empty($this->param['routine']) || pPermitted($this->param['routine'], 'c')) {
            $onClick = $this->pFilter->btNew = $this->param['crudname'].'.bt_new()';
            return $this->ind.'                    <button class="pButton" id="'.$this->name.'_btNew" onclick="'.$onClick.'">'._('Inserir Novo').'</button>'."\n";
        } else {
            return 'aa';
        }
    }
    
    /**
     * Gera o código HTML completo
     *
     * @param verbose boolean: quando true imprime o código
     *
     * @return string: código gerado
     */
    public function draw($verbose)
    {
        // adiciona uma columa a mais para os controles do fastCreate, fastupdate ou fastdelete
        if (
            (isset($this->param['fastcreate']) and $this->param['fastcreate']) or
            (isset($this->param['fastupdate']) and $this->param['fastupdate']) or
            (isset($this->param['fastdelete']) and $this->param['fastdelete'])
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
    private function makeCrudLink()
    {
        $htmlCrudLink  = $this->ind.'        <script type="text/javascript">'."\n";
        $htmlCrudLink .= $this->ind.'            '.$this->param['crudname'].'.pCrudList = '.$this->name.';'."\n";
        $htmlCrudLink .= $this->ind.'        </script>'."\n";
        
        return $htmlCrudLink;
    }
}
