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
 * Classe básica que contém a estrutura mais simples dos CRUD com os parâmetros básicos do objeto e os fields
 */
class PrumoBasic
{
    use PGetName;
    
    public $field = array();
    public $error = '';
    public $param;
    
    protected $pConnection;
    protected $ajaxFile;
    protected $ind = '';
    
    /**
     * Construtor da classe PrumoBasic
     *
     * @param $params string: parâmetros principais
     */
    function __construct($params)
    {
        global $pConnection;
        require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_connection.php';
        
        $this->pConnection = $pConnection;
        
        $this->param = pParameters($params);
        $this->param['schema'] = isset($this->param['schema']) ? $this->param['schema'] : $GLOBALS['pConfig']['appSchema'];
        $this->ajaxFile = $this->getAjaxFileName();
        $this->name = isset($this->param['objname']) ? $this->param['objname'] : '';
    }
    
    /**
     * Pega o nome do arquivo XML (controller da aplicação)
     */
    private function getAjaxFileName()
    {
        $files = get_included_files();
        $lastInclusion = array_pop($files);
        
        if (! isset($this->param['xmlfile'])) {
            if (dirname($_SERVER["SCRIPT_FILENAME"]) == dirname($lastInclusion)) {
                $this->param['xmlfile'] = basename($lastInclusion);
            } else {
                $this->param['xmlfile'] = $GLOBALS['pConfig']['appWebPath'] . str_replace($GLOBALS['pConfig']['appPath'], '', $lastInclusion);
            }
        }
        
        // transforma o caminho do arquivo ajax de relatovo para absoluto
        $ajaxFileName = substr($this->param['xmlfile'], 0, 1) == '/' ? $this->param['xmlfile'] : $GLOBALS['pConfig']['appWebPath'].'/'.$this->param['xmlfile'];
        
        return $ajaxFileName;
    }
    
    /**
     * Quantidade de campos
     *
     * @return integer: quantidade de campos
     */
    public function fieldCount()
    {
        return count($this->field);
    }
    
    /**
     * Adiciona um campo
     *
     * @param $params array: array associativo com os parâmetros do campo
     *
     * @return array: campo formatado
     */
    public function addField($params)
    {
        $this->getObjName();
    
        $param   = pParameters($params);
        $name    = $param['name'];
        $label   = isset($param['label']) ? $param['label'] : $param['name'];
        $size    = isset($param['size']) ? $param['size'] : null;
        $default = isset($param['default']) ? $param['default'] : null;
        $type    = isset($param['type']) ? $param['type'] : 'string';
        $notNull = (isset($param['notnull']) && $param['notnull']);
        $visible = (! isset($param['visible']) || $param['visible'] != 'false');
        
        $field = array(
                    'name' => $name,
                    'label' => $label,
                    'size' => $size,
                    'default' => $default,
                    'type' => $type,
                    'notnull' => $notNull,
                    'visible' => $visible
                 );
        
        if (isset($param['search'])) {
            $field['search'] = $param['search'];
        }
        
        $this->field[] = $field;
        
        return $field;
    }
    
    /**
     * Pega um campo pelo nome
     *
     * @param $name string: nome do campo
     *
     * @return array: campo completo
     */
    public function fieldByName($name)
    {
        for ($i = 0; $i < $this->fieldCount(); $i++) {
            if ($this->field[$i]['name'] == $name) {
                return $this->field[$i];
            }
        }
        
        return null;
    }
    
    /**
     * Adiciona um filtro no pFilter no lado do cliente (javascript)
     *
     * @param $fieldName string: nome do campo
     * @param $filterOperator string: operador (verificar operadores do banco em class_pg_connection.php)
     * @param $fieldValue string: valor
     */
    public function setFilter($fieldName, $filterOperator, $fieldValue, $fieldValue2='')
    {
        echo '<script type="text/javascript">'."\n";
        echo "    ".$this->name.".setFilter('$fieldName', '$filterOperator', '$fieldValue', '$fieldValue2');\n";
        echo '</script>'."\n";
    }
    
    /**
     * Adiciona um filtro invisibel no pFilter no lado do cliente (javascript)
     *
     * @param $fieldName string: nome do campo
     * @param $filterOperator string: operador (verificar operadores do banco em class_pg_connection.php)
     * @param $fieldValue string: valor
     */
    public function setInvisibleFilter($fieldName, $filterOperator, $fieldValue, $fieldValue2='')
    {
        echo '<script type="text/javascript">'."\n";
        echo "    ".$this->name.".setInvisibleFilter('$fieldName', '$filterOperator', '$fieldValue', '$fieldValue2');\n";
        echo '</script>'."\n";
    }
    
    /**
     * Seta a indentação para organizar o código gerado no lado do cliente
     *
     * @param $ind string: tabs para indentação no lado do cliente
     */
    public function setIndentation($ind)
    {
        $this->ind = $ind;
    }
}

