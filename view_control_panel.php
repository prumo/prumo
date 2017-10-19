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

pProtect('prumo_controlPanel');

function listThemes() {
    
    $prumoThemes = scandir($GLOBALS['pConfig']['prumoPath'].'/themes');
    
    $themes = array();
    for ($i = 0; $i<count($prumoThemes); $i++) {
        $themePath = $GLOBALS['pConfig']['prumoPath'].'/themes/'.$prumoThemes[$i];
        if (is_dir($themePath)) {
            if ($prumoThemes[$i] != '.' and $prumoThemes[$i] != '..' and $prumoThemes[$i] != '.svn') {
                $themes[] = $prumoThemes[$i];
            }
        }
    }
    return $themes;
}

$prumoThemes = listThemes();

$inputTheme  = '<select id="theme">'."\n";

for ($i = 0; $i < count($prumoThemes); $i++) {

    if ($GLOBALS['pConfig']['theme'] == $prumoThemes[$i]) {
        $inputTheme .= '                        <option value="'.$prumoThemes[$i].'" selected>'.$prumoThemes[$i].'</option>'."\n";
    } else {
        $inputTheme .= '                        <option value="'.$prumoThemes[$i].'">'.$prumoThemes[$i].'</option>'."\n";
    }

}
$inputTheme .= '                    </select>'."\n";
?>

<fieldset>
<legend><?=_('Painel de Controle');?></legend>
    <br />
    <div style="display:table; width:100%">
        <div style="display:table-row">
            <div style="display:table-cell; width:50%">
                <table class="prumoFormTable">
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields"><b><?=_('CONFIGURAÇÕES DA APLICAÇÃO');?></b></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">appIdent:</td>
                        <td class="prumoFormFields"><input id="appIdent" type="text" value="<?=$GLOBALS['pConfig']['appIdent'] ?>" size="30" /></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">appName:</td>
                        <td class="prumoFormFields"><input id="appName" type="text" value="<?=$GLOBALS['pConfig']['appName'] ?>" size="30" /></td>
                    </tr>
                    <tr>
                        <td><br /></td>
                        <td><br /></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields"><b><?=_('BANCO DE DADOS DA APLICAÇÃO');?></b></td>
                    </tr>
                    <?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) { ?>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class=""><?=_('Modo "dbSingle" ativado, configurações de conectividade disponível em prumo.php');?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="prumoFormLabel">sgdb:</td>
                        <td class="prumoFormFields">
                            <select name="sgdb" id="sgdb"<?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) echo ' disabled="disabled"' ?>>
                                <option <?php if ($GLOBALS['pConfig']['sgdb'] == 'pgsql') echo 'selected ';?>value="pgsql">pgsql</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">dbHost:</td>
                        <td class="prumoFormFields"><input id="dbHost" type="text" value="<?=$GLOBALS['pConfig']['dbHost'] ?>" size="30" <?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) echo ' disabled="disabled" ' ?>/></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">dbPort:</td>
                        <td class="prumoFormFields"><input id="dbPort" type="text" value="<?=$GLOBALS['pConfig']['dbPort'] ?>" size="30" <?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) echo ' disabled="disabled" ' ?>/></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">dbName:</td>
                        <td class="prumoFormFields"><input id="dbName" type="text" value="<?=$GLOBALS['pConfig']['dbName'] ?>" size="30" <?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) echo ' disabled="disabled" ' ?>/></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">dbUserName:</td>
                        <td class="prumoFormFields"><input id="dbUserName" type="text" value="<?=$GLOBALS['pConfig']['dbUserName'] ?>" size="30" <?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) echo ' disabled="disabled" ' ?>/></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">dbPassword:</td>
                        <td class="prumoFormFields"><input id="dbPassword" type="password" value="<?=$GLOBALS['pConfig']['dbPassword'] ?>" size="30" <?php if (isset($GLOBALS['pConfig']['dbSingle']) and $GLOBALS['pConfig']['dbSingle']) echo ' disabled="disabled" ' ?>/></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">appSchema:</td>
                        <td class="prumoFormFields"><input id="appSchema" type="text" value="<?=$GLOBALS['pConfig']['appSchema'] ?>" size="30" /></td>
                    </tr>
                </table>
                
            </div>
            <div style="display:table-cell">
                <!-- lado direito -->
                <table class="prumoFormTable">
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields"><br /><b><?=_('CONFIGURAÇÕES DO FRAMEWORK');?></b></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">theme:</td>
                        <td class="prumoFormFields">
                            <?=$inputTheme; echo ' <a href="https://github.com/prumo/themes" target="_blank">'._('Mais temas').'...</a>' ?> 
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">searchLines:</td>
                        <td class="prumoFormFields"><input id="searchLines" type="number" value="<?=$GLOBALS['pConfig']['searchLines'] ?>" size="5" /></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logInsert_prumo'] == 't') {
                                echo '        <input id="logInsert_prumo" type="checkbox" checked="checked" /> logInsert_prumo'."\n";
                            } else {
                                echo '        <input id="logInsert_prumo" type="checkbox" /> logInsert_prumo'."\n";
                            }
                            echo '('._('BD do Framework').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logSelect_prumo'] == 't') {
                                echo '        <input id="logSelect_prumo" type="checkbox" checked="checked" /> logSelect_prumo'."\n";
                            } else {
                                echo '        <input id="logSelect_prumo" type="checkbox" /> logSelect_prumo'."\n";
                            }
                            echo '('._('BD do Framework').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logUpdate_prumo'] == 't') {
                                echo '        <input id="logUpdate_prumo" type="checkbox" checked="checked" /> logUpdate_prumo'."\n";
                            } else {
                                echo '        <input id="logUpdate_prumo" type="checkbox" /> logUpdate_prumo'."\n";
                            }
                            echo '('._('BD do Framework').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logDelete_prumo'] == 't') {
                                echo '        <input id="logDelete_prumo" type="checkbox" checked="checked" /> logDelete_prumo'."\n";
                            } else {
                                echo '        <input id="logDelete_prumo" type="checkbox" /> logDelete_prumo'."\n";
                            }
                            echo '('._('BD do Framework').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">-----------------------------------------------------------</td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logInsert'] == 't') {
                                echo '        <input id="logInsert" type="checkbox" checked="checked" /> logInsert'."\n";
                            } else {
                                echo '        <input id="logInsert" type="checkbox" /> logInsert'."\n";
                            }
                            echo '('._('BD da Aplicação').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logSelect'] == 't') {
                                echo '        <input id="logSelect" type="checkbox" checked="checked" /> logSelect'."\n";
                            } else {
                                echo '        <input id="logSelect" type="checkbox" /> logSelect'."\n";
                            }
                            echo '('._('BD da Aplicação').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logUpdate'] == 't') {
                                echo '        <input id="logUpdate" type="checkbox" checked="checked" /> logUpdate'."\n";
                            } else {
                                echo '        <input id="logUpdate" type="checkbox" /> logUpdate'."\n";
                            }
                            echo '('._('BD da Aplicação').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormFields">
                            <?php
                            if ($GLOBALS['pConfig']['logDelete'] == 't') {
                                echo '        <input id="logDelete" type="checkbox" checked="checked" /> logDelete'."\n";
                            } else {
                                echo '        <input id="logDelete" type="checkbox" /> logDelete'."\n";
                            }
                            echo '('._('BD da Aplicação').')';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">scriptUpdateFramework:</td>
                        <td class="prumoFormFields"><input id="scriptUpdateFramework" type="text" value="<?=$GLOBALS['pConfig']['scriptUpdateFramework'] ?>" size="30" /></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel">scriptUpdateApp:</td>
                        <td class="prumoFormFields"><input id="scriptUpdateApp" type="text" value="<?=$GLOBALS['pConfig']['scriptUpdateApp'] ?>" size="30" /></td>
                    </tr>
                    <tr>
                        <td class="prumoFormLabel"><br /></td>
                        <td class="prumoFormLabel"><br /></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div style="text-align:center; width:100%; margin: auto;"><button class="pButton" onclick="writeConfig()">Gravar Configurações</button></div>
    <br />
