
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

function classCode() {
	this.state = 'coding';
	this.session = '';
	this.lineIndex = 3;
	this.lastKeyCode = 0;
	this.objCount = 0;
	
	this.init = function() {
		this.btPrumo = document.getElementById('btPrumo');
		this.btPrumoSearch = document.getElementById('btPrumoSearch');
		this.btPrumoList = document.getElementById('btPrumoList');
		
		this.divPrumo = document.getElementById('div_prumo');
		this.divPrumoMaisOpcoes = document.getElementById('div_prumo_mais_opcoes');
		this.divPrumoField = document.getElementById('div_prumo_field');
		this.divPrumoFieldMaisOpcoes = document.getElementById('div_prumo_field_mais_opcoes');
		this.divPrumoSearch = document.getElementById('div_prumo_search');
		this.divPrumoSearchMaisOpcoes = document.getElementById('div_prumo_search_mais_opcoes');
		this.divPrumoSearchField = document.getElementById('div_prumo_search_field');
		
		this.btPrumoMaisOpcoes = document.getElementById('bt_prumo_mais_opcoes');
		this.btPrumoFieldMaisOpcoes = document.getElementById('bt_prumo_field_mais_opcoes');
		this.btPrumoSearchMaisOpcoes = document.getElementById('bt_prumo_search_mais_opcoes');
		
		this.textarea = document.getElementById('prumo_code');
		this.textarea.parent = this;
		this.textarea.ondblclick = function() {
			if (this.parent.state == 'coding') {
				switch (this.parent.session) {
					case 'prumoCrud':
						this.parent.editPrumo();
						break;
					case 'prumoCrudField':
						this.parent.editPrumoField();
						break;
					case 'prumoSearch':
						this.parent.editPrumoSearch();
						break;
					case 'prumoSearchField':
						this.parent.editPrumoSearchField();
						break;
					case 'prumoList':
						this.parent.editPrumoSearch();
						break;
					case 'prumoListField':
						this.parent.editPrumoSearchField();
						break;
				}				
			}
		}
		this.textarea.onclick = function() {
			if (this.parent.state == 'coding') {
				this.parent.navigate();
			}
		}
		this.textarea.onkeyup = function() {
			if (this.parent.state == 'coding') {
				this.parent.navigate();
				if (this.parent.lastKeyCode == 13) {
					var lastSession = 	this.parent.getSessionLine(this.parent.getLineIndexCursor()-1);
					
					if (lastSession == 'prumoCrud' || lastSession == 'prumoCrudField') {
						this.parent.clearPrumoField();
						this.parent.showPrumoFieldEdit();
					}
					if (lastSession == 'prumoSearch' || lastSession == 'prumoSearchField' || lastSession == 'prumoList' || lastSession == 'prumoListField') {
						this.parent.clearPrumoSearchField();
						this.parent.showPrumoSearchFieldEdit();
					}
				}
			}
		}
		this.textarea.onkeydown = function(event) {
			if (this.parent.state == 'coding') {
				this.parent.lastKeyCode = event.keyCode;
				document.getElementById('lastKeyCode').value = this.parent.lastKeyCode;
			}
		}
	}
	
	this.btPrumo_click = function() {
		this.objName = '';
		this.showPrumoNew();
		document.getElementById('prumo_obj_name').focus();
	}
	
	this.btPrumoOk_click = function() {
		var objName = document.getElementById('prumo_obj_name').value;
		if (this.state == 'newPrumo' && objName != '') {
			var arrLine = explode("\n",this.textarea.value);
			arrLine[this.lineIndex] += "\n$" + objName + '->addField(\'name=,notNull\');\n';
			if (this.objCount == 0) {
				arrLine[this.lineIndex] += "\n$" + objName + '->autoInit();';
			}
			
			var newCode = "";
			for (i in arrLine) {
				if (newCode != '') {
					newCode += "\n";
				}
				newCode += arrLine[i];
			}
			
			this.textarea.value = newCode;
		}
		
		this.stateChange('coding');
		this.divPrumo.style.display = 'none';
	}
	
	this.btPrumoFieldOk_click = function() {
		this.stateChange('coding');
		this.divPrumoField.style.display = 'none';
	}
	
	this.btPrumoMaisOpcoes_click = function() {
		this.btPrumoMaisOpcoes.setAttribute('disabled','disabled');
		this.divPrumoMaisOpcoes.style.display = 'block';
	}
	
	this.btPrumoSearchMaisOpcoes_click = function() {
		this.btPrumoSearchMaisOpcoes.setAttribute('disabled','disabled');
		this.divPrumoSearchMaisOpcoes.style.display = 'block';
	}
	
	this.btPrumoAddFieldOk_click = function() {
		this.stateChange('coding');
		this.divPrumoField.style.display = 'none';
	}
	
	this.btPrumoFieldMaisOpcoes_click = function() {
		this.btPrumoFieldMaisOpcoes.setAttribute('disabled','disabled');
		this.divPrumoFieldMaisOpcoes.style.display = 'block';
	}
	
	this.btPrumoSearch_click = function() {
		this.showPrumoSearchNew();
	}
	
	this.btPrumoSearchOk_click = function() {
		var objName = document.getElementById('prumo_search_obj_name').value;
		if ((this.state == 'newPrumoSearch' || this.state == 'newPrumoList') && objName != '') {
			var arrLine = explode("\n",this.textarea.value);
			arrLine[this.lineIndex] += "\n$" + objName + '->addField(\'name=\');\n';
			if (this.objCount == 0) {
				arrLine[this.lineIndex] += "\n$" + objName + '->autoInit();';
			}
			
			var newCode = "";
			for (i in arrLine) {
				if (newCode != '') {
					newCode += "\n";
				}
				newCode += arrLine[i];
			}
			
			this.textarea.value = newCode;
		}
		
		this.stateChange('coding');
		this.divPrumoSearch.style.display = 'none';
	}
	
	this.btPrumoSearchFieldOk_click = function() {
		this.stateChange('coding');
		this.divPrumoSearchField.style.display = 'none';
	}
	
	this.btPrumoList_click = function() {
		this.showPrumoListNew();
	}
	
	this.editPrumo = function() {
		this.clearPrumo();
		this.readPrumoLine(this.getLineCursor());
		this.showPrumoEdit();
	}
	
	this.getObjName = function(textLine) {
		if (textLine != '') {
			this.objName = textLine.substr(0, textLine.indexOf("->addField(")).replace('$','').trim();
			if (this.objName == '') {
				this.objName = textLine.substr(0, textLine.indexOf("->autoInit(")).replace('$','').trim();
			}
			if (this.objName == '') {
				this.objName = textLine.substr(0, textLine.indexOf("= new")).replace('$','').trim();
			}
			document.getElementById('objName').value = this.objName;
			return this.objName;
		}
	}
	
	/**
	 * Lê uma linha de declaração de um objeto prumoCrud e preenche os campos do formulário
	 */
	this.readPrumoLine = function(textLine) {
		var text = textLine.substr(textLine.indexOf("prumoCrud('")+11, textLine.length - textLine.indexOf("prumoCrud('")+11);
		var text = text.replace("');","");
		var param = explode(',',text);
		
		document.getElementById('prumo_obj_name').value = this.objName;
		document.getElementById('prumo_capslock').checked = false;
		document.getElementById('prumo_audit').checked = false;
		document.getElementById('prumo_drawform').checked = false;
		document.getElementById('prumo_fastcreate').checked = false;
		document.getElementById('prumo_fastupdate').checked = false;
		document.getElementById('prumo_fastdelete').checked = false;
		document.getElementById('prumo_autoclick').checked = false;
		document.getElementById('prumo_debug').checked = false;
		
		for (i in param) {
			var paramPart = explode('=',param[i]);
			var paramName = paramPart[0].trim();
			if (paramPart[1] == undefined) {
				paramValue = '';
			}
			else {
				paramValue = paramPart[1].trim();
			}
			
			switch (paramName.toLowerCase()) {
				case 'title':
					document.getElementById('prumo_title').value = paramValue;
				break;
				case 'schema':
					document.getElementById('prumo_schema').value = paramValue;
				break;
				case 'tablename':
					document.getElementById('prumo_tablename').value = paramValue;
				break;
				case 'routine':
					document.getElementById('prumo_routine').value = paramValue;
				break;
				case 'capslock':
					document.getElementById('prumo_capslock').checked = true;
				break;
				
				case 'audit':
					document.getElementById('prumo_audit').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'parent1xn':
					document.getElementById('prumo_parent_type').value = '1xN';
					document.getElementById('prumo_name_parent1xn').value = paramValue;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'pagelines':
					document.getElementById('prumo_pagelines').value = paramValue;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'permission':
					document.getElementById('prumo_permission').value = paramValue;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'includehead':
					document.getElementById('prumo_include_head').value = paramValue;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'includefooter':
					document.getElementById('prumo_include_footer').value = paramValue;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'onduplicate':
					document.getElementById('prumo_onduplicate').value = paramValue;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'list':
					if (paramValue == 'false') {
						document.getElementById('prumo_list').checked = false;
					}
					else {
						document.getElementById('prumo_list').checked = true;
					}
					this.btPrumoMaisOpcoes_click();
				break;
				case 'autolist':
					if (paramValue == 'false') {
						document.getElementById('prumo_auto_list').checked = false;
					}
					else {
						document.getElementById('prumo_auto_list').checked = true;
					}
					this.btPrumoMaisOpcoes_click();
				break;
				case 'drawform':
					document.getElementById('prumo_drawform').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'fastcreate':
					document.getElementById('prumo_fastcreate').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'fastupdate':
					document.getElementById('prumo_fastupdate').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'fastdelete':
					document.getElementById('prumo_fastdelete').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'autoclick':
					document.getElementById('prumo_autoclick').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
				case 'debug':
					document.getElementById('prumo_debug').checked = true;
					this.btPrumoMaisOpcoes_click();
				break;
			}
		}
		
		// trata relacionamento 1x1
		var lineIndex1x1 = this.getLineIndex1x1();
		if (lineIndex1x1 > -1) {
			var text1x1 = this.getLine(lineIndex1x1);
			var text = text1x1.substr(text1x1.indexOf("->addParent1x1(")+15, textLine.length - textLine.indexOf("->addParent1x1(")+15);
			text1x1 = text;
			text = text1x1.replace(");","");
			var param = explode(',',text);
			
			document.getElementById('prumo_parent_type').value = '1x1';
			this.btPrumoMaisOpcoes_click();
			this.parentTypeChange('1x1');
			
			document.getElementById('prumo_name_parent1x1').value = replaceAll(param[0], "$","");
			document.getElementById('prumo_parent_field_condition').value = replaceAll(param[1], "'","");
			document.getElementById('prumo_condition_value').value = replaceAll(param[2], "'","");
		}
	}
	
	this.editPrumoField = function() {
		this.clearPrumoField();
		this.readPrumoFieldLine(this.getLineCursor());
		this.showPrumoFieldEdit();
	}
	
	/**
	 * Lê uma linha de adicão de campo de um objeto prumoCrud e preenche os campos do formulário
	 */
	this.readPrumoFieldLine = function(textLine) {
		var text = textLine.substr(textLine.indexOf("->addField('")+12, textLine.length - textLine.indexOf("->addField('")+12);
		var text = text.replace("');","");
		
		var param = explode(',',text);
		
		document.getElementById('prumo_field_type').value = 'string';
		document.getElementById('prumo_field_notnull').checked = false;
		document.getElementById('prumo_field_readonly').checked = false;
		document.getElementById('prumo_field_capslock').checked = false;
		document.getElementById('prumo_field_audit').checked = false;
		document.getElementById('prumo_field_virtual').checked = false;
		document.getElementById('prumo_field_nocreate').checked = false;
		document.getElementById('prumo_field_noupdate').checked = false;
		document.getElementById('prumo_field_unique').checked = false;
		document.getElementById('prumo_field_nohtml').checked = false;
		
		for (i in param) {
			var paramPart = explode('=',param[i]);
			var paramName = paramPart[0].trim();
			var paramValue = '';
			if (paramPart[1] == undefined) {
				paramValue = '';
			}
			else {
				for (i=1; i < paramPart.length; i++) {
					if (i > 1) {
						paramValue += '=';
					}
					paramValue += paramPart[i].trim();
				}
			}
						
			switch (paramName.toLowerCase()) {
				case 'pk':
					document.getElementById('prumo_field_pk').checked = true;
				break;
				case 'name':
					document.getElementById('prumo_field_name').value = paramValue;
				break;
				case 'label':
					document.getElementById('prumo_field_label').value = paramValue;
				break;
				case 'type':
					document.getElementById('prumo_field_type').value = paramValue;
				break;
				case 'size':
					document.getElementById('prumo_field_size').value = paramValue.replace('.', ',');
				break;
				case 'notnull':
					document.getElementById('prumo_field_notnull').checked = true;
				break;
				case 'visible':
					if (paramValue.toLowerCase() == 'false') {
						document.getElementById('prumo_field_visible').checked = false;
					}
					else {
						document.getElementById('prumo_field_visible').checked = true;
					}
				break;
				case 'readonly':
					document.getElementById('prumo_field_readonly').checked = true;
				break;
				case 'capslock':
					document.getElementById('prumo_field_capslock').checked = true;
				break;
				case 'audit':
					document.getElementById('prumo_field_audit').checked = true;
				break;
				
				case 'fieldid':
					document.getElementById('prumo_field_fieldid').value = paramValue;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'search':
					document.getElementById('prumo_field_search').value = paramValue;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'order':
					document.getElementById('prumo_field_order').value = paramValue;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'default':
					document.getElementById('prumo_field_default').value = paramValue;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'template':
					document.getElementById('prumo_field_template').value = paramValue;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'virtual':
					document.getElementById('prumo_field_virtual').checked = true;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'nocreate':
					document.getElementById('prumo_field_nocreate').checked = true;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'noupdate':
					document.getElementById('prumo_field_noupdate').checked = true;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'unique':
					document.getElementById('prumo_field_unique').checked = true;
					this.btPrumoFieldMaisOpcoes_click();
				break;
				case 'nohtml':
					document.getElementById('prumo_field_nohtml').checked = true;
					this.btPrumoFieldMaisOpcoes_click();
				break;
			}
		}
	}
	
	this.editPrumoSearch = function() {
		this.clearPrumoSearch();
		this.readPrumoSearchLine(this.getLineCursor());
		this.showPrumoSearchEdit();
	}
	
	/**
	 * Lê uma linha de declaração de um objeto prumoSearch e preenche os campos do formulário
	 */
	this.readPrumoSearchLine = function(textLine) {
		var text = textLine.substr(textLine.indexOf("prumoSearch('")+13, textLine.length - textLine.indexOf("prumoSearch('")+13);
		var text = text.replace("');","");
		var param = explode(',',text);
		
		document.getElementById('prumo_search_obj_name').value = this.objName;
		document.getElementById('prumo_search_obj_modal').checked = false;
		document.getElementById('prumo_search_obj_autofilter').checked = true;
		document.getElementById('prumo_search_obj_debug').checked = false;
		
		for (i in param) {
			var paramPart = explode('=',param[i]);
			var paramName = paramPart[0].trim();
			if (paramPart[1] == undefined) {
				paramValue = '';
			}
			else {
				paramValue = paramPart[1].trim();
			}
			
			switch (paramName.toLowerCase()) {
				case 'title':
					document.getElementById('prumo_search_obj_title').value = paramValue;
				break;
				case 'schema':
					document.getElementById('prumo_search_obj_schema').value = paramValue;
				break;
				case 'tablename':
					document.getElementById('prumo_search_obj_tablename').value = paramValue;
				break;
				case 'pagelines':
					document.getElementById('prumo_search_obj_pagelines').value = paramValue;
					this.btPrumoSearchMaisOpcoes_click();
				break;
				case 'menushortcut':
					document.getElementById('prumo_search_obj_menushortcut').value = paramValue;
					this.btPrumoSearchMaisOpcoes_click();
				break;
				case 'modal':
					document.getElementById('prumo_search_obj_modal').checked = true;
					this.btPrumoSearchMaisOpcoes_click();
				break;
				case 'autofilter':
					if (paramValue.toLowerCase() == 'false') {
					document.getElementById('prumo_search_obj_autofilter').checked = false;
					}
					else {
					document.getElementById('prumo_search_obj_autofilter').checked = true;
					}
					this.btPrumoSearchMaisOpcoes_click();
				break;
				case 'debug':
					document.getElementById('prumo_search_obj_debug').checked = true;
					this.btPrumoSearchMaisOpcoes_click();
				break;
			}
		}
	}
	
	this.editPrumoSearchField = function() {
		this.clearPrumoSearchField();
		this.readPrumoSearchFieldLine(this.getLineCursor());
		this.showPrumoSearchFieldEdit();
	}
	
	/**
	 * Lê uma linha de adicão de campo de um objeto prumoSearch e preenche os campos do formulário
	 */
	this.readPrumoSearchFieldLine = function(textLine) {
		var text = textLine.substr(textLine.indexOf("->addField('")+12, textLine.length - textLine.indexOf("->addField('")+12);
		var text = text.replace("');","");
		
		var param = explode(',',text);
		
		document.getElementById('prumo_search_field_pk').checked = false;
		document.getElementById('prumo_search_field_visible').checked = true;
		
		for (i in param) {
			var paramPart = explode('=',param[i]);
			var paramName = paramPart[0].trim();
			if (paramPart[1] == undefined) {
				paramValue = '';
			}
			else {
				paramValue = paramPart[1].trim();
			}
			
			switch (paramName.toLowerCase()) {
				case 'pk':
					document.getElementById('prumo_search_field_pk').checked = true;
				break;
				case 'name':
					document.getElementById('prumo_search_field_name').value = paramValue;
				break;
				case 'label':
					document.getElementById('prumo_search_field_label').value = paramValue;
				break;
				case 'type':
					document.getElementById('prumo_search_field_type').value = paramValue;
				break;
				case 'visible':
					if (paramValue.toLowerCase() == 'false') {
						document.getElementById('prumo_search_field_visible').checked = false;
					}
					else {
						document.getElementById('prumo_search_field_visible').checked = true;
					}
				break;
			}
		}
	}
	
	this.writePrumo = function() {
		var objName = document.getElementById('prumo_obj_name').value;
		var title = document.getElementById('prumo_title').value;
		var schema = document.getElementById('prumo_schema').value;
		var tableName = document.getElementById('prumo_tablename').value;
		var routine = document.getElementById('prumo_routine').value;
		var capsLock = document.getElementById('prumo_capslock').checked;
		var audit = document.getElementById('prumo_audit').checked;
		
		var parentType = document.getElementById('prumo_parent_type').value;
		var nameParent1x1 = document.getElementById('prumo_name_parent1x1').value;
		var parentFieldCondition = document.getElementById('prumo_parent_field_condition').value;
		var conditionValue = document.getElementById('prumo_condition_value').value;
		var nameParent1xn = document.getElementById('prumo_name_parent1xn').value;
		var pagelines = document.getElementById('prumo_pagelines').value;
		var permission = document.getElementById('prumo_permission').value;
		var includeHead = document.getElementById('prumo_include_head').value;
		var includeFooter = document.getElementById('prumo_include_footer').value;
		var onDuplicate = document.getElementById('prumo_onduplicate').value;
		var list = document.getElementById('prumo_list').checked;
		var autoList = document.getElementById('prumo_auto_list').checked;
		var drawForm = document.getElementById('prumo_drawform').checked;
		var fastCreate = document.getElementById('prumo_fastcreate').checked;
		var fastUpdate = document.getElementById('prumo_fastupdate').checked;
		var fastDelete = document.getElementById('prumo_fastdelete').checked;
		var autoClick = document.getElementById('prumo_autoclick').checked;
		var debug = document.getElementById('prumo_debug').checked;		
		
		if (objName == '') {
			alert('É necessário informar o nome do objeto.');
			document.getElementById('prumo_obj_name').focus();
		}
		else {
			if (this.objName != '' && objName != this.objName) {
				var newText = replaceAll(this.textarea.value, '$'+this.objName, '$'+objName);
				this.textarea.value = newText;
				this.objName = objName;
			}
			
			var codeLine = '$'+objName+' = new prumoCrud(\'';
			codeLine += 'tableName='+tableName;
			if (schema != '') codeLine += ',schema='+schema;
			if (title != '') codeLine += ',title='+title;
			if (routine != '') codeLine += ',routine='+routine;
			if (capsLock) codeLine += ',capsLock';
			if (audit) codeLine += ',audit';
			
			if (parentType == '1xN') codeLine += ',parent1xN='+nameParent1xn;
			this.parentTypeChange(parentType);
			
			if (pagelines != '') codeLine += ',pageLines='+pagelines;
			if (permission != '') codeLine += ',permission='+permission;
			if (includeHead != '') codeLine += ',includeHead='+includeHead;
			if (includeFooter != '') codeLine += ',includeFooter='+includeFooter;
			if (onDuplicate != '') codeLine += ',onDuplicate='+onDuplicate;
			if (list == false) codeLine += ',list=false';
			if (autoList == false) codeLine += ',autoList=false';
			if (drawForm) codeLine += ',drawForm';
			if (fastCreate) codeLine += ',fastCreate';
			if (fastUpdate) codeLine += ',fastUpdate';
			if (fastDelete) codeLine += ',fastDelete';
			if (autoClick) codeLine += ',autoClick';
			if (debug) codeLine += ',debug';
			
			codeLine += '\');'
			
			this.replaceTextboxLine(codeLine, this.lineIndex);
			
			//Trata relacionamento 1x1
			if (nameParent1x1 != '') {
				var line1x1 = this.getLineIndex1x1();
				if (line1x1 == -1) {
					this.addLine1x1();
					line1x1 = this.getLineIndex1x1();
				}
				
				codeLine = '$'+this.objName+'->addParent1x1($'+nameParent1x1+',\''+parentFieldCondition+'\',\''+conditionValue+'\');';
				
				this.replaceTextboxLine(codeLine, line1x1);
			}
		}
	}
	
	this.replaceTextboxLine = function(newTextLine, lineIndex) {		
		if (lineIndex == undefined) {
			lineIndex = -1;
		}
		var arrLine = explode("\n",this.textarea.value);
		arrLine[lineIndex] = newTextLine;
		
		var newCode = "";
		for (i in arrLine) {
			if (newCode != '') {
				newCode += "\n";
			}
			newCode += arrLine[i];
		}
		
		this.textarea.value = newCode;
	}
	
	this.getLineIndex1x1 = function() {
		var arrLine = explode("\n",this.textarea.value);
		for (i in arrLine) {
			if (arrLine[i].indexOf(this.objName+'->addParent1x1' ) > -1) {
				return i;
			}
		}
		return -1;
	}
	
	this.addLine1x1 = function() {
		var lastLineObject = -1;
		
		var arrLine = explode("\n",this.textarea.value);
		for (i in arrLine) {
			var startLine = arrLine[i].trim().substr(1,this.objName.length);
			if (startLine == this.objName && arrLine[i].indexOf('autoInit(') == -1) {
				lastLineObject = i;
			}
		}
		
		if (lastLineObject > -1) {
			lastLineObject++;
			var oldLineCode = this.getLine(lastLineObject);
			var newLineCode = '$'+this.objName+'->addParent1x1'+"\n"+oldLineCode;
			this.replaceTextboxLine(newLineCode,lastLineObject);
		}
	}
	
	this.writePrumoField = function() {		
		var name = document.getElementById('prumo_field_name').value;
		var pk = document.getElementById('prumo_field_pk').checked;
		var label = document.getElementById('prumo_field_label').value;
		var type = document.getElementById('prumo_field_type').value;
		var size = document.getElementById('prumo_field_size').value.replace(',', '.');
		var notNull = document.getElementById('prumo_field_notnull').checked;
		var visible = document.getElementById('prumo_field_visible').checked;
		var readonly = document.getElementById('prumo_field_readonly').checked;
		var capsLock = document.getElementById('prumo_field_capslock').checked;
		var audit = document.getElementById('prumo_field_audit').checked;
		
		var fieldId = document.getElementById('prumo_field_fieldid').value;
		var pSearch = document.getElementById('prumo_field_search').value;
		var order = document.getElementById('prumo_field_order').value;
		var pDefault = document.getElementById('prumo_field_default').value;
		var pTemplate = document.getElementById('prumo_field_template').value;
		var virtual = document.getElementById('prumo_field_virtual').checked;
		var nocreate = document.getElementById('prumo_field_nocreate').checked;
		var noupdate = document.getElementById('prumo_field_noupdate').checked;
		var unique = document.getElementById('prumo_field_unique').checked;
		var nohtml = document.getElementById('prumo_field_nohtml').checked;
		
		if (type == 'string') {
			document.getElementById('prumo_field_size').removeAttribute('disabled');
		}
		else {
			document.getElementById('prumo_field_size').setAttribute('disabled', 'disabled');
		}
		
		if (name == '') {
			alert('É necessário informar o nome do campo.');
			document.getElementById('prumo_field_name').focus();
		}
		else {
			var codeLine = '$'+this.objName+'->addField(\'';
			codeLine += 'name='+name;
			if (fieldId != '') codeLine += ',fieldId='+fieldId;
			if (label != '') codeLine += ',label='+label;
			if (pk) codeLine += ',pk';
			if (type != '' && type != 'string') codeLine += ',type='+type;
			if (size != '') codeLine += ',size='+size;
			if (notNull) codeLine += ',notNull';
			if (visible == false) codeLine += ',visible=false';
			if (readonly) codeLine += ',readonly';
			if (capsLock) codeLine += ',capsLock';
			if (audit) codeLine += ',audit';
			if (pSearch != '') codeLine += ',search='+pSearch;
			if (order != '') codeLine += ',order='+order;
			if (pDefault != '') codeLine += ',default='+pDefault;
			if (pTemplate != '') codeLine += ',template='+pTemplate;
			if (virtual) codeLine += ',virtual';
			if (nocreate) codeLine += ',noCreate';
			if (noupdate) codeLine += ',noUpdate';
			if (unique) codeLine += ',unique';
			if (nohtml) codeLine += ',nohtml';
			codeLine += '\');'
			
			this.replaceTextboxLine(codeLine, this.lineIndex);
		}
	}
	
	this.writePrumoSearch = function() {
		var objName = document.getElementById('prumo_search_obj_name').value;
		var title = document.getElementById('prumo_search_obj_title').value;
		var schema = document.getElementById('prumo_search_obj_schema').value;
		var tableName = document.getElementById('prumo_search_obj_tablename').value;
		var pageLines = document.getElementById('prumo_search_obj_pagelines').value;
		var menuShortcut = document.getElementById('prumo_search_obj_menushortcut').value;
		if (document.getElementById('prumo_search_obj_modal').checked) {
			var modal = true;
		}
		else {
			var modal = false;
		}
		if (document.getElementById('prumo_search_obj_autofilter').checked) {
			var autoFilter = true;
		}
		else {
			var autoFilter = false;
		}
		if (document.getElementById('prumo_search_obj_debug').checked) {
			var debug = true;
		}
		else {
			var debug = false;
		}
		
		if (objName == '') {
			alert('É necessário informar o nome do objeto.');
			document.getElementById('prumo_search_obj_name').focus();
		}
		else {
			if (this.objName != '' && objName != this.objName) {
				var newText = replaceAll(this.textarea.value, '$'+this.objName, '$'+objName);
				this.textarea.value = newText;
				this.objName = objName;
			}
			
			if (this.state == 'newPrumoSearch' || this.state == 'editPrumoSearch') {
				var codeLine = '$'+objName+' = new prumoSearch(\'';
			}
			
			if (this.state == 'newPrumoList' || this.state == 'editPrumoList') {
				var codeLine = '$'+objName+' = new prumoList(\'';
			}
			
			codeLine += 'tableName='+tableName;
			if (schema != '') codeLine += ',schema='+schema;
			if (title != '') codeLine += ',title='+title;
			if (pageLines != '') codeLine += ',pageLines='+pageLines;
			if (menuShortcut != '') codeLine += ',menuShortcut='+menuShortcut;

			if (modal) codeLine += ',modal';
			if (autoFilter == false) codeLine += ',autoFilter=false';
			if (debug) codeLine += ',debug';
			
			codeLine += '\');'
			
			this.replaceTextboxLine(codeLine, this.lineIndex);
		}
	}
	
	this.writePrumoSearchField = function() {		
		var pk = document.getElementById('prumo_search_field_pk').checked;
		var name = document.getElementById('prumo_search_field_name').value;
		var label = document.getElementById('prumo_search_field_label').value;
		var type = document.getElementById('prumo_search_field_type').value;
		var visible = document.getElementById('prumo_search_field_visible').checked;
		
		if (name == '') {
			alert('É necessário informar o nome do campo.');
			document.getElementById('prumo_search_field_name').focus();
		}
		else {
			var codeLine = '$'+this.objName+'->addField(\'';
			codeLine += 'name='+name;
			if (pk) codeLine += ',pk';
			if (label != '') codeLine += ',label='+label;
			if (type != '' && type != 'string') codeLine += ',type='+type;
			if (visible == false) codeLine += ',visible=false';
			codeLine += '\');'
		
			this.replaceTextboxLine(codeLine, this.lineIndex);
		}
	}
	
	this.parentTypeChange = function(parentType) {
		if (parentType == '1x1') {
			document.getElementById('crudObjCrud1x1_form').style.display = 'block';
			document.getElementById('crudObjCrud1xn_form').style.display = 'none';
		}
		else if (parentType == '1xN') {
			document.getElementById('crudObjCrud1x1_form').style.display = 'none';
			document.getElementById('crudObjCrud1xn_form').style.display = 'block';
		}
		else {
			document.getElementById('crudObjCrud1x1_form').style.display = 'none';
			document.getElementById('crudObjCrud1xn_form').style.display = 'none';
		}
	}
	
	this.stateChange = function(newState) {
		hideOtherCode();
		if (newState == 'coding') {
			this.textarea.removeAttribute('readonly');
			this.textarea.focus();
			this.navigate();
			
			if (this.getLineCursor() == '') {
				this.unFreezeBtClass();
			}
		}
		else {
			this.freezeBtClass();
			this.textarea.setAttribute('readonly','readonly');
			this.lastKeyCode = 0;
		}
		this.state = newState;
		document.getElementById('state').value = this.state;
	}
	
	this.showPrumoEdit = function() {
		this.stateChange('editPrumo');
		this.divPrumo.style.display = 'block';
		document.getElementById('prumo_obj_name').focus();
	}
	
	this.showPrumoNew = function() {
		this.stateChange('newPrumo');
		this.clearPrumo();
		this.divPrumo.style.display = 'block';
		this.divPrumoMaisOpcoes.style.display = 'none';
		this.btPrumoMaisOpcoes.removeAttribute('disabled');
		document.getElementById('prumo_obj_name').focus();	
	}
	
	this.clearPrumo = function() {
		this.divPrumoMaisOpcoes.style.display = 'none';
		this.btPrumoMaisOpcoes.removeAttribute('disabled');
		
		document.getElementById('prumo_obj_name').value = '';
		document.getElementById('prumo_title').value = '';
		document.getElementById('prumo_schema').value = '';
		document.getElementById('prumo_tablename').value = '';
		document.getElementById('prumo_routine').value = '';
		document.getElementById('prumo_capslock').checked = false;
		document.getElementById('prumo_audit').checked = false;
		
		document.getElementById('prumo_parent_type').value = '';
		document.getElementById('prumo_name_parent1x1').value = '';
		document.getElementById('prumo_parent_field_condition').value = '';
		document.getElementById('prumo_condition_value').value = '';
		document.getElementById('prumo_name_parent1xn').value = '';
		document.getElementById('prumo_pagelines').value = '';
		document.getElementById('prumo_permission').value = '';
		document.getElementById('prumo_include_head').value = '';
		document.getElementById('prumo_include_footer').value = '';
		document.getElementById('prumo_onduplicate').value = '';
		document.getElementById('prumo_list').checked = true;
		document.getElementById('prumo_auto_list').checked = true;
		document.getElementById('prumo_drawform').checked = false;
		document.getElementById('prumo_fastcreate').checked = false;
		document.getElementById('prumo_fastupdate').checked = false;
		document.getElementById('prumo_fastdelete').checked = false;
		document.getElementById('prumo_autoclick').checked = false;
		document.getElementById('prumo_debug').checked = false;
	}
	
	this.showPrumoFieldEdit = function() {
		this.stateChange('editPrumoField');
		this.divPrumoField.style.display = 'block';
		document.getElementById('prumo_field_name').focus();
	}
	
	this.clearPrumoField = function() {
		this.divPrumoFieldMaisOpcoes.style.display = 'none';
		this.btPrumoFieldMaisOpcoes.removeAttribute('disabled');
		
		document.getElementById('prumo_field_name').value = '';
		document.getElementById('prumo_field_pk').checked = false;
		document.getElementById('prumo_field_label').value = '';
		document.getElementById('prumo_field_type').value = '';
		document.getElementById('prumo_field_size').value = '';
		document.getElementById('prumo_field_notnull').checked = true;
		document.getElementById('prumo_field_visible').checked = true;
		document.getElementById('prumo_field_readonly').checked = false;
		document.getElementById('prumo_field_capslock').checked = false;
		document.getElementById('prumo_field_audit').checked = false;
		
		document.getElementById('prumo_field_fieldid').value = '';
		document.getElementById('prumo_field_search').value = '';
		document.getElementById('prumo_field_order').value = '';
		document.getElementById('prumo_field_default').value = '';
		document.getElementById('prumo_field_template').value = '';
		document.getElementById('prumo_field_virtual').checked = false;
		document.getElementById('prumo_field_nocreate').checked = false;
		document.getElementById('prumo_field_noupdate').checked = false;
		document.getElementById('prumo_field_unique').checked = false;
		document.getElementById('prumo_field_nohtml').checked = false;
	}
	
	this.showPrumoSearchEdit = function() {
		this.stateChange('editPrumoSearch');
		this.divPrumoSearch.style.display = 'block';
		document.getElementById('prumo_search_obj_name').focus();
	}
	
	this.showPrumoSearchNew = function() {
		this.stateChange('newPrumoSearch');
		this.clearPrumoSearch();
		this.divPrumoSearch.style.display = 'block';
		document.getElementById('prumo_search_obj_name').focus();
	}
	
	this.showPrumoListNew = function() {
		this.stateChange('newPrumoList');
		this.clearPrumoSearch();
		this.divPrumoSearch.style.display = 'block';
		document.getElementById('prumo_search_obj_name').focus();
	}
	
	this.clearPrumoSearch = function() {
		this.divPrumoSearchMaisOpcoes.style.display = 'none';
		this.btPrumoSearchMaisOpcoes.removeAttribute('disabled');
		
		document.getElementById('prumo_search_obj_name').value = '';
		document.getElementById('prumo_search_obj_title').value = '';
		document.getElementById('prumo_search_obj_schema').value = '';
		document.getElementById('prumo_search_obj_tablename').value = '';
		document.getElementById('prumo_search_obj_pagelines').value = '';
		document.getElementById('prumo_search_obj_menushortcut').value = '';
		document.getElementById('prumo_search_obj_modal').checked = false;
		document.getElementById('prumo_search_obj_autofilter').checked = true;
		document.getElementById('prumo_search_obj_debug').checked = false;
	}
	
	this.showPrumoSearchFieldEdit = function() {
		this.stateChange('editPrumoSearchField');
		this.divPrumoSearchField.style.display = 'block';
		document.getElementById('prumo_search_field_name').focus();
	}
	
	this.clearPrumoSearchField = function() {
		document.getElementById('prumo_search_field_pk').checked = false;
		document.getElementById('prumo_search_field_name').value = '';
		document.getElementById('prumo_search_field_label').value = '';
		document.getElementById('prumo_search_field_type').value = '';
		document.getElementById('prumo_search_field_visible').checked = true;

	}
	
	this.navigate = function() {
		var newLine = this.getLineIndexCursor();
		if (newLine != this.lineIndex) {
			this.lineIndex = newLine;
			this.onLineIndexChange();
		}
		document.getElementById('lineIndex').value = this.lineIndex;
		this.getObjName(this.getLineCursor());
		
		var objCount = 0;
		var objCount = 0;
		for (i in this.line) {
			if (this.line[i].indexOf(' = new ' ) > -1) {
				objCount++;
			}
		}
		
		this.objCount = objCount;
	}
	
	this.onSessionChange = function() {
		if (this.getLineCursor() == '') {
			this.unFreezeBtClass();
		}
		else {
			this.freezeBtClass();
		}
		
		if (this.session != '') {
			this.textarea.title = 'Duplo click para editar ou enter no final da linha para adicionar um campo';
		}
		else {
			this.textarea.title = '';
		}
	}
	
	this.freezeBtClass = function() {
		btPrumo.setAttribute('disabled','disabled');
		btPrumoSearch.setAttribute('disabled','disabled');
		btPrumoList.setAttribute('disabled','disabled');
	}
	
	this.unFreezeBtClass = function() {
		var text = this.textarea.value;
		if (text.indexOf('prumoSearch') == -1 && text.indexOf('prumoList') == -1) {
			btPrumo.removeAttribute('disabled');
		}
		
		if (this.objCount == 0) {
			btPrumoSearch.removeAttribute('disabled');
			btPrumoList.removeAttribute('disabled');
		}
	}
	
	this.onLineIndexChange = function() {
		var newSession = this.getSession();
		if (this.session != newSession) {
			this.session = newSession;
			this.onSessionChange();	
		}
		if (this.getLineCursor() == '') {
			this.unFreezeBtClass();
		}
		else {
			this.freezeBtClass();
		}
		document.getElementById('session').value = this.session;
	}
	
	this.getSession = function() {
		return this.getSessionLine(this.getLineIndexCursor());
	}
	
	this.getSessionLine = function(lineIndex,recursive) {
		var line = this.getLine(lineIndex);
		
		if (recursive == undefined) {
			var recursive = true;
		}
		
		if (line.indexOf("new prumoCrud(") > -1) {
			return 'prumoCrud';
		}
		else if (line.indexOf("new prumoSearch(") > -1) {
			return 'prumoSearch';
		}
		else if (line.indexOf("new prumoList(") > -1) {
			return 'prumoList';
		}
		else if (line.indexOf("->addField(") > -1 && recursive) {
			if (lineIndex == 0) {
				return '';
			}
			var index = lineIndex - 1;
			var session = this.getSessionLine(index,false);
			while (session != 'prumoCrud' && session != 'prumoSearch' && session != 'prumoList') {
				index--;
				session = this.getSessionLine(index,false);
			}
			return session+'Field';
		}
		else if (line.indexOf("->addField(") > -1 && recursive == false) {
			return 'addField';
		}
		else {
			return '';
		}
	}
	
	this.getLineIndexCursor = function() {
		var text = this.textarea.value;
		var cursorPosition = this.textarea.selectionStart;
		var lineIndex = 0;
		var position = 0;
		for (i=0; i < text.length; i++) {
			if (text.substr(i,1) == "\n") {
				if (cursorPosition >= position && cursorPosition <= i) {
					return lineIndex;
				}
				position = i + 1;
				lineIndex++;
			}
		}
		
		return lineIndex;
	}
	
	this.getLine = function(lineIndex) {
		this.line = explode("\n",this.textarea.value);
		return this.line[lineIndex];
	}
	
	this.getLineCursor = function() {
		return this.getLine(this.getLineIndexCursor());
	}
}

