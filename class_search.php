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
 * PrumoSearch é o elemento de pesquisa onde o usuário consulta campos com chave estrangeira
 */
class PrumoSearch extends PrumoBasic
{
    private $fixedSqlSearch;
    private $constructedGrid;
    private $orderby;
    
    protected $pConnection;
    protected $pGrid;
    protected $fieldReturn;
    
    public $page;
    public $pFilter;
    public $autoFilter; // quando true (default), prepara um filtro autometicamente quando o usuário altera qualquer campo que participe do fieldReturn
    
    /**
     * Construtor da classe PrumoSearch
     */
    function __construct($params)
    {
        parent::__construct($params);
        
        $this->orderby = '';
        $this->constructedGrid = false;        
        $this->page = 1;
        $this->fieldReturn = array();
        $this->autoFilter = (!isset($this->param['autofilter']) or $this->param['autofilter'] != 'false');
    }
    
    /**
     * Permite ao desenvolvedor da aplicação explicitar qual PrumoConnection usar
     *
     * @param $connecion object: conexão com o banco de dados
     */
    public function setConnection($connecion)
    {
        $this->pConnection = $connecion;
    }
    
    /**
     * Desenha o GRID
     *
     * @param $pageLines integer: numero de linhas do grid (quando não informado pega do arquivo de configuração)
     */
    protected function constructGrid($pageLines=false)
    {
        $this->getObjName();
        $lines = $pageLines ? $pageLines : $this->pageLines();
        $this->pGrid = new PrumoGrid($this->name, $lines);
        $this->pGrid->ind = $this->ind . '        ';
        $this->pGrid->lineEventOnData = $this->name.'.lineClick(%)';
        $this->pGrid->pointerCursorOnData = true;
    }
    
    /**
     * Decide a quantidade de linhas do grid de acordo com os parametros e arquivo de configuração do framework
     *
     * @return integer: número de linhas do grid
     */
    protected function pageLines()
    {
        return isset($this->param['pagelines']) ? $this->param['pagelines'] : $GLOBALS['pConfig']['searchLines'];
    }
    
    /**
     * Adiciona um campo
     *
     * @param $params string: string de configuração do campo no formato do framework
     */
    public function addField($params)
    {
        if ($this->constructedGrid == false) {
            
            $this->constructedGrid = true;
            
            if (! isset($this->param['tablename'])) {
                $this->param['tablename'] = $this->name;
            }
            
            $this->constructGrid();
        }
        
        parent::addField($params);
        
        $this->pGrid->addColumn($params);
        $this->pFilter = new PrumoFilter($this->name, $this->field);
        $this->pFilter->setIndentarion($this->ind.'        ');
        
        $param = pParameters($params);
        
        $lastField = count($this->field)-1;
        
        $this->field[$lastField]['sqlname'] = isset($param['sqlname']) ? $param['sqlname'] : $param['name'];
        $this->field[$lastField]['pk'] = isset($param['pk']) ? true : false;
    }
    
