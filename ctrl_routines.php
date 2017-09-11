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

require_once('prumo.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php');

$schema = $GLOBALS['pConfig']['loginSchema_prumo'];
$xmlFile = $GLOBALS['pConfig']['prumoWebPath'].'/ctrl_routines.php';

$crudRoutines = new prumoCrud('objName=crudRoutines,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,routine=prumo_routines');
$crudRoutines->setConnection($pConnectionPrumo);
$crudRoutines->addField('name=routine,label='._('Identificação').',pk,notNull');
$crudRoutines->addField('name=enabled,label='._('Ativo').',type=boolean,notNull,default=t');
$crudRoutines->addField('name=audit,label='._('Auditoria').',type=boolean,notNull,default=f');
$crudRoutines->addField('name=type,label='._('Tipo').',notNull,default=t');

$crudRoutinesView = new prumoCrud('objName=crudRoutinesView,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,menu=prumo_routines,onduplicate=update');
$crudRoutinesView->setConnection($pConnectionPrumo);
$crudRoutinesView->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesView->addField('name=link,fieldId=view_link,label='._('Link').',notNull');
$crudRoutinesView->addField('name=description,fieldId=view_description,label='._('Descrição').',type=text');
$crudRoutinesView->addField('name=menu_parent,fieldId=view_menu_parent,label='._('Menu Pai').',visible=false');
$crudRoutinesView->addField('name=tree,fieldId=view_parent_name,label='._('Menu Pai').',virtual,notNull');
$crudRoutinesView->addField('name=menu_label,fieldId=view_menu_label,label='._('Rótulo').',notNull');
$crudRoutinesView->addField('name=menu_icon,fieldId=view_menu_icon,label='._('Ícone'));
$crudRoutinesView->addParent1x1($crudRoutines, 'type', 'view');

$crudRoutinesRootMenu = new prumoCrud('objName=crudRoutinesRootMenu,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,menu=prumo_routines,onduplicate=update');
$crudRoutinesRootMenu->setConnection($pConnectionPrumo);
$crudRoutinesRootMenu->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesRootMenu->addField('name=menu_parent,fieldId=root_menu_parent,label='._('Menu Pai').',visible=false');
$crudRoutinesRootMenu->addField('name=tree,fieldId=root_parent_name,label='._('Menu Pai').',virtual');
$crudRoutinesRootMenu->addField('name=menu_label,fieldId=root_menu_label,label='._('Rótulo').',notNull');
$crudRoutinesRootMenu->addField('name=menu_icon,fieldId=root_menu_icon,label='._('Ícone'));
$crudRoutinesRootMenu->addParent1x1($crudRoutines, 'type', 'root_menu');

$crudRoutinesMenuLess = new prumoCrud('objName=crudRoutinesMenuLess,xmlFile='.$xmlFile.',schema='.$schema.',tableName=routines,menu=prumo_routines,onduplicate=update');
$crudRoutinesMenuLess->setConnection($pConnectionPrumo);
$crudRoutinesMenuLess->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesMenuLess->addField('name=description,fieldId=view_descriptionml,label='._('Descrição').',type=text');
$crudRoutinesMenuLess->addParent1x1($crudRoutines, 'type', 'menu_less');

$crudRoutines->pCrudList = new prumoCrudList('objName=pCrudList_crudRoutines,xmlFile='.$xmlFile.',crudName=crudRoutines,schema='.$schema.',tableName=routines,routine=prumo_routines');
$crudRoutines->pCrudList->setConnection($pConnectionPrumo);
$crudRoutines->pCrudList->addField('name=tree,label='._('Menu'));
$crudRoutines->pCrudList->addField('name=routine,label='._('Identificação').',pk');
$crudRoutines->pCrudList->addField('name=type,label='._('Tipo').'');
$crudRoutines->pCrudList->addField('name=enabled,label='._('Ativo').',type=boolean,default=t');

$sql  = 'SELECT'."\n";
$sql .= '	v.tree,'."\n";
$sql .= '	r.routine,'."\n";
$sql .= '	r.type,'."\n";
$sql .= '	r.enabled'."\n";
$sql .= 'FROM '.$pConnectionPrumo->getSchema().'routines r'."\n";
$sql .= 'LEFT OUTER JOIN '.$pConnectionPrumo->getSchema().'v_menu v ON v.routine=r.routine'."\n";

$crudRoutines->pCrudList->setSqlSearch($sql);

$crudRoutines->autoInit();
