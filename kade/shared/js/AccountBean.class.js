var AccountBean = function() {
	var saving = false;
	var initialFormHash = "";
	var timeoutSaving = 60; // em segundos 
	var errorFields = new Array();
	var checkChangesBeforeSave = false;
	
	this.initQuery = function() {
		formatGUIEventsQuery();
	};
	this.initBean = function(){
		formatGUIEventsBean();
	};
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
	var initDialogWorking = function(){
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
	};
	var setEventsDefaultTableParc = function(){
		$("#table_installment tr.tr_hover td").click(function(e){
			var $trParam = $(this).parent();
			
			$indexFieldDtl = $("tr.tr_hover").index($trParam);
			
			if($indexFieldDtl > -1){		
				var ident = $("input.acc_hidden_id").eq($indexFieldDtl).val();								
				if(ident > 0)
				{			
					var title_dialog = "Detalhes de Pagamento: Parcela "+ident;
					var statusBlk = $("#installment_status_blk_"+ident).val();			
					$("#parc_id").val(ident);
					$("#parc_payment_value_dtl").val($("#installment_payment_value_"+ident).val());
					$("#parc_payment_date_dtl").val($("#installment_payment_date_"+ident).val());
					$("#parc_payment_method_dtl").val($("#installment_payment_method_"+ident).val());							
					$("#parc_old_sts_dtl").val($("#installment_status_"+ident).val());
					
					if(statusBlk == '1'){
						$("#parc_payment_value_dtl").attr("readonly","true").addClass("readonly");
						$("#parc_payment_date_dtl").attr("readonly","true").addClass("readonly");
						$("#parc_payment_method_dtl").attr("readonly","true").addClass("readonly");
						
						$("#parc_payment_method_dtl option[data-enabled='0']").show();
						
						$("#parc_payment_date_dtl").datepicker( "destroy" );
						
						$("#view_parc").dialog({
							buttons : [ {
								text : "Fechar",
								click : function() {
									$(this).dialog("close");
								}
							} ]
						});
					}else{
						$("#parc_payment_value_dtl").removeAttr("readonly","true").removeClass("readonly");
						$("#parc_payment_date_dtl").removeAttr("readonly","true").removeClass("readonly");
						$("#parc_payment_method_dtl").removeAttr("readonly","true").removeClass("readonly");
						if($("#parc_payment_value_dtl").val() <= 0){
							$("#parc_payment_value_dtl").val("");
						}
						
						$("#parc_payment_method_dtl option[data-enabled='0']").hide();
						
						$("#parc_payment_date_dtl").datepicker(System.getDatePickerOp());
						
						$("#view_parc").dialog({
							buttons : [ {
								text : "Confirmar",
								click : function() {
									var ident  	  = $("#parc_id").val();
									var value  	  = $("#parc_payment_value_dtl").val();
									var dtPay  	  = $("#parc_payment_date_dtl").val();
									var methodPay = $("#parc_payment_method_dtl").val();
									var stsOld    = $("#parc_old_sts_dtl").val();
									
									if(value <= 0 || dtPay == "" || methodPay == "")
										alert("Informe os dados obrigatórios de pagamento!");
									else
									{
										var stsParc = 'PAID';
										$("#installment_payment_value_"+ident).val(value);
										$("#installment_payment_date_"+ident).val(dtPay);
										$("#installment_payment_method_"+ident).val(methodPay);
										$("#installment_status_"+ident).val(stsParc);
										
										$("#installment_status_nm_"+ident).html("Pago");
										$("#td_installment_"+ident).removeClass(stsOld).addClass(stsParc);
										
										$(this).dialog("close");
									}	
									
									
								}
							} ]
						});
					}
					
					
					$("#view_parc").dialog({ title:title_dialog});
					
					$("#view_parc").dialog("open");
				}
				else
				{
					$("#span_loc_msg").notify("Nenhum resultado encontrado!!!", "error");
				}
			}
			
		});
	};
	
	var onEventMatchCodeClient = function(){
		$("#btn_match_client").click(function(e){
	        	var param = new Object(); 
				param.width = 540;
				param.height = 520;
				param.fullscreen = 'no';
				param.top = 100;
				param.left = 100;
				param.resizable = 'no';
				param.toolbar = 'yes';
				param.scrollbars='no';
				
				System.openDefaultWindow('/mc_client/action=edit&windowPopUp=true','Procurar de Clientes',param,true);
	        });
		
		$("#account_client_id").change(function(e){
			$value = $(this).val().replace(/^\s+|\s+$/g, "");
			if ($value != "") {
				$("#dialog-working").dialog("open");
				$.ajax({
					type : "POST",
					dataType : "json",
					url : "../CustumerQueryCtrl.ctrlExt/searchSingleCHK",
					data : ({
						custumer_id : $value
					}),
					error : function(xhr, ajaxOptions, thrownError){
						$("#dialog-working").dialog("close");
						$("#span_account_client_name").html("");
						$("#account_client_name").val("");
						$("#account_client_id").val("");
						System.defaultErrorHandlerJQueryAjax(xhr, ajaxOptions, thrownError);
					},
					success : function(obj) {
						$("#dialog-working").dialog("close");
						try {
							if (obj.error != undefined && obj.error != "") 
							{
								$("#span_account_client_name").html("");
								$("#account_client_name").val("");
								$("#account_client_id").val("");
								
								$("#span_loc_msg").notify(obj.error,"error");
							} 
							else
							{
								$("#span_account_client_name").html(obj.name);
								$("#account_client_name").val(obj.name);
							}
						} catch (e) {
							$("#span_account_client_name").html("");
							$("#account_client_name").val("");
							$("#account_client_id").val("");
							
							$("#span_loc_msg").notify("Cliente não encontrado","error");
						}
					}
				});
			}else{
				$("#span_account_client_name").html("");
				$("#account_client_name").val("");
			}
			
		});
	};
	var formatGUIEventsBean = function(){
		onEventMatchCodeClient();
		
		$("#button_save").click(function(e) {
			e.preventDefault();
			$("#conta_form").submit();
		});
		$("#button_new").click(function(e) {
			e.preventDefault();
			var url = 'action=edit&windowPopUp=true';
			window.location = url;
		});
		
		$("#button_cancel").click(function(e){
			e.preventDefault();
			if(confirm('Deseja realmente cancelar esta conta?'))
			{
				$action  = $("#conta_form").attr("action"); 
				e.preventDefault();
				$("#conta_form").attr("action","../AccountRegCtrl.ctrlExt/cancelCont");
				$("#conta_form").submit();
				
				$("#conta_form").attr("action",$action);
			}
			
		})
		$("#button_ger_parc").click(function(e) {
			$action  = $("#conta_form").attr("action"); 
			e.preventDefault();
			$("#conta_form").attr("action","../AccountRegCtrl.ctrlExt/incParc");
			$("#conta_form").submit();
			
			$("#conta_form").attr("action",$action);
			
		});
		
		$("#installment_reg_dt").datepicker(System.getDatePickerOp());

		$("#tabs").tabs();
		
		$("#view_parc").dialog({
			autoOpen: false,
			closeOnEscape: true,
			width: 440,
			height: 220,
			modal: true
		});		
		
		setEventsDefaultTableParc();
		
		initialFormHash = $("#conta_form").serialize();
		$("#conta_form").submit(function(){
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
		
		$("#conta_iframe").load(function(){
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
					if(json.html_inc_installment != undefined)
						$("#tbody_installment").html("");
					
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
						if(json.html_inc_installment != undefined){
							$("#tbody_installment").html(json.html_inc_installment);
						}else{	
							$("#button_save").toggle(false);
							if(window.opener!=undefined){
								$("#button_search",window.opener.document).click();
							}
							
							alert(messageText);			
							$("#dialog-working").dialog("open");
							messageText = "";
							var url = 'action=edit&windowPopUp=true&account_id='+json.account_id;
							window.location = url;			
							
						}
						
					}
				}catch(e){
					System.setImgMessage('error');
					messageText = "Erro ao realizar registro<br/><br/>"; 
					messageText += "O sistema esta indisponível no momento, aguarde e tente novamente mais tarde. <br/><br/>"; 
					messageText += "Detalhes técnicos: "+e.message; 
				}
				if(messageText!=""){
					$("#dialog-result\\.messageText").html(messageText);
					$("#dialog-result").dialog("open");
				}
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
		
		initDialogWorking();
		
	};
	
	var formatGUIEventsQuery = function() {
		$("#account_dt_init").datepicker(System.getDatePickerOp());
		$("#account_dt_init").datepicker("option", "defaultDate", "+1w");
		//$("#account_dt_init").datepicker("option", "changeMonth", true);
		//$("#account_dt_init").datepicker("option", "numberOfMonths", 3);
		$("#account_dt_init").datepicker("option", "onClose", function( selectedDate ) {
				 $( "#account_dt_end" ).datepicker( "option", "minDate", selectedDate );
			}
		);
		
		$("#account_dt_end").datepicker(System.getDatePickerOp());
		$("#account_dt_end").datepicker("option", "defaultDate", "+1w");
		//$("#account_dt_end").datepicker("option", "changeMonth", true);
		//$("#account_dt_end").datepicker("option", "numberOfMonths", 3);
		$("#account_dt_end").datepicker("option", "onClose", function( selectedDate ) {
				 $( "#account_dt_init" ).datepicker( "option", "maxDate", selectedDate );
			}
		);
		
		initDialogWorking();
		onEventMatchCodeClient();
		

		pagination = new Pagination();
		pagination.init($("#conta_query_open"));
		
		$("#button_search").click(function(e) {
			e.preventDefault();
			$("#pag").val("1");
			$("#conta_query_open").submit();
		});
		
		$("#button_reset").click(function(e) {
			e.preventDefault();
			$("#conta_query_open .input_clear").val("");
		});
		
		$("#button_new").click(function(e) {
			e.preventDefault();
			
			var param = new Object(); 
			param.width = 640;
			param.height = 620;
			param.fullscreen = 'no';
			param.top = 100;
			param.left = 100;
			param.resizable = 'no';
			param.toolbar = 'yes';
			param.scrollbars='no';
			
			System.openDefaultWindow('/alt_parc/action=edit&windowPopUp=true','Gerenciamento de Contas',param,true);
		});
		
		$("tr.tr_hover td").click(function(e){
			var $trParam = $(this).parent();
			
			$indexFieldDtl = $("tr.tr_hover").index($trParam);
			
			if($indexFieldDtl > -1){		
				var ident = $("input.acc_hidden_id").eq($indexFieldDtl).val();								
				if(ident > 0)
				{			
					var param = new Object(); 
					param.width = 640;
					param.height = 620;
					param.fullscreen = 'no';
					param.top = 100;
					param.left = 100;
					param.resizable = 'no';
					param.toolbar = 'yes';
					param.scrollbars='no';
					
					System.openDefaultWindow('/alt_parc/action=edit&account_id='+ident+'&windowPopUp=true','Gerenciamento de Parcelas',param,true);
				}
				else
				{
					$("#span_loc_msg").notify("Nenhum resultado encontrado!!!", "error");
				}
			}
		});
		
	};
};
AccountBean.instance = null;
AccountBean.getInstance = function() {
	if (AccountBean.intance == null)
		AccountBean.instance = new AccountBean();
	return AccountBean.instance;
};