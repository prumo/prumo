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
require_once $GLOBALS['pConfig']['prumoPath'].'/ctrl_connection.php';

pProtect('prumo_update');
pCheckPOST('update');

// Atualiza Aplicação
if ($_POST['update'] == 'app') {
    
    if ($GLOBALS['pConfig']['scriptUpdateApp'] == '') {
        echo _('É necessário configurar o comando de atualização da aplicação no painel de controle');
    } else {
        
        $return = shell_exec($GLOBALS['pConfig']['scriptUpdateApp']);
        
        sleep(1);
        
        // prepara a lista de arquivos na pasta de atualização da aplicação
        $scriptUpdateDir = $GLOBALS['pConfig']['appPath'].'/updatedb/';
        if (is_dir($scriptUpdateDir)) {
            
            $scriptUpdateAll = scandir($scriptUpdateDir);
            $scriptUpdateNew = array();
            
            for ($i = 0; $i < count($scriptUpdateAll); $i++) {
                
                $pathParts = pathinfo($scriptUpdateDir.$scriptUpdateAll[$i]);
                
                if (strtolower($pathParts['extension']) == 'php' and upToDate($scriptUpdateAll[$i], $pConnection,'app') == false) {
                    $scriptUpdateNew[] = $scriptUpdateAll[$i];
                }
            }
            
            sort($scriptUpdateNew);
            
            // Executa os scripts sql novos
            for ($i = 0; $i < count($scriptUpdateNew); $i++) {
                
                $inclusionFile = $scriptUpdateDir.$scriptUpdateNew[$i];
                $sql = '';
                include $inclusionFile;
                
                $pConnection->sqlQuery($sql);
                
                //marca o script como atualizado
                writeAppUpdate($scriptUpdateNew[$i]);
            }
        }
    }
    
    echo $return;
}

// Atualiza Framework
if ($_POST['update'] == 'framework') {
    
    if ($GLOBALS['pConfig']['scriptUpdateFramework'] == '') {
        echo _('É necessário configurar o comando de atualização no painel de controle');
    } else {
        
        $return = shell_exec($GLOBALS['pConfig']['scriptUpdateFramework']);
        
        sleep(1);
        
        // prepara a lista de arquivos na pasta de atualização
        $scriptUpdateDir = $GLOBALS['pConfig']['prumoPath'].'/updatedb/'.$GLOBALS['pConfig']['sgdb_prumo'].'/';
        $scriptUpdateAll = scandir($scriptUpdateDir);
        $scriptUpdateNew = array();
        
        for ($i = 0; $i < count($scriptUpdateAll); $i++) {
            
            $pathParts = pathinfo($scriptUpdateDir.$scriptUpdateAll[$i]);
            
            if (strtolower($pathParts['extension']) == 'php' and upToDate($scriptUpdateAll[$i], $pConnectionPrumo) == false) {
                $scriptUpdateNew[] = $scriptUpdateAll[$i];
            }
        }
        
        sort($scriptUpdateNew);
        
        // Executa os scripts sql novos
        for ($i = 0; $i < count($scriptUpdateNew); $i++) {
            
            $inclusionFile = $scriptUpdateDir.$scriptUpdateNew[$i];
            $sql = '';
            include $inclusionFile;
            
            $pConnectionPrumo->sqlQuery($sql);
            
            //marca o script como atualizado
            $sql  = 'INSERT INTO '.$pConnectionPrumo->getSchema().'update_framework ('."\n";
            $sql .= '    file_name,'."\n";
            $sql .= '    usr_login,'."\n";
            $sql .= '    date_time'."\n";
            $sql .= ')'."\n";
            $sql .= 'VALUES ('."\n";
            $sql .= '    '.pFormatSql($scriptUpdateNew[$i],'string').','."\n";
            $sql .= '    '.pFormatSql($prumoGlobal['currentUser'],'string').','."\n";
            if ($pConnectionPrumo->sgdb() == 'pgsql') {
                $sql .= '    now()'."\n";
            }
            if ($pConnectionPrumo->sgdb() == 'sqlite3') {
                $sql .= '    CURRENT_TIMESTAMP'."\n";
            }
            $sql .= ');'."\n";
            
            $pConnectionPrumo->sqlQuery($sql);
        }
    }
    
    echo $return;
}