    /**
     * Adiciona um campo onde o registro escolhido deve ser retornado
     *
     * @param $fieldName string: nome do campo
     * @param $idReturn string: id do input html. Quando não informado, copia do $fieldName
     * @param $verbose boolean: quando true imprime o código gerado na tela
     * @param $linkInput boolean: quando true vincula o campo HTML com o search
     * @param $noRetrieve boolean: quando true não participa do retrieve (busca implicita disparada pelo crud pai)
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
        $noRetrieve = $noRetrieve ? 'true' : 'false';
        
        $fieldReturn  = $this->ind.'<script type="text/javascript">'."\n";
        $fieldReturn .= $this->ind. '    '.$this->name.'.addFieldReturn(\''.$fieldName.'\',\''.$idReturn.'\',\''.$fieldType.'\', '.$noRetrieve.");\n";
        if ($linkInput and $this->autoFilter) {
            $fieldReturn .= $this->ind. '    inputField = document.getElementById(\''.$idReturn.'\');'."\n";
            $fieldReturn .= $this->ind. '    inputField.pSearch = '.$this->name.';'."\n";
            $fieldReturn .= $this->ind. '    '."\n";
            
            $fieldReturn .= $this->ind. '    if (inputField.getAttribute(\'onFocus\') == null) {'."\n";
            $fieldReturn .= $this->ind. '        inputField.setAttribute(\'onFocus\',\'this.pSearch.fieldFocus(this)\');'."\n";
            $fieldReturn .= $this->ind. '    }'."\n";
            $fieldReturn .= $this->ind. '    '."\n";
            
            $fieldReturn .= $this->ind. '    if (inputField.getAttribute(\'onBlur\') == null) {'."\n";
            $fieldReturn .= $this->ind. '        inputField.setAttribute(\'onBlur\',\'this.pSearch.fieldBlur(this)\');'."\n";
            $fieldReturn .= $this->ind. '    }'."\n";
            
            $fieldReturn .= $this->ind. '    '."\n";
            $fieldReturn .= $this->ind. '    if (inputField.getAttribute(\'onKeyDown\') == null) {'."\n";
            $fieldReturn .= $this->ind. '        inputField.setAttribute(\'onKeyDown\',\'this.pSearch.fieldKeyDown(event)\');'."\n";
            $fieldReturn .= $this->ind. '    }'."\n";
        }
        
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
        // instancia o objeto PrumoSearch no cliente
        $clientObject = $this->ind. '<script type="text/javascript">'."\n";
        $clientObject .= $this->ind. '    '.$this->name.' = new PrumoSearch(\''.$this->name.'\',\''.$this->ajaxFile.'\');'."\n";
        // repassa condicionalmente o debug para o objeto ajax
        if (isset($this->param['debug']) && $this->param['debug']) {
            $clientObject .= $this->ind. '    '.$this->name.'.pAjax.debug = true;'."\n";
        }

        // repassa fields para o objeto cliente
        $fieldName = '';
        for ($i = 0; $i < count($this->field); $i++) {
            if ($fieldName != '')
                $fieldName .= ',';
            $fieldName .= '"'.$this->field[$i]['name'].'"';
        }
        
        $fieldPk = '';
        for ($i = 0; $i < count($this->field); $i++) {
            
            if ($fieldPk != '') {
                $fieldPk .= ',';
            }
            
            $fieldPk .= $this->field[$i]['pk'] ? 'true' : 'false';
        }
        
        $clientObject .= $this->ind.'    '.$this->name.'.fieldName = Array('.$fieldName.');'."\n";
        $clientObject .= $this->ind.'    '.$this->name.'.fieldPk = Array('.$fieldPk.');'."\n";
        
        $clientObject .= $this->ind. '</script>'."\n";
        
        return $clientObject;
    }
    
    /**
     * Gera o código HTML dos filtros
     *
     * @return string: código HTML dos filtros
     */
    protected function makeFilters()
    {
        $this->pFilter->shortcut = $this->makeShortcut();
        
        $htmlFilters = $this->pFilter->draw(false);
        
        // vinculo com o PrumoFilter
        $htmlFilters .= $this->ind. '        <script type="text/javascript">'."\n";
        $htmlFilters .= $this->ind. '            '.$this->name.'.pFilter = pFilter_'.$this->name.";\n";
        $htmlFilters .= $this->ind. '        </script>'."\n";
        
        return $htmlFilters;
    }
    
