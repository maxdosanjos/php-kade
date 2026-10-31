var VehicleTravelingBean = function() {
	var saving = false;
	var initialFormHash = "";
	var timeoutSaving = 60; // em segundos 
	var errorFields = new Array();
	var checkChangesBeforeSave = false;
	var veh_sts_dtl_last = "";
	var $indexFieldDtl   = -1;
	var intTimeOut = null;
	
	var address = Address.getInstance();
	
	this.initBean = function() {
		formatGUIEventsBean();
		viewFieldsPerson();
	};
	
	this.initQueryOpen = function(){
		formatGUIEventsQuery( );
	};
	
	var initDialogsQuery = function(){
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
	}
	
	var requestNewCaptcha = function(){
		$("#vehicle_captcha").val("");
		var d = new Date();
		$("#img_captcha").attr('src', $('img').attr('src') +'?a='+ d.getMilliseconds());
	}
	
	var onTimeoutSaving = function(){
		if(saving){
			saving = false;
			$("#dialog-working").dialog("close");
			
			var messageText = "O sistema esta indisponível no momento. <br/>" +
					"Tempo limite de execução de "+timeoutSaving+" segundos atingido, " +
					"tente cadastrar novamente mais tarde.";
			$("#dialog-result\\.messageText").html(messageText);
			$("#dialog-result").dialog("open");
		}
	};
	
	var viewFieldsPerson = function(){
		if ($("#person_pj").attr("checked")) {
			$("#div_fields_pf").toggle(false);
			$("#div_fields_pf input").val("");
			$("#div_fields_pj").toggle(true);
		}else if ($("#person_pf").attr("checked")) {				
			$("#div_fields_pf").toggle(true);
			$("#div_fields_pj").toggle(false);
			$("#div_fields_pj input").val("");
		}
	}
	
	var onCepRequest = function(){
		address.eventCEP(function(){
			$("#vehicle_address").val("");
			$("#vehicle_number").val("");
			$("#vehicle_city").val("");
			$("#vehicle_neighborhood").val("");
			$("#vehicle_region").val("");
			$("#vehicle_region_nm").val("");
		}
		, $("#vehicle_zipcode") 
		, function(response){		
				if(response.result == 1){
					$("#vehicle_address").val(response.data.address);
					$("#vehicle_number").val(response.data.addressNumber);
					$("#vehicle_city").val(response.data.city);
					$("#vehicle_neighborhood").val(response.data.neighborhood);
					$("#vehicle_region").val(response.data.UF);
					$("#vehicle_region_nm").val(response.data.UF_NM);
				}/*else{
					$("#vehicle_zipcode").val("");
					System.showMessageDialog(response.message);
				}*/
		});
	}
	
	var formatGUIEventsQuery = function(){
		pagination = new Pagination();
		pagination.init($("#vehicle_query_open"));
		
		$("#vehicle_dt_init").datepicker(System.getDatePickerOp());
		$("#vehicle_dt_init").datepicker("option", "defaultDate", "+1w");
		//$("#vehicle_dt_init").datepicker("option", "changeMonth", true);
		//$("#vehicle_dt_init").datepicker("option", "numberOfMonths", 3);
		$("#vehicle_dt_init").datepicker("option", "onClose", function( selectedDate ) {
				 $( "#vehicle_dt_end" ).datepicker( "option", "minDate", selectedDate );
			}
		);
		
		$("#vehicle_dt_end").datepicker(System.getDatePickerOp());
		$("#vehicle_dt_end").datepicker("option", "defaultDate", "+1w");
		//$("#vehicle_dt_end").datepicker("option", "changeMonth", true);
		//$("#vehicle_dt_end").datepicker("option", "numberOfMonths", 3);
		$("#vehicle_dt_end").datepicker("option", "onClose", function( selectedDate ) {
				 $( "#vehicle_dt_init" ).datepicker( "option", "maxDate", selectedDate );
			}
		);
		
		onCepRequest();
		initDialogsQuery();
		
		$("#view_details").dialog({
			autoOpen: false,
			closeOnEscape: true,
			dialogClass: "k-dialog",
			width: Math.min(640, $(window).width() - 32),
			height: "auto",
			modal: true
		});
		
		/*$( "#vehicle_city" ).autocomplete({
				minLength: 3,
				source: function( request, response ) {
					$.ajax({
					type:"POST",
					dataType: "json",
					url:System.CONTEXT+"VehicleRegCtrl.ctrlExt/searchCity",
					data:({query:request.term}),
					success: function(obj){
						try
						{
						response($.map(obj,function(item){
							return {label:item.name, value:item.descr};
						}));
						}catch(e){}
					}
				});
			},
			select: function( event, ui ) {
				
			}
		}).keypress(function(event){
			if($(this).val().length <= 2)
			{
				$("#vehicle_zipcode").val("");
				$("#vehicle_region").val("");
			}
		}).blur(function(){
			if($(this).val().length <= 2)
			{
				$("#vehicle_region").val("");
				$("#vehicle_zipcode").val("");
			}
		}).focus(function(){
			if($(this).val()!="")
				$(this).select();
		});	*/
		
		$("#button_search").click(function(e) {
			e.preventDefault();
			$("#pag").val("1");
			$("#vehicle_query_open").submit();
		});
		
		$("#button_reset").click(function(e) {
			e.preventDefault();
			$("#vehicle_query_open .input_clear").val("");
		});
		
		$("#button_csv").click(function(e) {
			$action  = $("#vehicle_query_open").attr("action"); 
			e.preventDefault();
			$("#vehicle_query_open").attr("action","../VehicleQueryCtrl.ctrlExt/exportCSV");
			$("#vehicle_query_open").attr("target","_blank");
			$("#vehicle_query_open").submit();
			
			$action  = $("#vehicle_query_open").attr("action",$action);
			$("#vehicle_query_open").removeAttr("target");
			
		});
		
		$("tr.tr_hover td").click(function(e){
			var url = System.CONTEXT+"VehicleQueryCtrl.ctrlExt/viewDetail";
			var $trParam = $(this).parent();
			
			$indexFieldDtl = $("tr.tr_hover").index($trParam);
			
			if($indexFieldDtl > -1){		
				var ident = $("input.trav_hidden_id").eq($indexFieldDtl).val();				
				var view_type = $("#view_type").val();
				
				if(ident > 0)
				{							
					$("#vehicle_id_detail").val(ident);
					$("#view_type_detail").val(view_type);
					$("#form_view_detail").submit();
				}
				else
				{
					$("#span_loc_msg").notify("Nenhum resultado encontrado!!!", "error");
				}
			}
		});
		
		$("#form_view_detail").submit(function(event){			
			if(saving){
				event.preventDefault();
				return false;
			}
			saving = true;
			$("#dialog-working").dialog("open");
			
			intTimeOut = window.setTimeout(function(){
							onTimeoutSaving();
						},timeoutSaving*1000);
			
			return true;
		});
		
		$("#detail_vec_iframe").load(function(){
			$("#vehicle_id_detail").val("");
			$("#view_type_detail").val("");
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
					
					
					if(json.result == "sucess")
					{
						var title_dialog = "Detalhes: Registro "+json.id;
						// .text(): contato e cidade vêm de cadastros (inclusive por SMS) e não podem ser interpretados como HTML
						$("#div_vehicle_id").text(json.id);

						$("#trav_hidden_id_sel").val(json.id);
						$("#trav_hidden_id_sel_add").val(json.addr_id);

						$("#div_vehicle_dt_proc").text(json.dt_proc);
						$("#div_vehicle_type").text(json.type_id+" - "+json.type_nm);
						$("#div_vehicle_cep").text(json.cep);
						$("#div_vehicle_city").text(json.city);
						$("#div_vehicle_state").text(json.state);
						$("#div_vehicle_contact_phone").text(json.contact_phone);
						$("#div_vehicle_contact_name").text(json.contact_name);
						$("#div_vehicle_source").text(json.source);
						if(json.dt_used !="" )
						{
							$("#label_vehicle_dt_used").toggle(true);
							$("#div_vehicle_dt_used").toggle(true);
							$("#div_vehicle_dt_used").text(json.dt_used);
						}
						else
						{
							$("#label_vehicle_dt_used").toggle(false);
							$("#div_vehicle_dt_used").toggle(false);									
						}
						if(veh_sts_dtl_last != json.sts){
							$("#div_vehicle_sts").removeClass(veh_sts_dtl_last);									
							$("#div_vehicle_sts").addClass(json.sts).text(json.sts_nm);												
							veh_sts_dtl_last = json.sts;
						}
						
						if(json.sts == VehicleTravelingBean.NONE){
							$("#button_check_cancel").css("display","inline");
							//$("#button_check_view").css("display","none");
						}else{
							$("#button_check_cancel").css("display","none");
							//$("#button_check_view").css("display","inline");
						}	
						
						
						$("#view_details").dialog({ title:title_dialog});
						$("#view_details").dialog("open");
					}else{
						throw json;
					}
					
					
				}catch(e){
					System.setImgMessage('error');
					messageText = "Erro ao realizar registro<br/><br/>"; 
					messageText += "O sistema esta indisponível no momento, aguarde e tente novamente mais tarde. <br/><br/>"; 
					messageText += "Detalhes técnicos: "+e.message;
					
					$("#dialog-result\\.messageText").html(messageText);
					$("#dialog-result").dialog("open");
				}
			}
		});
		
		
		$("#button_check_view").click(function(e) {
			e.preventDefault();
			var ident 	   = $("#trav_hidden_id_sel").val();
			var address_id = $("#trav_hidden_id_sel_add").val();
			var url 	   = "../VehicleQueryCtrl.ctrlExt/setStsUtilized";
			
			
			
			if(ident > 0){	
				$(this).css("display","none");
				
				$("#form_setsts").attr("action",url);
				$("#vehicle_id_setsts").val(ident);
				$("#address_id_setsts").val(address_id);
				
				$("#form_setsts").submit();
			}else{
				$("#span_loc_msg").notify("Nenhum resultado encontrado!!!", "error");
			}
		});		
		
		$("#button_check_cancel").click(function(e) {
			e.preventDefault();
			var ident 	   = $("#trav_hidden_id_sel").val();
			var address_id = $("#trav_hidden_id_sel_add").val();
			var url 	   = "../VehicleQueryCtrl.ctrlExt/setStsCancel";
			
			var conf = confirm("Deseja cancelar o registro "+ident+"?");
			
			if(conf && ident > 0){	
				$(this).css("display","none");
				
				$("#form_setsts").attr("action",url);
				$("#vehicle_id_setsts").val(ident);
				$("#address_id_setsts").val(address_id);
				
				$("#form_setsts").submit();
			}else{
				$("#span_loc_msg").notify("Nenhum resultado encontrado!!!", "error");
			}
		});	
		
		$("#button_close_detail").click(function(e) {
			e.preventDefault();
			$("#view_details").dialog("close");
		});
		
		$("#form_setsts").submit(function(event){			
			if(saving){
				event.preventDefault();
				return false;
			}
			saving = true;
			$("#dialog-working").dialog("open");
			
			intTimeOut = window.setTimeout(function(){
							onTimeoutSaving();
						},timeoutSaving*1000);
			
			return true;
		});
		
		$("#setsts_vec_iframe").load(function(){
			$("#vehicle_id_setsts").val("");
			$("#address_id_setsts").val("");
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
					
					
					if(json.result == "sucess")
					{
						$("#view_details").dialog("close");
						$("#msg_type").val("info");
						$("#msg_txt").val("Obrigado por nos informar!!!");
						
						$("#span_loc_msg").notify($("#msg_txt").val(),$("#msg_type").val());
						
						$("input.trav_hidden_id").eq($indexFieldDtl).val("");
						
						$("#button_search").click();
					}else{
						throw json;
					}
					
					
				}catch(e){
					System.setImgMessage('error');
					messageText = "Erro ao realizar registro<br/><br/>"; 
					messageText += "O sistema esta indisponível no momento, aguarde e tente novamente mais tarde. <br/><br/>"; 
					messageText += "Detalhes técnicos: "+e.message;
					
					$("#dialog-result\\.messageText").html(messageText);
					$("#dialog-result").dialog("open");
				}
			}
		});
		
	};
	
	var formatGUIEventsBean = function() {
		$("#button_save").click(function(e) {
			e.preventDefault();
			$("#vehicle_form").submit();
		});
		
		$("#button_back").click(function(e) {
			e.preventDefault();
			window.location = System.CONTEXT;
		});
		$("#button_new").click(function(e) {
			e.preventDefault();
			window.location = System.CONTEXT + "cad_veiculo/";
		});

		$("#tabs").tabs();

		$("#person_pj").live("click", function() {
			viewFieldsPerson();
			
		});
		
		$("#person_pf").live("click", function() {
			viewFieldsPerson();
		});
		
		/*person.eventCNPJ($("#vehicle_cnpj"));		
		person.eventCPF($("#vehicle_cpf"));*/
		
		
		onCepRequest();
		
		initialFormHash = $("#vehicle_form").serialize();
		$("#vehicle_form").submit(function(){
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
				requestNewCaptcha();
				onTimeoutSaving();
			},timeoutSaving*1000);
			
			return true;
		});
		
		$("#vehicle_iframe").load(function(){
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
						$("#vehicle_form .input_clear").val("");
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
		
		initDialogsQuery();
		
		$("#btn_new_captcha").click(function(e){
			e.preventDefault();
			requestNewCaptcha( );
		});

	};
	
};
VehicleTravelingBean.instance = null;
VehicleTravelingBean.getInstance = function() {
	if (VehicleTravelingBean.intance == null)
		VehicleTravelingBean.instance = new VehicleTravelingBean();
	return VehicleTravelingBean.instance;
};

VehicleTravelingBean.ONLY_VIEW = "ONLY_VIEW";
VehicleTravelingBean.UTILIZED = "UTILIZED";
VehicleTravelingBean.NONE = "NONE";
VehicleTravelingBean.CANCEL = "CANCEL";