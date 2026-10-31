<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/MapsExtCtrl.class.php");
$totalReg  = $ctrlMapsExt->getTotalReg();
$msgPeriod = $viewExtCtrl->getMsgPeriod();
$user      = User::getLogged();
$isLogged  = User::isLogged();

$_ufs          = $ctrlMapsExt->getListUf();
$_vehicleTypes = $ctrlMapsExt->getTypeVechicles();
$_qtyByUf      = $ctrlMapsExt->getQtyByUf();
$_mapPaths     = include(dirname(__FILE__)."/partials/map_brazil_data.php");
$_smallUfs     = array("DF", "ES", "RJ", "SE", "AL", "PB", "RN");

// Nível de cor de cada UF (0 = sem veículos, 1 a 4 = quartos do maior valor)
$maxQty = max(array_merge(array(0), array_values($_qtyByUf)));
$_levels = array();
foreach ($_mapPaths as $uf => $data) {
	$qty = $_qtyByUf[$uf] ?? 0;
	$_levels[$uf] = ($qty <= 0 || $maxQty <= 0) ? 0 : max(1, min(4, (int) ceil($qty / $maxQty * 4)));
}

// Banners gerenciados em "Carregar Banner Principal" (load_banner.php)
$_banners = array();
for ($i = 1; $i <= 3; $i++) {
	$file = dirname(dirname(__FILE__))."/shared/images/banner_slider".$i.".jpg";
	if (file_exists($file))
		$_banners[] = "../shared/images/banner_slider".$i.".jpg?v=".filemtime($file);
}

// Foto do hero opcional: coloque uma imagem licenciada em shared/images/hero.jpg
$heroFile  = dirname(dirname(__FILE__))."/shared/images/hero.jpg";
$heroStyle = file_exists($heroFile)
	? ' style="background: linear-gradient(rgba(11,37,69,.8), rgba(11,37,69,.8)), url(\'../shared/images/hero.jpg?v='.filemtime($heroFile).'\') center / cover no-repeat #0b2545"'
	: "";

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$fieldClass = "k-focus k:min-h-[46px] k:w-full k:rounded-[10px] k:border k:border-line-strong k:bg-white k:px-3 k:font-sans k:text-[15px] k:text-ink";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/HomeExt.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/HomeExt.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){ HomeExt.getInstance().initQuery(); });</script>