    /**
     * Gera o código HTML do grid
     *
     * @return string: código HTML do grid
     */
    protected function makeGrid()
    {
        $htmlGrid = $this->pGrid->draw(false);
        
        // passa informação dos fields do servidor para o grid
        $htmlGrid .= $this->ind.'        <script type="text/javascript">'."\n";
        
        $htmlGrid .= $this->ind. '            pGrid_'.$this->name.'.field = new Array(';
        for ($i = 0; $i < $this->fieldCount(); $i++) {
            $htmlGrid .= '"'.$this->field[$i]['name'].'"';
            if ($i < $this->fieldCount() -1) {
                $htmlGrid .= ',';
            }
        }
        $htmlGrid .= ');'."\n";
        $htmlGrid .= $this->ind. '            pGrid_'.$this->name.'.fieldType = new Array(';
        for ($i = 0; $i < $this->fieldCount(); $i++) {
            $htmlGrid .= '"'.$this->field[$i]['type'].'"';
            if ($i < $this->fieldCount() -1) {
                $htmlGrid .= ',';
            }
        }
        $htmlGrid .= ');'."\n";
        $htmlGrid .= $this->ind. '            pGrid_'.$this->name.'.fieldVisible = new Array(';
        for ($i = 0; $i < $this->fieldCount(); $i++) {
            $fieldVisible = $this->field[$i]['visible'] == true ? 'true' : 'false';
            $htmlGrid .= $fieldVisible;
            if ($i < $this->fieldCount() -1) {
                $htmlGrid .= ',';
            }
        }
        $htmlGrid .= ');'."\n";
        
        $htmlGrid .= $this->ind. '            pGrid_'.$this->name.'.xmlIdentification = \''.$this->name.'\';'."\n";
        $htmlGrid .= $this->ind. '            pGrid_'.$this->name.'.lineEventOnData = \''.$this->pGrid->lineEventOnData.'\';'."\n";
        if ($this->pGrid->pointerCursorOnData) {
            $htmlGrid .= $this->ind. '            pGrid_'.$this->name.'.pointerCursorOnData = true;'."\n";
        }
        
        // vinculo com o PrumoGrid
        $htmlGrid .= $this->ind. '            '.$this->name.'.pGrid = pGrid_'.$this->name.";\n";
        
        $htmlGrid .= $this->ind.'        </script>'."\n";
        
        return $htmlGrid;
    }
    
    /**
     * Gera o código HTML da barra de navegação
     *
     * @return string: código HTML da barra de navegação
     */
    protected function makeGridNavigation()
    {
        $htmlGridNavigation = $this->ind.'        <div id="pGridNavigation_'.$this->name.'" class="prumoGridNavigation"></div>'."\n";
        $htmlGridNavigation .= $this->ind.'        <br />'."\n";
        $htmlGridNavigation .= $this->ind.'        <script type="text/javascript">'."\n";
        $htmlGridNavigation .= $this->ind.'            pGridNavigation_'.$this->name.' = new PrumoGridNavigation(\''.$this->name.'\');'."\n";
        
        // vinculo com o PrumoGridNavigation
        $htmlGridNavigation .= $this->ind.'            '.$this->name.'.pGridNavigation = pGridNavigation_'.$this->name.';'."\n";

        $htmlGridNavigation .= $this->ind.'        </script>'."\n";
        
        return $htmlGridNavigation;
    }
    
    /**
     * Gera o link 'Inserir novo' para ser adicionado ao lado dos filtros
     *
     * @return string: código html do link
     */
    protected function makeShortcut()
    {
        global $pConnectionPrumo;
        
        if (isset($this->param['menushortcut'])) {
            
            if (pPermitted($this->param['menushortcut'], 'c')) {
                
                $sql  = 'SELECT'."\n";
                $sql .= '    routine,'."\n";
                $sql .= '    link'."\n";
                $sql .= 'FROM '.$pConnectionPrumo->getSchema().'routines'."\n";
                $sql .= 'WHERE routine='.pFormatSql($this->param['menushortcut'], 'string').';';
                
                $query = $pConnectionPrumo->fetchAssoc($sql);
                
                $isHttp = strtolower(substr($query['link'], 0, 4)) == 'http';
                $href = $isHttp ? $query['link'] : 'index.php?page='.$query['routine'];
                $link = '<a href="'.$href.'" target="_blank">'._('Cadastrar Novo').'</a>';
                
                return $link;
            }
            
        } else {
            return '';
        }
    }
    
    /**
     * Gera o código completo
     *
     * @param $verbose boolean: quando true imprime o código gerado
     *
     * @return string: código gerado
     */
    public function draw($verbose)
    {
        // junta os objetos
        $pSearchInit = $this->initClientObject();
        $pSearchChilds = $this->makeFilters();
        $pSearchChilds .= $this->makeGrid();
        $pSearchChilds .= $this->makeGridNavigation();        
        $pSearchChilds = $this->addWindow($pSearchChilds);
        
        $pSearch = $pSearchInit . $pSearchChilds;
        
        if ($verbose) {
            echo $pSearch;
        }
        
        return $pSearch;
    }
    
