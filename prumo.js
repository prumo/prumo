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

var jPrumo = new prumo();
function prumo() {
	this.button = function(type, elementId) {
		switch (type) {
		case 'upload':
			this.uploadButton(elementId);
			break;
		default:
			throw 'unsupported type';
			break;
		}
    }
	
	this.uploadButton = function(elementId) {
		var inputs = [];
		if (typeof(elementId) != "undefined") {
			var input = document.getElementById(elementId);
			inputs[0] = input;
		} else {
			inputs = document.querySelectorAll('input[type=file]');
		}
		Array.prototype.forEach.call( inputs, function( input ) {
			var html = '<label for="' + input.id + '">'
			         + '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg>'
			         + '<span>Escolher arquivo&hellip;</span></label>';
			input.insertAdjacentHTML('afterend', html);
			input.classList.add('inputFile');
			var label	 = input.nextElementSibling,
			labelVal = label.innerHTML;

		    input.addEventListener( 'change', function( e ) {
				var fileName = '';
				if( this.files && this.files.length > 1 )
					fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
				else
					fileName = e.target.value.split( '\\' ).pop();
		
				if( fileName )
					label.querySelector( 'span' ).innerHTML = fileName;
				else
					label.innerHTML = labelVal;
		    });
		});
	}
}

/**
 * Faz uma chamada ajax e joga o resultado dentro de um container sem nenhum tratamento ou executa como javascript
 * @param file string: endereço da chamada http
 * @param param string: parametros do objeto prumoAjax
 * @param result string: id do objeto que receberá o resultado da chamada
 * @param result string: 'eval' se deseja que seja executado diretamente o javascript
 */
function pSimpleAjax(file, param, result) {
	
	var pAjax = new prumoAjax(file);
	
	if (result == 'eval') {
		pAjax.process = function() {
			eval(this.responseText);
		}
	}
	else {
		document.getElementById(result).innerHTML = '';
		
		var img = document.createElement("IMG");
		img.setAttribute('src', 'prumo/images/loading.gif');
		img.setAttribute('alt', 'carregando...');
		document.getElementById(result).appendChild(img);
		
		pAjax.process = function() {
			document.getElementById(result).innerHTML = this.responseText;
		}
	}
	
	pAjax.goAjax(param);
	
}

/**
 * Valida a data
 */
function isDate(value) {
	var date = value;
	var arrDate = new Array();
	var ExpReg = new RegExp("(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[012])/[12][0-9]{3}");
	arrDate = date.split("/");
	
	if (date.search(ExpReg) == -1) {
		return false;
	}
	else if (((arrDate[1] == 4) || (arrDate[1] == 6) || (arrDate[1] == 9) || (arrDate[1] == 11)) && (arrDate[0] > 30)) {
		return false;
	}
	else if (arrDate[1] == 2) {
		if ((arrDate[0] > 28) && ((arrDate[2]%4) != 0)) {
			return false;
		}
		if ((arrDate[0] > 29)&&((arrDate[2]%4)==0)) {
			return false;
		}
	}
	return true;
}

/**
 * Valida a hora
 */
function isTime(value) {
	
	var partValue = value.split(":");
	
	var s = '00';
	
	if (partValue.length == 2) {
		var h = partValue[0];
		var m = partValue[1];
	}
	else if (partValue.length == 3) {
		var h = partValue[0];
		var m = partValue[1];
		var s = partValue[2];
	}
	else {
		alert(3);
		return false;
	}
	
	if (!isFinite(h)) {
		alert(4);
		return false;
	}
	
	if (!isFinite(m)) {
		alert(5);
		return false;
	}
	
	if (!isFinite(s)) {
		alert(6);
		return false;
	}
	
	if (h > 23 || m > 59 || s > 59 || h < 0 || m < 0 || s < 0) {
		return false;
	}
	
	return true;
}

/**
 * Valida data e hora
 */
function isTimestamp(value) {
	
	partValue = value.split(" ");
	
	if (partValue.length != 2) {
		return false;
	}
	else if (!isDate(partValue[0])) {
		return false;
	}
	else if (!isTime(partValue[1])) {
		return false;
	}
	else {
		return true;
	}
}

/**
 * Valida o formato de um valor
 *
 * @param value mixed: valor a ser validado
 * @param type string: tipo
 *
 * @return boolean
 */
function prumoIsType(value, type) {
	
	var regexInteger = /^-?[0-9]+$/;
	var regexFloat = /^-?\d*\.?\,?\d*$/;
	
	var isValid = true;
	
	switch(type) {
		
		case 'serial':
			if (!regexInteger.test(value)) {
				isValid = false;
			}
			break;
		case 'integer':
			if (!regexInteger.test(value)) {
				isValid = false;
			}
			break;
		
		case 'numeric':
			if (!regexFloat.test(value)) {
				isValid = false;
			}
			break;
		
		case 'date':
			if (!isDate(value)) {
				isValid = false;
			}
			break;
	
		case  'time':
			if (!isTime(value)) {
				isValid = false;
			}
			break;
	
		case  'timestamp':
			if (!isTimestamp(value)) {
				isValid = false;
			}
			break;
	
		case  'boolean':
			if (value != 't' && value != 'f') {
				isValid = false;
			}
			break;
	}
	
	return isValid;
}

function gettext(str) {
	return str;
}

function md5(str) {
	// Calculate the md5 hash of a string  
	// 
	// version: 1008.1718
	// discuss at: http://phpjs.org/functions/md5	// +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
	// + namespaced by: Michael White (http://getsprink.com)
	// +	tweaked by: Jack
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +	  input by: Brett Zamir (http://brett-zamir.me)	// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// -	depends on: utf8_encode
	// *	 example 1: md5('Kevin van Zonneveld');
	// *	 returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'
	var xl; 
	var rotateLeft = function (lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}
	var addUnsigned = function (lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			}
			else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		}
		else {
			return (lResult ^ lX8 ^ lY8);
		}
	} 
	var _F = function (x,y,z) { return (x & y) | ((~x) & z); }
	var _G = function (x,y,z) { return (x & z) | (y & (~z)); }
	var _H = function (x,y,z) { return (x ^ y ^ z); }
	var _I = function (x,y,z) { return (y ^ (x | (~z))); }
	var _FF = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	}
	var _GG = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	}
	var _HH = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	}
	var _II = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	}
	var convertToWordArray = function (str) {
		var lWordCount;
		var lMessageLength = str.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=new Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	}
 
	var wordToHex = function (lValue) {
		var wordToHexValue="",wordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			wordToHexValue_temp = "0" + lByte.toString(16);
			wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length-2,2);
		}
		return wordToHexValue;
	}
 
	var x=[],		k,AA,BB,CC,DD,a,b,c,d,
		S11=7, S12=12, S13=17, S14=22,
		S21=5, S22=9 , S23=14, S24=20,
		S31=4, S32=11, S33=16, S34=23,
		S41=6, S42=10, S43=15, S44=21; 
	//str = this.utf8_encode(str);
	x = convertToWordArray(str);
	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
		xl = x.length;
	for (k=0;k<xl;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=_FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=_FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=_FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=_FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=_FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=_FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=_FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=_FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=_FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=_FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=_FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=_FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=_FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=_FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=_FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=_FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=_GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=_GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=_GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=_GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=_GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=_GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=_GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=_GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=_GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=_GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=_GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=_GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=_GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=_GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=_GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=_GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=_HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=_HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=_HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=_HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=_HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=_HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=_HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=_HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=_HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=_HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=_HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=_HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=_HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=_HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=_HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=_HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=_II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=_II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=_II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=_II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=_II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=_II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=_II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=_II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=_II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=_II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=_II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=_II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=_II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=_II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=_II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=_II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=addUnsigned(a,AA);
		b=addUnsigned(b,BB);
		c=addUnsigned(c,CC);
		d=addUnsigned(d,DD);
	}
 
	var temp = wordToHex(a)+wordToHex(b)+wordToHex(c)+wordToHex(d);
	return temp.toLowerCase();
}

/**
 * formata dados recebidos via XML
 *
 * @param type string: tipo de dado (timestamp, time, date, numeric, boolean, string)
 * @param value string: valor
 * @param textStyle string: html ou text
 */
function format(type, value, textStyle) {
	
	if (type == 'timestamp' && value != '') {
		
		var year = value.substring(0, 4);
		var month = value.substring(5, 7);
		var day = value.substring(8, 10);
		var hour = value.substring(11, 13);
		var minute = value.substring(14, 16);
		var second = value.substring(17, 19);
		var timestamp = value.substring(17, 19);
		var formatedValue = day + '/' + month + '/' + year + ' ' + hour + ':' + minute + ':' + second;
	}
	else if (type == 'time' && value != '') {
		
		var hour = value.substring(0, 2);
		var minute = value.substring(3, 5);
		var second = value.substring(6, 8)
		var formatedValue = hour + ':' + minute + ':' + second;
	}
	else if (type == 'date' && value != '') {
		
		var year = value.substring(0, 4);
		var month = value.substring(5, 7);
		var day = value.substring(8, 10);
		var formatedValue = day + '/' + month + '/' + year;
	}
	else if (type == 'numeric' && value != '') {
		
		var formatedValue = value.replace(',', '');
		formatedValue = formatedValue.replace('.', ',');
	}
	else if (type == 'boolean' && value != '' && textStyle == 'html') {
		
		if (value == 't') {
			var formatedValue = '<input type="checkbox" readonly="readonly" checked="checked" />';
		}
		else {
			var formatedValue = '<input type="checkbox" readonly="readonly" />';
		}
	}
	else {
		
		if (textStyle == 'html') {
			formatedValue = value.replace(/\\n/g, '<br />');
		}
		else {
			formatedValue = value;
		}
	}
	
	if (formatedValue == '//' || formatedValue == '//::') {
		formatedValue = '';
	}
	
	return formatedValue;
}

//////////////////////// classes ////////////////////

function classAjax() {
	try {
		ajax = new XMLHttpRequest();
	}
	catch(e) {
		alert(gettext('Seu browser não suporta Ajax XMLHttpRequest'));
		ajax = null;
	}
	return ajax;
}

/**
 * Objeto de controle de chamadas ajax
 *
 * @param ajaxFile string: caminho do script
 * @param process function: função de call back quando a chamada for bem sucedida
 */
function prumoAjax(ajaxFile, process) {
	this.ajaxFile        = ajaxFile;
	this.ajaxFormat     = 'text';
	this.ajax           = new classAjax();
	this.pLoading       = pLoading;
	this.debug          = false;
	this.identification = 'Prumo';
	this.defaultParams  = '';
	this.xmlData;
	this.responseXML;
	this.responseText;
	this.working        = false;
	this.formName = '';

	this.goAjax = function(params) {
		if (this.working == false) {
			this.pLoading.show(this);
		}
		this.working = true;
	
		this.ajax.open("POST", this.ajaxFile, true);
		this.ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		this.ajax.parent = this;
		this.ajax.onreadystatechange = function() {
			if (this.readyState == 1) {
				//state 1
			}
			if (this.readyState == 4 ) {				
				if (this.parent.debug) {
					alert(this.responseText);
				}
				this.parent.responseText = this.responseText;
				if (this.parent.ajaxFormat == 'xml') {
					this.parent.responseXML = this.responseXML;
					if (this.parent.responseXML) {
						this.parent.beforeProcess();
						this.parent.pLoading.hide(this);
					}
					else {
						alert(gettext('Formato XML inválido para "')+this.parent.ajaxFile+'"');
					}
				}
				else if (this.parent.ajaxFormat == 'text') {
					this.parent.beforeProcess();
					this.parent.pLoading.hide(this);
				}
				else {
					alert(gettext('Formato desconhecido "')+this.parent.ajaxFormat+'"');
				}
				this.parent.working = false;
			}
		}
		if (this.defaultParams != '') {
			params += '&'+this.defaultParams;
		}
		if (this.formName != '') {
			var form = document.getElementById(this.formName);
			var inputs = form.getElementsByTagName('input');
			var i;
			for (i=0;i<inputs.length;i++) {
				var name = inputs[i].name != '' ? inputs[i].name : inputs[i].id;
				if ((inputs[i].type != 'checkbox') | inputs[i].type == 'checkbox' & inputs[i].checked == true) {
					params += '&' + name + '=' + inputs[i].value;
				}
			}
			var textareas = form.getElementsByTagName('textarea');
			for (i=0;i<textareas.length;i++) {
				var name = textareas[i].name != '' ? textareas[i].name : textareas[i].id;
				params += '&' + name + '=' + textareas[i].value;
			}
			var selects = form.getElementsByTagName('select');
			for (i=0;i<selects.length;i++) {
				var name = selects[i].name != '' ? selects[i].name : selects[i].id;
				for (var j=0;j< selects[i].length;j++) {
					if (selects[i][j].selected) {
						params += '&' + name + '[]=' + selects[i][j].value;
					}
				}
			}
			params = params.replace(/^&/, '');
		}
		if (this.debug) {
			alert(params);
		}
		this.ajax.send(params);
	}
	
	this.setFormName = function(formName) {
		if (document.getElementById(formName) !== null) {
			this.formName = formName;
		} else {
			alert('Error (setFormName): Elemento "' + formName + '" não encontrado!');
		}
	}
	
	this.beforeProcess = function() {
		if (this.ajaxFormat == 'xml') {
			this.xmlData = this.responseXML.getElementsByTagName(this.identification);
			this.process();			
		}
		else {
			this.process();
		}
	}
	
	this.ajaxXmlOk = function() {
		//para ajaxFormat xml, sobrescrever este método após o constructor
		alert('Implementar o método ajaxXmlOk');
	}
	
	this.ajaxXmlError = function(err,msg) {
		//para ajaxFormat xml, sobrescrever este método após o constructor
		alert(msg);
	}
	
	// para ajaxFormat texto, sobrescrever este método após o constructor
	if (process == undefined) {
		this.process = function() {
			if (this.ajaxFormat == 'xml') {
				try {
					xmlTagErr = this.responseXML.getElementsByTagName('err')[0];
					if (xmlTagErr == undefined) {
						this.ajaxXmlOk();
					}
					else {
						err = this.responseXML.getElementsByTagName('err')[0].firstChild.nodeValue;
						msg = this.responseXML.getElementsByTagName('msg')[0].firstChild.nodeValue;
						this.ajaxXmlError(err, msg);
					}
				}
				catch(e) {
					this.ajaxXmlOk();
				}			
			}
		}
	}
	else {
		this.process = process;
	}
}

