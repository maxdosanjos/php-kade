/* 
 * 
 */
var System = function(){	
}

// setando o contexto correto do WebMax tanto no servidor 
// de produção como no servidor de desenvolvimento
System.CONTEXT = "/";
System.ENVIRONMENT = "PRD";
if(window.location.hostname == "localhost"){
	System.ENVIRONMENT = "DEV";
}
System.ENV = System.ENVIRONMENT;

System.setImgMessage = function(typeMsg){
	if(typeMsg == 'error'){
		$("#span_message_icon").removeClass("img_msg_success").addClass("img_msg_error");
	}else{
		$("#span_message_icon").removeClass("img_msg_error").addClass("img_msg_success");						
	}
	
};

/*System.stylizeButtons = function(parent) {
    if (parent == undefined) {
        parent = $("body");
    }
    // Buttons with icons
    $(parent).find(".my-button-add").button({ icons: { primary: "ui-icon-plusthick"} });
    $(parent).find(".my-button-cancel").button({ icons: { primary: "ui-icon-closethick"} });
    $(parent).find(".my-button-delete").button({ icons: { primary: "ui-icon-closethick"} });
    $(parent).find(".my-button-submit").button({ icons: { primary: "ui-icon-check"} });
    $(parent).find(".my-button-export").button({ icons: { primary: "ui-icon-suitcase"} });
    $(parent).find(".my-button-search").button({ icons: { primary: "ui-icon-search"} });
    $(parent).find(".my-button-editicon").button({ icons: { primary: "ui-icon-pencil"} });
    $(parent).find(".my-button-edit").button({ icons: { primary: "ui-icon-pencil"} });
    $(parent).find(".my-button-back").button({ icons: { primary: "ui-icon-arrowthick-1-w"} });
    $(parent).find(".my-button-previous").button({ icons: { primary: "ui-icon-carat-1-w"} });
    $(parent).find(".my-button-next").button({ icons: { primary: "ui-icon-carat-1-e"} });
    $(parent).find(".my-button-history").button({ icons: { primary: "ui-icon-bookmark"} });
    $(parent).find(".my-button-reports").button({ icons: { primary: "ui-icon-calculator"} });
};*/

/**
 * Método para executar uma request assincrona sem Ajax
 */
System.irequestCounter = 0;
System.irequest = function(url,callback,callbackTimeout,timeout){
	// validando parâmetros
	var useTimeout = true;
	if(callbackTimeout == undefined || callbackTimeout == null){
		useTimeout = false;
	}
	if(timeout == undefined || isNaN(timeout) || parseInt(timeout) < 30){
		timeout = 30;
	}
	
	// criando iframe em runtime
	System.irequestCounter++;
	var id = "irequest-result"+System.irequestCounter;
	var style = "width:1px;height:1px;border:0;margin:0;padding:0";
	var code = "<iframe id=\""+id+"\" name=\""+id+"\" style=\""+style+"\" src=\""+url+"\"></iframe>";
	var running = true;
	$("body").append(code);
	
	$("#"+id).load(function(response){
		running = false;
		var content = $("#"+id).contents().text();
		
		// excluindo iframe da página
		$("#"+id).remove();
		
		callback(content);
	});
	
	if(useTimeout){
		window.setTimeout(function(){
			if(running){
				running = false;
				callbackTimeout();
			}
			
			// excluindo iframe da página
			$("#"+id).remove();
		},timeout*1000);
	}
};

System.inArray = function(obj,arrayObj) {
    var i = arrayObj.length;
    while (i--) {
        if (arrayObj[i] === obj) {
            return true;
        }
    }
    return false;
};

System.wordwrap = function( str, width, brk, cut) {
	brk = brk || '\n';
    width = width || 75;
    cut = cut || false;
 
    if (!str) { return str; }
 
    var regex = '.{1,' +width+ '}(\\s|$)' + (cut ? '|.{' +width+ '}|.+$' : '|\\S+?(\\s|$)');
 
    return str.match( RegExp(regex, 'g') ).join( brk );
};

