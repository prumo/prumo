<?php
require_once('prumo.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection_admin.php');
require_once($GLOBALS['pConfig']['prumoPath'].'/ctrl_connection.php');

pProtect('prumo_devtools');

// verifica se existe a pasta de scripts de atualização do banco da aplicação
$scriptDir = $GLOBALS['pConfig']['appPath'].'/updatedb';

if (!is_writable($GLOBALS['pConfig']['appPath']) and !file_exists($scriptDir)) {
	
	$msg = _('Diretório "%dir%" não possui permissão de escrita, nada feito!');
	echo str_replace('%dir%', $GLOBALS['pConfig']['appPath'], $msg);
	
	exit;
}

if (!file_exists($scriptDir)) {
	
	if (!mkdir($scriptDir)) {
		
		$msg = _('Erro ao criar o diretório "%dir%", nada feito!');
		echo str_replace('%dir%', $GLOBALS['pConfig']['appPath'], $msg);
		
		exit;
	}
}

//escolhe o nome do arquivo
$fileName = $pConnectionPrumo->sqlQuery('SELECT now()');
$fileName = str_replace(':','',$fileName);
$fileName = str_replace('-','',$fileName);
$fileName = str_replace('.','',$fileName);
$fileName = str_replace(' ','',$fileName);
$fileName .= '.php';

$completeFileName = $scriptDir.'/'.$fileName;

$fileContent  = '<?php'."\n";
$fileContent .= '$sql = \'BEGIN;'."\n\n".str_replace("'", "\'", $_POST['ddl'])."\n\nCOMMIT;".'\';';
$fileContent .= "\n";

if (file_put_contents($completeFileName, $fileContent) === false) {
	
	$msg = _('Erro ao gravar o arquivo "%file%"!');
	echo str_replace('%file%', $completeFileName, $msg);
	
	exit;
}

//verifica se o schema existe na base de dados, caso não existe, cria
$schema = $pConnectionPrumo->sqlQuery('SELECT count(*) FROM information_schema.schemata WHERE schema_name='.pFormatSql($GLOBALS['pConfig']['loginSchema_prumo'], 'string').';');
if (!$schema) {
	$pConnectionPrumo->sqlQuery('CREATE SCHEMA '.$GLOBALS['pConfig']['loginSchema_prumo'].';');
}

if ($_POST['uptodate'] == 't') {
	writeAppUpdate($fileName);
}

echo 'OK';