/**
 * Transforma uma string em array dado um delimitador
 */
function explode(delimiter,textIn) {
	var arrOut = Array();
	var text = textIn;
	
	var i = 0;
	while (text.indexOf(delimiter) > -1) {
		var position = text.indexOf(delimiter);
		arrOut[i] = text.substr(0,position);
		text = text.substr(position + delimiter.length, text.length - position - delimiter.length);
		i++;
	}
	arrOut[i] = text;
	
	return arrOut;
}

function replaceAll(str, from, to) {
	var strOut = str;
	while (strOut.indexOf(from) > -1){
		strOut = strOut.replace(from, to);
	}
	return (strOut);
}

window.onload = function() {
	document.getElementById('prumo_code').value = "<?php\nrequire_once('prumo.php');\n\n";
	document.getElementById('prumo_code').focus();
	
	code = new classCode();
	code.init();
}

pAjaxFileManager = new prumoAjax('dev/ctrl_file_manager.php');
pAjaxFileManager.process = function() {
	if (this.operation == 'open') {
		code.state = 'coding';
		code.textarea.value = this.responseText;
		code.textarea.focus();
		code.navigate();
		showOtherCode();
	}
	
	if (this.operation == 'save') {
		if (this.responseText == 'OK') {
			showOtherCode();
		}
		else {
			alert(this.responseText);
		}
	}
}

