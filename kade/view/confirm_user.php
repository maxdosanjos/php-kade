<?
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}	
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");	
?>
<link rel="stylesheet" type="text/css" href="../shared/css/confirm_cliente_ext.css?f=1" media="all" />
<div class="logo_back"></div>
<div id="div_body_confirm_cliente" class="div_body">
	<div id="div_text">
	<?try
	{
		$ctrlLogQry->confirmUser();?>
		<h1 id="h1_success">
			<div id="span_message_icon" class="img_msg_success"></div>
			Confirmação realizada com sucesso!!!
		</h1>
		<p id="p_success">
			Obrigado por confirmar seus dados. Seu usuário está validado! Basta realizar <a href="/<?=ViewExtCtrl::LOGIN."/"?>">seu login</a> para usufruir de nossos serviços.
		</p>
		
	<?} catch ( Exception $e ) {?>
		
		<h1 id="h1_error">
			<div id="span_message_icon" class="img_msg_error"></div>
			URL inválida para confirmação!!!
		</h1>
		<p id="p_error">
			A url informada já foi utilizada ou está inválida. Será necessário <a href="/<?=ViewExtCtrl::NEW_PASSWRD."/"?>">clicar aqui</a> para solicitar novamente uma nova confirmação.
		</p>
	<?}?>
	</div>
</div>