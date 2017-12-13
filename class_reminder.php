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

class Reminder
{
    protected $pConnection;
    protected $schema;
    protected $activeUser;
    
    public function __construct ($connection)
    {
        $this->setConnection($connection);
        $this->schema = $this->pConnection->getSchema($GLOBALS['pConfig']['loginSchema_prumo']);
        $this->activeUser = $GLOBALS['prumoGlobal']['currentUser'];
    }
    
    /**
     * Define a conexão com o banco de dados
     *
     * @param $connection object: PrumoConnection já instanciado e configurado
     */
    public function setConnection ($connection)
    {
        $this->pConnection = $connection;
    }
    
    /**
     * Verifica pelos lembretes a serem mostrados na data atual e os salva na tabela de lembretes ativos
     *  
     * @param integer $id Para verificar um lembrete específico
     */
    public function verify ($id = null)
    {
        $whereId = $id != null ? ' AND r.id=' . pFormatSql($id, 'integer') : '';
        
        $sql  = 'SELECT' . "\n";
        $sql .= '    r.id' . "\n";
        $sql .= 'FROM ' . $this->schema . 'reminder r' . "\n";
        $sql .= "JOIN generate_series(r.reminder_date, now()::date, (r.repeat_every || ' ' || r.repeat_interval)::interval) as g(datas) ON datas::date=now()::date" . "\n";
        $sql .= 'WHERE repeat_every IS NOT NULL AND last_seen!=now()::date' . $whereId . ' AND username=' . pFormatSql($this->activeUser, 'string') . "\n";
        $sql .= 'UNION' . "\n";
        $sql .= 'SELECT ' . "\n";
        $sql .= '    r.id' . "\n";
        $sql .= 'FROM ' . $this->schema . 'reminder r' . "\n";
        $sql .= 'WHERE repeat_every IS NULL AND last_seen!=now()::date AND r.reminder_date=now()::date ' . $whereId . ' AND username=' . pFormatSql($this->activeUser, 'string') . "\n";
        
        $reminders = $this->pConnection->sql2Array($sql);
        
        foreach ($reminders as $reminder) {
            
            $sql = 'SELECT count(*) FROM ' . $this->schema . 'active_reminder WHERE id=' . pFormatSql($reminder['id'], 'integer');
            $reminderExists = (bool) (int) $this->pConnection->sqlQuery($sql);
            
            if ($reminderExists) {
                $sql  = 'UPDATE ' . $this->schema . 'active_reminder SET ' . "\n";
                $sql .= 'reminder_date=now()::date,' . "\n";
                $sql .= 'show_at=now()' . "\n";
                $sql .= 'WHERE id=' . pFormatSql($reminder['id'], 'integer');
            } else {
                $sql  = 'INSERT INTO ' . $this->schema . 'active_reminder (id, reminder_date, show_at) VALUES (';
                $sql .= '    ' . pFormatSql($reminder['id'], 'integer') . ',' . "\n";
                $sql .= '    now()::date,' . "\n";
                $sql .= '    now()' . "\n";
                $sql .= ')';
            }
            
            $this->pConnection->sqlQuery($sql);
            
            $sql = 'UPDATE ' . $this->schema . 'reminder SET last_seen=now() WHERE id=' . pFormatSql($reminder['id'], 'integer');
            $this->pConnection->sqlQuery($sql);
        }
    }
    
