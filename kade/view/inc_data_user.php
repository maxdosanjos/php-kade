<?
	if ($viewExtCtrl == null || $ctrlCustReg == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
	
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
	
	$editUserList = ($userSuper && $user->getPerson()->getId()!= User::getLogged()->getPerson()->getId());
?>
<div id="div_body_cad_clie">
	<form id="custumer_form" method="post" target="custumer_iframe" action="../CustumerRegCtrl.ctrlExt/saveInter">
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1" alt="Dados de Usuário" title="Dados de Usuário">Dados de Usuário</a></li>
				<? if($editUserList){?>
				<li><a href="#tabs-2" alt="Observações" title="Observações">Observações</a></li>
				<?php } ?>
			</ul>
			<div id="tabs-1">
				<div class="div_input" style="<?=($user!=null)?"display:none;":""?>">
					<label for="person_pf" id="person_pf_label" alt="Pessoa Física" title="Pessoa Física">Pessoa Física</label>
					<input type="radio" name="type_person" value="PF" id="person_pf" alt="Pessoa Física" title="Pessoa Física" <?=$checkedPF?>/>
					<label for="person_pj" id="person_pj_label" alt="Pessoa Jurídica" title="Pessoa Jurídica">Pessoa Jurídica</label>
					<input type="radio" name="type_person" value="PJ" id="person_pj" alt="Pessoa Jurídica" title="Pessoa Jurídica" <?=$checkedPJ?>/>
				</div>
				<div id="div_fields_pf" class="div_input" >
					<div id="div_custumer_cpf" class="div_input">
						<label for="custumer_cpf"  class="label_input" alt="CPF" title="CPF">CPF: </label>
						<input type="text" id="custumer_cpf" name="custumer_cpf" class="mask.cpf required input_per  <?=($user!=null && $user->getPerson()->getType() == Person::PF)?"readonly":"";?>" size="14" maxlength="14" value="<?=($user!=null && $user->getPerson()->getType() == Person::PF)?$user->getPerson()->getCpf():"";?>" alt="CPF" title="CPF" <?=($user!=null && $user->getPerson()->getType() == Person::PF)?"readonly='true'":"";?>/>
					</div>
					<div class="div_input">
						<label for="custumer_name" class="label_input" alt="Nome" title="Nome">Nome: </label>
						<input type="text" id="custumer_name" name="custumer_name" size="40" maxlength="120" class="required input_per" value="<?=($user!=null && $user->getPerson()->getType() == Person::PF)?$user->getPerson()->getName():"";?>" alt="Nome" title="Nome"/>
					</div>
				</div>
				<div id="div_fields_pj" class="div_input" style="display:none;">
					<div id="div_custumer_cnpj" class="div_input">
						<label for="custumer_cnpj"  class="label_input" alt="CNPJ" title="CNPJ">CNPJ: </label>
						<input type="text" id="custumer_cnpj" name="custumer_cnpj" class="mask.cnpj required input_per <?=($user!=null && $user->getPerson()->getType() == Person::PJ)?"readonly":"";?>" size="14" maxlength="14" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?$user->getPerson()->getCnpj():"";?>" alt="CNPJ" title="CNPJ" <?=($user!=null && $user->getPerson()->getType() == Person::PJ)?"readonly='true'":"";?> />
					</div>
					<div class="div_input">
						<label for="custumer_rsoc"  class="label_input" alt="Razão Social" title="Razão Social">Razão Social: </label>
						<input type="text" id="custumer_rsoc" name="custumer_rsoc" size="40" maxlength="120" class="required input_per" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?$user->getPerson()->getName():"";?>" alt="Razão Social" title="Razão Social"/>
					</div>
					<div class="div_input">
						<label for="custumer_ie"  class="label_input" alt="Inscrição Estadual" title="Inscrição Estadual">I.E.: </label>
						<input type="text" id="custumer_ie" name="custumer_ie" size="12" maxlength="18" class="mask.numeric required input_per" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ && $user->getPerson()->getIe() != PersonEntity::ISENTO)?$user->getPerson()->getIe():""?>" alt="Inscrição Estadual" title="Inscrição Estadual"/>
						<label id="custumer_ie_isento_label" alt="Inscrição Estadual = Isento" title="Inscrição Estadual = Isento">
							<input type="checkbox" id="custumer_ie_isento" name="custumer_ie_isento" value="on" <?=($user!=null && $user->getPerson()->getType() == Person::PJ && $user->getPerson()->getIe() == PersonEntity::ISENTO)?"checked='true'":""?>/>Isento
						</label>
					</div>
					<div class="div_input">
						<label for="custumer_contact"  class="label_input" alt="Contato" title="Contato">Contato: </label>
						<input type="text" id="custumer_contact" name="custumer_contact" size="40" maxlength="120" class="input_per" value="<?=($user!=null && $user->getPerson()->getType() == Person::PJ)?$user->getPerson()->getContact():"";?>" alt="Contato" title="Contato"/>
					</div>
				</div>
				
				<input type="hidden" id="custumer_person_id" name="custumer_person_id" class="" value="<?=($user!=null)?$user->getPerson()->getId():"";?>"/>
				<input type="hidden" id="custumer_address_id" name="custumer_address_id" class="" value="<?=($user!=null)?$user->getPerson()->getAddress()->getId():"";?>"/>
				<input type="hidden" id="custumer_phone_id" name="custumer_phone_id" class="" value="<?=($user!=null)?$user->getPerson()->getPhone()->getId():"";?>"/>
				
				<div class="div_input">
					<label for="custumer_phone"  class="label_input" alt="Telefone" title="Telefone">Telefone: </label>
					<input type="text" id="custumer_phone" name="custumer_phone" size="12" maxlength="11" class="mask.phone2_with_ddd required " value="<?=($user!=null)?$user->getPerson()->getPhone()->getPhoneMask():"";?>" alt="Telefone" title="Telefone"/>
				</div>   
				<div class="div_input">
					<label for="custumer_mail"  class="label_input" alt="Email" title="Email">Email: </label>
					<input type="text" id="custumer_mail" name="custumer_mail" size="50" maxlength="255" class="mask.mail required " value="<?=($user!=null)?$user->getPerson()->getEmail():"";?>" alt="Email" title="Email"/>
				</div>   	
				<div class="div_input">
					<label for="custumer_login"  class="label_input" alt="Login" title="Login">Login: </label>
					<input type="text" id="custumer_login" name="custumer_login" size="16" maxlength="20" class="mask.login required  <?=($user!=null)?"readonly":"";?>" value="<?=($user!=null)?$user->getLogin():"";?>" <?=($user!=null)?"readonly='true'":"";?> alt="Login" title="Login"/>
				</div>   	
				<div class="div_input">
					<label for="custumer_password"  class="label_input" alt="Senha" title="Senha">Senha: </label>
					<a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" id="custumer_password" class="link-cinza4"> Requisitar nova senha?</a>
				</div>   					
				<br style="clear: both"/>
				<fieldset id="fieldset_end">
					<legend alt="Endereço" title="Endereço">Endereço</legend>
					<div class="div_input">
						<label for="custumer_zipcode" id="custumer_zipcode_label"  class="label_input" alt="CEP" title="CEP">CEP: </label>
						<input type="text" id="custumer_zipcode" name="custumer_zipcode" size="14" maxlength="10" class="mask.cep required " value="<?=($user!=null)?$user->getPerson()->getAddress()->getCep():"";?>" alt="CEP" title="CEP"/>
					</div>
					<div class="div_input">
						<label for="custumer_address"  class="label_input" alt="Logradouro" title="Logradouro">Logradouro: </label>
						<input type="text" id="custumer_address" name="custumer_address" size="40" maxlength="120" class="" value="<?=($user!=null)?$user->getPerson()->getAddress()->getStreet():"";?>" alt="Logradouro" title="Logradouro"/>
					</div>
					<div class="div_input">						
						<label for="custumer_neighborhood" id="custumer_neighborhood_label" class="label_input" alt="Bairro" title="Bairro">Bairro: </label>
						<input type="text" id="custumer_neighborhood" name="custumer_neighborhood" class="" value="<?=($user!=null)?$user->getPerson()->getAddress()->getNeighborhood():"";?>" alt="Bairro" title="Bairro"/>
					</div>	
					<div class="div_input">	
						<label for="custumer_address_number" id="custumer_address_number_label"  class="label_input" alt="Número" title="Número">Número: </label>
						<input type="text" id="custumer_address_number" name="custumer_address_number" size="10" maxlength="12" class="required "  value="<?=($user!=null)?$user->getPerson()->getAddress()->getNumber():"";?>" alt="Número" title="Número"/>		    		
					</div>
					<div class="div_input">						
						<label for="custumer_complement"  class="label_input" alt="Complemento" title="Complemento">Complemento: </label>
						<input type="text" id="custumer_complement" name="custumer_complement" class="" size="40" maxlength="120" value="<?=($user!=null)?$user->getPerson()->getAddress()->getComplement():"";?>" alt="Complemento" title="Complemento"/>
					</div>
					<div class="div_input">
						<label for="custumer_city"  class="label_input" alt="Cidade" title="Cidade">Cidade: </label>
						<input type="text" id="custumer_city" name="custumer_city" size="40" maxlength="120" class="required" value="<?=($user!=null)?$user->getPerson()->getAddress()->getCity():"";?>" alt="Cidade" title="Cidade"/>
					</div>	
					<div class="div_input">						
						<label for="custumer_region" id="custumer_region_label"  class="label_input" alt="UF" title="UF">UF: </label>
						<select id="custumer_region" name="custumer_region" class="required">
							<?php foreach ($_ufList as $uf=>$uf_name){?>
							<option value="<?=$uf?>" <?=($user!=null && $user->getPerson()->getAddress()->getState() == $uf)?"selected='true'":"";?> ><?=$uf?> - <?=$uf_name?></option>
							<?php } ?>
						</select>
						<input type="hidden" id="custumer_region_nm" name="custumer_region_nm" class=""/>
					</div>
				</fieldset>
				<? if($editUserList){?>
				<br/>
				<div class="div_input">
					<label for="custumer_status"  class="label_input" title="Status" alt="Status">Status:</label>
					<select id="custumer_status" name="custumer_status" class="">
						<?php foreach (User::listStatus() as $status=>$statuNm){?>
							<option value="<?=$status?>" <?=($user->getStatus() == $status)?"selected='true'":""?>><?=$statuNm?></option>
						<?php } ?>
					</select>
				</div>
				<br/>	
				<br style="clear: both"/>
				<? } else {?>
				<div>
					<span id="span_text_captcha" alt="Queremos garantir que uma pessoa real está criando um registro." title="Queremos garantir que uma pessoa real está criando um registro.">Queremos garantir que uma pessoa real está editando seu registro.</span>
				</div>
				<div class="div_input">
					<img id="img_captcha" src="../CustumerRegCtrl.ctrlExt/getCaptcha" alt="Código" title="Código"/>
					<input type="text" id="custumer_captcha" name="custumer_captcha" size="11" maxlength="9" class="required " value="" autocomplete="false" alt="Código" title="Código"/>	    								
					<span id="span_exp_cap" alt="Há diferença letras maiúsculas e minúsculas" title="Há diferença letras maiúsculas e minúsculas">
						***Há diferença letras maiúsculas e minúsculas <br/> Se os caracteres da imagem estiverem ilegíveis, <a id="btn_new_captcha" class="btn_default" href="#">gerar outra imagem</a>
					</span>
				</div>
				<?php } ?>
				<br style="clear: both"/>
				<div id="buttonBar" class="">
					<button id="button_save" class="btn_default" title="Salvar" alt="Salvar">
						<div class="div_btn_with_image"></div>
						<span>Salvar</span>
					</button>
				</div>
			</div>
			<? if($editUserList){?>
			<div id="tabs-2">
				<textarea rows="32" cols="90" name="custumer_obs"><?=$user->getText()?></textarea>
			</div>
			<?php } ?>
		</div>
	</form>
	<iframe id="custumer_iframe" name="custumer_iframe" class="iframe_ctrl" src=""></iframe>
	<div id="dialog-result" title="Resultado" alt="Resultado" style="display:none;">
		<div>
			<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
			<div id="dialog-result.messageText" style="border:1px solid #aaa;padding:10px;margin-top:10px;font-size:14px;"></div>
		</div> 
	</div>	
	<div id="dialog-working" title="Processando" alt="Aguarde, processando ..." style="display:none">
		<img id="img_dialog" src="../shared/images/dialog-working.gif" />
		<p id="dialog-working.message">Aguarde, processando ...</p>
	</div>
</div>