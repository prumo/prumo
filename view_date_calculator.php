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

require_once __DIR__.'/../prumo.php';

$pWindowDateCalculator = new prumoWindow();
$pWindowDateCalculator->title = _('Calculadora de datas');
$pWindowDateCalculator->width = '750';
$pWindowDateCalculator->draw(true,'<div id="div_date_calculator" style="padding:20px"></div>');
?>
<script type="text/javascript">
    
    var pAjaxDateCalculator = new prumoAjax('prumo/ctrl_date_calculator.php', function() {
        var objResult = JSON.parse(this.responseText); 
        document.getElementById('date_result').value = objResult.date_result;
        document.getElementById('day_of_week').innerHTML = objResult.day_of_week;
    });
    
    function goDateCalculator()
    {
        var dateStart = document.getElementById('date_start').value;
        var dateAdd = document.getElementById('date_add').value;
        var dateInterval = document.getElementById('date_interval').value;
        
        pAjaxDateCalculator.goAjax('date_start='+dateStart+'&date_add='+dateAdd+'&date_interval='+dateInterval);
    }
    
    pWindowDateCalculator.beforeShow = function()
    {
        if (this.showCount == 0) {
            pSimpleAjax('prumo/ctrl_date_calculator.php', '', 'div_date_calculator');
        }
        return true;
    }
    
</script>

