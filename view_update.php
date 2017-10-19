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

pProtect('prumo_update');

require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php';
?>

<fieldset>
<legend><?=_('Atualização')?></legend>

<?php 
if ($GLOBALS['pConfig']['scriptUpdateApp'] != '') {
    
    echo '<p><b>'._('Atualização da Aplicação').'</b></p>';
    echo '<p>';
    echo '    '.$GLOBALS['pConfig']['scriptUpdateApp'].' ';
    echo '    <button class="pButton" id="btAppUpdate" onclick="btAppUpdate_Click()">'._('Executar').'</button>';
    echo '</p>';
    
    echo '<img id="imgAppUpdate" src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/loading.gif" style="display:none" alt="" />'."\n";
    echo '<div id="div_appUpdate" class="resultaUpdate">'."\n";
    echo '</div>'."\n";
    
    echo '<hr />'."\n";
}

if ($GLOBALS['pConfig']['scriptUpdateFramework'] != '') {
    
    echo '<p><b>'._('Atualização do Prumo Framework').'</b></p>'."\n";
    echo '<p>'."\n";
    echo '    '.$GLOBALS['pConfig']['scriptUpdateFramework'].' '."\n";
    echo '    <button id="btPrumoUpdate" onclick="btPrumoUpdate_Click()">'._('Executar').'</button>'."\n";
    echo '</p>'."\n";
    
    echo '<img id="imgPrumoUpdate" src="'.$GLOBALS['pConfig']['prumoWebPath'].'/images/loading.gif" style="display:none" alt="" />'."\n";
    echo '<div id="div_prumoUpdate" class="resultaUpdate">'."\n";
    echo '</div>'."\n";
}

?>
</fieldset>

<script type="text/javascript">
    var ajaxAppUpdate = new prumoAjax('prumo/ctrl_update.php');
    ajaxAppUpdate.ajaxFormat = 'text';
    ajaxAppUpdate.process = function() {
        document.getElementById('div_appUpdate').innerHTML = '<pre>'+this.responseText+'</pre>';
        document.getElementById('btAppUpdate').removeAttribute('disabled');
        document.getElementById('imgAppUpdate').style.display = 'none';
    }

    function btAppUpdate_Click() {
        document.getElementById('div_appUpdate').innerHTML = '';
        document.getElementById('btAppUpdate').setAttribute('disabled','disabled');
        document.getElementById('imgAppUpdate').style.display = 'block';
        ajaxAppUpdate.goAjax('update=app');
    }

    var ajaxPrumoUpdate = new prumoAjax('prumo/ctrl_update.php');
    ajaxPrumoUpdate.ajaxFormat = 'text';
    ajaxPrumoUpdate.process = function() {
        document.getElementById('div_prumoUpdate').innerHTML = '<pre>'+this.responseText+'</pre>';
        document.getElementById('btPrumoUpdate').removeAttribute('disabled');
        document.getElementById('imgPrumoUpdate').style.display = 'none';
    }

    function btPrumoUpdate_Click() {
        document.getElementById('div_prumoUpdate').innerHTML = '';
        document.getElementById('btPrumoUpdate').setAttribute('disabled','disabled');
        document.getElementById('imgPrumoUpdate').style.display = 'block';
        ajaxPrumoUpdate.goAjax('update=framework');
    }
</script>

