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

require_once dirname(__DIR__).'/prumo.php';
pProtect('prumo_changePassword');

require_once __DIR__.'/class_login.php';
require_once __DIR__.'/ctrl_connection_admin.php';

Header('Content-type: application/xml; charset=UTF-8');

// Verifica se existe usuário logado
if ($prumoGlobal['currentUser'] == '') {
    $xml  = '<err>err</err>'."\n";
    $xml .= '<msg>'._('Sua sessão expirou, faça login novamente').'</msg>';
} else {
    
    $pLogin = new PrumoLogin($GLOBALS['pConfig']['appIdent'], $prumoGlobal['currentUser'], $_POST['password']);
    
    // retorna a mensagem em xml
    if ($_POST['new_password'] == '') {
        $xml  = '<err>err</err>'."\n";
        $xml .= '<msg>'._('A nova senha não pode ficar em branco.').'</msg>';
    } else if ($pLogin->checkPassword() === false) {
        $xml  = '<err>err</err>'."\n";
        $xml .= '<msg>'._('A senha atual não confere.').'</msg>';
    } else {
        if ($pLogin->changePassword($_POST['new_password']) === false) {
            $xml = '<msg>' . _('Erro atualizando a senha.') . '</msg>';
        } else {
            $xml = '<msg>' . _('Senha alterada com sucesso!') . '</msg>';
        }
    }
}

$xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);

echo $xml;
