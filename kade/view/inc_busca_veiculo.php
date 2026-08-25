<?
	if ($viewExtCtrl == null || $ctrlVehQry == null){
		header("HTTP/1.0 404 Not Found");
		exit ();
	}
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/busca_veiculo_ext.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/VehicleTravelingBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();VehicleTravelingBean.getInstance().initQueryOpen();});</script>

<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify("<?=$message->getDesc();?>", "<?=$message->getType();?>");});</script>
<?	} ?>
<div id="div_body_busc_veic" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<div id="div_search">
			<form id="vehicle_query_open" name="vehicle_query_open" action="<?=$_SERVER["REQUEST_URI"];?>" method="post">
				<fieldset id="fieldset_filter" class="ui-widget ui-widget-content ui-corner-all">
					<legend>
					<? if($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_CREATED) {?>
						Registros realizados
					<? } elseif($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_UTILIZED) {?>
						Registros utilizados
					<? } elseif($ctrlVehQry->getTypeView() == VehicleQueryCtrl::SEARCH_BY_ULT) {?>
						Buscar Veículos
					<? } ?>
					
					</legend>
					<div id="form_query_left">
						<div class="div_input">
							<label for="id" class="label_input">Registro</label>
							<input type="text" id="vehicle_id" name="vehicle_id" size="11" maxlength="10" class="input_clear mask.numeric" value="<?=($vehTravParam->getId() > 0)?$vehTravParam->getId():""?>" />
						</div>		
						<div class="div_input">
							<label for="vehicle_dt_init"  class="label_input">Período</label>
							<input type="text" id="vehicle_dt_init" name="vehicle_dt_init" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlVehQry->getDtHrInit()->format("d.m.Y")?>"/>
							 até 
							<input type="text" id="vehicle_dt_end" name="vehicle_dt_end" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlVehQry->getDtHrEnd()->format("d.m.Y")?>"/>
						</div>
						<div class="div_input">
							<label for="vehicle_type"  class="label_input">Tipo de Veículo</label>
							<select id="vehicle_type" name="vehicle_type" class="input_clear">
								<option value="" selected="selected"></option>
								<?php foreach ($_vechicleTypes as $vehicleType){?>
									<option value="<?=$vehicleType->getId()?>" <?=($vehTravParam->getVehicleType()->getId() == $vehicleType->getId())?"selected='true'":""?>><?=$vehicleType->getId()?> - <?=$vehicleType->getDescr()?></option>
								<?php } ?>
							</select>
						</div>
						<?php if($userSuper){?>
							<div class="div_input">
								<label for="vehicle_status"  class="label_input" title="Status da Mensagem" alt="Status da Mensagem">Status</label>
								<select id="vehicle_status" name="vehicle_status" class="input_clear">
								<option value="" selected="selected"></option>
									<?php foreach (VehicleTraveling::listStatus() as $status=>$statuNm){?>
										<option value="<?=$status?>" <?=($vehTravParam->getStatus() == $status)?"selected='true'":""?>><?=$statuNm?></option>
									<?php } ?>
								</select>
							</div>
						<?php }?>
						<br style="clear: both"/>
					</div>
					<div id="form_query_right">
						<div class="div_input">
				    		<label for="vehicle_zipcode" id="vehicle_zipcode_label"  class="label_input">CEP: </label>
				    		<input type="text" id="vehicle_zipcode" name="vehicle_zipcode" size="14" maxlength="10" class="mask.cep input_clear" value="<?=$vehTravParam->getAddress()->getCep()?>"/>
				    	</div>
				    	<div class="div_input">	
				    		<label for="vehicle_region" id="vehicle_region_label"  class="label_input">UF: </label>
				    		<select id="vehicle_region" name="vehicle_region" class="input_clear">
								<option value="" selected="selected"></option>
								<?php foreach ($_ufList as $uf=>$uf_name){?>
									<option value="<?=$uf?>" <?=($vehTravParam->getAddress()->getState() == $uf)?"selected='true'":""?>><?=$uf?> - <?=$uf_name?></option>
								<?php } ?>
							</select>
				    	</div>
				    	<div class="div_input">
				    		<label for="vehicle_city"  class="label_input">Cidade: </label>
				    		<input type="text" id="vehicle_city" name="vehicle_city" size="40" maxlength="120" class="input_clear" value="<?=$vehTravParam->getAddress()->getCity()?>"/>
				    	</div>
						<br style="clear: both"/>
					</div>
					<br style="clear: both"/>
			    	<div id="buttonBar" class="">
						<button id="button_search" class="btn_default" title="Salvar">
							<div class="div_btn_with_image"></div>
							<span>Buscar</span>
						</button>
						<span id="button_reset" class="btn_default" title="Voltar">
							<div class="div_btn_with_image"></div>
							<span>Limpar</span>
						</span>
						<?php if($userSuper){?>
						<span id="button_csv" class="btn_default" title="Exportar p/ CSV">
							<div class="div_btn_with_image"></div>
							<span>Exportar p/ CSV</span>
						</span>
						<?php }?>
					</div>
				</fieldset>
				<span id="span_loc_msg"></span>
				<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
				<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
				<input type="hidden" id="order_column" name="order_column" value="<?=$ctrlVehQry->getOrderColumn();?>"/>
				<input type="hidden" id="order_type" name="order_type" value="<?=$ctrlVehQry->getOrderType();?>"/>	
				<input type="hidden" id="msg_type" name="msg_type" value=""/>	
				<input type="hidden" id="msg_txt" name="msg_txt" value=""/>	
				<input type="hidden" id="view_type" name="view_type" value="<?=$ctrlVehQry->getTypeView()?>"/>	
				
			</form>
		</div>
		<div id="div_result">
			<table id="table_query_open" class="table table-bordered table-condensed">
				<thead class="table_head">
					<tr>
						<th class="column-order td_id" lang="vt.id" title="Clique para alternar a ordenação dos registros">Registro <span class="img_order"><?=$ctrlVehQry->getImgOrder('vt.id');?></span></th>
						<th class="column-order td_dthr" lang="vt.date_hr_proc" title="Clique para alternar a ordenação dos registros">Criado em <span class="img_order"><?=$ctrlVehQry->getImgOrder('vt.date_hr_proc');?></span></th>
						<th class="column-order td_tvehicle" lang="vt.vehicle_type_id" title="Clique para alternar a ordenação dos registros">Tipo Veículo <span class="img_order"><?=$ctrlVehQry->getImgOrder('vt.vehicle_type_id');?></span></th>
						<th class="column-order td_cep" lang="ad.cep" title="Clique para alternar a ordenação dos registros">CEP <span class="img_order"><?=$ctrlVehQry->getImgOrder('ad.cep');?></span></th>
						<th class="column-order td_city" lang="ad.city" title="Clique para alternar a ordenação dos registros">Cidade <span class="img_order"><?=$ctrlVehQry->getImgOrder('ad.city');?></span></th>
						<th class="column-order td_uf" lang="ad.state" title="Clique para alternar a ordenação dos registros">UF <span class="img_order"><?=$ctrlVehQry->getImgOrder('ad.state');?></span></th>
						<? if($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_CREATED || $userSuper) {?>
							<th class="column-order td_uf" lang="vt.status" title="Clique para alternar a ordenação dos registros">Status <span class="img_order"><?=$ctrlVehQry->getImgOrder('vt.status');?></span></th>
						<? } ?>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?
						$i = 0;
						while($i < $length){
							$vehicleTrav = $_vehicleTrav[$i];
							$vehicleType = $vehicleTrav->getVehicleType ( );
							$address 	 = $vehicleTrav->getAddress ( );
					?>
					<tr class="tr_hover" alt="Clique aqui para visualizar detalhes" title="Clique aqui para visualizar detalhes">
						<td class="td_id">
							<?=$vehicleTrav->getId()?>
							<input type="hidden" class="trav_hidden_id" value="<?=$vehicleTrav->getId()?>">
						</td>
						<td class="td_dthr"><?=$vehicleTrav->getDateHrProc()->format("d/m/Y H:i")?></td>
						<td class="td_tvehicle"><?=$vehicleType->getId()?> - <?=$vehicleType->getDescr()?></td>
						<td class="td_cep"><?=$address->getCepMask()?></td>
						<td class="td_city"><?=$address->getCity()?></td>
						<td class="td_uf"><?=$address->getState()?></td>
						<? if($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_CREATED || $userSuper) {?>
							<td class="td_uf <?=$vehicleTrav->getStatus()?>"><?=$vehicleTrav->getDescStatus()?></td>
						<? } ?>
					</tr>
					<? $i++; } ?>
				</tbody>
			</table>
			<div id="table_foot">
				<div class="details_pag">
					Registro(s) por Página: <?=$pagination->getEndLimit();?>
					<input type="hidden" id="param_regpag" class="xtextfield numeric formfield" name="param_regpag" value="<?=$pagination->getEndLimit();?>" size="3" readonly="true" /><br/>
					Número de Resultados: <span title="De - Até"><?=$pagination->showDetailsPag();?></span> 
										  <span title="Total de Registros">(<?=$pagination->getTotalReg();?>)</span>
				</div>
				<div class="nav_pag"> 
					<div class="nav_pag_left">
					<? if($pagination->getPagCurrent()!=1) { ?>		        
						<a href="#" class="nav_first" title="Ir para primeira página" lang="<?=$pagination->getStartPag();?>"></a> &nbsp;
						<a href="#" class="nav_prev" title="Ir para página anterior" lang="<?=$pagination->getPreviousPag();?>"></a>
					<? } ?>
					</div>
					<div class="nav_pag_right">
					<? if($pagination->getPagCurrent()!= $pagination->getTotalPag() && $pagination->getTotalReg() > 0) { ?> 
						<a href="#" class="nav_next" title="Ir para próxima página" lang="<?=$pagination->getNextPag();?>"></a> &nbsp;
						<a href="#" class="nav_end" title="Ir para última página"  lang="<?=$pagination->getTotalPag();?>"></a>
					 <?} ?>
					</div>
					 <div class="pags_number">
						<?=$pagination->getPagByNumber(2);?>
					</div>
				</div>
			</div>
		</div>
		
		<div id="view_details" title="Detalhes do Registro">
			<form id="vehicle_detail_query" name="vehicle_detail_query" action="<?=$_SERVER["REQUEST_URI"];?>" method="post">
			    <fieldset class="fieldset_detail">	
					<legend>Detalhes</legend>
					<div class="div_input">
						<label for="id" class="label_input">Registro</label> <div id="div_vehicle_id" class="div_input_reaonly"></div>
						<input type="hidden" id="trav_hidden_id_sel" value="">
						<input type="hidden" id="trav_hidden_id_sel_add" value="">
						
					</div>				
					<div class="div_input">
						<label for="dt_hr" class="label_input">Criado em</label> <div id="div_vehicle_dt_proc" class="div_input_reaonly"></div>
					</div>				
					<div class="div_input">
						<label for="tvehicle" class="label_input">Tipo de Veículo</label> <div id="div_vehicle_type" class="div_input_reaonly"></div>
					</div>
					<? if($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_UTILIZED || $ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_CREATED || $userSuper) {?>
						<div class="div_input">
							<label for="dt_used" id="label_vehicle_dt_used" class="label_input">Utilizado em</label> <div id="div_vehicle_dt_used" class="div_input_reaonly"></div>
						</div>
					<? } ?>
					<? if($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_CREATED || $userSuper) {?>
						<div class="div_input">
							<label for="sts" class="label_input">Status</label> <div id="div_vehicle_sts" class="div_input_reaonly"></div>
						</div>
					<? } ?>
				</fieldset>
				<fieldset class="fieldset_detail">	
					<legend>Localização</legend>
					<div class="div_input">
						<label for="cep" class="label_input">CEP</label> <div id="div_vehicle_cep" class="div_input_reaonly"></div>
					</div>
					<div class="div_input">
						<label for="city" class="label_input">Cidade</label> <div id="div_vehicle_city" class="div_input_reaonly"></div>
					</div>
					<div class="div_input">
						<label for="state" class="label_input">Estado</label> <div id="div_vehicle_state" class="div_input_reaonly"></div>
					</div>
				</fieldset>
				<fieldset class="fieldset_detail">	
					<legend>Contato</legend>
					<div class="div_input">
						<label for="phone" class="label_input">Telefone</label> <div id="div_vehicle_contact_phone" class="div_input_reaonly field_dest"></div>
					</div>
					<div class="div_input">
						<label for="contact" class="label_input">Contato</label> <div id="div_vehicle_contact_name" class="div_input_reaonly field_dest"></div>
					</div>
					<div class="div_input">
						<label for="contact" class="label_input">Fonte</label> <div id="div_vehicle_source" class="div_input_reaonly field_dest"></div>
					</div>
				</fieldset>
				<hr/>
				<span id="button_close_detail" class="btn_default" title="Fechar">
					<div class="div_btn_with_image"></div>
					<span>Fechar Detalhes</span>
				</span>
				<? if($ctrlVehQry->getTypeView() == VehicleQueryCtrl::SEARCH_BY_ULT && !$userSuper) {?>
					<span id="button_check_view" class="btn_default" title="Registro foi utilizado?">
						<div class="div_btn_with_image"></div>
						<span>Registro foi utilizado?</span>
					</span>
				<? } elseif($ctrlVehQry->getTypeView() == VehicleQueryCtrl::REG_CREATED || $userSuper) {?>
				<span id="button_check_cancel" class="btn_default" title="Cancelar Registro?">
					<div class="div_btn_with_image"></div>
					<span>Cancelar Registro?</span>
				</span>
				<? } ?>				
			</form>
		</div>
	</div>
	
</div>
<form name="form_view_detail" id="form_view_detail" method="post" target="detail_vec_iframe" action="../VehicleQueryCtrl.ctrlExt/viewDetail">
	 <input type="hidden" name="vehicle_id" id="vehicle_id_detail" />
	 <input type="hidden" name="view_type" id="view_type_detail" />
</form>
<iframe id="detail_vec_iframe" name="detail_vec_iframe" class="iframe_ctrl" src=""></iframe>

<form name="form_setsts" id="form_setsts" method="post" target="setsts_vec_iframe" action="">
	<input type="hidden" name="vehicle_id" id="vehicle_id_setsts" />
	<input type="hidden" name="vehicle_address_id" id="address_id_setsts" />
</form>
<iframe id="setsts_vec_iframe" name="setsts_vec_iframe" class="iframe_ctrl" src=""></iframe>

<div id="dialog-working" title="Processando" style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" />
	<p id="dialog-working.message">Aguarde, processando ...</p>
</div>
<div id="dialog-result" title="Resultado" style="display:none;">
	<div>
		<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
		<div id="dialog-result.messageText" style="border:1px solid #aaa;padding:10px;margin-top:10px;font-size:14px;"></div>
	</div> 
</div>	