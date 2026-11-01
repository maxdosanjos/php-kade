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
$isGrpByUf = $ctrlMapsExt->getIsGrpByUf ();

$title = $isGrpByUf ? "Estados com mais veículos" : $ctrlMapsExt->getSelectedUfName ();
?>
<div class="k-ui k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white">
	<div class="k:flex k:flex-col k:gap-0.5 k:border-b k:border-line k:px-6 k:py-5">
		<span class="k:text-[13px] k:text-muted">Últimos veículos</span>
		<h3 class="k:font-display k:text-lg k:font-semibold k:text-kade-950"><?=htmlspecialchars($title, ENT_QUOTES, "UTF-8")?></h3>
	</div>
	<table class="k:w-full k:border-collapse k:text-[15px]">
		<thead>
			<tr class="k:bg-surface k:text-left k:text-[13px] k:text-muted">
				<th class="k:px-6 k:py-2.5 k:font-semibold"><?=$isGrpByUf ? "Estado" : "Cidade"?></th>
				<th class="k:px-6 k:py-2.5 k:text-right k:font-semibold">Veículos</th>
			</tr>
		</thead>
		<tbody>
			<?php if (count ( $_mapsExt ) == 0) { ?>
			<tr class="k:border-t k:border-line">
				<td colspan="2" class="k:px-6 k:py-6 k:text-center k:text-muted">Nenhum veículo disponível <?=$isGrpByUf ? "no momento" : "neste estado"?>.</td>
			</tr>
			<?php } ?>
			<?php foreach ( $_mapsExt as $mapsExt ) {
				if ($mapsExt == null)
					continue;
				$address = $mapsExt->getAddress (); ?>
			<tr class="k:border-t k:border-line">
				<td class="k:px-6 k:py-3">
					<a href="/<?=ViewExtCtrl::BUSCA_VEICULO?>/" class="a_city_uf_maps k-focus k:rounded k:font-medium k:text-ink k:hover:text-kade-900" data-uf="<?=htmlspecialchars($address->getState(), ENT_QUOTES, "UTF-8")?>" data-city="<?=$isGrpByUf ? "" : htmlspecialchars($address->getCity(), ENT_QUOTES, "UTF-8")?>"><?=htmlspecialchars($isGrpByUf ? $address->getStateNm() : $address->getCity(), ENT_QUOTES, "UTF-8")?></a>
				</td>
				<td class="k:px-6 k:py-3 k:text-right k:font-semibold k:text-kade-900"><?=$ctrlMapsExt->getQty($mapsExt)?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-2 k:border-t k:border-line k:px-6 k:py-4">
		<a href="/<?=ViewExtCtrl::BUSCA_VEICULO?>/" class="k-focus k:rounded k:text-[15px] k:font-semibold k:text-kade-900 k:hover:text-kade-800">Ver todos os veículos →</a>
		<span class="k:text-[13px] k:text-muted" aria-live="off">Atualiza em <span id="crono">60</span>s</span>
	</div>
</div>
<form id="form_maps" method="post" action="/<?=ViewExtCtrl::BUSCA_VEICULO?>/">
	<input type="hidden" id="vehicle_region" name="vehicle_region">
	<input type="hidden" id="vehicle_city" name="vehicle_city">
</form>