    /**
     * Adiciona um filtro na pesquisa
     *
     * @param $field string: nome do campo
     * @param $operator string: operador lógico
     * @param $value string: valor do campo
     * @param $visible boolean: indica se o filtro deve aparecer na view
     */
    public function addFilter($field, $operator, $value, $visible)
    {
        $booVisible = $visible == false ? 'false' : 'true';
        
        echo '<script type="text/javascript">'."\n";
        echo '    '.$this->name.'.pFilter.addFilter(null,\''.$field.'\',\''.$operator.'\',\''.$value.'\',\'\','.$booVisible.');'."\n";
        echo '    '.$this->name.'.pFilter.draw();'."\n";
        echo '</script>'."\n";
    }
    
    /**
     * Gera código SQL da condição de pesquisa
     *
     * @return string: código SQL
     */
    public function sqlCondition()
    {
        $fieldName = $this->pFilter->filter['fieldName'];
        $operator  = $this->pFilter->filter['operator'];
        $value     = $this->pFilter->filter['value'];
        $value2    = $this->pFilter->filter['value2'];
        
        $arrCondition = array();
        $iValue = 0;
        for ($i = 0; $i < count($fieldName); $i++) {
            if ($value[$i] != '' or $operator[$i] == 'is null' or $operator[$i] == 'not is null') {
                $field = $this->fieldByName($fieldName[$i]);
                $condition = $this->pConnection->getSqlOperator($operator[$i]);
                $condition = str_replace(':field:', $field['sqlname'], $condition);
                $condition = str_replace(':value:', pFormatSql($value[$i], $field['type'], false, false), $condition);
                $condition = str_replace(':value2:', pFormatSql($value2[$i], $field['type'], false, false), $condition);
                $arrCondition[$iValue] = $condition;
                $iValue++;
            }
        }
        
        $conditionOut = '';
        for ($i = 0; $i < count($arrCondition); $i++) {
            $conditionOut .= $i == 0 ? ' WHERE '.$arrCondition[$i] : ' AND '.$arrCondition[$i];
        }
        
        return $conditionOut;
    }
    
    /**
     * Define o campo de ordenação da consulta
     *
     * @param $orderby string: nome do campo de ordenação
     */
    public function setOrderby($orderby)
    {
        $this->orderby = pFormatSql($orderby, 'string', false, false);
    }
    
    /**
     * Gera o código SQL da ordenação da consulta
     *
     * @return string: SQL da ordenação da consulta
     */
    public function sqlOrderby() 
    {
        $fieldName = $this->pFilter->filter['fieldName'];
        $visible   = $this->pFilter->filter['visible'];
        
        $orderbyOut = '';
        $iVisible = 0;
        for ($i = 0; $i < count($fieldName); $i++) {
            
            $field = $this->fieldByName($fieldName[$i]);
            if ($visible[$i]) {
                $orderbyOut .= $iVisible == 0 ? $field['sqlname'] : ','.$field['sqlname'];
                $iVisible++;
            }
        }
        
        $orderbyOut = empty($this->orderby) ? ' ORDER BY ' .$orderbyOut : ' ORDER BY ' .$this->orderby;
        
        return $orderbyOut;
    }
    
    /**
     * Gera o código SQL da consulta
     *
     * @return string: código SQL
     */
    public function sqlSearch()
    {
        $offsetNum = ($this->page - 1) * $this->pageLines();
        
        $tableName = $this->param['tablename'];
        
        $fields = '';
        for ($i = 0; $i < $this->fieldCount(); $i++) {
            
            if ($fields != '') {
                $fields .= ',';
            }
            
            $fields .= $this->field[$i]['name'];
        }
        
        $offset = ' OFFSET '.$offsetNum;
        $limit = ' LIMIT '.$this->pageLines();
        $orderby = $this->sqlOrderby();
        $condition = $this->sqlCondition();
        
        if ($this->fixedSqlSearch != '') {
            $sqlSearch = 'SELECT '.$fields.' FROM ('.$this->fixedSqlSearch.') fixed '.$condition.$orderby.$limit.$offset.';';
        } else {
            $sqlSearch = 'SELECT '.$fields.' FROM '.$this->pConnection->getSchema($this->param['schema']).$tableName.$condition.$orderby.$limit.$offset.';';
        }
        
        return $sqlSearch;
    }
    
