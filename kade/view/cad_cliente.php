<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

require_once(dirname(dirname(__FILE__))."/shared/class/controller/CustumerRegCtrl.class.php");

$_ufList = $ctrlCustReg->getListUf();
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$section = "k:flex k:flex-col k:gap-4 k:border-t k:border-line k:pt-6";
$sectionTitle = "k:font-display k:text-lg k:font-semibold k:text-kade-950";
$grid2 = "k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/CustumerBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/CustumerBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){CustumerBean.getInstance().initBean();});</script>

<div class="k-ui k:px-4 k:py-10 k:text-ink k:md:px-6 k:md:py-14">
	<div class="k:mx-auto k:max-w-[1100px]">
		<div class="k:mb-8 k:flex k:flex-col k:gap-1.5">
			<h1 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]">Crie sua conta</h1>
			<p class="k:text-base k:text-muted">Cadastre-se para buscar caminhões disponíveis em todo o Brasil.</p>
		</div>

		<div class="k:flex k:flex-wrap k:items-start k:gap-6">
			<!-- Como funciona -->
			<aside class="k:flex k:min-w-0 k:flex-[1_1_300px] k:lg:sticky k:lg:top-24 k:lg:order-2 k:flex-col k:gap-5 k:rounded-2xl k:bg-kade-900 k:p-6 k:text-white k:md:p-7">
				<h2 class="k:font-display k:text-xl k:font-semibold">Como funciona</h2>
				<ol class="k:flex k:flex-col k:gap-4 k:text-[15px] k:leading-relaxed k:text-[#d5deeb]">
					<li class="k:flex k:gap-3"><span class="k:flex k:h-7 k:w-7 k:shrink-0 k:items-center k:justify-center k:rounded-full k:bg-white/10 k:text-sm k:font-semibold k:text-white">1</span><span>Preencha o cadastro e confirme pelo link enviado ao seu e-mail.</span></li>
					<li class="k:flex k:gap-3"><span class="k:flex k:h-7 k:w-7 k:shrink-0 k:items-center k:justify-center k:rounded-full k:bg-white/10 k:text-sm k:font-semibold k:text-white">2</span><span>Use a ferramenta por <strong class="k:text-white">30 dias grátis</strong>.</span></li>
					<li class="k:flex k:gap-3"><span class="k:flex k:h-7 k:w-7 k:shrink-0 k:items-center k:justify-center k:rounded-full k:bg-white/10 k:text-sm k:font-semibold k:text-white">3</span><span>Se quiser continuar, a mensalidade é de <strong class="k:text-white">R$ 70,00</strong>, por boleto com vencimento em 30 dias. Se não, é só cancelar antes do fim do período grátis, sem custo.</span></li>
				</ol>
				<div class="k:border-t k:border-white/15 k:pt-4 k:text-sm k:text-[#d5deeb]">
					<p class="k:mb-1 k:font-semibold k:text-white">Dúvidas? Fale com a gente</p>
					<a href="mailto:contato@kadecaminhoes.com.br?subject=D%C3%BAvidas" class="k:text-[#ffb766] k:hover:text-white">contato@kadecaminhoes.com.br</a>
				</div>
			</aside>
			<!-- Formulário -->
			<form id="custumer_form" method="post" target="custumer_iframe" action="../CustumerRegCtrl.ctrlExt/save" class="k:flex k:min-w-0 k:flex-[999_1_560px] k:lg:order-1 k:flex-col k:gap-6 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-8">
				<!-- Anti-robô: campo invisível (robôs preenchem) e horário de abertura do formulário -->
				<div class="k:absolute k:-left-[9999px] k:h-px k:w-px k:overflow-hidden" aria-hidden="true">
					<label for="hp_name">Não preencha</label>
					<input type="text" id="hp_name" name="hp_name" value="" tabindex="-1" autocomplete="off" />
				</div>
				<input type="hidden" id="form_ts" name="form_ts" value="<?php echo time(); ?>" />

				<fieldset class="k:m-0 k:border-0 k:p-0">
					<legend class="k:mb-1.5 k:text-sm k:font-medium">Tipo de cadastro</legend>
					<div class="k:grid k:max-w-md k:grid-cols-2 k:gap-1 k:rounded-xl k:bg-surface k:p-1">
						<input type="radio" name="type_person" value="PF" id="person_pf" checked="checked" class="k:peer/pf k:sr-only"/>
						<label for="person_pf" id="person_pf_label" class="k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:text-sm k:font-semibold k:text-muted k:peer-checked/pf:bg-white k:peer-checked/pf:text-kade-900 k:peer-checked/pf:shadow-sm k:peer-focus-visible/pf:ring-2 k:peer-focus-visible/pf:ring-accent">Pessoa física</label>
						<input type="radio" name="type_person" value="PJ" id="person_pj" class="k:peer/pj k:sr-only"/>
						<label for="person_pj" id="person_pj_label" class="k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:text-sm k:font-semibold k:text-muted k:peer-checked/pj:bg-white k:peer-checked/pj:text-kade-900 k:peer-checked/pj:shadow-sm k:peer-focus-visible/pj:ring-2 k:peer-focus-visible/pj:ring-accent">Pessoa jurídica</label>
					</div>
				</fieldset>

				<div id="div_fields_pf">
					<div class="<?=$grid2?>">
						<label class="k-label" for="custumer_cpf">CPF
							<input type="text" id="custumer_cpf" name="custumer_cpf" class="k-field mask.cpf required input_clear input_per" maxlength="14" inputmode="numeric" value=""/>
						</label>
						<label class="k-label" for="custumer_name">Nome completo
							<input type="text" id="custumer_name" name="custumer_name" maxlength="120" class="k-field required input_clear input_per" autocomplete="name" value=""/>
						</label>
					</div>
				</div>
				<div id="div_fields_pj" style="display:none;">
					<div class="<?=$grid2?>">
						<label class="k-label" for="custumer_cnpj">CNPJ
							<input type="text" id="custumer_cnpj" name="custumer_cnpj" class="k-field mask.cnpj required input_clear input_per" maxlength="18" inputmode="numeric" value=""/>
						</label>
						<label class="k-label" for="custumer_rsoc">Razão social
							<input type="text" id="custumer_rsoc" name="custumer_rsoc" maxlength="120" class="k-field required input_clear input_per" autocomplete="organization" value=""/>
						</label>
						<div class="k-label">
							<label for="custumer_ie">Inscrição estadual</label>
							<input type="text" id="custumer_ie" name="custumer_ie" maxlength="18" class="k-field mask.numeric required input_clear input_per" inputmode="numeric" value=""/>
							<label id="custumer_ie_isento_label" class="k:mt-2 k:inline-flex k:cursor-pointer k:items-center k:gap-2 k:text-sm k:font-normal k:text-ink-soft">
								<input type="checkbox" id="custumer_ie_isento" name="custumer_ie_isento" value="on" class="k:h-4 k:w-4 k:accent-kade-900"> Isento
							</label>
						</div>
						<label class="k-label" for="custumer_contact">Nome do contato
							<input type="text" id="custumer_contact" name="custumer_contact" maxlength="120" class="k-field input_clear input_per" value=""/>
						</label>
					</div>
				</div>

				<div class="<?=$section?>">
					<h2 class="<?=$sectionTitle?>">Contato e acesso</h2>
					<div class="<?=$grid2?>">
						<label class="k-label" for="custumer_phone">Telefone
							<input type="tel" id="custumer_phone" name="custumer_phone" maxlength="15" class="k-field mask.phone2_with_ddd required input_clear" autocomplete="tel" value=""/>
						</label>
						<label class="k-label" for="custumer_mail">E-mail
							<input type="email" id="custumer_mail" name="custumer_mail" maxlength="255" class="k-field mask.mail required input_clear" autocomplete="email" value=""/>
						</label>
						<label class="k-label" for="custumer_login">Login
							<input type="text" id="custumer_login" name="custumer_login" maxlength="20" class="k-field mask.login required input_clear" autocomplete="username" value=""/>
						</label>
						<label class="k-label" for="custumer_password">Senha
							<input type="password" id="custumer_password" name="custumer_password" maxlength="40" class="k-field mask.password required input_clear" autocomplete="new-password" value=""/>
						</label>
					</div>
				</div>

				<div class="<?=$section?>">
					<h2 class="<?=$sectionTitle?>">Endereço</h2>
					<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-6">
						<label class="k-label k:sm:col-span-2" for="custumer_zipcode" id="custumer_zipcode_label">CEP
							<input type="text" id="custumer_zipcode" name="custumer_zipcode" maxlength="10" class="k-field mask.cep required input_clear" inputmode="numeric" autocomplete="postal-code" value=""/>
						</label>
						<label class="k-label k:sm:col-span-4" for="custumer_address">Logradouro
							<input type="text" id="custumer_address" name="custumer_address" maxlength="120" class="k-field input_clear" autocomplete="address-line1" value=""/>
						</label>
						<label class="k-label k:sm:col-span-2" for="custumer_address_number" id="custumer_address_number_label">Número
							<input type="text" id="custumer_address_number" name="custumer_address_number" maxlength="12" class="k-field required input_clear" value=""/>
						</label>
						<label class="k-label k:sm:col-span-4" for="custumer_complement">Complemento
							<input type="text" id="custumer_complement" name="custumer_complement" maxlength="120" class="k-field input_clear" autocomplete="address-line2" value=""/>
						</label>
						<label class="k-label k:sm:col-span-2" for="custumer_neighborhood" id="custumer_neighborhood_label">Bairro
							<input type="text" id="custumer_neighborhood" name="custumer_neighborhood" maxlength="120" class="k-field input_clear"/>
						</label>
						<label class="k-label k:sm:col-span-3" for="custumer_city">Cidade
							<input type="text" id="custumer_city" name="custumer_city" maxlength="120" class="k-field required input_clear" autocomplete="address-level2" value=""/>
						</label>
						<label class="k-label k:sm:col-span-1" for="custumer_region" id="custumer_region_label">UF
							<select id="custumer_region" name="custumer_region" class="k-field required input_clear" autocomplete="address-level1">
								<option value=""></option>
								<?php foreach ($_ufList as $uf=>$uf_name){?>
									<option value="<?=$h($uf)?>" title="<?=$h($uf_name)?>"><?=$h($uf)?></option>
								<?php } ?>
							</select>
						</label>
						<input type="hidden" id="custumer_region_nm" name="custumer_region_nm" class="input_clear"/>
					</div>
				</div>

				<div class="<?=$section?>">
					<h2 class="<?=$sectionTitle?>">Verificação</h2>
					<p class="k:text-sm k:text-muted">Queremos garantir que uma pessoa real está criando a conta. Maiúsculas e minúsculas fazem diferença.</p>
					<div class="k:flex k:flex-wrap k:items-end k:gap-4">
						<img id="img_captcha" src="../CustumerRegCtrl.ctrlExt/getCaptcha" alt="Código de verificação" class="k:h-[46px] k:rounded-[10px] k:border k:border-line"/>
						<label class="k-label k:w-40" for="custumer_captcha">Código
							<input type="text" id="custumer_captcha" name="custumer_captcha" maxlength="9" class="k-field required input_clear" autocomplete="off" value=""/>
						</label>
						<a id="btn_new_captcha" href="#" class="k:inline-flex k:min-h-11 k:items-center k:text-sm k:font-semibold k:text-kade-900 k:hover:text-kade-800">Gerar outro código</a>
					</div>
				</div>

				<div class="k:flex k:flex-col k:gap-4 k:border-t k:border-line k:pt-6 k:sm:flex-row k:sm:items-center k:sm:justify-between">
					<p id="span_conc" class="k:text-sm k:text-muted">Ao criar a conta, você concorda com os <a href="/<?=ViewExtCtrl::TERMS_USE;?>/" target="_blank" class="k:font-semibold k:text-kade-900">Termos de uso</a>.</p>
					<div id="buttonBar" class="k:flex k:gap-2.5">
						<button type="button" id="button_back" class="k-btn">Voltar</button>
						<button type="submit" id="button_save" class="k-btn k-btn-primary">Criar conta</button>
					</div>
				</div>
			</form>

		</div>
	</div>
</div>

<iframe id="custumer_iframe" name="custumer_iframe" class="iframe_ctrl" src="" title="Envio do cadastro" hidden></iframe>
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
