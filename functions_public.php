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

/**
 * Adiciona aspas quando $useQuote == true
 *
 * @param $value string: texto inicial
 * @param $useQuote boolean: indica se deve adicionar aspas
 *
 * @return string: texto inicial acrecido de aspas quando $useQuote == true
 */
function pAddQuote(string $value, bool $useQuote=true) : string
{
    return $useQuote ? "'$value'" : $value;
}

/**
 * Parser para adicionar valores a comandos SQL
 *
 * @param $value string: o valor a ser tratado
 * @param $type string: o tipo de dado [string,text,longtext,integer,numeric,serial,date,time,timestamp,boolean,phone]
 * @param $capsLock boolean: quando true, adiciona um strtoupper em $value
 * @param $useQuote boolean: quando true, adiciona aspas em $value
 *
 * @returns string
 */
function pFormatSql($value, string $type, bool $capsLock = false, bool $useQuote = true) : string
{
    $valueNoInjection = pSqlNoInjection($value, $type);
    
    if ($capsLock) {
        $valueNoInjection = mb_strtoupper($valueNoInjection, 'UTF-8');
    }
    
    switch ($type) {
        case "integer":
        case "serial":
            $valueNoInjection = $valueNoInjection == '' ? "NULL" : "$valueNoInjection";
            break;
        case "numeric":
        case "money":
            if ($valueNoInjection == '') {
                $valueNoInjection = "NULL";
            } else {
                $delimiterFound = false;
                $newNum = '';
                
                for ($i = strlen($valueNoInjection) - 1; $i >= 0; $i--) {
                    $char = $valueNoInjection[$i];
                    
                    if (in_array($char, array('+', '-'))) {
                        $delimiterFound = false;
                        $newNum = $char . $newNum;
                    } elseif (in_array($char, array('.', ','))) {
                        if (! $delimiterFound) {
                            $newNum = '.' . $newNum;
                            $delimiterFound = true;
                        }
                    } else {
                        $newNum = $char . $newNum;
                    }
                }
                
                $valueNoInjection = "$newNum";
            }
            break;
        case "phone":
            if ($valueNoInjection == '') {
                $valueNoInjection = "NULL";
            } else {
                
                if (strlen($valueNoInjection) <= 20) {
                    
                    $phoneNumber = preg_replace("/[^0-9+]/", "", $valueNoInjection);
                    
                    // remove o codigo do pais
                    if (strlen($phoneNumber) > 12 && substr($phoneNumber, 0, 3) == '+55') {
                        $phoneNumber = substr($phoneNumber, (strlen($phoneNumber)-3) * -1);
                    }
                    
                    // remove zero a esquerda do código DDD
                    if ((strlen($phoneNumber) == 11 || strlen($phoneNumber) == 12) && substr($phoneNumber, 0, 1) == '0') {
                        $phoneNumber = substr($phoneNumber, (strlen($phoneNumber)-1) * -1);
                    }
                    
                    // valida número de telefone
                    if (
                        is_numeric(substr($phoneNumber, 0, 1))
                        && substr($phoneNumber, 0, 1) != '0'
                        && (
                            strlen($phoneNumber) == 11 && substr($phoneNumber, 2, 1) == '9'
                            || strlen($phoneNumber) == 10
                        )
                    ) {
                        $valueNoInjection = $phoneNumber;
                    }
                }
                
                $valueNoInjection = pAddQuote($valueNoInjection, $useQuote);
            }
            break;
        case "date":
            if ($valueNoInjection == '') {
                $valueNoInjection = "NULL";
            } else {
                if (pCheckDate($valueNoInjection)) {
                    $arrDate = pParseDate($valueNoInjection);
                    $valueNoInjection = pAddQuote($arrDate['year'].'-'.$arrDate['month'].'-'.$arrDate['day'], $useQuote);
                } else {
                    $valueNoInjection = "NULL";
                }
            }
            break;
        case "timestamp":
            if ($valueNoInjection == '') {
                $valueNoInjection = "NULL";
            } else {
                while ($valueNoInjection != str_replace('  ', ' ', $valueNoInjection)) {
                    $valueNoInjection = str_replace('  ', ' ', $valueNoInjection);
                }
                
                $part = explode(' ', str_replace('T', ' ', $valueNoInjection));
                
                $arrDate = pParseDate($part[0]);
                
                $fuso = '';
                $partFuso = explode('+', $part[1]);
                if (isset($partFuso[1])) {
                    $fuso = '+'.$partFuso[1];
                } else {
                    $partFuso = explode('-', $part[1]);
                    if (isset($partFuso[1])) {
                        $fuso = '-'.$partFuso[1];
                    }
                }
                
                $arrTime = pParseTime($partFuso[0]);
                
                $year   = $arrDate['year'];
                $month  = $arrDate['month'];
                $day    = $arrDate['day'];
                $hour   = $arrTime['hour'];
                $minute = $arrTime['minute'];
                $second = $arrTime['second'];

                $valueNoInjection = pAddQuote("$year-$month-$day $hour:$minute:$second$fuso", $useQuote);
            }
            break;
        case "boolean":
            $valueNoInjection = ($valueNoInjection == 't') ? pAddQuote('t', $useQuote) : pAddQuote('f', $useQuote);
            break;
        case "string":
        case "text":
        case "longtext":
        case "time":
        default:
            $valueNoInjection = $valueNoInjection == '' ? "NULL" : pAddQuote($valueNoInjection, $useQuote);
            break;
    }

    return $valueNoInjection;
}