</fieldset>

<script type="text/javascript">
    
    pAjaxControlPanel = new prumoAjax('<?=$GLOBALS['pConfig']['prumoWebPath'];?>/ctrl_control_panel.php');
    pAjaxControlPanel.process = function() {
        if (this.responseText == 'OK') {
            window.location = 'index.php?page=prumo_controlPanel';
        } else {
            alert(this.responseText);
        }
    }
    
    function writeConfig() {
        var param = '';
        param += 'appIdent='+document.getElementById('appIdent').value;
        param += '&appName='+document.getElementById('appName').value;
        param += '&sgdb='+document.getElementById('sgdb').value;
        param += '&dbHost='+document.getElementById('dbHost').value;
        param += '&dbPort='+document.getElementById('dbPort').value;
        param += '&dbName='+document.getElementById('dbName').value;
        param += '&dbUserName='+document.getElementById('dbUserName').value;
        param += '&dbPassword='+document.getElementById('dbPassword').value;
        param += '&appSchema='+document.getElementById('appSchema').value;
        param += '&theme='+document.getElementById('theme').value;
        param += '&searchLines='+document.getElementById('searchLines').value;
        param += document.getElementById('logInsert').checked == true ? '&logInsert=t' : '&logInsert=f';
        param += document.getElementById('logSelect').checked == true ? '&logSelect=t' : '&logSelect=f';
        param += document.getElementById('logUpdate').checked == true ? '&logUpdate=t' : '&logUpdate=f';
        param += document.getElementById('logDelete').checked == true ? '&logDelete=t' : '&logDelete=f';
        param += document.getElementById('logInsert_prumo').checked == true ? '&logInsert_prumo=t' : '&logInsert_prumo=f';
        param += document.getElementById('logSelect_prumo').checked == true ? '&logSelect_prumo=t' : '&logSelect_prumo=f';
        param += document.getElementById('logUpdate_prumo').checked == true ? '&logUpdate_prumo=t' : '&logUpdate_prumo=f';
        param += document.getElementById('logDelete_prumo').checked == true ? '&logDelete_prumo=t' : '&logDelete_prumo=f';
        param += '&scriptUpdateFramework='+document.getElementById('scriptUpdateFramework').value;
        param += '&scriptUpdateApp='+document.getElementById('scriptUpdateApp').value;
        pAjaxControlPanel.goAjax(param);
    }
    
    function languageChange(objLanguage) {
        document.getElementById('locale').value = objLanguage.value + '.utf8';
    }
    
</script>

