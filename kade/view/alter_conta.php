<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/AccountRegCtrl.class.php");

$userSuper = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$account = $ctrlAcctReg->getAccountEdit();
$client  = $account->getClient();
$person  = ($client != null && $client->getUser() != null) ? $client->getUser()->getPerson() : null;
$_installments = $account->getInstallmentList() ?? array();
$date = new DateTimeCustom();
$paymentMethodList = $ctrlAcctReg->getPaymentMethodList() ?? array();

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$isNew     = !($account->getId() > 0);
$hasPerson = ($person != null && $person->getId() > 0);

$pageTitle = $isNew ? "Nova conta" : "Conta #".$h($account->getId());
$pageDesc  = $isNew ? "Escolha o cliente e gere as parcelas." : "Clique em uma parcela para registrar o pagamento.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/AccountBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/AccountBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){AccountBean.getInstance().initBean();});</script>
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>

	<form id="conta_form" method="post" target="conta_iframe" action="../AccountRegCtrl.ctrlExt/saveInter" class="k:flex k:flex-col k:gap-6 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-8">
		<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2">
			<div class="k-label k:sm:col-span-2">
				<label for="account_client_id">Cliente</label>
				<div class="k:mt-1.5 k:flex k:gap-2">
					<input type="text" id="account_client_id" name="account_client_id" maxlength="10" inputmode="numeric" placeholder="Nº" class="k-field input_clear mask.numeric client_id k:max-w-28<?=$isNew ? "" : " readonly"?>"<?=$isNew ? "" : " readonly"?> value="<?=$hasPerson ? $h($person->getId()) : ""?>" />
					<?php if ($isNew) { ?>
					<button type="button" id="btn_match_client" class="k-btn k:px-3" aria-label="Procurar cliente" title="Procurar cliente">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					</button>
					<?php } ?>
					<span id="span_account_client_name" class="client_name k:flex k:min-w-0 k:items-center k:truncate k:text-[15px] k:font-medium k:text-ink-soft"><?=$hasPerson ? $h($person->getName()) : ""?></span>
				</div>
				<input type="hidden" name="account_client_name" id="account_client_name" class="client_name" value="<?=$hasPerson ? $h($person->getName()) : ""?>" />
			</div>
			<input type="hidden" id="account_id" name="account_id" value="<?=$isNew ? "" : $h($account->getId())?>" />
			<?php if (!$isNew) { ?>
			<div>
				<span class="k:block k:text-[13px] k:text-muted">Criada em</span>
				<span class="k:text-[15px]"><?=$account->getDtHrProc()->format("d/m/Y H:i")?></span>
			</div>
			<div>
				<span class="k:block k:text-[13px] k:text-muted">Status</span>
				<span class="k-badge <?=$h($account->getStatus())?>"><?=$h($account->getDescStatus())?></span>
				<input type="hidden" id="account_validate_month" name="account_validate_month" value="<?=$h($account->getValidateMonth())?>"/>
			</div>
			<?php } ?>
		</div>

		<?php if ($isNew) { ?>
		<fieldset id="fieldset_options_parc" class="k:m-0 k:flex k:flex-col k:gap-4 k:rounded-xl k:border k:border-line k:bg-surface k:p-5">
			<legend class="k:px-1 k:font-display k:text-base k:font-semibold k:text-kade-950">Gerar parcelas</legend>
			<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-3">
				<label class="k-label" for="account_validate_month">Quantidade
					<input type="text" id="account_validate_month" name="account_validate_month" maxlength="3" inputmode="numeric" class="k-field input_clear required mask.numeric" value="12"/>
				</label>
				<label class="k-label" for="installment_reg_value">Valor de cada (R$)
					<input type="text" id="installment_reg_value" name="installment_reg_value" maxlength="10" inputmode="decimal" class="k-field input_clear required mask.money" value="70,00"/>
				</label>
				<label class="k-label" for="installment_reg_dt">Primeiro vencimento
					<input type="text" id="installment_reg_dt" name="installment_reg_dt" maxlength="10" class="k-field input_clear required mask.date" value="<?=$date->format("d.m.Y");?>"/>
				</label>
			</div>
			<div>
				<button type="button" id="button_ger_parc" class="k-btn k-btn-dark">Gerar parcelas</button>
			</div>
		</fieldset>
		<?php } ?>

		<section id="fieldset_installment" class="k:overflow-hidden k:rounded-xl k:border k:border-line" aria-label="Parcelas">
			<div class="k:overflow-x-auto">
				<table id="table_installment" class="k-table k:min-w-[520px]">
					<thead>
						<tr>
							<th>Parcela</th>
							<th>Valor (R$)</th>
							<th>Vencimento</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="tbody_installment" class="tbody_query">
						<?php foreach ($_installments as $installmentList) {
							$iid = $h($installmentList->getId()); ?>
						<tr class="tr_hover" title="Clique para ver ou registrar o pagamento">
							<td class="k:font-semibold k:text-kade-900">
								<?=$iid?>
								<input type="hidden" name="installment_id[]" class="acc_hidden_id" id="installment_id_<?=$iid?>" value="<?=$iid?>" />
								<input type="hidden" name="installment_payment_value[]" id="installment_payment_value_<?=$iid?>" value="<?=$h($installmentList->getPaymentValue())?>" />
								<input type="hidden" name="installment_payment_date[]" id="installment_payment_date_<?=$iid?>" value="<?=($installmentList->getPaymentDate() != null ? $installmentList->getPaymentDate()->format("d/m/Y") : "")?>" />
								<input type="hidden" name="installment_payment_method[]" id="installment_payment_method_<?=$iid?>" value="<?=($installmentList->getPaymentMethod() != null ? $h($installmentList->getPaymentMethod()->getId()) : "")?>" />
							</td>
							<td>
								<?=number_format($installmentList->getValue(), 2, ',', '.')?>
								<input type="hidden" name="installment_value[]" id="installment_value_<?=$iid?>" value="<?=$h($installmentList->getValue())?>" />
							</td>
							<td class="k:whitespace-nowrap">
								<?=$installmentList->getDueDate()->format("d/m/Y")?>
								<input type="hidden" name="installment_due_date[]" id="installment_due_date_<?=$iid?>" value="<?=$installmentList->getDueDate()->format("d/m/Y")?>" />
							</td>
							<td id="td_installment_<?=$iid?>" class="<?=$h($installmentList->getStatus())?>">
								<span id="installment_status_nm_<?=$iid?>" class="k-badge"><?=$h($installmentList->getDescStatus())?></span>
								<input type="hidden" name="installment_status[]" id="installment_status_<?=$iid?>" value="<?=$h($installmentList->getStatus())?>" />
								<input type="hidden" id="installment_status_blk_<?=$iid?>" value="<?=($installmentList->getStatus() != Installment::UNPAID) ? "1" : "0"?>" />
							</td>
						</tr>
						<?php } ?>
						<?php if (count($_installments) == 0) { ?>
						<tr class="k-empty"><td colspan="4" class="k:py-8 k:text-center k:text-muted">Nenhuma parcela ainda. Use "Gerar parcelas" acima.</td></tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</section>

		<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5 k:border-t k:border-line k:pt-6">
			<span id="span_loc_msg" class="k:mr-auto"></span>
			<?php if (!$isNew && $account->getStatus() == Account::UNPAID) { ?>
			<button type="button" id="button_cancel" class="k-btn k-btn-danger">Cancelar conta</button>
			<?php } ?>
			<button type="button" id="button_new" class="k-btn">Nova conta</button>
			<?php if ($account->getStatus() == Account::UNPAID) { ?>
			<button type="submit" id="button_save" class="k-btn k-btn-primary">Salvar</button>
			<?php } ?>
		</div>
	</form>

<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>

<!-- Pagamento da parcela (diálogo do jQuery UI aberto pelo AccountBean) -->
<div id="view_parc" title="Detalhes da parcela" style="display:none">
	<div class="k-ui k:flex k:flex-col k:gap-4">
		<label class="k-label" for="parc_payment_value_dtl">Valor pago (R$)
			<input type="text" id="parc_payment_value_dtl" class="k-field required mask.money" maxlength="10" inputmode="decimal" />
		</label>
		<label class="k-label" for="parc_payment_date_dtl">Data do pagamento
			<input type="text" id="parc_payment_date_dtl" class="k-field required mask.date" maxlength="10" />
		</label>
		<label class="k-label" for="parc_payment_method_dtl">Forma de pagamento
			<select id="parc_payment_method_dtl" class="k-field required">
				<option value=""></option>
				<?php foreach ($paymentMethodList as $paymentMethod){?>
				<option lang="<?=$h($paymentMethod->getEnabled())?>" value="<?=$h($paymentMethod->getId())?>"><?=$h($paymentMethod->getName())?></option>
				<?php } ?>
			</select>
		</label>
		<input type="hidden" id="parc_old_sts_dtl" />
		<input type="hidden" id="parc_id" />
	</div>
</div>
<iframe id="conta_iframe" name="conta_iframe" class="iframe_ctrl" src="" title="Envio da conta" hidden></iframe>
<div id="dialog-result" title="Resultado" style="display:none;">
	<div>
		<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
		<div id="dialog-result.messageText"></div>
	</div>
</div>
<div id="dialog-working" title="Processando" style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" alt="" />
	<p id="dialog-working.message">Aguarde, processando ...</p>
</div>
