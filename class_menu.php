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
 * PrumoMenu é o menu principal do sistema
 */
class PrumoMenu
{
    use PGetName;
    
    public $ind = '';
    
    /**
     * Gera o código HTML do ícone para determinado nó
     *
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function extractIcon(object $node) : string
    {
        return isset($node->icon) ? '<img src="'.$node->icon.'" alt="icon" /> ' : '';
    }
    
    /**
     * Gera o código HTML do link para determinado nó
     *
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function extractLink(object $node) : string
    {
        return isset($node->link) ? $node->link : '';
    }
    
    /**
     * Gera o código HTML do menu
     *
     * @param $arrParam array: array com os parâmetros do nó
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function drawMenu(array $arrParam, $node) : string
    {
        $icon = $this->extractIcon($node);
        $link = $this->extractLink($node);
        
        $menu  = $this->ind.'<div class="prumoMenu" onclick="'.$this->getObjName().'.onClick(\''.$arrParam['id'].
                                                  '\',\''.$link.'\',event)">'.$icon.$arrParam['label'].'</div>'."\n";
        $menu .= $this->ind.'<div id="'.$this->getObjName().'_'.$arrParam['id'].'">'."\n";
        
        return $menu;
    }
    
    /**
     * Gera o código HTML para menu de nível 1
     *
     * @param $arrParam array: array com os parâmetros do nó
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function drawMenuL1(array $arrParam, $node) : string
    {
        $icon = $this->extractIcon($node);
        $link = $this->extractLink($node);
        
        $menuL1  = $this->ind.'    <div class="prumoMenuL1" onclick="'.$this->getObjName().'.onClick(\''.
                      $arrParam['id'].'\',\''.$link.'\',event)">'.$icon.$arrParam['label'].'</div>'."\n";
        $menuL1 .= $this->ind.'    <div id="'.$this->getObjName().'_'.$arrParam['id'].'">'."\n";
        
        return $menuL1;
    }
    
    /**
     * Gera o código HTML para menu de nível 2
     *
     * @param $arrParam array: array com os parâmetros do nó
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function drawMenuL2(array $arrParam, $node) : string
    {
        $icon = $this->extractIcon($node);
        $link = $this->extractLink($node);
        
        $menuL2  = $this->ind.'        <div class="prumoMenuL2" onclick="'.$this->getObjName().'.onClick(\''.
                                           $arrParam['id'].'\',\''.$link.'\',event)">'.$icon.$arrParam['label'].'</div>'."\n";
        $menuL2 .= $this->ind.'        <div id="'.$this->getObjName().'_'.$arrParam['id'].'">'."\n";
        
        return $menuL2;
    }
    
    /**
     * Gera o código HTML para menu de nível 3
     *
     * @param $arrParam array: array com os parâmetros do nó
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function drawMenuL3(array $arrParam, $node) : string
    {
        $icon = $this->extractIcon($node);
        $link = $this->extractLink($node);
        
        $menuL3  = $this->ind.'            <div class="prumoMenuL3" onclick="'.$this->getObjName().'.onClick(\''.
                    $arrParam['id'].'\',\''.$link.'\',event)">'.$icon.$arrParam['label'].
                                                                                                    '</div>'."\n";
        $menuL3 .= $this->ind.'            <div id="'.$this->getObjName().'_'.$arrParam['id'].'">'."\n";
        
        return $menuL3;
    }
    
    /**
     * Gera o código HTML para menu de nível 4
     *
     * @param $arrParam array: array com os parâmetros do nó
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function drawMenuL4(array $arrParam, $node) : string
    {
        $icon = $this->extractIcon($node);
        $link = $this->extractLink($node);
        
        $menuL4  = $this->ind.'                <div class="prumoMenuL4" onclick="'.$this->getObjName().'.onClick(\''.
                               $arrParam['id'].'\',\''.$link.'\',event)">'.
                                                                                     $icon.$arrParam['label'].'</div>'."\n";
        $menuL4 .= $this->ind.'                <div id="'.$this->getObjName().'_'.$arrParam['id'].'">'."\n";
        
        return $menuL4;
    }
    
    /**
     * Gera o código HTML para menu de nível 5
     *
     * @param $arrParam array: array com os parâmetros do nó
     * @param $node string: indice do nó
     *
     * @return string: código HTML
     */
    private function drawMenuL5(array $arrParam, $node) : string
    {
        $icon = $this->extractIcon($node);
        $link = $this->extractLink($node);
        
        $menuL5  = $this->ind.'                    <div class="prumoMenuL5" onclick="'.$this->getObjName().
                                                    '.onClick(\''.$arrParam['id'].'\',\''.$link.
                                                   '\',event)">'.
                                                                           $icon.$arrParam['label'].'</div>'."\n";
        return $menuL5;
    }
    