function prumoCrud(objName, ajaxFile) {
	this.objName = objName;
	this.modal;
	this.identification = objName;
	this.state = 'new'; //suport new,edit,view,list
	
	this.fieldName;
	this.fieldPk;
	this.fieldId;
	this.fieldLabel;
	this.fieldType;
	this.fieldNotNull;
	
	this.fieldOldValue = Array();
	this.fieldNewValue = Array();
	this.sonSearch = Array();

	this.pAjax;
	this.pCrudList = false;
	this.pSearch;
	
	// permissões
	this.permC = true;
	this.permR = true;
	this.permU = true;
	this.permD = true;
	
	// para relação 1x1
	this.parent1x1 = false;
	this.parent1x1Condition = new Array();
	this.son1x1 = new Array();
	
	// para relação 1xN
	this.parent1xN = false;
	this.son1xN = new Array();
	
	this.isVisible = true;
	
	this.defaultParamCreate;
	
	this.pAjax = new prumoAjax(ajaxFile);
	this.pAjax.ajaxFormat = 'xml';
	this.pAjax.parent = this;
	this.pAjax.identification = this.identification;
	this.pAjax.ajaxXmlOk = function() {
		// copia o cmd para os filhos 1x1
		for (ii in this.parent.son1x1) {
			this.parent.son1x1[ii].pAjax.cmd = this.cmd;
		}
		
		var status = this.responseXML.getElementsByTagName('status').length > 0 ? this.responseXML.getElementsByTagName('status')[0].firstChild.nodeValue : 'ok';
		if (status == 'err') {
			var msg = this.responseXML.getElementsByTagName('msg')[0].firstChild.nodeValue;
			alert(msg);
			if (this.parent.state == 'list' || this.parent.state == 'edit' || this.parent.state == 'new') {
				this.parent.unFreezeFields();
				this.parent.toggleFreezeControls(false);
			}
		}
		else {
			
			if (this.cmd == 'create') {
				this.parent.assignResponseXML(this.responseXML);
				this.parent.stateChange('view');
				this.parent.visibleSon1x1();
				this.parent.retrieveVirtual();
				
				var controls = document.getElementById(this.parent.objName+'_control_view');
				for (i in controls.childNodes) {
					if (controls.childNodes[i].nodeType == 1) {
						controls.childNodes[i].focus();
						break;
					}
				}
				
				this.parent.afterCreate();
			}
			else if (this.cmd == 'fast_create') {
				this.parent.afterCreate();
				this.parent.stateChange('list');
			}
			else if (this.cmd == 'retrieve') {
				this.parent.assignResponseXML(this.responseXML);
				this.parent.stateChange('view');
				this.parent.visibleSon1x1();
				this.parent.retrieveVirtual();
				this.parent.afterRetrieve();
			}
			else if (this.cmd == 'update') {
				this.parent.assignResponseXML(this.responseXML);
				this.parent.stateChange('view');
				this.parent.retrieveVirtual();
				this.parent.afterUpdate();
			}
			else if (this.cmd == 'fast_update') {
				this.parent.afterUpdate();
				this.parent.stateChange('list');
			}
			else if (this.cmd == 'delete') {
				this.parent.afterDelete();
			}
			else if (this.cmd == 'fast_delete') {
				this.parent.afterDelete();
				this.parent.stateChange('list');
			}
			else if (this.cmd == 'copyFrom') {
				this.parent.assignResponseXML(this.responseXML);
				this.parent.clearSerials();
				this.parent.writeNewValues();
				this.parent.retrieveVirtual();
				this.parent.stateChange('copy');
				this.parent.visibleSon1x1();
			}
			else {
				status = this.responseXML.getElementsByTagName('status')[0].firstChild.nodeValue;
				msg = this.responseXML.getElementsByTagName('msg')[0].firstChild.nodeValue;
				alert(msg);
				this.parent.clear();
				this.parent.stateChange('new');
				this.parent.visibleSon1x1();
			}
			
			this.parent.toggleFreezeControls(false);
		}
	}
	
	/**
	 * Trata erro XML em objeto CRUD
	 */
	this.pAjax.ajaxXmlError = function(err, msg) {
		alert(msg);
		if (this.parent.state != 'view') {
			this.parent.unFreezeFields();
			this.parent.toggleFreezeControls(false);
		}
	}
	
	/**
	 * Verifica se existe algum search ligado a algum campo "virtual"
	 */
	this.retrieveVirtual = function() {
		for (j in this.sonSearch) {
		
			var canVirtual = false;
			for (k in this.sonSearch[j].fieldReturn) {
				for (l in this.fieldId) {
					if (this.sonSearch[j].fieldReturn[k][1] == this.fieldId[l] && this.fieldVirtual[l] == true) {
						canVirtual = true;
					}
				}
			}
			
			if (canVirtual) {
				this.sonSearch[j].goRetrieve();
			}
		}
		
		// Aplica o mesmo método recursivamente aos relacionamentos 1x1
		for (ii in this.son1x1) {
			if (this.fieldNewValue[this.son1x1[ii].parent1x1Condition['fieldName']] == this.son1x1[ii].parent1x1Condition['value'] || this.son1x1[ii].parent1x1Condition['fieldName'] == '') {
				this.son1x1[ii].retrieveVirtual();
			}
		}
	}

	this.addParent1x1 = function(parent,parentFieldCondition,conditionValue) {
		this.parent1x1 = parent;
		parent.son1x1.push(this);
		var arrCondition = Array();
		arrCondition['fieldName'] = parentFieldCondition;
		arrCondition['value'] = conditionValue;
		this.parent1x1Condition = arrCondition;
	}
	
	this.addParent1xN = function(parent) {
		this.parent1xN = parent;
		parent.son1xN.push(this);
	}
	
	this.addSonSearch = function(son) {
		this.sonSearch[this.sonSearch.length] = son;
	}
	
	this.assignResponseXML = function(responseXML) {
		this.xmlData = responseXML.getElementsByTagName(this.objName);

		//limpa o formulário
		if (this.parent1x1 == false) {
			this.clear();
		}
		
		//limpa o array de valores
		this.fieldOldValue = Array();
		this.fieldNewValue = Array();

		// laço que percorre os campos
		
		if (this.xmlData.length == 0) {
			if (this.isVisible) {
				alert(this.objName + ': ' + gettext('Falta preencher alguns campos'));
			}
		}
		else {
			for (i=0; i < this.fieldName.length; i++) {
				// quando é copia não deve preencher os campos noCreate
				if (this.pAjax.cmd == 'copyFrom' && this.fieldNoCreate[i]) {
					var value = '';
				}
				else {
					if (this.fieldVirtual[i]) {
						var value = '';
					}
					else {
						var value = this.xmlData[0].getElementsByTagName(this.fieldName[i])[0].childNodes[0].nodeValue;
					}
				}
				// trata nulos
				if (value == 'NULL') {
					value = '';
				}
				// trata quebra de linha
				value = value.replace(/\\n/g,'\n');
				if (value != '') {
					value = format(this.fieldType[i], value, 'text');
				}
				
				this.fieldOldValue[this.fieldName[i]] = value;
				this.fieldNewValue[this.fieldName[i]] = value;
			}
			this.writeNewValues();
		}
		
		// Aplica o mesmo método recursivamente aos relacionamentos 1x1
		for (var ii in this.son1x1) {
			if (this.fieldNewValue[this.son1x1[ii].parent1x1Condition['fieldName']] == this.son1x1[ii].parent1x1Condition['value'] || this.son1x1[ii].parent1x1Condition['fieldName'] == '') {
				this.son1x1[ii].assignResponseXML(responseXML);
			}
		}
	}
	
	this.clearSerials = function() {
		for (iSerials in this.fieldType) {
			if (this.fieldType[iSerials] == 'serial') {
				this.fieldOldValue[this.fieldName[iSerials]] = '';
				this.fieldNewValue[this.fieldName[iSerials]] = '';
			}
		}
	}
	
	this.visibleSon1x1 = function() {
		this.readNewValues();
		for (i in this.son1x1) {
			if (this.fieldNewValue[this.son1x1[i].parent1x1Condition['fieldName']] == this.son1x1[i].parent1x1Condition['value'] || this.son1x1[i].parent1x1Condition['fieldName'] == '') {
				this.son1x1[i].visibleForm(true);
			}
			else {
				this.son1x1[i].visibleForm(false);
			}
			
			this.son1x1[i].visibleSon1x1();
		}
	}
	
	this.visibleForm = function(state) {
		if (state) {
			this.isVisible = true;
			document.getElementById(this.objName+'_form').style.display = 'block';
		}
		else {
			this.isVisible = false;
			document.getElementById(this.objName+'_form').style.display = 'none';
		}
	}
	
	/**
	 * Valida se não possui campos com id duplicado entre os objetos com relacionamento 1x1 (exceto chave primária)
	 */
	this.validateDuplicatedIds = function() {
		var msg = 'prumoCrud: '+gettext('Campo com propriedade "fieldId" duplicado entre objetos "%crudParent%" e "%crudSon%" fieldId="%fieldId%"');
		var msgPk = 'prumoCrud: '+gettext('Campo com propriedade "fieldId" difirente entre objetos "%crudParent%" e "%crudSon%" fieldId="%fieldId%", para campo chave primária do relacionamento 1x1');
		
		for (i in this.son1x1) {
			for (j in this.fieldId) {
				for (k in this.son1x1[i].fieldId) {
					if (this.fieldId[j] == this.son1x1[i].fieldId[k]) {
						// excessão para a validação quando é campo chave primária de relacionamento 1x1
						if (this.son1x1[i].parent1x1 == false || this.son1x1[i].fieldPk[k] == false) {
							var fieldId = this.son1x1[i].fieldId[k];
							var crudParent = this.objName;
							var crudSon = this.son1x1[i].objName;
							msgValidate = msg.replace('%crudParent%',crudParent);
							msgValidate = msgValidate.replace('%crudSon%',crudSon);
							msgValidate = msgValidate.replace('%fieldId%',fieldId);
							alert(msgValidate);
							return false;
						}
					}
					else {
						if (this.son1x1[i].parent1x1 == true && this.son1x1[i].fieldPk[k] == true) {
							var fieldId = this.son1x1[i].fieldId[k];
							var crudParent = this.objName;
							var crudSon = this.son1x1[i].objName;
							msgValidate = msgPk.replace('%crudParent%',crudParent);
							msgValidate = msgValidate.replace('%crudSon%',crudSon);
							msgValidate = msgValidate.replace('%fieldId%',fieldId);
							alert(msgValidate);
							return false;
						}
					}
				}
			}
		}
		return true;
	}
	
	this.readNewValues = function() {
		for (i=0; i < this.fieldName.length; i++) {
			// verifica se tem pai ou campo não é chave primaria
			inputField = document.getElementById(this.fieldId[i]);
			if (inputField == null) {
				alert(this.objName+' Error: Campo "'+this.fieldId[i]+'" não encontrado!');
			}
			if (inputField.getAttribute('type') == 'checkbox') {
				if (inputField.checked) {
					this.fieldNewValue[this.fieldName[i]] = 't';
				}
				else {
					this.fieldNewValue[this.fieldName[i]] = 'f';
				}
			}
			else {
				this.fieldNewValue[this.fieldName[i]] = inputField.value;
			}
		}
		
		for (ii in this.son1x1) {
			this.son1x1[ii].readNewValues();
		}
	}
	
	this.writeNewValues = function() {
		for (i=0; i < this.fieldName.length; i++) {
			var value = this.fieldNewValue[this.fieldName[i]];
			// verifica se tem pai ou campo não é chave primaria
			if (this.parent1x1 == false || !this.fieldPk[i]) {
				inputField = document.getElementById(this.fieldId[i]);
				if (inputField.getAttribute('type') == 'checkbox') {
					if (value == 't') {
						inputField.checked = true;
					}
					else {
						inputField.checked = false;
					}
				}
				else {
					inputField.value = value;
				}
			}
		}
	}
	
	/**
	 * Limpa os campos do formulário
	 */
	this.clear = function() {	
		for (i=0; i < this.fieldName.length; i++) {
			if (this.fieldReadonly[i] == false || this.fieldNoCreate[i] == true) {
				if (this.parent1x1 == false || !this.fieldPk[i] || this.fieldNoCreate[i] == true) {
					inputField = document.getElementById(this.fieldId[i]);
					if (inputField == undefined) {
						alert(this.objName + ': ' + gettext('campo') + ' "' + this.fieldId[i] + '" ' + gettext('não encontrado'));
					}
					if (this.fieldType[i] == 'boolean') {
						inputField.removeAttribute('checked');
						inputField.checked = false;
					}
					else {
						inputField.value = '';
						inputField.checked = true;
					}
				}
			}
		}
		for (ii in this.son1x1) {
			this.son1x1[ii].clear();
		}
		this.setDefault();
	}
	
	/**
	 * Seta o valor padrão
	 */
	this.setDefault = function() {
		for (i=0; i < this.fieldName.length; i++) {
			inputField = document.getElementById(this.fieldId[i]);
			if (inputField != undefined && this.fieldDefault[i] != '') {
				if (this.fieldType[i] == 'boolean') {
					if (this.fieldDefault[i] == 't' || this.fieldDefault[i] == 'true') {
						inputField.setAttribute('checked','checked');
						inputField.checked = true;
					}
					else {
						inputField.removeAttribute('checked');
						inputField.checked = false;
					}
				}
				else {
					inputField.value = this.fieldDefault[i].toLowerCase() == 'null' ? '' : this.fieldDefault[i];
				}
			}
		}
	}
	
	this.paramPk = function() {
		param = '';
		for (i = 0; i < this.fieldPk.length; i++) {
			if (this.fieldPk[i]) {
				var fieldValue = this.fieldNewValue[this.fieldName[i]];
				param += '&new_'+this.fieldId[i]+'='+encodeURIComponent(fieldValue);
			}
		}
		return param;
	}
	
	this.syncPkSon = function() {
		var arrRel = Array();
		
		//prepara um array com as chaves do objeto atual
		var parentPk = Array();
		for (i in this.fieldId) {
			if (this.fieldPk[i]) {
				parentPk.push(this.fieldId[i]);
			}
		}

		//laço que percorre os filhos 1x1
		for (i in this.son1x1) {
			var sonPk = Array();
			for (j in this.son1x1[i].fieldPk) {
				if (this.son1x1[i].fieldPk[j]) {
					sonPk.push(this.son1x1[i].fieldName[j]);
				}
			}
			if (parentPk.length != sonPk.length) {
				return false;
			}
			else {
				for (j in parentPk) {
					arrRel.push(Array(parentPk[j],sonPk[j]));
				}
			}
		}

		// replica o valor entre os campos chave
		for (ii in this.son1x1) {
			for (j in arrRel) {
				this.son1x1[ii].fieldNewValue[arrRel[j][1]] = document.getElementById(arrRel[j][0]).value;
			}
		}
		
		// aplica o mesmo método recursivamente
		for (iii in this.son1x1) {
			this.son1x1[iii].syncPkSon();
		}
	}

	this.paramCreate = function() {
		var params = '';
		for (i = 0; i < this.fieldPk.length; i++) {
			if (this.parent1x1 == false || this.fieldPk[i] == false) {
				var fieldValue = this.fieldNewValue[this.fieldName[i]];
				params += '&new_'+this.fieldId[i]+'='+encodeURIComponent(fieldValue);
			}
		}
		
		if (this.parent1x1 == false) {
			params = 'objName='+this.objName + '&' + this.objName+'_action=c'+params;		
		}
		else {
			params = this.objName+'_action=c'+params;
		}

		for (iSon in this.son1x1) {
			if (this.son1x1[iSon].isVisible) {			
				if (this.fieldNewValue[this.son1x1[iSon].parent1x1Condition['fieldName']] == this.son1x1[iSon].parent1x1Condition['value'] || this.son1x1[iSon].parent1x1Condition['fieldName'] == '') {
					params += '&'+this.son1x1[iSon].paramCreate();
				}
			}
		}
		
		if (this.defaultParamCreate != '') {
			params += '&' + this.defaultParamCreate;
		}
		
		return params
	}

	this.doCreate = function() {
		if (this.beforeCreate()) {
			if (this.validateDuplicatedIds()) {
				this.freezeFields();
				this.toggleFreezeControls(true);
				this.readNewValues();
				this.pAjax.cmd = 'create';
				this.pAjax.goAjax(this.paramCreate());
			}
		}
	}
	
	this.doFastCreate = function() {
		if (this.beforeCreate()) {
			if (this.validateDuplicatedIds()) {
				this.freezeFields();
				this.toggleFreezeControls(true);
				this.readNewValues();
				this.pAjax.cmd = 'fast_create';
				this.pAjax.goAjax(this.paramCreate());
			}
		}
	}
	
	this.paramRetrieve = function() {
		if (this.parent1x1 == false) {
			var	params = 'objName='+this.objName+'&';
		}
		else {
			var	params = '';
		}
		
		params += this.objName+'_action=r'+this.paramPk();
		for (i in this.son1x1) {
			params += '&'+this.son1x1[i].paramRetrieve();
		}
		return params;
	}
	
	this.doRetrieve = function() {
		if (this.beforeRetrieve()) {
			if (this.validateDuplicatedIds()) {
				this.toggleFreezeControls(true);
				this.readNewValues();
				this.syncPkSon();
				this.pAjax.cmd = 'retrieve';
				this.pAjax.goAjax(this.paramRetrieve());
			}
		}
	}
	
	this.doCopyFrom = function() {
		if (this.validateDuplicatedIds()) {
			this.readNewValues();
			this.syncPkSon();
			this.pAjax.cmd = 'copyFrom';
			this.pAjax.goAjax(this.paramRetrieve());
		}
	}
	
	this.paramUpdate = function() {
		var params = '';
		for (i = 0; i < this.fieldPk.length; i++) {
			if (this.parent1x1 == false || this.fieldPk[i] == false) {
				var fieldNewValue = this.fieldNewValue[this.fieldName[i]];
				var fieldOldValue = this.fieldOldValue[this.fieldName[i]];
				params += '&old_'+this.fieldId[i]+'='+encodeURIComponent(fieldOldValue);
				params += '&new_'+this.fieldId[i]+'='+encodeURIComponent(fieldNewValue);
			}
		}
		
		if (this.parent1x1 == false) {
			params = 'objName='+this.objName + '&' + this.objName+'_action=u'+params;
		}
		else {
			params = this.objName+'_action=u'+params;
		}
		
		for (ii in this.son1x1) {
			if (this.son1x1[ii].isVisible) {
				params += '&'+this.son1x1[ii].paramUpdate();
			}
		}
		return params;
	}
	
	this.doUpdate = function() {
		if (this.beforeUpdate()) {
			if (this.validateDuplicatedIds()) {
				this.freezeFields();
				this.toggleFreezeControls(true);
				this.readNewValues();
				this.syncPkSon();
				this.pAjax.cmd = 'update';
				this.pAjax.goAjax(this.paramUpdate());
			}
		}
	}
	
	this.doFastUpdate = function() {
		if (this.beforeUpdate()) {
			if (this.validateDuplicatedIds()) {
				this.freezeFields();
				this.toggleFreezeControls(true);
				this.readNewValues();
				this.syncPkSon();
				this.pAjax.cmd = 'fast_update';
				this.pAjax.goAjax(this.paramUpdate());
			}
		}
	}
	
	this.doDelete = function() {
		if (this.beforeDelete()) {
			if (this.validateDuplicatedIds()) {
				this.readNewValues();
				var	param = this.paramPk();
			
				if (this.parent1x1 == false) {
					param += '&objName='+this.objName;
				}
				
				this.toggleFreezeControls(true);
				
				this.pAjax.cmd = 'delete';
				this.pAjax.goAjax(this.objName+'_action=d&'+param);
			}
		}
	}
	
	this.doFastDelete = function() {
		if (this.beforeDelete()) {
			if (this.validateDuplicatedIds()) {
				this.readNewValues();
				var	param = this.paramPk();
			
				if (this.parent1x1 == false) {
					param += '&objName='+this.objName;
				}
				
				this.toggleFreezeControls(true);
				
				this.pAjax.cmd = 'fast_delete';
				this.pAjax.goAjax(this.objName+'_action=d&'+param);
			}
		}
	}
	
	this.toggleFreezeControls = function(freeze) {
		var btName = Array('write_new', 'copy_from', 'clear', 'search', 'write_edit', 'cancel_edit', 'search_view', 'edit', 'delete', 'new', 'fast_create', 'fast_update');
		for (var i=0; i < btName.length; i++) {
			if (document.getElementById(this.objName+'_bt_'+btName[i]) != undefined) {
				if (freeze == true) {
					document.getElementById(this.objName+'_bt_'+btName[i]).setAttribute('disabled', 'disabled');
				}
				else {
					document.getElementById(this.objName+'_bt_'+btName[i]).removeAttribute('disabled');
				}
			}
		}
	}
	
	this.freezeFields = function() {
		var fieldCount = this.fieldId.length;
		for (i=0; i < fieldCount; i++) {
			var inputField = document.getElementById(this.fieldId[i]);
			inputField.setAttribute('title',inputField.value);
			if (this.parent1x1 == false || !this.fieldPk[i]) {
				
				inputField.setAttribute('disabled','disabled');
				
				var inputFastCreate = document.getElementById(this.objName+'_'+this.fieldName[i]+'_add');
				if (inputFastCreate != undefined) {
					inputFastCreate.setAttribute('disabled','disabled');
				}
				
				var inputFastUpdate = document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit');
				if (inputFastUpdate != undefined) {
					inputFastUpdate.setAttribute('disabled','disabled');
				}
			}
		}
		
		for (i=0; i < this.sonSearch.length; i++) {
			document.getElementById(this.sonSearch[i].objName+'Bt').setAttribute('disabled','disabled');
		}
	}
	
	this.unFreezeFields = function() {
		
		for (i=0; i < this.sonSearch.length; i++) {
			document.getElementById(this.sonSearch[i].objName+'Bt').removeAttribute('disabled');
		}
		
		var fieldCount = this.fieldId.length;
		for (i=0; i < fieldCount; i++) {
			
			var inputField = document.getElementById(this.fieldId[i]);
			var inputFastCreate = document.getElementById(this.objName+'_'+this.fieldName[i]+'_add');
			var inputFastUpdate = document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit');
			
			inputField.removeAttribute('title');
			
			if (this.parent1x1 == false || !this.fieldPk[i]) {
				
				if (this.fieldType[i] != 'serial' && this.fieldReadonly[i] == false && (this.state != 'list' && this.state != 'edit' || this.fieldNoUpdate[i] == false)) {
					
					inputField.removeAttribute('disabled');
					
					if (inputFastCreate != undefined) {
						inputFastCreate.removeAttribute('disabled');
					}
					
					if (inputFastUpdate != undefined) {
						inputFastUpdate.removeAttribute('disabled');
					}
				}
				else {
					
					if (inputField.pSearch != undefined) {
						document.getElementById(inputField.pSearch.objName+'Bt').setAttribute('disabled','disabled');
					}
					
					inputField.setAttribute('disabled','disabled');
					
					if (inputFastCreate != undefined) {
						inputFastCreate.setAttribute('disabled','disabled');
					}
					
					if (inputFastUpdate != undefined) {
						inputFastUpdate.setAttribute('disabled','disabled');
					}
				}
			}
		}
		
		this.formFocus();
	}
	
	this.backToForms = function() {
		divForms = document.getElementById(this.objName+'_form');
		if (divForms != 'undefined') {
			divForms.style.display = 'block';
		}
		if (this.pCrudList) {
			this.pCrudList.hide();
		}
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o stateChange do prumoCrud
	 */
	this.onStateChange = function() {
		return true;
	}
	
	this.beforeStateChange = function() {
		return true;
	}
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doRetrieve do prumoCrud,
	 * continuando somente se o retorno for true
	 *
	 * @returns boolean
	 */
	this.beforeCreate = function() {
		return true;
	}
		
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doRetrieve do prumoCrud,
	 * continuando somente se o retorno for true
	 *
	 * @returns boolean
	 */
	this.beforeRetrieve = function() {
		return true;
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doUpdate do prumoCrud,
	 * continuando somente se o retorno for true
	 *
	 * @returns boolean
	 */
	this.beforeUpdate = function() {
		return true;
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doDelete do prumoCrud,
	 * continuando somente se o retorno for true
	 *
	 * @returns boolean
	 */
	this.beforeDelete = function() {
		return true;
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o retrieve do prumoCrud
	 */
	this.afterCreate = function() {
		//
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o update do prumoCrud
	 */
	this.afterUpdate = function() {
		//
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o retrieve do prumoCrud
	 */
	this.afterRetrieve = function() {
		return true;
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o delete do prumoCrud
	 */
	this.afterDelete = function() {
		//
	}
	
	this.hideSon1xN = function() {
		for (iSon in this.son1xN) {
			this.son1xN[iSon].hide();
		}
	}
	
	/**
	 * Sincroniza os filtros dos objetos filhos com as chaves primárias do objeto pai
	 */
	this.syncPkToSon1xN = function() {
		for (iSon in this.son1xN) {
			if (this.son1xN[iSon].pCrudList) {
				this.son1xN[iSon].pCrudList.pFilter.clearVisibleFilters();
			}
			if (this.son1xN[iSon].pSearch) {
				this.son1xN[iSon].pSearch.pFilter.clearVisibleFilters();
			}
			for (iField in this.fieldPk) {
				if (this.fieldPk[iField] == true) {
					for (iFieldSon in this.son1xN[iSon].fieldId) {
						if (this.son1xN[iSon].fieldId[iFieldSon] == this.fieldId[iField]) {
							var fieldName = this.son1xN[iSon].fieldName[iFieldSon];
						}
					}
					var fieldId = this.fieldId[iField];
					var fieldValue = document.getElementById(fieldId).value;
					if (this.son1xN[iSon].pCrudList) {
						this.son1xN[iSon].pCrudList.pFilter.setInvisibleFilter(fieldName,'equal',fieldValue);
					}
					if (this.son1xN[iSon].pSearch) {
						this.son1xN[iSon].pSearch.pFilter.setInvisibleFilter(fieldName,'equal',fieldValue);
					}
				}
			}
			if (this.son1xN[iSon].pCrudList) {
				this.son1xN[iSon].pCrudList.pFilter.draw();
			}
			if (this.son1xN[iSon].pSearch) {
				this.son1xN[iSon].pSearch.pFilter.draw();
			}
			var sonDivForms = document.getElementById(this.son1xN[iSon].objName+'_form');
			if (sonDivForms != undefined) {
				sonDivForms.style.display = 'none';
			}
			if (this.son1xN[iSon].pCrudList) {
				this.son1xN[iSon].pCrudList.goSearch();
			}
		}
	}
	
	/**
	 * Muda o stado do objeto crud
	 * 
	 * @param newState string: suporta new, view, edit e list
	 */
	this.stateChange = function(newState) {
		this.state = newState;
		
		if (!this.beforeStateChange()) {
			return;
		}
		if (newState == 'new' || newState == 'copy') {
			if (newState == 'new') {
				this.clear();
			}
			this.unFreezeFields();
			if (this.parent1x1 == false) {
				document.getElementById(this.objName+'_control_new').style.display  = 'block';
				document.getElementById(this.objName+'_control_view').style.display = 'none';
				document.getElementById(this.objName+'_control_edit').style.display = 'none';
			}
			this.hideSon1xN();
			if (this.parent1xN) {
				this.parent1xN.hideControls();
			}
		}		
		else if (newState == 'view') {
			this.freezeFields();
			if (this.parent1x1 == false) {
				document.getElementById(this.objName+'_control_new').style.display  = 'none';
				document.getElementById(this.objName+'_control_view').style.display = 'block';
				document.getElementById(this.objName+'_control_edit').style.display = 'none';
			}
			this.syncPkToSon1xN();
			if (this.parent1xN) {
				this.parent1xN.showControls();
			}
		}
		else if (newState == 'edit') {
			this.unFreezeFields();
			if (this.parent1x1 == false) {
				document.getElementById(this.objName+'_control_new').style.display  = 'none';
				document.getElementById(this.objName+'_control_view').style.display = 'none';
				document.getElementById(this.objName+'_control_edit').style.display = 'block';
			}
			this.hideSon1xN();
			if (this.parent1xN) {
				this.parent1xN.hideControls();
			}
		}
		else if (newState == 'list') {
			divForms = document.getElementById(this.objName+'_form');
			
			if (divForms != undefined && divForms != null) {
				divForms.style.display = 'none';
				
				if (this.pCrudList == false) {
					if (this.parent1x1 == false) {
						alert(this.objName+': '+'Listagem não disponível');
					}
				}
				else {
					this.pCrudList.goSearch();
				}
			}
			else {
				alert(this.objName+': ('+this.objName+'_form'+') '+'Formulário não encontrado');
			}
			
			this.hideSon1xN();
			if (this.parent1xN) {
				this.parent1xN.showControls();
			}
		}
		else {
			var msg = gettext('Estado desconhecido para objeto prumoCrud: stateChange(\'"%newState%"\')');
			msg = msg.replace('%newState%',newState);
			alert(msg);
		}
		
		for (i in this.son1x1) {
			this.son1x1[i].stateChange(newState);
		}
		
		for (iSon in this.son1xN) {
			divContainer = document.getElementById(this.son1xN[iSon].objName+'_container');
			if (divContainer != undefined && divContainer != null) {
				if (newState == 'view') {
					divContainer.style.display = 'block';
				}
				else {
					divContainer.style.display = 'none';
				}
			}
		}
		
		this.onStateChange();
	}
	
	/**
	 * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após a validação de notNull
	 */
	this.preValidate = function() {
		return true;
	}
	
	/**
	 * Valida formato dos campos
	 */
	this.errValidateType = function() {
		
		var msg = '';
		var err = '';
		var fieldValue = '';
		
		for (var i in this.fieldName) {
			
			msg = '';
			fieldValue = this.fieldNewValue[this.fieldName[i]];
			if (fieldValue != '' && prumoIsType(fieldValue, this.fieldType[i]) == false) {
				
				switch (this.fieldType[i]) {
					
					case 'serial':
						msg = '- '+gettext('"%fieldValue%" não é um número inteiro, campo "%fieldLabel%"')+'.\n';
						break;
					case 'integer':
						msg = '- '+gettext('"%fieldValue%" não é um número inteiro, campo "%fieldLabel%"')+'.\n';
						break;
					case 'numeric':
						msg = '- '+gettext('"%fieldValue%" não é um número válido, campo "%fieldLabel%"')+'.\n';
						break;
					case 'date':
						msg = '- '+gettext('"%fieldValue%" não é uma data válida, campo "%fieldLabel%"')+'.\n';
						break;
					case 'time':
						msg = '- '+gettext('"%fieldValue%" não é uma hora válida, campo "%fieldLabel%"')+'.\n';
						break;
					case 'timestamp':
						msg = '- '+gettext('"%fieldValue%" não é uma data e hora válida, campo "%fieldLabel%"')+'.\n';
						break;
					case 'boolean':
						msg = '- '+gettext('"%fieldValue%" não é boleano válido, campo "%fieldLabel%"')+'.\n';
						break;
				}
				
				if (msg != '') {
					msg = msg.replace('%fieldLabel%', this.fieldLabel[i]);
					err += msg.replace('%fieldValue%', fieldValue);
				}
			}
			
			var fieldName = this.fieldName[i];
			if (this.fieldValidator.hasOwnProperty(fieldName)) {
				validators = this.fieldValidator[fieldName];
				for (validator in validators) {
					pValidator = new prumoValidator({'type': validator, 'value': validators[validator]});
					msg = pValidator.validate(fieldValue);
					if (msg != true) {
						msg = '- ' + gettext('Campo %fieldLabel%: ') + msg + '.\n';
						err += msg.replace('%fieldLabel%', this.fieldLabel[i]);
					}
				}
			}
		}
		
		for (var i in this.son1x1) {
			if (this.fieldNewValue[this.son1x1[i].parent1x1Condition['fieldName']] == this.son1x1[i].parent1x1Condition['value'] || this.son1x1[i].parent1x1Condition['fieldName'] == '') {
				err += this.son1x1[i].errValidateType();
			}
		}
		
		return err;
	}
	
	/**
	 * Valida campos notNull
	 */
	this.errValidateNotNull = function() {
		this.readNewValues();
		var err = '';
		for (var i in this.fieldName) {
			if (this.fieldNotNull[i] && this.fieldNewValue[this.fieldName[i]] == '') {
				var msg = '- '+gettext('Campo "%fieldLabel%" não pode ficar em branco')+'.\n'
				err += msg.replace('%fieldLabel%',this.fieldLabel[i]);
			}
		}
		
		for (var i in this.son1x1) {
			if (this.fieldNewValue[this.son1x1[i].parent1x1Condition['fieldName']] == this.son1x1[i].parent1x1Condition['value'] || this.son1x1[i].parent1x1Condition['fieldName'] == '') {
				err += this.son1x1[i].errValidateNotNull();
			}
		}
		
		return err;
	}
	
	this.validateNotNull = function() {
		err = this.errValidateNotNull();
		err += this.errValidateType();
		
		if (err == '') {
			return this.preValidate();
		}
		else {
			alert(err);
			this.focusNotNull();
			return false;
		}
	}

	this.focusNotNull = function() {
		
		var fucusOk = false;
		for (i in this.fieldName) {
			if (this.fieldNotNull[i] && this.fieldNewValue[this.fieldName[i]] == '') {
				if (this.parent1x1 == false || !this.fieldPk[i]) {
					
					if (document.getElementById(this.objName+'_'+this.fieldName[i]+'_add') != undefined) {
						document.getElementById(this.objName+'_'+this.fieldName[i]+'_add').focus();
					}
					else if (document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit') != undefined) {
						document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit').focus();
					}
					else {
						if (this.pCrudList) {
							this.pCrudList.hide();
							this.backToForms();
						}
						document.getElementById(this.fieldId[i]).focus();
					}
					
					fucusOk = true;
					break;
				}
			}
		}
		for (ii in this.son1x1) {
			if (fucusOk == false && this.son1x1[ii].isVisible) {
				this.son1x1[ii].focusNotNull();
			}
		}
	}
	
	this.formFocus = function() {
		if (this.parent1x1 == false) {
			for (i in this.fieldName) {
				inputField = document.getElementById(this.fieldId[i]);
				if (inputField.getAttribute('disabled') != 'disabled') {
					inputField.focus();
					break;
				}
			}
		}
	}
	
	this.bt_write_new = function() {
		if (this.validateNotNull()) {
			this.doCreate();
		}
		this.visibleSon1x1();
	}
	
	this.bt_search = function() {
		if (this.pCrudList == false) {
			this.pSearch.goSearch();
		}
		else {
			this.stateChange('list');
		}
	}
	
	this.bt_write_edit = function() {
		if (this.validateNotNull()) {
			this.doUpdate();
		}
	}
	
	this.bt_cancel_edit = function() {
		this.doRetrieve();
	}
	
	this.bt_edit = function() {
		this.stateChange('edit');
	}
	
	this.bt_delete = function() {
		if(confirm(gettext('Confirma a exclusão do registro?'))){
			this.doDelete();
			this.stateChange('new');
		}
	}
	
	this.bt_new = function() {
		if (this.pCrudList) {
			this.pCrudList.hide();
		}
		this.backToForms();
		this.stateChange('new');
		this.visibleSon1x1();
	}
	
	this.bt_list = function() {
		this.stateChange('list');
	}
	
	this.bt_copyFrom = function() {
		this.pSearch.goSearch();
	}
	
	this.hide = function() {
		document.getElementById(this.objName+'_form').style.display = 'none';
		if (this.pCrudList) {
			document.getElementById('pCrudList_'+this.objName).style.display = 'none';
		}
	}
	
	/**
	 * Torna visível os controles do crud
	 */
	this.showControls = function() {
		if (this.parent1x1) {
			this.parent1x1.showControls();
		}
		else {
			if (document.getElementById(this.objName+'_controls') == undefined) {
				alert('Botões de controle não encontrado para objeto "'+this.objName+'".');
			}
			else {
				document.getElementById(this.objName+'_controls').style.display = 'block';
			}
		}
	}
	
	/**
	 * Torna invisível os controles do crud
	 */
	this.hideControls = function() {
		if (this.parent1x1) {
			this.parent1x1.hideControls();
		}
		else {
			if (document.getElementById(this.objName+'_controls') == undefined) {
				alert('Botões de controle não encontrado para objeto "'+this.objName+'".');
			}
			else {
				document.getElementById(this.objName+'_controls').style.display = 'none';
			}
		}
	}
	
	/**
	 * Redirecionamento para o mesmo método em this.pCrudList.pFilter e this.pSearch.pFilter
	 */
	this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.pSearch.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
		if (this.pCrudList != false) {
			this.pCrudList.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
		}
	}
	
	/**
	 * Redirecionamento para o mesmo método em this.pCrudList.pFilter e this.pSearch.pFilter
	 */
	this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.pSearch.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
		if (this.pCrudList != false) {
			this.pCrudList.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
		}
	}
	
	this.inputSetValue = function(id, value) {
		var inputField = document.getElementById(id);
		
		if (inputField != undefined) {
			
			if (inputField.getAttribute('type') == 'checkbox') {
				if (value == 't') {
					inputField.checked = true;
				}
				else {
					inputField.checked = false;
				}
			}
			else {
				inputField.value = value;
			}
		}
	}
	
	this.inputGetValue = function(id) {
		var inputField = document.getElementById(id);
		if (inputField.getAttribute('type') == 'checkbox') {
			if (inputField.checked || inputField.getAttribute('checked') == 'checked') {
				return 't';
			}
			else {
				return 'f';
			}
		}
		else {
			return inputField.value;
		}
	}
	
	this.bt_fastCreate = function() {
		
		this.stateChange('new');
		
		var fieldCount = this.fieldId.length;
		for (i=0; i < fieldCount; i++) {
			
			var id = this.objName+'_'+this.fieldName[i]+'_add';
			if (document.getElementById(id) != undefined) {
				this.inputSetValue(this.fieldId[i], this.inputGetValue(id));
			}
		}
		
		this.visibleSon1x1();
		
		if (this.validateNotNull()) {
			document.getElementById(this.objName+'_bt_fast_create').setAttribute('disabled', 'disabled');
			this.doFastCreate();
		}
	}
	
	this.bt_fastCreate_onKeyDown = function(event) {
		//Enter
		if (event.keyCode == 13) {
			this.bt_fastCreate();
		}
	}
	
	this.bt_fastUpdate = function(lineIndex) {
		
		this.stateChange('edit');
		this.clear();
		
		var fieldCount = this.fieldId.length;
		for (i=0; i < fieldCount; i++) {
			
			var id = this.objName+'_'+this.fieldName[i]+'_edit';
			inputFastUpdate = document.getElementById(id);
			
			var oldValue = this.pCrudList.pGrid.getValue(this.fieldName[i], lineIndex);			
			if (inputFastUpdate == undefined) {
				var newValue = format(this.fieldType[i], oldValue, 'text');
			}
			else {
				var newValue = this.inputGetValue(id);
			}
			
			this.fieldOldValue[this.fieldName[i]] = format(this.fieldType[i], oldValue, 'text');
			this.fieldNewValue[this.fieldName[i]] = newValue;
			
			this.inputSetValue(this.fieldId[i], newValue);
		}
		
		this.visibleSon1x1();
		
		if (this.validateNotNull()) {
			document.getElementById(this.objName+'_bt_fast_update').setAttribute('disabled', 'disabled');
			this.doFastUpdate();
		}
	}
	
	this.bt_fastUpdate_onKeyDown = function(event, lineIndex) {
		//Enter
		if (event.keyCode == 13) {
			this.bt_fastUpdate(lineIndex);
		}
		
		//esc
		if (event.keyCode == 27) {
			this.pCrudList.assignResponseXML(this.pCrudList.responseXML);
		}
	}
	
	this.bt_fastDelete = function(lineIndex) {
		
		if(confirm(gettext('Confirma a exclusão do registro?'))){
			var fieldCount = this.fieldId.length;
			for (i=0; i < fieldCount; i++) {
				var value = format(this.fieldType[i], this.pCrudList.pGrid.getValue(this.fieldName[i], lineIndex), 'text');
				this.inputSetValue(this.fieldId[i], value);
			}
			
			this.freezeFields();
			this.doFastDelete();
		}
	}
}

/**
 * class prumoCrudList
 */
function prumoCrudList(objName, ajaxFile) {
	
	this.objName = objName;
	this.modal;
	this.identification = objName;
	this.page;
	this.orderBy;
	
	this.crudName;

	this.pAjax;
	this.pFilter;
	this.pGrid;
	this.pGridNavigation;
	
	this.autoClick = false;
	
	this.fieldReturn = Array();
	
	this.selected = false;
	
	this.responseXml;

	this.modal = true;
	this.pAjax = new prumoAjax(ajaxFile);
	this.pAjax.ajaxFormat = 'xml';
	this.pAjax.parent = this;
	this.pAjax.identification = this.identification;
	this.pAjax.pLoading = pLoading;
	this.pAjax.ajaxXmlOk = function() {
		this.parent.assignResponseXML(this.responseXML);
		if (this.parent.autoClick == true && this.parent.pGridNavigation.count == 1) {
			this.parent.lineClick(0);
		}
		document.getElementById(this.parent.objName+'_btSearch').removeAttribute('disabled');
		document.getElementById(this.parent.objName+'_btSearchAll').removeAttribute('disabled');
		
		this.parent.afterList();
	}
	
	this.assignResponseXML = function(responseXML) {
		
		this.responseXML = responseXML;
		this.pGrid.assignResponseXML(responseXML);
		this.pGridNavigation.assignResponseXML(responseXML);
		this.pFilter.assignResponseXML(responseXML);
		
		if (this.fastCreate || this.fastUpdate || this.fastDelete) {
			
			var xmlData = this.responseXML.getElementsByTagName(this.pGrid.xmlIdentification);
			
			//laço que com a quantidade de linhas do xml
			if (this.fastDelete) {
				
				// pega o id da coluna onde será colocado o botão excluir
				var iColumnControls = 0;
				for (j=0; j < this.pGrid.field.length; j++) {
					if (this.pGrid.fieldVisible[j]) {
						iColumnControls++;
					}
				}
				
				for (i=0; i < xmlData.length; i++) {
					
					// linha do grid
					var pGridRowCells = document.getElementById(this.pGrid.objName).rows[i+1].cells;
					
					// botão de excluir
					var htmlInputDelete = '<button class="pButton-outline" onclick="'+this.crudName+'.bt_fastDelete('+i+')"><img src="prumo/images/bt_remove.png" /></button>';
					pGridRowCells[iColumnControls].innerHTML = htmlInputDelete;
				}
			}
			
			if (this.fastCreate) {
				
				this.parent.clear();
				
				// percorre as colunas
				var iColumn = 0;
				for (j=0; j < this.pGrid.field.length; j++) {
				
					if (this.pGrid.fieldVisible[j]) {
						
						// faz referencia a linha do grid
						var pGridRowCells = document.getElementById(this.pGrid.objName).rows[xmlData.length+1].cells;
						
						var id = this.crudName+'_'+this.pGrid.field[j]+'_add';
						var htmlInput = this.parent.fieldTemplate[j];
						var htmlInput = htmlInput.replace('id=""', 'id="'+id+'"');
						
						htmlInput = htmlInput.replace(':id:', id);
						pGridRowCells[iColumn].innerHTML = htmlInput;
						document.getElementById(id).setAttribute('onkeydown', this.crudName+'.bt_fastCreate_onKeyDown(event)');
						this.parent.inputSetValue(id, document.getElementById(this.parent.fieldId[j]).value);
						
						iColumn++;
					}
				}
				
				// botão gravar novo
				var htmlInputNew = '<button class="pButton-outline" id="'+this.crudName+'_bt_fast_create" onclick="'+this.crudName+'.bt_fastCreate()"><img src="prumo/images/bt_ok.png" /></button>';
				pGridRowCells[iColumn].innerHTML = htmlInputNew;
				
				this.parent.unFreezeFields();
			}
		}
	}
	
	this.afterSearch = function() {
		//implementar conforme necessidade
	}
	
	this.afterList = function() {
		//implementar conforme necessidade
	}
	
	this.parametersFilters = function() {
		
		param = '';
		for (i=0; i < this.pFilter.filter.length; i++) {
			param += '&fField[]='+encodeURIComponent(this.pFilter.filter[i].fieldName);
			param += '&fOperator[]='+this.pFilter.filter[i].operator;
			param += '&fValue[]='+encodeURIComponent(this.pFilter.filter[i].value);
			param += '&fValue2[]='+encodeURIComponent(this.pFilter.filter[i].value2);
			param += '&fVisible[]='+this.pFilter.filter[i].visible;
		}
		
		return param;
	}
	
	this.parameters = function() {
		
		if (this.page == undefined) {
			this.page = 1;
		}
		
		if (this.crudName != undefined) {
			var param = 'objName='+this.crudName;
		}
		else {
			var param = 'objName='+this.objName;
		}
		
		param += '&'+this.objName+'_action=makeXml';
		param += '&page='+this.page;
		if (this.orderBy != undefined) {
			param += '&orderBy='+ this.orderBy;
		}
		
		param += this.parametersFilters();
		
		return param;
	}
	
	this.lineClick = function(lineIndex) {
		
		this.assignResponseXML(this.responseXML);
		
		if (this.fastUpdate) {
			
			// percorre as colunas e adiciona os campos de edição
			var iColumn = 0;
			for (j=0; j < this.pGrid.field.length; j++) {
				
				if (this.pGrid.fieldVisible[j]) {
					
					var value = format(this.pGrid.fieldType[j], this.pGrid.getValue(this.pGrid.field[j], lineIndex), 'text');
					var pGridRowCells = document.getElementById(this.pGrid.objName).rows[lineIndex+1].cells;
					var id = this.crudName+'_'+this.pGrid.field[j]+'_edit';
					var htmlInput = this.parent.fieldTemplate[j];
					var htmlInput = htmlInput.replace('id=""', 'id="'+id+'"');
					
					pGridRowCells[iColumn].innerHTML = htmlInput;
					document.getElementById(id).setAttribute('onkeydown', this.crudName+'.bt_fastUpdate_onKeyDown(event, '+lineIndex+')');
					this.parent.inputSetValue(id, value);
					
					pGridRowCells[iColumn].removeAttribute('onClick');
					pGridRowCells[iColumn].style.cursor = 'default';
					
					if (j == 0) {
						document.getElementById(id).focus();
					}
					
					iColumn++;
				}
			}
			
			//botão de atualizar
			var htmlInputUpdate = '<button class="pButton-outline" id="'+this.crudName+'_bt_fast_update" onclick="'+this.crudName+'.bt_fastUpdate('+lineIndex+')"><img src="prumo/images/bt_ok.png" /></button>';
			
			// botão de excluir
			//var htmlInputDelete = '<button onclick="'+this.crudName+'.bt_fastDelete('+lineIndex+')"><img src="prumo/images/bt_remove.png" /></button>';
			
			pGridRowCells[iColumn].innerHTML = htmlInputUpdate;
			
			////limpa o campo de inserir
			var xmlData = this.responseXML.getElementsByTagName(this.pGrid.xmlIdentification);
			var i = xmlData.length + 1;
			
			var pGridRow = document.getElementById(this.pGrid.objName).rows[i];
			for (j=0; j < pGridRow.cells.length; j++) {
				pGridRow.cells[j].style.cursor = 'default';
				pGridRow.cells[j].removeAttribute('onClick');
				pGridRow.cells[j].innerHTML = '<br />';
			}
			
			// limpa os botoes de excluir dos outros registros
			for (i=0; i < xmlData.length; i++) {
				if (i != lineIndex) {
					var pGridRowCells = document.getElementById(this.pGrid.objName).rows[i+1].cells;
					pGridRowCells[iColumn].innerHTML = '<br />';
				}
			}
			
			this.parent.unFreezeFields();
		}
		else {
			
			for (i=0; i < this.fieldReturn.length; i++) {
			
				var value = this.pGrid.getValue(this.fieldReturn[i][0], lineIndex);
				var fieldReturn = document.getElementById(this.fieldReturn[i][1]);
				
				if (fieldReturn != undefined) {
					
					var type = this.fieldReturn[i][2];
					if (fieldReturn.getAttribute('type') == 'checkbox') {
						
						if (value == 't') {
							fieldReturn.checked = true;
						}
						else {
							fieldReturn.checked = false;
						}
					}
					else {
						fieldReturn.value = format(type, value, 'text');
					}
				}
				else {
				
					var msg = gettext('Campo "%fieldName%" não encontrado, verifique a chamada "addFieldReturn" do objeto "%objName%"');
					msg = msg.replace('%fieldName%', this.fieldReturn[i][1]);
					msg = msg.replace('%objName%', this.objName);
					alert(msg);
				}
			}
			
			this.hide();
			this.parent.doRetrieve();
			this.parent.backToForms();
			this.afterSearch();
		}
	}
	
	this.goSearch = function(page) {
		
		if (this.pAjax.working) {
			alert(this.objName + ': ' + gettext('já está trabalhando'));
		}
		else {
			
			if (page != undefined) {
				this.page = page;
			}
			
			if (page == undefined) {
				if (this.pGridNavigation.count == 1 && this.pFilter.count > 0) {
					this.pFilter.clearValues();
				}
				this.pGrid.clear();
				this.pGridNavigation.clear();
			}
			
			this.show();
			document.getElementById(this.objName+'_btSearch').setAttribute('disabled', 'disabled');
			document.getElementById(this.objName+'_btSearchAll').setAttribute('disabled', 'disabled');
			
			this.pAjax.goAjax(this.parameters());
		}
	}
	
	this.cmdSearch = function() {
		if (this.pFilter.validateFilters()) {
			this.goSearch(1);
		}
	}
	
	this.cmdSearchAll = function() {
		
		this.pFilter.clearValues();
		this.cmdSearch();
		this.pFilter.draw();
	}
	
	this.addFieldReturn = function(fieldName, idReturn, fieldType) {
		this.fieldReturn[this.fieldReturn.length] = Array(fieldName, idReturn, fieldType);
	}
	
	this.show = function() {
		document.getElementById(this.objName).style.display = 'block';
	}
  
	this.hide = function() {
		document.getElementById(this.objName).style.display = 'none';
	}
	
	this.upArrow = function() {
		
		this.selected = true;
		
		if (this.pGrid.selectedLine == 0) {
			this.pGrid.selectedLine = this.pGrid.lines + 1;
		}
		
		var nextLine = this.pGrid.selectedLine - 1;
		
		if (this.pGrid.selectedLine > 1) {
			
			this.pGrid.selectedLine = nextLine;
			this.pGrid.onmouseover(this.pGrid.selectedLine);
		}
		
		if (nextLine == 0) {
			
			if (this.pGridNavigation.page > 1) {
				this.goSearch(this.pGridNavigation.page * 1 - 1);
			}
		}
	}
	
	this.downArrow = function() {
		
		this.selected = true;
		var nextLine = this.pGrid.selectedLine + 1;
		
		if (this.pGrid.selectedLine < this.pGrid.lines) {
			
			this.pGrid.selectedLine = nextLine;
			this.pGrid.onmouseover(this.pGrid.selectedLine);
		}
		
		if (nextLine == this.pGrid.lines + 1) {
			
			if (this.pGridNavigation.page < this.pGridNavigation.pages) {
				this.goSearch(this.pGridNavigation.page * 1 + 1);
			}
		}
	}
	
	this.enterKey = function() {
		
		if (this.selected) {
			this.lineClick(this.pGrid.selectedLine-1);
		}
		else {
			this.cmdSearch();
		}
	}
	
	this.keyDown = function() {
		this.selected = false;
	}
	
	this.sort = function(field, order) {
		if (order == undefined) {
			var element = document.getElementById('prumoGridTh_' + this.objName + '_' + field);
			var className = element.className;
			var arr = className.match(/sort(.*)/g) || [''];
			switch (arr[0]) {
			case '':
				element.setAttribute('class', 'prumoGridTh sortAsc');
				order = 'asc';
				break;
			case 'sortAsc':
				element.className = 'prumoGridTh sortDesc';
				order = 'desc';
				break;
			case 'sortDesc':
				element.className = 'prumoGridTh sortAsc';
				order = 'asc';
				break;
			default:
				element.className = 'prumoGridTh sortAsc';
				order = 'asc';
			}
			var elements = this.pGrid.field;
			for (i in elements) {
				element = document.getElementById('prumoGridTh_' + this.objName + '_' + elements[i]);
				if (elements[i] != field && this.pGrid.fieldVisible[i] == true) {
					element.setAttribute('class', 'prumoGridTh');	
				}
			}
		}
		this.orderBy = field + ' ' + order;
		this.goSearch(this.page);
	}
	
	/**
	 * Redirecionamento para o mesmo método em this.pFilter
	 */
	this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
	}
	
	/**
	 * Redirecionamento para o mesmo método em this.pFilter
	 */
	this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
	}
}

function prumoFilter(objName) {
	this.objName = objName;
	this.pAjax = new prumoAjax();
	this.pAjax.ajaxFormat = 'xml';
	this.page;
	this.prumoWebPath;
	this.xmlIdentification = 'pFilter';
	
	this.fieldName  = new Array();
	this.fieldLabel = new Array();
	this.fieldType  = new Array();
	
	this.filter = new Array();
	this.count = 0;
	
	//operadores lógicos para campos em formato texto
	this.textOperators        = Array(
	                                    'like',
	                                    'not like',
	                                    'begins with',
	                                    'ends with',
	                                    'not begins with',
	                                    'not ends with',
	                                    'equal',
	                                    'not equal',
   	                                    'is null',
   	                                    'not is null'
							         );
	this.textOperatorsName    = Array(
	                                    gettext('contém'),
	                                    gettext('não contém'),
	                                    gettext('começa com'),
	                                    gettext('termina com'),
	                                    gettext('não começa com'),
	                                    gettext('não termina com'),
							            gettext('igual a'),
							            gettext('diferente de'),
							            gettext('é nulo'),
							            gettext('não é nulo')
							         );
	//operadores lógicos para campos em formato numerico
	this.numericOperators     = Array(
							            'numeric equal',
							            'numeric not equal',
							            'less than',
							            'greater than',
							            'less than or equal',
							            'greater than or equal',
							            'between',
   	                                    'is null',
   	                                    'not is null'
							         );
	this.numericOperatorsName = Array(
							            gettext('igual a'),
							            gettext('diferente de'),
							            gettext('menor que'),
							            gettext('maior que'),
							            gettext('menor ou igual a'),
							            gettext('maior ou igual a'),
							            gettext('entre'),
							            gettext('é nulo'),
							            gettext('não é nulo')
							         );
	//operadores lógicos para campos boleanos
	this.booleanOperators        = Array(
							            'equal',
							            'not equal',
   	                                    'is null',
   	                                    'not is null'
							         );
	this.booleanOperatorsName    = Array(
							            gettext('igual a'),
							            gettext('diferente de'),
							            gettext('é nulo'),
							            gettext('não é nulo')
							         );
	
	/**
	 * Adiciona um filtro
	 *
	 * @param filterIndex integer: número do filtro, também aceita null para pegar o próximo disponível
	 * @param fieldName string: nome do campo
	 * @param operator string: operador inicial
	 * @param aValue string: valor inicial
	 * @param aValue2 string: segundo campo usado em condição BETWEEN
	 */
	this.addFilter = function(filterIndex, fieldName, operator, value, value2, visible) {
		function aFilter(aFieldName, aOperator, aValue, aValue2, aVisible) {
			this.fieldName = aFieldName;
			this.operator  = aOperator;
			this.value     = aValue;
			this.value2    = aValue2;
			this.visible   = aVisible;
		}
		var newFilter = new aFilter(fieldName, operator, value, value2, visible);
		if (filterIndex == null) {
			this.filter.push(newFilter);
		}
		else {
			this.filter.splice(filterIndex, 0, newFilter);
		}
		this.count++;
	}
	
	/**
	 * Coloca o foco no primeiro filtro
	 */
	this.focus = function() {
		for (i in this.filter) {
			if (this.filter[i].visible) {
				document.getElementById(this.objName+'_'+i+'_value').focus();
				break;
			}
		}
	}
	
	/**
	 * Evento do botão (-) do filtro
	 */
	this.cmdAddFilter = function(filterIndex) {
		this.addFilter(filterIndex,'','','','',true);
		this.draw();
	}
	
	/**
	 * Remove um determinado filtro
	 *
	 * @param filterIndex integer: número do filtro
	 */
	this.removeFilter = function(filterIndex) {
		removed = this.filter.splice(filterIndex,1);
		this.count--;
		return removed;
	}
	
	/**
	 * Evento do botão (-) do filtro
	 *
	 * @param filterIndex integer: número do filtro
	 */
	this.cmdRemoveFilter = function(filterIndex) {
		this.removeFilter(filterIndex);
		this.draw();
	}
	
	/**
	 * Remove todos os filtros
	 */
	this.clearFilters = function() {
		this.filter = new Array();
	}
	
	/**
	 * Remove apenas os filtros visíveis
	 */
	this.clearVisibleFilters = function() {
		var backupFilter = this.filter;
		this.clearFilters();
		
		for (iFilter in backupFilter) {
			if (backupFilter[iFilter].visible == false) {
				this.filter.push(backupFilter[iFilter]);
			}
		}
	}
	
	/**
	 * Pega o tipo do campo
	 *
	 * @param fieldName string: nome do campo
	 * @returns string: fieldType daquele campo
	 */
	this.fieldTypeByName = function(fieldName) {
		for (i=0; i < this.fieldName.length; i++) {
			if (fieldName == this.fieldName[i]) {
				return this.fieldType[i];
			}
		}
		return null;
	}
	
	/**
	 * Pega o rotulo do campo
	 *
	 * @param fieldName string: nome do campo
	 * @returns string: fieldType daquele campo
	 */
	this.fieldLabelByName = function(fieldName) {
		for (i=0; i < this.fieldName.length; i++) {
			if (fieldName == this.fieldName[i]) {
				return this.fieldLabel[i];
			}
		}
		return null;
	}
	
	/**
	 * Pega o nome do operador
	 *
	 * @param fieldName string: nome do campo
	 * @returns string: tipo de operador daquele campo (text, numeric ou boolean)
	 */
	this.operatorTypeByName = function(fieldName) {
		var fieldType = this.fieldTypeByName(fieldName);
		
	    switch (fieldType) {
		    case 'string':
		        return 'text';
		        break;
		    case 'integer':
		        return 'numeric';
		        break;
		    case 'serial':
		        return 'numeric';
		        break;
		    case 'numeric':
		        return 'numeric';
		        break;
		    case 'date':
		        return 'numeric';
		        break;
		    case 'time':
		        return 'numeric';
		        break;
		    case 'timestamp':
		        return 'numeric';
		        break;
		    case 'boolean':
		        return 'boolean';
		        break;
		default:
            return 'text';
	    }
	}
	
	/**
	 * Evento disparado pelo onChange do select "nome do campo"
	 * 
	 * @param selectFieldName string: nome do campo
	 * @param index integer: número do filtro
	 */
	this.selectFieldChange = function(selectFieldName, index) {
		var operatorType = this.operatorTypeByName(selectFieldName.value);
		var selectOperator = document.getElementById(this.objName+'_'+index+'_operator');
		
		var arrOperators = Array();
		var arrOperatorsName = Array();
		
		if (operatorType == 'text') {
			arrOperators     = this.textOperators;
			arrOperatorsName = this.textOperatorsName;
		}
		if (operatorType == 'numeric') {
			arrOperators     = this.numericOperators;
			arrOperatorsName = this.numericOperatorsName;
		}
		if (operatorType == 'boolean') {
			arrOperators     = this.booleanOperators;
			arrOperatorsName = this.booleanOperatorsName;
		}
		
		var htmlOptions = '';
		for (j=0; j < arrOperators.length; j++) {
			htmlOptions += '<option value="'+arrOperators[j]+'">'+arrOperatorsName[j]+'</option>\n';
		}

		selectOperator.innerHTML = htmlOptions;	
		this.filter[index].fieldName = selectFieldName.value;
		
		selectOperator.value = arrOperators[0];
		this.selectOperatorChange(selectOperator, index);

		// coloca o foco no campo de pesquisa
		document.getElementById(this.objName+'_'+index+'_value').focus();
		
		// redesenha o input
		var currentValue = document.getElementById(this.objName+'_'+index+'_value').value;
		
		var htmlInput = '';
		var labelTrue = gettext('Sim');
		var labelFalse = gettext('Não');
		if (operatorType == 'boolean') {
			htmlInput  = '<select id="'+this.objName+'_'+index+'_value" onchange="'+this.objName+'.inputValueChange(this,'+index+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+index+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+index+')">';
			htmlInput += '<option value="t">'+labelTrue+'</option>';
			htmlInput += '<option value="f">'+labelFalse+'</option>';
			htmlInput += '<option value=""></option>';
			htmlInput += '</select>';
		}
		else {
			htmlInput = '<input type="text" id="'+this.objName+'_'+index+'_value" size="15" onchange="'+this.objName+'.inputValueChange(this,'+index+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+index+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+index+')" />\n';
		}
		
		document.getElementById(this.objName+'_'+index+'_input').innerHTML = htmlInput;
		document.getElementById(this.objName+'_'+index+'_value').value = currentValue;
		document.getElementById(this.objName+'_'+index+'_value').focus();
	}
	
	/**
	 * Valida o valor dos filtros
	 *
	 * @return boolean
	 */
	this.validateFilters = function() {
		
		var err = '';
		var msg = '';
		
		for (var i in this.filter) {
			
			var fieldName = document.getElementById(this.objName+'_'+i+'_field').value;
			var fieldLabel = this.fieldLabelByName(fieldName);
			var fieldType = this.fieldTypeByName(fieldName);
			var fieldOperator = document.getElementById(this.objName+'_'+i+'_operator').value;
			var fieldValue = document.getElementById(this.objName+'_'+i+'_value').value;
			var fieldValue2 = document.getElementById(this.objName+'_'+i+'_value2').value;
			
			msg = '';
			if (fieldValue != '' && prumoIsType(fieldValue, fieldType) == false) {
				
				switch (fieldType) {
					
					case 'serial':
						msg = '- '+gettext('"%fieldValue%" não é um número inteiro, filtro "%fieldLabel%"');
						break;
					case 'integer':
						msg = '- '+gettext('"%fieldValue%" não é um número inteiro, filtro "%fieldLabel%"');
						break;
					case 'numeric':
						msg = '- '+gettext('"%fieldValue%" não é um número válido, filtro "%fieldLabel%"');
						break;
					case 'date':
						msg = '- '+gettext('"%fieldValue%" não é uma data válida, filtro "%fieldLabel%"');
						break;
					case 'time':
						msg = '- '+gettext('"%fieldValue%" não é uma hora válida, filtro "%fieldLabel%"');
						break;
					case 'timestamp':
						msg = '- '+gettext('"%fieldValue%" não é uma data e hora válida, filtro "%fieldLabel%"');
						break;
					case 'boolean':
						msg = '- '+gettext('"%fieldValue%" não é um boleano válido, filtro "%fieldLabel%"');
						break;
				}
				
				msg = msg.replace('%fieldValue%', fieldValue);
				if (err == '') {
					document.getElementById(this.objName+'_'+i+'_value').focus();
				}
			}
			
			if (fieldOperator == 'between' && fieldValue2 != '' && prumoIsType(fieldValue2, fieldType) == false) {
				
				switch (fieldType) {
					
					case 'serial':
						msg = '- '+gettext('"%fieldValue%" não é um número inteiro, filtro "%fieldLabel%"');
						break;
					case 'integer':
						msg = '- '+gettext('"%fieldValue%" não é um número inteiro, filtro "%fieldLabel%"');
						break;
					case 'numeric':
						msg = '- '+gettext('"%fieldValue%" não é um número válido, filtro "%fieldLabel%"');
						break;
					case 'date':
						msg = '- '+gettext('"%fieldValue%" não é uma data válida, filtro "%fieldLabel%"');
						break;
					case 'time':
						msg = '- '+gettext('"%fieldValue%" não é uma hora válida, filtro "%fieldLabel%"');
						break;
					case 'timestamp':
						msg = '- '+gettext('"%fieldValue%" não é uma data e hora válida, filtro "%fieldLabel%"');
						break;
				}
				
				msg = msg.replace('%fieldValue%', fieldValue2);
				if (err == '') {
					document.getElementById(this.objName+'_'+i+'_value2').focus();
				}
			}
			
			if (msg != '') {
				msg = msg.replace('%fieldLabel%', fieldLabel);
				err += (err == '') ? msg : '\n'+msg;
			}
		}
		
		if (err != '') {
			alert(err);
			return false;
		}
		else {
			return true;
		}
	}
	
	/**
	 * Evento disparado pelo onChange do select "nome do campo"
	 * 
	 * @param selectFieldName string: nome do campo
	 * @param index integer: número do filtro
	 */
	this.selectOperatorChange = function(selectOperator, index) {
		// passa o valor escolhido para o objeto filter
		this.filter[index].operator = selectOperator.value;
		
		if (selectOperator.value == 'is null' || selectOperator.value == 'not is null') {
			document.getElementById(this.objName+'_'+index+'_input').style.display = 'none';
		}
		else {
			document.getElementById(this.objName+'_'+index+'_input').style.display = 'block';
		}
		
		if (selectOperator.value == 'between') {
			document.getElementById(this.objName+'_'+index+'_input2').style.display = 'block';
		}
		else {
			document.getElementById(this.objName+'_'+index+'_input2').style.display = 'none';
		}
		
		// coloca o foco no campo de pesquisa
		document.getElementById(this.objName+'_'+index+'_value').focus();
	}
	
	/**
	 * Copia o valor de um input para a propriedade filter[index] do objeto prumoFilter
	 *
	 * @param inputObject objeto dom
	 * @param index integer: número do filtro
	 */
	this.inputValueChange = function(inputObject,index) {
		this.filter[index].value = inputObject.value;
	}
	
	/**
	 * Copia o valor de um input para a propriedade filter[index] do objeto prumoFilter
	 *
	 * @param inputObject objeto dom
	 * @param index integer: número do filtro
	 */
	this.input2ValueChange = function(inputObject,index) {
		this.filter[index].value2 = inputObject.value;
	}
	
	/**
	 * Evento disparado pelo onKeyup das caixas de texto dos filtros
	 * @param event: evento
	 * @param index integer: número do filtro
	 */
	this.inputValueKeyUp = function(event,index) {
		switch (event.keyCode) {
			case 13: //ENTER
				this.parent.enterKey();
				break;
			case 27: //ESC
				this.parent.cancel();
				break;
			case 113: //F2
				break;
			case 38: //Seta para cima
				this.parent.upArrow();
		        break;
		    case 40: //Seta para baixo
				this.parent.downArrow();
				break;
		}
	}
	
	/**
	 * Evento disparado pelo onKeydown das caixas de texto dos filtros
	 * @param event: evento
	 * @param index integer: número do filtro
	 */
	this.inputValueKeyDown = function(event,index) {
		//Enter
		if (event.keyCode != 13) {
			this.parent.keyDown();
		}
	}

	/**
	 * Desenha/Redesenha os botões (+) e (-) dos filtros
	 */
	this.drawFilterControls = function() {
		//Conta os filtros visíveis
		visibleFilters = 0;
		for (i=0; i < this.filter.length; i++) {
			if (this.filter[i].visible) {
				visibleFilters++;
			}
		}
		
		// laço que corre todos os filtros
		for (i=0; i < this.filter.length; i++) {
		
			// coloca o botão apenas se o filtro é visível
			if (this.filter[i].visible) {
				// coloca o botão (+)
				filterIndex = i+1;
				filterControl  = '&nbsp;<a href="javascript:'+this.objName+'.cmdAddFilter('+filterIndex+')">';
				filterControl += '<img src="'+this.prumoWebPath+'/images/add.png" alt="add" />';
				filterControl += '</a>\n';
				
				// se possui mais de um filtro visível coloca o botão (-)
				if (visibleFilters > 1) {
					filterControl += '<a href="javascript:'+this.objName+'.cmdRemoveFilter('+i+')">';
					filterControl += '<img src="'+this.prumoWebPath+'/images/remove.png" alt="del" />';
					filterControl += '</a>\n';
				}
				
				document.getElementById(this.objName+'_'+i+'_controls').innerHTML = filterControl;
			}
		}
	}
	
	/**
	 * Limpa os valores de todos os filtros visíveis
	 */
	this.clearValues = function() {
		for (i=0; i < this.filter.length; i++) {
			if (this.filter[i].visible) {
				this.filter[i].value  = '';
				this.filter[i].value2 = '';
			}
		}
	}
	
	/**
	 * Configura os filtros, passando as propriedades do objeto para a interface
	 */
	this.configureFilter = function(fieldName, filterIndex) {
		if (this.filter[filterIndex].visible) {
			var selectFieldName = document.getElementById(this.objName+'_'+filterIndex+'_field');
			var selectOperator  = document.getElementById(this.objName+'_'+filterIndex+'_operator');
			var inputValue      = document.getElementById(this.objName+'_'+filterIndex+'_value');
			var inputValue2     = document.getElementById(this.objName+'_'+filterIndex+'_value2');
			var operator        = this.filter[filterIndex].operator;

			// configura selectFilter e o inputValue com o valor anteriormente passado via XML
			if (this.filter[filterIndex].fieldName == '') {
				selectFieldName.value = this.filter[0].fieldName;
			}
			else {
				selectFieldName.value = this.filter[filterIndex].fieldName;
			}
			inputValue.value = this.filter[filterIndex].value;
			inputValue2.value = this.filter[filterIndex].value2;

			// preenche o combo selectOperator
			this.selectFieldChange(selectFieldName, filterIndex);

			// configura o selectOperator
			var operatorType = this.operatorTypeByName(this.filter[filterIndex].fieldName);
			if (this.filter[filterIndex].operator == '') {
				switch (operatorType) {
					case 'text':
						arrOperators = this.textOperators;
						break;
					case 'numeric':
						arrOperators = this.numericOperators;
						break;
				default:
					arrOperators = this.textOperators;
				}
				this.filter[filterIndex].operator = arrOperators[0];
			}
			selectOperator.value = operator;
			this.selectOperatorChange(selectOperator, filterIndex);
			if (operator != '') {
				this.filter[filterIndex].operator = operator;
			}
		}
	}
	
	/**
	 * Desenha/Redesenha a interface (apenas a parte do prumoFilter)
	 */
	this.draw = function() {
		htmlFilters = '<table cellpadding="0" cellspacing="0">\n';
		for (i=0; i< this.filter.length; i++) {
			if (this.filter[i].visible) {
				htmlFilters += '	<tr>\n';
				htmlFilters += '		<td>\n';
				htmlFilters += '			<select id="'+this.objName+'_'+i+'_field" onchange="'+this.objName+'.selectFieldChange(this,'+i+')">\n';
				for (j=0; j< this.fieldName.length; j++) {
					htmlFilters += '				<option value="'+this.fieldName[j]+'">'+this.fieldLabel[j]+'</option>\n';
				}
				htmlFilters += '			</select>&nbsp;\n';
				htmlFilters += '		</td>\n';
				htmlFilters += '		<td>\n';
				htmlFilters += '			<select id="'+this.objName+'_'+i+'_operator" onchange="'+this.objName+'.selectOperatorChange(this,'+i+')"></select>&nbsp;\n';
				htmlFilters += '		</td>\n';
				htmlFilters += '		<td>\n';
				htmlFilters += '			<span id="'+this.objName+'_'+i+'_input">\n';
				htmlFilters += '				<input type="text" id="'+this.objName+'_'+i+'_value" size="15" onchange="'+this.objName+'.inputValueChange(this,'+i+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+i+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+i+')" />&nbsp;\n';
				htmlFilters += '			</span>\n';
				htmlFilters += '		</td>\n';
				htmlFilters += '		<td>\n';
				htmlFilters += '			<span id="'+this.objName+'_'+i+'_input2">\n';
				htmlFilters += '				&nbsp;&nbsp;e&nbsp;\n';
				htmlFilters += '				<input type="text" id="'+this.objName+'_'+i+'_value2" size="15" onchange="'+this.objName+'.input2ValueChange(this,'+i+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+i+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+i+')" />&nbsp;\n';
				htmlFilters += '			</span>\n';
				htmlFilters += '		</td>\n';
				htmlFilters += '		<td id="'+this.objName+'_'+i+'_controls">\n';
				htmlFilters += '			<br/>\n';
				htmlFilters += '		</td>\n';
				htmlFilters += '	</tr>\n';
			}
		}
		htmlFilters += '</table>\n';
		
		document.getElementById(this.objName+'_filters').innerHTML = htmlFilters;
		
		// chama o metodo configure filter para passar os valores do objeto para a interface
		for (ii = 0; ii < this.filter.length; ii++) {
			this.configureFilter(this.filter[ii].fieldName,ii);
		}
		
		// redesenha os botões (+) e (-)
		this.drawFilterControls();
	}
	
	/**
	 * Pega um valor na tabela que foi retornada via XML
	 *
	 * @param fieldName string: nome do campo
	 * @param index integer: número da linha
	 * @returns string: valor do campo
	 */
	this.getValue = function(fieldName,index) {
		var value = this.xmlData[index].getElementsByTagName(fieldName)[0].childNodes[0].nodeValue;
		if (value == 'NULL') {
			value = '';
		}
		return value;
	}
	
	/**
	 * Evento disparado após o assignResponseXML para ser implementado conforme necessidade do desenvolvedor da aplicação
	 */
	this.afterAssignResponseXML = function() {
		// implementar conforme necessidade
	}
	
	/**
	 * Configura as propriedades do objeto prumoFilter de acordo com o resultado XML passado como parâmetro
	 *
	 * @param responseXML: resultado xml
	 */
	this.assignResponseXML = function(responseXML) {
		this.xmlData = responseXML.getElementsByTagName(this.xmlIdentification);
		
		// limpa os filtros
		this.filter = new Array()
		
		// laço que percorre o xml
		for (var i=0; i < this.xmlData.length; i++) {
			fieldName    = this.getValue('fieldName', i);
			operator     = this.getValue('operator', i);
			value        = this.getValue('value', i);
			value2       = this.getValue('value2', i);
			visible      = this.getValue('visible', i);
			if (visible == 'false') {
				visible = false;
			}
			else {
				visible = true;
			}
			
			this.addFilter(i, fieldName, operator, value, value2, visible);
		}
		// redesenha
		this.draw();
		
		this.afterAssignResponseXML();
	}
	
	/**
	 * Seta o valor para o primeiro filtro que encontrar com o id passado, caso não encontre cria um filtro
	 */
	this.privateSetFilter = function(fieldName, filterOperator, fieldValue, fieldValue2, visible) {
		
		// Procura um campo no filtro visivel com o mesmo fieldName
		var nothing = true;
		for (var iFilter in this.filter) {
			if (this.filter[iFilter].fieldName == fieldName && this.filter[iFilter].visible == visible) {
				this.filter[iFilter].value = fieldValue;
				this.filter[iFilter].value2 = fieldValue2;
				nothing = false;
				break;
			}
		}
		
		// caso não encontrou nenhum filtro, cria
		if (nothing) {
			this.addFilter(null, fieldName, filterOperator, fieldValue, fieldValue2, visible);
		}
	}
	
	/**
	 * Seta o valor para o primeiro filtro que encontrar com o id passado, caso não encontre cria um filtro
	 */
	this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.privateSetFilter(fieldName, filterOperator, fieldValue, fieldValue2, true);
	}
	
	/**
	 * Seta o valor para o primeiro filtro que encontrar com o id passado, caso não encontre cria um filtro
	 */
	this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.privateSetFilter(fieldName, filterOperator, fieldValue, fieldValue2, false);
	}
}

function prumoValidator(validator) {
	this.validator = validator;
	
	this.validate = function(value) {
		switch (validator.type) {
		case 'max':
			if (parseFloat(value) > parseFloat(validator.value)) {
				return 'Valor não pode ser maior que ' + validator.value;
			}
			break;
		case 'min':
			if (parseFloat(value) < parseFloat(validator.value)) {
				return 'Valor não pode ser menor que ' + validator.value;
			}
			break;
		default:
			break;
		}
		return true;
	}
}

function prumoGrid(objName) {
	
	this.xmlIdentification;
	this.xmlData;
	this.objName = objName;
	this.lines;
	this.lineEventOnData = '';
	this.pointerCursorOnData = false;
	this.selectedLine = 0;
	
	this.field;
	this.fieldType;
	this.fieldVisible;
	
	this.table = document.getElementById(objName);
	
	for (i=1; i <= this.table.rows.length -1; i++) {
		this.table.rows[i].setAttribute('onmouseover', objName+'.onmouseover('+i+')');
	}
	
	this.onmouseover = function(i) {
		this.selectedLine = i;
		if (i != 0) {
			this.table.rows[i].setAttribute('class', 'prumoGridTrSelected');
		}
		
		for (j=1; j <= this.table.rows.length -1; j++) {
			if (j != i) {
				if (j % 2 != 0) {
					this.table.rows[j].setAttribute('class', 'prumoGridTrEven');
				}
				else {
					this.table.rows[j].setAttribute('class', 'prumoGridTrOdd');
				}				
			}
		}
	}
	
	this.clear = function() {
		
		pGrid = document.getElementById(this.objName);
		
		for (i=1; i < pGrid.rows.length; i++) {
			
			pGridRow = pGrid.rows[i];
			for (j=0; j < pGridRow.cells.length; j++) {
				pGridRow.cells[j].style.cursor = 'default';
				pGridRow.cells[j].removeAttribute('onClick');
				pGridRow.cells[j].innerHTML = '<br />';
			}
		}
	}
	
	this.assignResponseXML = function(responseXML) {
		
		this.onmouseover(0);
		this.xmlData = responseXML.getElementsByTagName(this.xmlIdentification);
		
		//limpa o grid
		this.clear();
		
		//laço que percorre o xml
		for (i=0; i < this.xmlData.length; i++) {
			
			// faz referencia a celua do grid
			var pGridRowCells = document.getElementById(this.objName).rows[i+1].cells;
			
			var iColumn = 0;
			for (j=0; j < this.field.length; j++) {
				
				var valueCell = format(this.fieldType[j], this.getValue(this.field[j], i), 'html');
				
				if (this.fieldVisible[j] == true) {
					
					//coloca o cursor pointer
					if (this.pointerCursorOnData) {
						pGridRowCells[iColumn].style.cursor = 'pointer';
					}
					
					//coloca o evento click na celula do grid
					if (this.lineEventOnData != '') {
						pGridRowCells[iColumn].setAttribute('onClick', this.lineEventOnData.replace('%', i));
					}
					
					pGridRowCells[iColumn].innerHTML = valueCell;
					
					iColumn++;
				}
			}
		}
	}
	
	this.getValue = function(fieldName, index) {
		
		var value = this.xmlData[index].getElementsByTagName(fieldName)[0].childNodes[0].nodeValue;
		
		if (value == 'NULL') {
			value = '';
		}
		
		return value;
	}
}

function prumoGridNavigation(objName) {
	this.objName = objName;
	this.xmlIdentification = 'pGridStatus';
	this.count;
	this.pageLines;
	this.page;
	this.maxPages = 10;

	//recalc
	this.registersFrom;
	this.registersTo;
	this.pages;
	
	this.gridNavigation = document.getElementById('pGridNavigation_'+objName);
	
	this.clear = function() {
		this.gridNavigation.innerHTML = gettext('Carregando')+'...';
	}
	
	this.recalc = function() {
		this.registersFrom = (this.page - 1) * this.pageLines + 1;
		this.registersTo = this.registersFrom*1 + this.pageLines*1-1;
		if (this.registersTo > this.count) {
			this.registersTo = this.count;
		}
		this.pages = Math.ceil(this.count / this.pageLines);
	}
	
	this.redraw = function() {
		this.recalc();
		htmlOut = '';		
		
		// calculos
		var bars = Math.ceil(this.pages / this.maxPages);
		var thisBar = Math.ceil(this.page / this.maxPages);
		var pageFrom = (thisBar -1) * this.maxPages + 1;
		var pageTo = pageFrom + this.maxPages - 1;
		
		//quando nenhum registro encontrado
		if (this.registersTo == 0) {
			this.registersFrom = 0;
			this.page = 0;
		}
		
		// quando não há paginas suficientes para completar a barra
		if (pageTo > this.pages) {
			pageTo = this.pages;
		}
		
		// botão primeira página
		if (thisBar > 2) {
			htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.objName+'.goSearch(1)">1...</button>';
		}
		
		// botão pagina anterior
		if (thisBar > 1) {
			var lastPage = pageFrom - 1;
			htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.objName+'.goSearch('+lastPage+')">&lt;</button>';
		}
		
		// Laço que cria os botões da barra
		for(i = 0 ; i < pageTo - pageFrom +1 ; i++) {
			var iPage = pageFrom + i;
			if (iPage == this.page) {
				htmlOut += '<button class="pButton-outline prumoPagination" disabled="disabled" onclick="'+this.objName+'.goSearch('+iPage+')">'+iPage+'</button>';
			}
			else {
				htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.objName+'.goSearch('+iPage+')">'+iPage+'</button>';
			}
		}
		
		// botão próxima pagina
		if (iPage != this.pages && this.page != 0) {
			var nextPage = iPage + 1;
			htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.objName+'.goSearch('+nextPage+')">&gt;</button>';
		}
		
		// botão última página
		if (bars - thisBar > 1) {
			htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.objName+'.goSearch('+this.pages+')">...'+this.pages+'</button>';
		}

		// monta a barra de status
		htmlOut += '<br />';
		htmlOut += gettext('Registros encontrados')+': '+this.count+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		htmlOut += gettext('Mostrando de')+' '+this.registersFrom+' '+gettext('até')+' '+this.registersTo+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		htmlOut += gettext('Página')+' '+this.page+' '+gettext('de')+' '+this.pages;
		htmlOut += '<br />';
	
		this.gridNavigation.innerHTML = htmlOut;
	}
	
	/**
	 * Evento disparado após o assignResponseXML para ser implementado conforme necessidade do desenvolvedor da aplicação
	 */
	this.afterAssignResponseXML = function() {
		// implementar conforme necessidade
	}
	
	this.assignResponseXML = function(responseXML) {
		var x=responseXML.getElementsByTagName(this.xmlIdentification);
		this.clear();
		this.count     = x[0].getElementsByTagName('count')[0].childNodes[0].nodeValue;
		this.pageLines = x[0].getElementsByTagName('pageLines')[0].childNodes[0].nodeValue;
		this.page      = x[0].getElementsByTagName('page')[0].childNodes[0].nodeValue;

		this.redraw();
		
		this.afterAssignResponseXML();
	}
}

function prumoLoading(divName) {
	this.divName = divName;
	this.count = 0;

	this.show = function(loadId) {
		this.count++;		
		this.visible();
	}
	
	this.hide = function(loadId) {
		if (this.count > 0) {
			this.count--;
		}
		this.visible();
	}
	
	this.visible = function() {
		if (this.count == 0) {
			document.getElementById(this.divName).style.display = 'none';
		}
		else {
			document.getElementById(this.divName).style.display = 'block';
		}
	}
	
	this.visible();
}

function prumoMenu(objName) {
	this.objName = objName;
	
	this.open = function(id) {
		subMenu = document.getElementById(this.objName+'_'+id);
		subMenu.style.display = 'block';
	}
	
	this.close = function(id) {
		subMenu = document.getElementById(this.objName+'_'+id);
		subMenu.style.display = 'none';
	}
	
	this.onClick = function(id,link,element) {
		if (link == '') {
			subMenu = document.getElementById(this.objName+'_'+id);
			if (subMenu.style.display == 'none' || subMenu.style.display == undefined) {
				this.open(id);
			}
			else {
				this.close(id);
			}
		}
		else {
			if (element.ctrlKey) {
				window.open(link);
			}
			else {
				location.href = link;
			}
		}
	}
	
	this.onInput = function() {
		var inputMenuValue = document.getElementById('prumo_input_menu').value;
		var dl = document.getElementById('prumo_list_menu');
		for (i=0; i < dl.options.length; i++) {
		    if (inputMenuValue == dl.options[i].value) {
		    	location.href = 'index.php?page='+dl.options[i].getAttribute('routine');
		    }
		}
	}
	
	this.showInputMenu = function() {
		pWindowsImputMenu.show(1);
		document.getElementById('prumo_input_menu').focus();
	}
	
	this.imputMenuKeyDown = function(event) {
		if (event.keyCode == 27) { //ESC
			pWindowsImputMenu.hide();
		}
	}
}

function prumoQueue(objName,ajaxFile) {
    prumoSearch.apply(this, arguments);
	
	this.pAjax.ajaxXmlOk = function() {
		this.parent.pGrid.assignResponseXML(this.responseXML);
		this.parent.pGridNavigation.assignResponseXML(this.responseXML);
		this.parent.pFilter.assignResponseXML(this.responseXML);
		document.getElementById(this.parent.objName+'_btSearch').removeAttribute('disabled');
		document.getElementById(this.parent.objName+'_btSearchAll').removeAttribute('disabled');
		this.parent.afterList();
	}
	
	this.lineClick = function(lineIndex) {
		var msg = gettext('Falta implementar o método lineClick do objeto ')+this.objName;
		alert(msg);
	}
	
	this.afterList = function() {
		//disponivel para implementação
	}
	
}

function prumoSearch(objName,ajaxFile) {
	this.objName = objName;
	this.identification = objName;
	this.page;
	this.orderBy;
	
	this.crudName;

	this.pAjax;
	this.pWindow;
	this.pFilter;
	this.pGrid;
	this.pGridNavigation;
	
	this.fieldName;
	this.fieldPk;

	this.fieldValueOnFocus;
	this.fieldFocusId = '';
	
	this.autoClick = true;
	
	this.fieldReturn = Array();
	
	this.selected = false;
	
	this.lineIndex;

	this.modal = true;
	this.pAjax = new prumoAjax(ajaxFile);
	this.pAjax.ajaxFormat = 'xml';
	this.pAjax.parent = this;
	this.pAjax.identification = this.identification;
	this.pAjax.ajaxXmlOk = function() {
		if (this.cmd == 'r') {
			this.parent.pGrid.assignResponseXML(this.responseXML);
			this.parent.lineClick(0);
			this.parent.afterRetrieve();
		}
		else {
			this.parent.pGrid.assignResponseXML(this.responseXML);
			this.parent.pGridNavigation.assignResponseXML(this.responseXML);
			this.parent.pFilter.assignResponseXML(this.responseXML);
			if (this.parent.autoClick == true && this.parent.pGridNavigation.count == 1 && this.parent.pFilter.count > 0) {
				this.parent.lineClick(0);
			}
		}
		document.getElementById(this.parent.objName+'_btSearch').removeAttribute('disabled');
		document.getElementById(this.parent.objName+'_btSearchAll').removeAttribute('disabled');
		
		this.parent.afterList();
	}
	
	this.afterSearch = function() {
		//implementar conforme necessidade
	}
	
	this.afterRetrieve = function() {
		//implementar conforme necessidade
	}
	
	this.afterList = function() {
		//implementar conforme necessidade
	}
	
	this.parametersFilters = function() {
		param = '';
		for (i=0; i < this.pFilter.filter.length; i++) {
			param += '&fField[]='+this.pFilter.filter[i].fieldName;
			param += '&fOperator[]='+this.pFilter.filter[i].operator;
			param += '&fValue[]='+this.pFilter.filter[i].value;
			param += '&fValue2[]='+this.pFilter.filter[i].value2;
			param += '&fVisible[]='+this.pFilter.filter[i].visible;
		}
		return param;
	}
	
	this.parameters = function() {
		if (this.page == undefined) {
			this.page = 1;
		}
		
		if (this.crudName != undefined) {
			var param = 'objName='+this.crudName;
		}
		else {
			var param = 'objName='+this.objName;
		}
		param += '&'+this.objName+'_action=makeXml';
		param += '&page='+this.page;
		if (this.orderBy != undefined) {
			param += '&orderBy='+ this.orderBy;
		}
		param += this.parametersFilters();
		return param;
	}
	
	this.lineClick = function(lineIndex) {
		this.lineIndex = lineIndex;
		for (i=0; i < this.fieldReturn.length; i++) {
			var value = this.pGrid.getValue(this.fieldReturn[i][0],lineIndex);
			var fieldReturn = document.getElementById(this.fieldReturn[i][1]);
			var noRetrieve = this.fieldReturn[i][3];
			if (noRetrieve == false || this.pAjax.cmd != 'r') {
				if (fieldReturn != undefined) {
					var type = this.fieldReturn[i][2];
					if (type == 'date') {
						fieldReturn.value = format(type, value, 'text');
						if (this.pAjax.cmd == 'r') {
							fieldReturn.setAttribute('title', format(type, value, 'text'));
						}
					}
					else {
						if (fieldReturn.getAttribute('type') == 'checkbox') {
							if (value == 't') {
								fieldReturn.checked = true;
							}
							else {
								fieldReturn.checked = false;
							}
						}
						else {
							fieldReturn.value = format(type, value, 'text');
							if (this.pAjax.cmd == 'r') {
								fieldReturn.setAttribute('title',format(type, value, 'text'));
							}
						}
					}
				}
				else {
					var msg = gettext('Campo "%fieldName%" não encontrado, verifique a chamada "addFieldReturn" do objeto "%objName%"');
					msg = msg.replace('%fieldName%',this.fieldReturn[i][1]);
					msg = msg.replace('%objName%',this.objName);
					alert(msg);
				}
			}
		}
		this.hide();
		
		var lastReturn = document.getElementById(this.fieldReturn[this.fieldReturn.length -1][1]);
		lastReturn.focus();
		
		this.afterSearch();
	}
	
	this.beforeSearch = function() {
		//implementar conforme necessidade
		return true;
	}
	
	this.goSearch = function(page) {
		if (this.pAjax.working) {
			alert(this.objName + ': ' + gettext('já está trabalhando'));
		}
		else {
			this.page = page;
			if (this.beforeSearch()) {
				this.selected = false;
				if (page == undefined) {
					this.pFilter.clearValues();
					this.pGrid.clear();
					this.pGridNavigation.clear();
				}
				this.show();
				this.pAjax.cmd = 'search';
				document.getElementById(this.objName+'_btSearch').setAttribute('disabled','disabled');
				document.getElementById(this.objName+'_btSearchAll').setAttribute('disabled','disabled');
				this.pAjax.goAjax(this.parameters());
			}
		}
	}
	
	this.paramRetrieve = function() {
		var param = 'objName='+this.objName+'&'+this.objName+'_action=r';
		
		for (i in this.fieldPk) {
			if (this.fieldPk[i] == true) {
				var idReturn = '';
				for (j in this.fieldReturn) {
					if (this.fieldName[i] == this.fieldReturn[j][0]) {
						idReturn = this.fieldReturn[j][1];
					}
				}
				if (document.getElementById(idReturn) == undefined) {
					var msg = 'Erro: %objName% - id "%id%" não encontrado!';
					msg = msg.replace('%objName%', this.objName);
					msg = msg.replace('%id%', idReturn);
					alert(msg);
				}
				param += '&'+this.fieldName[i]+'='+document.getElementById(idReturn).value;
			}
		}
		
		return param;
	}
	
	this.goRetrieve = function() {
		
		var havePk = false;
		var pkNull = false;
		for (i in this.fieldPk) {
			if (this.fieldPk[i] == true) {
				havePk = true;
				var idReturn = '';
				for (j in this.fieldReturn) {
					if (this.fieldName[i] == this.fieldReturn[j][0]) {
						idReturn = this.fieldReturn[j][1];
						if (document.getElementById(idReturn).value == '') {
							pkNull = true;
						}
					}
				}
			}
		}
		
		if (havePk && !pkNull) {
			this.pAjax.cmd = 'r';
			this.pAjax.goAjax(this.paramRetrieve());
		}
	}
	
	this.fieldKeyDown = function(event) {
		if (event.keyCode == 113) { //F2
			this.goSearch();
		}
		if (event.keyCode == 13) { //ENTER
			this.fieldBlur(document.getElementById(this.fieldFocusId));
		}
	}
	
	this.cmdSearch = function() {
		//if (this.pFilter.validateFilters()) {
			this.goSearch(1);
		//}
	}
	
	this.cmdSearchAll = function() {
		this.pFilter.clearValues();
		this.cmdSearch();
		this.pFilter.draw();
	}
	
	this.addFieldReturn = function(fieldName, idReturn, fieldType, noRetrieve) {
		this.fieldReturn[this.fieldReturn.length] = Array(fieldName, idReturn, fieldType, noRetrieve);
	}
	
	this.afterShow = function() {
		//
	}
	
	this.show = function() {
		if (this.pWindow != undefined) {
			this.pWindow.show(this.modal);
		}
		this.afterShow();
	}
	
	this.cancel = function() {
		if (this.fieldFocusId != '') {
			inputField = document.getElementById(this.fieldFocusId);
			// Caso o usuário tenha digitado algum valor no campo
			if (this.fieldValueOnFocus != inputField.value) {
				for (i in this.fieldReturn) {			
					document.getElementById(this.fieldReturn[i][1]).value = "";
				}
			}
			
			inputField.focus();
		}
		this.hide();
	}
	
	this.afterHide = function() {
		//
	}
	
	this.hide = function() {
		this.pWindow.hide();
		this.afterHide();
	}
	
	this.upArrow = function() {
		this.selected = true;
		if (this.pGrid.selectedLine == 0) {
			this.pGrid.selectedLine = this.pGrid.lines + 1;
		}
		
		var nextLine = this.pGrid.selectedLine - 1;
		
		if (this.pGrid.selectedLine > 1) {
			this.pGrid.selectedLine = nextLine;
			this.pGrid.onmouseover(this.pGrid.selectedLine);
		}
		
		if (nextLine == 0) {
			if (this.pGridNavigation.page > 1) {
				this.goSearch(this.pGridNavigation.page * 1 - 1);
			}
		}
	}
	
	this.downArrow = function() {
		this.selected = true;
		var nextLine = this.pGrid.selectedLine + 1;
		if (this.pGrid.selectedLine < this.pGrid.lines) {
			this.pGrid.selectedLine = nextLine;
			this.pGrid.onmouseover(this.pGrid.selectedLine);
		}
		
		if (nextLine == this.pGrid.lines + 1) {
			if (this.pGridNavigation.page < this.pGridNavigation.pages) {
				this.goSearch(this.pGridNavigation.page * 1 + 1);
			}
		}
	}
	
	this.enterKey = function() {
		if (this.selected) {
			this.lineClick(this.pGrid.selectedLine-1);
		}
		else {
			this.cmdSearch();
		}
	}
	
	this.keyDown = function() {
		this.selected = false;
	}
	
	/**
	 * Grava o valor em this.fieldValueOnFocus ao receber o foco para posteriormente ser comparado no evento blur
	 */
	this.fieldFocus = function(objField) {
		this.fieldValueOnFocus = objField.value;
		this.fieldFocusId = objField.getAttribute('id');
	}
	
	/**
	 * Verifica se o usuário digitou ou alterou alguma informação no field
	 */
	this.fieldBlur = function(objField) {
		
		if (this.pAjax.working == false) {
			var objId = objField.getAttribute('id');
			
			for (iFieldReturn in this.fieldReturn) {
				
				if (this.fieldReturn[iFieldReturn][1] == objId) {
					//Compara o valor do campo ao entrar e ao sair se houve alteração, e dispara o search
					if (this.fieldValueOnFocus != objField.value) {
						
						if (objField.value == '') {
							for (iReturn in this.fieldReturn) {
								document.getElementById(this.fieldReturn[iReturn][1]).value = '';
							}
						}
						else {
							
							for (iFieldSearch in this.pFilter.fieldName) {
								if (this.pFilter.fieldName[iFieldSearch] == this.fieldReturn[iFieldReturn][0]) {
									var fieldType = this.pFilter.fieldType[iFieldSearch];
								}
							}
							
							if (fieldType == 'string') {
								var operator = 'like';
							}
							else {
								var operator = 'equal';
							}
							
							this.pFilter.clearValues();
							this.pFilter.setFilter(this.fieldReturn[iFieldReturn][0], operator, objField.value, '');
							this.goSearch(1);
						}
					}
				}
			}
		}
	}
	
	this.sort = function(field, order) {
		if (order == undefined) {
			var element = document.getElementById('prumoGridTh_' + this.objName + '_' + field);
			var className = element.className;
			var arr = className.match(/sort(.*)/g) || [''];
			switch (arr[0]) {
			case '':
				element.setAttribute('class', 'prumoGridTh sortAsc');
				order = 'asc';
				break;
			case 'sortAsc':
				element.className = 'prumoGridTh sortDesc';
				order = 'desc';
				break;
			case 'sortDesc':
				element.className = 'prumoGridTh sortAsc';
				order = 'asc';
				break;
			default:
				element.className = 'prumoGridTh sortAsc';
				order = 'asc';
			}
			var elements = this.pGrid.field;
			for (i in elements) {
				element = document.getElementById('prumoGridTh_' + this.objName + '_' + elements[i]);
				if (elements[i] != field && this.pGrid.fieldVisible[i] == true) {
					element.setAttribute('class', 'prumoGridTh');	
				}
			}
		}
		this.orderBy = field + ' ' + order;
		this.goSearch(this.page);
	}
	
	/**
	 * Redirecionamento para o mesmo método em this.pFilter
	 */
	this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
	}
	
	/**
	 * Redirecionamento para o mesmo método em this.pFilter
	 */
	this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2) {
		this.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
	}
}