    /**
     * Define o SQL para a consulta em substituiçao ao SQL gerado automaticamente
     *
     * @param $sql string: consulta SQL
     */
    public function setSqlSearch($sql)
    {
        if ($this->fixedSqlSearch == '') {
            for ($i = 0; $i < $this->fieldCount(); $i++) {
                $this->field[$i]['sqlname'] = 'fixed.'.$this->field[$i]['sqlname'];
            }
        }
        
        $this->fixedSqlSearch = $sql;
    }
    
    /**
     * Gera o comando SQL que conta os registro
     *
     * @return string: comando SQL
     */
    public function sqlCount()
    {
        $tableName = $this->param['tablename'];
        
        $condition = $this->sqlCondition();
        
        if ($this->fixedSqlSearch != '') {
            $sqlCount = 'SELECT count(*) FROM ('.$this->fixedSqlSearch.') fixed '.$condition.';';
        } else {
            $sqlCount = 'SELECT count(*) FROM '.$this->pConnection->getSchema($this->param['schema']).$tableName.$condition.';';
        }
        
        return $sqlCount;
    }
    
    /**
     * Gera o XML completo
     *
     * @param $verbose boolean: quando true imprime o XML
     *
     * @return string: XML completo
     */
    public function makeXml($verbose)
    {
        global $prumoGlobal;
        
        if ($prumoGlobal['currentUser'] == '') {
            $xml = pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
        } else {
            $this->page = $_POST['page'];
            
            if (isset($_POST['orderBy'])) {
                $this->setOrderby($_POST['orderBy']);
            }
            
            $this->pFilter->loadQuery();
            
            $count = $this->pConnection->sqlQuery($this->sqlCount());
            if ($count === false) {
                pXmlError('SqlError', $this->pConnection->getErr(), true);
                exit;
            }
            
            $xml = $this->pConnection->sqlXml($this->sqlSearch(), $this->name);
            if ($xml === false) {
                pXmlError('SqlError', $this->pConnection->getErr(), true);
                exit;
            }
            
            $xmlStatus  = '<count>'.$count.'</count>'."\n";
            $xmlStatus .= '<pageLines>'.$this->pageLines().'</pageLines>'."\n";
            $xmlStatus .= '<page>'.$this->page.'</page>';
            $xmlStatus = pXmlAddParent($xmlStatus, 'pGridStatus');
            
            $xml .= $xmlStatus;
            
            $xml .= $this->pFilter->makeXmlFilter();
            
            $debugSql  = '<sql>'.$this->sqlSearch().'</sql>'."\n";
            $debugSql .= '<sqlCount>'.$this->sqlCount().'</sqlCount>';
            $debugSql = pXmlAddParent($debugSql, 'debugSql');
            
            if (isset($this->param['debug']) && $this->param['debug']) {
                $xml .= $debugSql;
            }
            
            $xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);
        }
        
        if ($verbose) {
            Header('Content-type: application/xml; charset=UTF-8');
            echo $xml;
        }
        
