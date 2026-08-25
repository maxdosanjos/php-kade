var Address = function() {
	var objAfterEdit = undefined;
	
	this.eventCEP = function(funcClear,$input,callback){
		var url = System.CONTEXT+"AddressRegCtrl.ctrlExt/searchCEP";
		
		$input.focus(function(){
			var CEP = $(this).val().replace(/^\s+|\s+$/g, "").replace("-", "");
			
			if(CEP.length != 8){
				return;
			}	
		}).change(function(){			
			var CEP = $(this).val().replace(/^\s+|\s+$/g, "").replace("-", "");
			
			if(CEP.length != 8){
				return;
			}	
		
			// verificando para não carregar o mesmo CEP mais de uma vez				
			if(objAfterEdit!=undefined && objAfterEdit.data.zipcode == CEP){
				callback(objAfterEdit);
				return;
			}					
			
			
			if(CEP !="") funcClear();
			
			System.showWorkingDialog("Aguarde, verificando CEP ...", "Atenção");
			if (CEP != "") 
			{				
				$.ajax({
							type 	 : "POST",
							dataType : "json",
							url 	 : url,
							data 	 : ({
								zipcode : $(this).val()
							}),
							error:function (xhr, ajaxOptions, thrownError){
								System.hideWorkingDialog();
								System.defaultErrorHandlerJQueryAjax(xhr, ajaxOptions, thrownError);
					        },
							success : function(obj) {
								System.hideWorkingDialog();
								if(obj.result==1)
									objAfterEdit = obj;
								callback(obj);
							}
						});
			};
			
		});
	};
};
Address.instance = null;
Address.getInstance = function() {
	if (Address.intance == null)
		Address.instance = new Address();
	return Address.instance;
};