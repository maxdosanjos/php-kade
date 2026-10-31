<?
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}	
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");	
	$message   = $ctrlLogQry->getMessage();	
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/confirm_pwd_ext.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/UserBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){UserBean.getInstance().initConfirmPwd();});</script>
<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#button_sender").notify("<?=$message->getDesc()?>", "<?=$message->getType()?>");});</script>
<?}?>
<div class="logo_back"></div>
<div id="div_body_confirm_pwd" class="div_body">
	<div id="div_text">
	<?try
	{
		if($ctrlLogQry->isRequestNewPwd())
		{
		?>	
		<div id="div_text_content">
			<h1 id="h1_success">
				<div id="span_message_icon" class="img_msg_success"></div>
				Nova senha registrada com sucesso!!!
			</h1>
			<p id="p_success">
				Obrigado por confirmar seus dados. Seu usuário está validado! Basta realizar <a href="/<?=ViewExtCtrl::LOGIN."/"?>">seu login</a> para usufruir de nossos serviços.
			</p>
		</div>
		<? } else {
			$user = $ctrlLogQry->confirmLinkPwd();
		?>
			<form id="form_confirm_pwd" name="form_confirm_pwd" action="../LoginQueryCtrl.ctrlExt/confirmNewPwd" method="post">
				<div id="tabs">
					<ul>
						<li><a id="tab_new_pwd" href="#tabs-1">Nova Senha</a></li>
					</ul>
					<div id="tabs-1">
						<input type="hidden" id="vua" name="vua" value="<?=htmlspecialchars($_REQUEST["vua"] ?? "", ENT_QUOTES, "UTF-8")?>"/>
						<div class="div_input">
							<label for="custumer_login"  class="label_input" alt="Login" title="Login">Login: </label>
							<input type="text" id="custumer_login" name="custumer_login" size="16" maxlength="20" class="mask.login required readonly" value="<?=$user->getLogin()?>" alt="Login" title="Login" readonly="true"/>
						</div>   	
						<div class="div_input">
							<label for="custumer_password"  class="label_input" alt="Senha" title="Senha">Senha: </label>
							<input type="password" id="custumer_password" name="custumer_password" size="13" maxlength="40" class="mask.password required input_clear" value="" alt="Senha" title="Senha"/>
						</div> 
						<br style="clear: both"/>
						<div id="buttonBar" class="">
							<button id="button_save" class="btn_default" title="Salvar" alt="Salvar">
								<div class="div_btn_with_image"></div>
								<span>Salvar</span>
							</button>
						</div>
					</div>
					<span id="span_loc_msg"></span>
			</form>		
		<? } ?>
		
	<?} catch ( Exception $e ) {?>
		<div id="div_text_content">
			<h1 id="h1_error">
				<div id="span_message_icon" class="img_msg_error"></div>
				URL inválida para confirmação!!!
			</h1>
			<p id="p_error">
				<?=$e->getMessage()?>
			</p>
		</div>
	<?}?>
	</div>
</div>