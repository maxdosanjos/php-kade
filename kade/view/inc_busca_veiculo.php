<?
	if ($viewExtCtrl == null || $ctrlVehQry == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}

	$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
	$userSuper = User::isUserSuper();
	$typeView  = $ctrlVehQry->getTypeView();
	$showSts   = ($typeView == VehicleQueryCtrl::REG_CREATED || $userSuper);
	$formAction = $h($_SERVER["REQUEST_URI"]);

	if ($typeView == VehicleQueryCtrl::REG_CREATED) {
		$pageTitle = "Registros realizados";
		$pageDesc  = "Veículos que você cadastrou e o status de cada registro.";
	} elseif ($typeView == VehicleQueryCtrl::REG_UTILIZED) {
		$pageTitle = "Registros utilizados";
		$pageDesc  = "Veículos que você já marcou como utilizados.";
	} else {
		$pageTitle = "Busca por veículos";
		$pageDesc  = "Filtre por tipo, região e período para encontrar caminhões disponíveis.";
	}
	// "Mais filtros" começa aberto quando algum desses filtros está em uso
	$moreOpen = ($vehTravParam->getId() > 0 || $vehTravParam->getStatus() != "");
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/VehicleTravelingBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/VehicleTravelingBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){VehicleTravelingBean.getInstance().initQueryOpen();});</script>
<?if($message!=null && $message->getDesc()!=""){?>
	<script type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify(<?=json_encode($message->getDesc())?>, <?=json_encode($message->getType())?>);});</script>
<?	} ?>

