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
pProtect('prumo_changePassword');

require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php';

Header('Content-type: application/xml; charset=UTF-8');

// Verifica se existe usuário logado
if ($prumoGlobal['currentUser'] == '') {
    $xml  = '<err>err</err>'."\n";
    $xml .= '<msg>'._('Sua sessão expirou, faça login novamente').'</msg>';
} else {
    // monta o sql
    $password = $_POST['password'];

    $newPassword = sodium_crypto_pwhash_str(
        $_POST['new_password'],
        SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
        SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
    );
    $schema = $pConnectionPrumo->getSchema();
    
    $sqlConsulta = 'SELECT password FROM '.$schema.'syslogin WHERE username='.pFormatSql($prumoGlobal['currentUser'], 'string');
    $dbPassword = $pConnectionPrumo->sqlquery($sqlConsulta);

    $sqlUdate  = 'UPDATE '.$schema.'syslogin SET "password"='.pFormatSql($newPassword, 'string').' WHERE username='.pFormatSql($prumoGlobal['currentUser'], 'string').';';
    
    // retorna a mensagem em xml
    if ($_POST['new_password'] == '') {
        $xml  = '<err>err</err>'."\n";
        $xml .= '<msg>'._('A nova senha não pode ficar em branco.').'</msg>';
    } else if (sodium_crypto_pwhash_str_verify($dbPassword, $password) === false) {
        $xml  = '<err>err</err>'."\n";
        $xml .= '<msg>'._('A senha atual não confere.').'</msg>';
    } else {
        if ($pConnectionPrumo->sqlquery($sqlUdate) === false) {
            $xml = '<msg>' . _('Erro atualizando a senha.') . '</msg>';
        } else {
            $xml = '<msg>' . _('Senha alterada com sucesso!') . '</msg>';
        }
    }
    sodium_memzero($password);
}

$xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);

echo $xml;
