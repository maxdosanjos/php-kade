<?php 
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/VehicleRegCtrl.class.php");
	$_vechicleTypes = $ctrlVehReg->getTypeVechicles();
	$user = User::getLogged();
	
	$checkedPF = "checked='true'";
	$checkedPJ = "";
	
	if($user!=null)
	{
		if($user->getPerson()->getType() == Person::PJ)
		{
			$checkedPF = "";
			$checkedPJ = "checked='true'";
		}
		else
		{
			$checkedPF = "checked='true'";
			$checkedPJ = "";
		}
	}
	
	$_ufList 		  = $ctrlVehReg->getListUf();
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/cad_veiculo_ext.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/VehicleTravelingBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){VehicleTravelingBean.getInstance().initBean();});</script>
<div class="logo_back"></div>		
<div id="div_body_cad_veic" class="div_body">
	<form id="vehicle_form" method="post" target="vehicle_iframe" action="../VehicleRegCtrl.ctrlExt/save">
			<!-- Anti-bot hidden fields: honeypot and form timestamp (optional client JS can update form_ts) -->
			<input type="hidden" id="hp_name" name="hp_name" value="" />
			<input type="hidden" id="form_ts" name="form_ts" value="<?php echo time(); ?>" />
		<div id="tabs">
			<ul>
		    	<li><a href="#tabs-1">Cadastre seu Veículo</a></li>
		    </ul>
		  	<div id="tabs-1">
				<div class="div_input" style="<?=($user!=null)?"display:none;":""?>">
		    		<label for="person_pf" id="person_pf_label">Pessoa Física</label>
		    		<input type="radio" name="type_person" value="PF" id="person_pf" alt="Pessoa Física" title="Pessoa Física" <?=$checkedPF?> />
		    		<label for="person_pj" id="person_pj_label">Pessoa Jurídica</label>
		    		<input type="radio" name="type_person" value="PJ" id="person_pj" alt="Pessoa Jurídica" title="Pessoa Jurídica" <?=$checkedPJ?>/>
		    	</div>
		    	<div id="div_fields_pf" class="div_input" >
					<div id="div_vehicle_cpf" class="div_input">
						<label for="vehicle_cpf"  class="label_input">CPF: </label>
						<input type="text" id="vehicle_cpf" name="vehicle_cpf" class="mask.cpf required <?=($user!=null && $user->getPerson()->getType() == Person::PF)?"readonly":"input_clear";?>" size="14" maxlength="14" value="<?=($user!=null && $user->getPerson()->getType() == Person::PF)?$user->getPerson()->getCpf():"";?>" alt="CPF" title="CPF" <?=($user!=null && $user->getPerson()->getType() == Person::PF)?"readonly='true'":"";?>/>
					</div>
					<div class="div_input">
			    		<label for="vehicle_name" class="label_input">Nome: </label>
			    		<input type="text" id="vehicle_name" name="vehicle_name" size="40" maxlength="120" class="required <?=($user!=null && $user->getPerson()->getType() == Person::PF)?"readonly":"input_clear";?>" value="<?=($user!=null && $user->getPerson()->getType() == Person::PF)?$user->getPerson()->getName():"";?>" <?=($user!=null && $user->getPerson()->getType() == Person::PF)?"readonly='true'":"";?>/>
			    	</div>
		    	</div>
		    	<div id="div_fields_pj" class="div_input" style="display:none;">
					<div id="div_vehicle_cnpj" class="div_input">
			    		<label for="vehicle_cnpj"  class="label_input">CNPJ: </label>
						<input type="text" id="vehicle_cnpj" name="vehicle_cnpj" class="mask.cnpj required <?=($user!=null && $user->getPerson()->getType() == Person::PJ)?"readonly":"input_clear";?>" size="14" maxlength="14" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?$user->getPerson()->getCnpj():"";?>" alt="CNPJ" title="CNPJ" <?=($user!=null && $user->getPerson()->getType() == Person::PJ)?"readonly='true'":"";?>/>
					</div>
					<div class="div_input">
			    		<label for="vehicle_rsoc"  class="label_input">Razão Social: </label>
			    		<input type="text" id="vehicle_rsoc" name="vehicle_rsoc" size="40" maxlength="120" class="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?"readonly":"input_clear";?>" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?$user->getPerson()->getName():"";?>" <?=($user!=null && $user->getPerson()->getType() == Person::PJ)?"readonly='true'":"";?>/>
			    	</div>
			    	<div class="div_input">
			    		<label for="vehicle_contact"  class="label_input">Contato: </label>
			    		<input type="text" id="vehicle_contact" name="vehicle_contact" size="40" maxlength="120" class="input_clear" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?$user->getPerson()->getContact():"";?>"/>
			    	</div>
				</div>
				<div class="div_input">
					<label for="vehicle_type"  class="label_input">Tipo de Veículo</label>
					<select id="vehicle_type" name="vehicle_type" class="required input_clear">
						<option value="" selected="selected"></option>
						<?php foreach ($_vechicleTypes as $vehicleType){?>
							<option value="<?=$vehicleType->getId()?>" seleted="selected"><?=$vehicleType->getId()?> - <?=$vehicleType->getDescr()?></option>
						<?php } ?>
					</select>
				</div>
		    	<div class="div_input">
		    		<label for="vehicle_phone"  class="label_input">Telefone: </label>
		    		<input type="text" id="vehicle_phone" name="vehicle_phone" size="12" maxlength="11" class="mask.phone2_with_ddd required input_clear" value="<?=($user!=null)?$user->getPerson()->getPhone()->getPhoneMask():"";?>"/>
		    	</div>    	
		    	<br style="clear: both"/>
		    	<fieldset style="border:1px solid #CCC;padding:10px 10px 10px 0;margin-top:10px;">
		    		<legend style="font-style: italic;margin-left:10px;">Localização</legend>
			    	<div class="div_input">
			    		<label for="vehicle_zipcode" id="vehicle_zipcode_label"  class="label_input">CEP: </label>
			    		<input type="text" id="vehicle_zipcode" name="vehicle_zipcode" size="14" maxlength="10" class="mask.cep required input_clear" value=""/>
			    	</div>
			    	<div class="div_input">
			    		<!-- <label for="vehicle_address"  class="label_input">Endereço: </label> -->
			    		<input type="hidden" id="vehicle_address" name="vehicle_address" size="40" maxlength="120" class="input_clear" readonly="readonly" value=""/>
			    		
			    		
			    		<!-- <label for="vehicle_address_number" id="vehicle_address_number_label"  class="label_input">Número: </label>-->
			    		<input type="hidden" id="vehicle_address_number" name="vehicle_address_number" size="10" maxlength="12" class="input_clear"  value=""/>
			    		
			    	</div>
			    	<div class="div_input">
			    		<label for="vehicle_complement"  class="label_input">Complemento: </label>
			    		<input type="text" id="vehicle_complement" name="vehicle_complement" class="input_clear" size="40" maxlength="120" value=""/>
			    		<input type="hidden" id="vehicle_neighborhood" name="vehicle_neighborhood" class="input_clear" />
			    	</div>
			    	<div class="div_input">
			    		<label for="vehicle_city"  class="label_input">Cidade: </label>
			    		<input type="text" id="vehicle_city" name="vehicle_city" size="40" maxlength="120" class="required input_clear"  value=""/>
			    	</div>	
			    	<div class="div_input">
			    		<label for="vehicle_region" id="vehicle_region_label"  class="label_input">UF: </label>
			    		<select id="vehicle_region" name="vehicle_region" class="input_clear required">
							<option value="" selected="selected"></option>
							<?php foreach ($_ufList as $uf=>$uf_name){?>
								<option value="<?=$uf?>"><?=$uf?> - <?=$uf_name?></option>
							<?php } ?>
						</select>
			    		<input type="hidden" id="vehicle_region_nm" name="vehicle_region_nm" class="input_clear"/>
			    	</div>
		    	</fieldset>
		        <div>
		        	<span id="span_text_captcha">Queremos garantir que uma pessoa real está criando um registro.</span>
		        </div>
		    	<div class="div_input">
		    		<img id="img_captcha" src="../VehicleRegCtrl.ctrlExt/getCaptcha"/>
			    	<input type="text" id="vehicle_captcha" name="vehicle_captcha" size="11" maxlength="9" class="required input_clear" value="" autocompleted="false"/>	    		
					<span id="span_exp_cap">***Há diferença letras maiúsculas e minúsculas<br/> Se os caracteres da imagem estiverem ilegíveis, <a id="btn_new_captcha" class="btn_default" href="#">gerar outra imagem</a></span>
		    	</div>
		    	<br style="clear: both"/>
		    	<div id="buttonBar" class="">
					<span id="button_new" class="btn_default" title="Novo registro">
						<div class="div_btn_with_image"></div>
						<span>Novo</span>
					</span>
					<span id="button_back" class="btn_default" title="Voltar">
						<div class="div_btn_with_image"></div>
						<span>Voltar</span>
					</span>
					<button id="button_save" class="btn_default" title="Salvar">
						<div class="div_btn_with_image"></div>
						<span>Salvar</span>
					</button>
				</div>
				
			</div>
		</div>
	</form>
	<iframe id="vehicle_iframe" name="vehicle_iframe" class="iframe_ctrl" src=""></iframe>
	<div id="dialog-result" title="Resultado" style="display:none;">
		<div>
			<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
			<div id="dialog-result.messageText" style="border:1px solid #aaa;padding:10px;margin-top:10px;font-size:14px;"></div>
		</div> 
	</div>	
	<div id="dialog-working" title="Processando" style="display:none">
		<img id="img_dialog" src="../shared/images/dialog-working.gif" />
		<p id="dialog-working.message">Aguarde, processando ...</p>
	</div>
	
	<div id="div_explc" class="ui-state-highlight ui-corner-all">
		<span id="span_icon_explc" class="ui-icon ui-icon-info"></span>
		<p id="p_explc">
			<strong>Obs.:</strong> Você pode enviar a localização de seu caminhão através de um SMS! 
			Basta enviar para o número <span style="font-weight: bold">(43) 9676-1700</span> a mensagem com o seguinte padrão:<br/>
			<span id="span_tp_explc">Tipo de Veículo *(asterisco) CEP</span>
			<span id="span_ex_explc">Exemplo: 1*86188000</span><br>
		</p>
	</div>
</div>
<br style="clear: both"/>