<div class="k-ui k:bg-surface k:text-ink">
	<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-wrap k:items-start k:gap-6 k:px-4 k:py-8 k:md:px-6 k:md:py-10">

		<div class="k:w-full k:lg:w-60 k:lg:shrink-0">
			<?php include(dirname(__FILE__)."/partials/menu_int.php"); ?>
		</div>

		<div class="k:flex k:min-w-0 k:flex-1 k:basis-[640px] k:flex-col k:gap-6">
			<div class="k:flex k:flex-col k:gap-1.5">
				<h1 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]"><?=$pageTitle?></h1>
				<p class="k:text-base k:text-muted"><?=$pageDesc?></p>
			</div>

			<!-- Filtros -->
			<form id="vehicle_query_open" name="vehicle_query_open" action="<?=$formAction?>" method="post" class="k:flex k:flex-col k:gap-5 k:rounded-2xl k:border k:border-line k:bg-white k:p-5 k:md:p-6">
				<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2 k:xl:grid-cols-4">
					<label class="k-label" for="vehicle_type">Tipo de veículo
						<select id="vehicle_type" name="vehicle_type" class="k-field input_clear">
							<option value="">Todos os tipos</option>
							<?php foreach ($_vechicleTypes as $vehicleType){?>
								<option value="<?=$h($vehicleType->getId())?>" <?=($vehTravParam->getVehicleType()->getId() == $vehicleType->getId())?"selected":""?>><?=$h($vehicleType->getDescr())?></option>
							<?php } ?>
						</select>
					</label>
					<label class="k-label" for="vehicle_region" id="vehicle_region_label">UF
						<select id="vehicle_region" name="vehicle_region" class="k-field input_clear">
							<option value="">Todas</option>
							<?php foreach ($_ufList as $uf=>$uf_name){?>
								<option value="<?=$h($uf)?>" <?=($vehTravParam->getAddress()->getState() == $uf)?"selected":""?>><?=$h($uf)?> - <?=$h($uf_name)?></option>
							<?php } ?>
						</select>
					</label>
					<label class="k-label" for="vehicle_city">Cidade
						<input type="text" id="vehicle_city" name="vehicle_city" maxlength="120" placeholder="Ex.: Campinas" class="k-field input_clear" value="<?=$h($vehTravParam->getAddress()->getCity())?>"/>
					</label>
					<label class="k-label" for="vehicle_zipcode" id="vehicle_zipcode_label">CEP
						<input type="text" id="vehicle_zipcode" name="vehicle_zipcode" maxlength="10" placeholder="00000-000" class="k-field mask.cep input_clear" value="<?=$h($vehTravParam->getAddress()->getCep())?>"/>
					</label>
				</div>

				<details<?=$moreOpen ? " open" : ""?>>
					<summary class="k-focus k:inline-flex k:min-h-11 k:cursor-pointer k:list-none k:items-center k:gap-1.5 k:rounded k:text-sm k:font-semibold k:text-kade-900">
						Mais filtros (período<?=$userSuper ? ", status" : ""?>, nº do registro)
						<svg class="k-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
					</summary>
					<div class="k:mt-3 k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2 k:xl:grid-cols-4">
						<label class="k-label" for="vehicle_dt_init">Período: de
							<input type="text" id="vehicle_dt_init" name="vehicle_dt_init" maxlength="10" class="k-field mask.date input_clear" value="<?=$ctrlVehQry->getDtHrInit()->format("d.m.Y")?>"/>
						</label>
						<label class="k-label" for="vehicle_dt_end">até
							<input type="text" id="vehicle_dt_end" name="vehicle_dt_end" maxlength="10" class="k-field mask.date input_clear" value="<?=$ctrlVehQry->getDtHrEnd()->format("d.m.Y")?>"/>
						</label>
						<?php if($userSuper){?>
						<label class="k-label" for="vehicle_status">Status
							<select id="vehicle_status" name="vehicle_status" class="k-field input_clear">
								<option value="">Todos</option>
								<?php foreach (VehicleTraveling::listStatus() as $status=>$statuNm){?>
									<option value="<?=$h($status)?>" <?=($vehTravParam->getStatus() == $status)?"selected":""?>><?=$h($statuNm)?></option>
								<?php } ?>
							</select>
						</label>
						<?php }?>
						<label class="k-label" for="vehicle_id">Nº do registro
							<input type="text" id="vehicle_id" name="vehicle_id" maxlength="10" inputmode="numeric" class="k-field input_clear mask.numeric" value="<?=($vehTravParam->getId() > 0)?$h($vehTravParam->getId()):""?>" />
						</label>
					</div>
				</details>

				<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5">
					<span id="span_loc_msg" class="k:mr-auto"></span>
					<?php if($userSuper){?>
					<button type="button" id="button_csv" class="k-btn">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
						Exportar CSV
					</button>
					<?php }?>
					<button type="button" id="button_reset" class="k-btn">Limpar</button>
					<button type="submit" id="button_search" class="k-btn k-btn-primary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
						Buscar
					</button>
				</div>

				<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
				<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
				<input type="hidden" id="order_column" name="order_column" value="<?=$h($ctrlVehQry->getOrderColumn());?>"/>
				<input type="hidden" id="order_type" name="order_type" value="<?=$h($ctrlVehQry->getOrderType());?>"/>
				<input type="hidden" id="msg_type" name="msg_type" value=""/>
				<input type="hidden" id="msg_txt" name="msg_txt" value=""/>
				<input type="hidden" id="view_type" name="view_type" value="<?=$h($typeView)?>"/>
			</form>

			<!-- Resultados -->
			<section id="div_result" class="k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white" aria-label="Resultados">
				<div class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-3 k:border-b k:border-line k:px-5 k:py-4">
					<span class="k:text-[15px]"><strong class="k:font-semibold"><?=(int) $pagination->getTotalReg()?> <?=$pagination->getTotalReg() == 1 ? "registro" : "registros"?></strong>
						<?php if ($pagination->getTotalReg() > 0) { ?><span class="k:text-muted"> · exibindo <?=$pagination->showDetailsPag();?></span><?php } ?>
					</span>
					<span class="k:text-[13px] k:text-muted">Clique em um registro para ver os detalhes</span>
				</div>
				<div class="k:overflow-x-auto">
					<table id="table_query_open" class="k-table">
						<thead>
							<tr>
								<th class="column-order" lang="vt.id" title="Ordenar por registro">Registro <?=$ctrlVehQry->getImgOrder('vt.id');?></th>
								<th class="column-order" lang="vt.date_hr_proc" title="Ordenar por data">Criado em <?=$ctrlVehQry->getImgOrder('vt.date_hr_proc');?></th>
								<th class="column-order" lang="vt.vehicle_type_id" title="Ordenar por tipo de veículo">Tipo de veículo <?=$ctrlVehQry->getImgOrder('vt.vehicle_type_id');?></th>
								<th class="column-order" lang="ad.city" title="Ordenar por cidade">Cidade <?=$ctrlVehQry->getImgOrder('ad.city');?></th>
								<th class="column-order" lang="ad.state" title="Ordenar por UF">UF <?=$ctrlVehQry->getImgOrder('ad.state');?></th>
								<th class="column-order" lang="ad.cep" title="Ordenar por CEP">CEP <?=$ctrlVehQry->getImgOrder('ad.cep');?></th>
								<? if($showSts) {?>
								<th class="column-order" lang="vt.status" title="Ordenar por status">Status <?=$ctrlVehQry->getImgOrder('vt.status');?></th>
								<? } ?>
							</tr>
						</thead>
						<tbody class="tbody_query">
							<? if ($length == 0) { ?>
							<tr>
								<td colspan="<?=$showSts ? 7 : 6?>" class="k:py-10 k:text-center k:text-muted">Nenhum veículo encontrado com esses filtros.</td>
							</tr>
							<? } ?>
							<?
								for ($i = 0; $i < $length; $i++) {
									$vehicleTrav = $_vehicleTrav[$i];
									$vehicleType = $vehicleTrav->getVehicleType ( );
									$address 	 = $vehicleTrav->getAddress ( );
							?>
							<tr class="tr_hover" title="Clique para ver os detalhes">
								<td class="k:font-semibold k:text-kade-900">
									#<?=$h($vehicleTrav->getId())?>
									<input type="hidden" class="trav_hidden_id" value="<?=$h($vehicleTrav->getId())?>">
								</td>
								<td class="k:whitespace-nowrap k:text-ink-soft"><?=$vehicleTrav->getDateHrProc()->format("d/m/Y H:i")?></td>
								<td><?=$h($vehicleType->getDescr())?></td>
								<td><?=$h($address->getCity())?></td>
								<td><?=$h($address->getState())?></td>
								<td class="k:whitespace-nowrap k:text-ink-soft"><?=$h($address->getCepMask())?></td>
								<? if($showSts) {?>
								<td><span class="k-badge <?=$h($vehicleTrav->getStatus())?>"><?=$h($vehicleTrav->getDescStatus())?></span></td>
								<? } ?>
							</tr>
							<? } ?>
						</tbody>
					</table>
				</div>
				<?php include(dirname(__FILE__)."/partials/pagination.php"); ?>
			</section>
		</div>
	</div>