function prumoTab(objName) {
	this.objName = objName;		
	this.tabName = new Array();

	this.addTab = function(tabName) {
		this.tabName.push(tabName);
	}

	this.show = function() {
		document.getElementById(this.objName).style.display = 'block';
	}

	this.hide = function() {
		document.getElementById(this.objName).style.display = 'none';
	}

	this.showTab = function(tabName) {
		this.show();
		for (i in this.tabName) {
			if (this.tabName[i] == tabName) {
				document.getElementById(this.objName+'_tab_'+this.tabName[i]).style.display = 'block';
				document.getElementById(this.objName+'_bt_'+this.tabName[i]).className= "pButton-outline active";
			}
			else {
				document.getElementById(this.objName+'_tab_'+this.tabName[i]).style.display = 'none';
				document.getElementById(this.objName+'_bt_'+this.tabName[i]).className= "pButton-outline";
			}
		}
	}
}

function prumoWindow(objName) {
	this.objName = objName;
	this.width   = 730;
	this.title   = 'prumoWindow';
	this.align   = 'center';
	this.vAlign  = 'top';
	
	this.div = document.getElementById(objName);
	
	this.show = function(modal) {
		document.getElementById(this.objName+'_title').innerHTML = this.title;
		
		if (modal) {
			document.getElementById(this.objName+'_veil').style.display = 'block';
		}
		this.div.style.display = 'block';
		this.position();
	}
  
	this.hide = function() {
		document.getElementById(this.objName+'_veil').style.display = 'none';
		this.div.style.display = 'none';
	}
  
	this.move = function() {
		thisDiv = document.getElementById(this.objName)
		diffX = 0;
		diffY = 0;
		
		document.getElementById(this.objName+'_title').style.cursor = 'move';
		
		document.onmousemove = function Mouse(event){
			if (diffX == 0) {
				diffX = event.clientX - thisDiv.offsetLeft;
			}
			if (diffY == 0) {
				diffY = event.clientY - thisDiv.offsetTop;
			}

			x = event.clientX - diffX;
			y = event.clientY - diffY;

			thisDiv.style.left = x + "px";
			thisDiv.style.top  = y + "px";			
		}
		document.onclick = null;
	}
	
	this.dropMove = function() {
		document.getElementById(this.objName+'_title').style.cursor = '';
		document.onmousemove = false;
	}
  
	this.position = function() {
		var scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
		var topPosition = 0;
		var leftPosition = 0;
		
		this.div.style.width = this.width + "px";
		
		if (this.vAlign == 'middle') {
			topPosition = (document.body.offsetHeight/2)-(this.div.offsetHeight/2);
			if (topPosition < scrollTop) {
				topPosition = scrollTop + 1;
			}
		}
		
		if (this.vAlign == 'top') {
			
			var topPosition = scrollTop + 20;
			
			if (topPosition < 0) {
				topPosition = 1;
			}
		}
		
		this.div.style.top = topPosition + "px";
		this.top = topPosition;

		if (this.align == 'center') {
			leftPosition = (document.body.clientWidth/2)-(this.width/2);
			if (leftPosition < 0) {
				leftPosition = 1;
			}
		}
		if (this.align == 'left') {
			leftPosition = 1;
		}
		if (this.align == 'rigth') {
			leftPosition = document.body.clientWidth - this.width -5;
		}
		this.div.style.left = leftPosition + "px";
	}
}