/**
 * Valida data
 *
 * @param @data string: data a ser validade em formato string
 *
 * @return boolean
 */
function pCheckDate(string $date) : bool
{
    $arrDate = pParseDate($date);
    if ($arrDate === false) {
        return false;
    } else if (checkdate($arrDate['month'], $arrDate['day'], $arrDate['year'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * checa parâmetros enviados via método GET e interrompe a execussão informando o erro via echo
 *
 * @param $param string: campo que deveria ser enviado via método GET
 * @param $param array: lista de campos que deveriam ser enviado via método GET
 */
function pCheckGET($param) : void
{
    pParamCheck($param, 'GET');
}

/**
 * checa parâmetros enviados via método POST e interrompe a execussão informando o erro via echo
 *
 * @param $param string: campo que deveria ser enviado via método POST
 * @param $param array: lista de campos que deveriam ser enviado via método POST
 */
function pCheckPOST($param) : void
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
function pParamCheck($param, $method='POST') : void
{
    if (is_array($param)) {
        for ($i = 0; $i < count($param); $i++) {
            pParamCheck($param[$i], $method);
        }
    } else {
        
        if (
            ($method == 'GET' && (! isset($_GET[$param])))
            or
            ($method == 'POST' && (! isset($_POST[$param])))
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
function pRedirect(string $url)
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
function pError(string $text, string $stderr)
{
    if ($stderr == 'html') {
        echo $text."\n";
    } else if ($stderr == 'js') {
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
function pXmlAddParent(string $xml, string $parent) : string
{
    return "<$parent>$xml</$parent>";
}

/**
 * Pega o caminho de arquivos de acordo com o tema
 *
 * @param $fileName string nome do arquivo inicial
 * @param $webPath  string local na web onde o arquivo está
 *
 * @return string caminho do arquivo de acordo com o tema
 */
function pGetTheme(string $fileName, string $webPath) : string
{
    $path = $webPath ? $GLOBALS['pConfig']['prumoWebPath'] : __DIR__;
    $file = __DIR__.'/themes/'.$GLOBALS['pConfig']['theme'].'/'.$fileName;
    
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
function pXmlError(string $err, string $msg) : string
{
    $xml = "<err>$err</err><msg>$msg</msg>";
    $xml = pXmlAddParent($xml, $GLOBALS['pConfig']['appIdent']);
    
    return $xml;
}

/**
 * Testa se determinado Xml é uma mensagem de erro
 *
 * @param $xml string: xml
 *
 * @return bool
 */
function testXmlError(string $xml) : bool
{
    $start = strlen($GLOBALS['pConfig']['appIdent']) + 2;
    return (substr(trim($xml), $start, 5) == '<err>');
}

/**
 * Formata dados de acordo com o tipo, para mostrar ao usuário em HTML
 *
 * @param $type string: tipo de dado
 * @param $value string: valor
 *
 * @return string: dado formatado em html
 */
function htmlFormat(string $type, $value)
{
    if ($value == '') {
        return $value;
    }

    if ($type === 'timestamp') {
        $year = substr($value, 0, 4);
        $month = substr($value, 5, 2);
        $day = substr($value, 8, 2);
        $hour = substr($value, 11, 2);
        $minute = substr($value, 14, 2);
        $second = substr($value, 17, 2);
        $formattedValue = $day . '/' . $month . '/' . $year . ' ' . $hour . ':' . $minute . ':' . $second;
    } elseif ($type === 'date') {
        $year = substr($value, 0, 4);
        $month = substr($value, 5, 2);
        $day = substr($value, 8, 2);
        $formattedValue = $day . '/' . $month . '/' . $year;
    } elseif ($type === 'time') {
        $formattedValue = plainFormat($type, $value);
    } elseif ($type === 'numeric') {
        $formattedValue = plainFormat($type, $value);
    } elseif ($type === 'phone') {
        $formattedValue = plainFormat($type, $value);
    } elseif ($type === 'money') {
        $formattedValue = 'R$ '.plainFormat($type, $value);
    } elseif ($type === 'integer') {
        $formattedValue = plainFormat($type, $value);
    } elseif ($type === 'boolean') {
        if ($value == 't') {
            $formattedValue = '<input type="checkbox" readonly="readonly" disabled="disabled" checked="checked" />';
        } else {
            $formattedValue = '<input type="checkbox" readonly="readonly" disabled="disabled" />';
        }
    } elseif ($type === 'cnpj') {
        $value = str_replace(' ', '', trim(strtoupper(preg_replace("/[^0-9]/", "", $value))));
        $formattedValue = substr($value, 0, 2) . '.' . substr($value, 2, 3) . '.'
                        . substr($value, 5, 3) . '/' . substr($value, 8, 4) . '-'
                        . substr($value, 12, 2);
    } elseif ($type === 'cpf') {
        $value = str_replace(' ', '', trim(strtoupper(preg_replace("/[^0-9]/", "", $value))));
        $formattedValue = substr($value, 0, 3) . '.' . substr($value, 3, 3) . '.'
                        . substr($value, 6, 3) . '-' . substr($value, 9, 2);
    } else {
        $formattedValue = str_replace($value, '\\n', '<br />');
    }
    
    if ($formattedValue == '//' || $formattedValue == '//::') {
        $formattedValue = '';
    }
    
    return $formattedValue;
}

/**
 * Formata dados de acordo com o tipo, para mostrar ao usuário em texto plano
 *
 * @param $type string: tipo de dado
 * @param $value string: valor
 *
 * @return string: dado formatado em texto plano
 */
function plainFormat(string $type, $value)
{
    if ($type == 'time' && $value != '') {
        $time = substr($value, 0, 8);
        $formattedValue = $time;
    } else if (($type == 'numeric') && $value != '') {
        $number = str_replace('.', ',', str_replace(',', '', $value));
        $formattedValue = $number;
    } else if (($type == 'phone') && $value != '') {
        $phoneNumber = pFormatSql($value, 'phone', false, false);
        
        if (strlen($phoneNumber) == '10' && substr($phoneNumber, 0, 1) != '0') {
            $ddd = substr($phoneNumber, 0, 2);
            $part1 = substr($phoneNumber, 2, 4);
            $part2 = substr($phoneNumber, 6, 4);
            $formattedValue = "($ddd) $part1-$part2";
        } elseif (strlen($phoneNumber) == '11' && substr($phoneNumber, 0, 1) != '0' && substr($phoneNumber, 2, 1) == '9') {
            $ddd = substr($phoneNumber, 0, 2);
            $part1 = substr($phoneNumber, 2, 5);
            $part2 = substr($phoneNumber, 7, 4);
            $formattedValue = "($ddd) $part1-$part2";
        } else {
            $formattedValue = $value;
        }
    } else if ($type == 'money' && $value != '') {
        $formattedValue = number_format($value, 2, ',','.');
    } else {
        $formattedValue = $value;
    }
    
    return $formattedValue;
}

/**
 * Retorna true ou false informado a rotina e a permissão desejada
 *
 * @param $routine string: nome da rotina
 * @param $permission string: permissões desejadas
 *
 * @return boolean
 */
function pPermitted(string $routine, string $permission='any') : bool
{
    if (empty($routine)) {
        return true;
    }
    
    $arrPermission = getPermission($routine);
    
    if ($permission == 'any') {
        return ($arrPermission['c'] || $arrPermission['r'] || $arrPermission['u'] || $arrPermission['d']);
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
function pProtect(string $routine, string $permission='any')
{
    if (empty($GLOBALS['prumoGlobal']['currentUser'])) {
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
function pListFiles(string $directory, string $include='', string $exclude='', bool $recursive=true) : array
{
    $list = scandir($directory, 0);
    $fileList = array();
    
    for ($i = 0; $i < count($list); $i++) {
        if ($list[$i] != '.' && $list[$i] != '..') {
            $current = $directory . DIRECTORY_SEPARATOR . $list[$i];
            
            if (is_file($current) && ($include == '' || preg_match($include, strtolower($current))) && ($exclude == '' || ! preg_match($exclude, strtolower($current)))) {    
                $fileList[] = $current;
            }
            
            if (is_dir($current) && $recursive) {
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
        $locationWeb = dirname(__DIR__) == dirname($location) ? basename($location) : substr($location, strlen(dirname(__DIR__))+1);
    }
    
    return $locationWeb;
}

