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

/**
 * Adiciona aspas quando $useQuote == true
 *
 * @param $value string: texto inicial
 * @param $useQuote boolean: indica se deve adicionar aspas
 *
 * @return string: texto inicial acrecido de aspas quando $useQuote == true
 */
function pAddQuote($value, $useQuote)
{
    return $useQuote ? "'$value'" : $value;
}

/**
 * Parser para adicionar valores a comandos SQL
 *
 * @param $value string: o valor a ser tratado
 * @param $type string: o tipo de dado [string,text,longtext,integer,numeric,serial,date,time,timestamp,boolean]
 * @param $capsLock boolean: quando true, adiciona um strtoupper em $value
 *
 * @returns string
 */
function pFormatSql($value, $type, $capsLock=false, $useQuote=true)
{
    
    $valueNoInjection = pSqlNoInjection($value, $type);
    
    if ($capsLock) {
        $valueNoInjection = mb_strtoupper($valueNoInjection, 'UTF-8');
    }
    
    switch ($type) {
        
        case "string":
            return $valueNoInjection == '' ? "NULL" : pAddQuote($valueNoInjection, $useQuote);
        break;
        
        case "text":
            return $valueNoInjection == '' ? "NULL" : pAddQuote($valueNoInjection, $useQuote);
        break;
        
        case "longtext":
            return $valueNoInjection == '' ? "NULL" : pAddQuote($valueNoInjection, $useQuote);
        break;
        
        case "integer":
            return $valueNoInjection == '' ? "NULL" : "$valueNoInjection";
        break;
        
        case "numeric":
            
            if ($valueNoInjection == '') {
                return "NULL";
            } else {
                
                $delimiterFound = false;
                $newNum = '';
                
                for ($i = strlen($valueNoInjection)-1; $i >=0 ; $i--) {
                    
                    $char = $valueNoInjection[$i];
                    
                    if ($char == '.' or $char == ',') {
                        
                        if (! $delimiterFound) {
                            $newNum = '.' . $newNum;
                            $delimiterFound = true;
                        }
                    } else {
                        $newNum = $char . $newNum;
                    }
                }
                
                $valueNoInjection = $newNum;
                
                return "$valueNoInjection";
            }
        break;
        
        case "serial":
            return $valueNoInjection == '' ? "NULL" : "$valueNoInjection";
        break;
        
        case "date":
            
            if ($valueNoInjection == '') {
                return "NULL";
            } else {
                
                if (pCheckDate($valueNoInjection, 'dd/mm/aaaa', 1000, 3000)) {
                    list ($dia, $mes, $ano) = preg_split ('/[\/\.-]+/', $valueNoInjection);
                    return pAddQuote("$ano-$mes-$dia", true);
                } else {
                    return "NULL";
                }
            }
        break;
        
        case "time":
            return $valueNoInjection == '' ? "NULL" : pAddQuote($valueNoInjection, true);
        break;
        
        case "timestamp":
            
            if ($valueNoInjection == '') {
                return "NULL";
            } else {
                $ano     = substr($valueNoInjection, 6, 4);
                $mes     = substr($valueNoInjection, 3, 2);
                $dia     = substr($valueNoInjection, 0, 2);
                $hora    = substr($valueNoInjection, 11, 2);
                $minuto  = substr($valueNoInjection, 14, 2);
                $segundo = substr($valueNoInjection, 17, 2);
                if (empty($hora)) $hora = '00';
                if (empty($minuto)) $minuto = '00';
                if (empty($segundo)) $segundo = '00';
                return pAddQuote("$ano-$mes-$dia $hora:$minuto:$segundo", true);
            }
        break;

        case "boolean":
            return ($valueNoInjection == 't') ? pAddQuote('t', $useQuote) : pAddQuote('f', $useQuote);
        break;
    }
}

/**
 * Valida data
 *
 * @param @data string: data a ser validade em formato string
 * @param $dateFormat string: ddmmaaaa ou aaaammdd
 * @param $minYear integer: menor ano possível
 * @param $maxYear integer: maior ano possível
 *
 * @return boolean
 */
function pCheckDate($date, $dateFormat='ddmmaaaa', $minYear=1900, $maxYear=2100)
{
    
    $strDate = pSqlNoInjection($date, 'date')."\n";
    
    $strDate = str_replace('/', '', $strDate);
    $strDate = str_replace('.', '', $strDate);
    
    $dateFormat = str_replace('/', '', strtolower($dateFormat));
    $dateFormat = str_replace('.', '', $dateFormat);
    
    if (strlen($strDate) < 8) {
        return false;
    }
    
    switch ($dateFormat) {
        
        case 'ddmmaaaa':        
            $day = substr($strDate, 0, 2);
            $month = substr($strDate, 2, 2);
            $year = substr($strDate, 4, 4);
        break;
        
        case 'aaammdd':
            $day = substr($strDate, 0, 4);
            $month = substr($strDate, 4, 2);
            $year = substr($strDate, 6, 2);
        break;
        
        default:
            return false;
        break;
    }
    
    if ($year < $minYear) {
        return false;
    }
    
    if ($year > $maxYear) {
        return false;
    }
    
    return checkdate($month, $day, $year);
}