</div>

<!-- Detalhes do registro (diálogo do jQuery UI, preenchido por VehicleTravelingBean) -->
<div id="view_details" title="Detalhes do registro" style="display:none">
	<form id="vehicle_detail_query" name="vehicle_detail_query" action="<?=$formAction?>" method="post" class="k-ui k:flex k:flex-col k:gap-5">
		<input type="hidden" id="trav_hidden_id_sel" value="">
		<input type="hidden" id="trav_hidden_id_sel_add" value="">
		<div class="k:flex k:items-start k:justify-between k:gap-3">
			<div class="k:flex k:flex-col">
				<span class="k:text-[13px] k:text-muted">Registro</span>
				<span class="k:font-display k:text-[22px] k:font-semibold k:text-kade-950">#<span id="div_vehicle_id"></span></span>
			</div>
			<? if($showSts) {?>
			<span id="div_vehicle_sts" class="k-badge"></span>
			<? } ?>
		</div>
		<div class="k:grid k:grid-cols-2 k:gap-x-4 k:gap-y-3 k:text-[15px]">
			<div class="k:col-span-2">
				<span class="k:block k:text-[13px] k:text-muted">Tipo de veículo</span>
				<span id="div_vehicle_type" class="k:font-semibold"></span>
			</div>
			<div>
				<span class="k:block k:text-[13px] k:text-muted">Cidade / Estado</span>
				<span id="div_vehicle_city"></span> / <span id="div_vehicle_state"></span>
			</div>
			<div>
				<span class="k:block k:text-[13px] k:text-muted">CEP</span>
				<span id="div_vehicle_cep"></span>
			</div>
			<div>
				<span class="k:block k:text-[13px] k:text-muted">Criado em</span>
				<span id="div_vehicle_dt_proc"></span>
			</div>
			<div>
				<span class="k:block k:text-[13px] k:text-muted">Fonte</span>
				<span id="div_vehicle_source"></span>
			</div>
			<? if($typeView == VehicleQueryCtrl::REG_UTILIZED || $showSts) {?>
			<div>
				<span id="label_vehicle_dt_used" class="k:block k:text-[13px] k:text-muted">Utilizado em</span>
				<span id="div_vehicle_dt_used"></span>
			</div>
			<? } ?>
		</div>
		<div class="k:flex k:flex-col k:gap-2 k:rounded-xl k:bg-surface k:p-4">
			<span class="k:text-xs k:font-semibold k:uppercase k:tracking-wider k:text-muted">Contato</span>
			<span id="div_vehicle_contact_name" class="k:text-base k:font-semibold"></span>
			<span id="div_vehicle_contact_phone" class="k:text-base k:font-semibold k:text-kade-900"></span>
		</div>
		<div class="k:flex k:flex-wrap k:justify-end k:gap-2.5">
			<button type="button" id="button_close_detail" class="k-btn">Fechar</button>
			<? if($typeView == VehicleQueryCtrl::SEARCH_BY_ULT && !$userSuper) {?>
				<button type="button" id="button_check_view" class="k-btn k-btn-dark">Marcar como utilizado</button>
			<? } elseif($showSts) {?>
				<button type="button" id="button_check_cancel" class="k-btn k-btn-danger">Cancelar registro</button>
			<? } ?>
		</div>
	</form>
</div>

<form name="form_view_detail" id="form_view_detail" method="post" target="detail_vec_iframe" action="../VehicleQueryCtrl.ctrlExt/viewDetail">
	 <input type="hidden" name="vehicle_id" id="vehicle_id_detail" />
	 <input type="hidden" name="view_type" id="view_type_detail" />
</form>
<iframe id="detail_vec_iframe" name="detail_vec_iframe" class="iframe_ctrl" src="" title="Carregamento do detalhe" hidden></iframe>

<form name="form_setsts" id="form_setsts" method="post" target="setsts_vec_iframe" action="">
	<input type="hidden" name="vehicle_id" id="vehicle_id_setsts" />
	<input type="hidden" name="vehicle_address_id" id="address_id_setsts" />
</form>
<iframe id="setsts_vec_iframe" name="setsts_vec_iframe" class="iframe_ctrl" src="" title="Atualização de status" hidden></iframe>

<div id="dialog-working" title="Processando" style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" alt="" />
	<p id="dialog-working.message">Aguarde, processando ...</p>
</div>
<div id="dialog-result" title="Resultado" style="display:none;">
	<div>
		<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
		<div id="dialog-result.messageText" style="border:1px solid #aaa;padding:10px;margin-top:10px;font-size:14px;"></div>
	</div>
</div>
