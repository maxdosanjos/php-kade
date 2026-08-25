<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/CustumerQueryCtrl.class.php");

$userSuper 	   	  = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

$_ufList 		  = $ctrlCustQry->getListUf();
$ctrlCustQry->onSearchArray();
$_custumers 	  = $ctrlCustQry->getCustumers();
$length 		  = count($_custumers);

$custumerParam    = $ctrlCustQry->getCustumerParam();
$userParam		  = $custumerParam->getUser();
$personParam	  = $userParam->getPerson();

$pagination 	  = $ctrlCustQry->getPagination();
$message    	  = $ctrlCustQry->getMessage();

if($personParam!=null)
{
	if($personParam->getType() == Person::PJ)
	{
		$checkedAll = "";
		
		$checkedPF = "";
		$cssPF	   = "display:none";
		
		$checkedPJ = "checked='true'";
		$cssPJ	   = "";
	}
	elseif($personParam->getType() == Person::PF)
	{
		$checkedAll = "";
		
		$checkedPF = "checked='true'";
		$cssPF	   = "";		
		
		$checkedPJ = "";
		$cssPJ	   = "display:none";
	}else{
		$checkedAll = "checked='true'";
		
		$cssPF	   = "";
		$checkedPF = "";
		
		$checkedPJ = "";
		$cssPJ	   = "";
	}
}
?>

<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/view_clientes_int.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/CustumerBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();CustumerBean.getInstance().initQueryOpen();});</script>
<?if($message!=null){?>
	<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify("<?=$message->getDesc();?>", "<?=$message->getType();?>");});</script>
