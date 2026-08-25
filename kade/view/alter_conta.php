<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/AccountRegCtrl.class.php");

$userSuper 	   	  = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$account = $ctrlAcctReg->getAccountEdit();
$client	 = $account->getClient();
$person  = null;
if($client!=null)
	$person  = $client->getUser()->getPerson();

$_installments = $account->getInstallmentList();
$length 	   = sizeof($_installments);

$date = new DateTimeCustom();

$paymentMethodList = $ctrlAcctReg->getPaymentMethodList();
?>

<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/alter_conta.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/AccountBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){AccountBean.getInstance().initBean();});</script>
<div id="div_body_alter_conta" class="div_body">
	<form id="conta_form" method="post" target="conta_iframe" action="../AccountRegCtrl.ctrlExt/saveInter">
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1" alt="Dados de Usuário" title="Dados de Usuário">Dados da Conta</a></li>
			</ul>
			<div id="tabs-1">
				<div id="div_form_conta">
					<div class="div_input">
						<label for="account_id" class="label_input">Registro</label>
						<input type="text" id="account_id" name="account_id" size="20" maxlength="10" class="input_clear mask.numeric readonly" readonly="true" value="<?=($account->getId() > 0)?$account->getId():""?>" />
					</div>	
					<div class="div_input">
						<label for="id" class="label_input">Cód.Cliente</label>
						<input type="text" id="account_client_id" name="account_client_id" size="7" maxlength="10" <?=($account->getId() > 0)?"readonly='true'":""?> class="input_clear mask.numeric client_id <?=($account->getId() > 0)?"readonly":""?>" value="<?=($person!=null && $person->getId() > 0)?$person->getId():""?>" />
						<span id="btn_match_client" alt="Pesquisar por Clientes"></span>
						<span id="span_account_client_name" class="client_name"><?=($person!=null && $person->getId() > 0)?$person->getName():""?></span>
						<input type="hidden" name="account_client_name" id="account_client_name" class="client_name" value="<?=($person!=null && $person->getId() > 0)?$person->getName():""?>" />
					</div>		
					<?if($account->getId() > 0){?>
					<div class="div_input">
						<label for="id" class="label_input">Dt.Hr Criação</label>
						<input type="text" id="account_date_hr_proc" name="account_date_hr_proc" size="20" maxlength="20" class="input_clear readonly" readonly="true" value="<?=$account->getDtHrProc()->format("d/m/Y H:i")?>" />
					</div>	
					<div class="div_input">
						<label for="id" class="label_input">Status</label>
						<input type="text" id="account_status" name="account_status" size="20" maxlength="20" class="input_clear readonly <?=$account->getStatus()?>" readonly="true" value="<?=$account->getDescStatus()?>" />
					</div>	
					<div class="div_input">
							<label for="account_validate_month" class="label_input">Total de parcelas</label>
							<input type="text" id="account_validate_month" name="account_validate_month" size="10" maxlength="10" readonly=true class="input_clear mask.numeric readonly" value="<?=($account->getId() > 0)?$account->getValidateMonth():""?>"/>
						</div>	
					<?php } else {?>
					<br style="clear: both"/>
					<fieldset id="fieldset_options_parc">
						<legend>Opções para Gerar Parcelas</legend>
						<div class="div_input">
							<label for="account_validate_month" class="label_input">Total de parcelas</label>
							<input type="text" id="account_validate_month" name="account_validate_month" size="10" maxlength="10" class="input_clear required mask.numeric" value="12"/>
						</div>	
						<div class="div_input">
							<label for="installment_reg_value" class="label_input">Valor(R$)</label>
							<input type="text" id="installment_reg_value" name="installment_reg_value" size="10" maxlength="10" class="input_clear required mask.money" value="70,00"/>
						</div>	
						<div class="div_input">
							<label for="installment_reg_dt" class="label_input">Data Inicial</label>
							<input type="text" id="installment_reg_dt" name="installment_reg_dt" size="10" maxlength="10" class="input_clear required mask.date" value="<?=$date->format("d.m.Y");?>"/>
						</div>	
						<div class="div_input">
							<span id="button_ger_parc" class="btn_default" title="Gerar Parcelas" alt="Gerar Parcelas">
								<div class="div_btn_with_image"></div>
								<span>Clque aqui para Gerar Parcelas</span>
							</span>
						</div>	
					</fieldset>
					<?php }?>
				</div>
				<span id="span_loc_msg"></span>
				<br style="clear: both"/>
				<fieldset id="fieldset_installment">
					<legend>Parcelas</legend>
					<table id="table_installment" class="table table-bordered table-condensed">
						<thead class="table_head">
							<tr>
								<th class="td_id">Registro</th>
								<th class="td_value">Valor(R$)</th>
								<th class="td_drhr">Vencimento</th>
								<th class="td_sts">Status</th>
							</tr>
						</thead>
						<tbody id="tbody_installment" class="tbody_query">
							<?  $i = 0;
								while($i < $length)
								{							
									$installmentList = $_installments[$i];
							?>
							<tr class="tr_hover" title="Clique para visualizar dados de pagamento" alt="Clique para visualizar dados de pagamento">
								<td class="td_id">
									<?=$installmentList->getId ()?>
									<input type="hidden" name="installment_id[]" class="acc_hidden_id" id="installment_id_<?=$installmentList->getId ()?>" value="<?=$installmentList->getId ()?>" />
									<input type="hidden" name="installment_payment_value[]" id="installment_payment_value_<?=$installmentList->getId ()?>" value="<?=$installmentList->getPaymentValue()?>" />
									<input type="hidden" name="installment_payment_date[]" id="installment_payment_date_<?=$installmentList->getId ()?>" value="<?=($installmentList->getPaymentDate()!=null?$installmentList->getPaymentDate()->format("d/m/Y"):"")?>" />
									<input type="hidden" name="installment_payment_method[]" id="installment_payment_method_<?=$installmentList->getId ()?>" value="<?=($installmentList->getPaymentMethod()!=null?$installmentList->getPaymentMethod()->getId():"")?>" />
								</td>
								<td class="td_value">
									<?=number_format ( $installmentList->getValue (), 2, ',', '.' )?>
									<input type="hidden" name="installment_value[]" id="installment_value_<?=$installmentList->getId ()?>" value="<?=$installmentList->getValue ()?>" />
								</td>
								<td class="td_drhr">
									<?=$installmentList->getDueDate ()->format ( "d/m/Y" )?>
									<input type="hidden" name="installment_due_date[]" id="installment_due_date_<?=$installmentList->getId ()?>" value="<?=$installmentList->getDueDate ()->format ( "d/m/Y" )?>" />
								</td>
								<td id="td_installment_<?=$installmentList->getId ()?>" class="td_sts <?=$installmentList->getStatus ()?>">
									<span id="installment_status_nm_<?=$installmentList->getId ()?>"><?=$installmentList->getDescStatus ()?></span>
									<input type="hidden" name="installment_status[]" id="installment_status_<?=$installmentList->getId ()?>" value="<?=$installmentList->getStatus()?>" />
									<input type="hidden" id="installment_status_blk_<?=$installmentList->getId ()?>" value="<?=($installmentList->getStatus()!=Installment::UNPAID)?"1":"0"?>" />
								</td>				
							</tr>
							<?php $i++;}?>
						</tbody>
					</table>
				</fieldset>
				<br style="clear: both"/>
				<div id="buttonBar" class="">
					<?php if($account->getStatus() == Account::UNPAID){?>					
					<button id="button_save" class="btn_default" title="Salvar" alt="Salvar">
						<div class="div_btn_with_image"></div>
						<span>Salvar</span>
					</button>
					<?php } ?>
					<span id="button_new" class="btn_default" title="Nova Conta">
						<div class="div_btn_with_image"></div>
						<span>Nova Conta</span>
					</span>
					<?php if($account->getId() > 0 && $account->getStatus() == Account::UNPAID){?>
					<span id="button_cancel" class="btn_default" title="Nova Conta">
						<div class="div_btn_with_image"></div>
						<span>Cancelar Conta</span>
					</span>
					<?php } ?>
				</div>
			</div>
		</div>		
	</form>
	
	<div id="view_parc" title="Detalhes da Parcela">
		<div class="div_input">
			<label for="parc_payment_value_dtl" class="label_input">Valor Pago(R$)</label>
			<input type="text" id="parc_payment_value_dtl" class="required mask.money" size="10" maxlength="10" />
		</div>
		<div class="div_input">
			<label for="parc_payment_date_dtl" class="label_input">Data de Pagamento</label>
			<input type="text" id="parc_payment_date_dtl" class="required mask.date" size="10" maxlength="10" />
		</div>
		<div class="div_input">
			<label for="parc_payment_method_dtl"  class="label_input" title="Forma de Pagamento" alt="Forma de Pagamento">Forma de Pagamento</label>
			<select id="parc_payment_method_dtl" class="required">
				<option value=""></option>
				<?php foreach ($paymentMethodList as $paymentMethod){?>
					<option lang="<?=$paymentMethod->getEnabled()?>" value="<?=$paymentMethod->getId()?>"><?=$paymentMethod->getName()?></option>
				<?php } ?>
			</select>
		</div>					
		<input type="hidden" id="parc_old_sts_dtl" />
		<input type="hidden" id="parc_id" />
	</div>
	<iframe id="conta_iframe" name="conta_iframe" class="iframe_ctrl" src=""></iframe>
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


