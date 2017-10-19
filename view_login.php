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

require_once $GLOBALS['pConfig']['prumoPath'].'/view_loading.php';

$urlHttps = 'https://'.$_SERVER["HTTP_HOST"].$GLOBALS['pConfig']['appWebPath'];
$urlHttpsTest = 'https://'.$_SERVER["HTTP_HOST"].$GLOBALS['pConfig']['prumoWebPath'].'/ctrl_login.php';
if ($pConfig['preferHttps'] && $_SERVER["REQUEST_SCHEME"] == 'http' && file_get_contents($urlHttpsTest) !== false) {
    pRedirect($urlHttps);
    exit;
}

// caixas de texto e botão
$defaultUsername = isset($GLOBALS['pConfig']['defaultUsername']) ? $GLOBALS['pConfig']['defaultUsername'] : '';
$defaultPassword = isset($GLOBALS['pConfig']['defaultPassword']) ? $GLOBALS['pConfig']['defaultPassword'] : '';
$inputUsername = '<input id="txtUsername" name="txtUsername" onkeydown="loginKeyDown(event)" type="text" value="'.$defaultUsername.'" size="25" autofocus="autofocus" />';
$inputPassword = '<input id="txtPassword" name="txtPassword" onkeydown="loginKeyDown(event)" type="password" value="'.$defaultPassword.'" size="25" />';
$inputSubmit = '<button class="pButton" onclick="goLogin()">'._('Acessar').'</button>';

// substitui os elementos no template
$html = file_get_contents(pGetTheme('login.php', false));
$html = str_replace(':appIdent:', $GLOBALS['pConfig']['appIdent'], $html);
$html = str_replace(':appName:', $GLOBALS['pConfig']['appName'], $html);
$html = str_replace(':autentication:', _('Autenticação'), $html);
$html = str_replace(':username:', _('Usuário'), $html);
$html = str_replace(':inputUsername:', $inputUsername, $html);
$html = str_replace(':password:', _('Senha'), $html);
$html = str_replace(':inputPassword:', $inputPassword, $html);
$html = str_replace(':submit:', $inputSubmit, $html);

echo $html;
?>

<script type="text/javascript">
    ajaxLogin = new prumoAjax('<?=$GLOBALS['pConfig']['prumoWebPath'];?>/ctrl_login.php', function() {
        if (this.responseText == 'ok') {
            history.go(0);
        } else {
            alert(this.responseText);
            document.getElementById('txtPassword').focus();
        }
    });
    
    function goLogin() {
        params = '&txtUsername='+document.getElementById('txtUsername').value;
        params += '&txtPassword='+md5(document.getElementById('txtPassword').value);
        ajaxLogin.goAjax(params);
    }
    
    function loginKeyDown(event) {
        if (event.keyCode == 13) {
            goLogin();
        }
    }
</script>

<?php
include $GLOBALS['pConfig']['prumoPath'].'/view_footer.php';
