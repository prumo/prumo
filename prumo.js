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

var jPrumo = new prumo();
function prumo()
{
    this.button = function(type, elementId)
    {
        switch (type) {
        case 'upload':
            this.uploadButton(elementId);
            break;
        default:
            throw 'unsupported type';
            break;
        }
    }
    
    this.uploadButton = function(elementId)
    {
        var inputs = [];
        if (typeof(elementId) != "undefined") {
            var input = document.getElementById(elementId);
            inputs[0] = input;
        } else {
            inputs = document.querySelectorAll('input[type=file]');
        }
        Array.prototype.forEach.call(inputs, function(input) {
            var html = '<label for="' + input.id + '">'
                     + '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg>'
                     + '<span>Escolher arquivo&hellip;</span></label>';
            input.insertAdjacentHTML('afterend', html);
            input.classList.add('inputFile');
            var label = input.nextElementSibling;
            var labelVal = label.innerHTML;
            
            input.addEventListener('change', function(e) {
                var fileName = '';
                if (this.files && this.files.length > 1) {
                    fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
                } else {
                    fileName = e.target.value.split('\\').pop();
                }
        
                if (fileName) {
                    label.querySelector('span').innerHTML = fileName;
                } else {
                    label.innerHTML = labelVal;
                }
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
function pSimpleAjax(file, param, result)
{
    var pAjax = new prumoAjax(file);
    
    if (result == 'eval') {
        pAjax.process = function()
        {
            eval(this.responseText);
        }
    } else {
        document.getElementById(result).innerHTML = '';
        
        var img = document.createElement("IMG");
        img.setAttribute('src', 'prumo/images/loading.gif');
        img.setAttribute('alt', 'carregando...');
        document.getElementById(result).appendChild(img);
        
        pAjax.process = function()
        {
            document.getElementById(result).innerHTML = this.responseText;
        }
    }
    pAjax.goAjax(param);
}

/**
 * Valida a data
 *
 * @param value string: valor a ser validado
 *
 * @return boolean
 */
function isDate(value)
{
    if (value.split("/").length - 1 > 0) {
        var arrDate = value.split("/");
        var day = arrDate[0];
        var month = arrDate[1];
        var year = arrDate[2];
    } else if (value.split("-").length - 1 > 0) {
        var arrDate = value.split("-");
        var year = arrDate[0];
        var month = arrDate[1];
        var day = arrDate[2];
    } else {
        return false;
    }
    
    if (((month == 1) || (month == 3) || (month == 5) || (month == 7) || (month == 8) || (month == 10) || (month == 12)) && (day < 1 || day > 31)) {
        return false;
    } else if (((month == 4) || (month == 6) || (month == 9) || (month == 11)) && (day < 1 || day > 30)) {
        return false;
    } else if (month == 2) {
        if ((day > 28) && ((year%4) != 0)) {
            return false;
        }
        if ((day > 29) && ((year%4) == 0)) {
            return false;
        }
    }
    return true;
}

/**
 * Valida a hora
 *
 * @param value string: valor a ser validado
 *
 * @return boolean
 */
function isTime(value)
{
    var partValue = value.split(":");
    var s = '00';
    
    if (partValue.length == 2) {
        var h = partValue[0];
        var m = partValue[1];
    } else if (partValue.length == 3) {
        var h = partValue[0];
        var m = partValue[1];
        var s = partValue[2];
    } else {
        return false;
    }
    
    if (! isFinite(h)) {
        return false;
    }
    
    if (! isFinite(m)) {
        return false;
    }
    
    if (! isFinite(s)) {
        return false;
    }
    
    if (h > 23 || m > 59 || s > 59 || h < 0 || m < 0 || s < 0) {
        return false;
    }
    
    return true;
}

/**
 * Valida o formato de um valor
 *
 * @param value mixed: valor a ser validado
 * @param type string: tipo
 *
 * @return boolean
 */
function prumoIsType(value, type)
{
    var regexInteger = /^-?[0-9]*([+\-*/]?[0-9]){0,50}$/;
    var regexFloat = /^-?[0-9]*[.,]?[0-9]*([+\-*/]?[0-9]*[.,]?[0-9]){0,50}$/;
    
    var isValid = true;
    
    switch(type) {
        case 'serial':
            if (! regexInteger.test(value)) {
                isValid = false;
            }
            break;
        case 'integer':
            if (! regexInteger.test(value)) {
                isValid = false;
            }
            break;
        case 'numeric':
            if (! regexFloat.test(value)) {
                isValid = false;
            }
            break;
        case 'date':
            if (! isDate(value)) {
                isValid = false;
            }
            break;
        case 'time':
            if (! isTime(value)) {
                isValid = false;
            }
            break;
        case 'timestamp':
            let partValue = value.replace("T", " ").split(" ");
            
            if (partValue.length != 2) {
                isValid = false;
            } else if (! isDate(partValue[0])) {
                isValid = false;
            } else if (! isTime(partValue[1])) {
                isValid = false;
            }
            break;
        case 'boolean':
            if (value != 't' && value != 'f') {
                isValid = false;
            }
            break;
    }
    return isValid;
}

function gettext(str)
{
    return str;
}

/**
 * formata dados recebidos via XML
 *
 * @param type string: tipo de dado (timestamp, time, date, numeric, boolean, string)
 * @param value string: valor
 * @param textStyle string: html ou text
 */
function pFormat(type, value, textStyle)
{
    if (type == 'timestamp' && value != '') {
        var year = value.substring(0, 4);
        var month = value.substring(5, 7);
        var day = value.substring(8, 10);
        var hour = value.substring(11, 13);
        var minute = value.substring(14, 16);
        var second = value.substring(17, 19);
        var timestamp = value.substring(17, 19);
        if (textStyle == 'html') {
            var formatedValue = day + '/' + month + '/' + year + ' ' + hour + ':' + minute + ':' + second;
        } else {
            var formatedValue = year + '-' + month + '-' + day + 'T' + hour + ':' + minute + ':' + second;
        }
    } else if (type == 'time' && value != '' && textStyle == 'html') {
        var hour = value.substring(0, 2);
        var minute = value.substring(3, 5);
        var second = value.substring(6, 8)
        var formatedValue = hour + ':' + minute + ':' + second;
    } else if (type == 'date' && value != '' && textStyle == 'html') {
        var year = value.substring(0, 4);
        var month = value.substring(5, 7);
        var day = value.substring(8, 10);
        var formatedValue = day + '/' + month + '/' + year;
    } else if (type == 'numeric' && value != '') {
        var formatedValue = value.replace(',', '');
        formatedValue = formatedValue.replace('.', ',');
    } else if (type == 'boolean' && value != '' && textStyle == 'html') {
        if (value == 't') {
            var formatedValue = '<input type="checkbox" readonly="readonly" checked="checked" />';
        }
        else {
            var formatedValue = '<input type="checkbox" readonly="readonly" />';
        }
    } else {
        if (textStyle == 'html') {
            formatedValue = value.replace(/\\n/g, '<br />');
        } else {
            formatedValue = value;
        }
    }
    
    if (formatedValue == '//' || formatedValue == '//::') {
        formatedValue = '';
    }
    
    return formatedValue;
}

//////////////////////// classes ////////////////////

function classAjax()
{
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
function prumoAjax(ajaxFile, process)
{
    this.ajaxFile        = ajaxFile;
    this.ajaxFormat      = 'text';
    this.ajax            = new classAjax();
    this.pLoading        = pLoading;
    this.debug           = false;
    this.identification  = 'prumo';
    this.defaultParams   = '';
    this.xmlData;
    this.responseXML;
    this.responseText;
    this.working         = false;
    this.formName        = '';
    
    this.goAjax = function(params)
    {
        if (this.working == false) {
            this.pLoading.show(this);
        }
        this.working = true;
    
        this.ajax.open("POST", this.ajaxFile, true);
        this.ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        this.ajax.parent = this;
        this.ajax.onreadystatechange = function()
        {
            if (this.readyState == 1) {
                //state 1
            }
            if (this.readyState == 4) {                
                if (this.parent.debug) {
                    alert(this.responseText);
                }
                this.parent.responseText = this.responseText;
                if (this.parent.ajaxFormat == 'xml') {
                    this.parent.responseXML = this.responseXML;
                    if (this.parent.responseXML) {
                        this.parent.beforeProcess();
                        this.parent.pLoading.hide(this);
                    } else {
                        alert(gettext('Formato XML inválido para "')+this.parent.ajaxFile+'"');
                    }
                } else if (this.parent.ajaxFormat == 'text') {
                    this.parent.beforeProcess();
                    this.parent.pLoading.hide(this);
                } else {
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
            for (let i=0; i < inputs.length; i++) {
                var name = inputs[i].name != '' ? inputs[i].name : inputs[i].id;
                if ((inputs[i].type != 'checkbox') | inputs[i].type == 'checkbox' & inputs[i].checked == true) {
                    params += '&' + name + '=' + inputs[i].value;
                }
            }
            var textareas = form.getElementsByTagName('textarea');
            for (let i=0; i < textareas.length; i++) {
                var name = textareas[i].name != '' ? textareas[i].name : textareas[i].id;
                params += '&' + name + '=' + textareas[i].value;
            }
            var selects = form.getElementsByTagName('select');
            for (let i=0; i < selects.length; i++) {
                var name = selects[i].name != '' ? selects[i].name : selects[i].id;
                for (let j=0; j < selects[i].length; j++) {
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
    
    this.setFormName = function(formName)
    {
        if (document.getElementById(formName) !== null) {
            this.formName = formName;
        } else {
            alert('Error (setFormName): Elemento "' + formName + '" não encontrado!');
        }
    }
    
    this.beforeProcess = function()
    {
        if (this.ajaxFormat == 'xml') {
            this.xmlData = this.responseXML.getElementsByTagName(this.identification);
            this.process();            
        } else {
            this.process();
        }
    }
    
    this.ajaxXmlOk = function()
    {
        //para ajaxFormat xml, sobrescrever este método após o constructor
        alert('Implementar o método ajaxXmlOk');
    }
    
    this.ajaxXmlError = function(err, msg)
    {
        //para ajaxFormat xml, sobrescrever este método após o constructor
        alert(msg);
    }
    
    // para ajaxFormat texto, sobrescrever este método após o constructor
    if (process == undefined) {
        this.process = function()
        {
            if (this.ajaxFormat == 'xml') {
                try {
                    xmlTagErr = this.responseXML.getElementsByTagName('err')[0];
                    if (xmlTagErr == undefined) {
                        this.ajaxXmlOk();
                    } else {
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
    } else {
        this.process = process;
    }
}

document.pCrud = Array();
function PrumoCrud(objName, ajaxFile)
{
    this.objName = objName;
    this.modal;
    this.identification = objName;
    this.state = 'new'; //suport new, edit, view, list, copy
    
    this.fieldName;
    this.fieldPk;
    this.fieldId;
    this.fieldLabel;
    this.fieldType;
    this.fieldNotNull;
    
    this.fieldOldValue = Array();
    this.fieldNewValue = Array();
    this.fieldAutoClearValue = Array();
    this.sonSearch = Array();
    
    this.pAjax;
    this.pCrudList = false;
    this.pSearch;
    
    // para relação 1x1
    this.parent1x1 = false;
    this.parent1x1Condition = new Array();
    this.son1x1 = new Array();
    
    // para relação 1xN
    this.parent1xN = false;
    this.son1xN = new Array();
    
    this.isVisible = true;
    
    this.defaultParamCreate;
    
    this.initialState = '';
    
    this.pAjax = new prumoAjax(ajaxFile);
    this.pAjax.ajaxFormat = 'xml';
    this.pAjax.parent = this;
    this.pAjax.identification = this.identification;
    this.pAjax.ajaxXmlOk = function()
    {
        // copia o cmd para os filhos 1x1
        for (let i in this.parent.son1x1) {
            this.parent.son1x1[i].pAjax.cmd = this.cmd;
        }
        
        var status = this.responseXML.getElementsByTagName('status').length > 0 ? this.responseXML.getElementsByTagName('status')[0].firstChild.nodeValue : 'ok';
        if (status == 'err') {
            var msg = this.responseXML.getElementsByTagName('msg')[0].firstChild.nodeValue;
            alert(msg);
            if (this.parent.state == 'list' || this.parent.state == 'edit' || this.parent.state == 'new' || this.parent.state == 'copy') {
                this.parent.unFreezeFields();
                this.parent.toggleFreezeControls(false);
            }
        } else {
            
            if (this.cmd == 'create') {
                this.parent.assignResponseXML(this.responseXML);
                this.parent.stateChange('view');
                this.parent.visibleSon1x1();
                this.parent.retrieveVirtual();
                
                var controls = document.getElementById(this.parent.objName+'_control_view');
                for (let i in controls.childNodes) {
                    if (controls.childNodes[i].nodeType == 1) {
                        controls.childNodes[i].focus();
                        break;
                    }
                }
                
                this.parent.afterCreateRecursive();
                this.parent.afterCreate2Recursive();
            } else if (this.cmd == 'fast_create') {
                this.parent.afterCreateRecursive();
                this.parent.afterCreate2Recursive();
                this.parent.stateChange('list');
            } else if (this.cmd == 'retrieve') {
                this.parent.assignResponseXML(this.responseXML);
                this.parent.stateChange('view');
                this.parent.visibleSon1x1();
                this.parent.retrieveVirtual();
                this.parent.afterRetrieveRecursive();
                this.parent.afterRetrieve2Recursive();
                if (this.parent.initialState == 'edit') {
                    this.parent.bt_edit();
                    this.parent.initialState = '';
                }
            } else if (this.cmd == 'update') {
                this.parent.assignResponseXML(this.responseXML);
                this.parent.stateChange('view');
                this.parent.retrieveVirtual();
                this.parent.afterUpdateRecursive();
                this.parent.afterUpdate2Recursive();
            } else if (this.cmd == 'fast_update') {
                this.parent.afterUpdateRecursive();
                this.parent.afterUpdate2Recursive();
                this.parent.stateChange('list');
            } else if (this.cmd == 'delete') {
                this.parent.afterDeleteRecursive();
                this.parent.stateChange('new');
            } else if (this.cmd == 'fast_delete') {
                this.parent.afterDeleteRecursive();
                this.parent.stateChange('list');
            } else if (this.cmd == 'copyFrom') {
                this.parent.assignResponseXML(this.responseXML);
                this.parent.clearSerials();
                this.parent.writeNewValues();
                this.parent.retrieveVirtual();
                this.parent.stateChange('copy');
                this.parent.visibleSon1x1();
            } else {
                status = this.responseXML.getElementsByTagName('status')[0].firstChild.nodeValue;
                msg = this.responseXML.getElementsByTagName('msg')[0].firstChild.nodeValue;
                alert(msg);
                this.parent.clear();
                this.parent.stateChange('new');
                this.parent.visibleSon1x1();
            }
            
            if (this.cmd != 'copyFrom') {
                this.parent.resetFieldOldValue();
            }
            this.parent.toggleFreezeControls(false);
        }
    }
    
    /**
     * Trata erro XML em objeto CRUD
     */
    this.pAjax.ajaxXmlError = function(err, msg)
    {
        alert(msg);
        if (this.parent.state != 'view') {
            this.parent.unFreezeFields();
            this.parent.toggleFreezeControls(false);
        }
        if (this.cmd == 'delete') {
            this.parent.toggleFreezeControls(false);
        }
    }
    
    /**
     * Verifica se existe algum search ligado a algum campo "virtual"
     */
    this.retrieveVirtual = function()
    {
        for (let i in this.sonSearch) {
        
            var canVirtual = false;
            for (let j in this.sonSearch[i].fieldReturn) {
                for (let k in this.fieldId) {
                    if (this.sonSearch[i].fieldReturn[j][1] == this.fieldId[k] && this.fieldVirtual[k] == true) {
                        canVirtual = true;
                    }
                }
            }
            
            if (canVirtual) {
                this.sonSearch[i].goRetrieve();
            }
        }
        
        // Aplica o mesmo método recursivamente aos relacionamentos 1x1
        for (let i in this.son1x1) {
            if (this.verifySon(i)) {
                this.son1x1[i].retrieveVirtual();
            }
        }
    }
    
    this.addParent1x1 = function(parent, parentFieldCondition, conditionValue, conditionOperator)
    {
        this.parent1x1 = parent;
        parent.son1x1.push(this);
        var arrCondition = Array();
        arrCondition['fieldName'] = parentFieldCondition;
        arrCondition['value'] = conditionValue;
        arrCondition['operator'] = conditionOperator;
        this.parent1x1Condition = arrCondition;
    }
    
    this.addParent1xN = function(parent)
    {
        this.parent1xN = parent;
        parent.son1xN.push(this);
    }
    
    this.addSonSearch = function(son)
    {
        this.sonSearch[this.sonSearch.length] = son;
    }
    
    this.assignResponseXML = function(responseXML)
    {
        this.xmlData = responseXML.getElementsByTagName(this.objName);
        
        //limpa o formulário
        if (this.parent1x1 == false) {
            this.clear();
        }
        
        //limpa o array de valores
        this.fieldNewValue = Array();
        
        // laço que percorre os campos
        
        if (this.xmlData.length == 0) {
            if (this.isVisible) {
                alert(this.objName + ': ' + gettext('Falta preencher alguns campos'));
            }
        } else {
            for (let i=0; i < this.fieldName.length; i++) {
                // quando é copia não deve preencher os campos noCreate
                if (this.pAjax.cmd == 'copyFrom' && this.fieldNoCreate[i]) {
                    var value = '';
                } else {
                    if (this.fieldVirtual[i]) {
                        var value = '';
                    } else {
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
                    value = pFormat(this.fieldType[i], value, 'text');
                }
                
                this.fieldNewValue[this.fieldName[i]] = value;
            }
            this.writeNewValues();
        }
        
        // Aplica o mesmo método recursivamente aos relacionamentos 1x1
        for (let i in this.son1x1) {
            if (this.verifySon(i)) {
                this.son1x1[i].assignResponseXML(responseXML);
            }
        }
    }
    
    this.resetFieldOldValue = function()
    {
        this.fieldOldValue = Array();
        
        for (let i in this.fieldName) {
            this.fieldOldValue[this.fieldName[i]] = this.fieldNewValue[this.fieldName[i]];
        }
        
        // Aplica o mesmo método recursivamente aos relacionamentos 1x1
        for (let i in this.son1x1) {
            if (this.verifySon(i)) {
                this.son1x1[i].resetFieldOldValue();
            }
        }
    }
    
    /**
     * Verifica se as condições do crud filho estão satisfeitas
     *
     * @param i integer: indice do crud filho
     *
     * @return boolean
     */
    this.verifySon = function(i)
    {
        if ((this.fieldNewValue[this.son1x1[i].parent1x1Condition['fieldName']] == this.son1x1[i].parent1x1Condition['value'] || this.son1x1[i].parent1x1Condition['fieldName'] == '') && this.son1x1[i].parent1x1Condition['operator'] == 'equal') {
            return true;
        }
        if (this.fieldNewValue[this.son1x1[i].parent1x1Condition['fieldName']] != this.son1x1[i].parent1x1Condition['value'] && this.son1x1[i].parent1x1Condition['operator'] == 'not equal') {
            return true;
        }
        return false;
    }
    
    this.clearSerials = function()
    {
        for (iSerials in this.fieldType) {
            if (this.fieldType[iSerials] == 'serial') {
                this.fieldOldValue[this.fieldName[iSerials]] = '';
                this.fieldNewValue[this.fieldName[iSerials]] = '';
            }
        }
    }
    
    this.visibleSon1x1 = function()
    {
        this.readNewValues();
        for (let i in this.son1x1) {
            if (this.verifySon(i)) {
                this.son1x1[i].visibleForm(true);
            } else {
                this.son1x1[i].visibleForm(false);
            }
            
            this.son1x1[i].visibleSon1x1();
        }
    }
    
    /**
     * Gatilho disparado quando o formulário passa a ser visível ou invisível pela regra do relacionamento 1x1
     */
    this.onVisibleForm = function(stateVisible)
    {
        //
    }
    
    this.visibleForm = function(stateVisible)
    {
        if (stateVisible) {
            this.isVisible = true;
            document.getElementById(this.objName+'_form').style.display = 'block';
        } else {
            this.isVisible = false;
            document.getElementById(this.objName+'_form').style.display = 'none';
        }
        this.onVisibleForm(stateVisible);
    }
    
    /**
     * Valida se não possui campos com id duplicado entre os objetos com relacionamento 1x1 (exceto chave primária)
     */
    this.validateDuplicatedIds = function()
    {
        var msg = 'PrumoCrud: '+gettext('Campo com propriedade "fieldId" duplicado entre objetos "%crudParent%" e "%crudSon%" fieldId="%fieldId%"');
        var msgPk = 'PrumoCrud: '+gettext('Campo com propriedade "fieldId" difirente entre objetos "%crudParent%" e "%crudSon%" fieldId="%fieldId%", para campo chave primária do relacionamento 1x1');
        
        for (let i in this.son1x1) {
            for (let j in this.fieldId) {
                for (let k in this.son1x1[i].fieldId) {
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
                    } else {
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
    
    this.readNewValues = function()
    {
        for (let i=0; i < this.fieldName.length; i++) {
            // verifica se tem pai ou campo não é chave primaria
            inputField = document.getElementById(this.fieldId[i]);
            if (inputField == null) {
                alert(this.objName+' Error: Campo "'+this.fieldId[i]+'" não encontrado!');
            }
            if (inputField.getAttribute('type') == 'checkbox') {
                if (inputField.checked) {
                    this.fieldNewValue[this.fieldName[i]] = 't';
                } else {
                    this.fieldNewValue[this.fieldName[i]] = 'f';
                }
            } else {
                this.fieldNewValue[this.fieldName[i]] = inputField.value;
            }
        }
        
        for (let i in this.son1x1) {
            this.son1x1[i].readNewValues();
        }
    }
    
    this.writeNewValues = function()
    {
        for (let i=0; i < this.fieldName.length; i++) {
            var value = this.fieldNewValue[this.fieldName[i]];
            // verifica se tem pai ou campo não é chave primaria
            if (this.parent1x1 == false || !this.fieldPk[i]) {
                inputField = document.getElementById(this.fieldId[i]);
                if (inputField.getAttribute('type') == 'checkbox') {
                    if (value == 't') {
                        inputField.checked = true;
                    } else {
                        inputField.checked = false;
                    }
                } else {
                    inputField.value = value;
                }
            }
        }
    }
    
    /**
     * Limpa os campos do formulário
     */
    this.clear = function()
    {
        for (let i=0; i < this.fieldName.length; i++) {
            if (this.fieldReadonly[i] == false || this.fieldNoCreate[i] == true) {
                if (this.parent1x1 == false || !this.fieldPk[i] || this.fieldNoCreate[i] == true) {
                    inputField = document.getElementById(this.fieldId[i]);
                    if (inputField == undefined) {
                        alert(this.objName + ': ' + gettext('campo') + ' "' + this.fieldId[i] + '" ' + gettext('não encontrado'));
                    }
                    if (this.fieldType[i] == 'boolean') {
                        inputField.removeAttribute('checked');
                        inputField.checked = false;
                    } else {
                        inputField.value = '';
                        inputField.checked = true;
                    }
                }
            }
        }
        for (let i in this.son1x1) {
            this.son1x1[i].clear();
        }
        this.setDefault();
    }
    
    /**
     * Seta o valor padrão
     */
    this.setDefault = function()
    {
        for (let i=0; i < this.fieldName.length; i++) {
            let inputField = document.getElementById(this.fieldId[i]);
            if (inputField != undefined && this.fieldDefault[i] != '') {
                if (this.fieldType[i] == 'boolean' && inputField.getAttribute('type') == 'checkbox') {
                    if (this.fieldDefault[i] == 't' || this.fieldDefault[i] == 'true') {
                        inputField.setAttribute('checked','checked');
                        inputField.checked = true;
                    } else {
                        inputField.removeAttribute('checked');
                        inputField.checked = false;
                    }
                } else {
                    inputField.value = this.fieldDefault[i].toLowerCase() == 'null' ? '' : this.fieldDefault[i];
                }
            }
        }
    }
    
    this.paramPk = function()
    {
        param = '';
        for (let i=0; i < this.fieldPk.length; i++) {
            if (this.fieldPk[i]) {
                let fieldValue = this.fieldNewValue[this.fieldName[i]];
                param += '&new_'+this.fieldId[i]+'='+encodeURIComponent(fieldValue);
            }
        }
        return param;
    }
    
    this.syncPkSon = function()
    {
        let arrRel = Array();
        
        //prepara um array com as chaves do objeto atual
        let parentPk = Array();
        for (let i in this.fieldId) {
            if (this.fieldPk[i]) {
                parentPk.push(this.fieldId[i]);
            }
        }
        
        //laço que percorre os filhos 1x1
        for (let i in this.son1x1) {
            let sonPk = Array();
            for (let j in this.son1x1[i].fieldPk) {
                if (this.son1x1[i].fieldPk[j]) {
                    sonPk.push(this.son1x1[i].fieldName[j]);
                }
            }
            if (parentPk.length != sonPk.length) {
                return false;
            } else {
                for (let j in parentPk) {
                    arrRel.push(Array(parentPk[j],sonPk[j]));
                }
            }
        }
        
        // replica o valor entre os campos chave
        for (let i in this.son1x1) {
            for (let j in arrRel) {
                this.son1x1[i].fieldNewValue[arrRel[j][1]] = document.getElementById(arrRel[j][0]).value;
            }
        }
        
        // aplica o mesmo método recursivamente
        for (let i in this.son1x1) {
            this.son1x1[i].syncPkSon();
        }
    }
    
    this.paramCreate = function()
    {
        let params = '';
        for (let i=0; i < this.fieldPk.length; i++) {
            if (this.parent1x1 == false || this.fieldPk[i] == false) {
                let fieldValue = this.fieldNewValue[this.fieldName[i]];
                params += '&new_'+this.fieldId[i]+'='+encodeURIComponent(fieldValue);
            }
        }
        
        if (this.parent1x1 == false) {
            params = 'objName='+this.objName + '&' + this.objName+'_action=c'+params;        
        } else {
            params = this.objName+'_action=c'+params;
        }
        
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {            
                if (this.verifySon(i)) {
                    params += '&'+this.son1x1[i].paramCreate();
                }
            }
        }
        
        if (this.defaultParamCreate != '') {
            params += '&' + this.defaultParamCreate;
        }
        
        return params
    }
    
    this.doCreate = function()
    {
        if (this.beforeCreateRecursive()) {
            if (this.validateDuplicatedIds()) {
                this.freezeFields();
                this.toggleFreezeControls(true);
                this.readNewValues();
                this.pAjax.cmd = 'create';
                this.pAjax.goAjax(this.paramCreate());
            }
        }
    }
    
    this.doFastCreate = function()
    {
        if (this.beforeCreateRecursive()) {
            if (this.validateDuplicatedIds()) {
                this.freezeFields();
                this.toggleFreezeControls(true);
                this.readNewValues();
                this.pAjax.cmd = 'fast_create';
                this.pAjax.goAjax(this.paramCreate());
            }
        }
    }
    
    this.paramRetrieve = function()
    {
        if (this.parent1x1 == false) {
            var    params = 'objName='+this.objName+'&';
        } else {
            var    params = '';
        }
        
        params += this.objName+'_action=r'+this.paramPk();
        for (let i in this.son1x1) {
            params += '&'+this.son1x1[i].paramRetrieve();
        }
        return params;
    }
    
    this.doRetrieve = function()
    {
        if (this.beforeRetrieveRecursive()) {
            if (this.validateDuplicatedIds()) {
                this.toggleFreezeControls(true);
                this.readNewValues();
                this.syncPkSon();
                this.pAjax.cmd = 'retrieve';
                this.pAjax.goAjax(this.paramRetrieve());
            }
        }
    }
    
    this.doCopyFrom = function()
    {
        if (this.validateDuplicatedIds()) {
            this.readNewValues();
            this.syncPkSon();
            this.pAjax.cmd = 'copyFrom';
            this.pAjax.goAjax(this.paramRetrieve());
        }
    }
    
    this.paramUpdate = function()
    {
        var params = '';
        for (let i=0; i < this.fieldPk.length; i++) {
            if (this.parent1x1 == false || this.fieldPk[i] == false) {
                var fieldNewValue = this.fieldNewValue[this.fieldName[i]];
                var fieldOldValue = this.fieldOldValue[this.fieldName[i]];
                params += '&old_'+this.fieldId[i]+'='+encodeURIComponent(fieldOldValue);
                params += '&new_'+this.fieldId[i]+'='+encodeURIComponent(fieldNewValue);
            }
        }
        
        if (this.parent1x1 == false) {
            params = 'objName='+this.objName + '&' + this.objName+'_action=u'+params;
        } else {
            params = this.objName+'_action=u'+params;
        }
        
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                params += '&'+this.son1x1[i].paramUpdate();
            }
        }
        return params;
    }
    
    this.doUpdate = function()
    {
        if (this.beforeUpdateRecursive()) {
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
    
    this.doFastUpdate = function()
    {
        if (this.beforeUpdateRecursive()) {
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
    
    this.doDelete = function()
    {
        if (this.beforeDeleteRecursive()) {
            if (this.validateDuplicatedIds()) {
                this.readNewValues();
                var param = this.paramPk();
            
                if (this.parent1x1 == false) {
                    param += '&objName='+this.objName;
                }
                
                this.toggleFreezeControls(true);
                
                this.pAjax.cmd = 'delete';
                this.pAjax.goAjax(this.objName+'_action=d&'+param);
            }
        }
    }
    
    this.doFastDelete = function()
    {
        if (this.beforeDeleteRecursive()) {
            if (this.validateDuplicatedIds()) {
                this.readNewValues();
                var    param = this.paramPk();
            
                if (this.parent1x1 == false) {
                    param += '&objName='+this.objName;
                }
                
                this.toggleFreezeControls(true);
                
                this.pAjax.cmd = 'fast_delete';
                this.pAjax.goAjax(this.objName+'_action=d&'+param);
            }
        }
    }
    
    this.toggleFreezeControls = function(freeze)
    {
        var btName = Array('write_new', 'copy_from', 'clear', 'search', 'write_edit', 'cancel_edit', 'search_view', 'edit', 'delete', 'new', 'fast_create', 'fast_update');
        for (let i=0; i < btName.length; i++) {
            if (document.getElementById(this.objName+'_bt_'+btName[i]) != undefined) {
                if (freeze == true) {
                    document.getElementById(this.objName+'_bt_'+btName[i]).setAttribute('disabled', 'disabled');
                } else {
                    document.getElementById(this.objName+'_bt_'+btName[i]).removeAttribute('disabled');
                }
            }
        }
    }
    
    this.freezeFields = function()
    {
        var fieldCount = this.fieldId.length;
        for (let i=0; i < fieldCount; i++) {
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
        
        for (let i=0; i < this.sonSearch.length; i++) {
            document.getElementById(this.sonSearch[i].objName+'Bt').setAttribute('disabled','disabled');
        }
    }
    
    this.unFreezeFields = function()
    {
        
        for (let i=0; i < this.sonSearch.length; i++) {
            document.getElementById(this.sonSearch[i].objName+'Bt').setAttribute('disabled','disabled')
        }
        
        var fieldCount = this.fieldId.length;
        for (let i=0; i < fieldCount; i++) {
            
            var inputField = document.getElementById(this.fieldId[i]);
            var inputFastCreate = document.getElementById(this.objName+'_'+this.fieldName[i]+'_add');
            var inputFastUpdate = document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit');
            
            inputField.removeAttribute('title');
            
            if (this.parent1x1 == false || ! this.fieldPk[i]) {
                
                if (this.fieldType[i] != 'serial' && this.fieldReadonly[i] == false && (this.state != 'edit' || this.fieldNoUpdate[i] == false)) {
                    
                    inputField.removeAttribute('disabled');
                    
                    if (inputFastCreate != undefined) {
                        inputFastCreate.removeAttribute('disabled');
                    }
                    
                    if (inputFastUpdate != undefined) {
                        inputFastUpdate.removeAttribute('disabled');
                    }
                    
                    if (inputField.pSearch != undefined) {
                        document.getElementById(inputField.pSearch.objName+'Bt').removeAttribute('disabled');
                    }
                } else {
                    
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
        
        //desativa search que não tem ligação com nenhum campo
        for (let i=0; i < this.sonSearch.length; i++) {
            var linkInput = false;
            for (let j=0; j < fieldCount; j++) {
                var inputField = document.getElementById(this.fieldId[j]);
                if (inputField.pSearch != undefined && inputField.pSearch.objName == this.sonSearch[i].objName) {
                    linkInput = true;
                }
            }
            
            if (! linkInput) {
                document.getElementById(this.sonSearch[i].objName+'Bt').removeAttribute('disabled');
            }
        }
        
        this.formFocus();
    }
    
    this.backToForms = function()
    {
        divForms = document.getElementById(this.objName+'_form');
        if (divForms != 'undefined') {
            divForms.style.display = 'block';
        }
        if (this.pCrudList) {
            this.pCrudList.hide();
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o stateChange do PrumoCrud
     */
    this.afterStateChange = function()
    {
        return true;
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o stateChange do PrumoCrud
     */
    this.afterStateChange2 = function()
    {
        return true;
    }
    
    this.beforeStateChange = function()
    {
        return true;
    }
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doRetrieve do PrumoCrud,
     * continuando somente se o retorno for true
     *
     * @returns boolean
     */
    this.beforeCreate = function()
    {
        return true;
    }
    
    /**
     * Chama o beforeCreate recursivamente para filhos 1x1
     *
     * @returns boolean
     */
    this.beforeCreateRecursive = function()
    {
        if (! this.beforeCreate()) {
            return false;
        }
        
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                if (! this.son1x1[i].beforeCreateRecursive()) {
                    return false;
                }
            }
        }
        
        return true;
    }
        
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doRetrieve do PrumoCrud,
     * continuando somente se o retorno for true
     *
     * @returns boolean
     */
    this.beforeRetrieve = function()
    {
        return true;
    }
    
    /**
     * Chama o beforeRetrieve recursivamente para filhos 1x1
     *
     * @returns boolean
     */
    this.beforeRetrieveRecursive = function()
    {
        if (! this.beforeRetrieve()) {
            return false;
        }
        
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                if (! this.son1x1[i].beforeRetrieveRecursive()) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doUpdate do PrumoCrud,
     * continuando somente se o retorno for true
     *
     * @returns boolean
     */
    this.beforeUpdate = function()
    {
        return true;
    }
    
    /**
     * Chama o beforeUpdate recursivamente para filhos 1x1
     *
     * @returns boolean
     */
    this.beforeUpdateRecursive = function()
    {
        if (! this.beforeUpdate()) {
            return false;
        }
        
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                if (! this.son1x1[i].beforeUpdateRecursive()) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado antes do doDelete do PrumoCrud,
     * continuando somente se o retorno for true
     *
     * @returns boolean
     */
    this.beforeDelete = function()
    {
        return true;
    }
    
    /**
     * Chama o beforeDelete recursivamente para filhos 1x1
     *
     * @returns boolean
     */
    this.beforeDeleteRecursive = function()
    {
        if (! this.beforeDelete()) {
            return false;
        }
        
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                if (! this.son1x1[i].beforeDeleteRecursive()) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o retrieve do PrumoCrud
     */
    this.afterCreate = function()
    {
        //
    }
    
    /**
     * Chama o afterCreate recursivamente para filhos 1x1
     */
    this.afterCreateRecursive = function()
    {
        this.afterCreate();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterCreateRecursive();
            }
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o retrieve do PrumoCrud
     */
    this.afterCreate2 = function()
    {
        //
    }
    
    /**
     * Chama o afterCreate2 recursivamente para filhos 1x1
     */
    this.afterCreate2Recursive = function()
    {
        this.afterCreate2();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterCreate2Recursive();
            }
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o update do PrumoCrud
     */
    this.afterUpdate = function()
    {
        //
    }
    
    /**
     * Chama o afterUpdate recursivamente para filhos 1x1
     */
    this.afterUpdateRecursive = function()
    {
        this.afterUpdate();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterUpdateRecursive();
            }
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o update do PrumoCrud
     */
    this.afterUpdate2 = function()
    {
        //
    }
    
    /**
     * Chama o afterUpdate2 recursivamente para filhos 1x1
     */
    this.afterUpdate2Recursive = function()
    {
        this.afterUpdate2();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterUpdate2Recursive();
            }
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o retrieve do PrumoCrud
     */
    this.afterRetrieve = function()
    {
        return true;
    }
    
    /**
     * Chama o afterRetrieve recursivamente para filhos 1x1
     */
    this.afterRetrieveRecursive = function()
    {
        this.afterRetrieve();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterRetrieveRecursive();
            }
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o retrieve do PrumoCrud
     */
    this.afterRetrieve2 = function()
    {
        return true;
    }
    
    /**
     * Chama o afterRetrieve2 recursivamente para filhos 1x1
     */
    this.afterRetrieve2Recursive = function()
    {
        this.afterRetrieve2();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterRetrieve2Recursive();
            }
        }
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após o delete do PrumoCrud
     */
    this.afterDelete = function()
    {
        //
    }
    
    /**
     * Chama o afterDelete recursivamente para filhos 1x1
     */
    this.afterDeleteRecursive = function()
    {
        this.afterDelete();
        for (let i in this.son1x1) {
            if (this.son1x1[i].isVisible) {
                this.son1x1[i].afterDeleteRecursive();
            }
        }
    }
    
    this.hideSon1xN = function()
    {
        for (iSon in this.son1xN) {
            this.son1xN[iSon].hide();
        }
    }
    
    /**
     * Sincroniza os filtros dos objetos filhos com as chaves primárias do objeto pai
     */
    this.syncPkToSon1xN = function()
    {
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
     * @param newState string: suporta new, view, edit, copy e list
     */
    this.stateChange = function(newState)
    {
        this.state = newState;
        
        if (! this.beforeStateChange()) {
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
        } else if (newState == 'view') {
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
        } else if (newState == 'edit') {
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
        } else if (newState == 'list') {
            divForms = document.getElementById(this.objName+'_form');
            
            if (divForms != undefined && divForms != null) {
                divForms.style.display = 'none';
                
                if (this.pCrudList == false) {
                    if (this.parent1x1 == false) {
                        alert(this.objName+': '+'Listagem não disponível');
                    }
                } else {
                    this.pCrudList.goSearch();
                }
            } else {
                alert(this.objName+': ('+this.objName+'_form'+') '+'Formulário não encontrado');
            }
            
            this.hideSon1xN();
            if (this.parent1xN) {
                this.parent1xN.showControls();
            }
        } else {
            var msg = gettext('Estado desconhecido para objeto PrumoCrud: stateChange(\'"%newState%"\')');
            msg = msg.replace('%newState%',newState);
            alert(msg);
        }
        
        for (let i in this.son1x1) {
            this.son1x1[i].stateChange(newState);
        }
        
        for (iSon in this.son1xN) {
            divContainer = document.getElementById(this.son1xN[iSon].objName+'_container');
            if (divContainer != undefined && divContainer != null) {
                if (newState == 'view') {
                    divContainer.style.display = 'block';
                } else {
                    divContainer.style.display = 'none';
                }
            }
        }
        
        this.afterStateChange();
        this.afterStateChange2();
    }
    
    /**
     * Evento reservado para implementação pelo desenvolvedor da aplicação, disparado após a validação de notNull
     */
    this.preValidate = function()
    {
        return true;
    }
    
    /**
     * Valida formato dos campos
     */
    this.errValidateType = function()
    {
        var msg = '';
        var err = '';
        var fieldValue = '';
        
        for (let i in this.fieldName) {
            
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
        
        for (let i in this.son1x1) {
            if (this.verifySon(i)) {
                err += this.son1x1[i].errValidateType();
            }
        }
        
        return err;
    }
    
    /**
     * Valida campos notNull
     */
    this.errValidateNotNull = function()
    {
        this.readNewValues();
        var err = '';
        for (let i in this.fieldName) {
            if (this.fieldNotNull[i] && this.fieldNewValue[this.fieldName[i]] == '') {
                var msg = '- '+gettext('Campo "%fieldLabel%" não pode ficar em branco')+'.\n'
                err += msg.replace('%fieldLabel%',this.fieldLabel[i]);
            }
        }
        
        for (let i in this.son1x1) {
            if (this.verifySon(i)) {
                err += this.son1x1[i].errValidateNotNull();
            }
        }
        
        return err;
    }
    
    this.validateNotNull = function()
    {
        var err = this.errValidateNotNull();
        err += this.errValidateType();
        
        if (err == '') {
            return this.preValidate();
        } else {
            alert(err);
            this.focusNotNull();
            return false;
        }
    }
    
    this.focusNotNull = function()
    {
        
        var fucusOk = false;
        for (let i in this.fieldName) {
            if (this.fieldNotNull[i] && this.fieldNewValue[this.fieldName[i]] == '') {
                if (this.parent1x1 == false || !this.fieldPk[i]) {
                    
                    if (document.getElementById(this.objName+'_'+this.fieldName[i]+'_add') != undefined) {
                        document.getElementById(this.objName+'_'+this.fieldName[i]+'_add').focus();
                    } else if (document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit') != undefined) {
                        document.getElementById(this.objName+'_'+this.fieldName[i]+'_edit').focus();
                    } else {
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
        for (let i in this.son1x1) {
            if (fucusOk == false && this.son1x1[i].isVisible) {
                this.son1x1[i].focusNotNull();
            }
        }
    }
    
    this.formFocus = function()
    {
        if (this.parent1x1 == false) {
            for (let i in this.fieldName) {
                inputField = document.getElementById(this.fieldId[i]);
                if (inputField.getAttribute('disabled') != 'disabled') {
                    inputField.focus();
                    break;
                }
            }
        }
    }
    
    this.bt_write_new = function()
    {
        if (this.validateNotNull()) {
            this.doCreate();
        }
        this.visibleSon1x1();
    }
    
    this.bt_search = function()
    {
        if (this.pCrudList == false) {
            this.pSearch.goSearch();
        } else {
            this.stateChange('list');
        }
    }
    
    this.bt_write_edit = function()
    {
        if (this.validateNotNull()) {
            this.doUpdate();
        }
    }
    
    this.bt_cancel_edit = function()
    {
        this.doRetrieve();
    }
    
    this.bt_edit = function()
    {
        this.stateChange('edit');
    }
    
    this.bt_delete = function()
    {
        if (confirm(gettext('Confirma a exclusão do registro?'))) {
            this.doDelete();
        }
    }
    
    this.readAutoClearValues = function()
    {
        for (let i in this.fieldName) {
            inputField = document.getElementById(this.fieldId[i]);
            if (inputField.getAttribute('type') == 'checkbox') {
                if (inputField.checked) {
                    this.fieldAutoClearValue[this.fieldName[i]] = 't';
                } else {
                    this.fieldAutoClearValue[this.fieldName[i]] = 'f';
                }
            } else {
                this.fieldAutoClearValue[this.fieldName[i]] = inputField.value;
            }
        }
        
        for (let i in this.son1x1) {
            this.son1x1[i].readAutoClearValues();
        }
    }
    
    this.writeAutoClearValues = function()
    {
        for (let i in this.fieldName) {
            inputField = document.getElementById(this.fieldId[i]);
            if (inputField.getAttribute('type') != 'checkbox') {
                if (this.fieldAutoClearValue[this.fieldName[i]] != '') {
                    inputField.value = this.fieldAutoClearValue[this.fieldName[i]];
                }
            }
        }
        
        for (let i in this.son1x1) {
            this.son1x1[i].writeAutoClearValues();
        }
    }
    
    this.autoClear = function() {
        this.backToForms();
        this.readAutoClearValues();
        this.stateChange('new');
        this.writeAutoClearValues();
        this.visibleSon1x1();
        this.retrieveVirtual();
    }
    
    this.bt_new = function()
    {
        this.backToForms();
        this.stateChange('new');
        this.visibleSon1x1();
        this.retrieveVirtual();
    }
    
    this.bt_list = function()
    {
        this.stateChange('list');
    }
    
    this.bt_copyFrom = function()
    {
        this.pSearch.goSearch();
    }
    
    this.hide = function()
    {
        if (document.getElementById(this.objName+'_form') != undefined) {
            document.getElementById(this.objName+'_form').style.display = 'none';
        }
        if (this.pCrudList) {
            document.getElementById('pCrudList_'+this.objName).style.display = 'none';
        }
    }
    
    /**
     * Torna visível os controles do crud
     */
    this.showControls = function()
    {
        if (this.parent1x1) {
            this.parent1x1.showControls();
        } else {
            if (document.getElementById(this.objName+'_controls') == undefined) {
                alert('Botões de controle não encontrado para objeto "'+this.objName+'".');
            } else {
                document.getElementById(this.objName+'_controls').style.display = 'block';
            }
        }
    }
    
    /**
     * Torna invisível os controles do crud
     */
    this.hideControls = function()
    {
        if (this.parent1x1) {
            this.parent1x1.hideControls();
        } else {
            if (document.getElementById(this.objName+'_controls') == undefined) {
                alert('Botões de controle não encontrado para objeto "'+this.objName+'".');
            } else {
                document.getElementById(this.objName+'_controls').style.display = 'none';
            }
        }
    }
    
    /**
     * Redirecionamento para o mesmo método em this.pCrudList.pFilter e this.pSearch.pFilter
     */
    this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2)
    {
        this.pSearch.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
        if (this.pCrudList != false) {
            this.pCrudList.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
        }
    }
    
    /**
     * Redirecionamento para o mesmo método em this.pCrudList.pFilter e this.pSearch.pFilter
     */
    this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2)
    {
        this.pSearch.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
        if (this.pCrudList != false) {
            this.pCrudList.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
        }
    }
    
    this.inputSetValue = function(id, value)
    {
        var inputField = document.getElementById(id);
        
        if (inputField != undefined) {
            
            if (inputField.getAttribute('type') == 'checkbox') {
                if (value == 't') {
                    inputField.checked = true;
                } else {
                    inputField.checked = false;
                }
            } else {
                inputField.value = value;
            }
        }
    }
    
    this.inputGetValue = function(id)
    {
        var inputField = document.getElementById(id);
        if (inputField.getAttribute('type') == 'checkbox') {
            if (inputField.checked || inputField.getAttribute('checked') == 'checked') {
                return 't';
            } else {
                return 'f';
            }
        } else {
            return inputField.value;
        }
    }
    
    this.bt_fastCreate = function()
    {
        
        this.stateChange('new');
        
        var fieldCount = this.fieldId.length;
        for (let i=0; i < fieldCount; i++) {
            
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
    
    this.bt_fastCreate_onKeyDown = function(event)
    {
        //Enter
        if (event.keyCode == 13) {
            this.bt_fastCreate();
        }
    }
    
    this.bt_fastUpdate = function(lineIndex)
    {
        
        this.stateChange('edit');
        this.clear();
        
        var fieldCount = this.fieldId.length;
        for (let i=0; i < fieldCount; i++) {
            
            var id = this.objName+'_'+this.fieldName[i]+'_edit';
            inputFastUpdate = document.getElementById(id);
            
            var oldValue = this.pCrudList.pGrid.getValue(this.fieldName[i], lineIndex);            
            if (inputFastUpdate == undefined) {
                var newValue = pFormat(this.fieldType[i], oldValue, 'text');
            } else {
                var newValue = this.inputGetValue(id);
            }
            
            this.fieldOldValue[this.fieldName[i]] = pFormat(this.fieldType[i], oldValue, 'text');
            this.fieldNewValue[this.fieldName[i]] = newValue;
            
            this.inputSetValue(this.fieldId[i], newValue);
        }
        
        this.visibleSon1x1();
        
        if (this.validateNotNull()) {
            document.getElementById(this.objName+'_bt_fast_update').setAttribute('disabled', 'disabled');
            this.doFastUpdate();
        }
    }
    
    this.bt_fastUpdate_onKeyDown = function(event, lineIndex)
    {
        //Enter
        if (event.keyCode == 13) {
            this.bt_fastUpdate(lineIndex);
        }
    }
    
    this.bt_fastDelete = function(lineIndex)
    {
        if (confirm(gettext('Confirma a exclusão do registro?'))) {
            var fieldCount = this.fieldId.length;
            for (let i=0; i < fieldCount; i++) {
                if (this.fieldPk[i]) {
                    var value = pFormat(this.fieldType[i], this.pCrudList.pGrid.getValue(this.fieldName[i], lineIndex), 'text');
                    this.inputSetValue(this.fieldId[i], value);
                }
            }
            this.freezeFields();
            this.doFastDelete();
        }
    }
}

document.pFilter = Array();

function PrumoFilter(objName, useSimilaritySearch)
{
    this.prumoClass = 'PrumoFilter';
    this.objName = objName;
    this.htmlId = objName.replace('.', '_');
    this.pAjax = new prumoAjax();
    this.pAjax.ajaxFormat = 'xml';
    this.page;
    this.prumoWebPath;
    this.xmlIdentification = 'pFilter';
    this.useSimilaritySearch = useSimilaritySearch;
    
    this.fieldName  = new Array();
    this.fieldLabel = new Array();
    this.fieldType  = new Array();
    
    this.filter = new Array();
    this.count = 0;
    
    this.inputType = new Array();
    this.inputType['serial']    = 'number',
    this.inputType['integer']   = 'number',
    this.inputType['string']    = 'text',
    this.inputType['text']      = 'text',
    this.inputType['numeric']   = 'number',
    this.inputType['date']      = 'date',
    this.inputType['time']      = 'time',
    this.inputType['timestamp'] = 'datetime',
    this.inputType['boolean']   = 'select'
    
    //operadores lógicos para campos em formato texto
    this.textOperators = Array(
        'similarity',
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
    
    this.textOperatorsName = Array(
         gettext('similar à'),
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
    
    //operadores lógicos para campos em formato data/hora
    this.dateTimeOperators = Array(
         'date_time equal',
         'date_time not equal',
         'date_time less than',
         'date_time greater than',
         'date_time less than or equal',
         'date_time greater than or equal',
         'date_time between',
         'is null',
         'not is null'
    );
    
    this.dateTimeOperatorsName = Array(
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
    
    if (this.useSimilaritySearch == 'f') {
        this.textOperators.shift();
        this.textOperatorsName.shift();
    }
    
    //operadores lógicos para campos em formato numerico
    this.numericOperators = Array(
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
    this.booleanOperators = Array(
         'equal',
         'not equal',
         'is null',
         'not is null'
    );
    
    this.booleanOperatorsName = Array(
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
    this.addFilter = function(filterIndex, fieldName, operator, value, value2, visible)
    {
        function aFilter(aFieldName, aOperator, aValue, aValue2, aVisible)
        {
            this.fieldName = aFieldName;
            this.operator  = aOperator;
            this.value     = aValue;
            this.value2    = aValue2;
            this.visible   = aVisible;
        }
        var newFilter = new aFilter(fieldName, operator, value, value2, visible);
        if (filterIndex == null) {
            this.filter.push(newFilter);
        } else {
            this.filter.splice(filterIndex, 0, newFilter);
        }
        this.count++;
    }
    
    /**
     * Coloca o foco no primeiro filtro
     */
    this.focus = function()
    {
        for (let i in this.filter) {
            if (this.filter[i].visible) {
                document.getElementById(this.htmlId+'_'+i+'_value').focus();
                break;
            }
        }
    }
    
    /**
     * Evento do botão (+) do filtro
     */
    this.cmdAddFilter = function(filterIndex)
    {
        this.addFilter(filterIndex, '', '', '', '', true);
        this.draw();
    }
    
    /**
     * Remove um determinado filtro
     *
     * @param filterIndex integer: número do filtro
     */
    this.removeFilter = function(filterIndex)
    {
        removed = this.filter.splice(filterIndex, 1);
        this.count--;
        return removed;
    }
    
    /**
     * Evento do botão (-) do filtro
     *
     * @param filterIndex integer: número do filtro
     */
    this.cmdRemoveFilter = function(filterIndex)
    {
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
    this.clearVisibleFilters = function()
    {
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
    this.fieldTypeByName = function(fieldName)
    {
        for (let i=0; i < this.fieldName.length; i++) {
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
    this.fieldLabelByName = function(fieldName)
    {
        for (let i=0; i < this.fieldName.length; i++) {
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
    this.operatorTypeByName = function(fieldName)
    {
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
                return 'date_time';
                break;
            case 'time':
                return 'date_time';
                break;
            case 'timestamp':
                return 'date_time';
                break;
            case 'boolean':
                return 'boolean';
                break;
        default:
            return 'text';
        }
    }
    
    /**
     * gatilho disparado depois do selectFieldChange
     */
    this.afterSelectFieldChange = function() {
        // método que pode ser usado pelo desenvolvedor da aplicação
    }
    
    /**
     * Evento disparado pelo onChange do select "nome do campo"
     * 
     * @param selectFieldName string: nome do campo
     * @param index integer: número do filtro
     */
    this.selectFieldChange = function(selectFieldName, index)
    {
        let operatorType = this.operatorTypeByName(selectFieldName.value);
        let selectOperator = document.getElementById(this.htmlId+'_'+index+'_operator');
        
        let arrOperators = Array();
        let arrOperatorsName = Array();
        
        if (operatorType == 'text') {
            arrOperators     = this.textOperators;
            arrOperatorsName = this.textOperatorsName;
        }
        if (operatorType == 'date_time') {
            arrOperators     = this.dateTimeOperators;
            arrOperatorsName = this.dateTimeOperatorsName;
        }
        if (operatorType == 'numeric') {
            arrOperators     = this.numericOperators;
            arrOperatorsName = this.numericOperatorsName;
        }
        if (operatorType == 'boolean') {
            arrOperators     = this.booleanOperators;
            arrOperatorsName = this.booleanOperatorsName;
        }
        
        let htmlOptions = '';
        for (let j=0; j < arrOperators.length; j++) {
            htmlOptions += '<option value="'+arrOperators[j]+'">'+arrOperatorsName[j]+'</option>\n';
        }
        
        selectOperator.innerHTML = htmlOptions;    
        this.filter[index].fieldName = selectFieldName.value;
        
        selectOperator.value = arrOperators[0];
        this.selectOperatorChange(selectOperator, index);
        
        // coloca o foco no campo de pesquisa
        document.getElementById(this.htmlId+'_'+index+'_value').focus();
        
        // redesenha o input
        let currentValue = document.getElementById(this.htmlId+'_'+index+'_value').value;
        let currentValue2 = document.getElementById(this.htmlId+'_'+index+'_value2').value;
        
        let htmlInput = '';
        let htmlInput2 = '';
        let labelTrue = gettext('Sim');
        let labelFalse = gettext('Não');
        if (operatorType == 'boolean') {
            htmlInput  = '<select id="'+this.htmlId+'_'+index+'_value" onchange="'+this.objName+'.inputValueChange(this,'+index+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+index+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+index+')">';
            htmlInput += '<option value="t">'+labelTrue+'</option>';
            htmlInput += '<option value="f">'+labelFalse+'</option>';
            htmlInput += '<option value=""></option>';
            htmlInput += '</select>';
            htmlInput2  = '<select id="'+this.htmlId+'_'+index+'_value2" onchange="'+this.objName+'.inputValueChange(this,'+index+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+index+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+index+')">';
            htmlInput2 += '<option value="t">'+labelTrue+'</option>';
            htmlInput2 += '<option value="f">'+labelFalse+'</option>';
            htmlInput2 += '<option value=""></option>';
            htmlInput2 += '</select>';
        } else {
            let fieldType = this.inputType[this.fieldTypeByName(selectFieldName.value)];
            htmlInput = '<input type="'+fieldType+'" id="'+this.htmlId+'_'+index+'_value" size="15" onchange="'+this.objName+'.inputValueChange(this,'+index+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+index+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+index+')" />\n';
            htmlInput2 = '<input type="'+fieldType+'" id="'+this.htmlId+'_'+index+'_value2" size="15" onchange="'+this.objName+'.inputValue2Change(this,'+index+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+index+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+index+')" />\n';
        }
        
        document.getElementById(this.htmlId+'_'+index+'_input').innerHTML = htmlInput;
        document.getElementById(this.htmlId+'_'+index+'_value').value = currentValue;
        document.getElementById(this.htmlId+'_'+index+'_value').focus();
        document.getElementById(this.htmlId+'_'+index+'_input2').innerHTML = htmlInput2;
        document.getElementById(this.htmlId+'_'+index+'_value2').value = currentValue2;
        
        this.afterSelectFieldChange();
    }
    
    /**
     * Valida o valor dos filtros
     *
     * @return boolean
     */
    this.validateFilters = function()
    {
        var err = '';
        var msg = '';
        
        for (let i in this.filter) {
            
            if (this.filter[i].visible) {
                
                var fieldName = this.filter[i].fieldName;
                var fieldLabel = this.fieldLabelByName(fieldName);
                var fieldType = this.fieldTypeByName(fieldName);
                var fieldOperator = this.filter[i].operator;
                var fieldValue = this.filter[i].value;
                var fieldValue2 = this.filter[i].value2;
                
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
                        if (document.getElementById(this.htmlId+'_'+i+'_value') != undefined) {
                            document.getElementById(this.htmlId+'_'+i+'_value').focus();
                        }
                    }
                }
                
                if ((fieldOperator == 'between' || fieldOperator == 'date_time between') && fieldValue2 != '' && prumoIsType(fieldValue2, fieldType) == false) {
                    
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
                        if (document.getElementById(this.htmlId+'_'+i+'_value2') != undefined) {
                            document.getElementById(this.htmlId+'_'+i+'_value2').focus();
                        }
                    }
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
        } else {
            return true;
        }
    }
    
    /**
     * Evento disparado pelo onChange do select "nome do campo"
     * 
     * @param selectFieldName string: nome do campo
     * @param index integer: número do filtro
     */
    this.selectOperatorChange = function(selectOperator, index)
    {
        // passa o valor escolhido para o objeto filter
        this.filter[index].operator = selectOperator.value;
        
        if (selectOperator.value == 'is null' || selectOperator.value == 'not is null') {
            document.getElementById(this.htmlId+'_'+index+'_input').style.display = 'none';
        } else {
            document.getElementById(this.htmlId+'_'+index+'_input').style.display = 'block';
        }
        
        if (selectOperator.value == 'between' || selectOperator.value == 'date_time between') {
            document.getElementById(this.htmlId+'_'+index+'_input2').style.display = 'block';
        } else {
            document.getElementById(this.htmlId+'_'+index+'_input2').style.display = 'none';
        }
        
        // coloca o foco no campo de pesquisa
        document.getElementById(this.htmlId+'_'+index+'_value').focus();
    }
    
    /**
     * Copia o valor de um input para a propriedade filter[index] do objeto PrumoFilter
     *
     * @param inputObject objeto dom
     * @param index integer: número do filtro
     */
    this.inputValueChange = function(inputObject, index)
    {
        this.filter[index].value = inputObject.value;
    }
    
    /**
     * Copia o valor de um input para a propriedade filter[index] do objeto PrumoFilter
     *
     * @param inputObject objeto dom
     * @param index integer: número do filtro
     */
    this.inputValue2Change = function(inputObject, index)
    {
        this.filter[index].value2 = inputObject.value;
    }
    
    /**
     * Evento disparado pelo onKeyup das caixas de texto dos filtros
     * @param event: evento
     * @param index integer: número do filtro
     */
    this.inputValueKeyUp = function(event,index)
    {
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
    this.inputValueKeyDown = function(event, index)
    {
        //Enter
        if (event.keyCode != 13) {
            this.parent.keyDown();
        }
    }
    
    /**
     * Desenha/Redesenha os botões (+) e (-) dos filtros
     */
    this.drawFilterControls = function()
    {
        //Conta os filtros visíveis
        visibleFilters = 0;
        for (let i=0; i < this.filter.length; i++) {
            if (this.filter[i].visible) {
                visibleFilters++;
            }
        }
        
        // laço que corre todos os filtros
        for (let i=0; i < this.filter.length; i++) {
        
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
                
                document.getElementById(this.htmlId+'_'+i+'_controls').innerHTML = filterControl;
            }
        }
    }
    
    /**
     * Limpa os valores de todos os filtros visíveis
     */
    this.clearValues = function()
    {
        for (let i=0; i < this.filter.length; i++) {
            if (this.filter[i].visible) {
                this.filter[i].value  = '';
                this.filter[i].value2 = '';
            }
        }
    }
    
    /**
     * Configura os filtros, passando as propriedades do objeto para a interface
     */
    this.configureFilter = function(fieldName, filterIndex)
    {
        if (this.filter[filterIndex].visible) {
            var selectFieldName = document.getElementById(this.htmlId+'_'+filterIndex+'_field');
            var selectOperator  = document.getElementById(this.htmlId+'_'+filterIndex+'_operator');
            var inputValue      = document.getElementById(this.htmlId+'_'+filterIndex+'_value');
            var inputValue2     = document.getElementById(this.htmlId+'_'+filterIndex+'_value2');
            var operator        = this.filter[filterIndex].operator;
            
            // configura selectFilter e o inputValue com o valor anteriormente passado via XML
            if (this.filter[filterIndex].fieldName == '') {
                selectFieldName.value = this.filter[0].fieldName;
            } else {
                selectFieldName.value = this.filter[filterIndex].fieldName;
            }
            inputValue.value = this.filter[filterIndex].value;
            inputValue2.value = this.filter[filterIndex].value2;
            
            // preenche o combo selectOperator
            this.selectFieldChange(selectFieldName, filterIndex);
            
            // configura o selectOperator
            var operatorType = this.operatorTypeByName(this.filter[filterIndex].fieldName);
            if (operator == '') {
                switch (operatorType) {
                    case 'numeric':
                        arrOperators = this.numericOperators;
                        break;
                    case 'date_time':
                        arrOperators = this.dateTimeOperators;
                        break;
                default:
                    arrOperators = this.textOperators;
                }
                operator = arrOperators[0];
            }
            selectOperator.value = operator;
            this.selectOperatorChange(selectOperator, filterIndex);
            if (operator != '') {
                this.filter[filterIndex].operator = operator;
            }
        }
    }
    
    /**
     * Desenha/Redesenha a interface (apenas a parte do PrumoFilter)
     */
    this.draw = function()
    {
        let htmlFilters = '<table cellpadding="0" cellspacing="0">\n';
        for (let i=0; i < this.filter.length; i++) {
            if (this.filter[i].visible) {
                htmlFilters += '    <tr>\n';
                htmlFilters += '        <td>\n';
                htmlFilters += '            <select id="'+this.htmlId+'_'+i+'_field" onchange="'+this.objName+'.selectFieldChange(this,'+i+')">\n';
                for (let j=0; j < this.fieldName.length; j++) {
                    htmlFilters += '                <option value="'+this.fieldName[j]+'">'+this.fieldLabel[j]+'</option>\n';
                }
                htmlFilters += '            </select>&nbsp;\n';
                htmlFilters += '        </td>\n';
                htmlFilters += '        <td>\n';
                htmlFilters += '            <select id="'+this.htmlId+'_'+i+'_operator" onchange="'+this.objName+'.selectOperatorChange(this,'+i+')"></select>&nbsp;\n';
                htmlFilters += '        </td>\n';
                htmlFilters += '        <td>\n';
                htmlFilters += '            <span id="'+this.htmlId+'_'+i+'_input">\n';
                htmlFilters += '                <input type="text" id="'+this.htmlId+'_'+i+'_value" size="15" onchange="'+this.objName+'.inputValueChange(this,'+i+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+i+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+i+')" />&nbsp;\n';
                htmlFilters += '            </span>\n';
                htmlFilters += '        </td>\n';
                htmlFilters += '        <td>\n';
                htmlFilters += '            <span id="'+this.htmlId+'_'+i+'_input2">\n';
                htmlFilters += '                &nbsp;&nbsp;e&nbsp;\n';
                htmlFilters += '                <input type="text" id="'+this.htmlId+'_'+i+'_value2" size="15" onchange="'+this.objName+'.inputValue2Change(this,'+i+')" onkeyup="'+this.objName+'.inputValueKeyUp(event,'+i+')" onkeydown="'+this.objName+'.inputValueKeyDown(event,'+i+')" />&nbsp;\n';
                htmlFilters += '            </span>\n';
                htmlFilters += '        </td>\n';
                htmlFilters += '        <td id="'+this.htmlId+'_'+i+'_controls">\n';
                htmlFilters += '            <br/>\n';
                htmlFilters += '        </td>\n';
                htmlFilters += '    </tr>\n';
            }
        }
        htmlFilters += '</table>\n';
        
        document.getElementById(this.htmlId+'_filters').innerHTML = htmlFilters;
        
        // chama o metodo configure filter para passar os valores do objeto para a interface
        for (let i=0; i < this.filter.length; i++) {
            this.configureFilter(this.filter[i].fieldName,i);
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
    this.getValue = function(fieldName,index)
    {
        var value = this.xmlData[index].getElementsByTagName(fieldName)[0].childNodes[0].nodeValue;
        if (value == 'NULL') {
            value = '';
        }
        return value;
    }
    
    /**
     * Evento disparado após o assignResponseXML para ser implementado conforme necessidade do desenvolvedor da aplicação
     */
    this.afterAssignResponseXML = function()
    {
        // implementar conforme necessidade
    }
    
    /**
     * Configura as propriedades do objeto PrumoFilter de acordo com o resultado XML passado como parâmetro
     *
     * @param responseXML: resultado xml
     */
    this.assignResponseXML = function(responseXML)
    {
        this.xmlData = responseXML.getElementsByTagName(this.xmlIdentification);
        
        // limpa os filtros
        this.filter = new Array()
        
        // laço que percorre o xml
        for (let i=0; i < this.xmlData.length; i++) {
            fieldName    = this.getValue('fieldName', i);
            operator     = this.getValue('operator', i);
            value        = this.getValue('value', i);
            value2       = this.getValue('value2', i);
            visible      = this.getValue('visible', i);
            if (visible == 'false') {
                visible = false;
            } else {
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
    this.privateSetFilter = function(fieldName, filterOperator, fieldValue, fieldValue2, visible)
    {
        // Procura um campo no filtro visivel com o mesmo fieldName
        let nothing = true;
        for (let iFilter in this.filter) {
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
    this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2)
    {
        this.privateSetFilter(fieldName, filterOperator, fieldValue, fieldValue2, true);
    }
    
    /**
     * Seta o valor para o primeiro filtro que encontrar com o id passado, caso não encontre cria um filtro
     */
    this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2)
    {
        this.privateSetFilter(fieldName, filterOperator, fieldValue, fieldValue2, false);
    }
}

function prumoValidator(validator)
{
    this.validator = validator;
    
    this.validate = function(value)
    {
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

document.pGrid = Array();
function PrumoGrid(objName)
{
    this.xmlIdentification;
    this.xmlData;
    this.objName = objName;
    this.htmlId = objName.replace('.', '_');
    this.lines;
    this.lineEventOnData = '';
    this.pointerCursorOnData = false;
    this.selectedLine = 0;
    
    this.field;
    this.fieldType;
    this.fieldVisible;
    
    this.table = document.getElementById(this.htmlId);
    
    for (let i=1; i <= this.table.rows.length -1; i++) {
        this.table.rows[i].setAttribute('onmouseover', objName+'.onmouseover('+i+')');
    }
    
    this.onmouseover = function(i)
    {
        this.selectedLine = i;
        if (i != 0) {
            this.table.rows[i].setAttribute('class', 'prumoGridTrSelected');
        }
        
        for (let j=1; j <= this.table.rows.length -1; j++) {
            if (j != i) {
                if (j % 2 != 0) {
                    this.table.rows[j].setAttribute('class', 'prumoGridTrEven');
                } else {
                    this.table.rows[j].setAttribute('class', 'prumoGridTrOdd');
                }                
            }
        }
    }
    
    this.clear = function()
    {
        pGrid = document.getElementById(this.htmlId);
        
        for (let i=1; i < pGrid.rows.length; i++) {
            
            pGridRow = pGrid.rows[i];
            for (let j=0; j < pGridRow.cells.length; j++) {
                pGridRow.cells[j].style.cursor = 'default';
                pGridRow.cells[j].removeAttribute('onClick');
                pGridRow.cells[j].innerHTML = '<br />';
            }
        }
    }
    
    this.assignResponseXML = function(responseXML)
    {
        this.onmouseover(0);
        this.xmlData = responseXML.getElementsByTagName(this.xmlIdentification);
        
        //limpa o grid
        this.clear();
        
        //laço que percorre o xml
        for (let i=0; i < this.xmlData.length; i++) {
            
            // faz referencia a celua do grid
            var pGridRowCells = document.getElementById(this.htmlId).rows[i+1].cells;
            
            var iColumn = 0;
            for (let j=0; j < this.field.length; j++) {
                
                var valueCell = pFormat(this.fieldType[j], this.getValue(this.field[j], i), 'html');
                
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
    
    this.getValue = function(fieldName, index)
    {
        var value = this.xmlData[index].getElementsByTagName(fieldName)[0].childNodes[0].nodeValue;
        
        if (value == 'NULL') {
            value = '';
        }
        
        return value;
    }
}

function PrumoGridNavigation()
{
    this.xmlIdentification = 'pGridStatus';
    this.count;
    this.pageLines;
    this.page;
    this.maxPages = 10;
    
    //recalc
    this.registersFrom;
    this.registersTo;
    this.pages;
    
    this.getParentName = function()
    {
        return this.parent.objName;
    }
    
    this.getObjName = function()
    {
        return this.getParentName() + '.pGridNavigation';
    }
    
    this.getElement = function()
    {
        return document.getElementById(this.getObjName().replace('.', '_'));
    }
    
    this.clear = function()
    {
        this.getElement().innerHTML = gettext('Carregando')+'...';
    }
    
    this.recalc = function()
    {
        this.registersFrom = (this.page - 1) * this.pageLines + 1;
        this.registersTo = this.registersFrom*1 + this.pageLines*1-1;
        if (this.registersTo > this.count) {
            this.registersTo = this.count;
        }
        this.pages = Math.ceil(this.count / this.pageLines);
    }
    
    this.redraw = function()
    {
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
            htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.getParentName()+'.goSearch(1)">1...</button>';
        }
        
        // botão pagina anterior
        if (thisBar > 1) {
            var lastPage = pageFrom - 1;
            htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.getParentName()+'.goSearch('+lastPage+')">&lt;</button>';
        }
        
        // Laço que cria os botões da barra
        for (let i=0; i < pageTo - pageFrom +1; i++) {
            var iPage = pageFrom + i;
            if (iPage == this.page) {
                htmlOut += '<button class="pButton-outline prumoPagination" disabled="disabled" onclick="'+this.getParentName()+'.goSearch('+iPage+')">'+iPage+'</button>';
            } else {
                htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.getParentName()+'.goSearch('+iPage+')">'+iPage+'</button>';
            }
        }
        
        // botão próxima pagina
        if (iPage != this.pages && this.page != 0) {
            var nextPage = iPage + 1;
            htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.getParentName()+'.goSearch('+nextPage+')">&gt;</button>';
        }
        
        // botão última página
        if (bars - thisBar > 1) {
            htmlOut += '<button class="pButton-outline prumoPagination" onclick="'+this.getParentName()+'.goSearch('+this.pages+')">...'+this.pages+'</button>';
        }
        
        // monta a barra de status
        htmlOut += '<br />';
        htmlOut += gettext('Registros encontrados')+': '+this.count+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        htmlOut += gettext('Mostrando de')+' '+this.registersFrom+' '+gettext('até')+' '+this.registersTo+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        htmlOut += gettext('Página')+' '+this.page+' '+gettext('de')+' '+this.pages;
        htmlOut += '<br />';
    
        this.getElement().innerHTML = htmlOut;
    }
    
    /**
     * Evento disparado após o assignResponseXML para ser implementado conforme necessidade do desenvolvedor da aplicação
     */
    this.afterAssignResponseXML = function()
    {
        // implementar conforme necessidade
    }
    
    this.assignResponseXML = function(responseXML)
    {
        var x=responseXML.getElementsByTagName(this.xmlIdentification);
        this.clear();
        this.count     = x[0].getElementsByTagName('count')[0].childNodes[0].nodeValue;
        this.pageLines = x[0].getElementsByTagName('pageLines')[0].childNodes[0].nodeValue;
        this.page      = x[0].getElementsByTagName('page')[0].childNodes[0].nodeValue;
        
        this.redraw();
        
        this.afterAssignResponseXML();
    }
}

function prumoLoading(divName)
{
    this.divName = divName;
    this.count = 0;
    
    this.show = function(loadId)
    {
        this.count++;        
        this.visible();
    }
    
    this.hide = function(loadId)
    {
        if (this.count > 0) {
            this.count--;
        }
        this.visible();
    }
    
    this.visible = function()
    {
        if (this.count == 0) {
            document.getElementById(this.divName).style.display = 'none';
        } else {
            document.getElementById(this.divName).style.display = 'block';
        }
    }
    
    this.visible();
}

function PrumoMenu(objName)
{
    this.objName = objName;
    
    this.open = function(id)
    {
        subMenu = document.getElementById(this.objName+'_'+id);
        subMenu.style.display = 'block';
    }
    
    this.close = function(id)
    {
        subMenu = document.getElementById(this.objName+'_'+id);
        subMenu.style.display = 'none';
    }
    
    this.onClick = function(id, link, element)
    {
        if (link == '') {
            subMenu = document.getElementById(this.objName+'_'+id);
            if (subMenu.style.display == 'none' || subMenu.style.display == undefined) {
                this.open(id);
            } else {
                this.close(id);
            }
        } else {
            if (element.ctrlKey) {
                window.open(link);
            } else {
                location.href = link;
            }
        }
    }
    
    this.onInput = function()
    {
        var inputMenuValue = document.getElementById('prumo_input_menu').value;
        var dl = document.getElementById('prumo_list_menu');
        for (let i=0; i < dl.options.length; i++) {
            if (inputMenuValue == dl.options[i].value) {
                location.href = 'index.php?page='+dl.options[i].getAttribute('routine');
            }
        }
    }
    
    this.showInputMenu = function()
    {
        pWindowsImputMenu.show(1);
        document.getElementById('prumo_input_menu').focus();
    }
    
    this.imputMenuKeyDown = function(event)
    {
        if (event.keyCode == 27) { //ESC
            pWindowsImputMenu.hide();
        }
    }
}

document.pSearch = Array();
function PrumoSearch(objName, ajaxFile)
{
    this.objName = objName;
    this.identification = objName;
    this.page;
    this.orderBy;
    
    this.crudName;
    
    this.pAjax;
    this.pFilter;
    this.pGrid;
    this.pGridNavigation = new PrumoGridNavigation();
    this.pGridNavigation.parent = this;
    
    this.autoClick = true;
    
    this.fieldReturn = Array();
    
    this.selected = false;
    
    this.responseXml;
    
    this.lineIndex;
    
    this.pAjax = new prumoAjax(ajaxFile);
    this.pAjax.ajaxFormat = 'xml';
    this.pAjax.parent = this;
    this.pAjax.identification = this.identification;
    this.pAjax.pLoading = pLoading;
    
    this.fieldValueOnFocus;
    this.fieldFocusId = '';
    
    this.pWindow = false;
    this.modal = true;
    
    this.fieldName;
    this.fieldPk;
    this.pAjax.ajaxXmlOk = function()
    {
        if (this.cmd == 'r') {
            this.parent.pGrid.assignResponseXML(this.responseXML);
            this.parent.lineClick(0);
            this.parent.afterRetrieve();
        } else {
            this.parent.assignResponseXML(this.responseXML);
            if (this.parent.autoClick == true && this.parent.pGridNavigation.count == 1 && this.parent.pFilter.count > 0) {
                this.parent.lineClick(0);
                this.parent.afterRetrieve();
            }
        }
        document.getElementById(this.parent.objName+'_btSearch').removeAttribute('disabled');
        document.getElementById(this.parent.objName+'_btSearchAll').removeAttribute('disabled');
        
        this.parent.afterList();
    }
    
    this.afterRetrieve = function()
    {
        //implementar conforme necessidade
    }
    
    this.assignResponseXML = function(responseXML)
    {
        this.responseXML = responseXML;
        this.pGrid.assignResponseXML(responseXML);
        this.pGridNavigation.assignResponseXML(responseXML);
        this.pFilter.assignResponseXML(responseXML);
        this.fastCRUD();
    }
    
    this.fastCRUD = function()
    {
        // evento do PrumoCrudList
    }
    
    this.afterSearch = function()
    {
        //implementar conforme necessidade
    }
    
    this.afterSearch2 = function()
    {
        //implementar conforme necessidade
    }
    
    this.afterList = function()
    {
        //implementar conforme necessidade
    }
    
    this.parametersFilters = function()
    {
        param = '';
        for (let i=0; i < this.pFilter.filter.length; i++) {
            param += '&fField[]='+encodeURIComponent(this.pFilter.filter[i].fieldName);
            param += '&fOperator[]='+this.pFilter.filter[i].operator;
            param += '&fValue[]='+encodeURIComponent(this.pFilter.filter[i].value);
            param += '&fValue2[]='+encodeURIComponent(this.pFilter.filter[i].value2);
            param += '&fVisible[]='+this.pFilter.filter[i].visible;
        }
        
        return param;
    }
    
    this.parameters = function()
    {
        if (this.page == undefined) {
            this.page = 1;
        }
        
        if (this.crudName != undefined) {
            var param = 'objName='+this.crudName;
        } else {
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
    
    this.lineClick = function(lineIndex)
    {
        this.lineIndex = lineIndex;
        for (let i=0; i < this.fieldReturn.length; i++) {
            var value = this.pGrid.getValue(this.fieldReturn[i][0], lineIndex);
            var fieldReturn = document.getElementById(this.fieldReturn[i][1]);
            var noRetrieve = this.fieldReturn[i][3];
            if (noRetrieve == false || this.pAjax.cmd != 'r') {
                if (fieldReturn != undefined) {
                    
                    var type = this.fieldReturn[i][2];
                    if (type == 'date') {
                        fieldReturn.value = pFormat(type, value, 'text');
                        if (this.pAjax.cmd == 'r') {
                            fieldReturn.setAttribute('title', pFormat(type, value, 'text'));
                        }
                    } else {
                        if (fieldReturn.getAttribute('type') == 'checkbox') {
                            if (value == 't') {
                                fieldReturn.checked = true;
                            } else {
                                fieldReturn.checked = false;
                            }
                        } else {
                            fieldReturn.value = pFormat(type, value, 'text');
                            if (this.pAjax.cmd == 'r') {
                                fieldReturn.setAttribute('title',pFormat(type, value, 'text'));
                            }
                        }
                    }
                } else {
                
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
        this.afterSearch2();
    }
    
    this.beforeSearch = function()
    {
        //implementar conforme necessidade
        return true;
    }
    
    this.goSearch = function(page)
    {
        if (this.pAjax.working) {
            alert(this.objName + ': ' + gettext('já está trabalhando'));
        } else if (this.pFilter.validateFilters()) {
            
            if (page != undefined) {
                this.page = page;
            }
            
            if (this.beforeSearch()) {
                this.selected = false;
                if (page == undefined) {
                    if (this.pGridNavigation.count == 1 && this.pFilter.count > 0) {
                        this.pFilter.clearValues();
                    }
                    this.pGrid.clear();
                    this.pGridNavigation.clear();
                }
                
                this.show();
                this.pAjax.cmd = 'search';
                document.getElementById(this.objName+'_btSearch').setAttribute('disabled', 'disabled');
                document.getElementById(this.objName+'_btSearchAll').setAttribute('disabled', 'disabled');
                this.pAjax.goAjax(this.parameters());
            }
        }
    }
    
    this.paramRetrieve = function()
    {
        var param = 'objName='+this.objName+'&'+this.objName+'_action=r';
        
        for (let i in this.fieldPk) {
            if (this.fieldPk[i] == true) {
                var idReturn = '';
                for (let j in this.fieldReturn) {
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
    
    this.goRetrieve = function()
    {
        var havePk = false;
        var pkNull = false;
        for (let i in this.fieldPk) {
            if (this.fieldPk[i] == true) {
                havePk = true;
                var idReturn = '';
                for (let j in this.fieldReturn) {
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
        
        if (havePk && pkNull) {
            for (let i=0; i < this.fieldReturn.length; i++) {
                document.getElementById(this.fieldReturn[i][1]).value = '';
            }
        }
    }
    
    this.fieldKeyDown = function(event)
    {
        if (event.keyCode == 113) { //F2
            this.goSearch();
        }
        if (event.keyCode == 13) { //ENTER
            this.fieldBlur(document.getElementById(this.fieldFocusId));
        }
    }
    
    this.cmdSearch = function()
    {
        this.goSearch(1);
    }
    
    this.cmdSearchAll = function()
    {
        this.pFilter.clearValues();
        this.cmdSearch();
        this.pFilter.draw();
    }
    
    this.addFieldReturn = function(fieldName, idReturn, fieldType, noRetrieve)
    {
        this.fieldReturn[this.fieldReturn.length] = Array(fieldName, idReturn, fieldType, noRetrieve);
    }
    
    this.afterShow = function()
    {
        //
    }
    
    this.show = function()
    {
        if (this.pWindow != false) {
            this.pWindow.show(this.modal);
        } else {
            if (document.getElementById(this.objName) != undefined) {
                document.getElementById(this.objName).style.display = 'block';
            }
        }
        this.afterShow();
    }
    
    this.cancel = function()
    {
        if (this.fieldFocusId != '') {
            inputField = document.getElementById(this.fieldFocusId);
            // Caso o usuário tenha digitado algum valor no campo
            if (this.fieldValueOnFocus != inputField.value) {
                for (let i in this.fieldReturn) {            
                    document.getElementById(this.fieldReturn[i][1]).value = "";
                }
            }
            
            inputField.focus();
        }
        this.hide();
    }
    
    this.afterHide = function()
    {
        //
    }
    
    this.hide = function()
    {
        if (this.pWindow) {
            this.pWindow.hide();
        } else {
            if (document.getElementById(this.objName) != undefined) {
                document.getElementById(this.objName).style.display = 'none';
            }
        }
        this.afterHide();
    }
    
    this.upArrow = function()
    {
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
    
    this.downArrow = function()
    {
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
    
    this.enterKey = function()
    {
        if (this.selected) {
            this.lineClick(this.pGrid.selectedLine-1);
        } else {
            this.cmdSearch();
        }
    }
    
    this.keyDown = function()
    {
        this.selected = false;
    }
    
    /**
     * Grava o valor em this.fieldValueOnFocus ao receber o foco para posteriormente ser comparado no evento blur
     */
    this.fieldFocus = function(objField)
    {
        this.fieldValueOnFocus = objField.value;
        this.fieldFocusId = objField.getAttribute('id');
    }
    
    /**
     * Verifica se o usuário digitou ou alterou alguma informação no field
     */
    this.fieldBlur = function(objField)
    {
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
                        
                        for (iFieldSearch in this.pFilter.fieldName) {
                            if (this.pFilter.fieldName[iFieldSearch] == this.fieldReturn[iFieldReturn][0]) {
                                var fieldType = this.pFilter.fieldType[iFieldSearch];
                            }
                        }
                        
                        if (fieldType == 'string') {
                            var operator = 'like';
                        } else {
                            var operator = 'equal';
                        }
                        
                        this.pFilter.clearValues();
                        this.pFilter.setFilter(this.fieldReturn[iFieldReturn][0], operator, objField.value, '');
                        if (objField.value != '') {
                            this.goSearch(1);
                        }
                    }
                }
            }
        }
    }
    
    this.sort = function(field, order)
    {
        if (order == undefined) {
            var element = document.getElementById('PrumoGridTh_' + this.objName + '_' + field);
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
            for (let i in elements) {
                element = document.getElementById('PrumoGridTh_' + this.objName + '_' + elements[i]);
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
    this.setFilter = function(fieldName, filterOperator, fieldValue, fieldValue2)
    {
        this.pFilter.setFilter(fieldName, filterOperator, fieldValue, fieldValue2);
    }
    
    /**
     * Redirecionamento para o mesmo método em this.pFilter
     */
    this.setInvisibleFilter = function(fieldName, filterOperator, fieldValue, fieldValue2)
    {
        this.pFilter.setInvisibleFilter(fieldName, filterOperator, fieldValue, fieldValue2);
    }
}

document.pCrudList = Array();
function PrumoCrudList(objName, ajaxFile)
{
    PrumoSearch.apply(this, arguments);
    
    this.autoClick = false;
    
    this.fastCreate = false;
    this.fastUpdate = false;
    this.fastDelete = false;
    
    this.afterFastCrud = function() {
        // gatilho
    }
    
    this.fastCRUD = function()
    {
        if (this.fastCreate || this.fastUpdate || this.fastDelete) {
            
            let xmlData = this.responseXML.getElementsByTagName(this.pGrid.xmlIdentification);
            
            //laço que com a quantidade de linhas do xml
            if (this.fastDelete) {
                
                // pega o id da coluna onde será colocado o botão excluir
                var iColumnControls = 0;
                for (let j=0; j < this.pGrid.field.length; j++) {
                    if (this.pGrid.fieldVisible[j]) {
                        iColumnControls++;
                    }
                }
                
                for (let i=0; i < xmlData.length; i++) {
                    
                    // linha do grid
                    var pGridRowCells = document.getElementById(this.pGrid.htmlId).rows[i+1].cells;
                    
                    // botão de excluir
                    var htmlInputDelete = '<button class="pButton-outline" onclick="'+this.crudName+'.bt_fastDelete('+i+')"><img src="prumo/images/bt_remove.png" /></button>';
                    pGridRowCells[iColumnControls].innerHTML = htmlInputDelete;
                }
            }
            
            if (this.fastCreate) {
                
                this.parent.clear();
                
                // percorre as colunas
                var iColumn = 0;
                for (let j=0; j < this.pGrid.field.length; j++) {
                
                    if (this.pGrid.fieldVisible[j]) {
                        
                        // faz referencia a linha do grid
                        var pGridRowCells = document.getElementById(this.pGrid.htmlId).rows[xmlData.length+1].cells;
                        
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
            
            this.afterFastCrud();
        }
    }
    
    this.lineClick = function(lineIndex)
    {
        this.lineIndex = lineIndex;
        this.assignResponseXML(this.responseXML);
        
        if (this.fastUpdate) {
            
            // percorre as colunas e adiciona os campos de edição
            var iColumn = 0;
            for (let j=0; j < this.pGrid.field.length; j++) {
                
                if (this.pGrid.fieldVisible[j]) {
                    
                    var value = pFormat(this.pGrid.fieldType[j], this.pGrid.getValue(this.pGrid.field[j], lineIndex), 'text');
                    var pGridRowCells = document.getElementById(this.pGrid.htmlId).rows[lineIndex+1].cells;
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
            
            var pGridRow = document.getElementById(this.pGrid.htmlId).rows[i];
            for (let j=0; j < pGridRow.cells.length; j++) {
                pGridRow.cells[j].style.cursor = 'default';
                pGridRow.cells[j].removeAttribute('onClick');
                pGridRow.cells[j].innerHTML = '<br />';
            }
            
            // limpa os botoes de excluir dos outros registros
            for (let i=0; i < xmlData.length; i++) {
                if (i != lineIndex) {
                    var pGridRowCells = document.getElementById(this.pGrid.htmlId).rows[i+1].cells;
                    pGridRowCells[iColumn].innerHTML = '<br />';
                }
            }
            
            this.parent.unFreezeFields();
            this.afterFastCrud();
        } else {
            
            for (let i=0; i < this.fieldReturn.length; i++) {
            
                var value = this.pGrid.getValue(this.fieldReturn[i][0], lineIndex);
                var fieldReturn = document.getElementById(this.fieldReturn[i][1]);
                
                if (fieldReturn != undefined) {
                    
                    var type = this.fieldReturn[i][2];
                    if (fieldReturn.getAttribute('type') == 'checkbox') {
                        
                        if (value == 't') {
                            fieldReturn.checked = true;
                        } else {
                            fieldReturn.checked = false;
                        }
                    } else {
                        fieldReturn.value = pFormat(type, value, 'text');
                    }
                } else {
                
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
            this.afterSearch2();
        }
    }
}

document.pQueue = Array();
function PrumoQueue(objName,ajaxFile)
{
    PrumoSearch.apply(this, arguments);
    
    this.pAjax.ajaxXmlOk = function()
    {
        this.parent.pGrid.assignResponseXML(this.responseXML);
        this.parent.pGridNavigation.assignResponseXML(this.responseXML);
        this.parent.pFilter.assignResponseXML(this.responseXML);
        document.getElementById(this.parent.objName+'_btSearch').removeAttribute('disabled');
        document.getElementById(this.parent.objName+'_btSearchAll').removeAttribute('disabled');
        this.parent.afterList();
    }
    
    this.lineClick = function(lineIndex)
    {
        var msg = gettext('Falta implementar o método lineClick do objeto ')+this.objName;
        alert(msg);
    }
    
    this.afterList = function()
    {
        //disponivel para implementação
    }
    
}

document.pTab = Array();
function PrumoTab(objName)
{
    this.objName = objName;
    this.tabName = new Array();
    
    this.addTab = function(tabName)
    {
        this.tabName.push(tabName);
    }
    
    this.show = function()
    {
        document.getElementById(this.objName).style.display = 'block';
    }
    
    this.hide = function()
    {
        document.getElementById(this.objName).style.display = 'none';
    }
    
    this.showTab = function(tabName)
    {
        this.show();
        for (let i in this.tabName) {
            if (this.tabName[i] == tabName) {
                document.getElementById(this.objName+'_tab_'+this.tabName[i]).style.display = 'block';
                document.getElementById(this.objName+'_bt_'+this.tabName[i]).className= "pButton-outline active";
            } else {
                document.getElementById(this.objName+'_tab_'+this.tabName[i]).style.display = 'none';
                document.getElementById(this.objName+'_bt_'+this.tabName[i]).className= "pButton-outline";
            }
        }
    }
}

document.pWindow = Array();
function PrumoWindow(objName)
{
    this.objName = objName;
    this.width   = 730;
    this.title   = 'PrumoWindow';
    this.align   = 'center';
    this.vAlign  = 'top';
    
    this.div = document.getElementById(objName);
    
    this.show = function(modal)
    {
        document.getElementById(this.objName+'_title').innerHTML = this.title;
        
        if (modal) {
            document.getElementById(this.objName+'_veil').style.display = 'block';
        }
        this.div.style.display = 'block';
        this.position();
    }
    
    this.afterHide = function() {
        //
    }
    
    this.hide = function()
    {
        document.getElementById(this.objName+'_veil').style.display = 'none';
        this.div.style.display = 'none';
        this.afterHide();
    }
    
    this.move = function()
    {
        thisDiv = document.getElementById(this.objName)
        diffX = 0;
        diffY = 0;
        
        document.getElementById(this.objName+'_title').style.cursor = 'move';
        
        document.onmousemove = function Mouse(event)
        {
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
    
    this.dropMove = function()
    {
        document.getElementById(this.objName+'_title').style.cursor = '';
        document.onmousemove = false;
    }
    
    this.position = function()
    {
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

