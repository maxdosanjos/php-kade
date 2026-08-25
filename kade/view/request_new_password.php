<?
	if ($viewExtCtrl == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}	
	require_once(dirname(dirname(__FILE__))."/shared/class/controller/LoginQueryCtrl.class.php");	
	$message   = $ctrlLogQry->getMessage();	
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/request_new_password.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/UserBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){UserBean.getInstance().initRequestPwd();});</script>

<?if($message!=null){?>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#button_sender").notify("<?=$message->getDesc()?>", "<?=$message->getType()?>");});</script>
<?}?>
<div class="logo_back"></div>
<div id="div_body_request_new_password" class="div_body">
	<form id="form_new_password" name="form_new_password" target="newpwd_iframe" action="../LoginQueryCtrl.ctrlExt/requestNewPwd" method="post">
		<div id="tabs">
			<ul>
		    	<li><a href="#tabs-1">Encontre sua conta</a></li>
		    </ul>
		  	<div id="tabs-1">
		  	<div class="div_input">
	    		<label for="person_pf" id="person_pf_label">Pessoa Física</label>
	    		<input type="radio" name="type_person" value="PF" id="person_pf" checked="checked" />
	    		<label for="person_pj" id="person_pj_label">Pessoa Jurídica</label>
	    		<input type="radio" name="type_person" value="PJ" id="person_pj" />
	    	</div>
	    	<div id="div_fields_pf" class="div_input" >
				<div id="div_newpwd_cpf" class="div_input">
					<label for="newpwd_cpf"  class="label_input">CPF: </label>
					<input type="text" id="newpwd_cpf" name="newpwd_cpf" class="mask.cpf required input_clear" size="14" maxlength="14" value=""/>
				</div>
	    	</div>
	    	<div id="div_fields_pj" class="div_input" style="display:none;">
				<div id="div_newpwd_cnpj" class="div_input">
		    		<label for="newpwd_cnpj"  class="label_input">CNPJ: </label>
					<input type="text" id="newpwd_cnpj" name="newpwd_cnpj" class="mask.cnpj required input_clear" size="14" maxlength="14" value=""/>
				</div>
			</div>			
			<div class="div_input">
	    		<label for="newpwd_mail"  class="label_input" alt="Email" title="Email">Email: </label>
	    		<input type="text" id="newpwd_mail" name="newpwd_mail" size="50" maxlength="255" class="mask.mail required input_clear" value="" alt="Email" title="Email"/>
	    	</div>
	    	<input type="hidden" name="action" value="requestNewPwd" />
	    	<br style="clear: both"/>   	
			<div id="buttonBar" class="">
				<button id="button_sender" class="btn_default" title="Enviar" alt="Enviar">
					<div class="div_btn_with_image"></div>
					<span>Enviar</span>
				</button>
			</div>
			<span id="span_loc_msg"></span>
		</div>	
	</form>
</div>
<iframe id="newpwd_iframe" name="newpwd_iframe" class="iframe_ctrl" src=""></iframe>
<div id="dialog-result" title="Resultado" alt="Resultado" style="display:none;">
	<div>
		<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
		<div id="dialog-result.messageText" style="border:1px solid #aaa;padding:10px;margin-top:10px;font-size:14px;"></div>
	</div> 
</div>	
<div id="dialog-working" title="Processando" alt="Aguarde, processando ..." style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" />
	<p id="dialog-working.message">Aguarde, processando ...</p>
</div>