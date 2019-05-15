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

$schema = $GLOBALS['pConfig']['loginSchema_prumo'];
$xmlFile = $GLOBALS['pConfig']['prumoWebPath'].'/ctrl_routines.php';

$crudRoutines = new PrumoCrud('objName=crudRoutines,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,routine=prumo_routines');
$crudRoutines->setConnection($pConnectionPrumo);
$crudRoutines->addField('name=routine,label='._('Identificação').',pk,notNull,unique');
$crudRoutines->addField('name=enabled,label='._('Ativo').',type=boolean,notNull,default=t');
$crudRoutines->addField('name=audit,label='._('Auditoria').',type=boolean,notNull,default=f');
$crudRoutines->addField('name=type,label='._('Tipo').',notNull,default=t');

$crudRoutinesView = new PrumoCrud('objName=crudRoutinesView,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,menu=prumo_routines,onduplicate=update');
$crudRoutinesView->setConnection($pConnectionPrumo);
$crudRoutinesView->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesView->addField('name=link,fieldId=view_link,label='._('Link').',notNull');
$crudRoutinesView->addField('name=description,fieldId=view_description,label='._('Descrição').',type=text');
$crudRoutinesView->addField('name=menu_parent,fieldId=view_menu_parent,label='._('Menu Pai').',visible=false');
$crudRoutinesView->addField('name=tree,fieldId=view_parent_name,label='._('Menu Pai').',virtual,notNull');
$crudRoutinesView->addField('name=menu_label,fieldId=view_menu_label,label='._('Rótulo').',notNull');
$crudRoutinesView->addField('name=menu_icon,fieldId=view_menu_icon,label='._('Ícone'));
$crudRoutinesView->addParent1x1($crudRoutines, 'type', 'view');

$crudRoutinesRootMenu = new PrumoCrud('objName=crudRoutinesRootMenu,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,menu=prumo_routines,onduplicate=update');
$crudRoutinesRootMenu->setConnection($pConnectionPrumo);
$crudRoutinesRootMenu->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesRootMenu->addField('name=menu_parent,fieldId=root_menu_parent,label='._('Menu Pai').',visible=false');
$crudRoutinesRootMenu->addField('name=tree,fieldId=root_parent_name,label='._('Menu Pai').',virtual');
$crudRoutinesRootMenu->addField('name=menu_label,fieldId=root_menu_label,label='._('Rótulo').',notNull');
$crudRoutinesRootMenu->addField('name=menu_icon,fieldId=root_menu_icon,label='._('Ícone'));
$crudRoutinesRootMenu->addParent1x1($crudRoutines, 'type', 'root_menu');

$crudRoutinesMenuLess = new PrumoCrud('objName=crudRoutinesMenuLess,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,menu=prumo_routines,onduplicate=update');
$crudRoutinesMenuLess->setConnection($pConnectionPrumo);
$crudRoutinesMenuLess->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesMenuLess->addField('name=description,fieldId=view_descriptionml,label='._('Descrição').',type=text');
$crudRoutinesMenuLess->addParent1x1($crudRoutines, 'type', 'menu_less');

$crudRoutines->pCrudList = new PrumoCrudList('objName=pCrudList_crudRoutines,xmlFile='.$xmlFile.',crudName=crudRoutines,schema='.$schema.',tableName=routines,routine=prumo_routines');
$crudRoutines->pCrudList->setConnection($pConnectionPrumo);
$crudRoutines->pCrudList->addField('name=tree,label='._('Menu'));
$crudRoutines->pCrudList->addField('name=routine,label='._('Identificação').',pk');
$crudRoutines->pCrudList->addField('name=type,label='._('Tipo').'');
$crudRoutines->pCrudList->addField('name=enabled,label='._('Ativo').',type=boolean,default=t');

$sqlSchema = $pConnectionPrumo->getSchema();
$sql = <<<SQL
SELECT
    v.tree,
    r.routine,
    r.type,
    r.enabled
FROM {$sqlSchema}routines r
LEFT OUTER JOIN {$sqlSchema}v_menu v ON v.routine=r.routine
SQL;

$crudRoutines->pCrudList->setSqlSearch($sql);

$crudRoutines->autoInit();
