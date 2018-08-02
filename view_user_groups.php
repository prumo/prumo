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
require_once __DIR__.'/ctrl_connection_admin.php';

$schema = $pConnectionPrumo->getSchema();

pProtect('prumo_users');


$sql  = 'SELECT'."\n";
$sql .= '    groupname'."\n";
$sql .= 'FROM '.$schema.'groups'."\n";
$sql .= 'WHERE NOT groupname IN ('."\n";
$sql .= '    SELECT'."\n";
$sql .= '        groupname'."\n";
$sql .= '    FROM '.$schema.'groups_syslogin'."\n";
$sql .= '    WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
$sql .= ')'."\n";
$sql .= 'ORDER BY groupname;';
$availableGroup = $pConnectionPrumo->sql2Array($sql);

$sql  = 'SELECT'."\n";
$sql .= '    groupname'."\n";
$sql .= 'FROM '.$schema.'groups_syslogin'."\n";
$sql .= 'WHERE username='.pFormatSql($_POST['username'], 'string')."\n";
$sql .= 'ORDER BY groupname;';
$activeGroup = $pConnectionPrumo->sql2Array($sql);

echo '<table align="center" width="600">'."\n";
echo '    <tr>'."\n";
echo '        <td align="right">'._('Grupos disponíveis').'</td>'."\n";
echo '        <td align="center"><br /></td>'."\n";
echo '        <td align="left">'._('Grupos deste usuário').'</td>'."\n";
echo '    </tr>'."\n";
echo '    <tr>'."\n";
echo '        <td align="right">'."\n";
echo '            <select id="available_group" style="width:200px; height:323px" multiple'.$readonly.'>'."\n";
for ($i = 0; $i < count($availableGroup); $i++) {
    echo '                <option value="'.$availableGroup[$i]['groupname'].'">'.$availableGroup[$i]['groupname'].'</option>'."\n";
}
echo '            </select>'."\n";
echo '        </td>'."\n";
echo '        <td align="center">'."\n";
echo '            <button id="bt_add" onclick="btAdd_Click()" style="width:50px; height:31px"'.$readonly.'> > </button>'."\n";
echo '            <br />'."\n";
echo '            <button id="bt_remove" onclick="btRemove_Click()" style="width:50px; height:31px"'.$readonly.'> < </button>'."\n";
echo '            <br />'."\n";
echo '            <br />'."\n";
echo '            <button id="bt_add_all" onclick="btAddAll_Click()" style="width:50px; height:31px"'.$readonly.'> >> </button>'."\n";
echo '            <br />'."\n";
echo '            <button id="bt_remove_all" onclick="btRemoveAll_Click()" style="width:50px; height:31px"'.$readonly.'> << </button>'."\n";
echo '        </td>'."\n";
echo '        <td align="left">'."\n";
echo '            <select id="active_group" style="width:200px; height:323px" multiple'.$readonly.'>'."\n";
for ($i = 0; $i < count($activeGroup); $i++) {
    echo '                <option value="'.$activeGroup[$i]['groupname'].'">'.$activeGroup[$i]['groupname'].'</option>'."\n";
}
echo '            </select>'."\n";
echo '        </td>'."\n";
echo '    </tr>'."\n";
echo '</table>'."\n";
