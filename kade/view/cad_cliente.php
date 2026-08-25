<?php 
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

require_once(dirname(dirname(__FILE__))."/shared/class/controller/CustumerRegCtrl.class.php");

$_ufList 		  = $ctrlCustReg->getListUf();
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/cad_cliente_ext.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/CustumerBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){CustumerBean.getInstance().initBean();});</script>
<div class="logo_back"></div>
<div id="div_body_cad_clie" class="div_body">
	<div id="div_text_cad_cli" class="ui-state-highlight ui-corner-all">
		<span id="span_icon_explc" class="ui-icon ui-icon-info"></span>
		<div id="p_text_out">			
			<p><span id="span_dest"> Caro Transportador,</span> para começar a utilizar a ferramenta Kade Caminhões é muito simples:</p>
			
			<ul>
				<li> Cadastre-se abaixo;</li>
				<li>Você terá um período de 30 dias gratuitos para conhecer e testar a ferramenta;</li>
				<li>Caso não tenha demonstrado interesse durante o período gratuito, basta cancelar seu cadastro totalmente sem custo antes dos 30 dias.</li>
			</ul>
			
			<p>Após os 30 dias de gratuidade e você tendo interesse em assinar a ferramenta, será gerado mensalmente um boleto no valor da mensalidade padrão de R$ 70,00 (Setenta Reais) com vencimento para posteriores 30 dias.</p>
			<br/>
			<p><span id="span_dest">Pronto!! Seja bem-vindo ao Kade Caminhões!</span></p>
			<p><span id="span_dest">Teremos prazer em ajuda-lo, contate-nos:</span></p>
			<address>
				<p><span id="span_dest">E-mail: <a id="span_cli_mail" href="mailto:contato@kadecaminhoes.com.br?Subject=Dúvidas" target="_top">contato@kadecaminhoes.com.br</a></span></p>
				<p><span id="span_dest">Skype: <a id="span_cli_skype" href="skype:Kadecaminhoes?chat">Kadecaminhoes</a></span></p>
			</address>
		</div>
	</div>
	
	<form id="custumer_form" method="post" target="custumer_iframe" action="../CustumerRegCtrl.ctrlExt/save">
		<!-- Anti-bot hidden fields: honeypot and form timestamp -->
		<input type="hidden" id="hp_name" name="hp_name" value="" />
		<input type="hidden" id="form_ts" name="form_ts" value="<?php echo time(); ?>" />
		<div id="tabs">
			<ul>
		    	<li><a href="#tabs-1" alt="Cadastre-se" title="Cadastre-se">Cadastre-se</a></li>
		    </ul>
		  	<div id="tabs-1">
				<div class="div_input">
		    		<label for="person_pf" id="person_pf_label" alt="Pessoa Física" title="Pessoa Física">Pessoa Física</label>
		    		<input type="radio" name="type_person" value="PF" id="person_pf" checked="checked" alt="Pessoa Física" title="Pessoa Física"/>
		    		<label for="person_pj" id="person_pj_label" alt="Pessoa Jurídica" title="Pessoa Jurídica">Pessoa Jurídica</label>
		    		<input type="radio" name="type_person" value="PJ" id="person_pj" alt="Pessoa Jurídica" title="Pessoa Jurídica"/>
		    	</div>
				<div id="div_fields_pf" class="div_input" >
					<div id="div_custumer_cpf" class="div_input">
						<label for="custumer_cpf"  class="label_input" alt="CPF" title="CPF">CPF: </label>
						<input type="text" id="custumer_cpf" name="custumer_cpf" class="mask.cpf required input_clear input_per" size="14" maxlength="14" value="" alt="CPF" title="CPF"/>
					</div>
					<div class="div_input">
			    		<label for="custumer_name" class="label_input" alt="Nome" title="Nome">Nome: </label>
			    		<input type="text" id="custumer_name" name="custumer_name" size="40" maxlength="120" class="required input_clear input_per" value="" alt="Nome" title="Nome"/>
			    	</div>
		    	</div>
		    	<div id="div_fields_pj" class="div_input" style="display:none;">
					<div id="div_custumer_cnpj" class="div_input">
			    		<label for="custumer_cnpj"  class="label_input" alt="CNPJ" title="CNPJ">CNPJ: </label>
						<input type="text" id="custumer_cnpj" name="custumer_cnpj" class="mask.cnpj required input_clear input_per" size="14" maxlength="14" value="" alt="CNPJ" title="CNPJ"/>
					</div>
					<div class="div_input">
			    		<label for="custumer_rsoc"  class="label_input" alt="Razão Social" title="Razão Social">Razão Social: </label>
			    		<input type="text" id="custumer_rsoc" name="custumer_rsoc" size="40" maxlength="120" class="required input_clear input_per" value="" alt="Razão Social" title="Razão Social"/>
			    	</div>
					<div class="div_input">
			    		<label for="custumer_ie"  class="label_input" alt="Inscrição Estadual" title="Inscrição Estadual">I.E.: </label>
			    		<input type="text" id="custumer_ie" name="custumer_ie" size="12" maxlength="18" class="mask.numeric required input_clear input_per" value="" alt="Inscrição Estadual" title="Inscrição Estadual"/>
						<label id="custumer_ie_isento_label" alt="Inscrição Estadual = Isento" title="Inscrição Estadual = Isento">
							<input type="checkbox" id="custumer_ie_isento" name="custumer_ie_isento" value="on">Isento
						</label>
			    	</div>
			    	<div class="div_input">
			    		<label for="custumer_contact"  class="label_input" alt="Contato" title="Contato">Contato: </label>
			    		<input type="text" id="custumer_contact" name="custumer_contact" size="40" maxlength="120" class="input_clear input_per" value="" alt="Contato" title="Contato"/>
			    	</div>
				</div>
				<div class="div_input">
		    		<label for="custumer_phone"  class="label_input" alt="Telefone" title="Telefone">Telefone: </label>
		    		<input type="text" id="custumer_phone" name="custumer_phone" size="12" maxlength="11" class="mask.phone2_with_ddd required input_clear" value="" alt="Telefone" title="Telefone"/>
		    	</div>   
				<div class="div_input">
		    		<label for="custumer_mail"  class="label_input" alt="Email" title="Email">Email: </label>
		    		<input type="text" id="custumer_mail" name="custumer_mail" size="50" maxlength="255" class="mask.mail required input_clear" value="" alt="Email" title="Email"/>
		    	</div>   	
				<div class="div_input">
		    		<label for="custumer_login"  class="label_input" alt="Login" title="Login">Login: </label>
		    		<input type="text" id="custumer_login" name="custumer_login" size="16" maxlength="20" class="mask.login required input_clear" value="" alt="Login" title="Login"/>
		    	</div>   	
				<div class="div_input">
		    		<label for="custumer_password"  class="label_input" alt="Senha" title="Senha">Senha: </label>
		    		<input type="password" id="custumer_password" name="custumer_password" size="13" maxlength="40" class="mask.password required input_clear" value="" alt="Senha" title="Senha"/>
		    	</div>   					
		    	<br style="clear: both"/>
		    	<fieldset id="fieldset_end">
		    		<legend alt="Endereço" title="Endereço">Endereço</legend>
			    	<div class="div_input">
			    		<label for="custumer_zipcode" id="custumer_zipcode_label"  class="label_input" alt="CEP" title="CEP">CEP: </label>
			    		<input type="text" id="custumer_zipcode" name="custumer_zipcode" size="14" maxlength="10" class="mask.cep required input_clear" value="" alt="CEP" title="CEP"/>
			    	</div>
			    	<div class="div_input">
			    		<label for="custumer_address"  class="label_input" alt="Logradouro" title="Logradouro">Logradouro: </label>
			    		<input type="text" id="custumer_address" name="custumer_address" size="40" maxlength="120" class="input_clear"  value="" alt="Logradouro" title="Logradouro"/>
			    	</div>
					<div class="div_input">						
						<label for="custumer_neighborhood" id="custumer_neighborhood_label" class="label_input" alt="Bairro" title="Bairro">Bairro: </label>
			    		<input type="text" id="custumer_neighborhood" name="custumer_neighborhood" class="input_clear"  alt="Bairro" title="Bairro"/>
					</div>	
			    	<div class="div_input">	
			    		<label for="custumer_address_number" id="custumer_address_number_label"  class="label_input" alt="Número" title="Número">Número: </label>
			    		<input type="text" id="custumer_address_number" name="custumer_address_number" size="10" maxlength="12" class="required input_clear"  value="" alt="Número" title="Número"/>		    		
			    	</div>
					<div class="div_input">						
						<label for="custumer_complement"  class="label_input" alt="Complemento" title="Complemento">Complemento: </label>
			    		<input type="text" id="custumer_complement" name="custumer_complement" class="input_clear" size="40" maxlength="120" value="" alt="Complemento" title="Complemento"/>
			    	</div>
			    	<div class="div_input">
			    		<label for="custumer_city"  class="label_input" alt="Cidade" title="Cidade">Cidade: </label>
			    		<input type="text" id="custumer_city" name="custumer_city" size="40" maxlength="120" class="required input_clear"  value="" alt="Cidade" title="Cidade"/>
			    	</div>	
					<div class="div_input">						
			    		<label for="custumer_region" id="custumer_region_label"  class="label_input" alt="UF" title="UF">UF: </label>
			    		<select id="custumer_region" name="custumer_region" class="input_clea requiredr">
							<option value="" selected="selected"></option>
							<?php foreach ($_ufList as $uf=>$uf_name){?>
								<option value="<?=$uf?>"><?=$uf?> - <?=$uf_name?></option>
							<?php } ?>
						</select>
			    		<input type="hidden" id="custumer_region_nm" name="custumer_region_nm" class="input_clear" size="10"/>
			    	</div>
		    	</fieldset>
				
				<div>
		        	<span id="span_text_captcha" alt="Queremos garantir que uma pessoa real está criando um registro." title="Queremos garantir que uma pessoa real está criando um registro.">Queremos garantir que uma pessoa real está criando um registro.</span>
		        </div>
		    	<div class="div_input">
		    		<img id="img_captcha" src="../CustumerRegCtrl.ctrlExt/getCaptcha" alt="Código" title="Código"/>
		    		<input type="text" id="custumer_captcha" name="custumer_captcha" size="11" maxlength="9" class="required input_clear" value="" autocomplete="false" alt="Código" title="Código"/>	    					    	
					<span id="span_exp_cap" alt="Há diferença letras maiúsculas e minúsculas" title="Há diferença letras maiúsculas e minúsculas">
						***Há diferença letras maiúsculas e minúsculas<br/> Se os caracteres da imagem estiverem ilegíveis, <a id="btn_new_captcha" class="btn_default" href="#">gerar outra imagem</a>
					</span>
		    	</div>
				<br style="clear: both"/>
				<br/>
				<span id="span_conc"> Eu concordo com os <a href="/<?=ViewExtCtrl::TERMS_USE;?>/" target="_blank">Termos de uso da Kade Caminhões</a> </span>			
		    	<div id="buttonBar" class="">
					<span id="button_new" class="btn_default" title="Novo registro" alt="Novo registro">
						<div class="div_btn_with_image"></div>
						<span>Novo</span>
					</span>
					<span id="button_back" class="btn_default" title="Voltar" alt="Voltar">
						<div class="div_btn_with_image"></div>
						<span>Voltar</span>
					</span>
					<button id="button_save" class="btn_default" title="Salvar" alt="Salvar">
						<div class="div_btn_with_image"></div>
						<span>Salvar</span>
					</button>
				</div>
			</div>
		</div>
	</form>
	<iframe id="custumer_iframe" name="custumer_iframe" class="iframe_ctrl" src=""></iframe>
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