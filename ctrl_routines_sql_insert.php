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

if (pPermitted('prumo_devtools') && $pConnectionPrumo->sgdb() == 'pgsql') {
    
    $schema = $pConnectionPrumo->getSchema();
    
    $queryRoutine = $pConnectionPrumo->fetchAssoc(
        'SELECT * FROM '.$schema.'routines WHERE routine='.pFormatSql($_POST['routine'], 'string').';'
    );
    
    $sql  = 'INSERT INTO prumo.routines ('."\n";
    $sql .= '    routine,'."\n";
    $sql .= '    link,'."\n";
    $sql .= '    enabled,'."\n";
    $sql .= '    description,'."\n";
    $sql .= '    menu_parent,'."\n";
    $sql .= '    menu_label,'."\n";
    $sql .= '    menu_icon,'."\n";
    $sql .= '    type,'."\n";
    $sql .= '    audit'."\n";
    $sql .= ')'."\n";
    $sql .= '('."\n";
    $sql .= '    SELECT'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['routine'], 'string').' as routine,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['link'], 'string').' as link,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['enabled'], 'boolean').' as enabled,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['description'], 'string').' as description,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['menu_parent'], 'string').' as menu_parent,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['menu_label'], 'string').' as menu_label,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['menu_icon'], 'string').' as menu_icon,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['type'], 'string').' as type,'."\n";
    $sql .= '        '.pFormatSql($queryRoutine['audit'], 'boolean').' as audit'."\n";
    $sql .= '    WHERE NOT EXISTS ('."\n";
    $sql .= '        SELECT routine FROM prumo.routines WHERE routine='.pFormatSql($queryRoutine['routine'], 'string')."\n";
    $sql .= '    )'."\n";
    $sql .= ');';
    
    $queryRoutineGroup = $pConnectionPrumo->sql2Array(
        'SELECT * FROM '.$schema.'routines_groups WHERE routine='.pFormatSql($_POST['routine'], 'string').';'
    );
    
    for ($i=0; $i < count($queryRoutineGroup); $i++) {
        $sql .= "\n";
        $sql .= 'INSERT INTO prumo.routines_groups ('."\n";
        $sql .= '    routine,'."\n";
        $sql .= '    groupname,'."\n";
        $sql .= '    c,'."\n";
        $sql .= '    r,'."\n";
        $sql .= '    u,'."\n";
        $sql .= '    d'."\n";
        $sql .= ')'."\n";
        $sql .= '('."\n";
        $sql .= '    SELECT'."\n";
        $sql .= '       '.pFormatSql($queryRoutineGroup[$i]['routine'], 'string').' as routine,'."\n";
        $sql .= '       '.pFormatSql($queryRoutineGroup[$i]['groupname'], 'string').' as groupname,'."\n";
        $sql .= '       '.pFormatSql($queryRoutineGroup[$i]['c'], 'boolean').' as c,'."\n";
        $sql .= '       '.pFormatSql($queryRoutineGroup[$i]['r'], 'boolean').' as r,'."\n";
        $sql .= '       '.pFormatSql($queryRoutineGroup[$i]['u'], 'boolean').' as u,'."\n";
        $sql .= '       '.pFormatSql($queryRoutineGroup[$i]['d'], 'boolean').' as d'."\n";
        $sql .= '    WHERE NOT EXISTS ('."\n";
        $sql .= '        SELECT routine FROM prumo.routines_groups WHERE routine='.pFormatSql($queryRoutineGroup[$i]['routine'], 'string')."\n";
        $sql .= '    )'."\n";
        $sql .= ');'."\n";
    }
    
    echo str_replace(" ", '&nbsp;', str_replace("\n", "<br>\n", $sql));
}
