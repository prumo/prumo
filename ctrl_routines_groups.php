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
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php';

$schema = $GLOBALS['pConfig']['loginSchema_prumo'];
$xmlFile = $GLOBALS['pConfig']['prumoWebPath'].'/ctrl_routines_groups.php';

$crudRoutinesGroups = new PrumoCrud('objName=crudRoutinesGroups,xmlFile='.$xmlFile.',parent1xN=crudRoutines,schema='.$schema.',tableName=routines_groups,routine=prumo_routines');
$crudRoutinesGroups->setConnection($pConnectionPrumo);
$crudRoutinesGroups->addField('name=routine,label='._('Rotina').',pk,readonly,visible=false');
$crudRoutinesGroups->addField('name=groupname,pk,label='._('Nome do grupo'));
$crudRoutinesGroups->addField('name=c,label='._('Inserir').',type=boolean,notNull');
$crudRoutinesGroups->addField('name=r,label='._('Visualizar').',type=boolean,notNull');
$crudRoutinesGroups->addField('name=u,label='._('Alterar').',type=boolean,notNull');
$crudRoutinesGroups->addField('name=d,label='._('Apagar').',type=boolean,notNull');

$crudRoutinesGroups->autoInit();
