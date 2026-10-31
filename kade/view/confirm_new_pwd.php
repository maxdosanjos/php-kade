<?
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");
	$message   = $ctrlLogQry->getMessage();

	$linkUser  = null;
	$linkError = "";
	if (!$ctrlLogQry->isRequestNewPwd()) {
		try {
			$linkUser = $ctrlLogQry->confirmLinkPwd();
		} catch (Exception $e) {
			$linkError = $e->getMessage();
		}
	}
?>
<?php if ($ctrlLogQry->isRequestNewPwd()) {
	$resultOk     = true;
	$resultTitle  = "Nova senha registrada";
	$resultText   = "Tudo certo! Agora é só entrar com a sua nova senha.";
	$resultAction = '<a href="/'.ViewExtCtrl::LOGIN.'/" class="k-btn k-btn-dark">Entrar</a>';
	include(dirname(__FILE__)."/partials/result.php");
} elseif ($linkUser == null) {
	$resultOk     = false;
	$resultTitle  = "Link inválido ou já utilizado";
	// A mensagem do controller traz um link "clique aqui"; o botão abaixo já cumpre esse papel
	$resultText   = htmlspecialchars(trim(preg_replace('/Será necessário.*$/s', '', strip_tags($linkError))), ENT_QUOTES, "UTF-8");
	$resultAction = '<a href="/'.ViewExtCtrl::NEW_PASSWRD.'/" class="k-btn k-btn-dark">Pedir um novo link</a>';
	include(dirname(__FILE__)."/partials/result.php");
} else {
	$pageTitle = "Criar nova senha";
	$pageDesc  = "Escolha uma senha com pelo menos 5 caracteres.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/UserBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/UserBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){UserBean.getInstance().initConfirmPwd();});</script>
<?php include(dirname(__FILE__)."/partials/pub_open.php"); ?>
	<?php include(dirname(__FILE__)."/partials/alert.php"); ?>
	<form id="form_confirm_pwd" name="form_confirm_pwd" action="../LoginQueryCtrl.ctrlExt/confirmNewPwd" method="post" class="k:flex k:flex-col k:gap-4">
		<input type="hidden" id="vua" name="vua" value="<?=htmlspecialchars($_REQUEST["vua"] ?? "", ENT_QUOTES, "UTF-8")?>"/>
		<label class="k-label" for="custumer_login">Login
			<input type="text" id="custumer_login" name="custumer_login" maxlength="20" class="k-field k:bg-surface k:text-muted" value="<?=htmlspecialchars($linkUser->getLogin(), ENT_QUOTES, "UTF-8")?>" readonly autocomplete="username"/>
		</label>
		<label class="k-label" for="custumer_password">Nova senha
			<input type="password" id="custumer_password" name="custumer_password" minlength="5" maxlength="40" class="k-field required input_clear" value="" autocomplete="new-password" autofocus/>
		</label>
		<button type="submit" id="button_save" class="k-btn k-btn-dark k:mt-2 k:w-full">Salvar nova senha</button>
		<span id="span_loc_msg"></span>
	</form>
<?php include(dirname(__FILE__)."/partials/pub_close.php"); ?>
<?php } ?>