System.isCompatibleVersion = function(pUserVersion){
	if(pUserVersion == undefined || pUserVersion == ""){
		return false;
	}
	
	var libVersion = $.fn.jquery.split(".");
	var libLevel1 = parseInt(libVersion[0]);
	var libLevel2 = parseInt(libVersion[1]);
	var libLevel3 = parseInt(libVersion[2]);
	
	var userVersion = pUserVersion.split(".");
	if(userVersion.length != 3){
		return false;
	}
	var userLevel1 = parseInt(userVersion[0]);
	var userLevel2 = parseInt(userVersion[1]);
	var userLevel3 = parseInt(userVersion[2]);
	
	// nível 1
	if(libLevel1 < userLevel1){
		return false;
	}
	// nível 2
	if(libLevel2 < userLevel2){
		return false;
	}
	// nível 3
	if(libLevel3 < userLevel3){
		return false;
	}
	
	return true;
};

System.getVersion = function(){
	var version = $.fn.jquery.split(".");
	var buffer = "";
	
	for(var i=0;i<version.length;i++){
		buffer += version[i];
	}
	
	return parseInt(buffer);
}

System.IMG_PATH = System.CONTEXT+"imagens/";
System.COLOR_BACKGROUND_ERROR = "#FA8072";
System.COLOR_FOREGROUND_ERROR = "#000";
System.IMG_ERROR = System.IMG_PATH+"err.png";
System.COLOR_BACKGROUND_INFO = "#87CEFA";
System.COLOR_FOREGROUND_INFO = "#000";
System.IMG_INFO = System.IMG_PATH+"info.png";
System.COLOR_BACKGROUND_SUCCESS = "#98FB98";
System.COLOR_FOREGROUND_SUCCESS = "#000";
System.IMG_SUCCESS = System.IMG_PATH+"success.png";
System.messageText = "";
System.messageType = "SUCCESS";

System.getClientInfo = function(){
	var nVer = navigator.appVersion;
	var nAgt = navigator.userAgent;
	var browserName  = navigator.appName;
	var fullVersion  = ''+parseFloat(navigator.appVersion); 
	var majorVersion = parseInt(navigator.appVersion,10);
	var nameOffset,verOffset,ix;

	// In Opera, the true version is after "Opera" or after "Version"
	if ((verOffset=nAgt.indexOf("Opera"))!=-1) {
	 browserName = "Opera";
	 fullVersion = nAgt.substring(verOffset+6);
	 if ((verOffset=nAgt.indexOf("Version"))!=-1) 
	   fullVersion = nAgt.substring(verOffset+8);
	}
	// In MSIE, the true version is after "MSIE" in userAgent
	else if ((verOffset=nAgt.indexOf("MSIE"))!=-1) {
	 browserName = "Microsoft Internet Explorer";
	 fullVersion = nAgt.substring(verOffset+5);
	}
	// In Chrome, the true version is after "Chrome" 
	else if ((verOffset=nAgt.indexOf("Chrome"))!=-1) {
	 browserName = "Chrome";
	 fullVersion = nAgt.substring(verOffset+7);
	}
	// In Safari, the true version is after "Safari" or after "Version" 
	else if ((verOffset=nAgt.indexOf("Safari"))!=-1) {
	 browserName = "Safari";
	 fullVersion = nAgt.substring(verOffset+7);
	 if ((verOffset=nAgt.indexOf("Version"))!=-1) 
	   fullVersion = nAgt.substring(verOffset+8);
	}
	// In Firefox, the true version is after "Firefox" 
	else if ((verOffset=nAgt.indexOf("Firefox"))!=-1) {
	 browserName = "Firefox";
	 fullVersion = nAgt.substring(verOffset+8);
	}
	// In most other browsers, "name/version" is at the end of userAgent 
	else if ( (nameOffset=nAgt.lastIndexOf(' ')+1) < 
	          (verOffset=nAgt.lastIndexOf('/')) ) 
	{
	 browserName = nAgt.substring(nameOffset,verOffset);
	 fullVersion = nAgt.substring(verOffset+1);
	 if (browserName.toLowerCase()==browserName.toUpperCase()) {
	  browserName = navigator.appName;
	 }
	}
	// trim the fullVersion string at semicolon/space if present
	if ((ix=fullVersion.indexOf(";"))!=-1)
	   fullVersion=fullVersion.substring(0,ix);
	if ((ix=fullVersion.indexOf(" "))!=-1)
	   fullVersion=fullVersion.substring(0,ix);

	majorVersion = parseInt(''+fullVersion,10);
	if (isNaN(majorVersion)) {
	 fullVersion  = ''+parseFloat(navigator.appVersion); 
	 majorVersion = parseInt(navigator.appVersion,10);
	}	
	
	// sistema operacional
	var OSName = "Unknown";
	if (window.navigator.userAgent.indexOf("Windows NT 6.2") != -1) OSName="Windows 8";
	if (window.navigator.userAgent.indexOf("Windows NT 6.1") != -1) OSName="Windows 7";
	if (window.navigator.userAgent.indexOf("Windows NT 6.0") != -1) OSName="Windows Vista";
	if (window.navigator.userAgent.indexOf("Windows NT 5.1") != -1) OSName="Windows XP";
	if (window.navigator.userAgent.indexOf("Windows NT 5.0") != -1) OSName="Windows 2000";
	if (window.navigator.userAgent.indexOf("Mac")!=-1) OSName="Mac/iOS";
	if (window.navigator.userAgent.indexOf("X11")!=-1) OSName="UNIX";
	if (window.navigator.userAgent.indexOf("Linux")!=-1) OSName="Linux";
	
	return {
		"os": {
			"name": OSName,
			"version": "?",
			"shortName": "?"
		},
		"browser": {
			"name": browserName+" "+majorVersion,
			"version": majorVersion,
			"shortName": "?"
		}
	};
};