/**
 * checa parâmetros enviados via método GET e interrompe a execussão informando o erro via echo
 *
 * @param $param string: campo que deveria ser enviado via método GET
 * @param $param array: lista de campos que deveriam ser enviado via método GET
 */
function pCheckGET($param)
{
    pParamCheck($param, 'GET');
}

/**
 * checa parâmetros enviados via método POST e interrompe a execussão informando o erro via echo
 *
 * @param $param string: campo que deveria ser enviado via método POST
 * @param $param array: lista de campos que deveriam ser enviado via método POST
 */
function pCheckPOST($param)
{
    pParamCheck($param, 'POST');
}

/**
 * checa parâmetros enviados via método POST e interrompe a execussão informando o erro via echo
 *
 * @param $param string: campo que deveria ser enviado via método POST ou GET
 * @param $param array: lista de campos que deveriam ser enviado via método POST ou GET
 * @param $method string: POST ou GET
 */
function pParamCheck($param, $method='POST')
{
    if (is_array($param)) {
        for ($i = 0; $i < count($param); $i++) {
            pParamCheck($param[$i], $method);
        }
    } else {
        
        if (
            ($method == 'GET' and (!isset($_GET[$param])))
            or
            ($method == 'POST' and (!isset($_POST[$param])))
        ) {
            
            $msg = _('Parâmetro %param% não informado!');
            $msg = str_replace('%param%', '$_'.$method.'[\''.$param.'\']', $msg);
            $msg .= ' ('.$_SERVER["SCRIPT_FILENAME"].')';
            
            echo $msg;
            exit;
        }
    }
}

/**
 * Gera e imprime um código javascript de redirecionamento de URL
 *
 * @param $usr string: url do redirecionamento
 */
function pRedirect($url)
{
    echo '<script type="text/javascript">parent.location = \''.(empty($url) ? '/' : $url).'\'</script>'."\n";
    exit;
}

/**
 * Mostra uma mensagem de erro
 *
 * @param $text string: texto da mensagem
 * @param $stderr string: tipo de saída de erro (html ou js)
 */
function pError($text, $stderr)
{
    if ($stderr == 'html') {
        echo $text."\n";
    } elseif ($stderr == 'js') {
        $tratedText = str_replace('\'','\\\'',$text);
        echo '<script type="text/javascript">' . "\n";
        echo '    alert(\''.$tratedText.'\')' . "\n";
        echo '</script>' . "\n";    
    } else {
        echo _('"stderr" desconhecido para pError');
    }
}

/**
 * Adiciona uma tag um nível abaixo em um XML
 *
 * @param $xml string: xml de entrada
 * @param $parent string: nome da tag
 *
 * @return string: xml tratado
 */
function pXmlAddParent($xml, $parent)
{
    $arrXml = explode("\n", $xml);
    
    $newXml = '<'.$parent.'>'."\n";
    for ($i = 0; $i < count($arrXml); $i++) {
        $newXml .= '    '.$arrXml[$i]."\n";
    }
    $newXml .= '</'.$parent.'>'."\n";
    
    return $newXml;
}

/**
 * Pega o caminho de arquivos de acordo com o tema
 *
 * @param $fileName string: nome do arquivo inicial
 * @param $webPath string: local na web onde o arquivo está
 *
 * @return camindo do arquivo de acordo com o tema
 */
function pGetTheme($fileName, $webPath)
{
    $path = $webPath ? $GLOBALS['pConfig']['prumoWebPath'] : $GLOBALS['pConfig']['prumoPath'];
    $file = $GLOBALS['pConfig']['prumoPath'].'/themes/'.$GLOBALS['pConfig']['theme'].'/'.$fileName;
    
    return file_exists($file) ? $path.'/themes/'.$GLOBALS['pConfig']['theme'].'/'.$fileName : $path.'/themes/default/'.$fileName;
}

/**
 * Formata um erro em XML
 *
 * @param $err string: código do erro
 * @param $msg string: mensagem de erro
 * @param $verbose boolean: quando true imprime o XML gerado
 *
 * @return string: xml do erro
 */
function pXmlError($err, $msg, $verbose=false)
{
    $xml  = '<err>'.$err.'</err>'."\n";
    $xml .= '<msg>'.$msg.'</msg>';
    $xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);
    
    if ($verbose) {
        Header('Content-type: application/xml; charset=UTF-8');
        echo $xml;
    }
    
    return $xml;
}

/**
 * Formata dados de acordo com o tipo, para mostrar ao usuário em HTML
 *
 * @param $type string: tipo de dado
 * @param $value string: valor
 *
 * @return string: dado formatado em html
 */
