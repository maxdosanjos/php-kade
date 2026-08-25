<?php
if ($ctrlMapsExt == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

if ($ctrlMapsExt->getIsAjax ()){
	ob_start( 'ob_gzhandler' );
	header('Content-type: text/html; charset=UTF-8');
}

require_once(dirname(dirname(__FILE__))."/shared/class/controller/ViewExtCtrl.class.php");
$_mapsExt = $ctrlMapsExt->getListMapsExt ();
$length = count ( $_mapsExt );

$stateName = "";
if (! $ctrlMapsExt->getIsGrpByUf () && $length > 0) {
	$mapsExt = $_mapsExt [0];
	if ($mapsExt != null) {
		$address = $mapsExt->getAddress ();
		$stateName = " - " . $address->getStateNm ();
	}
}
$mapsExt = null;
$address = null;
?>
<div id="div_title_maps">Últimos veículos<?=$stateName!=""?$stateName:"";?></div>
<table id="table_demonstr" class="table table-striped table-bordered table-condensed">
	<thead>
		<tr>
			<th class="td_limit"></th>
			<th class="td_cid"><?=$ctrlMapsExt->getIsGrpByUf()?"Estados":"Cidades"?></th>
			<th class="td_qtde">Qtde.Veículos</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 0;
		while ( $i < $length ) {
			$mapsExt = $_mapsExt [$i];
			if ($mapsExt == null) {
				$i ++;
				continue;
			}
			$address = $mapsExt->getAddress ();
			?>
		<tr>
			<td class="td_limit"></td>
			<td class="td_cid">
				<a class="a_city_uf_maps" href="#" uf="<?=$address->getState()?>" city="<?=$ctrlMapsExt->getIsGrpByUf()?"":$address->getCity()?>">
					<?=$ctrlMapsExt->getIsGrpByUf()?$address->getStateNm():$address->getCity()?>
				</a>
			</td>
			<td class="td_qtde"><?=$mapsExt->getQtyReal()?></td>
		</tr>
		<?php $i++; }?>
	</tbody>
	<tfoot>
		<tr>
			<td class="td_limit active td_foot_update" colspan="3">
				<a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/">Saiba mais...</a>
				<span class="timer_update">Atualizado em: <span id="crono">60</span> segundos</span>				
			</td>
		</tr>
	</tfoot>
</table>
<form id="form_maps" method="post" action="/busca_veiculo/">
	<input type="hidden" id="vehicle_region" name="vehicle_region" class="input_clear">
	<input type="hidden" id="vehicle_city" name="vehicle_city" class="input_clear">
</form>