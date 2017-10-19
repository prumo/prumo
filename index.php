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

////////////////////// validação das configurações do ambiente //////////////////////
$error = 0;
$txtError = '';

if (! file_exists($GLOBALS['pConfig']['appPath'].'/prumo.php')) {
    $txtError .= '<p><img src=\'images/logo_small.png\' alt=\'logo\' /></p>'."\n";
    $txtError .= '<h1>'._('Pré configuração').'</h1>'."\n";
    
    $txtError .= _('Certifique-se de que Prumo Framework foi descompactado dentro do diretório da sua aplicação.').'<br>';
    $txtError .= _('Por exemplo: se sua aplicação está em /var/www/sistema, o Prumo deverá ficar em /var/www/sistema/prumo.')."\n";
    
    $txtError .= '<p>'._('Feito isso, copie os arquivos index.php e prumo.php da pasta "/var/www/sistema/prumo/example" para a pasta da sua aplicação').'.</p>'."\n";
    $txtError .= '<p>'._('Pressione F5 para recarregar esta página, a tela de login será exibida, o usuário padrão é "admin" e a senha padrão também é "admin"').'.</p>'."\n";
    $txtError .= '<p>'._('Na tela inicial você receberá novas instruções').'.</p>'."\n";
    
    $error++;
} else {
    
    $requirePgsql = false;
    $requireSqlite3 = false;
    
    switch ($GLOBALS['pConfig']['sgdb_prumo']) {
        case 'pgsql':
            $requirePgsql = true;
            break;
        case 'sqlite3':
            $requireSqlite3 = true;
            break;
    }
    
    $msg = _('É necessário ativar a extensão %extension% no PHP');
    
    if ($requirePgsql and !extension_loaded('pgsql')) {
        $txtError .= '    <p>'.str_replace('%extension%', 'php_pgsql', $msg).'.</p>'."\n";
        $error++;
    }
    
    if ($requireSqlite3 and !extension_loaded('sqlite3')) {
        $txtError .= '    <p>'.str_replace('%extension%','php_sqlite3',$msg).', ';
        $txtError .= _('ou utilizar outro servidor de banco de dados PostgreSQL para a base de dados do Framework').'.</p>';
        $txtError .= '    <p>'._('Mais detalhes você encontra nos comentários do arquivo de exemplo prumo.php').'</p>'."\n";
        $error++;
    }
}

if ($error > 0) {
    
    header('Content-type: text/html; charset=utf-8');
    echo '<!DOCTYPE HTML>'."\n";
    echo '<html>'."\n\n";
    echo '<head>'."\n";
    echo '    <title>Prumo Framework para PHP</title>'."\n";
    echo '</head>'."\n";
    echo '<body>'."\n";
    
    echo $txtError;
    
    echo '</body>'."\n";
    echo '</html>'."\n";
    exit;
}

////////////////////// ferramentas de desenvolvimento //////////////////////

$pLogin = new PrumoLogin($GLOBALS['pConfig']['appIdent'], '', '');

