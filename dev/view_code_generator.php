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

require_once 'prumo.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/dev/func_code_generator.php';

pProtect('prumo_devtools');

// datalist para o arquivo
$fileList = scandir($GLOBALS['pConfig']['appPath']);
sort($fileList);
$dataList = '<datalist id="list_file">'."\n";
for ($i = 0; $i < count($fileList); $i++) {
    if (substr($fileList[$i],0,5) == 'ctrl_') {
        $dataList .= '            <option value="'.$fileList[$i].'">'.$fileList[$i].'</option>'."\n";
    }
}
$dataList .= '        </datalist>'."\n";

// datalist objetos PrumoSearch
$arrObjSearch = getObjSearchList();
$dataListObjSearch = '<datalist id="list_obj_search">'."\n";
for ($i = 0; $i < count($arrObjSearch); $i++) {
    $dataListObjSearch .= '                            <option value="'.$arrObjSearch[$i].'">'.$arrObjSearch[$i].'</option>'."\n";
}
$dataListObjSearch .= '                        </datalist>'."\n";

//datalist routines
$sql  = 'SELECT'."\n";
$sql .= '    routine,'."\n";
$sql .= '    description'."\n";
$sql .= 'FROM '.$pConnectionPrumo->getSchema().'routines'."\n";
$sql .= 'WHERE enabled='.pFormatSql(true, 'boolean').';';
$routine = $pConnectionPrumo->sql2Array($sql);

$dataListRoutine = '<datalist id="list_routine">'."\n";
for ($i = 0; $i < count($routine); $i++) {
    if (empty($routine[$i]['description'])) {
        $dataListRoutine .= '                        <option value="'.$routine[$i]['routine'].'">'.$routine[$i]['routine'].'</option>'."\n";
    } else {
        $dataListRoutine .= '                        <option value="'.$routine[$i]['routine'].'">'.$routine[$i]['routine'].' - '.$routine[$i]['description'].'</option>'."\n";
    }

}
$dataListRoutine .= '                    </datalist>'."\n";

//datalist link do atalho para menu no PrumoSearch
$sql  = 'SELECT'."\n";
$sql .= '    routine,'."\n";
$sql .= '    tree'."\n";
$sql .= 'FROM '.$pConnectionPrumo->getSchema().'v_menu;';
$menu = $pConnectionPrumo->sql2Array($sql);

$dataListMenuShortcut = '<datalist id="list_menu" style="{width:600px}">'."\n";
for ($i = 0; $i < count($menu); $i++) {
    $label = $menu[$i]['tree'].' ('.$menu[$i]['routine'].')';
    $dataListMenuShortcut .= '                        <option value="'.$menu[$i]['routine'].'">'.$label.'</option>'."\n";
}
$dataListMenuShortcut .= '                    </datalist>'."\n";

?>

<div id="div_debug" style="display:none">
    <table>
        <tr>
            <td>
                state:
                <br /><input type="text" id="state" value="" />
            </td>
            <td>
                lineIndex:
                <br /><input type="text" id="lineIndex" value="3" />
            </td>
            <td>
                session:
                <br /><input type="text" id="session" />
            </td>
            <td>
                objName:
                <br /><input type="text" id="objName" />
            </td>
            <td>
                lastKeyCode:
                <br /><input type="text" id="lastKeyCode" />
            </td>
        </tr>
    </table>
</div>

