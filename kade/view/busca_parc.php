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
$_installment	  = $ctrlInstQry->getInstallments();
$installmentParam = $ctrlInstQry->getInstallmentParam();
$length 		  = count($_installment);


$message    	  = $ctrlInstQry->getMessage();
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/busca_parc.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/InstallmentBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();InstallmentBean.getInstance().initQuery();});</script>
<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify("<?=$message->getDesc();?>", "<?=$message->getType();?>");});</script>
<?	} ?>
<div id="div_body_busca_parc" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<fieldset id="fieldset_filter">
			<legend>Parcelas</legend>
			<div id="div_search">
				<form id="parc_query_open" name="parc_query_open" action="<?=$_SERVER["REQUEST_URI"];?>" method="post">
					<div class="div_input">
						<label for="parc_status"  class="label_input" title="Status" alt="Status">Filtrar por Status:</label>
						<select id="parc_status" name="parc_status" class="">
							<?php foreach (Installment::listStatus() as $status=>$statuNm){
								if($status!=Installment::CANCEL){
							?>
								<option value="<?=$status?>" <?=($installmentParam->getStatus() == $status)?"selected='true'":""?>><?=$statuNm?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>		
					<span id="span_loc_msg"></span>
					<input type="hidden" id="order_column" name="order_column" value="<?=$ctrlInstQry->getOrderColumn();?>"/>
					<input type="hidden" id="order_type" name="order_type" value="<?=$ctrlInstQry->getOrderType();?>"/>	
					<input type="hidden" id="msg_type" name="msg_type" value=""/>	
					<input type="hidden" id="msg_txt" name="msg_txt" value=""/>		
				</form>
			</div>
		</fieldset>		
		<div id="div_result">
			<div id="table_foot">
				<div class="details_pag">Número de Resultados: <span title="Total de Registros">(<?=$length;?>)</span></div>
			</div>
			<table id="table_query_open" class="table table-bordered table-condensed">
				<thead class="table_head">
					<tr>
						<th class="column-order td_id" lang="ins.account_id" title="Clique para alternar a ordenação dos registros">Registro <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.account_id');?></span></th>
						<th class="column-order td_id" lang="ins.id" title="Clique para alternar a ordenação dos registros">Parcela <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.id');?></span></th>
						<th class="column-order td_value" lang="ins.value" title="Clique para alternar a ordenação dos registros">Valor da Parcela <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.value');?></span></th>
						<th class="column-order td_drhr" lang="ins.due_date" title="Clique para alternar a ordenação dos registros">Data Vencimento <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.due_date');?></span></th>
						<th class="column-order td_drhr" lang="ins.payment_date" title="Clique para alternar a ordenação dos registros">Data Pagamento <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.payment_date');?></span></th>
						<th class="column-order td_value" lang="ins.payment_value" title="Clique para alternar a ordenação dos registros">Valor Pago <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.payment_value');?></span></th>
						<th class="td_name">Forma de Pagamento</th>
						<th class="column-order td_sts" lang="ins.status" title="Clique para alternar a ordenação dos registros">Status <span class="img_order"><?=$ctrlInstQry->getImgOrder('ins.status');?></span></th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?
						$i = 0;
						while($i < $length){
							$installmentList = $_installment[$i];
							$accountList     = $installmentList->getAccount();
							$payMethodList   = $installmentList->getPaymentMethod();
					?>
					<tr class="tr_hover">
						<td class="td_id"><?=$accountList->getId()?></td>
						<td class="td_id"><?=$installmentList->getId ()?></td>
						<td class="td_value"><?=number_format ( $installmentList->getValue (), 2, ',', '.' )?></td>
						<td class="td_drhr"><?=$installmentList->getDueDate ()->format ( "d/m/Y" )?></td>
						<td class="td_drhr"><?=($installmentList->getPaymentDate ()!=null)?$installmentList->getPaymentDate ()->format ( "d/m/Y" ):""?></td>
						<td class="td_value"><?=number_format ( $installmentList->getPaymentValue (), 2, ',', '.' )?></td>
						<td class="td_name"><?=($payMethodList!=null)?$payMethodList->getName ():""?></td>
						<td class="td_sts <?=$installmentList->getStatus ()?>"><?=$installmentList->getDescStatus ()?></td>			
					</tr>
					<? $i++; } ?>
				</tbody>
			</table>
		</div>
		
	</div>
</div>