<?	} ?>
<div id="div_body_view_cli" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<div id="div_search">
			<form id="custumer_query_open" name="custumer_query_open" action="<?=$_SERVER["REQUEST_URI"];?>" method="post">
				<fieldset id="fieldset_filter" class="ui-widget ui-widget-content ui-corner-all">
					<legend>Buscar por Clientes</legend>
					<div id="form_query_left">
						<div class="div_input">
							<label for="person_type"  class="label_input" alt="Tipo" title="Tipo">Tipo: </label>
							
							<input type="radio" name="type_person" value="" id="person_all" alt="Todos" title="Todos" <?=$checkedAll?>/>
							<label for="person_all" id="person_all_label" class="label_radio_type" alt="Todos" title="Todos">Todos</label>
							
							<input type="radio" name="type_person" value="PF" id="person_pf" alt="Pessoa Física" title="Pessoa Física" <?=$checkedPF?>/>
							<label for="person_pf" id="person_pf_label" alt="Pessoa Física" class="label_radio_type" title="Pessoa Física">Física</label>
							
							<input type="radio" name="type_person" value="PJ" id="person_pj" alt="Pessoa Jurídica" title="Pessoa Jurídica" <?=$checkedPJ?>/>
							<label for="person_pj" id="person_pj_label" alt="Pessoa Jurídica" class="label_radio_type" title="Pessoa Jurídica">Jurídica</label>
							
						</div>
						<div id="div_fields_pf" class="div_input" style="<?=$cssPF?>">
							<div id="div_custumer_cpf" class="div_input">
								<label for="custumer_cpf"  class="label_input" alt="CPF" title="CPF">CPF: </label>
								<input type="text" id="custumer_cpf" name="custumer_cpf" class="mask.cpf input_per input_clear" size="14" maxlength="14" value="<?=($personParam!=null && $personParam->getType() == Person::PF)?$personParam->getCpf():"";?>" alt="CPF" title="CPF"/>
							</div>
							<div class="div_input">
								<label for="custumer_name" class="label_input" alt="Nome" title="Nome">Nome: </label>
								<input type="text" id="custumer_name" name="custumer_name" size="40" maxlength="120" class="input_per input_clear" value="<?=($personParam!=null && $personParam->getType() == Person::PF)?$personParam->getName():"";?>" alt="Nome" title="Nome"/>
							</div>
							<br style="clear: both"/>
						</div>
						<div id="div_fields_pj" class="div_input" style="<?=$cssPJ?>">
							<div id="div_custumer_cnpj" class="div_input">
								<label for="custumer_cnpj"  class="label_input" alt="CNPJ" title="CNPJ">CNPJ: </label>
								<input type="text" id="custumer_cnpj" name="custumer_cnpj" class="mask.cnpj  input_per input_clear" size="14" maxlength="14" value="<?=($user!=null && $personParam->getType() == Person::PJ)?$personParam->getCnpj():"";?>" alt="CNPJ" title="CNPJ"/>
							</div>
							<div class="div_input">
								<label for="custumer_rsoc"  class="label_input" alt="Razão Social" title="Razão Social">Razão Social: </label>
								<input type="text" id="custumer_rsoc" name="custumer_rsoc" size="40" maxlength="120" class=" input_per input_clear" value="<?=($personParam!=null && $personParam->getType() == Person::PJ)?$personParam->getName():"";?>" alt="Razão Social" title="Razão Social"/>
							</div>
							<div class="div_input">
								<label for="custumer_ie"  class="label_input" alt="Inscrição Estadual" title="Inscrição Estadual">I.E.: </label>
								<input type="text" id="custumer_ie" name="custumer_ie" size="12" maxlength="18" class="mask.numeric  input_per " value="<?=($personParam!=null && $personParam->getType() == Person::PJ && $personParam->getIe() != PersonEntity::ISENTO)?$personParam->getIe():""?>" alt="Inscrição Estadual" title="Inscrição Estadual"/>
								<label id="custumer_ie_isento_label" alt="Inscrição Estadual = Isento" title="Inscrição Estadual = Isento">
									<input type="checkbox" id="custumer_ie_isento" name="custumer_ie_isento" value="on" <?=($personParam!=null && $personParam->getType() == Person::PJ && $personParam->getIe() == PersonEntity::ISENTO)?"checked='true'":""?>/>Isento
								</label>
							</div>
							<div class="div_input">
								<label for="custumer_contact"  class="label_input" alt="Contato" title="Contato">Contato: </label>
								<input type="text" id="custumer_contact" name="custumer_contact" size="40" maxlength="120" class="input_per input_clear" value="<?=($personParam!=null && $personParam->getType() == Person::PJ)?$personParam->getContact():"";?>" alt="Contato" title="Contato"/>
							</div>
							<br style="clear: both"/>
						</div>
						
						<div class="div_input">
							<label for="id" class="label_input">Registro</label>
							<input type="text" id="custumer_id" name="custumer_id" size="11" maxlength="10" class="input_clear mask.numeric" value="<?=($personParam->getId() > 0)?$personParam->getId():""?>" />
						</div>		
						<!-- <div class="div_input">
							<label for="custumer_dt_init" title="Data de Cadastro"  alt="Data de Cadastro"  class="label_input">Dt.Cadastro</label>
							<input type="text" title="Data de Cadastro"  alt="Data de Cadastro" id="custumer_dt_init" name="custumer_dt_init" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlCustQry->getDtHrInit()->format("d.m.Y")?>"/>
							 até 
							<input type="text" title="Data de Cadastro"  alt="Data de Cadastro" id="custumer_dt_end" name="custumer_dt_end" size="10" maxlength="10" class="mask.date input_clear" value="<?=$ctrlCustQry->getDtHrEnd()->format("d.m.Y")?>"/>
						</div>  -->
						<div class="div_input">
							<label for="custumer_status"  class="label_input" title="Status" alt="Status">Status</label>
							<select id="custumer_status" name="custumer_status" class="">
								<option value=""></option>
								<?php foreach (User::listStatus() as $status=>$statuNm){?>
									<option value="<?=$status?>" <?=($userParam->getStatus() == $status)?"selected='true'":""?>><?=$statuNm?></option>
								<?php } ?>
							</select>
						</div>					
						<br style="clear: both"/>
					</div>
					<div id="form_query_right">
						<div class="div_input">
							<label for="custumer_phone"  class="label_input" alt="Telefone" title="Telefone">Telefone: </label>
							<input type="text" id="custumer_phone" name="custumer_phone" size="12" maxlength="11" class="mask.phone2_with_ddd  " value="<?=($personParam!=null)?$personParam->getPhone()->getPhoneMask():"";?>" alt="Telefone" title="Telefone"/>
						</div>   
						<div class="div_input">
							<label for="custumer_mail"  class="label_input" alt="Email" title="Email">Email: </label>
							<input type="text" id="custumer_mail" name="custumer_mail" size="50" maxlength="255" class="mask.mail  " value="<?=($personParam!=null)?$personParam->getEmail():"";?>" alt="Email" title="Email"/>
						</div>   	
						<div class="div_input">
				    		<label for="custumer_zipcode" id="custumer_zipcode_label"  class="label_input">CEP: </label>
				    		<input type="text" id="custumer_zipcode" name="custumer_zipcode" size="14" maxlength="10" class="mask.cep input_clear" value="<?=$personParam->getAddress()->getCep()?>"/>
				    	</div>
				    	<div class="div_input">	
				    		<label for="custumer_region" id="custumer_region_label"  class="label_input">UF: </label>
				    		<select id="custumer_region" name="custumer_region" class="input_clear">
								<option value="" selected="selected"></option>
								<?php foreach ($_ufList as $uf=>$uf_name){?>
									<option value="<?=$uf?>" <?=($personParam->getAddress()->getState() == $uf)?"selected='true'":""?>><?=$uf?> - <?=$uf_name?></option>
								<?php } ?>
							</select>
				    	</div>
				    	<div class="div_input">
				    		<label for="custumer_city"  class="label_input">Cidade: </label>
				    		<input type="text" id="custumer_city" name="custumer_city" size="40" maxlength="120" class="input_clear" value="<?=$personParam->getAddress()->getCity()?>"/>
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
						<span id="button_csv" class="btn_default" title="Exportar p/ CSV">
							<div class="div_btn_with_image"></div>
							<span>Exportar p/ CSV</span>
						</span>
					</div>
				</fieldset>
				<span id="span_loc_msg"></span>
				<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
				<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
				<input type="hidden" id="order_column" name="order_column" value="<?=$ctrlCustQry->getOrderColumn();?>"/>
				<input type="hidden" id="order_type" name="order_type" value="<?=$ctrlCustQry->getOrderType();?>"/>	
				<input type="hidden" id="msg_type" name="msg_type" value=""/>	
				<input type="hidden" id="msg_txt" name="msg_txt" value=""/>		
				
			</form>
		</div>
		<div id="div_result">
			<table id="table_query_open" class="table table-bordered table-condensed">
				<thead class="table_head">
					<tr>
						<th class="column-order td_id" lang="cli.user_person_id" title="Clique para alternar a ordenação dos registros">Registro <span class="img_order"><?=$ctrlCustQry->getImgOrder('cli.user_person_id');?></span></th>
						<th class="column-order td_id" lang="per.type" title="Clique para alternar a ordenação dos registros">Tipo <span class="img_order"><?=$ctrlCustQry->getImgOrder('per.type');?></span></th>
						<th class="column-order td_dthr" lang="usr.date_cad" title="Clique para alternar a ordenação dos registros">Criado em <span class="img_order"><?=$ctrlCustQry->getImgOrder('usr.date_cad');?></span></th>
						<th class="column-order td_tcustumer" lang="per.name" title="Clique para alternar a ordenação dos registros">Nome/Razão Social <span class="img_order"><?=$ctrlCustQry->getImgOrder('per.name');?></span></th>
						<th class="td_cep">CPF/CNPJ</th>
						<th class="column-order td_city" lang="addr.city" title="Clique para alternar a ordenação dos registros">Cidade <span class="img_order"><?=$ctrlCustQry->getImgOrder('addr.city');?></span></th>
						<th class="column-order td_uf" lang="addr.state" title="Clique para alternar a ordenação dos registros">UF <span class="img_order"><?=$ctrlCustQry->getImgOrder('addr.state');?></span></th>
						<th class="column-order td_uf" lang="usr.status" title="Clique para alternar a ordenação dos registros">Status <span class="img_order"><?=$ctrlCustQry->getImgOrder('usr.status');?></span></th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?
						$i = 0;
						while($i < $length){
							$custumerList = $_custumers[$i];
							
							$userList		  = $custumerList->getUser();
							$personList 	  = $userList->getPerson();
							$addressList 	  = $personList->getAddress ( );
					?>
					<tr class="tr_hover tr_edit" alt="Clique aqui para visualizar detalhes" title="Clique aqui para visualizar detalhes">
						<td class="td_id">
							<?=$personList->getId()?>
							<input type="hidden" class="cust_hidden_id" value="<?=$personList->getId()?>">
						</td>
						<td class="td_id <?=$personList->getType()?>"><?=$personList->getDescType()?></td>
						<td class="td_dthr"><?=$userList->getDateCad()->format("d/m/Y H:i")?></td>
						<td class="td_tcustumer"><?=$personList->getName()?></td>
						<td class="td_cep"><?=($personList->getType() == Person::PJ)?Util::mask($personList->getCnpj(),"##.###.###/####-##"):Util::mask($personList->getCpf(),"###.###.###-##")?></td>
						<td class="td_city"><?=$addressList->getCity()?></td>
						<td class="td_uf"><?=$addressList->getState()?></td>
						<td class="td_uf <?=$userList->getStatus()?>"><?=$userList->getDescStatus()?></td>
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