<div class="k-ui k:bg-surface k:text-ink">

	<!-- Hero -->
	<section class="k:bg-kade-900 k:text-white"<?=$heroStyle?>>
		<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-wrap k:items-center k:gap-12 k:px-4 k:py-14 k:md:px-6 k:md:py-20">
			<div class="k:flex k:min-w-0 k:flex-[1_1_460px] k:flex-col k:gap-5">
				<span class="k:text-[13px] k:font-semibold k:uppercase k:tracking-[1.5px] k:text-[#ffb766]">
					<?=$isLogged ? $h($msgPeriod.", ".$user->getPerson()->getName()) : "Fretes em todo o Brasil"?>
				</span>
				<h1 class="k:font-display k:text-4xl k:leading-tight k:font-bold k:md:text-5xl">Encontre o caminhão certo para a sua carga</h1>
				<p class="k:max-w-[520px] k:text-lg k:leading-relaxed k:text-[#d5deeb]">Informações em tempo real de veículos disponíveis em todo o território nacional, agilizando seu processo de embarque.</p>
				<span class="k:mt-2 k:inline-flex k:items-center k:gap-2.5 k:self-start k:rounded-full k:bg-white/10 k:px-4 k:py-2.5 k:text-[15px]">
					<span class="k:h-2 k:w-2 k:rounded-full k:bg-[#4ade80]" aria-hidden="true"></span>
					<strong class="k:font-semibold"><?=(int) $totalReg?></strong> veículos ativos agora
				</span>
			</div>

			<form method="post" action="/<?=ViewExtCtrl::BUSCA_VEICULO?>/" class="k:flex k:min-w-0 k:flex-[1_1_380px] k:flex-col k:gap-4 k:rounded-2xl k:bg-white k:p-7 k:text-ink k:shadow-[0_20px_50px_rgba(3,15,35,.35)]">
				<h2 class="k:font-display k:text-xl k:font-semibold">Busca rápida</h2>
				<label class="k:flex k:flex-col k:gap-1.5 k:text-sm k:font-medium">Tipo de veículo
					<select name="vehicle_type" class="<?=$fieldClass?>">
						<option value="">Todos os tipos</option>
						<?php foreach ($_vehicleTypes as $vehicleType) { ?>
						<option value="<?=$h($vehicleType->getId())?>"><?=$h($vehicleType->getDescr())?></option>
						<?php } ?>
					</select>
				</label>
				<div class="k:grid k:grid-cols-3 k:gap-3">
					<label class="k:flex k:flex-col k:gap-1.5 k:text-sm k:font-medium">UF
						<select name="vehicle_region" class="<?=$fieldClass?>">
							<option value="">Todas</option>
							<?php foreach ($_ufs as $uf => $ufName) { ?>
							<option value="<?=$h($uf)?>" title="<?=$h($ufName)?>"><?=$h($uf)?></option>
							<?php } ?>
						</select>
					</label>
					<label class="k:col-span-2 k:flex k:flex-col k:gap-1.5 k:text-sm k:font-medium">Cidade
						<input type="text" name="vehicle_city" maxlength="120" placeholder="Ex.: Campinas" class="<?=$fieldClass?>">
					</label>
				</div>
				<button type="submit" class="k-focus k:mt-1 k:inline-flex k:min-h-[50px] k:cursor-pointer k:items-center k:justify-center k:gap-2 k:rounded-[10px] k:border-0 k:bg-accent k:font-sans k:text-base k:font-semibold k:text-kade-950 k:hover:brightness-95">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					Buscar veículos
				</button>
				<?php if (!$isLogged) { ?>
				<p class="k:text-[13px] k:text-muted">É preciso <a href="/<?=ViewExtCtrl::LOGIN?>/" class="k:font-semibold k:text-kade-900">entrar</a> ou <a href="/<?=ViewExtCtrl::CAD_CLIENTE?>/" class="k:font-semibold k:text-kade-900">cadastrar-se</a> para ver os resultados.</p>
				<?php } ?>
			</form>
		</div>
	</section>

	<!-- Mapa: veículos por estado -->
	<section class="k:mx-auto k:max-w-[1200px] k:px-4 k:py-16 k:md:px-6">
		<div class="k:mb-8 k:flex k:flex-col k:gap-2">
			<h2 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]">Veículos disponíveis por estado</h2>
			<p class="k:text-base k:text-muted">Clique em um estado no mapa para ver as cidades com mais caminhões disponíveis.</p>
		</div>
		<div class="k:flex k:flex-wrap k:items-start k:gap-8">
			<div class="k:flex k:min-w-0 k:flex-[999_1_560px] k:flex-col k:gap-5 k:rounded-2xl k:border k:border-line k:bg-white k:p-6">
				<svg id="k_map_br" class="k-map k:mx-auto k:block k:h-auto k:w-full k:max-w-[620px]" viewBox="0 0 784 781" aria-label="Mapa do Brasil com a quantidade de veículos por estado">
					<?php foreach ($_mapPaths as $uf => $data) {
						$label = $_ufs[$uf] ?? $uf;
						$qty = $_qtyByUf[$uf] ?? 0; ?>
					<g class="k-uf" data-uf="<?=$uf?>" data-level="<?=$_levels[$uf]?>" tabindex="0" role="button" aria-pressed="false" aria-label="<?=$h($label)?>: <?=$qty?> veículos">
						<title><?=$h($label)?>: <?=$qty?> veículos</title>
						<path d="<?=$data[0]?>"/>
						<?php if ($data[1] !== null) { ?><text x="<?=$data[1]?>" y="<?=$data[2]?>"><?=$uf?></text><?php } ?>
					</g>
					<?php } ?>
				</svg>
				<div class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-x-6 k:gap-y-4 k:border-t k:border-line k:pt-4">
					<div class="k:flex k:flex-wrap k:items-center k:gap-2 k:text-[13px] k:text-muted">
						<span class="k:font-semibold k:text-ink-soft">Veículos:</span>
						<span class="k:inline-flex k:items-center k:gap-1.5"><span class="k-swatch" data-level="0" aria-hidden="true"></span>nenhum</span>
						<span class="k:inline-flex k:items-center k:gap-1.5">menos</span>
						<span class="k-swatch" data-level="1" aria-hidden="true"></span>
						<span class="k-swatch" data-level="2" aria-hidden="true"></span>
						<span class="k-swatch" data-level="3" aria-hidden="true"></span>
						<span class="k-swatch" data-level="4" aria-hidden="true"></span>
						<span>mais</span>
					</div>
					<div class="k:flex k:flex-wrap k:items-center k:gap-1.5">
						<span class="k:mr-0.5 k:text-[13px] k:text-muted">Estados menores:</span>
						<?php foreach ($_smallUfs as $uf) { ?>
						<button type="button" class="k-uf-btn k-focus" data-uf="<?=$uf?>" aria-pressed="false" title="<?=$h($_ufs[$uf] ?? $uf)?>"><?=$uf?></button>
						<?php } ?>
					</div>
				</div>
				<p class="k:text-xs k:text-muted">Fonte dos contornos: IBGE.</p>
			</div>

			<div id="k_maps_table" class="k:min-w-0 k:flex-[1_1_340px]" aria-live="polite">
				<?php $ctrlMapsExt->onSearchMaps(); ?>
			</div>
		</div>
	</section>

	<?php if (count($_banners) > 0) { ?>
	<!-- Destaques (banners do administrador) -->
	<section class="k:mx-auto k:max-w-[1200px] k:px-4 k:pb-16 k:md:px-6" aria-label="Destaques">
		<div class="k:mb-6 k:flex k:items-end k:justify-between k:gap-4">
			<h2 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950">Destaques</h2>
			<?php if (count($_banners) > 1) { ?>
			<div class="k:flex k:gap-2">
				<button type="button" class="k-banner-prev k-focus k:inline-flex k:h-11 k:w-11 k:cursor-pointer k:items-center k:justify-center k:rounded-full k:border k:border-line-strong k:bg-white k:text-ink-soft k:hover:bg-surface" aria-label="Destaque anterior">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
				</button>
				<button type="button" class="k-banner-next k-focus k:inline-flex k:h-11 k:w-11 k:cursor-pointer k:items-center k:justify-center k:rounded-full k:border k:border-line-strong k:bg-white k:text-ink-soft k:hover:bg-surface" aria-label="Próximo destaque">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
				</button>
			</div>
			<?php } ?>
		</div>
		<div class="k-banner-track k-focus k:flex k:snap-x k:snap-mandatory k:gap-4 k:overflow-x-auto k:rounded-2xl k:scroll-smooth" tabindex="0" aria-label="Destaques (role para o lado)">
			<?php foreach ($_banners as $i => $src) { ?>
			<img src="<?=$src?>" alt="Destaque <?=$i + 1?> de <?=count($_banners)?>" loading="lazy" class="k:aspect-[11/3] k:w-full k:shrink-0 k:snap-center k:rounded-2xl k:border k:border-line k:bg-white k:object-cover">
			<?php } ?>
		</div>
	</section>
	<?php } ?>

	<!-- Como funciona -->
	<section class="k:border-y k:border-line k:bg-white">
		<div class="k:mx-auto k:max-w-[1200px] k:px-4 k:py-16 k:md:px-6">
			<h2 class="k:mb-8 k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]">Como funciona</h2>
			<div class="k:grid k:grid-cols-1 k:gap-6 k:md:grid-cols-3">
				<div class="k:flex k:flex-col k:gap-3 k:rounded-2xl k:border k:border-line k:p-7">
					<span class="k:inline-flex k:h-12 k:w-12 k:items-center k:justify-center k:rounded-xl k:bg-accent-soft">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#b35f00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
					</span>
					<h3 class="k:font-display k:text-[19px] k:font-semibold">1. Cadastre seu veículo</h3>
					<p class="k:text-[15px] k:leading-relaxed k:text-muted">Motoristas informam onde o caminhão está disponível, pelo site ou por SMS.</p>
				</div>
				<div class="k:flex k:flex-col k:gap-3 k:rounded-2xl k:border k:border-line k:p-7">
					<span class="k:inline-flex k:h-12 k:w-12 k:items-center k:justify-center k:rounded-xl k:bg-kade-50">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0b3b6f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					</span>
					<h3 class="k:font-display k:text-[19px] k:font-semibold">2. Busque por veículos</h3>
					<p class="k:text-[15px] k:leading-relaxed k:text-muted">Embarcadores filtram por tipo de veículo, região e período.</p>
				</div>
				<div class="k:flex k:flex-col k:gap-3 k:rounded-2xl k:border k:border-line k:p-7">
					<span class="k:inline-flex k:h-12 k:w-12 k:items-center k:justify-center k:rounded-xl k:bg-[#e7f6ec]">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1a6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
					</span>
					<h3 class="k:font-display k:text-[19px] k:font-semibold">3. Fale direto com o motorista</h3>
					<p class="k:text-[15px] k:leading-relaxed k:text-muted">Veja telefone e contato do responsável e feche o frete sem intermediários.</p>
				</div>
			</div>
		</div>
	</section>

	<?php if (!$isLogged) { ?>
	<!-- Parceiro -->
	<section class="k:mx-auto k:max-w-[1200px] k:px-4 k:pt-16 k:md:px-6">
		<div class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-6 k:rounded-[20px] k:bg-accent k:p-8 k:md:p-12">
			<div class="k:flex k:min-w-0 k:flex-[1_1_420px] k:flex-col k:gap-2">
				<h2 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[30px]">Seja nosso parceiro!</h2>
				<p class="k:text-[17px] k:text-[#2a1a05]">Mais visibilidade e novos clientes para sua empresa.</p>
			</div>
			<a href="/<?=ViewExtCtrl::CAD_CLIENTE?>/" class="k-focus k:inline-flex k:min-h-[50px] k:items-center k:rounded-[10px] k:bg-kade-950 k:px-7 k:text-base k:font-semibold k:text-white k:hover:bg-kade-900">Cadastre-se</a>
		</div>
	</section>
	<?php } ?>
</div>
