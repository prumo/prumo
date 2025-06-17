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
 * PrumoCrud é a classe principal que faz as operações de CRUD
 */
class PrumoCrud extends PrumoBasic
{
    private $parent1xN;
    private $permission;
    private $capsLock;
    private $audit;
    private $clientObjectsStarted = false;
    
    protected $serialFields = array();
    protected $action = null;

    public $name;
    
    public $pSearch = null;
    public $pCrudList = null;
    
    public $parent1x1 = null;
    public $parent1x1Condition = null;
    public $son1x1 = array();
    
    public $customSqlCreate = '';
    public $customSqlRetrieve = '';
    public $customSqlCount = '';
    public $customSqlUpdate = '';
    public $customSqlDelete = '';
    
    public $xmlRetrieve;
    
    public $containerType = ''; //div, fieldset, window
    public $containerVisible = true;
    
    public $msgErrorBeforeRetrieve;
    public $msgErrorBeforeCreate;
    public $msgErrorBeforeUpdate;
    public $msgErrorBeforeDelete;
    
    public $validator = array();
    
    public $fieldTemplate = array(
        'serial'    => '<input type="text" size="5" />',
        'integer'   => '<input type="number" size="5" />',
        'string'    => '<input type="text" size="20" />',
        'text'      => '<textarea></textarea>',
        'numeric'   => '<input type="text" size="5" style="text-align:right;" />',
        'money'     => '<input type="text" size="9" style="text-align:right;" />',
        'phone'   => '<input type="text" size="20" style="text-align:right;" />',
        'date'      => '<input type="date" size="9" />',
        'time'      => '<input type="text" size="9" />',
        'timestamp' => '<input type="datetime-local" size="15" />',
        'boolean'   => '<input type="checkbox" />',
        'file'      => '<input type="file" />'
    );
    
    /**
     * Constritor da classe PrumoCrud
     */
    function __construct(string $params)
    {
        parent::__construct($params);

        $this->msgErrorBeforeRetrieve = _('PrumoCrud Error: beforeRetrieve retornou false para objeto ":o:"!');
        $this->msgErrorBeforeCreate = _('PrumoCrud Error: beforeCreate retornou false para objeto ":o:"!');
        $this->msgErrorBeforeUpdate = _('PrumoCrud Error: beforeUpdate retornou false para objeto ":o:"!');
        $this->msgErrorBeforeDelete = _('PrumoCrud Error: beforeDelete retornou false para objeto ":o:"!');
        
        if (! isset($this->param['tablename'])) {
            $this->param['tablename'] = '';
        }
        
        if (! isset($this->param['onduplicate'])) {
            $this->param['onduplicate'] = 'error';
        }
        
        if (! isset($this->param['list'])) {
            $this->param['list'] = true;
        }
        
        if (! isset($this->param['capslock'])) {
            $this->capsLock = false;
        } else {
            $this->capsLock = $this->param['capslock'] == 'true' ? true : false;
        }
        
        // permissões
        if (isset($this->param['routine'])) {
            $this->setPermissions($this->param['routine']);
        } else if (isset($this->param['permission'])) {
            $this->permission = $this->param['permission'];
        } else {
            $this->permission = 'crud';
        }
        
        // parametro autoclear serve para limpar o formulário quando desenha os controles
        if (isset($this->param['autoclear'])) {
            $this->param['autoclear'] = $this->param['autoclear'] == 'true';
        } else {
            $this->param['autoclear'] = false;
        }
        
        // audit
        if (isset($this->param['audit'])) {
            $this->audit = $this->param['audit'] === 'false' ? false : true;
        } else if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
            $this->audit = pGetAudit($this->param['routine']);
        } else {
            $this->audit = false;
        }
        
