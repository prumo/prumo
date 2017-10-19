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
pProtect('prumo_routines');

require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_routines.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_routines_groups.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_search_groups.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_search_menus.php';
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_search_menus2.php';

$image = pFileLocal2Web(pListFiles($GLOBALS['pConfig']['appPath'], '(\.(png|jpg|jpeg|gif))', '(\.(svn|git|md))'));
$datalistIcons = '<datalist id="list_icon">'."\n";
for ($i = 0; $i < count($image); $i++) {
    $datalistIcons .= '                  <option value="'.$image[$i].'">'.$image[$i].'</option>'."\n";
}
$datalistIcons .= '                </datalist>'."\n";

?>

<fieldset>
<legend><?=_('Rotinas e Permissões');?></legend>

    <div id="crudRoutines_form">
        <br />
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields"><input id="enabled" type="checkbox" checked="checked" /> <?=_('Ativo');?></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields"><input id="audit" type="checkbox" /> <?=_('Auditoria');?></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Identificação da rotina');?>:</td>
                <td class="prumoFormFields"><input id="routine" type="text" size="45" maxlength="40" autofocus="autofocus" />* (<?=_('Não usar espaços') ?>)</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Tipo');?>:</td>
                <td class="prumoFormFields">
                    <select id="type" onchange="crudRoutines.visibleSon1x1();">
                        <option value="">-</option>
                        <option value="view"><?=_('Menu + Página (view)');?></option>
                        <option value="root_menu"><?=_('Menu Principal (apenas menu)');?></option>
                        <option value="menu_less"><?=_('Rotina sem menu');?></option>
                    </select>*
                </td>
            </tr>
        </table>
        
        <div id="crudRoutinesView_form" style="display:none">
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('Link / View / Arquivo da página');?>:</td>
                    <td class="prumoFormFields"><input id="view_link" type="text" size="45" maxlength="255" />*</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Descrição');?>:</td>
                    <td class="prumoFormFields">
                        <textarea id="view_description" rows="4" cols="44"></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Menu Pai');?>:</td>
                    <td class="prumoFormFields">
                        <input id="view_menu_parent" type="hidden" />
                        <input id="view_parent_name" type="text" size="39" /> <?php $searchMenus->makeButton();?>*</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Rótulo do menu');?>:</td>
                    <td class="prumoFormFields"><input id="view_menu_label" type="text" size="45" maxlength="40" />*</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel">Ícone:</td>
                    <td class="prumoFormFields">
                        <input id="view_menu_icon" list="list_icon" type="text" size="45" maxlength="40" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="crudRoutinesRootMenu_form" style="display:none">
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('Menu Pai');?>:</td>
                    <td class="prumoFormFields">
                        <input id="root_menu_parent" type="hidden" />
                        <input id="root_parent_name" type="text" size="39" /> <?php $searchMenus2->makeButton();?></td>
                </tr>
                <tr>
                    <td class="prumoFormLabel"><?=_('Rótulo do menu');?>:</td>
                    <td class="prumoFormFields"><input id="root_menu_label" type="text" size="45" maxlength="40" />*</td>
                </tr>
                <tr>
                    <td class="prumoFormLabel">Ícone:</td>
                    <td class="prumoFormFields">
                        <input id="root_menu_icon" list="list_icon" type="text" size="45" maxlength="255" />
                        <?=$datalistIcons;?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="crudRoutinesMenuLess_form" style="display:none">
            <table class="prumoFormTable">
                <tr>
                    <td class="prumoFormLabel"><?=_('Descrição');?>:</td>
                    <td class="prumoFormFields">
                        <textarea id="view_descriptionml" rows="4" cols="44"></textarea>
                    </td>
                </tr>
            </table>
        </div>
        
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields"><?php $crudRoutines->drawControls();?></td>
            </tr>
        </table>
        
        <fieldset id="crudRoutinesGroups_container" style="display:none">
        <legend><?=_('Permissões para este grupo de usuários');?></legend>
        
            <div id="crudRoutinesGroups_form" style="display:none">
                <table class="prumoFormTable">
                    <tr>
                        <td class="prumoFormLabel"><?=_('Grupo');?>:</td>
                        <td class="prumoFormFields"><input id="groupname" type="text" size="45" maxlength="30" /><?php $searchGroups->makeButton();?>*</td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"></td>
                        <td class="prumoFormFields"><input id="c" type="checkbox" checked="checked" /> <?=_('Inserir');?></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"></td>
                        <td class="prumoFormFields"><input id="r" type="checkbox" checked="checked" /> <?=_('Visualizar');?></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"></td>
                        <td class="prumoFormFields"><input id="u" type="checkbox" checked="checked" /> <?=_('Alterar');?></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"></td>
                        <td class="prumoFormFields"><input id="d" type="checkbox" checked="checked" /> <?=_('Apagar');?></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"></td>
                        <td class="prumoFormFields"><?php $crudRoutinesGroups->drawControls();?></td>
                    </tr>
                </table>
            </div>
            
            <?php $crudRoutinesGroups->drawCrudList();?>
        </fieldset>
        
        <br />
        * <?=_('campos de preenchimento obrigatório');?>
    </div>

    <?php 
    $crudRoutines->drawCrudList();
    
    $searchGroups->addFieldReturn('groupname','groupname');
    $searchGroups->crudState('crudRoutinesGroups');
    
    $searchMenus->addFieldReturn('routine','view_menu_parent');
    $searchMenus->addFieldReturn('tree','view_parent_name');
    $searchMenus->crudState('crudRoutinesView');
    $searchMenus->setInvisibleFilter('type', 'equal', 'root_menu');
    
    $searchMenus2->addFieldReturn('routine','root_menu_parent');
    $searchMenus2->addFieldReturn('tree','root_parent_name');
    $searchMenus2->crudState('crudRoutinesRootMenu');
    $searchMenus2->setInvisibleFilter('type', 'equal', 'root_menu');
    ?>
</fieldset>
