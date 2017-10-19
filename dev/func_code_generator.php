<?php

/* *********************************************************************
 *
 *	Prumo Framework para PHP é um framework vertical para
 *	desenvolvimento rápido de sistemas de informação web.
 *	Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *	e Odair Rubleski <orubleski@gmail.com>
 *
 *	This file is part of Prumo.
 *
 *	Prumo is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 3, or (at your option)
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

function getObjSearchList()
{
	$list = array();
	$fileList = scandir($GLOBALS['pConfig']['appPath']);
	for ($i = 0; $i < count($fileList); $i++) {
		
		$info = pathinfo($fileList[$i]);
		
		//apenas arquivos .php
		if (isset($info['extension']) and strtolower($info['extension']) == 'php' and $info['basename'] != 'index.php' and $info['basename'] != 'prumo.php') {
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
