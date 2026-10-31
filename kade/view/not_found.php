<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
// O status 404 é enviado pelo ViewExtCtrl::getRequest(), antes de qualquer saída
?>
<div class="k-ui k:flex k:flex-1 k:items-center k:justify-center k:px-4 k:py-16 k:text-center k:text-ink">
	<div class="k:flex k:w-full k:max-w-[520px] k:flex-col k:items-center k:gap-4 k:rounded-2xl k:border k:border-line k:bg-white k:p-8 k:md:p-10">
		<span class="k:font-display k:text-6xl k:font-bold k:text-accent">404</span>
		<h1 class="k:font-display k:text-2xl k:font-bold k:text-kade-950">Página não encontrada</h1>
		<p class="k:text-base k:leading-relaxed k:text-muted">O endereço pode ter sido digitado errado ou a página não existe mais.</p>
		<div class="k:mt-2 k:flex k:flex-wrap k:justify-center k:gap-2.5">
			<a href="/" class="k-btn k-btn-primary k:no-underline">Ir para a página inicial</a>
			<a href="/<?=ViewExtCtrl::BUSCA_VEICULO?>/" class="k-btn k:no-underline">Buscar veículos</a>
		</div>
	</div>
</div>