        $this->parent1xN = isset($this->param['parent1xn']) ? $this->param['parent1xn'] : '';
    }
    
    /**
     * Inicializa os objetos filhos pCrudList que faz a listagem e o pSearch responsável pelo "copiar de"
     *
     * @return boolean true
     */
    private function startClientObjects() : bool
    {
        if ($this->clientObjectsStarted == false) {
            
            $this->clientObjectsStarted = true;
            $this->getObjName();
            
            if (empty($this->param['tablename'])) {
                $this->param['tablename'] = $this->name;
            }
            
            //pSearch
            $title = isset($this->param['title']) ? ',title='.$this->param['title'] : '';
            $debug = (isset($this->param['debug']) && $this->param['debug']) ? ',debug' : '';
            $pageLines = isset($this->param['pagelines']) ? ',pageLines='.$this->param['pagelines'] : '';
            
            $this->pSearch = new PrumoSearch('objName=pSearch_'.$this->name.','.
                                                    'xmlFile='.$this->param['xmlfile'].','.
                                                    'tableName='.$this->param['tablename'].','.
                                                    'schema='.$this->param['schema'].
                                                    $title.$debug.$pageLines
                                                );
            
            //pCrudList
            $autoClick = isset($this->param['autoclick']) ? ',autoClick' : '';
            
            $fastCreate = '';
            if (isset($this->param['fastcreate']) && $this->param['fastcreate'] && $this->getPermission('c')) {
                $fastCreate = ',fastCreate';
            }
            
            $fastUpdate = '';
            if (isset($this->param['fastupdate']) && $this->param['fastupdate'] && $this->getPermission('u')) {
                $fastUpdate = ',fastUpdate';
            }
            
            $fastDelete = '';
            if (isset($this->param['fastdelete']) && $this->param['fastdelete'] && $this->getPermission('d')) {
                $fastDelete = ',fastDelete';
            }
            
            $routine = '';
            if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                $routine = ',routine='.$this->param['routine'];
            }
            
            if ($this->param['list']) {
                $this->pCrudList = new PrumoCrudList(
                    'objName=pCrudList_'.$this->name.','.
                    'xmlFile='.$this->param['xmlfile'].','.
                    'crudName='.$this->name.','.
                    'tableName='.$this->param['tablename'].','.
                    'schema='.$this->param['schema'].
                    $debug.
                    $pageLines.
                    $autoClick.
                    $fastCreate.
                    $fastUpdate.
                    $fastDelete.
                    $routine
                );
            } else {
                $this->pCrudList = false;
            }
        }
        
        return true;
    }
    
    /**
     * Define a conexão com o banco de dados
     *
     * @param $connection object: PrumoConnection já instanciado e configurado
     */
    public function setConnection(PrumoConnection $connection)
    {
        $this->startClientObjects();
        $this->pConnection = $connection;
        $this->pSearch->setConnection($connection);
        $this->pCrudList->setConnection($connection);
    }
    
    /**
     * Adiciona um campo
     *
     * @param $params string: string de configuração do campo no formato do framework
     */
    public function addField(string $params) : array
    {
        $this->startClientObjects();
        
        parent::addField($params);
        
        $param = pParameters($params);
        
        $fieldIndex = count($this->field)-1;
        
        // parâmetros
        $this->field[$fieldIndex]['fieldid'] = isset($param['fieldid']) ? $param['fieldid'] : $param['name'];
        $this->field[$fieldIndex]['pk'] = isset($param['pk']) ? true : false;
        $this->field[$fieldIndex]['readonly'] = isset($param['readonly']) ? true : false;
        $this->field[$fieldIndex]['nocreate'] = isset($param['nocreate']) ? true : false;
        $this->field[$fieldIndex]['noupdate'] = isset($param['noupdate']) ? true : false;
        $this->field[$fieldIndex]['virtual'] = isset($param['virtual']) ? true : false;
        $this->field[$fieldIndex]['nohtml'] = isset($param['nohtml']) ? true : false;
        $this->field[$fieldIndex]['unique'] = isset($param['unique']) ? true : false;

        $this->field[$fieldIndex]['sizelimit'] = isset($param['sizelimit']) ? $param['sizelimit'] : 0;

        $this->field[$fieldIndex]['template'] = isset($param['template']) ? $param['template'] : $this->fieldTemplate[$this->field[$fieldIndex]['type']];
        $this->field[$fieldIndex]['template'] = str_replace("\r", '', $this->field[$fieldIndex]['template']);
        $this->field[$fieldIndex]['template'] = str_replace("\n", '', $this->field[$fieldIndex]['template']);
        $this->field[$fieldIndex]['template'] = str_replace("\t", '', $this->field[$fieldIndex]['template']);
        $this->field[$fieldIndex]['template'] = str_replace(".", ',', $this->field[$fieldIndex]['template']);
        
        // parâmetro default
        if (isset($param['default'])) {
            $this->field[$fieldIndex]['default'] = $param['default'];
        }
        
        // parâmetro capsLock
        if (isset($param['capslock'])) {
            $this->field[$fieldIndex]['capslock'] = $param['capslock'] == 'true' ? true : false;
        } else {
            if ($this->field[$fieldIndex]['type'] == 'string' || $this->field[$fieldIndex]['type'] == 'text') {
                $this->field[$fieldIndex]['capslock'] = $this->capsLock;
            } else {
                $this->field[$fieldIndex]['capslock'] = false;
            }
        }
        
        // parâmetro list
        if ($this->param['list'] && ! $this->field[$fieldIndex]['virtual']) {
            $this->pCrudList->addField($params);
        }
        
        if (! $this->field[$fieldIndex]['virtual']) {
            $this->pSearch->addField($params);
        }
        
        return $this->field[$fieldIndex];
    }
    
    /**
     * Adiciona validadores para um determinado campo
     * 
     * @param string       $field     O fieldId
     * @param string|array $validator string para um único validador ou array com vários validadores
     */
    public function addFieldValidator (string $field, $validator)
    {
        $validators = is_string($validator) ? array($validator) : $validator;
        $index = $this->fieldIndexById($field);
        if ($index) {
            $fieldName = $this->field[$index]['name'];
            foreach ($validators as $type => $value) {
                switch ($type) {
                    case 'max':
                    case 'min':
                        $fieldType = $this->field[$index]['type'];
                        if (! in_array($fieldType, array('integer', 'numeric', 'money'))) {
                            echo "<script type=\"text/javascript\">alert('Validador $type disponível somente para tipos numéricos');</script>";
                        }
                        break;
                    default:
                        echo "<script type=\"text/javascript\">alert('Validador $type não implementado');</script>";
                }
                $this->validator[$fieldName][$type] = $value;
            }
        } else {
            echo "<script type=\"text/javascript\">alert('Campo $field não encontrado');</script>";
        }
    }
    
    /**
     * Adiciona um objeto CRUD pai com relacionamento 1x1
     *
     * @param $parent object: instancia do objeto pai
     * @param $parentFieldCondition string: nome do campo no objeto pai que deve obedecer uma condição/valor
     * @param $conditionValue string: valor condicional da campo no objeto pai
     * @param $conditionOperator string: operador lógico para a condição (equal, not equal)
     */
    public function addParent1x1(PrumoCrud $parent, string $parentFieldCondition='', string $conditionValue='', string $conditionOperator='equal')
    {
        $parent->son1x1[] = $this;
        $this->parent1x1 = $parent;
        $arrCondition['fieldName'] = $parentFieldCondition;
        $arrCondition['value'] = $conditionValue;
        $arrCondition['operator'] = $conditionOperator;
        $this->parent1x1Condition = $arrCondition;
        
        for ($i = 0; $i < count($this->parent1x1->field); $i++) {
            if ($this->parent1x1->field[$i]['name'] == $parentFieldCondition && $conditionOperator == 'equal') {
                $this->parent1x1->field[$i]['haveChild'] = true;
            }
            if ($this->parent1x1->field[$i]['name'] != $parentFieldCondition && $conditionOperator == 'not equal') {
                $this->parent1x1->field[$i]['haveChild'] = true;
            }
        }
    }
    
    /**
     * Corrige a condição WHERE de um SQL substituindo campo=NULL para campo IS NULL
     *
     * @param $sql string: comando SQL a ser corrigido
     *
     * @return string: comando SQL corrigido
     */
    private function fixCondition(string $condition) : string
    {
        // corrige condições "campo=NULL" para "campo IS NULL" apos condição WHERE
        $arrSql = explode('WHERE', $condition);
        $fixedCondition = '';
        for ($i = 0; $i<count($arrSql); $i++) {
            $sqlPart = $i == 0 ? $arrSql[$i] : str_replace('=NULL', ' IS NULL', $arrSql[$i]);
            $sqlPart = str_replace(' IS NULL::text', '::text IS NULL', $sqlPart);
            $fixedCondition .= empty($fixedCondition) ? $sqlPart : 'WHERE'.$sqlPart;
        }

        return $fixedCondition;
    }
    
    /**
     * Recupera o fieldName pelo fieldId
     *
     * @param: fieldId string
     *
     * @return: string
     */
    public function fieldNameById(string $fieldId) : string
    {
        for ($i = 0; $i < count($this->field); $i++) {
            if ($this->field[$i]['fieldid'] == $fieldId) {
                return $this->field[$i]['name'];
            }
        }
        $msg = _('Campo com fieldId %fielId% não encontrado!');
        throw new Exception(str_replace('%fielId%', $fieldId, $msg));
    }
    
    /**
     * Recupera o fieldIndex pelo fieldId
     *
     * @param: fieldId string
     *
     * @return: integer
     */
    private function fieldIndexById(string $fieldId) : int
    {
        for ($i = 0; $i < count($this->field); $i++) {
            if ($this->field[$i]['fieldid'] == $fieldId) {
                return $i;
            }
        }
        $msg = _('Campo com fieldId %fielId% não encontrado!');
        throw new Exception(str_replace('%fielId%', $fieldId, $msg));
    }
    
    /**
     * Substitui os parâmetros pelos valores no comando sql (exemplo: substitui ':new_campo1:' por 'valor1')
     *
     * @param $sql string: comando SQL
     * @param $values array: array associativo com nome do campo e valor 
     *
     * @return string: sql com os valores substituidos
     */
    protected function sqlValues(string $sql, array $values=array()) : string
    {
        $sqlVal = $sql;

        //substitui variáveis globais do prumo no comando sql
        $sqlVal = $this->pConnection->replacePrumoGlobals($sqlVal);
        
        // Quanto possui objeto pai, verifica os valores dos campos no objeto pai (apenas para evento retrieve)
        if ($this->parent1x1 != null && $this->action == 'r') {
            
            // Carrega um objeto xml através dos dados do pai 1x1
            $xml = $this->parent1x1->xmlRetrieve;
            $xml = pXmlAddParent($xml,'prumo');
            
            $objXml = simplexml_load_string($xml);
            
            // Aplica a substituição apenas nos campos chave primária
            for ($i = 0; $i < count($this->field); $i++) {
                if ($this->field[$i]['pk']) {
                    $parentName = $this->parent1x1->name;
                    $fieldNameOnParent = $this->parent1x1->fieldNameById($this->field[$i]['fieldid']);
                    $value = $objXml->$parentName->$fieldNameOnParent;
                    
                    $value = pFormatSql($value, $this->field[$i]['type'], $this->field[$i]['capslock']);
                    $field = ':new_'.$this->field[$i]['name'].':';
                    $sqlVal = str_replace($field, $value, $sqlVal);
                }
            }
        }
        
        //substitui os valores de acordo com o fieldName ou fieldId
        for ($i = 0; $i < count($this->field); $i++) {
            
            if (isset($values[$this->field[$i]['name']])) {
                
                //substitui o parametro pelo valor de acordo com o nome do campo
                $value = pFormatSql($values[$this->field[$i]['name']], $this->field[$i]['type'], $this->field[$i]['capslock']);
                $field = ':new_'.$this->field[$i]['name'].':';
                $sqlVal = str_replace($field, $value, $sqlVal);
            } else if (isset($_POST['new_'.$this->field[$i]['fieldid']])) {
                
                //substitui o parametro pelo valor de acordo com fieldId do campo
                $value = pFormatSql($_POST['new_'.$this->field[$i]['fieldid']], $this->field[$i]['type'], $this->field[$i]['capslock']);
                $field = ':new_'.$this->field[$i]['name'].':';
                $sqlVal = str_replace($field, $value, $sqlVal);
            }
            
            if (isset($_POST['old_'.$this->field[$i]['fieldid']])) {
                
                //substitui o parametro pelo valor de acordo com fieldId anterior do campo caso exista    
                $value = pFormatSql($_POST['old_'.$this->field[$i]['fieldid']], $this->field[$i]['type'], false);
                $field = ':old_'.$this->field[$i]['name'].':';
                $sqlVal = str_replace($field, $value, $sqlVal);
            } else if (isset($values[$this->field[$i]['name']])) {
                
                //em casos de onduplicate=true em relacionamentos 1x1 que manipulam a mesma tabela pode haver este caso
                $value = pFormatSql($values[$this->field[$i]['name']], $this->field[$i]['type'], false);
                $field = ':old_'.$this->field[$i]['name'].':';
                $sqlVal = str_replace($field, $value, $sqlVal);
            } else if (isset($_POST['new_'.$this->field[$i]['fieldid']])) {
                
                //em casos de onduplicate=true em relacionamentos 1x1 que manipulam a mesma tabela pode haver este caso
                $value = pFormatSql($_POST['new_'.$this->field[$i]['fieldid']], $this->field[$i]['type'], false);
                $field = ':old_'.$this->field[$i]['name'].':';
                $sqlVal = str_replace($field, $value, $sqlVal);
            }
        }
        
        $sqlVal = $this->fixCondition($sqlVal);
        
        return $sqlVal;
    }
    
    /**
     * Monta um comando SQL para fazer a contagem de registro de acordo com o filtro
     *
     * @return string: comando SQL
     */
    protected function sqlCount() : string
    {
        if (! empty($this->customSqlCount)) {
            $sql = $this->customSqlCount;
        } else {
            
            // monta condicao
            $condition = '';
            
            for ($i = 0; $i < count($this->field); $i++) {
                
                $fieldName = $this->field[$i]['name'];
                
                if ($this->field[$i]['pk']) {
                    
                    $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                    $value = ':new_'.$fieldName.':';
                    $condition .= $fieldName.'='.$value;
                }
            }
            $tableName = $this->param['tablename'];
            $schema = $this->pConnection->getSchema($this->param['schema']);

            $sql = 'SELECT count(*) FROM '.$schema.$tableName.$condition.';';
        }
        
        return $sql;
    }
    
    /**
     * Monta um comando SQL de inserção
     *
     * @return string: comando SQL
     */
    protected function sqlCreate() : string
    {
        if (! empty($this->customSqlCreate)) {
            $sql = $this->customSqlCreate;
        } else {
            
            $tableName = $this->param['tablename'];
            $schema = $this->pConnection->getSchema($this->param['schema']);
            
            $fields = '';
            $values = '';
            
            for ($i = 0; $i < count($this->field); $i++) {
                
                if ($this->field[$i]['type'] != 'serial' && $this->field[$i]['nocreate'] == false && $this->field[$i]['virtual'] == false) {
                    
                    if (! empty($fields)) {
                        $fields .= ',';
                        $values .= ',';
                    }
                    
                    $fields .= $this->field[$i]['name'];
                    $values .= ':new_'.$this->field[$i]['name'].':';
                }
            }
            
            $sql = 'INSERT INTO '.$schema.$tableName.' ('.$fields.') VALUES ('.$values.');';
        }
        
        return $sql;
    }
    
    /**
     * Monta um comando SQL de consulta
     *
     * @return string: comando SQL
     */
    protected function sqlRetrieve() : string
    {
        // monta condicao
        $condition = '';
        
        for ($i = 0; $i < count($this->field); $i++) {
            
            $fieldName = $this->field[$i]['name'];
            
            if ($this->field[$i]['pk']) {
                
                $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                $value = ':new_'.$fieldName.':';
                $condition .= $fieldName.'='.$value;
            }
        }

        // monta campos
        $fields = '';
        for ($i = 0; $i < count($this->field); $i++) {
            
            if ($this->field[$i]['virtual'] == false) {
                
                if (! empty($fields)) {
                    $fields .= ',';
                }
                $fields .= $this->field[$i]['name'];
            }
        }

        $tableName = $this->param['tablename'];
        $schema = $this->pConnection->getSchema($this->param['schema']);

        if (empty($this->customSqlRetrieve)) {
            $sql = 'SELECT '.$fields.' FROM '.$schema.$tableName.$condition.';';
        } else {
            $sql = $this->customSqlRetrieve.$condition.';';
        }
        
        return $sql;
    }
    
    /**
     * Prepara um SQL para pegar o valor de campos seriais recém inseridos no banco de dados
     *
     * @return string: SQL pronto
     */
    protected function sqlGetSerials() : string
    {
        $condition = '';
        for ($i = 0; $i < count($this->field); $i++) {
            
            $fieldName = $this->field[$i]['name'];
            
            if ($this->field[$i]['type'] != 'serial' && $this->field[$i]['nocreate'] == false && $this->field[$i]['virtual'] == false) {
                
                $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                $value = $this->field[$i]['type'] == 'file' ? ':new_'.$fieldName.':::text' : ':new_'.$fieldName.':';
                $condition .= $fieldName.'='.$value;
            }
        }
        
        $fields = '';
        for ($i = 0; $i < count($this->field); $i++) {
            
            if ($this->field[$i]['type'] == 'serial' && $this->field[$i]['nocreate'] == false && $this->field[$i]['virtual'] == false) {
                
                if (! empty($fields)) {
                    $fields .= ',';
                }
                
                $fields .= $this->field[$i]['name'];
            }
        }
        
        $tableName = $this->param['tablename'];
        $schema = $this->pConnection->getSchema($this->param['schema']);
        
        $sql = 'SELECT '.$fields.' FROM '.$schema.$tableName.$condition.' ORDER BY '.$fields.' DESC LIMIT 1;';
        
        return $sql;
    }
    
    /**
     * Monta um comando SQL para atualização
     *
     * @return string: SQL pronto
     */
    protected function sqlUpdate(array $valuesParent=array()) : string
    {
        if (! empty($this->customSqlUpdate)) {
            $sql = $this->customSqlUpdate;
        } else {
            
            $tableName = $this->param['tablename'];
            $schema = $this->pConnection->getSchema($this->param['schema']);
            
            // monta a condição
            $condition = '';
            for ($i = 0; $i < count($this->field); $i++) {
                
                if ($this->field[$i]['pk']) {
                    
                    $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                    $condition .= $this->field[$i]['name'].'=:old_'.$this->field[$i]['name'].':';
                }
            }
            
            // consulta registros atuais para fazer update apenas nos valores alterados
            $sqlSelect = 'SELECT * FROM '.$schema.$tableName.$condition.';';
            $sqlSelect = $this->parent1x1 == null ? $this->sqlValues($sqlSelect) : $this->sqlValues($sqlSelect, $this->syncPk($valuesParent));
            $currentValue = $this->pConnection->fetchAssoc($sqlSelect);
            
            // monta os campos alterados
            $values = '';
            for ($i = 0; $i < count($this->field); $i++) {
                
                if ($this->field[$i]['virtual'] == false) {
                    
                    if (
                        ! isset($_POST['new_'.$this->field[$i]['fieldid']]) ||
                        ! isset($_POST['old_'.$this->field[$i]['fieldid']]) ||
                        pFormatSql($_POST['new_'.$this->field[$i]['fieldid']], $this->field[$i]['type']) != pFormatSql(plainFormat($this->field[$i]['type'], $currentValue[$this->field[$i]['name']]), $this->field[$i]['type']) &&
                        $this->field[$i]['name'] != 'prumoUser' &&
                        $this->field[$i]['noupdate'] == false &&
                        ($this->field[$i]['type'] != 'file' || $_POST['new_'.$this->field[$i]['fieldid']] != '')
                    ) {
                        if (! empty($values)) {
                            $values .= ',';
                        }
                        
                        $values .= $this->field[$i]['name'].'=:new_'.$this->field[$i]['name'].':';
                    }
                }
            }
            
            if (empty($values)) {
                return '';
            }
            
            // monta o sql completo
            $sql = 'UPDATE '.$schema.$tableName. ' SET '.$values.$condition.';';
        }
        
        return $sql;
    }
    
    /**
     * Monta um comando SQL para DELETE
     *
     * @return string: SQL pronto
     */
    protected function sqlDelete() : string
    {
        $condition = '';
        if (! empty($this->customSqlDelete)) {
            $sql = $this->customSqlDelete;
        } else {
            
            for ($i = 0; $i < count($this->field); $i++) {
                
                if ($this->field[$i]['pk']) {
                    
                    $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                    $condition .= $this->field[$i]['name'].'=:new_'.$this->field[$i]['name'].':';
                }
            }

            $tableName = $this->param['tablename'];
            $schema = $this->pConnection->getSchema($this->param['schema']);

            $sql = 'DELETE FROM '.$schema.$tableName.$condition.';';
        }
        
        return $sql;
    }
    
    /**
     * Monta um comando SQL de verificação de unicidade
     *
     * @param $fieldName string: nome do campo
     * @param $excludePk boolean: exclui o item com a chave primária da consulta (Update)
     *
     * @return string: comando SQL
     */
    private function sqlUnique(string $fieldName, bool $excludePk=true) : string
    {
        $tableName = $this->param['tablename'];
        $schema = $this->pConnection->getSchema($this->param['schema']);
        
        $condition = ' WHERE '.$fieldName.'=:new_'.$fieldName.':';
        
        if ($excludePk === true) {
            for ($i = 0; $i < count($this->field); $i++) {
                if ($this->field[$i]['pk']) {
                    $condition .= ' AND NOT '.$this->field[$i]['name'].'=:old_'.$this->field[$i]['name'].':';
                }
            }
        }
        
        return 'SELECT count(*) FROM '.$schema.$tableName.$condition.';';
    }
    
    /**
     * Devolve um array associativo com valor para campos do tipo serial, incluindo os seriais do objeto pai 1x1
     *
     * @param: $serialsParent array
     *
     * @returns: array
     */
    private function syncPk(array $serialsParent) : array
    {
        $serials = array();
        
        // Cria um array com os campos pk do tipo inteiro
        $childParent = array();
        for ($i = 0; $i < count($this->field); $i++) {
            
            if ($this->field[$i]['pk'] && $this->field[$i]['type'] == 'integer') {
                $childParent[] = $this->field[$i]['name'];
            }
        }
        
        // Corre o array do objeto pai e renomeia os campos da chave estrangeira caso seja diferentes no objeto filho
        if (count($childParent) > 0) {
            
            $iParent = 0;
            foreach ($serialsParent as $keyParent => $valueParent) {
                
                if (isset($childParent[$iParent])) {
                    $serials[$childParent[$iParent]] = $valueParent;
                }
                $iParent++;
            }
        }
        
        // Junta os seriais do campo filho caso haja
        foreach ($this->serialFields as $keyChild => $valueChild) {
            $serials[$keyChild] = $valueChild;
        }
        
        return $serials;
    }
    
    /**
     * Executa a rotina CREATE
     *
     * @return string: xml
     */
    public function doCreate() : string
    {
        $this->action = 'c';
        
        if (empty($GLOBALS['prumoGlobal']['currentUser'])) {
            return pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
        }
        
        if ($this->parent1x1 == null) {
            $this->pConnection->sqlQuery('BEGIN;');
        }
        
        if ($this->callBeforeCreate()) {
            
            if (isset($this->param['onduplicate']) && $this->param['onduplicate'] == 'error') {
                
                // verifica se possui registro duplicado
                if ($this->parent1x1 == null) {
                    $sqlCount = $this->sqlValues($this->sqlCount());
                } else {
                    $pkValue = $this->syncPk($this->parent1x1->serialFields);
                    $sqlCount = $this->sqlValues($this->sqlCount(), $pkValue);
                }
                
                $count = $this->pConnection->sqlQuery($sqlCount);
                if ($count === false) {
                    if ($this->parent1x1 == null) {
                        $this->pConnection->sqlQuery('ROLLBACK;');
                    }
                    return pXmlError('SqlError', $this->pConnection->getErr());
                }
                
                if ($count > 0) {
                    if ($this->parent1x1 == null) {
                        $this->pConnection->sqlQuery('ROLLBACK;');
                    }
                    return pXmlError('Duplicated', _('Registro duplicado.').' - '.$this->name);
                }
            }
            
            if ($this->parent1x1 == null) {
                
                $sql = $this->sqlValues($this->sqlCreate());
                
                $sqlOk = $this->pConnection->sqlQuery($sql);
                if ($sqlOk === false) {
                    $this->pConnection->sqlQuery('ROLLBACK;');
                    return pXmlError('SqlError', $this->pConnection->getErr());
                }
                
                if ($this->audit) {
                    pAuditLog($this->param['routine'], $this->getObjName(), $sql, 'CREATE');
                }
                
                $this->serialFields = $this->getPks();
            } else {
                
                $pkValue = $this->syncPk($this->parent1x1->serialFields);
                
                if (isset($this->param['onduplicate']) && $this->param['onduplicate'] == 'update') {
                    
                    // verifica se já existe um registro com esta chave primária
                    $sqlCount = $this->sqlValues($this->sqlCount(), $pkValue);
                    $count = $this->pConnection->sqlQuery($sqlCount);
                    if ($count === false) {
                        if ($this->parent1x1 == null) {
                            $this->pConnection->sqlQuery('ROLLBACK;');
                        }
                        return pXmlError('SqlError', $this->pConnection->getErr());
                    }
                    
                    if ($count > 0) {
                        
                        //atualiza
                        $sql = $this->sqlUpdate($this->parent1x1->serialFields);
                        $sql = str_replace('old_', 'new_', $sql);
                        $sql = $this->sqlValues($sql, $pkValue);
                    } else {
                        //insere
                        $sql = $this->sqlValues($this->sqlCreate(), $pkValue);
                    }
                } else {
                    $sql = $this->sqlValues($this->sqlCreate(), $pkValue);
                }
                
                if (! empty($sql)) {
                    
                    $sqlOk = $this->pConnection->sqlQuery($sql);
                    if ($sqlOk === false) {
                        if ($this->parent1x1 == null) {
                            $this->pConnection->sqlQuery('ROLLBACK;');
                        }
                        return pXmlError('SqlError', $this->pConnection->getErr());
                    }
                    
                    if ($this->audit) {
                        pAuditLog($this->param['routine'], $this->getObjName(), $sql, 'CREATE');
                    }
                }
                $this->serialFields = array_merge($this->getPks(), $this->parent1x1->serialFields);
            }
            
            // laço que trata os objetos filhos
            for ($i = 0; $i < count($this->son1x1); $i++) {
                if (isset($_POST[$this->son1x1[$i]->name.'_action'])) {
                    $xmlSon = $this->son1x1[$i]->doCreate();
                    if (testXmlError($xmlSon)) {
                        if ($this->parent1x1 == null) {
                            $this->pConnection->sqlQuery('ROLLBACK;');
                        }
                        return $xmlSon;
                    }
                }
            }
            
            if ($this->parent1x1 == null) {
                $this->afterCreate();
                $this->pConnection->sqlQuery('COMMIT;');
                return $this->doRetrieve($this->serialFields);
            }
            else {
                return '';
            }
        } else {
            if ($this->parent1x1 == null) {
                $this->pConnection->sqlQuery('ROLLBACK;');
            }
            $xml = '<status>err</status>';
            $xml .= '<msg>'.str_replace(':o:', $this->name, $this->msgErrorBeforeCreate).'</msg>';
            $xml = pXmlAddParent($xml, $this->name);
            
            return $xml;
        }
    }
    
    /**
     * Chama o gatilho before create em todos os CRUDs recursivamente
     */
    public function callBeforeCreate() : bool
    {
        if ($this->beforeCreate() == false) {
            return false;
        }
        
        if ($this->validateUnique(false) == false) {
            return false;
        }
        
        $resultBeforeCreate = true;
        for ($i = 0; $i < count($this->son1x1); $i++) {
            if (isset($_POST[$this->son1x1[$i]->name.'_action'])) {
                if ($this->son1x1[$i]->callBeforeCreate() == false) {
                    $resultBeforeCreate = false;
                    $this->msgErrorBeforeCreate = $this->son1x1[$i]->msgErrorBeforeCreate;
                }
            }
        }
        
        return $resultBeforeCreate;
    }
    
    /**
     * Gatilho disparado depois do evento create
     * Função reservada para desenvolvedor da aplicação
     */
    public function beforeCreate() : bool
    {
        // Reservado para desenvolvedor da aplicação, deve retornar true ou false
        return true;
    }
    
    /**
     * Gatilho disparado depois do evento retrieve
     * Função reservada para desenvolvedor da aplicação
     */
    public function beforeRetrieve() : bool
    {
        // Reservado para desenvolvedor da aplicação, deve retornar true ou false
        return true;
    }
    
    /**
     * Chama o gatilho before create em todos os CRUDs recursivamente
     */
    public function callBeforeRetrieve() : bool
    {
        if ($this->beforeRetrieve() == false) {
            return false;
        }
        
        $resultBeforeRetrieve = true;
        for ($i = 0; $i < count($this->son1x1); $i++) {
            if (isset($_POST[$this->son1x1[$i]->name.'_action'])) {
                if ($this->son1x1[$i]->callBeforeRetrieve() == false) {
                    $resultBeforeRetrieve = false;
                    $this->msgErrorBeforeRetrieve = $this->son1x1[$i]->msgErrorBeforeRetrieve;
                }
            }
        }
        
        return $resultBeforeRetrieve;
    }
    
    /**
     * Chama o gatilho before update em todos os CRUDs recursivamente
     */
    public function callBeforeUpdate() : bool
    {
        if ($this->beforeUpdate() == false) {
            return false;
        }
        
        if ($this->validateUnique(true) == false) {
            return false;
        }
        
        $resultBeforeUpdate = true;
        for ($i = 0; $i < count($this->son1x1); $i++) {
            if (isset($_POST[$this->son1x1[$i]->name.'_action'])) {
                if ($this->son1x1[$i]->callBeforeUpdate() == false) {
                    $resultBeforeUpdate = false;
                    $this->msgErrorBeforeUpdate = $this->son1x1[$i]->msgErrorBeforeUpdate;
                }
            }
        }
        
        return $resultBeforeUpdate;
    }
    
    /**
     * Valida unique no before Update
     *
     * @param $excludePk boolean: exclui o item com a chave primária da consulta (Update)
     *
     * @return boolean
     */
    private function validateUnique($excludePk=true) : bool
    {
        // verifica unique
        for ($i = 0; $i < count($this->field); $i++) {
            if ($this->field[$i]['unique']) {
                $sqlUnique = $this->sqlValues($this->sqlUnique($this->field[$i]['name'], $excludePk));
                if ($this->pConnection->sqlQuery($sqlUnique) != '0') {
                    $msg = _('Registro duplicado, campo ":fieldLabel:".');
                    $this->msgErrorBeforeCreate = str_replace(':fieldLabel:', $this->field[$i]['label'], $msg);
                    $this->msgErrorBeforeUpdate = str_replace(':fieldLabel:', $this->field[$i]['label'], $msg);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Gatilho disparado depois do evento update
     * Função reservada para desenvolvedor da aplicação
     */
    public function beforeUpdate() : bool
    {
        // Reservado para desenvolvedor da aplicação, deve retornar true ou false
        return true;
    }
    
    /**
     * Gatilho disparado depois do evento update
     * Função reservada para desenvolvedor da aplicação
     */
    public function beforeDelete() : bool
    {
        // Reservado para desenvolvedor da aplicação, deve retornar true ou false
        return true;
    }
    
    /**
     * Gatilho disparado depois do evento create
     * Função reservada para desenvolvedor da aplicação
     */
    public function afterCreate()
    {
        // Reservado para desenvolvedor da aplicação
    }
    
    /**
     * Gatilho disparado depois do evento update
     * Função reservada para desenvolvedor da aplicação
     */
    public function afterUpdate()
    {
        // Reservado para desenvolvedor da aplicação
    }
    
    /**
     * Gatilho disparado depois do evento update
     * Função reservada para desenvolvedor da aplicação
     */
    public function afterDelete()
    {
        // Reservado para desenvolvedor da aplicação
    }
    
    /**
     * Ação executada quando ocorre um erro
     *
     * @return string: xml com a mensagem de erro
     */
    private function doAccessDenied() : string
    {
        if (empty($GLOBALS['prumoGlobal']['currentUser'])) {
            $xml = pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
        } else {
            $xml = pXmlError('access denied', _('Acesso Negado'));
        }
        
        return $xml;
    }
    
    /**
     * Executa a rotina RETRIEVE
     *
     * @param $values array: valor dos campos para ser substituído no comando SQL
     *
     * @return string: resultado em XML
     */
    public function doRetrieve(array $values=array()) : string
    {
        $this->action = 'r';
        
        if (empty($GLOBALS['prumoGlobal']['currentUser'])) {
            return pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
        }
        
        if ($this->callBeforeRetrieve()) {
            $sql = $this->sqlValues($this->sqlRetrieve(), $this->syncPk($values));
            $this->xmlRetrieve = $this->pConnection->sqlXml($sql, $this->name);
            if ($this->xmlRetrieve === false) {
                return pXmlError('SqlError', $this->pConnection->getErr());
            }
            
            // laço que trata os objetos filhos recursivamente
            for ($i = 0; $i < count($this->son1x1); $i++) {
                if (isset($_POST[$this->son1x1[$i]->name.'_action'])) {
                    $this->xmlRetrieve .= $this->son1x1[$i]->doRetrieve();
                }
            }
            
            // adiciona o appIdent no XML
            if ($this->parent1x1 == null) {
                $this->xmlRetrieve = pXmlAddParent($this->xmlRetrieve, $GLOBALS['pConfig']['appIdent']);
            }
        } else {
            $xml = '<status>err</status>';
            $xml .= '<msg>'.str_replace(':o:', $this->name, $this->msgErrorBeforeRetrieve).'</msg>';
            $xml = pXmlAddParent($xml, $this->name);
            
            $this->xmlRetrieve = $xml;
        }
        
        return $this->xmlRetrieve;
    }
    
    /**
     * Monta um array associativo com nome do campo e valor para campos do tipo serial
     *
     * @returns: array
     */
    private function getPks() : array
    {
        // procura campos serial
        $serialField = array();
        for ($i = 0; $i < count($this->field); $i++) {
            if ($this->field[$i]['type'] == 'serial') {
                $serialField[] = $this->field[$i]['name'];
            }
        }
        
        // se encontrou algum campo serial recupera os valores recem gerados automaticamente pelo sgdb
        if (count($serialField) > 0 && $this->action == 'c') {
            $arrPks = $this->pConnection->fetchAssoc($this->sqlValues($this->sqlGetSerials()));
            if ($arrPks === false) {
                $arrPks = array();
            }
        } else {
            
            $arrPks = array();
            for ($i = 0; $i < count($this->field); $i++) {
                
                if ($this->field[$i]['pk']) {
                    $arrPks[$this->field[$i]['name']] = $_POST['new_'.$this->field[$i]['fieldid']];
                }
            }
        }
        
        return $arrPks;
    }
    
    /**
     * Executa a rotina UPDATE
     *
     * @param $valuesParent array: valor dos campos para ser substituído no comando SQL caso tenha um objeto pai (opcional)
     *
     * @return string: resultado em XML
     */
    public function doUpdate(array $valuesParent=array()) : string
    {
        $this->action = 'u';
        
        if (empty($GLOBALS['prumoGlobal']['currentUser'])) {
            return pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
        }
        
        if ($this->parent1x1 == null) {
            $this->pConnection->sqlQuery('BEGIN;');
        }
        
        if ($this->callBeforeUpdate()) {
            
            if ($this->parent1x1 == null) {
                $sql = $this->sqlValues($this->sqlUpdate($valuesParent));
            } else {
                
                $sqlCount = $this->sqlValues($this->sqlCount(), $this->syncPk($valuesParent));
                $sonCount = $this->pConnection->sqlQuery($sqlCount);
                if ($sonCount === false) {
                    return pXmlError('SqlError', $this->pConnection->getErr());
                }
                
                // se não possui o registro na tabela filha da um create, se possui, da um update normal
                if ($sonCount == '0') {
                    $sql = $this->sqlValues($this->sqlCreate(), $this->syncPk($valuesParent));
                } else {
                    $sql = $this->sqlValues($this->sqlUpdate($valuesParent));
                }
            }
            
            if (! empty($sql)) {
                
                $sqlOk = $this->pConnection->sqlQuery($sql);
                if ($sqlOk === false) {
                    if ($this->parent1x1 == null) {
                        $this->pConnection->sqlQuery('ROLLBACK;');
                    }
                    return pXmlError('SqlError', $this->pConnection->getErr());
                }
                
                if ($this->audit) {
                    pAuditLog($this->param['routine'], $this->getObjName(), $sql, 'UPDATE');
                }
            }
            
            $this->serialFields = $this->getPks();
            
            // laço que trata os objetos filhos
            for ($i = 0; $i < count($this->son1x1); $i++) {
                if (isset($_POST[$this->son1x1[$i]->name.'_action'])) {
                    $sonXml = $this->son1x1[$i]->doUpdate($this->serialFields);
                    if (testXmlError($sonXml)) {
                        if ($this->parent1x1 == null) {
                            $this->pConnection->sqlQuery('ROLLBACK;');
                        }
                        return $sonXml;
                    }
                }
            }
            
            $this->afterUpdate();
            
            if ($this->parent1x1 == null) {
                $xml = $this->doRetrieve();
                if ($this->parent1x1 == null) {
                    $this->pConnection->sqlQuery('COMMIT;');
                }
            } else {
                $xml = '';
            }
        } else {
            if ($this->parent1x1 == null) {
                $this->pConnection->sqlQuery('ROLLBACK;');
            }
            $xml = '<status>err</status>';
            $xml .= '<msg>'.str_replace(':o:', $this->name, $this->msgErrorBeforeUpdate).'</msg>';
            $xml = pXmlAddParent($xml, $this->name);
        }
        
        return $xml;
    }
    
    /**
     * Executa a rotina DELETE
     *
     * @return string: resultado em XML
     */
    public function doDelete() : string
    {
        $this->action = 'd';
        
        if (empty($GLOBALS['prumoGlobal']['currentUser'])) {
            return pXmlError('session expires', _('Sua sessão expirou, faça login novamente.'));
        }
        
        if ($this->beforeDelete()) {
            
            $sql = $this->sqlValues($this->sqlDelete());
            
            $sqlOk = $this->pConnection->sqlQuery($sql);
            if ($sqlOk === false) {
                return pXmlError('SqlError', $this->pConnection->getErr());
            }
            
            if ($this->audit) {
                pAuditLog($this->param['routine'], $this->getObjName(), $sql, 'DELETE');
            }
            
            $xml = '<status>ok</status>';
            $xml .= '<msg>'._('Registro excluído com sucesso!').'</msg>';
            $xml = pXmlAddParent($xml, $this->name);
            
            $this->afterDelete();
        } else {
            $xml = '<status>err</status>';
            $xml .= '<msg>'.str_replace(':o:', $this->name, $this->msgErrorBeforeDelete).'</msg>';
            $xml = pXmlAddParent($xml, $this->name);
        }
        
        return $xml;
    }    
    
    /**
     * Gera e imprime o código HTML que associa os objetos pSearch aos campos do CRUD (caso haja)
     */
    private function drawSearch() : string
    {
        $pSearch = $this->pSearch->draw(false);
        for ($i = 0; $i < count($this->field); $i++) {
            if ($this->field[$i]['pk']) {
                $pSearch .= $this->pSearch->addFieldReturn($this->field[$i]['name'], $this->field[$i]['fieldid'], false, false);
            }
        }
        
        $pSearch .= "{$this->ind}<script type=\"text/javascript\">\n";
        $pSearch .= "{$this->ind}\tpSearch_{$this->name}.crudName = '{$this->name}';\n";
        $pSearch .= "{$this->ind}</script>\n";
        
        return $pSearch;
    }
    
    /**
     * Monta a linha de código que faz require_once para o arquivo controller do PrumoSearch
     *
     * @param $objName string: Nome do objeto PrumoSearch
     *
     * @returns string: linha de código php ex: require_once 'ctrl_search_pessoa.php';
     */
    private function requirePrumoSearch(string $objName) : string
    {
        $fileList = scandir(dirname(__DIR__));
        for ($i = 0; $i < count($fileList); $i++) {
            
            $info = pathinfo($fileList[$i]);
            $extension = isset($info['extension']) ? $info['extension'] : '';
            
            //apenas arquivos .php
            if (strtolower($extension) == 'php' && $info['basename'] != 'index.php' && $info['basename'] != 'prumo.php') {
                $fileContent = file_get_contents(dirname(__DIR__) . '/' . $fileList[$i]);
                
                // verifica se o arquivo inicializa o objeto informado
                if (substr_count($fileContent, '$'.$objName.' = new PrumoSearch(') > 0) {
                    return 'require_once __DIR__.\'/'.$fileList[$i].'\';';
                }
            }
        }
        
        return '//require_once \'\'; ATENÇÃO! não foi encontrado nenhum arquivo controller para o objeto "'.$objName.'". Informe o nome do arquivo nesta linha.';
    }
    
    /**
     * Desenha o forumlário
     *
     * @returns string: html do formulário
     */    
    public function drawForms(bool $verbose=true, bool $withPhpCode=false) : string
    {
        $form = '';
        if ($this->parent1x1 == null) {
            
            if ($withPhpCode) {
                
                $controls = '<?php $'.$this->name.'->drawControls(); ?>';
                $crudList = '    <?php $'.$this->name.'->drawCrudList(); ?>';
            } else {
                
                $controls = $this->drawControls(false);
                $crudList = $this->drawCrudList(false);
            }
            
            if (empty($this->containerType)) {
                
                $this->containerType = 'fieldset';
                
                if (! empty($this->parent1xN)) {
                    $this->containerType = 'div';
                    $this->containerVisible = false;
                }
            }
            
            $containerStyle = $this->containerVisible ? 'display:block' : 'display:none';
            
            $title = '';
            if (isset($this->param['title'])) {
                $title = $this->param['title'];
            }
            
            if ($withPhpCode) {
                
                $form .= '<?php'."\n";
                $form .= 'require_once __DIR__.\'/prumo.php\';'."\n";
                
                for ($i = 0; $i < count($this->field); $i++) {
                    
                    if (isset($this->field[$i]['search'])) {
                        $form .= $this->requirePrumoSearch($this->field[$i]['search'])."\n";
                    }
                }
                
                $required = str_replace(dirname(__DIR__), '', $_SERVER["SCRIPT_FILENAME"]);
                $form .= 'require_once __DIR__.\''.$required.'\';'."\n";
                $form .= '?>'."\n\n";
            }
            
            if ($this->containerType == 'fieldset') {
                
                $form .= '<fieldset id="'.$this->name.'_container" style="'.$containerStyle.'">'."\n";
                $form .= '<legend>'.$title.'</legend>'."\n";
            }
            
            if ($this->containerType == 'div') {
                
                $form .= '<div id="'.$this->name.'_container" style="'.$containerStyle.'">'."\n";
            }
            
            $form .= "\n";
            $form .= '    <div id="'.$this->name.'_form">'."\n";
            $form .= '        <br />'."\n";
            $form .= '        <table class="prumoFormTable">'."\n";
        } else {
            $form .= '        <div id="'.$this->name.'_form" style="display:none">'."\n";
            $form .= '            <table class="prumoFormTable">'."\n";
        }
        
        // campos
        for ($i = 0; $i < count($this->field); $i++) {
            
            if (! $this->field[$i]['nohtml'] && ($this->parent1x1 == null || ! $this->field[$i]['pk'])) {
                
                $label = $this->field[$i]['label'];
                $id = $this->field[$i]['fieldid'];
                $notNull = $this->field[$i]['notnull'] ? '*' : '';
                $disabled = ($this->field[$i]['readonly'] || $this->field[$i]['type'] == 'serial') ? ' disabled="disabled"' : '';
                
                if (isset($this->field[$i]['search'])) {
                    
                    $pSearch = $this->field[$i]['search'];
                    
                    if (! isset($$pSearch)) {
                        global $$pSearch;
                    }
                    
                    $search = $withPhpCode ? '<?php $'.$pSearch.'->makeButton(); ?>' : $$pSearch->makeButton(false);
                } else {
                    $search = '';
                }
                
                $defaultValue = isset($this->field[$i]['default']) ? $this->field[$i]['default'] : '';
                $ind = $this->parent1x1 == null ? '            ' : '                ';
                
                $formChild = '';
                $onChange = '';
                
                if (isset($this->field[$i]['haveChild'])) {
                    
                    $formChild .= '        </table>'."\n";
                    for ($j=0; $j < count($this->son1x1); $j++) {
                        
                        $formChild .= $this->son1x1[$j]->drawForms(false, $withPhpCode);
                        $onChange = ' onchange="'.$this->name.'.visibleSon1x1()"';
                    }
                    $formChild .= '        <table class="prumoFormTable">'."\n";
                }
                
                $form .= $ind.'<tr>'."\n";
                if ($this->field[$i]['type'] == 'boolean') {
                    
                    if (isset($this->field[$i]['default']) && ($this->field[$i]['default'] == 'true' || $this->field[$i]['default'] == 't')) {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '';
                    }
                    
                    $form .= $ind.'    <td class="prumoFormLabel"><br /></td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="checkbox"'.$disabled.$checked.$onChange.' />'.$label.' '.$search.'</td>'."\n";
                } else if ($this->field[$i]['type'] == 'date') {
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="date"'.$disabled.$onChange.' value="'.$defaultValue.'" />'.$search.$notNull.'</td>'."\n";
                } else if ($this->field[$i]['type'] == 'time') {
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="text"'.$disabled.$onChange.' value="'.$defaultValue.'" />'.$search.$notNull.'</td>'."\n";
                } else if ($this->field[$i]['type'] == 'integer' || $this->field[$i]['type'] == 'serial') {
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="text" size="9"'.$disabled.$onChange.' value="'.$defaultValue.'" />'.$search.$notNull.'</td>'."\n";
                } else if ($this->field[$i]['type'] == 'timestamp') {
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="datetime-local"'.$disabled.$onChange.' value="'.$defaultValue.'" />'.$search.$notNull.'</td>'."\n";
                } else if ($this->field[$i]['type'] == 'file') {
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="file"'.$disabled.$onChange.' value="'.$defaultValue.'" />'.$search.$notNull.'</td>'."\n";
                } else if ($this->field[$i]['type'] == 'text') {
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><textarea id="'.$id.'" cols="26" rows="3" '.$disabled.$onChange.'>'.$defaultValue.'</textarea>'.$search.$notNull.'</td>'."\n";
                } else {
                    
                    if (isset($this->field[$i]['size']) && ! empty($this->field[$i]['size'])) {
                        
                        $size = $this->field[$i]['size'] > 40 ? 40 :$this->field[$i]['size'];
                        $maxLength = ' maxlength='.$this->field[$i]['size'];
                    } else {
                        
                        $size = '40';
                        $maxLength = '';
                    }
                    
                    $form .= $ind.'    <td class="prumoFormLabel">'.$label.':</td>'."\n";
                    $form .= $ind.'    <td class="prumoFormFields"><input id="'.$id.'" type="text" size="'.$size.'"'.$disabled.$onChange.$maxLength.' value="'.$defaultValue.'" />'.$search.$notNull.'</td>'."\n";
                }
                
                $form .= $ind.'</tr>'."\n";
                
                $form .= $formChild;
            }
        }
        
        if ($this->parent1x1 == null) {
            
            $form .= '            <tr>'."\n";
            $form .= '                <td class="prumoFormLabel"><br /></td>'."\n";
            $form .= '                <td class="prumoFormFields">'.$controls.'</td>'."\n";
            $form .= '            </tr>'."\n";
            $form .= '        </table>'."\n";
            $form .= '        <br />'."\n";
        
            if (empty($this->parent1xN)) {
                $form .= '        * '._('Campos de preenchimento obrigatório')."\n";
            }
            
            $form .= '    </div>'."\n";
            $form .= "\n";
            $form .= $crudList."\n";
            $form .= "\n";
            
            if ($this->containerType == 'fieldset') {
                $form .= '</fieldset>'."\n";
            }
            
            if ($this->containerType == 'div') {
                $form .= '</div>'."\n";
            }
            
            $haveFieldReturn = false;
            for ($i = 0; $i < count($this->field); $i++) {
                if (isset($this->field[$i]['search']) || $this->field[$i]['virtual']) {
                    $haveFieldReturn = true;
                }
            }
            
            if ($withPhpCode && $haveFieldReturn) {
                $form .= "\n";
                $form .= '<?php'."\n";
            }
            
            // faz a ligação entre os objetos Search (crudState);
            for ($i = 0; $i < count($this->field); $i++) {
                
                if (isset($this->field[$i]['search'])) {
                    
                    $pSearch = $this->field[$i]['search'];
                    
                    if ($withPhpCode) {
                        
                        // addFieldReturn para o campo que possui o botão search
                        if (isset($this->field[$i]['search'])) {
                            if ($this->field[$i]['name'] == $this->field[$i]['fieldid']) {
                                $form .= '$'.$pSearch.'->addFieldReturn(\''.$this->field[$i]['name'].'\');'."\n";
                            } else {
                                $form .= '$'.$pSearch.'->addFieldReturn(\''.$this->field[$i]['name'].'\',\''.$this->field[$i]['fieldid'].'\');'."\n";
                            }
                            $lastSearch = $this->field[$i]['search'];
                        }
                    } else {
                        $form .= $$pSearch->crudState($this->name, false);
                    }
                }
                
                // addFieldReturn para campos virtuais
                if ($withPhpCode && $this->field[$i]['virtual']) {
                    if ($this->field[$i]['name'] = $this->field[$i]['fieldid']) {
                        $form .= '$'.$lastSearch.'->addFieldReturn(\''.$this->field[$i]['name'].'\');'."\n";
                    } else {
                        $form .= '$'.$lastSearch.'->addFieldReturn(\''.$this->field[$i]['name'].'\',\''.$this->field[$i]['fieldid'].'\');'."\n";
                    }
                }
            }
            
            if ($withPhpCode && $haveFieldReturn) {
                $form .= '$'.$pSearch.'->crudState(\''.$this->name.'\');'."\n";
                $form .= '?>'."\n";
            }
        } else {
            $form .= '            </table>'."\n";
            $form .= '        </div>'."\n";
        }
        
        if ($verbose) {
            
            if (isset($this->param['includehead'])) {
                include $this->param['includehead'];
            }
            
            echo $form;
            
            if (isset($this->param['includefooter'])) {
                include $this->param['includefooter'];
            }
        }
        
        return $form;
    }
    
    /**
     * Gera o código HTML do crudList
     *
     * @param $verbose boolean: quando true imprime o código gerado
     *
     * @return string: código HTML do crudList
     */
    public function drawCrudList(bool $verbose=true) : string
    {
        $pCrudList = "{$this->ind}<div id=\"pCrudList_{$this->name}\" style=\"display:none;\">\n";
        
        $pCrudList .= $this->pCrudList->draw(false);
        for ($i = 0; $i < count($this->field); $i++) {
            
            if ($this->field[$i]['pk']) {
                $pCrudList .= $this->pCrudList->addFieldReturn($this->field[$i]['name'], $this->field[$i]['fieldid'], false, false);
            }
        }
        
        $pCrudList .= "{$this->ind}</div>\n";
        $pCrudList .= "{$this->ind}<script type=\"text/javascript\">\n";
        $pCrudList .= "{$this->ind}\tpCrudList_{$this->name}.crudName = '{$this->name}';\n";
        $pCrudList .= "{$this->ind}</script>\n";
        
        if ($verbose) {
            echo $pCrudList;
        }
            
        return $pCrudList;
    }

    /**
     * Desenha os botões de controle do crud
     * 
     * @param $verbose boolean: quando true imprime o html dos controles na tela
     *
     * @returns string: html dos controles
     */
    public function drawControls(bool $verbose=true) : string
    {
        $permC = $this->getPermission('c') ? '' : ' style="display:none;"';
        $permR = $this->getPermission('r') ? '' : ' style="display:none;"';
        $permU = $this->getPermission('u') ? '' : ' style="display:none;"';
        $permD = $this->getPermission('d') ? '' : ' style="display:none;"';
        
        $controls = "\n";
        $controls .= "{$this->ind}\t\t\t\t\t<span id=\"{$this->name}_controls\">\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t<span id=\"{$this->name}_control_new\" style=\"display:block;\">\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_bt_write_new\" $permC onclick=\"{$this->name}.bt_write_new()\">"._('Gravar')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_bt_copy_from\" $permC onclick=\"{$this->name}.bt_copyFrom()\">"._('Copiar de')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton warning\" id=\"{$this->name}_bt_clear\" $permC onclick=\"{$this->name}.bt_new()\">"._('Limpar')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_bt_search\" $permR onclick=\"{$this->name}.bt_search()\">"._('Listar')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t</span>\n";
        
        $controls .= "{$this->ind}\t\t\t\t\t\t<span id=\"{$this->name}_control_edit\" style=\"display:none;\">\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_bt_write_edit\" $permU onclick=\"{$this->name}.bt_write_edit()\">"._('Gravar Alterações')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton warning\" id=\"{$this->name}_bt_cancel_edit\" $permU onclick=\"{$this->name}.bt_cancel_edit()\">"._('Cancelar')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t</span>\n";
        
        $controls .= "{$this->ind}\t\t\t\t\t\t<span id=\"{$this->name}_control_view\" style=\"display:none;\">\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_bt_search_view\" $permR onclick=\"{$this->name}.bt_search()\">"._('Listar')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton warning\" id=\"{$this->name}_bt_edit\" $permU onclick=\"{$this->name}.bt_edit()\">"._('Alterar')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton danger\" id=\"{$this->name}_bt_delete\" $permD onclick=\"{$this->name}.bt_delete()\">"._('Excluir')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t\t<button class=\"pButton\" id=\"{$this->name}_bt_new\" $permC onclick=\"{$this->name}.bt_new()\">"._('Inserir Novo')."</button>\n";
        $controls .= "{$this->ind}\t\t\t\t\t\t</span>\n";
        $controls .= "{$this->ind}\t\t\t\t\t</span>\n";
        
        if ($this->param['autoclear']) {
            $controls .= "{$this->ind}\t\t\t\t\t<script type=\"text/javascript\">\n";
            $controls .= "{$this->ind}\t\t\t\t\t\twindow.onload = function() { {$this->name}.autoClear(); }\n";
            $controls .= "{$this->ind}\t\t\t\t\t</script>\n";
        }
        
        //seta a propriedade maxlength dos campos quando necessário
        $controls .= $this->initMaxLength();
        
        if ($verbose) {
            echo $controls;
        }
        
        return $controls;
    }
    
    /**
     * Inicializa os objetos no lado do cliente
     *
     * @return string: código js dos cruds recursivamente
     */
    private function initClientObject(): string
    {
        $clientObject = $this->drawSearch();
        
        // instancia o objeto PrumoSearch no cliente
        $clientObject .= "{$this->ind}<script type=\"text/javascript\">\n";
        $clientObject .= "{$this->ind}\t{$this->name} = new PrumoCrud('{$this->name}', '{$this->ajaxFile}');\n";
        
        // repassa condicionalmente o debug para o objeto ajax
        if (isset($this->param['debug']) && $this->param['debug']) {
            $clientObject .= "{$this->ind}\t{$this->name}.pAjax.debug = true;\n";
        }

        $onload = '';

        // repassa fields para o objeto cliente
        $fieldName = '';
        $fieldPk = '';
        $fieldUnique = '';
        $fieldId = '';
        $fieldLabel = '';
        $fieldType = '';
        $fieldNotNull = '';
        $fieldReadonly = '';
        $fieldNoCreate = '';
        $fieldNoUpdate = '';
        $fieldVirtual = '';
        $fieldDefault = '';
        $fieldTemplate = '';
        for ($i = 0; $i < count($this->field); $i++) {
            if (! empty($fieldName)) {
                $fieldName .= ',';
            }
            $fieldName .= '"'.$this->field[$i]['name'].'"';
            
            if (! empty($fieldPk)) {
                $fieldPk .= ',';
            }
            $fieldPk .= $this->field[$i]['pk'] ? 'true' : 'false';
            
            if (! empty($fieldUnique)) {
                $fieldUnique .= ',';
            }
            $fieldUnique .= $this->field[$i]['unique'] ? 'true' : 'false';
            
            if (! empty($fieldId)) {
                $fieldId .= ',';
            }
            $fieldId .= '"'.$this->field[$i]['fieldid'].'"';
            
            if (! empty($fieldLabel)) {
                $fieldLabel .= ',';
            }
            $fieldLabel .= '"'.$this->field[$i]['label'].'"';
            
            if (! empty($fieldType)) {
                $fieldType .= ',';
            }
            $fieldType .= '"'.$this->field[$i]['type'].'"';
            if ($this->field[$i]['type'] === 'file') {
                $fieldNameJs = $this->field[$i]['name'];
                $sizeLimit = $this->field[$i]['sizelimit'];

                $onload .= "{$this->ind}\t{$this->name}.fileReader['$fieldNameJs'] = new FileReader();\n";
                $onload .= "{$this->ind}\tdocument.getElementById('$fieldNameJs').addEventListener(\n";
                $onload .= "{$this->ind}\t\t'change',\n";
                $onload .= "{$this->ind}\t\tfunction() {\n";
                $onload .= "{$this->ind}\t\t\tlet input = document.getElementById('$fieldNameJs');\n";
                $onload .= "{$this->ind}\t\t\tlet fileSize = input.files[0].size;\n";
                $onload .= "{$this->ind}\t\t\tif ($sizeLimit > 0 && fileSize >= $sizeLimit) {\n";
                $onload .= "{$this->ind}\t\t\t\talert('Tamanho do arquivo informado maior que o máximo permitido');\n";
                $onload .= "{$this->ind}\t\t\t\tthis.value = '';\n";
                $onload .= "{$this->ind}\t\t\t\treturn false;\n";
                $onload .= "{$this->ind}\t\t\t}\n";
                $onload .= "{$this->ind}\t\t\t{$this->name}.fileReader['$fieldNameJs'].readAsBinaryString(input.files[0]);\n";
                $onload .= "{$this->ind}\t\t\treturn true;\n";
                $onload .= "{$this->ind}\t\t}\n";
                $onload .= "{$this->ind}\t);\n";
                $onload .= "{$this->ind}\tlet button_$fieldNameJs = document.createElement(\"button\");\n";
                $onload .= "{$this->ind}\tbutton_$fieldNameJs.innerText = 'Download';\n";
                $onload .= "{$this->ind}\tbutton_$fieldNameJs.setAttribute('class', 'pButton-outline');\n";
                $onload .= "{$this->ind}\tbutton_$fieldNameJs.setAttribute('id', '{$this->name}_bt_download');\n";
                $onload .= "{$this->ind}\tbutton_$fieldNameJs.setAttribute('onclick', '{$this->name}.bt_download(\'$fieldNameJs\')');\n";
                $onload .= "{$this->ind}\tdocument.getElementById('$fieldNameJs').parentNode.appendChild(button_$fieldNameJs);\n";
                $onload .= "\n";
            }
            
            if (! empty($fieldNotNull)) {
                $fieldNotNull .= ',';
            }
            $fieldNotNull .= $this->field[$i]['notnull'] ? 'true' : 'false';
            
            if (! empty($fieldReadonly)) {
                $fieldReadonly .= ',';
            }
            $fieldReadonly .= $this->field[$i]['readonly'] ? 'true' : 'false';
            
            $fieldNoCreate .= empty($fieldNoCreate) ? '' : ',';
            $fieldNoCreate .= $this->field[$i]['nocreate'] ? 'true' : 'false';
            
            $fieldNoUpdate .= empty($fieldNoUpdate) ? '' : ',';
            $fieldNoUpdate .= $this->field[$i]['noupdate'] ? 'true' : 'false';
            
            if (! empty($fieldVirtual)) {
                $fieldVirtual .= ',';
            }
            $fieldVirtual .= $this->field[$i]['virtual'] ? 'true' : 'false';
            
            if (! empty($fieldDefault)) {
                $fieldDefault .= ',';
            }
            $fieldDefault .= isset($this->field[$i]['default']) ? '"'.$this->field[$i]['default'].'"' : '""';
            
            if (! empty($fieldTemplate)) {
                $fieldTemplate .= ',';
            }
                
            $template = '"'.str_replace('"', '\"', $this->field[$i]['template']).'"';
            if (! substr_count($template, 'id=\"\"')) {
                if (substr_count($template, 'input')) {
                    $template = str_replace('input', 'input id=\"\"', $template);
                } else if (substr_count($template, 'select')) {
                    $template = str_replace('select', 'select id=\"\"', $template);
                } else if (substr_count($template, 'textarea')) {
                    $template = str_replace('textarea', 'textarea id=\"\"', $template);
                }
            }
            $fieldTemplate .= $template;
        }
        
        $clientObject .= "{$this->ind}\t{$this->name}.fieldName = Array($fieldName);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldPk = Array($fieldPk);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldUnique = Array($fieldUnique);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldId = Array($fieldId);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldLabel = Array($fieldLabel);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldType = Array($fieldType);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldNotNull = Array($fieldNotNull);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldReadonly = Array($fieldReadonly);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldNoCreate = Array($fieldNoCreate);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldNoUpdate = Array($fieldNoUpdate);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldVirtual = Array($fieldVirtual);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldDefault = Array($fieldDefault);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldTemplate = Array($fieldTemplate);\n";
        $clientObject .= "{$this->ind}\t{$this->name}.fieldValidator = ".json_encode($this->validator)."\n";

        // repassa as permissões CRUD para o cliente
        $clientObject .= "\n";
        $clientObject .= "{$this->ind}\t{$this->name}.permC = " . ($this->getPermission('c') ? 'true' : 'false').";\n";
        $clientObject .= "{$this->ind}\t{$this->name}.permR = " . ($this->getPermission('r') ? 'true' : 'false').";\n";
        $clientObject .= "{$this->ind}\t{$this->name}.permU = " . ($this->getPermission('u') ? 'true' : 'false').";\n";
        $clientObject .= "{$this->ind}\t{$this->name}.permD = " . ($this->getPermission('d') ? 'true' : 'false').";\n";
        
        // ligação do crud com o search
        $clientObject .= "\n";
        $clientObject .= "{$this->ind}\t{$this->name}.pSearch = pSearch_{$this->name};\n";
        
        // evento after Search
        $clientObject .= "\n";
        $clientObject .= "{$this->ind}\t{$this->name}.pSearch.afterSearch = function() {\n";
        $clientObject .= "{$this->ind}\t\t{$this->name}.doCopyFrom();\n";
        $clientObject .= "{$this->ind}\t}\n";
        
        // ligações 1 to 1
        $clientObject .= "\n";
        if ($this->parent1x1 != null) {
            $clientObject .= "{$this->ind}\t{$this->name}.addParent1x1({$this->parent1x1->name},'{$this->parent1x1Condition['fieldName']}', '{$this->parent1x1Condition['value']}', '{$this->parent1x1Condition['operator']}');\n";
        }
        
        if (! empty($this->parent1xN)) {
            $clientObject .= "\n";
            $clientObject .= "{$this->ind}\t{$this->name}.addParent1xN({$this->parent1xN});\n";
            $clientObject .= "{$this->ind}\t{$this->name}.pSearch.autoClick = false;\n";
        }
        
        //// trata onload
        if (isset($_GET['initialState'])) {
            $onload .= "{$this->ind}\t{$this->name}.initialState = '{$_GET['initialState']}';\n";
        }
        
        $initialStateNew = (isset($_GET['initialState']) && $_GET['initialState'] == 'new');
        if ($initialStateNew && $this->parent1x1 == null && empty($this->parent1xN)) {
            $onload .= "{$this->ind}\t\t{$this->name}.bt_new();\n";
        }
        
        // preenche os campos
        for ($i = 0; $i < count($this->field); $i++) {
            $id = $this->field[$i]['fieldid'];
            if (isset($_GET[$id]) && ! empty($_GET[$id]) && ($initialStateNew == false || $this->field[$i]['type'] != 'serial')) {
                if ($this->field[$i]['pk'] || $initialStateNew) {
                    $onload .= "{$this->ind}\t\t{$this->name}.inputSetValue('$id', '".str_replace("\n", '\n', urldecode($_GET[$id]))."');\n";
                } else {
                    $onload .= "{$this->ind}\t\t{$this->name}.initialFieldId.push('{$this->field[$i]['name']}');\n";
                    $onload .= "{$this->ind}\t\t{$this->name}.initialFieldValue.push('".str_replace("\n", '\n', urldecode($_GET[$id]))."');\n";
                }
            }
        }
        if ($initialStateNew) {
            $onload .= "{$this->ind}\t{$this->name}.retrieveVirtual();\n";
        }
        
        // verifica se deve abrir algum registro ou a listagem
        if ($this->parent1x1 == null && empty($this->parent1xN)) {
            
            $countPk = 0;
            $countPkValue = 0;
            
            for ($i = 0; $i < count($this->field); $i++) {
                if ($this->field[$i]['pk']) {
                    $countPk++;
                    $id = $this->field[$i]['fieldid'];
                    if (isset($_GET[$id]) && ! empty($_GET[$id])) {
                        $countPkValue++;
                    }
                }
            }
            
            $autoList = (! isset($this->param['autolist']) || $this->param['autolist'] != 'false');
            
            if ($initialStateNew == false && $autoList == true) {
                if ($countPk > 0 && $countPk == $countPkValue) {
                    $onload .= "{$this->ind}\t\t{$this->name}.doRetrieve();\n";
                } else if ($this->pCrudList) {
                    $onload .= "{$this->ind}\t\t{$this->name}.bt_search();\n";
                }
            }
        }
        
        if (! empty($onload)) {
            $clientObject .= "{$this->ind}\twindow.addEventListener(\"load\", function() {\n";
            $clientObject .= "{$this->ind}\t$onload\n";
            $clientObject .= "{$this->ind}\t});\n";
        }
        
        $clientObject .= "{$this->ind}\tdocument.pCrud.push({$this->name});\n";
        $clientObject .= "{$this->ind}</script>\n";
        $clientObject .= $this->initClientObject1x1();
        
        if ($initialStateNew && $this->parent1x1 == null && empty($this->parent1xN)) {
            $clientObject .= "{$this->ind}<script type=\"text/javascript\">\n";
            $clientObject .= "{$this->ind}\twindow.addEventListener(\"load\", function() {\n";
            $clientObject .= "{$this->ind}\t\t{$this->name}.visibleSon1x1();\n";
            $clientObject .= "{$this->ind}\t});\n";
            $clientObject .= "{$this->ind}</script>\n";
        }
        
        return $clientObject;
    }
    
    /**
     * Inicializa os objetos no lado do cliente com relacionamento 1x1 recursivamente
     *
     * @return string: código js dos cruds filhos recursivamente
     */
    private function initClientObject1x1() : string
    {
        $clientObject = '';
        for ($i = 0; $i < count($this->son1x1); $i++) {
            $clientObject .= $this->son1x1[$i]->initClientObject();
        }
        return $clientObject;
    }
    
    /**
     * Verifica quais campos possuem explicito o atributo size e seta a propriedade maxlength
     */
    private function initMaxLength() : string
    {
        $out = '';
        for ($i = 0; $i < count($this->field); $i++) {
            
            if (! empty($this->field[$i]['size'])) {
                $out .= "{$this->ind}\tinputField = document.getElementById('{$this->field[$i]['fieldid']}');\n";
                $out .= "{$this->ind}\tif (inputField.getAttribute('maxlength') == undefined) {\n";
                $out .= "{$this->ind}\t\tinputField.setAttribute('maxlength',{$this->field[$i]['size']});\n";
                $out .= "{$this->ind}\t}\n";
            }
        }
        
        return empty($out) ? '' : "{$this->ind}<script type=\"text/javascript\">\n$out{$this->ind}</script>\n";
    }
    
    /**
     * configura a propriedade $this->permission buscando as informações no banco de dados
     *
     * @param $routine string: nome da rotina
     */
    private function setPermissions(string $routine)
    {
        $arrPermission = getPermission($routine);
        
        $permission = '';
        if ($arrPermission['c'] == true) {
            $permission .= 'c';
        }
        if ($arrPermission['r'] == true) {
            $permission .= 'r';
        }
        if ($arrPermission['u'] == true) {
            $permission .= 'u';
        }
        if ($arrPermission['d'] == true) {
            $permission .= 'd';
        }
        
        $this->permission = $permission;
    }
    
    /**
     * Verifica as permissões do objeto para operações CRUD
     * 
     * @params $perm string: "c", "r", "u" ou "d" acordo com a operação desejada
     *
     * @returns boolean
     */
    public function getPermission(string $perm) : bool
    {
        for ($i = 0; $i < strlen($this->permission); $i++) {
            if ($this->permission[$i] == $perm) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Propara a propriedade action para todos os relacionamentos 1x1 recussivamente
     */
    private function cascadeAction()
    {
        for ($i = 0; $i < count($this->son1x1); $i++) {
            $this->son1x1[$i]->action = $this->action;
            $this->son1x1[$i]->cascadeAction();
        }
    }
    
    /**
     * Faz o download de um arquivo (campo do tipo file)
     */
    public function downloadFile() : void
    {
        $downloadField = '';
        for ($i=0; $i < count($this->field); $i++) {
            if ($_GET['downloadField'] == $this->field[$i]['name'] && $this->field[$i]['type'] == 'file') {
                $downloadField = $this->field[$i]['name'];
            }
        }
        
        $tableName = $this->param['tablename'];
        $schema = $this->pConnection->getSchema($this->param['schema']);

        $condition = '';
        for ($i = 0; $i < count($this->field); $i++) {
            if ($this->field[$i]['pk']) {
                $condition .= empty($condition) ? ' WHERE ' : ' AND ';
                $condition .= $this->field[$i]['name'].'=:new_'.$this->field[$i]['name'].':';
            }
        }

        $sql = <<<SQL
            SELECT
                $downloadField
            FROM $schema$tableName
            $condition
        SQL;
        $sql = $this->sqlValues($sql, $_GET);
        $arquivo = $this->pConnection->sqlQuery($sql);

        if ($arquivo) {
            $dados = json_decode($arquivo, true);

            header('Content-type: ' . $dados['mime-type']);
            header('Content-Disposition:inline; filename="' . $dados['nome'] . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');

            echo base64_decode($dados['conteudo']);
        } else {
            echo 'Nenhum arquivo encontrado. ';
            echo '<a href="javascript: window.close()">Voltar</a>';
        }
    }
    
    /**
     * Decide qual ação tomar de acordo com os parametros passados via GET ou POST
     */
    public function autoInit()
    {
        if (isset($_GET['ddl']) || isset($_GET['htmlcode']) || isset($_GET['executeddl'])) {
            
            header('Content-type: text/html; charset=UTF-8');
            pProtect('prumo_devtools');
            
            if (isset($_GET['htmlcode'])) {
                $htmlCode = $this->drawForms(false,true);
                $htmlCode = str_replace('<','&lt;',$htmlCode);
                $htmlCode = str_replace('>','&gt;',$htmlCode);
                echo "<pre>\n$htmlCode\n</pre>\n";
            }
        
            if (isset($_GET['ddl'])) {
                echo "<pre>\n".$this->ddl()."\n</pre>";
            }
            
            if (isset($_GET['executeddl'])) {
                echo $this->executeDdl();
            }
        } else {

            $this->action = $_POST[$this->name.'_action'] ?? $_GET[$this->name.'_action'] ?? null;
            if ($this->action != null) {
                $this->cascadeAction();
                
                switch ($this->action) {
                    case 'c':
                        if ($this->getPermission('c')) {
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doCreate();
                        } else {
                            if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                                pLogAcessDenied($this->param['routine'], 'c');
                            }
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doAccessDenied();
                        }

                        break;
                    case 'r':
                        if ($this->getPermission('r')) {
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doRetrieve();
                        } else {
                            if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                                pLogAcessDenied($this->param['routine'], 'r');
                            }
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doAccessDenied();
                        }

                        break;
                    case 'u':
                        if ($this->getPermission('u')) {
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doUpdate();
                        } else {
                            if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                                pLogAcessDenied($this->param['routine'], 'u');
                            }
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doAccessDenied();
                        }

                        break;
                    case 'd':
                        if ($this->getPermission('d')) {
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doDelete();
                        } else {
                            if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                                pLogAcessDenied($this->param['routine'], 'd');
                            }
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doAccessDenied();
                        }

                        break;
                    case 'download':
                        if ($this->getPermission('r')) {
                            if (isset($_GET['crudDownload'])) {
                                $objName = $_GET['crudDownload'];
                                global $$objName;
                                $$objName->downloadFile();
                            } else {
                                $this->downloadFile();
                            }
                        } else {
                            if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                                pLogAcessDenied($this->param['routine'], 'r');
                            }
                            Header('Content-type: application/xml; charset=UTF-8');
                            echo $this->doAccessDenied();
                        }
                        break;
                    default:
                        echo 'Error';
                        break;
                }
            } else {

                if (isset($_POST['pSearch_'.$this->name.'_action']) && ! empty($_POST['pSearch_'.$this->name.'_action'])) {
                    $this->pSearch->autoInit();
                } else if (isset($_POST['pCrudList_'.$this->name.'_action']) && ! empty($_POST['pCrudList_'.$this->name.'_action'])) {
                    
                    if ($this->getPermission('r')) {
                        Header('Content-type: application/xml; charset=UTF-8');
                        echo $this->pCrudList->autoInit();
                    } else {
                        
                        if (isset($this->param['routine']) && ! empty($this->param['routine'])) {
                            pLogAcessDenied($this->param['routine'], 'r');
                        }
                        
                        Header('Content-type: application/xml; charset=UTF-8');
                        echo $this->doAccessDenied();
                    }
                } else {
                    
                    echo $this->initClientObject();
                    
                    if (isset($this->param['drawform']) && $this->param['drawform']) {
                        $this->drawForms();
                    }
                }
            }
        }
    }
    
    /**
     * Monta o código para cada objeto de forma recursiva para relacionamentos 1x1
     */
    public function ddl() : string
    {
        $tableName = $this->param['tablename'];
        $schema = $this->pConnection->getSchema($this->param['schema']);
        
        $code  = 'CREATE TABLE '.$schema.$tableName."\n";
        $code .= '('."\n";
        
        for ($i = 0; $i < count($this->field); $i++) {
            
            // campos da tabela (exceto virtuais)
            if (! isset($this->field[$i]['virtual']) || $this->field[$i]['virtual'] == false) {
                
                $name = $this->field[$i]['name'];
                $type = ' '.$this->pConnection->dbType($this->field[$i]['type']);
                $notNull = $this->field[$i]['notnull'] ? ' NOT NULL' : '';
                $default = (isset($this->field[$i]['default']) && ! empty($this->field[$i]['default'])) ? ' DEFAULT '.$this->field[$i]['default'] : '';
                
                if (isset($this->field[$i]['size']) && ! empty($this->field[$i]['size'])) {
                    
                    $size = '('.$this->field[$i]['size'].')';
                    
                    // troca o ponto por virgula para os campos do tipo numeric
                    if ($this->field[$i]['type'] == 'numeric' || $this->field[$i]['type'] == 'money') {
                        $size = str_replace('.', ',', $size);
                    }
                } else {
                    $size = '';
                }
                
                $code .= '  '.$name.$type.$size.$notNull.$default.','."\n";
            }
        }
        
        // chave primária
        $pk = '';
        for ($i = 0; $i < count($this->field); $i++) {
            
            if ($this->field[$i]['pk']) {
                
                if (! empty($pk)) {
                    $pk .= ',';
                }
                
                $pk .= $this->field[$i]['name'];
            }
        }
        $code .= '  CONSTRAINT '.$tableName.'_pkey PRIMARY KEY ('.$pk.')';
        
        // monta chave estrangeira
        if ($this->parent1x1 != null) {
            
            $fk = '';
            for ($i = 0; $i < count($this->parent1x1->field); $i++) {
                
                if ($this->parent1x1->field[$i]['pk']) {
                    
                    if (! empty($fk)) {
                        $fk .= ',';
                    }
                    
                    $fk .= $this->parent1x1->field[$i]['name'];
                }
            }
            
            $code .= ','."\n";
            $code .= '  CONSTRAINT '.$tableName.'_'.str_replace(',', '_', $pk).'_fkey FOREIGN KEY ('.$pk.')'."\n";
            $code .= '      REFERENCES '.$this->pConnection->getSchema($this->parent1x1->param['schema']).$this->parent1x1->param['tablename'].' ('.$fk.') MATCH SIMPLE'."\n";
            $code .= '      ON UPDATE CASCADE ON DELETE CASCADE'."\n";
        } else {
            $code .= "\n";
        }
        
        $code .= ');'."\n";
            
        // Aplica codigo dos objetos filhos
        for ($i = 0; $i < count($this->son1x1); $i++) {
            
            $code .= "\n";
            $code .= $this->son1x1[$i]->ddl();
        }
        
        return $code;
    }
    
    /**
     * Cria a tabela no banco de dados, inclusive todas as relações 1x1
     */
    public function executeDdl() : string
    {
        $sqlOk = $this->pConnection->sqlQuery($this->ddl());
        if ($sqlOk === false) {
            return $this->pConnection->getErr();
        }
        
        return _('Código SQL executado');
    }
    
}

