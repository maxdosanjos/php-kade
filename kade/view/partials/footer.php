<?php
/**
 * Rodapé do site. Incluído por main_ext.php.
 */
?>
<footer class="k-ui k:mt-16 k:shrink-0 k:bg-kade-950 k:text-[#c9d3e3]">
	<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-wrap k:justify-between k:gap-8 k:px-4 k:py-12 k:md:px-6">
		<div class="k:flex k:max-w-sm k:flex-col k:gap-2.5">
			<span class="k:font-display k:text-xl k:font-bold k:text-white">KADE Caminhões</span>
			<span class="k:text-sm k:leading-relaxed">Conectando embarcadores e caminhoneiros em todo o Brasil. É barato, rápido e fácil!</span>
		</div>
		<nav aria-label="Rodapé">
			<ul class="k:flex k:flex-wrap k:gap-x-6 k:gap-y-3 k:text-sm">
				<li><a href="/<?=ViewExtCtrl::QUEM_SOMOS?>/" class="k-focus k:rounded k:text-[#c9d3e3] k:hover:text-white">Quem somos</a></li>
				<li><a href="/<?=ViewExtCtrl::CAD_VEICULO?>/" class="k-focus k:rounded k:text-[#c9d3e3] k:hover:text-white">Cadastre seu veículo</a></li>
				<li><a href="/<?=ViewExtCtrl::BUSCA_VEICULO?>/" class="k-focus k:rounded k:text-[#c9d3e3] k:hover:text-white">Busca por veículos</a></li>
				<li><a href="/<?=ViewExtCtrl::TERMS_USE?>/" class="k-focus k:rounded k:text-[#c9d3e3] k:hover:text-white">Termos de uso</a></li>
				<li><a href="https://www.facebook.com/KadeCaminhoes" target="_blank" rel="noopener" class="k-focus k:rounded k:text-[#c9d3e3] k:hover:text-white">Facebook</a></li>
			</ul>
		</nav>
	</div>
	<div class="k:border-t k:border-white/10 k:px-4 k:py-4 k:text-center k:text-[13px]">
		© 2012–<?=date("Y")?> KADE Caminhões. Todos os direitos reservados.
	</div>
</footer>