if (isset($_GET['action']) and $_GET['action'] == 'logoff') {
    $pLogin->logoff();
    pRedirect($GLOBALS['pConfig']['prumoWebPath']);
} else {
    if ($pLogin->isSession()) {

        pProtect('prumo_devtools');

        if (isset($_GET['phpinfo'])) {
            pProtect('prumo_controlPanel');
            phpinfo();
            exit;
        }
        if (isset($_GET['prumoInfo'])) {
            pProtect('prumo_controlPanel');
            prumoInfo();
            exit;
        }

        require_once $GLOBALS['pConfig']['prumoPath'].'/view_header.php';
        require_once $GLOBALS['pConfig']['prumoPath'].'/view_loading.php';

        ?>

        <div class="prumoContainer">
            <div class="prumoHeader">
                <div class="prumoHeaderLeft"><a href="index.php"><img src="<?=$GLOBALS['pConfig']['prumoWebPath'];?>/themes/default/icons/home.png" alt="home" /> <?=_('Início');?></a></div>
                <div class="prumoHeaderRight"><a href="index.php?action=logoff"><?=_('sair');?></a></div>
                <div class="prumoHeaderCenter"><?=$GLOBALS['pConfig']['appIdent'].' '.$GLOBALS['pConfig']['appName'].' - DEVTOOLS';?></div>
            </div>
            <div class="prumoBody">
    
                <div id="menu">
                    <center>
                    <a href="index.php?page=prumo_submission"><?=_('Instruções Iniciais');?></a> : : 
                    <a href="index.php?page=prumo_code_generator"><?=_('Gerador de código');?></a> : : 
                    <a href="index.php?page=prumo_db_update"><?=_('Atualização de banco de dados');?></a> : : 
                    <a href="index.php?page=prumo_routines"><?=_('Rotinas, permissões e menus');?></a> : : 
                    <a href="index.php?page=prumo_controlPanel"><?=_('Painel de controle');?></a> : : 
                    <a href="index.php?prumoInfo">prumoInfo()</a> : : 
                    <a href="index.php?phpinfo">phpinfo()</a> : : 
                    <a href="<?=$GLOBALS['pConfig']['appWebPath'];?>/index.php"><?=_('Ir para o Sistema');?></a>
                    <br /><br />
                    </center>
                </div>
    
                <div>
                    <?php
                    if (isset($_GET['page']) and $_GET['page'] == 'prumo_code_generator') {
                        include $GLOBALS['pConfig']['prumoPath'].'/dev/view_code_generator.php';
                    } elseif (isset($_GET['page']) and $_GET['page'] == 'prumo_db_update') {
                        include $GLOBALS['pConfig']['prumoPath'].'/dev/view_db_update.php';
                    } elseif (isset($_GET['page'])) {
                        include $GLOBALS['pConfig']['appPath'].'/'.$prumoPage[$_GET['page']];
                    } else {
                    ?>
                        <fieldset>
                        <legend>Ferramenta de desenvolvimento Prumo</legend>
                    
                            <p><?=_('Bem vindo! Você está no área de ferramentas de desenvolvimento Prumo, um espaço onde estão agrupadas algumas ferramentas específicas para o desenvolvedor da aplicação.');?></p>
                    
                            <h2><a href="index.php?page=prumo_submission"><?=_('Instruções Iniciais');?></a></h2>
                            <p><?=_('Se esta é a primeira vez que você acessa esta página, é interessante ler estas instruções iniciais antes de começar.');?></p>
                            
                            <h2><a href="index.php?page=prumo_code_generator"><?=_('Gerador de código');?></a></h2>
                            <p><?=_('O Gerador de código é uma ferramenta prática para construir controllers para objetos prumoCrud, PrumoSearch e PrumoList.');?></p>
                            
                            <h2><a href="index.php?page=prumo_db_update"><?=_('Atualização de Banco de dados');?></a></h2>
                            <p><?=_('Ferramenta para empacotar comandos DDL SQL em arquivos php, dessa forma os comandos poderão fazer parte de um commit na sua ferramenta de controle de versão, e serão executados automaticamente na rotina de atualização após o seu script de atualização definido no painel de controle.');?></p>
                    
                            <h2><a href="index.php?page=prumo_routines"><?=_('Rotinas, Permissões e Menus');?></a></h2>
                            <p><?=_('Rotina é um tipo de entidade que suporta o cadastro de credenciais de acesso por grupo de usuário. Artefatos como menus, formulários, arquivos view e controller podem ser vinculados a rotinas e assim ter acesso controlado de acordo com as permissões cadastradas pelo sysadmin.');?></p>
                    
                            <h2><a href="index.php?page=prumo_controlPanel"><?=_('Painel de Controle');?></a></h2>
                            <p><?=_('No painel de controle você encontra alguns parâmetros e configurações para a sua aplicação. Você pode acessar o painel de controle através do menu sistema ou <a href="index.php?page=prumo_controlPanel">clicando aqui</a>.');?></p>
                    
                            <h2><a href="index.php?phpinfo"><?=_('phpinfo()');?></a></h2>
                            <p><?=_('Um atalho para a função phpinfo() do PHP, que só pode ser acessado se você estiver logado no Prumo como desenvolvedor.');?></p>
                    
                            <h2><a href="index.php?prumoInfo"><?=_('prumoInfo()');?></a></h2>
                            <p><?=_('Semelhante ao phpinfo, o Prumo oferece algumas informações e variáveis de ambiente fornecidas pelo framework.');?></p>
                        
                            <h2><a href="<?=$GLOBALS['pConfig']['appWebPath'];?>/index.php"><?=_('Área de trabalho');?></a></h2>
                            <p><?=_('A Área de trabalho é a primeira tela que é apresentada ao usuário após o login, ela pode facilmente ser personalizada. Basta criar um arquivo desktop.php na raiz da sua aplicação com o conteúdo que você desejar.');?></p>
                        
                        </fieldset>
                        <br />
                        
                        <p><?=_('OBS: Todos os grupos de usuário que tiverem permissão de acesso a rotina "prumo_devtools" tem acesso as ferramentas de desenvolvimento.');?></p>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <div class="clear"></div>
            <br />
            <div class="prumoFooter">    <a href="index.php"><?=_('Início');?></a> : : <a href="index.php?page=prumo_changePassword"><?=_('Alterar Senha');?></a>
             : : <a href="index.php?action=logoff"><?=_('Sair');?></a>
            </div>
        </div>

        <?php
        include $GLOBALS['pConfig']['prumoPath'].'/view_footer.php';
        
    } else {
        require_once $GLOBALS['pConfig']['prumoPath'].'/view_header.php';
        include $GLOBALS['pConfig']['prumoPath'].'/view_login.php';
        require_once $GLOBALS['pConfig']['prumoPath'].'/view_footer.php';
    }
}

