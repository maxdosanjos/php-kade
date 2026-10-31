<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");
$message   = $ctrlLogQry->getMessage();

$pageTitle = "Entrar no KADE";
$pageDesc  = "Acesse sua conta para buscar e cadastrar veículos.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<?php include(dirname(__FILE__)."/partials/pub_open.php"); ?>
	<?php include(dirname(__FILE__)."/partials/alert.php"); ?>
	<form id="form_login" name="form_login" action="../LoginQueryCtrl.ctrlExt/onLogin" method="post" class="k:flex k:flex-col k:gap-4">
		<label class="k-label" for="user_login">Login
			<input type="text" maxlength="21" class="k-field mask.login required input_clear" placeholder="Seu login" name="user_login" id="user_login" autocomplete="username" autofocus>
		</label>
		<div class="k-label">
			<div class="k:flex k:items-center k:justify-between k:gap-3">
				<label for="user_password">Senha</label>
				<a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="k:text-sm k:font-medium k:text-kade-900 k:hover:text-kade-800">Esqueci minha senha</a>
			</div>
			<input type="password" maxlength="40" placeholder="Sua senha" name="user_password" id="user_password" class="k-field required input_clear" autocomplete="current-password">
		</div>
		<button type="submit" class="k-btn k-btn-dark k:mt-2 k:w-full">Entrar</button>
	</form>
	<p class="k:mt-6 k:border-t k:border-line k:pt-5 k:text-center k:text-[15px] k:text-muted">
		Ainda não tem conta? <a href="/<?=ViewExtCtrl::CAD_CLIENTE;?>/" class="k:font-semibold k:text-kade-900 k:hover:text-kade-800">Cadastre-se</a>
	</p>
<?php include(dirname(__FILE__)."/partials/pub_close.php"); ?>
