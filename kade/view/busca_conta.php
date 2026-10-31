<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/AccountQueryCtrl.class.php");

$userSuper = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

$ctrlAcctQry->onSearchArray();
$_accounts    = $ctrlAcctQry->getAccounts() ?? array();
$length       = count($_accounts);
$accountParam = $ctrlAcctQry->getAccountParam();
$clientParam  = $accountParam->getClient();
$personParam  = $clientParam->getUser()->getPerson();
$pagination   = $ctrlAcctQry->getPagination();
$message      = $ctrlAcctQry->getMessage();

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$hasClient = ($personParam != null && $personParam->getId() > 0);

$pageTitle   = "Contas e parcelas";
$pageDesc    = "Contas a receber dos clientes. Clique em uma conta para ver e editar as parcelas.";
$pageActions = '<button type="button" id="button_new" class="k-btn k-btn-primary">'
	.'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>'
	.'Nova conta</button>';
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/AccountBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/AccountBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){AccountBean.getInstance().initQuery();});</script>
<?php if ($message != null && $message->getDesc() != "") { ?>
	<script type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify(<?=json_encode($message->getDesc())?>, <?=json_encode($message->getType())?>);});</script>
<?php } ?>
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>

	<form id="conta_query_open" name="conta_query_open" action="<?=$h($_SERVER["REQUEST_URI"])?>" method="post" class="k:flex k:flex-col k:gap-5 k:rounded-2xl k:border k:border-line k:bg-white k:p-5 k:md:p-6">
		<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2 k:xl:grid-cols-4">
			<div class="k-label k:xl:col-span-2">
				<label for="account_client_id">Cliente</label>
				<div class="k:mt-1.5 k:flex k:gap-2">
					<input type="text" id="account_client_id" name="account_client_id" maxlength="10" inputmode="numeric" placeholder="Nº" class="k-field client_id input_clear mask.numeric k:max-w-28" value="<?=$hasClient ? $h($personParam->getId()) : ""?>" />
					<button type="button" id="btn_match_client" class="k-btn k:px-3" aria-label="Procurar cliente" title="Procurar cliente">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					</button>
					<span id="span_account_client_name" class="client_name k:flex k:min-w-0 k:items-center k:truncate k:text-[15px] k:font-medium k:text-ink-soft"><?=$hasClient ? $h($personParam->getName()) : ""?></span>
				</div>
				<input type="hidden" name="account_client_name" class="client_name" id="account_client_name" value="<?=$hasClient ? $h($personParam->getName()) : ""?>" />
			</div>
			<label class="k-label" for="account_dt_init">Criada de
				<input type="text" id="account_dt_init" name="account_dt_init" maxlength="10" class="k-field mask.date input_clear" value="<?=$ctrlAcctQry->getDtHrInit()->format("d.m.Y")?>"/>
			</label>
			<label class="k-label" for="account_dt_end">até
				<input type="text" id="account_dt_end" name="account_dt_end" maxlength="10" class="k-field mask.date input_clear" value="<?=$ctrlAcctQry->getDtHrEnd()->format("d.m.Y")?>"/>
			</label>
			<label class="k-label" for="account_id">Nº da conta
				<input type="text" id="account_id" name="account_id" maxlength="10" inputmode="numeric" class="k-field input_clear mask.numeric" value="<?=($accountParam->getId() > 0) ? $h($accountParam->getId()) : ""?>" />
			</label>
		</div>

		<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5">
			<span id="span_loc_msg" class="k:mr-auto"></span>
			<button type="button" id="button_reset" class="k-btn">Limpar</button>
			<button type="submit" id="button_search" class="k-btn k-btn-primary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
				Buscar
			</button>
		</div>

		<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
		<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
		<input type="hidden" id="order_column" name="order_column" value="<?=$h($ctrlAcctQry->getOrderColumn());?>"/>
		<input type="hidden" id="order_type" name="order_type" value="<?=$h($ctrlAcctQry->getOrderType());?>"/>
		<input type="hidden" id="msg_type" name="msg_type" value=""/>
		<input type="hidden" id="msg_txt" name="msg_txt" value=""/>
	</form>

	<section id="div_result" class="k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white" aria-label="Resultados">
		<div class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-3 k:border-b k:border-line k:px-5 k:py-4">
			<span class="k:text-[15px]"><strong class="k:font-semibold"><?=(int) $pagination->getTotalReg()?> <?=$pagination->getTotalReg() == 1 ? "conta" : "contas"?></strong>
				<?php if ($pagination->getTotalReg() > 0) { ?><span class="k:text-muted"> · exibindo <?=$pagination->showDetailsPag();?></span><?php } ?>
			</span>
			<span class="k:text-[13px] k:text-muted">Clique em uma conta para ver as parcelas</span>
		</div>
		<div class="k-focus k:overflow-x-auto" tabindex="0" role="region" aria-label="Tabela (role para o lado se precisar)">
			<table id="table_query_open" class="k-table">
				<thead>
					<tr>
						<th class="column-order k-focus" tabindex="0" data-col="acc.id" title="Ordenar por número">Nº <?=$ctrlAcctQry->getImgOrder('acc.id');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="acc.date_hr_proc" title="Ordenar por data">Criada em <?=$ctrlAcctQry->getImgOrder('acc.date_hr_proc');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="acc.client_user_person_id" title="Ordenar por cliente">Cliente <?=$ctrlAcctQry->getImgOrder('acc.client_user_person_id');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="acc.validate_month" title="Ordenar por parcelas">Parcelas <?=$ctrlAcctQry->getImgOrder('acc.validate_month');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="acc.status" title="Ordenar por status">Status <?=$ctrlAcctQry->getImgOrder('acc.status');?></th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?php if ($length == 0) { ?>
					<tr><td colspan="5" class="k:py-10 k:text-center k:text-muted">Nenhuma conta encontrada com esses filtros.</td></tr>
					<?php } ?>
					<?php foreach ($_accounts as $accountList) {
						$personList = $accountList->getClient()->getUser()->getPerson(); ?>
					<tr class="tr_hover" title="Clique para ver as parcelas">
						<td class="k:font-semibold k:text-kade-900">
							#<?=$h($accountList->getId())?>
							<input type="hidden" class="acc_hidden_id" value="<?=$h($accountList->getId())?>">
						</td>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=$accountList->getDtHrProc()->format("d/m/Y H:i")?></td>
						<td>
							<span class="k:block k:font-medium"><?=$h($personList->getName())?></span>
							<span class="k:text-[13px] k:text-muted">Nº <?=$h($personList->getId())?></span>
						</td>
						<td><?=$h($accountList->getValidateMonth())?></td>
						<td><span class="k-badge <?=$h($accountList->getStatus())?>"><?=$h($accountList->getDescStatus())?></span></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php include(dirname(__FILE__)."/partials/pagination.php"); ?>
	</section>

<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>
<div id="dialog-working" title="Processando" style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" alt="" />
	<p id="dialog-working.message">Aguarde, processando ...</p>
</div>
