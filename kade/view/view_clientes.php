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

$_ufList = $ctrlCustQry->getListUf();
$ctrlCustQry->onSearchArray();
$_custumers  = $ctrlCustQry->getCustumers() ?? array();
$length      = count($_custumers);

$custumerParam = $ctrlCustQry->getCustumerParam();
$userParam     = $custumerParam->getUser();
$personParam   = $userParam->getPerson();
$addressParam  = ($personParam != null && $personParam->getAddress() != null) ? $personParam->getAddress() : new Address();
$phoneParam    = ($personParam != null && $personParam->getPhone() != null) ? $personParam->getPhone() : new Phone();

$pagination = $ctrlCustQry->getPagination();
$message    = $ctrlCustQry->getMessage();

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$typeParam = ($personParam != null) ? $personParam->getType() : "";
$isPF = ($typeParam == Person::PF);
$isPJ = ($typeParam == Person::PJ);
$isento = ($isPJ && $personParam->getIe() == PersonEntity::ISENTO);
$moreOpen = (($personParam != null && $personParam->getId() > 0) || $phoneParam->getPhone() != "" || ($personParam != null && $personParam->getEmail() != "") || $addressParam->getCep() != "");

$pageTitle = "Clientes";
$pageDesc  = "Consulte, filtre e edite os clientes cadastrados.";
$grid4 = "k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2 k:xl:grid-cols-4";
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
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>

	<form id="custumer_query_open" name="custumer_query_open" action="<?=$h($_SERVER["REQUEST_URI"])?>" method="post" class="k:flex k:flex-col k:gap-5 k:rounded-2xl k:border k:border-line k:bg-white k:p-5 k:md:p-6">
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
			<div class="<?=$grid4?>">
				<label class="k-label" for="custumer_cnpj">CNPJ
					<input type="text" id="custumer_cnpj" name="custumer_cnpj" maxlength="18" inputmode="numeric" class="k-field mask.cnpj input_per input_clear" value="<?=$isPJ ? $h($personParam->getCnpj()) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_rsoc">Razão social
					<input type="text" id="custumer_rsoc" name="custumer_rsoc" maxlength="120" class="k-field input_per input_clear" value="<?=$isPJ ? $h($personParam->getName()) : ""?>"/>
				</label>
				<div class="k-label">
					<label for="custumer_ie">Inscrição estadual</label>
					<input type="text" id="custumer_ie" name="custumer_ie" maxlength="18" inputmode="numeric" class="k-field mask.numeric input_per" value="<?=($isPJ && !$isento) ? $h($personParam->getIe()) : ""?>"/>
					<label id="custumer_ie_isento_label" class="k:mt-2 k:inline-flex k:cursor-pointer k:items-center k:gap-2 k:text-sm k:font-normal k:text-ink-soft">
						<input type="checkbox" id="custumer_ie_isento" name="custumer_ie_isento" value="on" class="k:h-4 k:w-4 k:accent-kade-900" <?=$isento ? "checked" : ""?>/> Isento
					</label>
				</div>
				<label class="k-label" for="custumer_contact">Contato
					<input type="text" id="custumer_contact" name="custumer_contact" maxlength="120" class="k-field input_per input_clear" value="<?=$isPJ ? $h($personParam->getContact()) : ""?>"/>
				</label>
			</div>
		</div>

		<div class="<?=$grid4?>">
			<label class="k-label" for="custumer_status">Status
				<select id="custumer_status" name="custumer_status" class="k-field">
					<option value="">Todos</option>
					<?php foreach (User::listStatus() as $status=>$statusNm){?>
					<option value="<?=$h($status)?>" <?=($userParam->getStatus() == $status) ? "selected" : ""?>><?=$h($statusNm)?></option>
					<?php } ?>
				</select>
			</label>
			<label class="k-label" for="custumer_region" id="custumer_region_label">UF
				<select id="custumer_region" name="custumer_region" class="k-field input_clear">
					<option value="">Todas</option>
					<?php foreach ($_ufList as $uf=>$uf_name){?>
					<option value="<?=$h($uf)?>" <?=($addressParam->getState() == $uf) ? "selected" : ""?>><?=$h($uf)?> - <?=$h($uf_name)?></option>
					<?php } ?>
				</select>
			</label>
			<label class="k-label k:xl:col-span-2" for="custumer_city">Cidade
				<input type="text" id="custumer_city" name="custumer_city" maxlength="120" class="k-field input_clear" value="<?=$h($addressParam->getCity())?>"/>
			</label>
		</div>

		<details<?=$moreOpen ? " open" : ""?>>
			<summary class="k-focus k:inline-flex k:min-h-11 k:cursor-pointer k:list-none k:items-center k:gap-1.5 k:rounded k:text-sm k:font-semibold k:text-kade-900">
				Mais filtros (nº, telefone, e-mail, CEP)
				<svg class="k-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
			</summary>
			<div class="k:mt-3 <?=$grid4?>">
				<label class="k-label" for="custumer_id">Nº do cliente
					<input type="text" id="custumer_id" name="custumer_id" maxlength="10" inputmode="numeric" class="k-field input_clear mask.numeric" value="<?=($personParam != null && $personParam->getId() > 0) ? $h($personParam->getId()) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_phone">Telefone
					<input type="tel" id="custumer_phone" name="custumer_phone" maxlength="15" class="k-field mask.phone2_with_ddd" value="<?=$h($phoneParam->getPhoneMask())?>"/>
				</label>
				<label class="k-label" for="custumer_mail">E-mail
					<input type="text" id="custumer_mail" name="custumer_mail" maxlength="255" class="k-field" value="<?=$personParam != null ? $h($personParam->getEmail()) : ""?>"/>
				</label>
				<label class="k-label" for="custumer_zipcode" id="custumer_zipcode_label">CEP
					<input type="text" id="custumer_zipcode" name="custumer_zipcode" maxlength="10" inputmode="numeric" class="k-field mask.cep input_clear" value="<?=$h($addressParam->getCep())?>"/>
				</label>
			</div>
		</details>

		<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5">
			<span id="span_loc_msg" class="k:mr-auto"></span>
			<button type="button" id="button_csv" class="k-btn">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
				Exportar CSV
			</button>
			<button type="button" id="button_reset" class="k-btn">Limpar</button>
			<button type="submit" id="button_search" class="k-btn k-btn-primary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
				Buscar
			</button>
		</div>

		<input type="hidden" id="custumer_region_nm" name="custumer_region_nm"/>
		<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
		<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
		<input type="hidden" id="order_column" name="order_column" value="<?=$h($ctrlCustQry->getOrderColumn());?>"/>
		<input type="hidden" id="order_type" name="order_type" value="<?=$h($ctrlCustQry->getOrderType());?>"/>
		<input type="hidden" id="msg_type" name="msg_type" value=""/>
		<input type="hidden" id="msg_txt" name="msg_txt" value=""/>
	</form>

	<section id="div_result" class="k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white" aria-label="Resultados">
		<div class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-3 k:border-b k:border-line k:px-5 k:py-4">
			<span class="k:text-[15px]"><strong class="k:font-semibold"><?=(int) $pagination->getTotalReg()?> <?=$pagination->getTotalReg() == 1 ? "cliente" : "clientes"?></strong>
				<?php if ($pagination->getTotalReg() > 0) { ?><span class="k:text-muted"> · exibindo <?=$pagination->showDetailsPag();?></span><?php } ?>
			</span>
			<span class="k:text-[13px] k:text-muted">Clique em um cliente para editar</span>
		</div>
		<div class="k:overflow-x-auto">
			<table id="table_query_open" class="k-table">
				<thead>
					<tr>
						<th class="column-order" lang="cli.user_person_id" title="Ordenar por número">Nº <?=$ctrlCustQry->getImgOrder('cli.user_person_id');?></th>
						<th class="column-order" lang="per.name" title="Ordenar por nome">Nome / razão social <?=$ctrlCustQry->getImgOrder('per.name');?></th>
						<th>CPF / CNPJ</th>
						<th class="column-order" lang="addr.city" title="Ordenar por cidade">Cidade <?=$ctrlCustQry->getImgOrder('addr.city');?></th>
						<th class="column-order" lang="addr.state" title="Ordenar por UF">UF <?=$ctrlCustQry->getImgOrder('addr.state');?></th>
						<th class="column-order" lang="usr.date_cad" title="Ordenar por data de cadastro">Cadastro <?=$ctrlCustQry->getImgOrder('usr.date_cad');?></th>
						<th class="column-order" lang="usr.status" title="Ordenar por status">Status <?=$ctrlCustQry->getImgOrder('usr.status');?></th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?php if ($length == 0) { ?>
					<tr><td colspan="7" class="k:py-10 k:text-center k:text-muted">Nenhum cliente encontrado com esses filtros.</td></tr>
					<?php } ?>
					<?php foreach ($_custumers as $custumerList) {
						$userList    = $custumerList->getUser();
						$personList  = $userList->getPerson();
						$addressList = $personList->getAddress() ?? new Address(); ?>
					<tr class="tr_hover tr_edit" title="Clique para editar">
						<td class="k:font-semibold k:text-kade-900">
							<?=$h($personList->getId())?>
							<input type="hidden" class="cust_hidden_id" value="<?=$h($personList->getId())?>">
						</td>
						<td>
							<span class="k:block k:font-medium"><?=$h($personList->getName())?></span>
							<span class="k:text-[13px] k:text-muted"><?=$h($personList->getDescType())?> · <?=$h($userList->getLogin())?></span>
						</td>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=$h(k_fmt_doc($personList->getType() == Person::PJ ? $personList->getCnpj() : $personList->getCpf()))?></td>
						<td><?=$h($addressList->getCity())?></td>
						<td><?=$h($addressList->getState())?></td>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=$userList->getDateCad()->format("d/m/Y")?></td>
						<td><span class="k-badge <?=$h($userList->getStatus())?>"><?=$h($userList->getDescStatus())?></span></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php include(dirname(__FILE__)."/partials/pagination.php"); ?>
	</section>

<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>
