var CustumerBean = function() {
	var saving = false;
	var initialFormHash = "";
	var timeoutSaving = 60; // em segundos 
	var errorFields = new Array();
	var checkChangesBeforeSave = false;
	var intTimeOut = null;
	
	var address = Address.getInstance();
	
	this.initQueryOpen = function(){
		formatGUIEventsQuery( );
	};
	
	this.initBean = function() {
		formatGUIEventsBean();
		viewFieldsPerson();
		viewFieldIE();
	};
	
	var formatGUIEventsQuery = function(){		
		viewFieldsPerson();
		
		
		pagination = new Pagination();
		pagination.init($("#custumer_query_open"));
		
		onCepRequest();
		
		$("#person_pj").live("click", function() {
			viewFieldsPerson();			
		});
		$("#person_pf").live("click", function() {
			viewFieldsPerson();
		});
		$("#person_all").live("click", function() {
			viewFieldsPerson();
		});
		
		$("#custumer_ie_isento").click(function(){
			viewFieldIE();			
		});
		
		$("#button_search").click(function(e) {
			e.preventDefault();
			$("#pag").val("1");
			$("#custumer_query_open").submit();
		});
		
		$("#button_reset").click(function(e) {
			e.preventDefault();
			$("#custumer_query_open .input_clear").val("");
		});
		
		$("#button_csv").click(function(e) {
			$action  = $("#custumer_query_open").attr("action"); 
			e.preventDefault();
			$("#custumer_query_open").attr("action","../CustumerQueryCtrl.ctrlExt/exportCSV");
			$("#custumer_query_open").attr("target","_blank");
			$("#custumer_query_open").submit();
			
			$action  = $("#custumer_query_open").attr("action",$action);
			$("#custumer_query_open").removeAttr("target");
			
		});
		
		$("tr.tr_edit td").click(function(e){
			var $trParam = $(this).parent();
			
			$indexFieldDtl = $("tr.tr_edit").index($trParam);
			
			if($indexFieldDtl > -1){		
				var ident = $("input.cust_hidden_id").eq($indexFieldDtl).val();								
				if(ident > 0)
				{			
					var param = new Object(); 
					param.width = 920;
					param.height = 820;
					param.fullscreen = 'no';
					param.top = 100;
					param.left = 100;
					param.resizable = 'yes';
					param.toolbar = 'yes';
					param.scrollbars='yes';
					
					System.openDefaultWindow('/alter_custumer/action=edit&custumer_person_id='+ident+'&windowPopUp=true','Gerenciamento de Clientes',param,true);
				}
				else
				{
					$("#span_loc_msg").notify("Nenhum resultado encontrado!!!", "error");
				}
			}
		});
		
		$("tr.tr_mc_code td").click(function(e){
			var $trParam = $(this).parent();
			
			$indexFieldDtl = $("tr.tr_mc_code").index($trParam);
			if($indexFieldDtl > -1){		
				$input_id   = window.opener.$("input.client_id");
				$input_name = window.opener.$("input.client_name");
		    	$span_name = window.opener.$("span.client_name");
		    	
		    	$input_id.val($("input.cust_hidden_id").eq($indexFieldDtl).val());
		    	$input_name.val($("input.cust_hidden_id").eq($indexFieldDtl).val());
		    	$span_name.html($("span.span_cli_name_mc").eq($indexFieldDtl).html());
		    	window.close();
			}
		});
	};
	
	var requestNewCaptcha = function(){
		$("#custumer_captcha").val("");
		var d = new Date();
		$("#img_captcha").attr('src', $('img').attr('src') +'?a='+ d.getMilliseconds());
	}
	var onTimeoutSaving = function(){
		if(saving){
			saving = false;
			$("#dialog-working").dialog("close");
			requestNewCaptcha();
			var messageText = "O sistema esta indisponível no momento. <br/>" +
					"Tempo limite de execução de "+timeoutSaving+" segundos atingido, " +
					"tente cadastrar novamente mais tarde.";
			$("#dialog-result\\.messageText").html(messageText);
			$("#dialog-result").dialog("open");
		}
	};
	
	var viewFieldIE = function(){
		if($("#custumer_ie_isento").prop("checked")){
			$("#custumer_ie").val("").addClass("readonly").attr("readonly","true");
		}else{
			$("#custumer_ie").removeClass("readonly").removeAttr("readonly");
		}
	}
	
	var viewFieldsPerson = function(){
		if ($("#person_pj").prop("checked")) {
			$("#div_fields_pj").toggle(true);
			
			$("#div_fields_pf").toggle(false);
			$("#div_fields_pf input.input_per").val("");			
		}else if ($("#person_pf").prop("checked")) {				
			$("#div_fields_pf").toggle(true);
			
			$("#div_fields_pj").toggle(false);
			$("#div_fields_pj input.input_per").val("");
		}else{
			$("#div_fields_pf").toggle(false);
			$("#div_fields_pf input.input_per").val("");
			
			$("#div_fields_pj").toggle(false);
			$("#div_fields_pj input.input_per").val("");
			
		}
	}
	
	var onCepRequest = function(){
		address.eventCEP(function(){
			$("#custumer_address").val("");
			$("#custumer_number").val("");
			$("#custumer_city").val("");
			$("#custumer_neighborhood").val("");
			$("#custumer_region").val("");
			$("#custumer_region_nm").val("");
		}
		, $("#custumer_zipcode") 
		, function(response){		
				if(response.result == 1){
					$("#custumer_address").val(response.data.address);
					$("#custumer_number").val(response.data.addressNumber);
					$("#custumer_city").val(response.data.city);
					$("#custumer_neighborhood").val(response.data.neighborhood);
					$("#custumer_region").val(response.data.UF);
					$("#custumer_region_nm").val(response.data.UF_NM);
				}/*else{
					$("#custumer_zipcode").val("");
					System.showMessageDialog(response.message);
				}*/
		});
	}
	var formatGUIEventsBean = function() {
		
		
		$("#button_save").click(function(e) {
			e.preventDefault();
			$("#custumer_form").submit();
		});
		
		$("#button_back").click(function(e) {
			e.preventDefault();
			window.location = System.CONTEXT;
		});
		$("#button_new").click(function(e) {
			e.preventDefault();
			window.location = System.CONTEXT + "cad_assinante/";
		});

		$("#tabs").tabs();

		$("#person_pj").live("click", function() {
			viewFieldsPerson();
			
		});
		
		$("#person_pf").live("click", function() {
			viewFieldsPerson();
		});
		
		$("#custumer_ie_isento").click(function(){
			viewFieldIE();			
		});
		
		/*person.eventCNPJ($("#custumer_cnpj"));		
		person.eventCPF($("#custumer_cpf"));*/
		
		
		onCepRequest();
		
		initialFormHash = $("#custumer_form").serialize();
		$("#custumer_form").submit(function(){
			// controle de alteração
			var formHash = $(this).serialize();
			if(checkChangesBeforeSave && initialFormHash == formHash){
				System.showMessageDialog("Você não alterou nenhuma informação para salvar!");
				return false;
			}
			initialFormHash = formHash;
			
			if(saving){
				return false;
			}
			saving = true;
			$("#dialog-working").dialog("open");
			
			intTimeOut = window.setTimeout(function(){
				onTimeoutSaving();
			},timeoutSaving*1000);
			
			return true;
		});
		
		$("#custumer_iframe").load(function(){
			if(saving){
				saving = false;
				$("#dialog-working").dialog("close");
				
				window.clearInterval(intTimeOut);
				intTimeOut = null;
				
				// resultado
				var messageText = "";
				try {
					var content = $(this).contents().text();
					var json = $.parseJSON(content);
					System.setImgMessage(json.result);

					
					messageText = json.message;
					// independente se deu certo errado, verifica as mensagens
					var messageList = new Array();
					
					// limpando todos os erros anteriores
					for(var key in errorFields){
						var id = errorFields[key];
						$(id).css("background","#FFFFFF");
					}
					errorFields = new Array();
					
					if(json.result == 'error'){
						if(json.validationException.length > 0){
							messageText += "<br/></br>";
							for(var key in json.validationException){
								var obj = json.validationException[key];
								var isValidId = (obj.id != "");
								
								// trocando ponto e adaptando id
								obj.id = "#"+(obj.id.replace(/\./g,"\\."));
								if(isValidId){
									$(obj.id).css("background","#FFC1C1");
								}
								errorFields.push(obj.id);
								messageList.push(obj.message);
							}
							
							messageText += messageList.join("<br/>");
						}
					}else{
						$("#custumer_form .input_clear").val("");
						
						if(window.opener!=undefined){
							$("#button_search",window.opener.document).click();
						}
						
					}
				}catch(e){
					System.setImgMessage('error');
					messageText = "Erro ao realizar registro<br/><br/>"; 
					messageText += "O sistema esta indisponível no momento, aguarde e tente novamente mais tarde. <br/><br/>"; 
					messageText += "Detalhes técnicos: "+e.message; 
				}
				requestNewCaptcha();
				$("#dialog-result\\.messageText").html(messageText);
				$("#dialog-result").dialog("open");
			}
		});
		
		$("#dialog-result").dialog({
			autoOpen: false,
			closeOnEscape: false,
			width: 640,
			height: 240,
			modal: true,
			open: function(event, ui) {
		    	 $(this).parent().children().children('.ui-dialog-titlebar-close').hide();
		    },
			buttons: {
				"OK": function(){
					$(this).dialog("close");
				}
			}
		});
		
		$("#dialog-working").dialog({
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
		
		$("#btn_new_captcha").click(function(e){
			e.preventDefault();
			requestNewCaptcha( );
		});

	};
};
CustumerBean.instance = null;
CustumerBean.getInstance = function() {
	if (CustumerBean.intance == null)
		CustumerBean.instance = new CustumerBean();
	return CustumerBean.instance;
};