    /**
     * Gera o código completo do menu
     *
     * @param $verbose boolean: quando true imprime o código gerado
     *
     * @return string: código gerado
     */
    public function draw(bool $verbose) : string
    {
        global $pConnectionPrumo;
        
        $schema = $pConnectionPrumo->getSchema();
        
        $htmlMenu  = "\n";
        
        // inicializa javascript
        $htmlMenu .= $this->ind.'<script type="text/javascript">'."\n";
        $htmlMenu .= $this->ind.'    '.$this->getObjName().' = new PrumoMenu(\''.$this->getObjName().'\');'."\n";
        $htmlMenu .= $this->ind.'</script>'."\n";
        
        // input menu
        $sqlUsername = pFormatSql($GLOBALS['prumoGlobal']['currentUser'], 'string');
        $sql = <<<SQL
        SELECT
            v.tree,
            v.routine
        FROM {$schema}v_menu v
        JOIN {$schema}routines r ON r.routine=v.routine
        JOIN {$schema}routines_groups rg ON rg.routine=r.routine
        JOIN {$schema}groups g ON g.groupname=rg.groupname
        JOIN {$schema}groups_syslogin gs ON gs.groupname=g.groupname
        JOIN {$schema}syslogin s ON s.username=gs.username
        WHERE r.enabled='t'
        AND g.enabled='t'
        AND s.enabled='t'
        AND (rg.c='t' OR rg.r='t' OR rg.u='t' OR rg.d='t')
        AND s.username=$sqlUsername
        AND NOT v.tree IS NULL
        AND v.type='view'
        GROUP BY v.tree, v.routine
        ORDER BY v.tree;
        SQL;
        $queryMenu = $pConnectionPrumo->sql2Array($sql);
        
        $htmlImputMenu  = '<center>'."\n";
        $htmlImputMenu .= '<br>'."\n";
        $htmlImputMenu .= '<input type="text" id="prumo_input_menu" size="100" list="prumo_list_menu" oninput="'.$this->getObjName().'.onInput()" onkeydown="'.$this->getObjName().'.imputMenuKeyDown(event)" />'."\n";
        $htmlImputMenu .= '<datalist id="prumo_list_menu">'."\n";
        for ($i = 0; $i < count($queryMenu); $i++) {
            $htmlImputMenu .= '    <option value="'.$queryMenu[$i]['tree'].'" routine="'.$queryMenu[$i]['routine'].'"></option>'."\n";
        }
        $htmlImputMenu .= '</datalist>'."\n";
        $htmlImputMenu .= '<br>'."\n";
        $htmlImputMenu .= '<br>'."\n";
        $htmlImputMenu .= '</center>'."\n";
        
        $pWindowsImputMenu = new PrumoWindow('pWindowsImputMenu');
        $pWindowsImputMenu->title = _('Menu principal');
        $htmlMenu .= $pWindowsImputMenu->draw(false, $htmlImputMenu);
        $htmlMenu .= '<button onclick="'.$this->getObjName().'.showInputMenu()" class="pButton">...</button><br><br>'."\n";
        
        $xml = simplexml_load_string($this->dbXml());
        
        $iMenu = 0;
        //menu
        foreach($xml as $value0) {
            $htmlMenu .= $this->ind.'<div class="prumoMenuBox">'."\n";
            
            $menu = array();
            foreach($value0->attributes() as $menuAtt => $menuAttValue) {
                $menu[$menuAtt] = $menuAttValue;
            }
            if (count($value0->attributes()) > 0) {
                $htmlMenu .= $this->drawMenu($menu, $xml->menu[$iMenu]);
            }
            $iMenu++;
            
            //level1
            $iLevel1 = 0;
            foreach($value0 as $level1Key => $level1Value) {
                $level1 = array();
                foreach($level1Value->attributes() as $level1Att => $level1AttValue) {
                    $level1[$level1Att] = $level1AttValue;
                }
                
                if (count($level1Value->attributes()) > 0) {
                    $htmlMenu .= $this->drawMenuL1($level1,$value0->level1[$iLevel1]);
                }
                $iLevel1++;
                
                //level2
                $iLevel2 = 0;
                foreach($level1Value as $level2Key => $level2Value) {
                    $level2 = array();
                    foreach($level2Value->attributes() as $level2Att => $level2AttValue) {
                        $level2[$level2Att] = $level2AttValue;
                    }
                    
                    if (count($level2Value->attributes()) > 0) {
                        $htmlMenu .= $this->drawMenuL2($level2,$level1Value->level2[$iLevel2]);
                    }
                    $iLevel2++;
                    
                    //level3
                    $iLevel3 = 0;
                    foreach($level2Value as $level3Key => $level3Value) {
                        $level3 = array();
                        foreach($level3Value->attributes() as $level3Att => $level3AttValue) {
                            $level3[$level3Att] = $level3AttValue;
                        }
                        
                        if (count($level3Value->attributes()) > 0) {
                            $htmlMenu .= $this->drawMenuL3($level3,$level2Value->level3[$iLevel3]);
                        }
                        $iLevel3++;
                        
                        //level4
                        $iLevel4 = 0;
                        foreach($level3Value as $level4Key => $level4Value) {
                            $level4 = array();
                            foreach($level4Value->attributes() as $level4Att => $level4AttValue) {
                                $level4[$level4Att] = $level4AttValue;
                            }
                            
                            if (count($level4Value->attributes()) > 0) {
                                $htmlMenu .= $this->drawMenuL4($level4,$level3Value->level4[$iLevel4]);
                            }
                            $iLevel4++;
                            
                            //level5
                            $iLevel5 = 0;
                            foreach($level4Value as $level5Key => $level5Value) {
                                $level5 = array();
                                foreach($level5Value->attributes() as $level5Att => $level5AttValue) {
                                    $level5[$level5Att] = $level5AttValue;
                                }
                                
                                if (count($level5Value->attributes()) > 0) {
                                    $htmlMenu .= $this->drawMenuL5($level5,$level4Value->level5[$iLevel5]);
                                }
                                $iLevel5++;
                            }
                            if (count($level4Value->attributes()) > 0) {
                                $htmlMenu .= $this->ind.'                </div>'."\n"; //menuL4
                            }
                        }
                        if (count($level3Value->attributes()) > 0) {
                            $htmlMenu .= $this->ind.'            </div>'."\n"; //menuL3
                        }
                    }
                    if (count($level2Value->attributes()) > 0) {
                        $htmlMenu .= $this->ind.'        </div>'."\n";  //menuL2
                    }
                }
                if (count($level1Value->attributes()) > 0) {
                    $htmlMenu .= $this->ind.'    </div>'."\n"; //menuL1
                }
            }
            $htmlMenu .= $this->ind.'</div>'."\n"; //menu
            $htmlMenu .= $this->ind.'</div>'."\n";
            $htmlMenu .= $this->ind.'<br />'."\n";
        }
        
        if ($verbose) {
            echo $htmlMenu;
        }
        
        return $htmlMenu;
    }
    
