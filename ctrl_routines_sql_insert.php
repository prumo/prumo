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
    
    $sqlRoutine = pFormatSql($queryRoutine['routine'], 'string');
    $sqlLink = pFormatSql($queryRoutine['link'], 'string');
    $sqlEnabled = pFormatSql($queryRoutine['enabled'], 'boolean');
    $sqlDescription = pFormatSql($queryRoutine['description'], 'string');
    $sqlMenuParent = pFormatSql($queryRoutine['menu_parent'], 'string');
    $sqlMenuLabel = pFormatSql($queryRoutine['menu_label'], 'string');
    $sqlMenuIcon = pFormatSql($queryRoutine['menu_icon'], 'string');
    $sqlType = pFormatSql($queryRoutine['type'], 'string');
    $sqlAudit = pFormatSql($queryRoutine['audit'], 'boolean');
    
    $sql = <<<SQL
    INSERT INTO prumo.routines (
        routine,
        link,
        enabled,
        description,
        menu_parent,
        menu_label,
        menu_icon,
        type,
        audit
    )
    (
        SELECT
            $sqlRoutine as routine,
            $sqlLink as link,
            $sqlEnabled as enabled,
            $sqlDescription as description,
            $sqlMenuParent as menu_parent,
            $sqlMenuLabel as menu_label,
            $sqlMenuIcon as menu_icon,
            $sqlType as type,
            $sqlAudit as audit
        WHERE NOT EXISTS (
            SELECT routine FROM prumo.routines WHERE routine=$sqlRoutine
        )
    );
    SQL;
    
    $queryRoutineGroup = $pConnectionPrumo->sql2Array(
        'SELECT * FROM '.$schema.'routines_groups WHERE routine='.pFormatSql($_POST['routine'], 'string').';'
    );
    
    for ($i=0; $i < count($queryRoutineGroup); $i++) {
    
        $sqlRoutine = pFormatSql($queryRoutineGroup[$i]['routine'], 'string');
        $sqlGroupName = pFormatSql($queryRoutineGroup[$i]['groupname'], 'string');
        $sqlC = pFormatSql($queryRoutineGroup[$i]['c'], 'boolean');
        $sqlR = pFormatSql($queryRoutineGroup[$i]['r'], 'boolean');
        $sqlU = pFormatSql($queryRoutineGroup[$i]['u'], 'boolean');
        $sqlD = pFormatSql($queryRoutineGroup[$i]['d'], 'boolean');
        
        $sql = <<<SQL
        INSERT INTO prumo.routines_groups (
            routine,
            groupname,
            c,
            r,
            u,
            d
        )
        (
            SELECT
               $sqlRoutine as routine,
               $sqlGroupName as groupname,
               $sqlC as c,
               $sqlR as r,
               $sqlU as u,
               $sqlD as d
            WHERE NOT EXISTS (
                SELECT routine FROM prumo.routines_groups WHERE routine=$sqlRoutine
            )
        );
        SQL;
    }
    
    echo str_replace(" ", '&nbsp;', str_replace("\n", "<br>\n", $sql));
}
