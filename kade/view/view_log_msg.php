<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/MsgLogQueryCtrl.class.php");

$userSuper 	   	  = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

$ctrlMsgLogQry->onSearchArray();
$_msgLogs   	  = $ctrlMsgLogQry->getMsgLogs();
$length 		  = count($_msgLogs);

$pagination 	  = $ctrlMsgLogQry->getPagination();
$message    	  = $ctrlMsgLogQry->getMessage();
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/view_log_msg.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/MsgLogBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();MsgLogBean.getInstance().initQuery();});</script>
<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify("<?=$message->getDesc();?>", "<?=$message->getType();?>");});</script>
<?	} ?>
<div id="div_body_msg_log" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<div id="div_search">
			<form id="msglog_query_open" name="msglog_query_open" action="<?=$_SERVER["REQUEST_URI"];?>" method="post">
				<fieldset id="fieldset_filter" class="ui-widget ui-widget-content ui-corner-all">
					<legend>Logs de SMS</legend>
					<div class="div_input">
						<label for="msglog_dt_init"  class="label_input">Período</label>
						<input type="text" id="msglog_dt_init" name="msglog_dt_init" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlMsgLogQry->getDtHrInit()->format("d.m.Y")?>"/>
						 até 
						<input type="text" id="msglog_dt_end" name="msglog_dt_end" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlMsgLogQry->getDtHrEnd()->format("d.m.Y")?>"/>
					</div>
					<div class="div_input">						
						<label for="type_log" id="type_log_label"  class="label_input" alt="Status" title="Status">Status: </label>
						<select id="type_log" name="type_log">
							<option value="">Todos</option>
							<option value="error" <?=($_POST["type_log"]=="error")?"selected=true":""?>>Erro</option>
							<option value="sucess" <?=($_POST["type_log"]=="sucess")?"selected=true":""?>>Sucesso</option>					
						</select>
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
					</div>
				</fieldset>
				<span id="span_loc_msg"></span>					
				<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
				<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
				<input type="hidden" id="order_column" name="order_column" value="<?=$ctrlMsgLogQry->getOrderColumn();?>"/>
				<input type="hidden" id="order_type" name="order_type" value="<?=$ctrlMsgLogQry->getOrderType();?>"/>	
				<input type="hidden" id="msg_type" name="msg_type" value=""/>	
				<input type="hidden" id="msg_txt" name="msg_txt" value=""/>						
			</form>
		</div>
		<div id="div_result">
			<table id="table_query_open" class="table table-bordered table-condensed">
				<thead class="table_head">
					<tr>
						<th class="td_dthr">Data/Hora</th>
						<th class="td_name">Nº telefone</th>
						<th class="td_name">Mensagem</th>
						<th class="td_dthr">Tipo</th>
						<th class="td_name">Log</th>
						<th class="td_dthr">IP recebedor</th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?
						$i = 0;
						while($i < $length){
							$msgLogList  = $_msgLogs[$i];
							$dtHr = new DateTimeCustom($msgLogList->date_hr);
							
							$json = json_decode($msgLogList->log);
							if($json[0]->message == ""){
								$json[0]->message = $msgLogList->log;
							}else{
								$json[0]->message = html_entity_decode(iconv (  "UTF-8","UTF-8", $json[0]->message));
							}
					?>
					<tr class="tr_hover">
						<td class="td_dthr"><?=$dtHr->format("d/m/Y")?></td>
						<td class="td_name"><?=$msgLogList->from?></td>
						<td class="td_name"><?=$msgLogList->body?></td>
						<td class="td_dthr <?=$msgLogList->type_log?>"><?=($msgLogList->type_log == "sucess")?"Sucesso":"Erro"?></td>
						<td class="td_name"><?=$json[0]->message?></td>
						<td class="td_dthr" alt="URL requisitada: <?=$msgLogList->url_request?>" title="URL requisitada: <?=$msgLogList->url_request?>"><?=$msgLogList->ip_trat?></td>
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