<?php
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/VehicleRegCtrl.class.php");
	$_vechicleTypes = $ctrlVehReg->getTypeVechicles();
	$_ufList        = $ctrlVehReg->getListUf();
	$_msgLinks      = Messenger::links();
	$user = User::getLogged();

	$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
	// Logado: os dados da pessoa vêm do cadastro e ficam travados
	$isPF = ($user != null && $user->getPerson()->getType() == Person::PF);
	$isPJ = ($user != null && $user->getPerson()->getType() == Person::PJ);
	$lockAttr = ' readonly class="k-field required readonly"';
	require_once(dirname(__FILE__)."/partials/helpers.php");
	$grid2 = "k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2";
	$section = "k:flex k:flex-col k:gap-4 k:border-t k:border-line k:pt-6";
	$sectionTitle = "k:font-display k:text-lg k:font-semibold k:text-kade-950";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/VehicleTravelingBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/VehicleTravelingBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){VehicleTravelingBean.getInstance().initBean();});</script>

<div class="k-ui k:px-4 k:py-10 k:text-ink k:md:px-6 k:md:py-14">
	<div class="k:mx-auto k:max-w-[1100px]">
		<div class="k:mb-8 k:flex k:flex-col k:gap-1.5">
			<h1 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]">Cadastre seu veículo</h1>
			<p class="k:text-base k:text-muted">Informe onde seu caminhão está disponível e receba contatos de embarcadores.</p>
		</div>

		<div class="k:flex k:flex-wrap k:items-start k:gap-6">
			<!-- Envio por mensagem: SMS, WhatsApp ou Telegram (os botões só aparecem quando configurados no .env) -->
			<aside class="k:flex k:min-w-0 k:flex-[1_1_300px] k:flex-col k:gap-4 k:rounded-2xl k:bg-kade-900 k:p-6 k:text-white k:md:p-7 k:lg:sticky k:lg:top-24 k:lg:order-2">
				<span class="k:inline-flex k:h-11 k:w-11 k:items-center k:justify-center k:rounded-xl k:bg-white/10">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ffb766" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				</span>
				<h2 class="k:font-display k:text-xl k:font-semibold">Prefere mandar por mensagem?</h2>
				<p class="k:text-[15px] k:leading-relaxed k:text-[#d5deeb]">Envie por SMS para <strong class="k:text-white k:whitespace-nowrap"><?=Messenger::SMS_NUMBER?></strong>, ou pelo WhatsApp ou Telegram, uma mensagem com o código do tipo de veículo, um asterisco e o CEP onde ele está:</p>
				<div class="k:rounded-xl k:bg-white/10 k:p-4 k:text-[15px]">
					<span class="k:block k:text-[13px] k:text-[#d5deeb]">Formato</span>
					<code class="k:font-semibold k:text-white">tipo*CEP</code>
					<span class="k:mt-2 k:block k:text-[13px] k:text-[#d5deeb]">Exemplo</span>
					<code class="k:font-semibold k:text-white">1*86188000</code>
				</div>
				<p class="k:text-[13px] k:text-[#d5deeb]">O código do tipo é o número que aparece antes do nome na lista "Tipo de veículo".</p>
				<?php if ($_msgLinks) { ?>
				<div class="k:flex k:flex-col k:gap-3 k:border-t k:border-white/15 k:pt-4">
					<p class="k:text-[15px] k:leading-relaxed k:text-[#d5deeb]">Pelo <?=Messenger::linkNames($_msgLinks)?>, você recebe a resposta na hora:</p>
					<div class="k:flex k:flex-wrap k:gap-2">
						<?php foreach ($_msgLinks as $channel => $url) { ?>
						<a href="<?=$h($url)?>" target="_blank" rel="noopener" class="k-btn k-btn-primary"><?=Messenger::channels()[$channel]?></a>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</aside>

			<!-- Formulário -->
			<form id="vehicle_form" method="post" target="vehicle_iframe" action="../VehicleRegCtrl.ctrlExt/save" class="k:flex k:min-w-0 k:flex-[999_1_560px] k:flex-col k:gap-6 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-8 k:lg:order-1">
				<!-- Anti-robô: campo invisível (robôs preenchem) e horário de abertura do formulário -->
				<div class="k:absolute k:-left-[9999px] k:h-px k:w-px k:overflow-hidden" aria-hidden="true">
					<label for="hp_name">Não preencha</label>
					<input type="text" id="hp_name" name="hp_name" value="" tabindex="-1" autocomplete="off" />
				</div>
				<input type="hidden" id="form_ts" name="form_ts" value="<?php echo time(); ?>" />

				<fieldset class="k:m-0 k:border-0 k:p-0"<?=$user != null ? ' style="display:none;"' : ""?>>
					<legend class="k:mb-1.5 k:text-sm k:font-medium">Tipo de cadastro</legend>
					<div class="k:grid k:max-w-md k:grid-cols-2 k:gap-1 k:rounded-xl k:bg-surface k:p-1">
						<input type="radio" name="type_person" value="PF" id="person_pf" class="k:peer/pf k:sr-only" <?=$isPJ ? "" : "checked"?> />
						<label for="person_pf" id="person_pf_label" class="k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:text-sm k:font-semibold k:text-muted k:peer-checked/pf:bg-white k:peer-checked/pf:text-kade-900 k:peer-checked/pf:shadow-sm k:peer-focus-visible/pf:ring-2 k:peer-focus-visible/pf:ring-accent">Pessoa física</label>
						<input type="radio" name="type_person" value="PJ" id="person_pj" class="k:peer/pj k:sr-only" <?=$isPJ ? "checked" : ""?> />
						<label for="person_pj" id="person_pj_label" class="k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:text-sm k:font-semibold k:text-muted k:peer-checked/pj:bg-white k:peer-checked/pj:text-kade-900 k:peer-checked/pj:shadow-sm k:peer-focus-visible/pj:ring-2 k:peer-focus-visible/pj:ring-accent">Pessoa jurídica</label>
					</div>
				</fieldset>

				<div id="div_fields_pf">
					<div class="<?=$grid2?>">
						<label class="k-label" for="vehicle_cpf">CPF
							<?php if ($isPF) { ?>
							<input type="text" id="vehicle_cpf" name="vehicle_cpf" maxlength="14" value="<?=$h(k_fmt_doc($user->getPerson()->getCpf()))?>"<?=$lockAttr?>/>
							<?php } else { ?>
							<input type="text" id="vehicle_cpf" name="vehicle_cpf" maxlength="14" inputmode="numeric" class="k-field mask.cpf required input_clear" value=""/>
							<?php } ?>
						</label>
						<label class="k-label" for="vehicle_name">Nome
							<?php if ($isPF) { ?>
							<input type="text" id="vehicle_name" name="vehicle_name" maxlength="120" value="<?=$h($user->getPerson()->getName())?>"<?=$lockAttr?>/>
							<?php } else { ?>
							<input type="text" id="vehicle_name" name="vehicle_name" maxlength="120" class="k-field required input_clear" autocomplete="name" value=""/>
							<?php } ?>
						</label>
					</div>
				</div>
				<div id="div_fields_pj" style="display:none;">
					<div class="<?=$grid2?>">
						<label class="k-label" for="vehicle_cnpj">CNPJ
							<?php if ($isPJ) { ?>
							<input type="text" id="vehicle_cnpj" name="vehicle_cnpj" maxlength="18" value="<?=$h(k_fmt_doc($user->getPerson()->getCnpj()))?>"<?=$lockAttr?>/>
							<?php } else { ?>
							<input type="text" id="vehicle_cnpj" name="vehicle_cnpj" maxlength="18" inputmode="numeric" class="k-field mask.cnpj required input_clear" value=""/>
							<?php } ?>
						</label>
						<label class="k-label" for="vehicle_rsoc">Razão social
							<?php if ($isPJ) { ?>
							<input type="text" id="vehicle_rsoc" name="vehicle_rsoc" maxlength="120" value="<?=$h($user->getPerson()->getName())?>" readonly class="k-field readonly"/>
							<?php } else { ?>
							<input type="text" id="vehicle_rsoc" name="vehicle_rsoc" maxlength="120" class="k-field input_clear" autocomplete="organization" value=""/>
							<?php } ?>
						</label>
						<label class="k-label k:sm:col-span-2" for="vehicle_contact">Nome do contato
							<input type="text" id="vehicle_contact" name="vehicle_contact" maxlength="120" class="k-field input_clear" value="<?=$isPJ ? $h($user->getPerson()->getContact()) : ""?>"/>
						</label>
					</div>
				</div>

				<div class="<?=$grid2?>">
					<label class="k-label" for="vehicle_type">Tipo de veículo
						<select id="vehicle_type" name="vehicle_type" class="k-field required input_clear">
							<option value="">Selecione</option>
							<?php foreach ($_vechicleTypes as $vehicleType){?>
								<option value="<?=$h($vehicleType->getId())?>"><?=$h($vehicleType->getId())?> - <?=$h($vehicleType->getDescr())?></option>
							<?php } ?>
						</select>
					</label>
					<label class="k-label" for="vehicle_phone">Telefone para contato
						<input type="tel" id="vehicle_phone" name="vehicle_phone" maxlength="15" class="k-field mask.phone2_with_ddd required input_clear" autocomplete="tel" value="<?=$user != null ? $h($user->getPerson()->getPhone()->getPhoneMask()) : ""?>"/>
					</label>
				</div>

				<div class="<?=$section?>">
					<h2 class="<?=$sectionTitle?>">Onde o veículo está</h2>
					<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-6">
						<label class="k-label k:sm:col-span-2" for="vehicle_zipcode" id="vehicle_zipcode_label">CEP
							<input type="text" id="vehicle_zipcode" name="vehicle_zipcode" maxlength="10" inputmode="numeric" class="k-field mask.cep required input_clear" value=""/>
						</label>
						<label class="k-label k:sm:col-span-4" for="vehicle_complement">Referência
							<input type="text" id="vehicle_complement" name="vehicle_complement" maxlength="120" placeholder="Ex.: posto na saída da cidade" class="k-field input_clear" value=""/>
						</label>
						<label class="k-label k:sm:col-span-4" for="vehicle_city">Cidade
							<input type="text" id="vehicle_city" name="vehicle_city" maxlength="120" class="k-field required input_clear" value=""/>
						</label>
						<label class="k-label k:sm:col-span-2" for="vehicle_region" id="vehicle_region_label">UF
							<select id="vehicle_region" name="vehicle_region" class="k-field input_clear required">
								<option value=""></option>
								<?php foreach ($_ufList as $uf=>$uf_name){?>
									<option value="<?=$h($uf)?>" title="<?=$h($uf_name)?>"><?=$h($uf)?></option>
								<?php } ?>
							</select>
						</label>
					</div>
					<!-- Preenchidos pela busca de CEP -->
					<input type="hidden" id="vehicle_address" name="vehicle_address" class="input_clear" value=""/>
					<input type="hidden" id="vehicle_address_number" name="vehicle_address_number" class="input_clear" value=""/>
					<input type="hidden" id="vehicle_neighborhood" name="vehicle_neighborhood" class="input_clear" />
					<input type="hidden" id="vehicle_region_nm" name="vehicle_region_nm" class="input_clear"/>
				</div>

				<div class="<?=$section?>">
					<h2 class="<?=$sectionTitle?>">Verificação</h2>
					<p class="k:text-sm k:text-muted">Queremos garantir que uma pessoa real está criando o registro. Maiúsculas e minúsculas fazem diferença.</p>
					<div class="k:flex k:flex-wrap k:items-end k:gap-4">
						<img id="img_captcha" src="../VehicleRegCtrl.ctrlExt/getCaptcha" alt="Código de verificação" class="k:h-[46px] k:rounded-[10px] k:border k:border-line"/>
						<label class="k-label k:w-40" for="vehicle_captcha">Código
							<input type="text" id="vehicle_captcha" name="vehicle_captcha" maxlength="9" class="k-field required input_clear" autocomplete="off" value=""/>
						</label>
						<a id="btn_new_captcha" href="#" class="k:inline-flex k:min-h-11 k:items-center k:text-sm k:font-semibold k:text-kade-900 k:hover:text-kade-800">Gerar outro código</a>
					</div>
				</div>

				<div id="buttonBar" class="k:flex k:justify-end k:gap-2.5 k:border-t k:border-line k:pt-6">
					<button type="button" id="button_back" class="k-btn">Voltar</button>
					<button type="submit" id="button_save" class="k-btn k-btn-primary">Cadastrar veículo</button>
				</div>
			</form>
		</div>
	</div>
</div>

<iframe id="vehicle_iframe" name="vehicle_iframe" class="iframe_ctrl" src="" title="Envio do cadastro do veículo" hidden></iframe>
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
