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

$schema = $pConnectionPrumo->getSchema();

pProtect('prumo_users');
pCheckPOST('username');

$readonly = pPermitted('prumo_users', 'u') ? '' : ' readonly="readonly" disabled="disabled"';

///////////////////// grava alterações ///////////////////
if (isset($_POST['action']) and $_POST['action'] == 'write') {
    
    if (pPermitted('prumo_users', 'u')) {
        
        $groupName = isset($_POST['groupname']) ? $_POST['groupname'] : array();
    
        // remove os grupos que não estão na nova lista
        $sql  = 'DELETE FROM '.$schema.'groups_syslogin'."\n";
        $sql .= 'WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
        for ($i = 0; $i < count($groupName); $i++) {
            $sql .= 'AND NOT groupname='.pFormatSql($groupName[$i], 'string')."\n";
        }
        $sql .= ';';
        $pConnectionPrumo->sqlQuery($sql);
    
        // adiciona os novos
        for ($i = 0; $i < count($groupName); $i++) {
            $sql  = 'SELECT'."\n";
            $sql .= '    count(*)'."\n";
            $sql .= 'FROM '.$schema.'groups_syslogin'."\n";
            $sql .= 'WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
            $sql .= 'AND groupname='.pFormatSql($groupName[$i], 'string')."\n";
            if ($pConnectionPrumo->sqlQuery($sql) == 0) {
                $sql  = 'INSERT INTO '.$schema.'groups_syslogin (username, groupname) VALUES ('."\n";
                $sql .= '    '.pFormatSql($_POST['username'], 'string').','."\n";
                $sql .= '    '.pFormatSql($groupName[$i], 'string')."\n";
                $sql .= ');';
                $pConnectionPrumo->sqlQuery($sql);
            }
        }
    } else {
        echo _('Acesso Negado!');
    }
}

include $GLOBALS['pConfig']['prumoPath'].'/view_user_groups.php';