System.getBrowser = function(){
    var N=navigator.appName, ua=navigator.userAgent, tem;
    var M=ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
    if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
    M=M? [M[1], M[2]]: [N, navigator.appVersion, '-?'];
    return M[0].toLowerCase();
};

System.getBrowserVersion = function(){
    var N=navigator.appName, ua=navigator.userAgent, tem;
    var M=ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
    if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
    M=M? [M[1], M[2]]: [N, navigator.appVersion, '-?'];
    return M[1];
};

/**
 * Formata um valor do tipo double em String deixando no formato monetário
 * @price double preço a ser formatado
 * @c int casas decimais
 * @d char separador decimal
 * @t char separador de milhares
 * @return String
 */
System.formatMoney = function(price,c,d,t){
	if(c == undefined){
		c = 2;
	}
	if(d == undefined){
		d = ",";
	}
	if(t == undefined){
		t = ".";
	}
	var n = price, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

System.getNumbers = function(data){
	if(data == undefined || data == ""){
		return 0;
	}
	try {
		return parseInt(data.replace(/[^\d]/gi,''));
	}catch(e){
		return 0;
	}
}

/**
 * Converte um valor formatado em real para double
 */
System.real2double = function(input){
	if(input == undefined || input == null){
		return 0;
	}
	input = input.replace(/R$/gi,"");
	input = input.replace(/\./gi,"");
	input = input.replace(/,/gi,".");
	return parseFloat(input);
};

/**
 */
System.trim = function(str) {
	return str.replace(/^\s+|\s+$/g,"");
};

System.standardizeWidth = function(obj1, obj2){
	var w1 = obj1.width();
	var w2 = obj2.width();
	var w = Math.max(w1,w2);
	obj1.width(w);
	obj2.width(w);
};

System.standardizeHeight = function(obj1, obj2, minHeight){
	var h1 = obj1.height();
	var h2 = obj2.height();
	var h = Math.max(h1,h2);
	
	if(isNaN(minHeight)){
		minHeight = 1;
	}
	
	if(h < minHeight){
		h = minHeight;
	}
	
	obj1.height(h);
	obj2.height(h);
};

System.standardizeSize = function(obj1, obj2){
	System.standardizeWidth(obj1,obj2);
	System.standardizeHeight(obj1,obj2);
};

/**
 * Lista as propriedades do objeto no padrão "nome: valor"
 * @param obj
 * @returns {String}
 */
System.toStringObject = function(obj){
	if (typeof obj != "object" && typeof obj != "function" || obj === null){
		return "";
	}
	var keys = [];
	for(var key in obj){
		var v = "";
		eval("v=obj."+key+";");
		keys.push(key+": "+v+"\n");
	}
	return "Object ["+keys.join(",")+"]";
};

/**
 * Corta um texto caso o comprimento for maior que o estabelecido. Caso
 * o texto for cortado, os ultimos três caracteres serão reticências.
 */
System.cutText = function(text, maxLength){
	if(typeof(text)=='string'){
		if(text.length > maxLength){
			return text.substring(0,Math.max(maxLength-3))+"...";
		}else{
			return text;
		}
	}
	return "";
};

/**
 * @param date Objeto Date
 * @param format Opções disponíveis d:m:Y H:i:s
 */
System.formatDate = function(date,format){
	var day = date.getDate(); 
	var month = date.getMonth()+1 
	var year = date.getFullYear(); 
	var hour = date.getHours();
	var minute = date.getMinutes();
	var second = date.getSeconds();
	
	// padding
	if(day < 10){
		day = "0"+day;
	}
	if(month < 10){
		month = "0"+month;
	}
	if(hour < 10){
		hour = "0"+hour;
	}
	if(minute < 10){
		minute = "0"+minute;
	}
	if(second < 10){
		second = "0"+second;
	}
	
	format = format.replace(/d/gi,day);	
	format = format.replace(/m/gi,month);
	format = format.replace(/Y/gi,year);
	format = format.replace(/H/gi,hour);
	format = format.replace(/i/gi,minute);
	format = format.replace(/s/gi,second);
	
	return format;
};

/**
 * Interpreta data em determinado formato
 * Exemplo: 
 * ano = yy 
 * mês = mm
 * dia = dd
 * @param date
 * @param format
 * @returns
 */
System.parseDate = function(date,format){
	return $.datepicker.parseDate(format, date);
};

/**
 * Retorna o valor de um parâmetro GET
 * @param name
 * @returns
 */
System.GET = function(name){
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.location.href );
	if( results == null ){
		return "";
	}else{
	    return results[1];
	}
};