<fieldset>
<legend><?=_('Gerador de Código PrumoCrud Framework');?></legend>
<div style="padding:5px">
    
    <div style="float:left">
        Arquivo controller: <input id="file_name" type="text" size="30" list="list_file" onchange="txFileName_change()" />
        <?=$dataList;?>
        <button id="bt_open" onclick="btOpen_click()">Abrir</button> 
        <button id="bt_save" onclick="btSalve_click()">Salvar</button>
    </div>
    
    <div style="text-align:right">
        <button onclick="code.btPrumo_click()" id="btPrumo">+PrumoCrud</button> 
        <button onclick="code.btPrumoSearch_click()" id="btPrumoSearch">+PrumoSearch</button> 
        <button onclick="code.btPrumoList_click()" id="btPrumoList" style="display:none">+PrumoList</button>
    </div>
    
    <div style="text-align:center">
        <textarea id="prumo_code" wrap="off" style="font: 12px Courier New; height: 200px; width: 99%;" onchange="txPrumoCode_change()"></textarea>
    </div>
    
    <div id="div_prumo" style="display:none">
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"><?=_('Nome do Objeto PrumoCrud');?>:</td>
                <td class="prumoFormFields"><input id="prumo_obj_name" type="text" size="40" onchange="code.writePrumo()" />*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Título da Janela');?> (title):</td>
                <td class="prumoFormFields"><input id="prumo_title" type="text" size="40" onchange="code.writePrumo()" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Schema da Tabela');?> (schema):</td>
                <td class="prumoFormFields"><input id="prumo_schema" type="text" size="40" onchange="code.writePrumo()" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Nome da Tabela');?> (tableName):</td>
                <td class="prumoFormFields"><input id="prumo_tablename" type="text" size="40" onchange="code.writePrumo()" />*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Rotina');?> (routine):</td>
                <td class="prumoFormFields">
                    <input id="prumo_routine" type="text" size="40" onchange="code.writePrumo()" list="list_routine" /> <a href="index.php?page=prumo_routines" target="_blank"><?=_('Rotinas, permissões e menus');?></a>
                    <?=$dataListRoutine;?>
                </td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class=""><input id="prumo_capslock" type="checkbox" onchange="code.writePrumo()" /><?=_('Tudo maiúsculo para todos os campos');?> (capsLock)</td>
            </tr>
        </table>
        
        <div id="div_prumo_mais_opcoes" style="display:none">
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class=""><input id="prumo_audit" type="checkbox" onchange="code.writePrumo()" /><?=_('Auditoria');?> (audit)</td>
                </tr>
            </table>
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('Tipo de Relacionamento');?></td>
                    <td class="prumoFormFields">
                        <select id="prumo_parent_type" onchange="code.writePrumo()">
                            <option value="">-</option>
                            <option value="1x1">1x1</option>
                            <option value="1xN">1xN</option>
                        </select>
                    </td>
                </tr>
            </table>
    
            <div id="crudObjCrud1x1_form" style="display:none;">
                <table class="prumoFormTable">
                    <tr>
                        <td class="prumoFormLabel"><?=_('Objeto pai Relacionamento 1x1');?>:</td>
                        <td class="prumoFormFields"><input id="prumo_name_parent1x1" type="text" size="40" onchange="code.writePrumo()" /><?php /*$searchObjCrud1x1->makeButton();*/ ?> *</td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><?=_('Campo Condicional no Objeto Pai');?>:</td>
                        <td class="prumoFormFields"><input id="prumo_parent_field_condition" type="text" size="40" onchange="code.writePrumo()" /></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><?=_('Valor da Condição');?>:</td>
                        <td class="prumoFormFields"><input id="prumo_condition_value" type="text" size="40" onchange="code.writePrumo()" /></td>
                    </tr>
                </table>
            </div>
        
            <div id="crudObjCrud1xn_form" style="display:none;">
                <table class="prumoFormTable">
                    <tr>
                        <td class="prumoFormLabel"><?=_('Objeto pai Relacionamento 1xN');?>:</td>
                        <td class="prumoFormFields"><input id="prumo_name_parent1xn" type="text" size="40" onchange="code.writePrumo()" /><?php /*$searchObjCrud1xn->makeButton();*/ ?> *</td>
                    </tr>
                </table>
            </div>
    
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('Qtd Linhas da Tabela');?> (pageLines):</td>
                    <td class="prumoFormFields"><input id="prumo_pagelines" type="text" size="9" onchange="code.writePrumo()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Permissao Fixa');?> (permission):</td>
                    <td class="prumoFormFields"><input id="prumo_permission" type="text" size="40" onchange="code.writePrumo()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Incluir arquivo de cabeçalho');?> (includeHead):</td>
                    <td class="prumoFormFields"><input id="prumo_include_head" type="text" size="40" onchange="code.writePrumo()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Incluir arquivo de Rodapé');?> (includeFooter):</td>
                    <td class="prumoFormFields"><input id="prumo_include_footer" type="text" size="40" onchange="code.writePrumo()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Ação ao duplicar registro');?> (onDuplicate):</td>
                    <td class="prumoFormFields">
                        <select id="prumo_onduplicate" onchange="code.writePrumo()">
                            <option value="">-</option>
                            <option value="update">update</option>
                        <option value="error">error</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_list" type="checkbox" checked="checked" onchange="code.writePrumo()" /><?=_('Possui Listagem');?> (list) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_auto_list" type="checkbox" checked="checked" onchange="code.writePrumo()" /><?=_('Listagem automática');?> (autoList) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_drawform" type="checkbox" onchange="code.writePrumo()" /><?=_('Desenhar Formulário');?> (drawForm) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_fastcreate" type="checkbox" onchange="code.writePrumo()" /><?=_('Campos de inserção dentro do grid da listagem');?> (fastCreate) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_fastupdate" type="checkbox" onchange="code.writePrumo()" /><?=_('Campos de aLteração dentro do grid da listagem');?> (fastUpdate) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_fastdelete" type="checkbox" onchange="code.writePrumo()" /><?=_('Botões de delete dentro do grid da listagem');?> (fastDelete) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_autoclick" type="checkbox" onchange="code.writePrumo()" /><?=_('Auto Click');?> (autoclick) </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_debug" type="checkbox" onchange="code.writePrumo()" /><?=_('Modo Depuração');?> (debug) </td>
                </tr>
            </table>
        </div>
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields">
                    <button onclick="code.btPrumoOk_click()">OK</button> 
                    <button onclick="code.btPrumoMaisOpcoes_click()" id="bt_prumo_mais_opcoes">Mais Opções</button> 
                </td>
            </tr>
        </table>
    </div>
    
    <div id="div_prumo_field" style="display:none">
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_field_pk" type="checkbox" onchange="code.writePrumoField()" /><?=_('Chave Primária');?> (pk) *</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Nome do campo');?> (name):</td>
                <td class="prumoFormFields"><input id="prumo_field_name" type="text" size="40" onchange="code.writePrumoField()" />*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Rótulo');?> (label):</td>
                <td class="prumoFormFields"><input id="prumo_field_label" type="text" size="40" onchange="code.writePrumoField()" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Tipo de dado');?> (type):</td>
                <td class="prumoFormFields">
                    <select id="prumo_field_type" onchange="code.writePrumoField()">
                        <option value="string"><?=_('texto');?></option>
                        <option value="text"><?=_('texto longo');?></option>
                        <option value="integer"><?=_('inteiro');?></option>
                        <option value="serial"><?=_('inteiro auto incrementado');?></option>
                        <option value="numeric"><?=_('numérico');?></option>
                        <option value="date"><?=_('data');?></option>
                        <option value="time"><?=_('hora');?></option>
                        <option value="timestamp"><?=_('data e hora');?></option>
                        <option value="boolean"><?=_('boleano');?></option>
                    </select>*
                </td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Tamanho');?> (size):</td>
                <td class="prumoFormFields"><input id="prumo_field_size" type="text" size="10" onchange="code.writePrumoField()" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_field_notnull" type="checkbox" onchange="code.writePrumoField()" /><?=_('Não pode ser nulo');?> (notNull)</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_field_visible" type="checkbox" onchange="code.writePrumoField()" /><?=_('Visível na listagem');?> (visible)</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_field_readonly" type="checkbox" onchange="code.writePrumoField()" /><?=_('Somente leitura');?> (readonly)</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_field_capslock" type="checkbox" onchange="code.writePrumoField()" /><?=_('Tudo maiúsculo para este campo');?> (capsLock)</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_field_audit" type="checkbox" onchange="code.writePrumoField()" /><?=_('Auditoria');?> (audit)</td>
            </tr>
        </table>
        
        <div id="div_prumo_field_mais_opcoes" style="display:none">
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('id html');?> (fieldId):</td>
                    <td class="prumoFormFields"><input id="prumo_field_fieldid" type="text" size="40" onchange="code.writePrumoField()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Relação com PrumoSearch');?> (search):</td>
                    <td class="prumoFormFields">
                        <input id="prumo_field_search" type="text" size="40" onchange="code.writePrumoField()" list="list_obj_search" />
                        <?=$dataListObjSearch;?>
                    </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Ordenação');?> :</td>
                    <td class="prumoFormFields"><input id="prumo_field_order" type="text" size="10" onchange="code.writePrumoField()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Valor padrão');?> (default):</td>
                    <td class="prumoFormFields"><input id="prumo_field_default" type="text" size="40" onchange="code.writePrumoField()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Modelo do campo HTML');?> (template):</td>
                    <td class="prumoFormFields"><input id="prumo_field_template" type="text" size="40" onchange="code.writePrumoField()" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_field_virtual" type="checkbox" onchange="code.writePrumoField()" /><?=_('Campo Virtual (pertence a outra tabela)');?> (virtual)</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_field_nocreate" type="checkbox" onchange="code.writePrumoField()" /><?=_('Não participa do Create');?> (noCreate)</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_field_noupdate" type="checkbox" onchange="code.writePrumoField()" /><?=_('Não participa do Update');?> (noUpdate)</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_field_unique" type="checkbox" onchange="code.writePrumoField()" /><?=_('Valida unicidade');?> (unique)</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_field_nohtml" type="checkbox" onchange="code.writePrumoField()" /><?=_('Não desenhar o campo na tela');?> (nohtml)</td>
                </tr>
            </table>
        </div>
        
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields">
                    <button onclick="code.btPrumoFieldOk_click()">OK</button>
                    <button onclick="code.btPrumoFieldMaisOpcoes_click()" id="bt_prumo_field_mais_opcoes">Mais Opções</button> 
                </td>
            </tr>
        </table>
    </div>
    
    <div id="div_prumo_search" style="display:none">
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"><?=_('Nome do Objeto');?>:</td>
                <td class="prumoFormFields"><input id="prumo_search_obj_name" type="text" onchange="code.writePrumoSearch()" size="40" autofocus="autofocus" />*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Título da Janela');?> (title):</td>
                <td class="prumoFormFields"><input id="prumo_search_obj_title" type="text" onchange="code.writePrumoSearch()" size="40" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Schema da Tabela');?> (schema):</td>
                <td class="prumoFormFields"><input id="prumo_search_obj_schema" type="text" onchange="code.writePrumoSearch()" size="40" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Nome da Tabela');?> (tableName):</td>
                <td class="prumoFormFields"><input id="prumo_search_obj_tablename" type="text" onchange="code.writePrumoSearch()" size="40" />*</td>
            </tr>
        </table>
        <div id="div_prumo_search_mais_opcoes" style="display:none">
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('Qtd Linhas da Tabela');?> (pageLines):</td>
                    <td class="prumoFormFields"><input id="prumo_search_obj_pagelines" type="text" onchange="code.writePrumoSearch()" size="9" /></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Atalho para ítem de menu');?> (menuShortcut):</td>
                    <td class="prumoFormFields">
                        <input id="prumo_search_obj_menushortcut" type="text" onchange="code.writePrumoSearch()" size="80" list="list_menu" />
                        <?=$dataListMenuShortcut;?>
                    </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_search_obj_modal" type="checkbox" onchange="code.writePrumoSearch()" checked="checked" /><?=_('Modal');?> (modal)</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_search_obj_autofilter" type="checkbox" onchange="code.writePrumoSearch()" checked="checked" /><?=_('Auto Filtro');?> (autoFilter)</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><br /></td>
                    <td class="prumoFormFields"><input id="prumo_search_obj_debug" type="checkbox" onchange="code.writePrumoSearch()" /><?=_('Modo Depuração');?> (debug)</td>
                </tr>
            </table>
        </div>
        
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields">
                    <button onclick="code.btPrumoSearchOk_click()">OK</button> 
                    <button onclick="code.btPrumoSearchMaisOpcoes_click()" id="bt_prumo_search_mais_opcoes">Mais Opções</button> 
                </td>
            </tr>
        </table>
    </div>
    
    <div id="div_prumo_search_field" style="display:none">
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_search_field_pk" onchange="code.writePrumoSearchField()" type="checkbox" /><?=_('Chave Primária');?> (pk) *</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Nome do campo');?> (name):</td>
                <td class="prumoFormFields"><input id="prumo_search_field_name" onchange="code.writePrumoSearchField()" type="text" size="30" />*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Rótulo');?> (label):</td>
                <td class="prumoFormFields"><input id="prumo_search_field_label" onchange="code.writePrumoSearchField()" type="text" size="30" /></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Tipo de dado');?> (type):</td>
                <td class="prumoFormFields">
                    <select id="prumo_search_field_type" onchange="code.writePrumoSearchField()">
                        <option value="string"><?=_('texto');?></option>
                        <option value="text"><?=_('texto longo');?></option>
                        <option value="integer"><?=_('inteiro');?></option>
                        <option value="serial"><?=_('inteiro auto incrementado');?></option>
                        <option value="numeric"><?=_('numérico');?></option>
                        <option value="date"><?=_('data');?></option>
                        <option value="time"><?=_('hora');?></option>
                        <option value="timestamp"><?=_('data e hora');?></option>
                        <option value="boolean"><?=_('boleano');?></option>
                    </select>*
                </td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><br /></td>
                <td class="prumoFormFields"><input id="prumo_search_field_visible" onchange="code.writePrumoSearchField()" type="checkbox" /><?=_('Visível');?> (visible)</td>
            </tr>
        </table>
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields">
                    <button onclick="code.btPrumoSearchFieldOk_click()">OK</button> 
                </td>
            </tr>
        </table>
    </div>
        
</div>

<?php

$tabOtherCode = new PrumoTab();
$tabOtherCode->addTab('view',_('Código da VIEW'));
$tabOtherCode->addTab('ddl',_('Código DDL'));
$tabOtherCode->init();
$tabOtherCode->showTab(true,'view');
?>
<script type="text/javascript">
    tabOtherCode.hide();
</script>

</fieldset>

<br />

<script type="text/javascript" src="dev/view_code_generator.js"></script>
