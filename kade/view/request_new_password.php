<?
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");
	$message   = $ctrlLogQry->getMessage();

	$pageTitle = "Recuperar senha";
	$pageDesc  = "Informe seus dados de cadastro e enviaremos um link para criar uma nova senha.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/UserBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/UserBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){UserBean.getInstance().initRequestPwd();});</script>
<?php include(dirname(__FILE__)."/partials/pub_open.php"); ?>
	<?php include(dirname(__FILE__)."/partials/alert.php"); ?>
	<form id="form_new_password" name="form_new_password" target="newpwd_iframe" action="../LoginQueryCtrl.ctrlExt/requestNewPwd" method="post" class="k:flex k:flex-col k:gap-4">
		<fieldset class="k:m-0 k:border-0 k:p-0">
			<legend class="k:mb-1.5 k:text-sm k:font-medium">Tipo de cadastro</legend>
			<div class="k:grid k:grid-cols-2 k:gap-1 k:rounded-xl k:bg-surface k:p-1">
				<input type="radio" name="type_person" value="PF" id="person_pf" checked="checked" class="k:peer/pf k:sr-only" />
				<label for="person_pf" id="person_pf_label" class="k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:text-sm k:font-semibold k:text-muted k:peer-checked/pf:bg-white k:peer-checked/pf:text-kade-900 k:peer-checked/pf:shadow-sm k:peer-focus-visible/pf:ring-2 k:peer-focus-visible/pf:ring-accent">Pessoa física</label>
				<input type="radio" name="type_person" value="PJ" id="person_pj" class="k:peer/pj k:sr-only" />
				<label for="person_pj" id="person_pj_label" class="k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:text-sm k:font-semibold k:text-muted k:peer-checked/pj:bg-white k:peer-checked/pj:text-kade-900 k:peer-checked/pj:shadow-sm k:peer-focus-visible/pj:ring-2 k:peer-focus-visible/pj:ring-accent">Pessoa jurídica</label>
			</div>
		</fieldset>
		<div id="div_fields_pf">
			<label class="k-label" for="newpwd_cpf">CPF
				<input type="text" id="newpwd_cpf" name="newpwd_cpf" class="k-field mask.cpf required input_clear" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value=""/>
			</label>
		</div>
		<div id="div_fields_pj" style="display:none;">
			<label class="k-label" for="newpwd_cnpj">CNPJ
				<input type="text" id="newpwd_cnpj" name="newpwd_cnpj" class="k-field mask.cnpj required input_clear" maxlength="18" inputmode="numeric" placeholder="00.000.000/0000-00" value=""/>
			</label>
		</div>
		<label class="k-label" for="newpwd_mail">E-mail
			<input type="email" id="newpwd_mail" name="newpwd_mail" maxlength="255" class="k-field mask.mail required input_clear" placeholder="voce@empresa.com.br" autocomplete="email" value=""/>
		</label>
		<input type="hidden" name="action" value="requestNewPwd" />
		<button type="submit" id="button_sender" class="k-btn k-btn-dark k:mt-2 k:w-full">Enviar link</button>
		<span id="span_loc_msg"></span>
	</form>
	<p class="k:mt-6 k:border-t k:border-line k:pt-5 k:text-center k:text-[15px] k:text-muted">
		Lembrou a senha? <a href="/<?=ViewExtCtrl::LOGIN?>/" class="k:font-semibold k:text-kade-900 k:hover:text-kade-800">Entrar</a>
	</p>
<?php include(dirname(__FILE__)."/partials/pub_close.php"); ?>
<iframe id="newpwd_iframe" name="newpwd_iframe" class="iframe_ctrl" src="" title="Envio do pedido de nova senha" hidden></iframe>
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