function htmlFormat($type, $value)
{
    if ($type == 'timestamp' and $value != '') {
        $formatedValue = plainFormat($type, $value);
    } elseif ($type == 'date' and $value != '') {
        $formatedValue = plainFormat($type, $value);
    } elseif ($type == 'time' and $value != '') {
        $formatedValue = plainFormat($type, $value);
    } elseif ($type == 'numeric' and $value != '') {
        $formatedValue = plainFormat($type, $value);
    } elseif ($type == 'integer' and $value != '') {
        $formatedValue = plainFormat($type, $value);
    } elseif ($type == 'boolean' and $value != '') {
        
        if ($value == 't') {
            $formatedValue = '<input type="checkbox" readonly="readonly" disabled="disabled" checked="checked" />';
        } else {
            $formatedValue = '<input type="checkbox" readonly="readonly" disabled="disabled" />';
        }
    } else {
        $formatedValue = str_replace($value, '\\n', '<br />');
    }
    
    if ($formatedValue == '//' or $formatedValue == '//::') {
        $formatedValue = '';
    }
    
    return $formatedValue;
}

/**
 * Formata dados de acordo com o tipo, para mostrar ao usuário em texto plano
 *
 * @param $type string: tipo de dado
 * @param $value string: valor
 *
 * @return string: dado formatado em texto plano
 */
function plainFormat($type, $value)
{
    if ($type == 'timestamp' and $value != '') {
        
        $year = substr($value, 0, 4);
        $month = substr($value, 5, 2);
        $day = substr($value, 8, 2);
        $hour = substr($value, 11, 2);
        $minute = substr($value, 14, 2);
        $second = substr($value, 17, 2);
        $timestamp = substr($value, 17, 2);
        $formatedValue = $day . '/' . $month . '/' . $year . ' ' . $hour . ':' . $minute . ':' . $second;
    } elseif ($type == 'date' and $value != '') {
        
        $year = substr($value, 0, 4);
        $month = substr($value, 5, 2);
        $day = substr($value, 8, 2);
        
        $formatedValue = $day . '/' . $month . '/' . $year;
    } elseif ($type == 'time' and $value != '') {
        
        $time = substr($value, 0, 8);
        
        $formatedValue = $time;
    } elseif ($type == 'numeric' and $value != '') {
        
        $number = str_replace('.', ',', str_replace(',', '', $value));
        
        $formatedValue = $number;
    } else {
        $formatedValue = $value;
    }
    
    return $formatedValue;
}

/**
 * Retorna true ou false informado a rotina e a permissão desejada
 *
 * @param $routine string: nome da rotina
 * @param $permission string: permissões desejadas
 *
 * @return boolean
 */
function pPermitted($routine, $permission='any')
{
    if (empty($routine)) {
        return true;
    }
    
    $arrPermission = getPermission($routine);
    
    if ($permission == 'any') {
        return ($arrPermission['c'] or $arrPermission['r'] or $arrPermission['u'] or $arrPermission['d']);
    } else {
        return $arrPermission[$permission];
    }
}

/**
 * Protege o script de acordo com as permissões de determinada rotina
 *
 * @param $routine string: nome da rotina
 * @param $permission string: permissões desejadas
 */
function pProtect($routine, $permission='any')
{
    if ($GLOBALS['prumoGlobal']['currentUser'] == '') {
        echo _('Sua sessão expirou, faça login novamente!');
        exit;
    }
    
    if (! pPermitted($routine, $permission)) {
        
        pLogAcessDenied($routine, $permission);
        echo _('Acesso Negado');
        exit;
    }
}

/**
 * Gera uma lista de arquivos em determinado diretório recursivamente
 *
 * @param $directory string: caminho do diretório
 * 
 * @return array: lista de arquivos
 */
function pListFiles($directory, $include='', $exclude='', $recursive=true)
{
    $list = scandir($directory, 0);
    $fileList = array();
    
    for ($i = 0; $i < count($list); $i++) {
        if ($list[$i] != '.' and $list[$i] != '..') {
            $current = $directory . DIRECTORY_SEPARATOR . $list[$i];
            
            if (is_file($current) and (empty($include) or preg_match($include, strtolower($current))) and (empty($exclude) or !preg_match($exclude, strtolower($current)))) {    
                $fileList[] = $current;
            }
            
            if (is_dir($current) and $recursive) {
                $fileList = array_merge($fileList, pListFiles($current, $include, $exclude));
            }
        }
    }
    
    return $fileList;
}

/**
 * Converte um endereço do disco local em endereço web
 *
 * @param $location string: local no disco
 * @param $location array: varios locais no disco
 *
 * @return string: endereço do arquivo na web
 * @return array: varios endereços do arquivo na web
 */
function pFileLocal2Web($location)
{
    if (is_array($location)) {
        
        $locationWeb = array();
        for ($i = 0; $i < count($location); $i++) {
            $locationWeb[] = pFileLocal2Web($location[$i]);
        }
    } else {
        $locationWeb = $GLOBALS['pConfig']['appPath'] == dirname($location) ? basename($location) : substr($location, strlen($GLOBALS['pConfig']['appPath'])+1);
    }
    
    return $locationWeb;
}