    /**
     * Busca os lembretes ativos para o usuário logado e monta o HTML para mostrar o lembrete 
     * 
     * @param integer $id      Para mostrar um lembrete específico
     * @param boolean $verbose Se true dá um echo no HTML, caso contrário apenas retorna
     *  
     * @return string O HTML gerado
     */
    public function show ($id = null, $verbose = true)
    {
        $whereId = $id != null ? ' AND r.id=' . pFormatSql($id, 'integer') : '';
        
        $sql  = 'SELECT ' . "\n";
        $sql .= '    r.id,' . "\n";
        $sql .= '    r.event,' . "\n";
        $sql .= '    r.description' . "\n";
        $sql .= 'FROM ' . $this->schema . 'active_reminder ar' . "\n";
        $sql .= 'JOIN ' . $this->schema . 'reminder r ON ar.id=r.id' . "\n";
        $sql .= 'WHERE r.username=' . pFormatSql($this->activeUser, 'string') . $whereId . "\n";
        $sql .= 'AND ar.reminder_date=now()::date AND ar.show_at <= now()';
        
        $reminders = $this->pConnection->sql2Array($sql);
        
        $html = '<script type="text/javascript">' . PHP_EOL;
        $html .= "    pAjaxReminder = new prumoAjax('prumo/ajax_reminder.php');". PHP_EOL;
        $html .= '    pAjaxReminder.process = function() {'. PHP_EOL;
        $html .= '        var response = this.responseText.trim();' . PHP_EOL;
        $html .= '        if (isNaN(response)) {' . PHP_EOL;
        $html .= '            alert(response);' . PHP_EOL;
        $html .= '        } else {' . PHP_EOL;
        $html .= "            this.pWindow.hide();". PHP_EOL;
        $html .= '        }' . PHP_EOL;
        $html .= '    }'. PHP_EOL;
        $html .= '</script>' . PHP_EOL;
        
        $i = 0;
        foreach ($reminders as $reminder) {
            $pWindowReminder = new prumoWindow('pWindow_event' . $reminder['id']);
            $pWindowReminder->title = $reminder['event'];
            
            $content  = '<div style="text-align:center; padding: 25px; font-size: 16pt">' . PHP_EOL;
            $content .= '   '. str_replace(PHP_EOL, '<br>', $reminder['description']) . '<br><br>' . PHP_EOL;
            $content .= '   <button class="pButton" onclick="event.preventDefault(); btDelete_'.$reminder['id'].'_click()">'._('Não mostrar novamente').'</button>' . PHP_EOL;
            $content .= '   <button class="pButton" onclick="event.preventDefault(); btPutOff_'.$reminder['id'].'_click()">'._('Mostrar novamente daqui 1 hora').'</button>' . PHP_EOL;
            $content .= '   <button class="pButton" onclick="event.preventDefault(); pWindow_event' . $reminder['id'].'.hide()">'._('Apenas fechar esta tela').'</button>' . PHP_EOL;
            $content .= '   ' . PHP_EOL;
            $content .= '</div>' . PHP_EOL;
            
            $html .= $pWindowReminder->draw(false, $content) . PHP_EOL;
            $html .= '<script type="text/javascript">' . PHP_EOL;
            $html .= '    pWindow_event' . $reminder['id'].'.show(1);' . PHP_EOL;
            $html .= '    function btDelete_'.$reminder['id'].'_click() {' . PHP_EOL;
            $html .= '        pAjaxReminder.pWindow = pWindow_event' . $reminder['id'] . ';' . PHP_EOL;
            $html .= '        pAjaxReminder.goAjax(\'action=delete&id=' . $reminder['id'] . '\');' . PHP_EOL;
            $html .= '    }' . PHP_EOL;
            $html .= '    function btPutOff_'.$reminder['id'].'_click() {' . PHP_EOL;
            $html .= '        pAjaxReminder.pWindow = pWindow_event' . $reminder['id'] . ';' . PHP_EOL;
            $html .= '        pAjaxReminder.goAjax(\'action=postpone&id=' . $reminder['id'] . '\');' . PHP_EOL;
            $html .= '    }' . PHP_EOL;
            $html .= '</script>' . PHP_EOL;
            
            $i++;
        }
        
        if ($verbose === true) {
            echo $html;
        }
        
        return $html;
    }
    
    /**
     * Não mostra o lembrete por X horas
     * 
     * @param integer $id    O ID do lembrete a ser adiado
     * @param integer $hours A quantidade de horas até o lembrete ser mostrado novamente
     */
    public function postponeActive ($id, $hours = 1)
    {
        $sql  = 'UPDATE ' . $this->schema . 'active_reminder SET show_at=now()+ \'' . pFormatSql($hours, 'integer') . ' hours\'' . "\n";
        $sql .= 'WHERE id=' . pFormatSql($id, 'integer');
        
        $this->pConnection->sqlQuery($sql);
    }
    
    /**
     * Remove um lembrete da lista de lembretes ativos
     * 
     * @param integer $id O ID do lembrete a ser removido
     */
    public function deleteActive ($id)
    {
        $sql = 'DELETE FROM ' . $this->schema . 'active_reminder WHERE id=' . pFormatSql($id, 'integer');
        
        $this->pConnection->sqlQuery($sql);
    }
}
