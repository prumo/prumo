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
require_once __DIR__.'/ctrl_connection_admin.php';

if (isset($_POST['date_start'])) {
    if ($_POST['date_start'] != '' && $_POST['date_add'] != '' && $_POST['date_interval'] != '') {
        
        $sqlDateStart = pFormatSql($_POST['date_start'], 'date');
        $sqlDateAdd = pFormatSql($_POST['date_add'], 'integer');
        $sqlDateInterval = in_array($_POST['date_interval'], array('day', 'week', 'month')) ? $_POST['date_interval'] : 'day';
        
        $dayOfWeek = array(
            0 => _('domingo'),
            1 => _('segunda-feira'),
            2 => _('terça-feira'),
            3 => _('quarta-feira'),
            4 => _('quinta-feira'),
            5 => _('sexta-feira'),
            6 => _('sábado'),
        );
        
        $sql =<<<SQL
        SELECT
            ($sqlDateStart::date + interval '$sqlDateAdd $sqlDateInterval')::date as date_result,
            extract(DOW FROM DATE ($sqlDateStart::date + interval '$sqlDateAdd $sqlDateInterval')::date) as day_of_week;
        SQL;
        $query = $pConnectionPrumo->fetchAssoc($sql);
        $query['day_of_week'] = $dayOfWeek[$query['day_of_week']];
        echo json_encode($query);
    } else {
        echo json_encode(array(
            'date_result' => '',
            'day_of_week' => ''
        ));
    } 
} else {
    $today = $pConnectionPrumo->sqlQuery('SELECT now()::date;');
    $day = _('dias');
    $week = _('semanas');
    $month = _('meses');
    echo <<<HTML
    Date: <input id="date_start" type="date" value="$today" onchange="goDateCalculator()" />
     + <input id="date_add" type="number" onchange="goDateCalculator()" /> 
    <select id="date_interval" onchange="goDateCalculator()">
        <option value="day" selected>$day</option>
        <option value="week">$week</option>
        <option value="month">$month</option>
    </select>
     <button class="pButton" onClick="goDateCalculator()">=</button> <input id="date_result" type="date" size="20" readonly="readonly" />
     <span id="day_of_week"></span>
    HTML;
}

