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

$schema   = $GLOBALS['pConfig']['loginSchema_prumo'];
$username = $GLOBALS['prumoGlobal']['currentUser'];
$xmlFile  = $GLOBALS['pConfig']['prumoWebPath'] . '/ctrl_reminder.php';

$crudReminder = new PrumoCrud('objName=crudReminder,xmlFile='.$xmlFile.',schema='.$schema.',tableName=reminder,routine=prumo_reminders');
$crudReminder->setConnection($pConnectionPrumo);
$crudReminder->addField('name=id,label='._('Código').',pk,type=serial');
$crudReminder->addField('name=event,label='._('Título').',type=string,notNull,size=30');
$crudReminder->addField('name=description,label='._('Descrição').',type=string,notNull');
$crudReminder->addField('name=reminder_date,label='._('Lembrar em').',notNull,type=date');
$crudReminder->addField('name=repeat_every,label='._('Repetir a cada').',type=integer');
$crudReminder->addField('name=repeat_interval,type=string');
$crudReminder->addField('name=username,type=string,readonly,default=' . $username);

$crudReminder->pCrudList = new PrumoCrudList('objName=pCrudList_crudReminder,xmlFile='.$xmlFile.',crudName=crudReminder,schema='.$schema.',tableName=reminder,routine=prumo_reminders');
$crudReminder->pCrudList->setConnection($pConnectionPrumo);
$crudReminder->pCrudList->addField('name=event,label='._('Evento'));
$crudReminder->pCrudList->addField('name=reminder_date,label='._('Lembrar em').',type=date');
$crudReminder->pCrudList->addField('name=repeat,label='._('Repetir a cada').'');
$crudReminder->pCrudList->addField('name=id,label='._('Código').',visible=false,pk');

$sqlSchema = $pConnectionPrumo->getSchema($GLOBALS['pConfig']['loginSchema_prumo']);
$sqlUserName = pFormatSql($username, 'string');
$sql = <<<SQL
SELECT
    id,
    event,
    reminder_date,
    repeat_every || ' ' ||
    CASE
        WHEN repeat_interval='days' THEN 'dias'
        WHEN repeat_interval='months' THEN 'mêses'
        WHEN repeat_interval = 'years' THEN 'anos'
        ELSE 'desconhecido'
    END as repeat
FROM {$sqlSchema}reminder
WHERE username=$sqlUserName
SQL;
$crudReminder->pCrudList->setSqlSearch($sql);

$crudReminder->autoInit();