/**
 * Cria formulário em tempo de execução e submete via POST 
 * @param url URL que vai receber os dados
 * @param data Vetor de objetos genericos com as propriedades "name" e "value"
 */
System.sendPOST = function(url,data,target){
	if(target == undefined){
		target = "_self";
	}
	
	// criando elementos
	var code = "";
	var size = data.length;
	for(var i=0;i<size;i++){
		code += "<input type=\"hidden\" name=\""+data[i].name+"\" value=\""+data[i].value+"\"/>";
	}
	var formElem = $("<form action=\""+url+"\" method=\"POST\" target=\""+target+"\">"+code+"</form>");
	$("body").append(formElem);
	formElem.submit();
};

/**
 * Método padrão para tratar erros HTTP utilizando o ajax do JQuery
 */
System.defaultErrorHandlerJQueryAjax = function(jqXHR, textStatus, errorThrown){
	// não validar status 0 (zero). Quando mais de uma requisição é feita, o navegador (provavelmente)
	// cancela a requisição. Quando o usuário não esta logado ou a sessão expirou, o status 403 é emitido.
	// não validar qualquer outra situação para evitar mensagens de erros quando o sistema esta em perfeito
	// funcionamento
	if(jqXHR.status == 403){
		$("#span_loc_msg").notify('Sua sessão expirou ou usuário não logado!\n Por favor, efetue login novamente.',"error");
	}else if(jqXHR.status == 404){
		$("#span_loc_msg").notify('Página não encontrada!\n Tente novamente mais tarde',"error");
	}else if(jqXHR.status == 500){
		$("#span_loc_msg").notify('Erro interno no servidor!\n Por favor, entre em contato com o T.I.',"error");
	}else if(jqXHR.status == 504){
		$("#span_loc_msg").notify('O servidor não esta respondendo, aguarde e tente novamente mais tarde.',"error");
	}else if(errorThrown == 'parsererror'){
		$("#span_loc_msg").notify('Erro de conversão de dados.\n Por favor, entre em contato com o T.I.',"error");
	}else if(errorThrown == 'timeout'){
		$("#span_loc_msg").notify('Tempo de requisição excedido!\n Por favor, aguarde e tente novamente mais tarde',"error");
	}else{
		$("#span_loc_msg").notify('Por favor, aguarde e tente novamente mais tarde',"error");
	}
};

/**
 * Gera um número randômico dentro da faixa informada
 */
System.random = function(minVal,maxVal){
	var randVal = minVal+(Math.random()*(maxVal-minVal));
	return typeof floatVal=='undefined'?Math.round(randVal):randVal.toFixed(floatVal);
};

/**
 * Coloca listras na tabela e coloca o 
 * efeito hover ao passar o mouse
 */
System.zebra = function(tbody){
	color1 = "#f8f8f8"; // par
	color2 = "#ffffff"; // impar
	color3 = "#FFFFE5"; // selected
	$("tbody."+tbody+" tr").each(function(i){
		$(this).attr("number",i);
		if(i%2==0){
			$(this).children().css("backgroundColor",color1);
		}else{			
			$(this).children().css("backgroundColor",color2);
		}
	}).hover(function(){		
		$(this).children().css("background",color3);
	},function(){
		number = parseInt($(this).attr("number"));
		if(number%2==0){
			$(this).children().css("backgroundColor",color1);
		}else{			
			$(this).children().css("backgroundColor",color2);
		}		
	});	
};

