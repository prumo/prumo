<?php

/* *********************************************************************
 *
 *  Prumo Framework para PHP é um framework vertical para
 *  desenvolvimento rápido de sistemas de informação web.
 *  Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *  e Odair Rubleski <orubleski@gmail.com>
 *
 *  This file is part of Prumo.
 *
 *  Prumo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3, or (at your option)
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

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
        } elseif (new_password == '') {
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

