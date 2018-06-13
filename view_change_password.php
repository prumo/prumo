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

pProtect('prumo_changePassword');

?>

<script>

    pAjaxChangePassword = new prumoAjax('<?=$GLOBALS['pConfig']['prumoWebPath'];?>/ctrl_change_password.php');
    pAjaxChangePassword.ajaxFormat = 'xml';
    pAjaxChangePassword.ajaxXmlOk = function() {
        alert(gettext('Senha alterada com sucesso!'));
        document.getElementById('password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('new_password_confirm').value = '';
    }

    function goChangePassword() {
        var password = document.getElementById('password').value;
        var new_password = document.getElementById('new_password').value;
        var new_password_confirm = document.getElementById('new_password_confirm').value;
        
        if (new_password != new_password_confirm) {
            alert(gettext('As senhas não conferem.'));
        } else if (new_password == '') {
            alert(gettext('A nova senha não pode ficar em branco.'));
        } else {
            params = 'password='+password+'&new_password='+new_password;
            pAjaxChangePassword.goAjax(params);
        }
    }

</script>

<fieldset>
<legend><?=_('Alterar Senha');?></legend>
    <br />
    <table class="prumoFormTable">
        <tr>
            <td class="prumoFormLabel"><?=_('Usuário');?>:</td>
            <td class="prumoFormFields"><input id="username" type="text" size="30" value="<?=$prumoGlobal['currentUser'] ?>" disabled="disabled" /></td>
        </tr>
        <tr>
            <td class="prumoFormLabel"><?=_('Senha Atual');?>:</td>
            <td class="prumoFormFields"><input id="password" type="password" size="30" autofocus="autofocus" />*</td>
        </tr>
        <tr>
            <td class="prumoFormLabel"><?=_('Nova Senha');?>:</td>
            <td class="prumoFormFields"><input id="new_password" type="password" size="30" />*</td>
        </tr>
        <tr>
            <td class="prumoFormLabel"><?=_('Repita a nova senha');?>:</td>
            <td class="prumoFormFields"><input id="new_password_confirm" type="password" size="30" />*</td>
        </tr>
        <tr>
            <td class="prumoFormLabel"></td>
            <td class="prumoFormFields"><button class="pButton" onclick="goChangePassword()"><?=_('Alterar Senha');?></button></td>
        </tr>
    </table>
    <br />
    * <?=_('campos de preenchimento obrigatório');?>
</fieldset>

