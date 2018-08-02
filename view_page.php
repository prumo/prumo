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

$pHtmlPreLayout = file_get_contents(pGetTheme('layout.php',false));

// barra de cabeçalho
$pHtmlHeaderShortcuts = '<a href="index.php"><img src="'.pGetTheme('icons/home.png',true).'" alt="home" /> '._('Início').'</a>';
$pHtmlPreLayout = str_replace(':shortcuts:', $pHtmlHeaderShortcuts, $pHtmlPreLayout);

$pHtmlHeaderLogoff = '<a href="index.php?action=logoff">'._('sair').'</a>';
$pHtmlPreLayout = str_replace(':logoff:', $pHtmlHeaderLogoff, $pHtmlPreLayout);

$pHtmlPreLayout = str_replace(':appIdent:', $GLOBALS['pConfig']['appIdent'], $pHtmlPreLayout);
$pHtmlPreLayout = str_replace(':appName:', $GLOBALS['pConfig']['appName'], $pHtmlPreLayout);
$pHtmlPreLayout = str_replace(':fullName:', $prumoGlobal['currentFullName'], $pHtmlPreLayout);


// menu
require_once __DIR__.'/ctrl_connection_admin.php';
$pMenu = new PrumoMenu();
$pMenu->ind = '            ';
$pHtmlMenu  = $pMenu->draw(false);
$pHtmlPreLayout = str_replace(':menu:',$pHtmlMenu,$pHtmlPreLayout);

//rodape
$pHtmlFooter  = '    <a href="index.php">'._('Início').'</a>';
$pHtmlFooter .= ' : : <a href="index.php?page=prumo_changePassword">'._('Alterar Senha').'</a>'."\n";
$pHtmlFooter .= ' : : <a href="index.php?action=logoff">'._('Sair').'</a>'."\n";
$pHtmlPreLayout = str_replace(':footer:',$pHtmlFooter,$pHtmlPreLayout);

//inicia a mostrar o conteudo
$pArrHtmlLayout = explode(':desktop:',$pHtmlPreLayout);
echo $pArrHtmlLayout[0];

require_once __DIR__.'/class_reminder.php';
$reminder = new Reminder($pConnectionPrumo);
$reminder->verify();
$reminder->show();

//inclusao do desktop
if (isset($_GET['page']) && $prumoPage[$_GET['page']]) {
    pProtect($_GET['page']);
    $pPage = $prumoPage[$_GET['page']];
} else {
    $pPage = file_exists(dirname(__DIR__).'/desktop.php') ? dirname(__DIR__).'/desktop.php' : __DIR__.'/view_submission.php';
}
include $pPage;

//conteúdo após a inclusao
echo $pArrHtmlLayout[1];
