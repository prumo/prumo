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

echo '    <fieldset>'."\n";
echo '        <legend>'._('Instruções Iniciais').'</legend>'."\n";

echo '        <p>'._('Veja a seguir algumas dicas de como utilizar o Prumo Framework.').'</p>'."\n";

echo '        <h2>'._('Por onde começar?').'</h2>'."\n";
echo '        <p>'.str_replace('%link%', $GLOBALS['pConfig']['appWebPath'].'/index.php?page=prumo_devtools', _('Se você é desenvolvedor, acesse a <a href="%link%">ferramenta de desenvolvimento Prumo</a>, lá você encontra gerador de código, ferramenta de gestão de atualizações de bancos de dados e outras informações úteis para o desenvolvedor. Por padrão o usuário admin participa do grupo "dev" e portanto tem acesso as ferramentas de desenvolvimento. Todos os usuários cadastrados no grupo "dev" terão acesso a estas ferramentas.')).'</p>'."\n";
echo '        <p>'._('Se você é sysadmin, a seguir, uma breve explicação sobre configurações do ambiente, controle de acesso, temas e atualizações.').'</p>'."\n";

echo '        <h2>'._('Painel de Controle').'</h2>'."\n";
echo '        <p>'._('No painel de controle você encontra alguns parâmetros e configurações para a sua aplicação. Você pode acessar o painel de controle através do menu sistema ou <a href="index.php?page=prumo_controlPanel">clicando aqui</a>.').'</p>'."\n";

echo '        <h2>'._('Controle de acesso').'</h2>'."\n";
echo '        <p>'._('As permissões de acesso são definidas por rotina x grupo de usuário, dessa forma, para que um usuário tenha permissão de acesso à determinada rotina, basta que ele participe de um grupo que tenha permissão cadastrada.').'</p>'."\n";
echo '        <p>'._('Um usuário pode participar de vários grupos, portanto suas permissões são a união das permissões de todos os grupos em que ele participa.').'</p>'."\n";

echo '        <h2>'._('Suporte a Temas').'</h2>'."\n";
echo '        <p>'._('Altere a aparência da sua aplicação usando o suporte a temas, disponível no painel de controle. Para instalar novos temas, basta descompactar em "themes" dentro da pasta do framework. Você também pode criar seu tema personalizado, utilize o tema default ou qualquer outro tema como base.').'</p>'."\n";

echo '        <h2>'._('Atualizações automatizáveis').'</h2>'."\n";
echo '        <p>'._('É possível fazer atualização do framework e da sua aplicação de forma automatizada acessando o menu "Atualização".').'</p>'."\n";
echo '        <p>'._('Para tanto, você deve criar um script no sistema operacional do servidor de aplicação, que faça a atualização via controle de versão Subversion ou GIT, ajuste de permissões e o que mais você achar importante.').'</p>'."\n";
echo '        <p>'._('Feito isso, basta cadastrar seu script no painel de controle. O Prumo fará uma chamada de sistema para estes scripts ao clicar no botão "Atualizar", e em seguida rodará automaticamente a atualização de bancos de dados.').'</p>'."\n";

echo '    </fieldset>'."\n";
