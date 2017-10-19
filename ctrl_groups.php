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

$schema = $GLOBALS['pConfig']['loginSchema_prumo'];
$xmlFile = $GLOBALS['pConfig']['prumoWebPath'].'/ctrl_groups.php';

$crudGroups = new PrumoCrud('objName=crudGroups,xmlFile='.$xmlFile.',schema='.$schema.',tableName=groups,routine=prumo_groups,fastCreate,fastUpdate,fastDelete');
$crudGroups->setConnection($pConnectionPrumo);
$crudGroups->addField('name=groupname,label='._('Nome do grupo').',pk');
$crudGroups->addField('name=enabled,label='._('Ativo').',type=boolean,notNull,default=t');

$crudGroups->autoInit();