/**
 * Abre um match code de objetos
 * @param title
 * @param url
 * @param params
 * @param f
 */
System.openMatchCodeObject = function(title,url,params,f){
	if(url.lastIndexOf("?") == -1){
		url += "?f="+f;
	}else{
		url += "&f="+f;
	}
	
	// convertendo lista de parâmetros
	paramList = params.split(",");
	var options = new Object();
	for(var i=0;i<paramList.length;i++){
		var tmp = paramList[i].split("=");
		var pKey = tmp[0].toLowerCase();
		var pValue = tmp[1];
		if(pKey == undefined || pKey == ""){
			continue;
		}
		if(pValue == undefined){
			pValue = "";
		}
		var com = "options."+pKey+"='"+pValue+"';";
		eval(com);
	}	
	System.openDefaultWindow(url,title,options);
};

/**
 * Retorna um objeto para a função/método informada
 * pelo GET. Utiliza window.opener como referência
 * @param obj
 */
System.returnMathCodeObject = function(obj){
	var f = System.GET('f');
	if(f == undefined || f == null || f == ''){
		window.alert("Nenhuma função ou método definido para receber dados");
		return;
	}
	eval("window.opener."+f+"(obj)");
	window.close();
};

/**
 * Desabilita a tecla ENTER em campos do formulário como o input text para submeter formulários
 * @returns {Boolean}
 */
System.noenter = function() {
	  return !(window.event && window.event.keyCode == 13); 
};

/**
 * Marca todos os checkbox
 */
System.checkboxCheckAll = function(selector){
	$(selector).attr("checked",true);
};

/**
 * Desmarca todos os checkbox
 */
System.checkboxUncheckAll = function(selector){
	$(selector).attr("checked",false);
};

/**
 * Cria toda a estrutura necessária para marca e desmarca checkbox
 * de acordo com o estado de um checkbox "mestre". 
 * @param checkboxMasterId Id do checkbox principal que vai marcar ou
 * desmarcar outros checkbox
 * @param checkboxClass Classe dos checkbox que serão marcados/desmarcados
 */
System.checkBoxChecker = function(checkboxMasterId,checkboxClass){
	var isCompatible = System.isCompatibleVersion("1.7.0");
		
	if(!isCompatible){
		$(checkboxMasterId).on('change',function(){
			var masterValue = $(this).is(":checked");
			
			$(checkboxClass).each(function(){
				if(!$(this).is(":disabled")){
					this.checked = masterValue;
				}
			});
		});
	}else{
		$(checkboxMasterId).live('change',function(){
			var masterValue = $(this).attr("checked");
			if(masterValue == undefined){
				masterValue = false;
			}
			$(checkboxClass).each(function(){
				var self = $(this);
				if(self.attr("disabled") != "disabled"){
					self.attr("checked",masterValue);
				}
			});
		});
	}
};

System.checkBoxCheckerDOM = function(checkboxMaster){
	if(System.getVersion() > 181){
		$(checkboxMaster).on('change',function(){
			var masterValue = $(this).attr("checked");
			if(masterValue == undefined){
				masterValue = false;
			}
			$(this).parent().parent().parent().parent().find("tbody tr td input[type=checkbox]").each(function(){
				var self = $(this);
				if(self.attr("disabled") != "disabled"){
					self.attr("checked",masterValue);
				}
			});
		});
	}else{
		$(checkboxMaster).live('change',function(){
			var masterValue = $(this).attr("checked");
			if(masterValue == undefined){
				masterValue = false;
			}
			$(this).parent().parent().parent().parent().find("tbody tr td input[type=checkbox]").each(function(){
				var self = $(this);
				if(self.attr("disabled") != "disabled"){
					self.attr("checked",masterValue);
				}
			});
		});
	}
};

