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
    $password = md5($_POST['password']);
    $newPassword = md5($_POST['new_password']);
    $schema = $pConnectionPrumo->getSchema();
    
    $sqlConsulta = 'SELECT username FROM '.$schema.'syslogin WHERE username='.pFormatSql($prumoGlobal['currentUser'], 'string').' AND "password"='.pFormatSql($password, 'string').';';
    
    $sqlUdate  = 'UPDATE '.$schema.'syslogin SET "password"='.pFormatSql($newPassword, 'string').' ';
    $sqlUdate .= 'WHERE username='.pFormatSql($prumoGlobal['currentUser'], 'string').';';
    
    // retorna a mensagem em xml
    if ($_POST['new_password'] == '') {
        $xml  = '<err>err</err>'."\n";
        $xml .= '<msg>'._('A nova senha não pode ficar em branco.').'</msg>';
    } elseif ($pConnectionPrumo->sqlquery($sqlConsulta) != $prumoGlobal['currentUser']) {
        $xml  = '<err>err</err>'."\n";
        $xml .= '<msg>'._('A senha atual não confere.').'</msg>';
    } else {
        $pConnectionPrumo->sqlquery($sqlUdate);
        $xml = '<msg>'._('Senha alterada com sucesso!').'</msg>';
    }
}

$xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);

echo $xml;
