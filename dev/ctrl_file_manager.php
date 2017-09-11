<?php
require_once('prumo.php');

pProtect('prumo_devtools');

if (isset($_POST['open'])) {
	$fileName = $GLOBALS['pConfig']['appPath'].'/'.$_POST['filename'];
	
	if (file_exists($fileName)) {
		$fileContent = file_get_contents($fileName);
		echo $fileContent;
	}
	else {
		echo _('Arquivo não encontrado "'.$fileName.'".');
	}
}

if (isset($_POST['save'])) {
	$fileName = $GLOBALS['pConfig']['appPath'].'/'.$_POST['filename'];
	
	if (file_exists($fileName) and !is_writable($fileName)) {
		$msg = _('Sem permissão de escrita para o arquivo "%filename%".');
		echo str_replace('%filename%',$_POST['filename'],$msg);
	}
	else if (!is_writable($GLOBALS['pConfig']['appPath'])) {
		echo _('Sem permissão de escrita na pasta da aplicação.');
	}
	else {
		file_put_contents($fileName, $_POST['code']);
		echo 'OK';
	}
}
