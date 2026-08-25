var Person = function() {
	var CNPJBeforeEdit = "";
	var CNPJAfterEdit = "";
	
	var CPFBeforeEdit = "";
	var CPFAfterEdit = "";
	
	this.eventCNPJ = function($input){
		
		$input.focus(function(){
			var CNPJ = $(this).val();
			if(CNPJ.length != 18){
				return;
			}
			CNPJBeforeEdit = CNPJ;
		}).blur(function(){			
			var CNPJ = $(this).val();
			if(CNPJ.length != 18){
				return;
			}
			
			CNPJAfterEdit = CNPJ;
			
			// verificando para não carregar o mesmo CNPJ mais de uma vez
			if(CNPJBeforeEdit == CNPJAfterEdit){
				return;
			}
			
			System.showWorkingDialog("Aguarde, verificando CNPJ ...", "Atenção");
			var url = System.CONTEXT+"VehicleRegCtrl.ctrlExt/searchCNPJ/"+CNPJ;
			System.irequest(url,function(output){
				System.hideWorkingDialog();
				try {
					var json = $.parseJSON(output);
					if(json.result == 1){
						/*if(json.exists){
							System.showWorkingDialog("O CNPJ "+CNPJ+" já existe, carregando dados ...");
							window.setTimeout(function(){
								window.location = System.CONTEXT+"sprod/servlet/SupplierServlet.class.php?action=edit&CNPJ="+CNPJ 
							},1);
						}*/
					}else{
						System.showMessageDialog(json.message);
					}
				}catch(e){
					System.showMessageDialog("Erro em verificar CNPJ: "+e.message);
				}
			},function(){
				// timeout
				System.hideWorkingDialog();
			});
		});
	};
	
	this.eventCPF = function($input){
		$input.focus(function(){
			var CPF = $(this).val();
			if(CPF.length != 14){
				return;
			}
			CPFBeforeEdit = CPF;
		}).blur(function(){
			var CPF = $(this).val();
			if(CPF.length != 14){
				return;
			}
			
			CPFAfterEdit = CPF;
			
			// verificando para não carregar o mesmo CNPJ mais de uma vez
			if(CPFBeforeEdit == CPFAfterEdit){
				return;
			}
			
			System.showWorkingDialog("Aguarde, verificando CPF ...", "Atenção");
			var url = System.CONTEXT+"VehicleRegCtrl.ctrlExt/searchCPF/"+CPF;
			System.irequest(url,function(output){
				System.hideWorkingDialog();
				try {
					var json = $.parseJSON(output);
					if(json.result == 1){
						/*if(json.exists){
							System.showWorkingDialog("O CPF "+CPF+" já existe, carregando dados ...");
							window.setTimeout(function(){
								window.location = System.CONTEXT+"sprod/servlet/SupplierServlet.class.php?action=edit&CNPJ="+CPF 
							},1);
						}*/
					}else{
						System.showMessageDialog(json.message);
					}
				}catch(e){
					System.showMessageDialog("Erro em verificar CPF: "+e.message);
				}
			},function(){
				// timeout
				System.hideWorkingDialog();
			});
		});
	}
};
Person.instance = null;
Person.getInstance = function() {
	if (Person.intance == null)
		Person.instance = new Person();
	return Person.instance;
};