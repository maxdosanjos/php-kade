<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/CustumerQueryCtrl.class.php");
require_once(dirname(__FILE__)."/partials/helpers.php");

$userSuper = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$ctrlCustQry->onSearchArray();
$_custumers    = $ctrlCustQry->getCustumers() ?? array();
$length        = count($_custumers);
$custumerParam = $ctrlCustQry->getCustumerParam();
$personParam   = $custumerParam->getUser()->getPerson();
$pagination    = $ctrlCustQry->getPagination();
$message       = $ctrlCustQry->getMessage();

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$typeParam = ($personParam != null) ? $personParam->getType() : "";
$isPF = ($typeParam == Person::PF);
$isPJ = ($typeParam == Person::PJ);
$segLabel = "k:flex k:min-h-10 k:cursor-pointer k:items-center k:justify-center k:rounded-lg k:px-3 k:text-sm k:font-semibold k:text-muted";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/CustumerBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/CustumerBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){CustumerBean.getInstance().initQueryOpen();});</script>
<?php if ($message != null && $message->getDesc() != "") { ?>
	<script type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify(<?=json_encode($message->getDesc())?>, <?=json_encode($message->getType())?>);});</script>
<?php } ?>

<div class="k-ui k:mx-auto k:flex k:max-w-[880px] k:flex-col k:gap-5 k:px-4 k:py-6 k:text-ink">
	<div class="k:flex k:flex-col k:gap-1">
		<h1 class="k:font-display k:text-2xl k:font-bold k:text-kade-950">Escolher cliente</h1>
		<p class="k:text-[15px] k:text-muted">Busque e clique no cliente para usá-lo na conta.</p>
	</div>

	<form id="custumer_query_open" name="custumer_query_open" action="<?=$h($_SERVER["REQUEST_URI"])?>" method="post" class="k:flex k:flex-col k:gap-4 k:rounded-2xl k:border k:border-line k:bg-white k:p-5">
		<fieldset class="k:m-0 k:border-0 k:p-0">
			<legend class="k:mb-1.5 k:text-sm k:font-medium">Tipo</legend>
			<div class="k:inline-grid k:grid-cols-3 k:gap-1 k:rounded-xl k:bg-surface k:p-1">
				<input type="radio" name="type_person" value="" id="person_all" class="k:peer/all k:sr-only" <?=(!$isPF && !$isPJ) ? "checked" : ""?>/>
				<label for="person_all" id="person_all_label" class="<?=$segLabel?> k:peer-checked/all:bg-white k:peer-checked/all:text-kade-900 k:peer-checked/all:shadow-sm">Todos</label>
				<input type="radio" name="type_person" value="PF" id="person_pf" class="k:peer/pf k:sr-only" <?=$isPF ? "checked" : ""?>/>
				<label for="person_pf" id="person_pf_label" class="<?=$segLabel?> k:peer-checked/pf:bg-white k:peer-checked/pf:text-kade-900 k:peer-checked/pf:shadow-sm">Física</label>
				<input type="radio" name="type_person" value="PJ" id="person_pj" class="k:peer/pj k:sr-only" <?=$isPJ ? "checked" : ""?>/>
				<label for="person_pj" id="person_pj_label" class="<?=$segLabel?> k:peer-checked/pj:bg-white k:peer-checked/pj:text-kade-900 k:peer-checked/pj:shadow-sm">Jurídica</label>
			</div>
		</fieldset>
		<div id="div_fields_pf"<?=$isPJ ? ' style="display:none;"' : ""?>>
			<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2">
				<label class="k-label" for="custumer_cpf">CPF
					<input type="text" id="custumer_cpf" name="custumer_cpf" maxlength="14" inputmode="numeric" class="k-field mask.cpf input_per input_clear" value="<?=$isPF ? $h($personParam->getCpf()) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_name">Nome
					<input type="text" id="custumer_name" name="custumer_name" maxlength="120" class="k-field input_per input_clear" value="<?=$isPF ? $h($personParam->getName()) : ""?>"/>
				</label>
			</div>
		</div>
		<div id="div_fields_pj"<?=$isPF ? ' style="display:none;"' : ""?>>
			<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2">
				<label class="k-label" for="custumer_cnpj">CNPJ
					<input type="text" id="custumer_cnpj" name="custumer_cnpj" maxlength="18" inputmode="numeric" class="k-field mask.cnpj input_per input_clear" value="<?=$isPJ ? $h($personParam->getCnpj()) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_rsoc">Razão social
					<input type="text" id="custumer_rsoc" name="custumer_rsoc" maxlength="120" class="k-field input_per input_clear" value="<?=$isPJ ? $h($personParam->getName()) : ""?>"/>
				</label>
			</div>
		</div>
		<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5">
			<span id="span_loc_msg" class="k:mr-auto"></span>
			<button type="button" id="button_reset" class="k-btn">Limpar</button>
			<button type="submit" id="button_search" class="k-btn k-btn-primary">Buscar</button>
		</div>
		<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
		<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
		<input type="hidden" id="order_column" name="order_column" value="<?=$h($ctrlCustQry->getOrderColumn());?>"/>
		<input type="hidden" id="order_type" name="order_type" value="<?=$h($ctrlCustQry->getOrderType());?>"/>
	</form>

	<section id="div_result" class="k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white" aria-label="Clientes">
		<div class="k-focus k:overflow-x-auto" tabindex="0" role="region" aria-label="Tabela (role para o lado se precisar)">
			<table id="table_query_open" class="k-table k:min-w-[560px]">
				<thead>
					<tr>
						<th class="column-order k-focus" tabindex="0" data-col="cli.user_person_id">Nº <?=$ctrlCustQry->getImgOrder('cli.user_person_id');?></th>
						<th class="column-order k-focus" tabindex="0" data-col="per.name">Nome / razão social <?=$ctrlCustQry->getImgOrder('per.name');?></th>
						<th>CPF / CNPJ</th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?php if ($length == 0) { ?>
					<tr><td colspan="3" class="k:py-8 k:text-center k:text-muted">Nenhum cliente encontrado.</td></tr>
					<?php } ?>
					<?php foreach ($_custumers as $custumerList) {
						$personList = $custumerList->getUser()->getPerson(); ?>
					<tr class="tr_hover tr_mc_code" title="Clique para escolher este cliente">
						<td class="k:font-semibold k:text-kade-900">
							<?=$h($personList->getId())?>
							<input type="hidden" class="cust_hidden_id" value="<?=$h($personList->getId())?>">
						</td>
						<td>
							<span class="span_cli_name_mc k:font-medium"><?=$h($personList->getName())?></span>
							<span class="k:block k:text-[13px] k:text-muted"><?=$h($personList->getDescType())?></span>
						</td>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=$h(k_fmt_doc($personList->getType() == Person::PJ ? $personList->getCnpj() : $personList->getCpf()))?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php include(dirname(__FILE__)."/partials/pagination.php"); ?>
	</section>
</div>
