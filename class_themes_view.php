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

class prumoThemesView {
	public $prumoPath;
	public $prumoWebPath;
	public $action;
	
	function __construct() {
		$this->prumoPath = substr($_SERVER['SCRIPT_FILENAME'],0,strlen($_SERVER['SCRIPT_FILENAME'])-18);
		$this->prumoWebPath = substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['REQUEST_URI'])-18);
		$this->action = isset($_POST['action']) ? $_POST['action'] : '';
	}
	
	/**
	 * Monta uma lista com os temas disponíveis
	 *
	 * @return array: lista com os temas disponíveis
	 */
	public function themes() {
		
		$pointer  = opendir($this->prumoPath.'/themes');
		
		while ($thisFile = readdir($pointer)) {
			if ($thisFile != '.' and $thisFile != '..' and $thisFile != '.svn') {
				$files[] = substr($thisFile,0,strlen($thisFile));
			}
		}
		
		sort($files);
		
		return $files;
	}
	
}