        return $xml;
    }
    
    /**
     * Desenha o botão "pesquisar"
     * 
     * @param $verbose boolean: quando true imprime o html gerado
     *
     * @returns string: html dos controles
     */
    public function makeButton($verbose=true)
    {
        $iconSearch = pGetTheme('icons/prumoSearch.png',true);
        $button = '<button class="pButton-outline" type="button" id="'.$this->name.'Bt" onClick="javascript:'.$this->name.'.goSearch();"><img src="'.$iconSearch.'" alt="PrumoSearch" /></button>';
        
        if ($verbose) {
            echo $button;
        }
        
        return $button;
    }
    
    /**
     * Decide qual ação tomar de acordo com os parametros passados via GET ou POST
     */
    public function autoInit()
    {
        if (isset($_POST[$this->name.'_action']) && $_POST[$this->name.'_action'] == 'makeXml') {
            $this->makeXml(true);
        } elseif (isset($_POST[$this->name.'_action']) && $_POST[$this->name.'_action'] == 'r') {
            $this->doRetrieve(true);
        } else {
            $this->draw(true);
        }
    }
    
    /**
     * Gera o código JS que faz o link entre o search e o crud para bloquear/desbloquear o botão de acordo com o estado do crud
     *
     * @param $crudName string: nome do PrumoCrud
     * @verbose boolean: quando true imprime o código gerado
     *
     * @return string: código gerado
     */
    public function crudState($crudName, $verbose=true)
    {
        $state  = '<script type="text/javascript">'."\n";
        $state .= '    '.$crudName.'.addSonSearch('.$this->name.');'."\n";
        $state .= '</script>'."\n";
        
        if ($verbose) {
            echo $state;
        }
        
        return $state;
    }
    
    /**
     * Adiciona um container ao código, sendo o id o nome do objeto
     *
     * @param $pSearch string: código de entrada
     *
     * @return string: código de saída
     */
    private function addWindow($pSearch)
    {
        $title = isset($this->param['title']) ? $this->param['title'] : $this->name;
        
        $pWindow = new PrumoWindow('pWindow_'.$this->name);
        $pWindow->title = $title;
        $pWindow->ind = $this->ind;
        $pWindow->commandClose = $this->name.'.cancel()';
        $pSearchReturn = $pWindow->draw(false,$pSearch);
        
        // vinculo do PrumoWindow com o PrumoSearch
        $pSearchReturn .= $this->ind.'<script type="text/javascript">'."\n";
        $pSearchReturn .= $this->ind.'    '.$this->name.'.pWindow = pWindow_'.$this->name.";\n";
        
        // repassa parametro modal do PrumoSearch (que só faz sentido se tiver pWindow, por isso o codigo esta aqui)
        if (isset($this->param['modal'])) {
            $pSearchReturn .= $this->ind. '            '.$this->name.'.modal = '.$this->param['modal'].";\n";
        }
        $pSearchReturn .= $this->ind. '    '.$this->name.'.pAjax.pLoading = new prumoLoading(\'pWindow_'.$this->name.'_loading\');'."\n";
        $pSearchReturn .= $this->ind.'</script>'."\n";
        
        return $pSearchReturn;
    }
    
    /**
     * Monta um comando SQL de consulta
     *
     * @return string: comando SQL
     */
    public function sqlRetrieve()
    {
        // monta condicao
        $condition = '';
        for ($i = 0; $i < count($this->field); $i++) {
            $fieldName = $this->field[$i]['name'];
            if ($this->field[$i]['pk']) {
                $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                $value = pFormatSql($_POST[$this->field[$i]['name']],$this->field[$i]['type']);
                $condition .= $fieldName.'='.$value;
            }
        }
        
        // monta campos
        $fields = '';
        for ($i = 0; $i < count($this->field); $i++) {
            if (! isset($this->field[$i]['virtual']) or $this->field[$i]['virtual'] == false) {
                if ($fields != '') {
                    $fields .= ',';
                }
                $fields .= $this->field[$i]['name'];
            }
        }
        
        $tableName = $this->param['tablename'];
        
        if ($this->fixedSqlSearch != '') {
            $sql = 'SELECT '.$fields.' FROM ('.$this->fixedSqlSearch.') fixed '.$condition.';';
        } else {
            $sql = 'SELECT '.$fields.' FROM '.$this->pConnection->getSchema($this->param['schema']).$tableName.$condition.';';
        }
        
        return $sql;
    }
    
    /**
     * Executa a rotina RETRIEVE
     *
     * @param $verbose boolean: mostra resultado na tela
     *
     * @return string: resultado em XML
     */
    private function doRetrieve($verbose)
    {
        global $prumoGlobal;
        
        if ($prumoGlobal['currentUser'] == '') {
            $xml = pXmlError('session expires',_('Sua sessão expirou, faça login novamente.'));
        } else {
            
            $xml = $this->pConnection->sqlXml($this->sqlRetrieve(), $this->name);
            if ($xml === false) {
                pXmlError('SqlError', $this->pConnection->getErr(), true);
                exit;
            }
            
            $xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);
        }
        
        if ($verbose) {
            Header('Content-type: application/xml; charset=UTF-8');        
            echo $xml;
        }
        
        return $xml;
    }
}
