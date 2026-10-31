<?
	/**
	 * Formulário de dados do cliente. Usado em "Meus dados" (data_user.php) e na edição
	 * de cliente pelo administrador (alter_cliente.php). Espera $user, $userSuper e $_ufList.
	 */
	if ($viewExtCtrl == null || $ctrlCustReg == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}

	require_once(dirname(__FILE__)."/partials/helpers.php");
	$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
	$person  = ($user != null) ? $user->getPerson() : null;
	$isPJ    = ($person != null && $person->getType() == Person::PJ);
	$isPF    = ($person != null && !$isPJ);
	// Endereço/telefone podem não existir no cadastro: usa objetos vazios
	$address = ($person != null && $person->getAddress() != null) ? $person->getAddress() : new Address();
	$phone   = ($person != null && $person->getPhone() != null) ? $person->getPhone() : new Phone();
	// Administrador editando outra pessoa: sem captcha, com status e observações
	$editUserList = ($userSuper && $person != null && $person->getId() != User::getLogged()->getPerson()->getId());
	$isento  = ($isPJ && $person->getIe() == PersonEntity::ISENTO);

	$section = "k:flex k:flex-col k:gap-4 k:border-t k:border-line k:pt-6";
	$sectionTitle = "k:font-display k:text-lg k:font-semibold k:text-kade-950";
	$grid2 = "k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2";
