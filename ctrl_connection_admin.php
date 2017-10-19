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
 * Este arquivo contém o objeto de conexão com o banco de dados.
 */

$pConnectionPrumo = new PrumoConnection('sgdb='.$GLOBALS['pConfig']['sgdb_prumo'].',dbHost='.$GLOBALS['pConfig']['dbHost_prumo']
                                        .',dbPort='.$GLOBALS['pConfig']['dbPort_prumo'].',dbName='.$GLOBALS['pConfig']['dbName_prumo']
                                        .',dbUserName='.$GLOBALS['pConfig']['dbUserName_prumo'].',dbPassword='
                                        .$GLOBALS['pConfig']['dbPassword_prumo']
                                       );

$pConnectionPrumo->setDefaultSchema($GLOBALS['pConfig']['loginSchema_prumo']);
