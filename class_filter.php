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
 * PrumoFilter é um sistema de filtros genérico que serve para PrumoSearch, PrumoCrudList e PrumoQueue
 */
class PrumoFilter
{
    
    private $parentName;
    private $jsName;
    private $htmlId;
    private $ind = '';
    private $countVisible;
    
    public $field;
    public $pConfig;
    public $shortcut;
    
    /**
     * Construtor da classe PrumoFilter
     *
     * @param $parentName string: nome do objeto pai, usado para manipular os objetos em js
     * @param $field array: parametros do field
     */
    function __construct(string $parentName, array $field)
    {
        global $pConfig;
        $this->pConfig = $pConfig;
        
        $this->parentName = $parentName;
        $this->jsName = "$parentName.pFilter";
        $this->htmlId = str_replace('.', '_', $this->jsName);
        
        $this->field = $field;
        $this->shortcut = '';
    }
    
    /**
     * Seta a indentação para organizar o código gerado no lado do cliente
     *
     * @param $ind string: tabs para indentação no lado do cliente
     */
    public function setIndentarion(string $ind)
    {
        $this->ind = $ind;
    }
    
    /**
     * Quantidade de campos
     *
     * @return integer: quantidade de campos
     */
    public function fieldCount() : int
    {
        return count($this->field);
    }
    
    /**
     * Gera o HTML e JS dos filtros
     *
     * @param verbose boolean: quando true imprime o código
     *
     * @return string: código HTML e JS
     */
    public function draw(bool $verbose) : string
    {
        $htmlFilters = $this->makeHtml();
        $htmlFilters .= $this->makeJs();
    
        if ($verbose) {
            echo $htmlFilters;
        }
        
        return $htmlFilters;
    }
    
    /**
     * Gera o HTML dos filtros
     *
     * @param verbose boolean: quando true imprime o código
     *
     * @return string: código HTML
     */
    public function makeHtml() : string
    {
        $htmlFilters  = "{$this->ind}<div class=\"prumoFilter\" align=\"center\" id=\"{$this->htmlId}\">\n";
        $htmlFilters .= "{$this->ind}\t<table>\n";
        $htmlFilters .= "{$this->ind}\t\t<tr>\n";
        $htmlFilters .= "{$this->ind}\t\t\t<td id=\"{$this->htmlId}_filters\" style=\"text-align:left\">\n";
        $htmlFilters .= "{$this->ind}\t\t\t</td>\n";
        $htmlFilters .= "{$this->ind}\t\t\t<td id=\"{$this->htmlId}_controls\">\n";
        $htmlFilters .= "{$this->ind}\t\t\t\t<div style=\"text-align:center;\">\n";
        $htmlFilters .= "{$this->ind}\t\t\t\t\t<button class=\"pButton\" id=\"{$this->parentName}_btSearch\" onclick=\"{$this->parentName}.cmdSearch()\">"._('Pesquisar')."</button>\n";
        $htmlFilters .= "{$this->ind}\t\t\t\t\t<button class=\"pButton\" id=\"{$this->parentName}_btSearchAll\" onclick=\"{$this->parentName}.cmdSearchAll()\">"._('Todos')."</button>\n";
        if (! empty($this->shortcut)) {
            $htmlFilters .= "{$this->ind}\t\t\t\t\t{$this->shortcut}\n";
        }
        $htmlFilters .= "{$this->ind}\t\t\t\t</div>\n";
        $htmlFilters .= "{$this->ind}\t\t\t</td>\n";
        $htmlFilters .= "{$this->ind}\t\t</tr>\n";
        $htmlFilters .= "{$this->ind}\t</table>\n";
        $htmlFilters .= "{$this->ind}</div>\n";
        
        return $htmlFilters;
    }
    