?>
<form id="custumer_form" method="post" target="custumer_iframe" action="../CustumerRegCtrl.ctrlExt/saveInter" class="k:flex k:flex-col k:gap-6 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-8">
	<!-- O tipo de pessoa não muda depois do cadastro; os rádios só alimentam o JS -->
	<div style="display:none;">
		<input type="radio" name="type_person" value="PF" id="person_pf" <?=$isPJ ? "" : "checked"?>/>
		<input type="radio" name="type_person" value="PJ" id="person_pj" <?=$isPJ ? "checked" : ""?>/>
	</div>
	<input type="hidden" id="custumer_person_id" name="custumer_person_id" value="<?=$person != null ? $h($person->getId()) : ""?>"/>
	<input type="hidden" id="custumer_address_id" name="custumer_address_id" value="<?=$h($address->getId())?>"/>
	<input type="hidden" id="custumer_phone_id" name="custumer_phone_id" value="<?=$h($phone->getId())?>"/>

	<div class="k:flex k:flex-col k:gap-4">
		<h2 class="<?=$sectionTitle?>"><?=$isPJ ? "Dados da empresa" : "Dados pessoais"?></h2>
		<div id="div_fields_pf"<?=$isPJ ? ' style="display:none;"' : ""?>>
			<div class="<?=$grid2?>">
				<label class="k-label" for="custumer_cpf">CPF
					<input type="text" id="custumer_cpf" name="custumer_cpf" maxlength="14" readonly class="k-field readonly input_per" value="<?=$isPF ? $h(k_fmt_doc($person->getCpf())) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_name">Nome completo
					<input type="text" id="custumer_name" name="custumer_name" maxlength="120" class="k-field required input_per" autocomplete="name" value="<?=$isPF ? $h($person->getName()) : ""?>"/>
				</label>
			</div>
		</div>
		<div id="div_fields_pj"<?=$isPJ ? "" : ' style="display:none;"'?>>
			<div class="<?=$grid2?>">
				<label class="k-label" for="custumer_cnpj">CNPJ
					<input type="text" id="custumer_cnpj" name="custumer_cnpj" maxlength="18" readonly class="k-field readonly input_per" value="<?=$isPJ ? $h(k_fmt_doc($person->getCnpj())) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_rsoc">Razão social
					<input type="text" id="custumer_rsoc" name="custumer_rsoc" maxlength="120" class="k-field required input_per" value="<?=$isPJ ? $h($person->getName()) : ""?>"/>
				</label>
				<div class="k-label">
					<label for="custumer_ie">Inscrição estadual</label>
					<input type="text" id="custumer_ie" name="custumer_ie" maxlength="18" inputmode="numeric" class="k-field mask.numeric required input_per" value="<?=($isPJ && !$isento) ? $h($person->getIe()) : ""?>"/>
					<label id="custumer_ie_isento_label" class="k:mt-2 k:inline-flex k:cursor-pointer k:items-center k:gap-2 k:text-sm k:font-normal k:text-ink-soft">
						<input type="checkbox" id="custumer_ie_isento" name="custumer_ie_isento" value="on" class="k:h-4 k:w-4 k:accent-kade-900" <?=$isento ? "checked" : ""?>/> Isento
					</label>
				</div>
				<label class="k-label" for="custumer_contact">Nome do contato
					<input type="text" id="custumer_contact" name="custumer_contact" maxlength="120" class="k-field input_per" value="<?=$isPJ ? $h($person->getContact()) : ""?>"/>
				</label>
			</div>
		</div>
	</div>

	<div class="<?=$section?>">
		<h2 class="<?=$sectionTitle?>">Contato e acesso</h2>
		<div class="<?=$grid2?>">
			<label class="k-label" for="custumer_phone">Telefone
				<input type="tel" id="custumer_phone" name="custumer_phone" maxlength="15" class="k-field mask.phone2_with_ddd required" autocomplete="tel" value="<?=$h($phone->getPhoneMask())?>"/>
			</label>
			<label class="k-label" for="custumer_mail">E-mail
				<input type="email" id="custumer_mail" name="custumer_mail" maxlength="255" class="k-field mask.mail required" autocomplete="email" value="<?=$person != null ? $h($person->getEmail()) : ""?>"/>
			</label>
			<label class="k-label" for="custumer_login">Login
				<input type="text" id="custumer_login" name="custumer_login" maxlength="20" readonly class="k-field readonly" value="<?=$user != null ? $h($user->getLogin()) : ""?>"/>
			</label>
			<div class="k-label">
				<span>Senha</span>
				<a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="k:mt-2 k:flex k:min-h-11 k:items-center k:text-[15px] k:font-semibold k:text-kade-900 k:hover:text-kade-800">Pedir link para trocar a senha</a>
			</div>
		</div>
	</div>

	<div class="<?=$section?>">
		<h2 class="<?=$sectionTitle?>">Endereço</h2>
		<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-6">
			<label class="k-label k:sm:col-span-2" for="custumer_zipcode" id="custumer_zipcode_label">CEP
				<input type="text" id="custumer_zipcode" name="custumer_zipcode" maxlength="10" inputmode="numeric" class="k-field mask.cep required" autocomplete="postal-code" value="<?=$h($address->getCep())?>"/>
			</label>
			<label class="k-label k:sm:col-span-4" for="custumer_address">Logradouro
				<input type="text" id="custumer_address" name="custumer_address" maxlength="120" class="k-field" autocomplete="address-line1" value="<?=$h($address->getStreet())?>"/>
			</label>
			<label class="k-label k:sm:col-span-2" for="custumer_address_number" id="custumer_address_number_label">Número
				<input type="text" id="custumer_address_number" name="custumer_address_number" maxlength="12" class="k-field required" value="<?=$h($address->getNumber())?>"/>
			</label>
			<label class="k-label k:sm:col-span-4" for="custumer_complement">Complemento
				<input type="text" id="custumer_complement" name="custumer_complement" maxlength="120" class="k-field" autocomplete="address-line2" value="<?=$h($address->getComplement())?>"/>
			</label>
			<label class="k-label k:sm:col-span-2" for="custumer_neighborhood" id="custumer_neighborhood_label">Bairro
				<input type="text" id="custumer_neighborhood" name="custumer_neighborhood" maxlength="120" class="k-field" value="<?=$h($address->getNeighborhood())?>"/>
			</label>
			<label class="k-label k:sm:col-span-3" for="custumer_city">Cidade
				<input type="text" id="custumer_city" name="custumer_city" maxlength="120" class="k-field required" autocomplete="address-level2" value="<?=$h($address->getCity())?>"/>
			</label>
			<label class="k-label k:sm:col-span-1" for="custumer_region" id="custumer_region_label">UF
				<select id="custumer_region" name="custumer_region" class="k-field required" autocomplete="address-level1">
					<option value=""></option>
					<?php foreach ($_ufList as $uf=>$uf_name){?>
					<option value="<?=$h($uf)?>" title="<?=$h($uf_name)?>" <?=($address->getState() == $uf) ? "selected" : ""?>><?=$h($uf)?></option>
					<?php } ?>
				</select>
			</label>
			<input type="hidden" id="custumer_region_nm" name="custumer_region_nm"/>
		</div>
	</div>

	<?php if ($editUserList) { ?>
	<div class="<?=$section?>">
		<h2 class="<?=$sectionTitle?>">Administração</h2>
		<div class="<?=$grid2?>">
			<label class="k-label" for="custumer_status">Status do usuário
				<select id="custumer_status" name="custumer_status" class="k-field">
					<?php foreach (User::listStatus() as $status=>$statusNm){?>
					<option value="<?=$h($status)?>" <?=($user->getStatus() == $status) ? "selected" : ""?>><?=$h($statusNm)?></option>
					<?php } ?>
				</select>
			</label>
		</div>
		<label class="k-label" for="custumer_obs">Observações internas
			<textarea id="custumer_obs" name="custumer_obs" rows="6" class="k-field k:py-3"><?=$h($user->getText())?></textarea>
		</label>
	</div>
	<?php } else { ?>
	<div class="<?=$section?>">
		<h2 class="<?=$sectionTitle?>">Verificação</h2>
		<p class="k:text-sm k:text-muted">Para sua segurança, confirme o código abaixo antes de salvar. Maiúsculas e minúsculas fazem diferença.</p>
		<div class="k:flex k:flex-wrap k:items-end k:gap-4">
			<img id="img_captcha" src="../CustumerRegCtrl.ctrlExt/getCaptcha" alt="Código de verificação" class="k:h-[46px] k:rounded-[10px] k:border k:border-line"/>
			<label class="k-label k:w-40" for="custumer_captcha">Código
				<input type="text" id="custumer_captcha" name="custumer_captcha" maxlength="9" class="k-field required" autocomplete="off" value=""/>
			</label>
			<a id="btn_new_captcha" href="#" class="k:inline-flex k:min-h-11 k:items-center k:text-sm k:font-semibold k:text-kade-900 k:hover:text-kade-800">Gerar outro código</a>
		</div>
	</div>
	<?php } ?>

	<div id="buttonBar" class="k:flex k:justify-end k:gap-2.5 k:border-t k:border-line k:pt-6">
		<button type="submit" id="button_save" class="k-btn k-btn-primary">Salvar alterações</button>
	</div>
</form>

<iframe id="custumer_iframe" name="custumer_iframe" class="iframe_ctrl" src="" title="Envio dos dados" hidden></iframe>
<div id="dialog-result" title="Resultado" style="display:none;">
	<div>
		<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
		<div id="dialog-result.messageText"></div>
	</div>
</div>
<div id="dialog-working" title="Processando" style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" alt="" />
	<p id="dialog-working.message">Aguarde, processando ...</p>
</div>
