<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/InstallmentQueryCtrl.class.php");
if (User::getLogged()==null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$ctrlInstQry->onSearchArray();
$_installment     = $ctrlInstQry->getInstallments() ?? array();
$installmentParam = $ctrlInstQry->getInstallmentParam();
$length           = count($_installment);

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$money = function ($v) { return "R$ ".number_format((float) $v, 2, ',', '.'); };
$total = 0;
foreach ($_installment as $installmentList)
	$total += ($installmentList->getStatus() == Installment::PAID) ? $installmentList->getPaymentValue() : $installmentList->getValue();
$isPaidView = ($installmentParam->getStatus() == Installment::PAID);

$pageTitle = "Minhas parcelas";
$pageDesc  = "Acompanhe as mensalidades da sua assinatura.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/InstallmentBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/InstallmentBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){InstallmentBean.getInstance().initQuery();});</script>
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>

	<div class="k:flex k:flex-wrap k:items-end k:justify-between k:gap-4">
		<form id="parc_query_open" name="parc_query_open" action="<?=$h($_SERVER["REQUEST_URI"])?>" method="post">
			<label class="k-label k:w-56" for="parc_status">Mostrar
				<select id="parc_status" name="parc_status" class="k-field">
					<?php foreach (Installment::listStatus() as $status=>$statusNm){
						if ($status == Installment::CANCEL) continue; ?>
					<option value="<?=$h($status)?>" <?=($installmentParam->getStatus() == $status) ? "selected" : ""?>><?=$status == Installment::PAID ? "Parcelas pagas" : "Parcelas em aberto"?></option>
					<?php } ?>
				</select>
			</label>
			<span id="span_loc_msg"></span>
			<input type="hidden" id="order_column" name="order_column" value="<?=$h($ctrlInstQry->getOrderColumn());?>"/>
			<input type="hidden" id="order_type" name="order_type" value="<?=$h($ctrlInstQry->getOrderType());?>"/>
			<input type="hidden" id="msg_type" name="msg_type" value=""/>
			<input type="hidden" id="msg_txt" name="msg_txt" value=""/>
		</form>
		<div class="k:rounded-2xl k:border k:border-line k:bg-white k:px-5 k:py-3 k:text-right">
			<span class="k:block k:text-[13px] k:text-muted"><?=$isPaidView ? "Total pago" : "Total em aberto"?></span>
			<span class="k:font-display k:text-xl k:font-semibold k:text-kade-950"><?=$money($total)?></span>
		</div>
	</div>

	<section id="div_result" class="k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white" aria-label="Parcelas">
		<div class="k:border-b k:border-line k:px-5 k:py-4 k:text-[15px]">
			<strong class="k:font-semibold"><?=$length?> <?=$length == 1 ? "parcela" : "parcelas"?></strong>
		</div>
		<div class="k-focus k:overflow-x-auto" tabindex="0" role="region" aria-label="Tabela (role para o lado se precisar)">
			<table id="table_query_open" class="k-table">
				<thead>
					<tr>
						<th class="column-order k-focus" tabindex="0" data-col="ins.account_id" title="Ordenar por conta">Conta <?=$ctrlInstQry->getImgOrder('ins.account_id');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="ins.id" title="Ordenar por parcela">Parcela <?=$ctrlInstQry->getImgOrder('ins.id');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="ins.due_date" title="Ordenar por vencimento">Vencimento <?=$ctrlInstQry->getImgOrder('ins.due_date');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="ins.value" title="Ordenar por valor">Valor <?=$ctrlInstQry->getImgOrder('ins.value');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="ins.payment_date" title="Ordenar por pagamento">Pago em <?=$ctrlInstQry->getImgOrder('ins.payment_date');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="ins.payment_value" title="Ordenar por valor pago">Valor pago <?=$ctrlInstQry->getImgOrder('ins.payment_value');?></th>
						<th>Forma</th>
						<th class="column-order k-focus" tabindex="0" data-col="ins.status" title="Ordenar por status">Status <?=$ctrlInstQry->getImgOrder('ins.status');?></th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?php if ($length == 0) { ?>
					<tr><td colspan="8" class="k:py-10 k:text-center k:text-muted"><?=$isPaidView ? "Nenhuma parcela paga ainda." : "Nenhuma parcela em aberto. Tudo em dia!"?></td></tr>
					<?php } ?>
					<?php foreach ($_installment as $installmentList) {
						$payMethodList = $installmentList->getPaymentMethod(); ?>
					<tr>
						<td class="k:font-semibold k:text-kade-900">#<?=$h($installmentList->getAccount()->getId())?></td>
						<td><?=$h($installmentList->getId())?></td>
						<td class="k:whitespace-nowrap"><?=$installmentList->getDueDate()->format("d/m/Y")?></td>
						<td class="k:whitespace-nowrap"><?=$money($installmentList->getValue())?></td>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=($installmentList->getPaymentDate() != null) ? $installmentList->getPaymentDate()->format("d/m/Y") : "—"?></td>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=($installmentList->getPaymentValue() > 0) ? $money($installmentList->getPaymentValue()) : "—"?></td>
						<td class="k:text-ink-soft"><?=($payMethodList != null) ? $h($payMethodList->getName()) : "—"?></td>
						<td class="<?=$h($installmentList->getStatus())?>"><span class="k-badge"><?=$h($installmentList->getDescStatus())?></span></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</section>

<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>
