<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/VehicleRegCtrl.class.php");
$_vechicleTypes = $ctrlVehReg->getTypeVechicles() ?? array();

$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$smsNumber = "(43) 9676-1700";
$_topics = array(
	"quem_somos"   => "Quem somos",
	"beneficios"   => "Benefícios",
	"usuarios"     => "Quem usa",
	"missao"       => "Missão, visão e valores",
	"como_funciona"=> "Cadastro por SMS",
	"codigos"      => "Códigos dos veículos",
);
$check = '<svg class="k:mt-0.5 k:shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1a6b35" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
$h2 = "k:font-display k:text-2xl k:font-bold k:text-kade-950 k:md:text-[28px]";
?>
<div class="k-ui k:bg-surface k:text-ink">

	<section class="k:bg-kade-900 k:text-white">
		<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-col k:gap-4 k:px-4 k:py-14 k:md:px-6 k:md:py-16">
			<span class="k:text-[13px] k:font-semibold k:uppercase k:tracking-[1.5px] k:text-[#ffb766]">Sobre a KADE</span>
			<h1 class="k:max-w-[760px] k:font-display k:text-4xl k:leading-tight k:font-bold k:md:text-5xl">Conectando cargas e caminhões em todo o Brasil</h1>
			<p class="k:max-w-[640px] k:text-lg k:leading-relaxed k:text-[#d5deeb]">Veículos de carga disponíveis em tempo real para embarcadores, e mais oferta de fretes para o motorista autônomo.</p>
		</div>
	</section>

	<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-col k:gap-10 k:px-4 k:py-12 k:md:px-6 k:lg:flex-row k:lg:items-start">

		<nav aria-label="Tópicos" class="k:rounded-2xl k:border k:border-line k:bg-white k:p-4 k:lg:sticky k:lg:top-6 k:lg:w-[250px] k:lg:shrink-0">
			<span class="k:mb-2 k:block k:px-3 k:text-xs k:font-semibold k:uppercase k:tracking-[1px] k:text-muted">Nesta página</span>
			<ul class="k:m-0 k:flex k:list-none k:flex-wrap k:gap-1 k:p-0 k:lg:flex-col">
				<?php foreach ($_topics as $anchor => $topic) { ?>
				<li><a href="#<?=$anchor?>" class="k-focus k:block k:rounded-lg k:px-3 k:py-2 k:text-[15px] k:text-ink-soft k:no-underline k:hover:bg-surface k:hover:text-kade-900"><?=$topic?></a></li>
				<?php } ?>
				<li><a href="/<?=ViewExtCtrl::TERMS_USE;?>/" class="k-focus k:block k:rounded-lg k:px-3 k:py-2 k:text-[15px] k:text-ink-soft k:no-underline k:hover:bg-surface k:hover:text-kade-900">Termos de uso</a></li>
			</ul>
		</nav>

		<div class="k:flex k:min-w-0 k:flex-1 k:flex-col k:gap-12">

			<section id="quem_somos" class="k:flex k:scroll-mt-6 k:flex-col k:gap-4">
				<h2 class="<?=$h2?>">Quem somos</h2>
				<p class="k:text-[17px] k:leading-relaxed k:text-ink-soft">
					A <strong class="k:font-semibold k:text-kade-900">KADE Caminhões</strong> nasceu de diversas pesquisas com profissionais do transporte rodoviário,
					para oferecer, de forma simples e eficiente, melhorias para a logística no Brasil. Nosso sistema traz informações online
					de veículos de carga disponíveis em todo o território nacional. Essa informação chega em tempo real aos embarcadores,
					agilizando a contratação nas mais diversas operações de transporte, e é uma forma inteligente de o motorista autônomo
					aumentar sua oferta de cargas.
				</p>
			</section>

			<section id="beneficios" class="k:flex k:scroll-mt-6 k:flex-col k:gap-5">
				<h2 class="<?=$h2?>">Benefícios</h2>
				<div class="k:grid k:grid-cols-1 k:gap-5 k:md:grid-cols-2">
					<div class="k:flex k:flex-col k:gap-4 k:rounded-2xl k:border k:border-line k:bg-white k:p-6">
						<span class="k:inline-flex k:h-12 k:w-12 k:items-center k:justify-center k:rounded-xl k:bg-kade-50">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0b3b6f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
						</span>
						<h3 class="k:font-display k:text-[19px] k:font-semibold">Para embarcadores e transportadoras</h3>
						<ul class="k:m-0 k:flex k:list-none k:flex-col k:gap-2.5 k:p-0 k:text-[15px] k:leading-relaxed k:text-ink-soft">
							<li class="k:flex k:gap-2.5"><?=$check?>Mais opções de veículos à disposição.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Contato direto com motoristas à procura de frete.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Menos tempo e custo com deslocamento atrás de caminhões vazios.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Economia com telefone, combustível e manutenção da frota.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Embarques mais rápidos.</li>
						</ul>
					</div>
					<div class="k:flex k:flex-col k:gap-4 k:rounded-2xl k:border k:border-line k:bg-white k:p-6">
						<span class="k:inline-flex k:h-12 k:w-12 k:items-center k:justify-center k:rounded-xl k:bg-accent-soft">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#b35f00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 16V6a1 1 0 0 1 1-1h12v11"/><path d="M14 9h4l4 4v3h-8"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
						</span>
						<h3 class="k:font-display k:text-[19px] k:font-semibold">Para motoristas autônomos</h3>
						<ul class="k:m-0 k:flex k:list-none k:flex-col k:gap-2.5 k:p-0 k:text-[15px] k:leading-relaxed k:text-ink-soft">
							<li class="k:flex k:gap-2.5"><?=$check?>Muito mais oferta de cargas.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>As cargas são oferecidas gratuitamente ao motorista.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Menos tempo com o caminhão parado e vazio.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Melhor aproveitamento do veículo e mais renda.</li>
							<li class="k:flex k:gap-2.5"><?=$check?>Segurança e praticidade em lugares pouco conhecidos.</li>
						</ul>
					</div>
				</div>
			</section>

			<section id="usuarios" class="k:flex k:scroll-mt-6 k:flex-col k:gap-5">
				<h2 class="<?=$h2?>">Quem usa a KADE</h2>
				<ul class="k:m-0 k:flex k:list-none k:flex-wrap k:gap-2.5 k:p-0">
					<?php foreach (array("Transportadoras de cargas em geral", "Indústrias", "Comércio", "Atacados", "Agenciadores de cargas") as $userType) { ?>
					<li class="k:rounded-full k:border k:border-line k:bg-white k:px-4 k:py-2 k:text-[15px] k:font-medium k:text-ink-soft"><?=$userType?></li>
					<?php } ?>
				</ul>
			</section>

			<section id="missao" class="k:flex k:scroll-mt-6 k:flex-col k:gap-5">
				<h2 class="<?=$h2?>">Missão, visão e valores</h2>
				<div class="k:grid k:grid-cols-1 k:gap-5 k:md:grid-cols-3">
					<div class="k:flex k:flex-col k:gap-2 k:rounded-2xl k:border k:border-line k:bg-white k:p-6">
						<h3 class="k:font-display k:text-[17px] k:font-semibold k:text-kade-900">Missão</h3>
						<p class="k:text-[15px] k:leading-relaxed k:text-ink-soft">Ofertar em tempo real veículos disponíveis para frete às empresas de transporte e a todas que precisam contratar motoristas autônomos, facilitando o fechamento das cargas com rapidez, segurança e tecnologia, em todo o território nacional.</p>
					</div>
					<div class="k:flex k:flex-col k:gap-2 k:rounded-2xl k:border k:border-line k:bg-white k:p-6">
						<h3 class="k:font-display k:text-[17px] k:font-semibold k:text-kade-900">Visão</h3>
						<p class="k:text-[15px] k:leading-relaxed k:text-ink-soft">Ser uma empresa de atuação nacional, reconhecida como facilitadora na contratação de cargas, contribuindo para o crescimento e a profissionalização do transporte no Brasil.</p>
					</div>
					<div class="k:flex k:flex-col k:gap-2 k:rounded-2xl k:border k:border-line k:bg-white k:p-6">
						<h3 class="k:font-display k:text-[17px] k:font-semibold k:text-kade-900">Valores</h3>
						<p class="k:text-[15px] k:leading-relaxed k:text-ink-soft">Ética, agilidade, comprometimento e segurança para todos os usuários da KADE Caminhões.</p>
					</div>
				</div>
			</section>

			<section id="como_funciona" class="k:flex k:scroll-mt-6 k:flex-col k:gap-5">
				<h2 class="<?=$h2?>">Cadastro por SMS</h2>
				<div class="k:flex k:flex-wrap k:gap-6 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-8">
					<ol class="k:m-0 k:flex k:min-w-0 k:flex-[1_1_380px] k:list-none k:flex-col k:gap-5 k:p-0">
						<li class="k:flex k:gap-4">
							<span class="k:inline-flex k:h-8 k:w-8 k:shrink-0 k:items-center k:justify-center k:rounded-full k:bg-kade-900 k:text-sm k:font-bold k:text-white">1</span>
							<p class="k:text-[15px] k:leading-relaxed k:text-ink-soft">Procure na tabela abaixo o <strong class="k:font-semibold k:text-ink">código do tipo do seu caminhão</strong>.</p>
						</li>
						<li class="k:flex k:gap-4">
							<span class="k:inline-flex k:h-8 k:w-8 k:shrink-0 k:items-center k:justify-center k:rounded-full k:bg-kade-900 k:text-sm k:font-bold k:text-white">2</span>
							<p class="k:text-[15px] k:leading-relaxed k:text-ink-soft">Envie um SMS para <strong class="k:font-semibold k:text-ink k:whitespace-nowrap"><?=$smsNumber?></strong> com o código e o <strong class="k:font-semibold k:text-ink">CEP da cidade onde você está</strong>, separados por <strong class="k:font-semibold k:text-ink">*</strong> (asterisco).</p>
						</li>
						<li class="k:flex k:gap-4">
							<span class="k:inline-flex k:h-8 k:w-8 k:shrink-0 k:items-center k:justify-center k:rounded-full k:bg-kade-900 k:text-sm k:font-bold k:text-white">3</span>
							<p class="k:text-[15px] k:leading-relaxed k:text-ink-soft">Pronto! Transportadoras e embarcadores de todo o Brasil ficam sabendo que você está vazio e entram em contato para oferecer cargas. Você escolhe e fecha com as melhores.</p>
						</li>
					</ol>
					<figure class="k:m-0 k:flex k:min-w-0 k:flex-[1_1_260px] k:flex-col k:gap-3 k:rounded-xl k:bg-surface k:p-5">
						<figcaption class="k:text-[13px] k:leading-relaxed k:text-muted">Exemplo: vazio em Paranaguá/PR com uma Carreta LS Graneleira (código 6), CEP 83203-010:</figcaption>
						<span class="k:text-xs k:font-semibold k:uppercase k:tracking-[1px] k:text-muted">Para: <?=$smsNumber?></span>
						<span class="k:self-end k:rounded-2xl k:rounded-br-md k:bg-kade-900 k:px-4 k:py-2.5 k:font-mono k:text-lg k:font-semibold k:tracking-wide k:text-white">6*83203010</span>
					</figure>
				</div>
			</section>

			<section id="codigos" class="k:flex k:scroll-mt-6 k:flex-col k:gap-5">
				<div class="k:flex k:flex-col k:gap-1">
					<h2 class="<?=$h2?>">Códigos dos veículos</h2>
					<p class="k:text-[15px] k:text-muted">Use o código do seu tipo de caminhão no SMS.</p>
				</div>
				<?php if (count($_vechicleTypes) == 0) { ?>
				<p class="k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:text-[15px] k:text-muted">A lista de códigos está indisponível no momento. Tente novamente mais tarde.</p>
				<?php } else { ?>
				<ul class="k:m-0 k:grid k:list-none k:grid-cols-1 k:gap-2.5 k:p-0 k:sm:grid-cols-2 k:xl:grid-cols-3">
					<?php foreach ($_vechicleTypes as $vehicleType) { ?>
					<li class="k:flex k:items-center k:gap-3 k:rounded-xl k:border k:border-line k:bg-white k:px-4 k:py-3">
						<span class="k:inline-flex k:h-9 k:min-w-9 k:shrink-0 k:items-center k:justify-center k:rounded-lg k:bg-accent-soft k:px-2 k:font-mono k:text-base k:font-bold k:text-[#8a4a00]"><?=$h($vehicleType->getId())?></span>
						<span class="k:text-[15px] k:leading-snug"><?=$h($vehicleType->getDescr())?></span>
					</li>
					<?php } ?>
				</ul>
				<?php } ?>
			</section>

		</div>
	</div>
</div>
