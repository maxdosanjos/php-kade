<?php 
	$message = null;
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/load_banner.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/MainExt.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();MainExt.getInstance().initLoadBanner();});</script>
<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify("<?=$message->getDesc();?>", "<?=$message->getType();?>");});</script>
<?	} ?>
<div id="div_body_load_banner" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<fieldset id="fieldset_banner">
			<legend>Banner Principal</legend>
			<form id="banner_form" method="post" target="banner_iframe" action="../MapsExtCtrl.ctrlExt/loadBanner" enctype="multipart/form-data">
				<div class="div_input">						
					<label for="banner_id" id="banner_id_label"  class="label_input" alt="Banner" title="Banner">Banner: </label>
					<select id="banner_id" name="banner_id" class="required">
						<option value="1">Banner 1</option>
						<option value="2">Banner 2</option>
						<option value="3">Banner 3</option>					
					</select>
				</div>
				<div class="div_input">			
					<img id="banner_img" src="../shared/images/banner_slider1.jpg" width="800px" style="border:5px solid #e8e8e8;">
				</div>
				<div class="div_input">
					<label for="banner_file"  class="label_input" alt="Nova imagem" title="Nova imagem">Nova imagem: </label>
					<input type="file" id="banner_file" name="banner_file" size="12" maxlength="11" class="required " alt="Nova imagem" title="Nova imagem"/>
				</div>   
				
				<div id="div_text_explic" class="div_input">
					<p>*** Tamanho ideal: Comprimento: 1100 px, Altura: 300 px.<br/></p>
					<p>*** Para visualizar a alteração, não se esqueça de atualizar o cache de seu navegador( Teclas CTRL + F5 )<br/></p>
				</div>
				<br style="clear: both"/>
				<div id="buttonBar" class="">
					<button id="button_save" class="btn_default" title="Salvar" alt="Salvar">
						<div class="div_btn_with_image"></div>
						<span>&nbsp;Salvar</span>
					</button>
				</div>
			</form>
		</fieldset>
		
		<iframe id="banner_iframe" name="banner_iframe" class="iframe_ctrl" src=""></iframe>
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
	</div>
</div>
