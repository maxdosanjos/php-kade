<?php
/**
 * Abertura das páginas públicas com conteúdo centralizado (login, senha, cadastro...).
 * Variáveis: $pageTitle, $pageDesc (opcional), $pageWidth: "sm" (padrão), "md" ou "lg".
 * Fechar com partials/pub_close.php.
 */
$pageWidths = array("sm" => "k:max-w-[460px]", "md" => "k:max-w-[640px]", "lg" => "k:max-w-[880px]");
?>
<div class="k-ui k:px-4 k:py-10 k:text-ink k:md:py-14">
	<div class="k:mx-auto k:w-full <?=$pageWidths[$pageWidth ?? "sm"] ?? $pageWidths["sm"]?>">
		<div class="k:mb-6 k:flex k:flex-col k:gap-1.5 k:text-center">
			<h1 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]"><?=$pageTitle?></h1>
			<?php if (!empty($pageDesc)) { ?><p class="k:text-base k:text-muted"><?=$pageDesc?></p><?php } ?>
		</div>
		<div class="k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:shadow-[0_1px_2px_rgba(15,27,45,.04)] k:md:p-8">
