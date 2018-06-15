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

function getObjSearchList()
{
    $list = array();
    $fileList = scandir($GLOBALS['pConfig']['appPath']);
    for ($i = 0; $i < count($fileList); $i++) {
        
        $info = pathinfo($fileList[$i]);
        
        //apenas arquivos .php
        if (isset($info['extension']) && strtolower($info['extension']) == 'php' && $info['basename'] != 'index.php' && $info['basename'] != 'prumo.php') {
            $fileContent = file_get_contents($GLOBALS['pConfig']['appPath'] . '/' . $fileList[$i]);
            
            // verifica se o arquivo inicializa o objeto informado
            if (substr_count($fileContent, '= new PrumoSearch(') > 0) {
                $line = explode("\n", str_replace("\r", "", $fileContent));
                
                for ($j=0; $j < count($line); $j++) {
                    if (substr_count($line[$j], '= new PrumoSearch(') > 0) {
                        $part = explode('= new PrumoSearch(', $line[$j]);
                        $objName = trim($part[0]);
                        $objName = str_replace('$', '', $objName);
                        $list[] = $objName;
                    }
                }
            }
        }
    }
    
    return $list;
}