System.showMessageDialog = function(message,title,pwidth,pheight){
	// validação
	if(message == undefined || message == null){
		message = "";
	}
	if(title == undefined || title == null || title == ""){
		title = "Atenção";
	}
	if(pwidth == undefined || pwidth == null || pwidth == ""){
		pwidth = 360;
	}
	if(pheight == undefined || pheight == null || pheight == ""){
		pheight = 200;
	}
	
	// criando dialog caso não exista
	if(jQuery('#system\\.messageDialog')[0] == undefined){
		// html
		var code  = "<div id='system.messageDialog' title=\"Atenção\">";
		    code += "<div id='system.messageDialog.message'></div>";
		    code += "</div>";
		jQuery('body').append(code);
		
		// jquery
		$( "#system\\.messageDialog" ).dialog({
			modal: true,
			maxWidth:800,
            maxHeight: 600,
            width: pwidth,
            height: pheight,
			buttons: {
				Ok: function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}else{
		jQuery('#system\\.messageDialog').dialog("open");
	}
	
	// definindo conteúdo
	jQuery('#system\\.messageDialog').dialog('option', 'title', title);
	jQuery('#system\\.messageDialog\\.message').html(message);
};

System.hideWorkingDialog = function(){
	jQuery("#system\\.workingDialog").dialog("close");
};

System.showWorkingDialog = function(message,title,pwidth,pheight){
	// validação
	if(message == undefined || message == null){
		message = "";
	}
	if(title == undefined || title == null || title == ""){
		title = "Atenção";
	}
	if(pwidth == undefined || pwidth == null || pwidth == ""){
		pwidth = 360;
	}
	if(pheight == undefined || pheight == null || pheight == ""){
		pheight = 200;
	}
	
	// criando dialog caso não exista
	if(jQuery('#system\\.workingDialog')[0] == undefined){
		// html
		var code = "";
			code += "<div id='system.workingDialog' title=\"Atenção\">";
		    	code += "<center><img src=\""+System.CONTEXT+"shared/images/dialog-working.gif\"/></center>";
		    	code += "<div id='system.workingDialog.message'>Aguarde, processando ...</div>";
		    code += "</div>";
		jQuery('body').append(code);
		
		// jquery
		$("#system\\.workingDialog").dialog({
			width: 420,
			height: 220,
		    modal: true,
		    autoOpen: false,
		    draggable: false,
	        resizable: false,
		    closeOnEscape: false,
		    open: function(event, ui) {
		    	 $(this).parent().children().children('.ui-dialog-titlebar-close').hide();
		    }
		});
	}
	
	// definindo conteúdo
	jQuery('#system\\.workingDialog').dialog('option', 'title', title);
	jQuery('#system\\.workingDialog\\.message').html(message);
	jQuery("#system\\.workingDialog").dialog("open");
};

/**
 * Exibe um box com uma gif carregando
 */
System.showLoading = function(message){
	if(message == undefined || message == null || message == ""){
		message = "Aguarde, carregando...";
	}
	// verificando se a div esta na página
	if(jQuery('#loading')[0] == undefined){
		var code  = "<div id='loading' style=\"display:none;text-align:center; background-color:#000;color:#fff;padding:10px\">";
		    code += "<center>";
		    	code += "<span id=\"loading-message\">"+message+"</span><br/>";
		    	code += "<img src=\""+System.IMG_PATH+"loading_black.gif\"/>";
		    code += "</center>";
		    code += "</div>";
		jQuery('body').append(code);
	}else{
		$("#loading").css("display","none");
	}
	jQuery('#loading-message').html(message);
	System.centralize($("#loading"));
	$("#loading").css("zIndex",999999);
	$("#loading").show("fast");
};

/**
 * Oculta a box com a gif carregando
 */
System.hiddenLoading = function(){
	System.hideLoading();
};

System.hideLoading = function(){
	$("#loading").hide("fast");
};

/**
 * Exibe um dialogo
 */
System.simpleDialog = function(content,type,delay){
	if(jQuery('#simpledialog')[0] == undefined){
		jQuery('body').append("<div id='simpledialog'></div>");		
	}
	
	// tipo de mensagem
	if(type == null || type == undefined){
		type = "INFO";
	}
	
	var bgColor = "";	
	if(type=='ERROR'){
		bgColor = System.COLOR_BACKGROUND_ERROR;
	}else if(type=='INFO'){
		bgColor = System.COLOR_BACKGROUND_INFO;
	}else if(type=='SUCCESS'){
		bgColor = System.COLOR_BACKGROUND_SUCCESS;
	}
	
	var box = jQuery('#simpledialog');
	box.css("width","80%");	
	box.css("display","block");
	box.css("padding","10px");
	box.css("position","fixed");
	box.css("border","1px solid #aaa");
	box.css("background",bgColor);
	box.html(content);
	
	// verificando se a altura é muito grande
	if(box.height() > 500){
		box.css("height","500px");
		box.css("overflow","auto");
	}
	
	var hPage = $(window).height();
	var hBox = box.height()+20;
	
	box.css("top",hPage);
	System.horizontalCenter(box);
	
	box.animate({
		'top': hPage - hBox
	},250);
	
	// removendo e colocando evento
	box.unbind("dblclick");
	box.dblclick(function(){
		box.animate({
			'top': hPage
		},500,function(){
			box.css("display","none");
		});
	});
	
	// regras para delay
	if(delay == undefined || delay == null){
		if(type == "ERROR"){
			delay = 20;
		}else{
			delay = 3;
		}
	}
	
	// criando temporizador
	if(delay > 0){
		delay = parseInt(delay*1000);
		if(System.messageTimeout != undefined){
			window.clearTimeout(System.messageTimeout);
		}
		System.messageTimeout = window.setTimeout(function(){
			box.animate({
				'top': hPage
			},500,function(){
				box.css("display","none");
			});
		},delay);
	}
};

/**
 * Centraliza um elemento na horizontal da tela
 */
System.horizontalCenter = function(selector){
	var newLeft = ($(window).width()  - $(selector).width()) / 2;
	$(selector).css({'left': newLeft});
};

System.verticalCenter = function(selector){
	var newLeft = ($(window).height()  - $(selector).height()) / 2;
	$(selector).css({'top': newLeft});
};

/**
 * Centraliza um elemento na tela
 */
System.centralize = function(jQueryElement){
	jQueryElement.css("position","absolute");
	jQueryElement.css("top", (($(window).height() - jQueryElement.outerHeight()) / 2) + $(window).scrollTop() + "px");
	jQueryElement.css("left", (($(window).width() - jQueryElement.outerWidth()) / 2) + $(window).scrollLeft() + "px");
}

/**
 * Abre uma nova janela default
 * @param title
 * @param url
 */
System.openDefaultWindow = function(url,title,options,autofocus){
	var param = new Object(); 
	param.width = 640;
	param.height = 480;
	param.fullscreen = 'no';
	param.top = 100;
	param.left = 100;
	param.resizable = 'yes';
	param.toolbar = 'yes';
	param.scrollbars='yes';
	
	if(options != undefined && options != ""){
		param.width = (options.width == undefined)?param.width:options.width;
		param.height = (options.height == undefined)?param.height:options.height;
		param.fullscreen = (options.fullscreen == undefined)?param.fullscreen:options.fullscreen;
		param.top = (options.top == undefined)?param.top:options.top;
		param.left = (options.left == undefined)?param.left:options.left;
		param.resizable = (options.resizable == undefined)?param.resizable:options.resizable;
		param.toolbar = (options.toolbar == undefined)?param.toolbar:options.toolbar;
		param.scrollbars = (options.scrollbars == undefined)?param.scrollbars:options.scrollbars;
	}
	// Telas novas são mais largas: todo popup é redimensionável, rola e tem um tamanho
	// mínimo confortável (limitado ao tamanho da tela)
	param.resizable  = 'yes';
	param.scrollbars = 'yes';
	param.width  = Math.min(Math.max(param.width, 900), screen.availWidth - 40);
	param.height = Math.min(Math.max(param.height, 760), screen.availHeight - 80);
	var win = window.open(url,title,"WIDTH="+param.width+",HEIGHT="+param.height+",fullscreen="+param.fullscreen+"," +
			"top="+param.top+",left="+param.left+",resizable="+param.resizable+",toolbar="+param.toolbar+"," +
					"titlebar="+param.titlebar+",scrollbars="+param.scrollbars);
	if(autofocus != false){
		win.focus();
	}
	return win;
};

System.textCounterPattern = "<b>Restante</b> :restante | <b>Digitados</b> :digitados | <b>Máximo</b> :max";
System.textareaCounter = function(textareaId,counterId,max){                        
	var etextarea = $(textareaId);
	etextarea.keyup(function(){             
		// tratamento           
	    var pattern = System.textCounterPattern;
	    var len = $(this).val().length;
	    var ecounter = $(counterId);
	    if(len >= max){
	    	$(this).val($(this).val().substr(0,max));
	        len = max;
	    }
	    var diff = max-len;
	                
        // output
        pattern = pattern.replace(/:restante/,diff);
        pattern = pattern.replace(/:digitados/,len);
        pattern = pattern.replace(/:max/,max);  
        ecounter.html(pattern);         
	});
	etextarea.keyup();// chamando uma vez
}

/**
 * Traduz um datepicker se disponível
 */
System.translateDatepicker = function(){
	if($.datepicker != undefined){
		$.datepicker.regional['pt-BR'] = {
			closeText: 'Fechar',
			prevText: '&#x3c;Anterior',
			nextText: 'Pr&oacute;ximo&#x3e;',
			currentText: 'Hoje',
			monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
			'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
			monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
			'Jul','Ago','Set','Out','Nov','Dez'],
			dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],
			dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
			dayNamesMin: ['D','S','T','Q','Q','S','S'],
			weekHeader: 'Sm',
			dateFormat: 'dd.mm.yy',
			firstDay: 0,
			isRTL: false,
			showMonthAfterYear: false,
			changeMonth: true,
			changeYear: true,
			showButtonPanel: false,
			showOtherMonths: true,
			selectOtherMonths: true,
			showWeek: false,
			firstDay: 1,
			numberOfMonths: 1,			
			yearSuffix: ''};
		$.datepicker.setDefaults($.datepicker.regional['pt-BR']);
	}
};

//http://kevin.vanzonneveld.net
System.numberFormat = function (number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
};

/**
 * Arredondamento
 * @param value
 * @param precision
 * @param mode
 * @returns {Number}
 */
System.round = function (value, precision, mode) {
    var m, f, isHalf, sgn; 
    precision |= 0; 
    m = Math.pow(10, precision);
    value *= m;
    sgn = (value > 0) | -(value < 0); 
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
        case 'PHP_ROUND_HALF_DOWN':
            value = f + (sgn < 0); 
            break;
        case 'PHP_ROUND_HALF_EVEN':
            value = f + (f % 2 * sgn);
            break;
        case 'PHP_ROUND_HALF_ODD':
            value = f + !(f % 2); 
            break;
        default:
            value = f + (sgn > 0); 
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
};

System.normalize = function(texto){
    texto = texto.replace(/[á|ã|â|à]/gi, "a");
    texto = texto.replace(/[é|ê|è]/gi, "e");
    texto = texto.replace(/[í|ì|î]/gi, "i");
    texto = texto.replace(/[õ|ò|ó|ô]/gi, "o");
    texto = texto.replace(/[ú|ù|û]/gi, "u");
    texto = texto.replace(/[ç]/gi, "c");
    texto = texto.replace(/[ñ]/gi, "n");
    texto = texto.replace(/[á|ã|â]/gi, "a");
    //faz a substituição dos espaços e outros caracteres por - (hífen)
    texto = texto.replace(/\W/gi, "-");
    // remove - (hífen) duplicados
    texto = texto.replace(/(\-)\1+/gi, "-");
    return texto;
};

System.urldecode = function(str){
	str = str.replace(/\+/gi," ");
	str = str.replace(/\%E3/gi,"ã");
	str = str.replace(/\%2C/gi,".");
	return str;
};

System.getDatePickerOp = function()
{
	return {
		dateFormat		: 'dd/mm/yy',
		dayNamesMin		: ['Do', 'Se', 'Te', 'Qa', 'Qu', 'Se', 'Sa'],
		dayNames		: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
		monthNames		: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		//showOn			: "button",
		//buttonImageOnly : true,
		//buttonImage		: "shared/images/calendar.gif"
	};
};


/*jQuery(document).ready(function(){
	// traduzindo datepicker
	System.translateDatepicker();
	
	// exibe uma mensagem
	if(System.messageText != ""){
		System.showMessage(System.messageText,System.messageType);
	}
	
	// testes da soft
	System.getPendingSoftTests(function(response){
		if(response.result == 1){
			// verificando se há testes
			var testCount = response.data.length;
			if(testCount > 0){
				var message = "Você tem "+testCount+" teste(s) pendente(s), " +
						"a avaliação dos requisitos é necessária para confirmar se o que foi solicitado foi atendido.<br/></br>";
				message += "<center><input type=\"button\" value=\"Testar Agora\" onclick=\"System.openTestView()\" style=\"cursor:pointer;padding:10px\"/></center>";
				System.addMainMessage(message);
			}
		}else{
			// ignora erros
		}
	});
	
	// detectando CTRL 
	System.CTRL_DOWN = false;
	$(document).keydown(function(e){
		var KeyCTRL = 17;
        if (e.keyCode == KeyCTRL) {
        	System.CTRL_DOWN = true;
        }
    }).keyup(function(e){
    	var KeyCTRL = 17;
        if (e.keyCode == KeyCTRL) {
        	System.CTRL_DOWN = false;
        }
    });
});
*/