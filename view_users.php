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
pProtect('prumo_users');

require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_users.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_syslogin_groups.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_search_users.php';
?>

<fieldset>
<legend><?=_('Usuários');?></legend>
<div id="crudUsers_form">
    <br />
    <table class="prumoFormTable">
        <tr>
            <td class="prumoFormLabel"><?=_('Usuário');?>:</td>
            <td class="prumoFormFields"><input id="username" type="text" size="45" autofocus="autofocus" />*</td>
        </tr>
        <tr>
            <td class="prumoFormLabel"><?=_('Nome completo');?>:</td>
            <td class="prumoFormFields"><input id="fullname" type="text" size="45" />*</td>
        </tr>
        <tr>
            <td class="prumoFormLabel"></td>
            <td class="prumoFormFields"><input id="enabled" type="checkbox" checked="checked" /> <?=_('Ativo');?></td>
        </tr>
        <tr>
            <td class="prumoFormLabel"></td>
            <td class="prumoFormFields"><?php $crudUsers->drawControls();?></td>
        </tr>
    </table>
    
    <fieldset id="div_groups" style="display:none">
    <legend><?=_('Grupos do usuário');?></legend>
    
        <br />
        
        <div id="div_lists"></div>
        
        <table align="center" width="600">
            <tr>
                <td align="center">
                    <br />
                    <button class="pButton" id="bt_write" onclick="btWrite_click()"><?=_('Gravar');?></button>
                    <button class="pButton" id="bt_copy_from" onclick="searchUsers.goSearch();"><?=_('Copiar de');?></button>
                    <button class="pButton warning" id="bt_cancel" onclick="refreshLists()"><?=_('Cancelar');?></button>
                </td>
            </tr>
        </table>
        
        <br />
        <?=_('OBS: CTRL + Clique para selecionar mais de um');?>
    </fieldset>
    <br />
    * <?=_('campos de preenchimento obrigatório');?>
</div>

<input type="hidden" id ="username_copy_from" />
<?php
$crudUsers->drawCrudList();
$searchUsers->addFieldReturn('username', 'username_copy_from');
?>

</fieldset>

<script type="text/javascript">
    
    function refreshLists() {
        
        if (crudUsers.permU) {
            document.getElementById('bt_write').removeAttribute('disabled');
            document.getElementById('bt_copy_from').removeAttribute('disabled');
            document.getElementById('bt_cancel').removeAttribute('disabled');
        } else {
            document.getElementById('bt_write').setAttribute('disabled', 'disabled');
            document.getElementById('bt_copy_from').setAttribute('disabled', 'disabled');
            document.getElementById('bt_cancel').setAttribute('disabled', 'disabled');
        }
        
        pSimpleAjax('prumo/ctrl_user_groups.php', 'username='+document.getElementById('username').value, 'div_lists');
    }
    
    function move(selectSource, selectDestination, selectAll) {
        
        var sel = Array();        
        for (i in selectSource.options) {
            if (selectSource.options[i].selected || selectAll) {
                
                var groupName = selectSource.options[i].value
                if (groupName != undefined) {
                    sel.push(groupName);
                    
                    var option = document.createElement("option");
                    option.text = groupName;
                    option.value = groupName;
                    selectDestination.add(option);
                }
            }
        }
        
        for (i in sel) {
            for (j in selectSource.options) {
                if (selectSource.options[j].value == sel[i]) {
                    selectSource.remove(j);
                    break;
                }
            }
        }
    }
    
    function btAdd_Click() {
        move(document.getElementById('available_group'), document.getElementById('active_group'), false);
    }
    
    function btRemove_Click() {
        move(document.getElementById('active_group'), document.getElementById('available_group'), false);
    }
    
    function btAddAll_Click() {
        move(document.getElementById('available_group'), document.getElementById('active_group'), true);
    }
    
    function btRemoveAll_Click() {
        move(document.getElementById('active_group'), document.getElementById('available_group'), true);
    }
    
    function btWrite_click() {
        
        var param = 'username='+document.getElementById('username').value;
        param += '&action=write';

        var select = document.getElementById('active_group');        
        for (i in select.options) {
            
            var groupName = select.options[i].value
            if (groupName != undefined) {
                param += '&groupname[]='+groupName;
            }
        }
        
        pSimpleAjax('prumo/ctrl_user_groups.php', param, 'div_lists');
    }
    
    crudUsers.onStateChange = function() {
        if (this.state == 'view') {
            document.getElementById('div_groups').style.display = 'block';
            refreshLists();
        } else {
            document.getElementById('div_groups').style.display = 'none';
        }
    }
    
    searchUsers.afterSearch = function() {
        pSimpleAjax('prumo/ctrl_user_groups.php', 'username='+document.getElementById('username_copy_from').value, 'div_lists');
    }
</script>
