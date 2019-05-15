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
    
    public function __construct (PrumoConnection $connection)
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
    public function setConnection (PrumoConnection $connection)
    {
        $this->pConnection = $connection;
    }
    
    /**
     * Verifica pelos lembretes a serem mostrados na data atual e os salva na tabela de lembretes ativos
     *  
     * @param integer $id Para verificar um lembrete específico
     */
    public function verify (int $id = null)
    {
        $whereId = $id != null ? ' AND r.id=' . pFormatSql($id, 'integer') : '';
        
        $sqlActiveUser = pFormatSql($this->activeUser, 'string');
        $sql = <<<SQL
        SELECT
            r.id
        FROM {$this->schema}reminder r
        JOIN generate_series(r.reminder_date, now()::date, (r.repeat_every || ' ' || r.repeat_interval)::interval) as g(datas) ON datas::date=now()::date
        WHERE repeat_every IS NOT NULL AND last_seen!=now()::date$whereId AND username=$sqlActiveUser
        UNION
        SELECT
            r.id
        FROM {$this->schema}reminder r
        WHERE repeat_every IS NULL AND last_seen!=now()::date AND r.reminder_date=now()::date $whereId AND username=$sqlActiveUser;
        SQL;
        
        $reminders = $this->pConnection->sql2Array($sql);
        
        foreach ($reminders as $reminder) {
            
            $sql = 'SELECT count(*) FROM ' . $this->schema . 'active_reminder WHERE id=' . pFormatSql($reminder['id'], 'integer');
            $reminderExists = (bool) (int) $this->pConnection->sqlQuery($sql);
            
            $sqlReminderId = pFormatSql($reminder['id'], 'integer');
            if ($reminderExists) {
                $sql = <<<SQL
                UPDATE {$this->schema}active_reminder
                SET
                    reminder_date=now()::date,
                    show_at=now()
                WHERE id=$sqlReminderId;
                SQL;
            } else {
                $sql = <<<SQL
                INSERT INTO {$this->schema}active_reminder (
                    id,
                    reminder_date,
                    show_at
                )
                VALUES (
                    $sqlReminderId,
                    now()::date
                    now()
                );
                SQL;
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
    public function show (int $id = null, bool $verbose = true) : string
    {
        $whereId = $id != null ? ' AND r.id=' . pFormatSql($id, 'integer') : '';
        
        $sqlActiveUser = pFormatSql($this->activeUser, 'string');
        $sql = <<<SQL
        SELECT 
            r.id,
            r.event,
            r.description
        FROM {$this->schema}active_reminder ar
        JOIN {$this->schema}reminder r ON ar.id=r.id
        WHERE r.username=$sqlActiveUser$whereId
        AND ar.reminder_date=now()::date AND ar.show_at <= now()
        SQL;
        
        $reminders = $this->pConnection->sql2Array($sql);
        
        $html = <<<HTML
        <script type="text/javascript">
            pAjaxReminder = new prumoAjax('prumo/ajax_reminder.php');
            pAjaxReminder.process = function() {
                var response = this.responseText.trim();
                if (isNaN(response)) {
                    alert(response);
                } else {
                    this.pWindow.hide();
                }
            }
        </script>
        
        HTML;
        
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
            $html .= <<<HTML
            <script type="text/javascript">
                pWindow_event{$reminder['id']}.show(1);
                function btDelete_{$reminder['id']}_click() {
                    pAjaxReminder.pWindow = pWindow_event{$reminder['id']};
                    pAjaxReminder.goAjax('action=delete&id={$reminder['id']}');
                }
                function btPutOff_{$reminder['id']}_click() {
                    pAjaxReminder.pWindow = pWindow_event{$reminder['id']};
                    pAjaxReminder.goAjax('action=postpone&id={$reminder['id']}');
                }
            </script>
            HTML;
            
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
    public function postponeActive (int $id, int $hours = 1)
    {
        $sqlHours = pFormatSql($hours, 'integer');
        $sqlId = pFormatSql($id, 'integer');
        
        $sql = <<<SQL
        UPDATE {$this->schema}active_reminder SET show_at=now()+ '$sqlHours hours'
        WHERE id=$sqlId;
        SQL;
        
        $this->pConnection->sqlQuery($sql);
    }
    
    /**
     * Remove um lembrete da lista de lembretes ativos
     * 
     * @param integer $id O ID do lembrete a ser removido
     */
    public function deleteActive (int $id)
    {
        $sql = 'DELETE FROM ' . $this->schema . 'active_reminder WHERE id=' . pFormatSql($id, 'integer');
        
        $this->pConnection->sqlQuery($sql);
    }
}
