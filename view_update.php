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

pProtect('prumo_update');

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

