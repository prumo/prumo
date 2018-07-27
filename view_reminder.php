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

require_once 'prumo.php';

require_once $GLOBALS['pConfig']['prumoPath'] . '/ctrl_reminder.php';

?>

<fieldset>
<legend><?=_('Lembretes');?></legend>

    <div id="crudReminder_form">
        <br />
        <table class="prumoFormTable">
            <tr>
                <td class="prumoFormLabel"><?=_('Código');?>:</td>
                <td class="prumoFormFields"><input id="id" type="text" size="10" disabled="disabled"/>*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Título');?>:</td>
                <td class="prumoFormFields"><input id="event" type="text" size="30" maxlength="30" />*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Descrição');?>:</td>
                <td class="prumoFormFields"><textarea rows="4" cols="30" id="description"></textarea>*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Lembrar em');?>:</td>
                <td class="prumoFormFields"><input id="reminder_date" type="date" size="10" maxlength="10"/>*</td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Repetir a cada');?>:</td>
                <td class="prumoFormFields">
                    <input id="repeat_every" type="text" size="3"/> &nbsp;
                    <select id="repeat_interval">
                        <option value="days"><?=_('Dias');?></option>
                        <option value="months"><?=_('Mêses');?></option>
                        <option value="years"><?=_('Anos');?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="prumoFormLabel"><?=_('Usuário');?>:</td>
                <td class="prumoFormFields"><input id="username" type="text" size="30" disabled="disabled"/></td>
            </tr>
            <tr>
                <td class="prumoFormLabel"></td>
                <td class="prumoFormFields"><?php $crudReminder->drawControls();?></td>
            </tr>
        </table>
        <br />
        * <?=_('campos de preenchimento obrigatório');?>
    </div>

    <?php 
    $crudReminder->drawCrudList();
    ?>
</fieldset>
