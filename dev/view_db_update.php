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

require_once dirname(dirname(__DIR__)).'/prumo.php';

pProtect('prumo_devtools');
?>

<fieldset> 
<legend><?=_('Atualização de banco de dados');?></legend>

<div style="padding:5px">
    <?=_('Cole na caixa de texto o código DDL de atualização do banco de dados:');?>
    <div style="text-align:center">
        <textarea id="ddl_code" autofocus wrap="off" style="font: 12px Courier New; height: 350px; width: 99%;" onchange="txPrumoCode_change()"></textarea>
    </div>
    <input type="checkbox" id="uptodate" checked="checked" /> <?=_('Considerar que este script já foi executado na base de dados atual');?><br /><br />
    <button onclick="btWriteScript_click()"><?=_('Gravar script');?></button><br />
</div>

</fieldset>

<script type="text/javascript">
    pAjaxDdl = new prumoAjax('dev/ctrl_db_update.php');
    pAjaxDdl.process = function() {
        if (this.responseText == 'OK') {
            alert(gettext('Atualização de banco de dados gravada com sucesso!'));
            document.getElementById('ddl_code').value = '';
        } else {
            alert(this.responseText);
        }
    }
    
    function btWriteScript_click() {
        if (document.getElementById('uptodate').checked) {
            var upToDate = 't';
        } else {
            var upToDate = 'f';
        }
        pAjaxDdl.goAjax('ddl='+ encodeURIComponent(document.getElementById('ddl_code').value)+'&uptodate='+upToDate);
    }
</script>

