<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");	
$message   = $ctrlLogQry->getMessage();
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/login.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/LoginBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){LoginBean.getInstance().initQuery();});</script>
<div class="logo_back"></div>
<div id="div_body_login" class="div_body">
	<div class="container my-4">
	<form id="form_login" name="form_login" action="../LoginQueryCtrl.ctrlExt/onLogin" method="post">
		<div id="tabs">
			<ul>
		    	<li><a href="#tabs-1">Login no Kade Caminhões</a></li>
		    </ul>
		  	<div id="tabs-1">
				<? if($message!=null && $message->getType()!="") { ?>
				<div id="div_message_error_login" class="<?=($message->getType()==Message::ERR)?"ui-state-error":"ui-state-highlight"?> ui-corner-all mb-3">
					<div id="span_message_icon" class="<?=($message->getType()==Message::ERR)?"img_msg_error":"img_msg_success"?>"></div>
					<div id="message_text">
						<?=$message->getDesc()?>
					</div>
					<br style="clear:both"/>
				</div>
				<? } ?>
				
				<div class="div_input form-legacy-row">
					<label for="user_login" class="label_input form-label">Login: </label>
					<input type="text" size="16" maxlength="21" class="mask.login required input_clear input03b form-control" placeholder="Seu login" name="user_login" id="user_login" title="seu login" alt="seu login">
				</div>
				<div class="div_input form-legacy-row">
					<label for="user_password" class="label_input form-label">Senha: </label>
					<input type="password" value="" placeholder="Sua senha" size="13" maxlength="40" name="user_password" id="user_password" class="required input03b cor-cinza5 input_clear form-control" title="sua senha" alt="sua senha">                
				</div>
				<br style="clear: both"/>    
				<div class="glb-menu_div_login_msg mb-2">
					<a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="link-cinza4"> Esqueceu seu login ou sua senha?</a>
				</div>
				<br style="clear: both"/>    
				<div id="buttonBar" class="">
					<button id="" type="submit" class="btn btn-primary btn-sm btn_default submit_login" title="digite o seu login e senha e clique para acessar sua conta" alt="digite o seu login e senha e clique para acessar sua conta">
						<div class="div_btn_with_image"></div>
						<span>Entrar</span>
					</button>
				</div>
			</div>
			
			<span id="span_loc_msg"></span>
	</form>
	</div>
</div>