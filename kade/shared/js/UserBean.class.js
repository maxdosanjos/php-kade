var UserBean = function() {
	var saving = false;
	var initialFormHash = "";
	var timeoutSaving = 60; // em segundos 
	var errorFields = new Array();
	var checkChangesBeforeSave = false;
	
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
	
	this.initConfirmPwd = function(){
		$("#tabs").tabs();
		
		$("#button_save").button().click(function(e) {
			e.preventDefault();
			$("#form_confirm_pwd").submit();
		});
	}
	
	this.initRequestPwd = function(){
		$("#tabs").tabs();

		$("#person_pj").live("click", function() {
			viewFieldsPerson();
			
		});
		
		$("#person_pf").live("click", function() {
			viewFieldsPerson();
		});
		
		$("#button_sender").button().click(function(e) {
			e.preventDefault();
			$("#form_new_password").submit();
		});
		
		initialFormHash = $("#form_new_password").serialize();
		$("#form_new_password").submit(function(){
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
			
			window.setTimeout(function(){
				onTimeoutSaving();
			},timeoutSaving*1000);
			
			return true;
		});
		
		viewFieldsPerson();
		
		$("#newpwd_iframe").load(function(){
			if(saving){
				saving = false;
				$("#dialog-working").dialog("close");
				
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
						$("#form_new_password .input_clear").val("");
					}
				}catch(e){
					System.setImgMessage('error');
					messageText = "Erro ao requisitar nova senha<br/><br/>"; 
					messageText += "O sistema esta indisponível no momento, aguarde e tente novamente mais tarde. <br/><br/>"; 
					messageText += "Detalhes técnicos: "+e.message; 
				}
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
	};
}
UserBean.instance = null;
UserBean.getInstance = function() {
	if (UserBean.intance == null)
		UserBean.instance = new UserBean();
	return UserBean.instance;
};