    /**
     * Gera codigo de abertura de um node XML
     *
     * @param $start string: inicio do node
     * @param $node string: conteudo
     */
    function openNodeXml(string $start, array $node) : string
    {
        $id = str_replace(' ','_',$node['menu_label']);
        $id = strtolower($id);
        $xmlReturn = $start.' id="'.$id.'" label="'._($node['menu_label']).'">';
        if (! empty($node['routine']) && $node['childs'] == 0) {
            if (isset($node['link'])) {
                $explode = explode(':',$node['link']);
                $protocol = $explode[0];
                if ($protocol == 'http') {
                    $xmlReturn .= '<link>'.$node['link'].'</link>';
                } else {
                    $xmlReturn .= '<link>index.php?page='.$node['routine'].'</link>';
                }
            } else {
                $xmlReturn .= '<link>index.php?page='.$node['routine'].'</link>';
            }
        }
        return $xmlReturn;
    }
    
    /**
     * Gera um XML com os dados do menu pegando do banco de dados
     *
     * @return string: xml com os dados do menu
     *
     * @return string: texto montado
     */
    public function dbXml() : string
    {
        global $pConnectionPrumo;
        
        $schema = $pConnectionPrumo->getSchema();
        
        $sqlUsername = pFormatSql($GLOBALS['prumoGlobal']['currentUser'], 'string');
        $sql = <<<SQL
        SELECT
            r.menu_parent,
            r.menu_label,
            r.routine,
            r.menu_icon,
            r.link,
            (SELECT count(*) FROM {$schema}routines WHERE menu_parent=r.routine) as childs
        FROM {$schema}routines r
        JOIN {$schema}routines_groups rg ON rg.routine=r.routine
        JOIN {$schema}groups g ON g.groupname=rg.groupname
        JOIN {$schema}groups_syslogin gs ON gs.groupname=g.groupname
        JOIN {$schema}syslogin s ON s.username=gs.username
        WHERE r.enabled='t'
        AND g.enabled='t'
        AND s.enabled='t'
        AND (rg.c='t' OR rg.r='t' OR rg.u='t' OR rg.d='t')
        AND s.username=$sqlUsername
        GROUP BY r.menu_parent,r.menu_label,r.routine,r.menu_icon,r.link
        ORDER BY r.menu_label;
        SQL;
        
        $sqlResult = $pConnectionPrumo->sql2Array($sql);
        
        $xml = '<PrumoMenu>';
        
        for ($l1=0; $l1 < count($sqlResult); $l1++) {
            if (empty($sqlResult[$l1]['menu_parent'])) {
                $l1Cod = $sqlResult[$l1]['routine'];
                $xml .= $this->openNodeXml('<menu',$sqlResult[$l1]);
                
                for ($l2=0; $l2 < count($sqlResult); $l2++) {
                    if (! empty($sqlResult[$l2]['menu_parent']) && $sqlResult[$l2]['menu_parent'] == $l1Cod) {
                        $l2Cod = $sqlResult[$l2]['routine'];
                        $xml .= $this->openNodeXml('<level1',$sqlResult[$l2]);
                        
                        for ($l3=0; $l3 < count($sqlResult); $l3++) {
                            if (! empty($sqlResult[$l3]['menu_parent']) && $sqlResult[$l3]['menu_parent'] == $l2Cod) {
                                $l3Cod = $sqlResult[$l3]['routine'];
                                $xml .= $this->openNodeXml('<level2',$sqlResult[$l3]);
                                
                                for ($l4=0; $l4 < count($sqlResult); $l4++) {
                                    if (! empty($sqlResult[$l4]['menu_parent']) && $sqlResult[$l4]['menu_parent'] == $l3Cod) {
                                        $l4Cod = $sqlResult[$l4]['routine'];
                                        $xml .= $this->openNodeXml('<level3',$sqlResult[$l4]);
                                        
                                        for ($l5=0; $l5 < count($sqlResult); $l5++) {
                                            if (! empty($sqlResult[$l5]['menu_parent']) && $sqlResult[$l5]['menu_parent'] == $l4Cod) {
                                                $l5Cod = $sqlResult[$l5]['routine'];
                                                
                                                $xml .= $this->openNodeXml('<level4',$sqlResult[$l5]);
                                                if (! empty($sqlResult[$l5]['menu_icon'])) {
                                                    $xml .= '<icon>'.$sqlResult[$l5]['menu_icon'].'</icon>';
                                                }
                                                $xml .= '</level4>';
                                            }
                                        }
                                        if (! empty($sqlResult[$l4]['menu_icon'])) {
                                            $xml .= '<icon>'.$sqlResult[$l4]['menu_icon'].'</icon>';
                                        }
                                        $xml .= '</level3>';
                                    }
                                }
                                if (! empty($sqlResult[$l3]['menu_icon'])) {
                                    $xml .= '<icon>'.$sqlResult[$l3]['menu_icon'].'</icon>';
                                }
                                $xml .= '</level2>';
                            }
                        }
                        if (! empty($sqlResult[$l2]['menu_icon'])) {
                            $xml .= '<icon>'.$sqlResult[$l2]['menu_icon'].'</icon>';
                        }
                        $xml .= '</level1>';
                    }
                }
                
                if (! empty($sqlResult[$l1]['menu_icon'])) {
                    $xml .= '<icon>'.$sqlResult[$l1]['menu_icon'].'</icon>';
                }
                $xml .= '</menu>';
            }
        }
        
        $xml .= '</PrumoMenu>';
        return $xml;
    }
}