    /**
     * Gera o JS dos filtros
     *
     * @param verbose boolean: quando true imprime o código
     *
     * @return string: código JS
     */
    private function makeJs() : string
    {
        $jsFilters  = "{$this->ind}<script type=\"text/javascript\">\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName} = new PrumoFilter('{$this->jsName}', '{$GLOBALS['pConfig']['useSimilaritySearch']}');\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName}.htmlId = '{$this->htmlId}';\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName}.prumoWebPath = '{$GLOBALS['pConfig']['prumoWebPath']}';\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName}.parent = {$this->parentName};\n";
        
        // passa informação dos fields do servidor para o filter
        $filterName = '';
        $filterLabel = '';
        $filterType = '';
        
        for ($i = 0; $i < $this->fieldCount(); $i++) {
            
            $filterName .= "\"{$this->field[$i]['name']}\"";
            if ($i < $this->fieldCount() -1) {
                $filterName .= ',';
            }
            
            $filterLabel .= "\"{$this->field[$i]['label']}\"";
            if ($i < $this->fieldCount() -1) {
                $filterLabel .= ',';
            }
            
            $filterType .= "\"{$this->field[$i]['type']}\"";
            if ($i < $this->fieldCount() -1) {
                $filterType .= ',';
            }
        }
        
        $jsFilters .= "{$this->ind}\t{$this->jsName}.fieldName  = new Array($filterName);\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName}.fieldLabel = new Array($filterLabel);\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName}.fieldType  = new Array($filterType);\n";
        $jsFilters .= "{$this->ind}\t{$this->jsName}.draw();\n";
        $jsFilters .= "{$this->ind}\tdocument.pFilter.push({$this->jsName});\n";
        $jsFilters .= "{$this->ind}</script>\n";
        
        return $jsFilters;
    }
    
    /**
     * Prepara a carga de uma consulta
     */
    public function loadQuery()
    {
        $this->filter = array();
        $this->filter['fieldName'] = isset($_POST['fField']) ? $_POST['fField'] : array();
        
        //adiciona um filtro vazio e visivel caso não haja nenhum
        if (count($this->filter['fieldName']) == 0) {
            $this->filter['fieldName'][0] = $this->field[0]['name'];
            $this->filter['operator'][0]  = '';
            $this->filter['value'][0]     = '';
            $this->filter['value2'][0]    = '';
            $this->filter['visible'][0]   = 'true';
        } else {
            $this->filter['operator']  = $_POST['fOperator'];
            $this->filter['value']     = $_POST['fValue'];
            $this->filter['value2']    = $_POST['fValue2'];
            $this->filter['visible']   = $_POST['fVisible'];
        }
        
        //conta filtros visíveis
        $this->countVisible = 0;
        for ($i = 0; $i < count($this->filter['visible']); $i++) {
            if ($this->filter['visible'][$i] != 'false' && $this->filter['visible'][$i] != false) {
                $this->countVisible++;
            }
        }
        
        //se não possui nenhum filtro visível adiciona
        if ($this->countVisible == 0) {
            $nextFilterIndex = count($this->filter['fieldName']);
            $this->filter['fieldName'][$nextFilterIndex] = $this->field[0]['name'];
            $this->filter['operator'][$nextFilterIndex]  = '';
            $this->filter['value'][$nextFilterIndex]     = '';
            $this->filter['value2'][$nextFilterIndex]    = '';
            $this->filter['visible'][$nextFilterIndex]   = true;
        }
    }
    
    /**
     * Gera o código XML dos filtros
     *
     * @return string: código XML
     */
    public function makeXmlFilter() : string
    {
        $this->loadQuery();
        $xmlFilters = '';
        for ($i = 0; $i < count($this->filter['fieldName']); $i++) {
            $xmlFilter  = '<fieldName>'.$this->formatXmlData($this->filter['fieldName'][$i]).'</fieldName>'."\n";
            $xmlFilter .= '<operator>'.$this->formatXmlData($this->filter['operator'][$i]).'</operator>'."\n";
            $xmlFilter .= '<value>'.$this->formatXmlData($this->filter['value'][$i]).'</value>'."\n";
            $xmlFilter .= '<value2>'.$this->formatXmlData($this->filter['value2'][$i]).'</value2>'."\n";
            $xmlFilter .= '<visible>'.$this->formatXmlData($this->filter['visible'][$i]).'</visible>';
            $xmlFilter = pXmlAddParent($xmlFilter,'pFilter');
            $xmlFilters .= $xmlFilter;
        }
        return $xmlFilters;
    }
    
    /**
     * Formata dados XML (substitui vazio por NULL)
     *
     * @param $text string: texto a ser formatado
     *
     * @return string: xml formatado
     */
    private function formatXmlData(string $text) : string
    {
        $formatedText = $text;
        $formatedText = str_replace('&', '&amp;', $formatedText);
        
        return $text == '' ? 'NULL' : $formatedText;
    }
    
}