function makeViewCode() {
	var ctrlFile = document.getElementById('file_name').value;
	var pAjaxCodeView = new prumoAjax('../'+ctrlFile+'?htmlcode');
	pAjaxCodeView.process = function() {
		document.getElementById('tabOtherCode_tab_view').innerHTML = this.responseText;
	}
	pAjaxCodeView.goAjax('');
}

function makeDdl() {
	var ctrlFile = document.getElementById('file_name').value;
	var pAjaxCodeDdl = new prumoAjax('../'+ctrlFile+'?ddl');
	pAjaxCodeDdl.process = function() {
		document.getElementById('tabOtherCode_tab_ddl').innerHTML = this.responseText;
	}
	pAjaxCodeDdl.goAjax('');
}

function showOtherCode() {
	if (document.getElementById('prumo_code').value.indexOf("new prumoCrud") > -1) {
		document.getElementById('tabOtherCode_tab_view').innerHTML = '';
		document.getElementById('tabOtherCode_tab_ddl').innerHTML = '';
		tabOtherCode.show();
		makeViewCode();
		makeDdl();
	}
}

function hideOtherCode() {
	tabOtherCode.hide();
}

function btOpen_click() {
	pAjaxFileManager.operation = 'open';
	pAjaxFileManager.goAjax('open=true&filename='+document.getElementById('file_name').value);
	
	code.divPrumo.style.display = 'none';
	code.divPrumoMaisOpcoes.style.display = 'none';
	code.divPrumoField.style.display = 'none';
	code.divPrumoFieldMaisOpcoes.style.display = 'none';
	code.divPrumoSearch.style.display = 'none';
	code.divPrumoSearchField.style.display = 'none';
}

function btSalve_click() {
	pAjaxFileManager.operation = 'save';
	pAjaxFileManager.goAjax('save=true&filename='+document.getElementById('file_name').value+'&code='+encodeURIComponent(code.textarea.value));
}

function txPrumoCode_change() {
	hideOtherCode();
}

function txFileName_change() {
	hideOtherCode();
}
