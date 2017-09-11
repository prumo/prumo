<?php

/* *********************************************************************
 *
 *  Prumo Framework para PHP é um framework vertical para
 *  desenvolvimento rápido de sistemas de informação web.
 *  Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *  e Odair Rubleski <orubleski@gmail.com>
 *
 *  This file is part of Prumo.
 *
 *  Prumo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3, or (at your option)
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_inc_js.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/view_loading.php');

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
	ajaxLogin = new prumoAjax('<?php echo $GLOBALS['pConfig']['prumoWebPath']; ?>/ctrl_login.php', function() {
		if (this.responseText == 'ok') {
			history.go(0);
		}
		else {
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
include($GLOBALS['pConfig']['prumoPath'].'/view_footer.php');
