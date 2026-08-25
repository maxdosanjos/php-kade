<?php 
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/AccountQueryCtrl.class.php");

$userSuper 	   	  = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

$ctrlAcctQry->onSearchArray();
$_accounts 	 	 = $ctrlAcctQry->getAccounts();
$length 		  = count($_accounts);

$accountParam    = $ctrlAcctQry->getAccountParam();
$clientParam	 = $accountParam->getClient();
$personParam	 = $clientParam->getUser()->getPerson();

$pagination 	  = $ctrlAcctQry->getPagination();
$message    	  = $ctrlAcctQry->getMessage();
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/busca_conta.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/AccountBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();AccountBean.getInstance().initQuery();});</script>
<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify("<?=$message->getDesc();?>", "<?=$message->getType();?>");});</script>
<?	} ?>
<div id="div_body_busca_conta" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<div id="div_search">
			<form id="conta_query_open" name="conta_query_open" action="<?=$_SERVER["REQUEST_URI"];?>" method="post">
				<fieldset id="fieldset_filter" class="ui-widget ui-widget-content ui-corner-all">
					<legend>Gerenciamento de Contas à receber</legend>
					
					<div class="div_input">
						<label for="id" class="label_input">Registro</label>
						<input type="text" id="account_id" name="account_id" size="20" maxlength="10" class="input_clear mask.numeric" value="<?=($accountParam->getId() > 0)?$accountParam->getId():""?>" />
					</div>		
					<div class="div_input">
						<label for="vehicle_dt_init"  class="label_input">Período</label>
						<input type="text" id="account_dt_init" name="account_dt_init" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlAcctQry->getDtHrInit()->format("d.m.Y")?>"/>
						 até 
						<input type="text" id="account_dt_end" name="account_dt_end" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlAcctQry->getDtHrEnd()->format("d.m.Y")?>"/>
					</div>
					<div class="div_input">
						<label for="id" class="label_input">Cód.Cliente</label>
						<input type="text" id="account_client_id" name="account_client_id" size="7" maxlength="10" class="client_id input_clear mask.numeric" value="<?=($personParam->getId() > 0)?$personParam->getId():""?>" />
						<span id="btn_match_client" alt="Pesquisar por Clientes"></span>
						<span id="span_account_client_name" class="client_name"><?=($personParam!=null && $personParam->getId() > 0)?$personParam->getName():""?></span>
						<input type="hidden" name="account_client_name" class="client_name" id="account_client_name" value="<?=($personParam!=null && $personParam->getId() > 0)?$personParam->getName():""?>" />
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
						<span id="button_new" class="btn_default" title="Nova Conta">
							<div class="div_btn_with_image"></div>
							<span>Nova Conta</span>
						</span>
					</div>
				</fieldset>		
				<span id="span_loc_msg"></span>
				<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
				<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
				<input type="hidden" id="order_column" name="order_column" value="<?=$ctrlAcctQry->getOrderColumn();?>"/>
				<input type="hidden" id="order_type" name="order_type" value="<?=$ctrlAcctQry->getOrderType();?>"/>	
				<input type="hidden" id="msg_type" name="msg_type" value=""/>	
				<input type="hidden" id="msg_txt" name="msg_txt" value=""/>		
			</form>
			
			<div id="div_result">
				<table id="table_query_open" class="table table-bordered table-condensed">
					<thead class="table_head">
						<tr>
							<th class="column-order td_id" lang="acc.id" title="Clique para alternar a ordenação dos registros">Registro <span class="img_order"><?=$ctrlAcctQry->getImgOrder('acc.id');?></span></th>
							<th class="column-order td_dthr" lang="acc.date_hr_proc" title="Clique para alternar a ordenação dos registros">Criado em <span class="img_order"><?=$ctrlAcctQry->getImgOrder('acc.date_hr_proc');?></span></th>
							<th class="column-order td_id" lang="acc.validate_month" title="Clique para alternar a ordenação dos registros">Total de Parcelas <span class="img_order"><?=$ctrlAcctQry->getImgOrder('acc.validate_month');?></span></th>
							<th class="column-order td_id" lang="acc.client_user_person_id" title="Clique para alternar a ordenação dos registros">Cód. Cliente <span class="img_order"><?=$ctrlAcctQry->getImgOrder('acc.client_user_person_id');?></span></th>
							<th class="column-order td_name">Nome Cliente</th>
							<th class="column-order td_name" lang="acc.status" title="Clique para alternar a ordenação dos registros">Status <span class="img_order"><?=$ctrlAcctQry->getImgOrder('acc.status');?></span></th>
						</tr>
					</thead>
					<tbody class="tbody_query">
						<?
							$i = 0;
							while($i < $length){
								$accountList = $_accounts[$i];
								$clientList  = $accountList->getClient(); 
								$personList  = $clientList->getUser()->getPerson();
						?>
						<tr class="tr_hover" alt="Clique aqui para visualizar detalhes" title="Clique aqui para visualizar detalhes">
							<td class="td_id">
								<?=$accountList->getId()?>
								<input type="hidden" class="acc_hidden_id" value="<?=$accountList->getId()?>">
							</td>
							<td class="td_dthr"><?=$accountList->getDtHrProc()->format("d/m/Y H:i")?></td>
							<td class="td_id"><?=$accountList->getValidateMonth()?></td>
							<td class="td_id"><?=$personList->getId()?></td>
							<td class="td_name"><?=$personList->getName()?></td>
							<td class="td_name <?=$accountList->getStatus()?>"><?=$accountList->getDescStatus()?></td>
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
		</div>
	</div>
	<div id="dialog-working" title="Processando" alt="Aguarde, processando ..." style="display:none">
		<img id="img_dialog" src="../shared/images/dialog-working.gif" />
		<p id="dialog-working.message">Aguarde, processando ...</p>
	</div>
</div>