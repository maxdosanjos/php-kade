<?
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");

	try {
		$ctrlLogQry->confirmUser();
		$resultOk     = true;
		$resultTitle  = "Cadastro confirmado";
		$resultText   = "Obrigado por confirmar seus dados. Seu usuário está ativo: é só entrar para usar o KADE.";
		$resultAction = '<a href="/'.ViewExtCtrl::LOGIN.'/" class="k-btn k-btn-dark">Entrar</a>';
	} catch (Exception $e) {
		$resultOk     = false;
		$resultTitle  = "Link inválido ou já utilizado";
		$resultText   = "Este link de confirmação expirou ou já foi usado. Se ainda não consegue entrar, peça um link de nova senha.";
		$resultAction = '<a href="/'.ViewExtCtrl::NEW_PASSWRD.'/" class="k-btn k-btn-dark">Pedir novo link</a>';
	}
	include(dirname(__FILE__)."/partials/result.php");
?>
