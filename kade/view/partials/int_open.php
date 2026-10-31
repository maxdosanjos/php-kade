<?php
/**
 * Abertura das páginas da área interna: menu lateral + título.
 * Variáveis: $viewExtCtrl, $pageTitle, $pageDesc (opcional), $pageActions (HTML opcional à direita do título).
 * Fechar com partials/int_close.php.
 */
?>
<div class="k-ui k:text-ink">
	<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-wrap k:items-start k:gap-6 k:px-4 k:py-8 k:md:px-6 k:md:py-10">
		<div class="k:w-full k:lg:w-60 k:lg:shrink-0">
			<?php include(dirname(__FILE__)."/menu_int.php"); ?>
		</div>
		<div class="k:flex k:min-w-0 k:flex-1 k:basis-[640px] k:flex-col k:gap-6">
			<div class="k:flex k:flex-wrap k:items-end k:justify-between k:gap-4">
				<div class="k:flex k:flex-col k:gap-1.5">
					<h1 class="k:font-display k:text-[28px] k:font-bold k:text-kade-950 k:md:text-[32px]"><?=$pageTitle?></h1>
					<?php if (!empty($pageDesc)) { ?><p class="k:text-base k:text-muted"><?=$pageDesc?></p><?php } ?>
				</div>
				<?php if (!empty($pageActions)) { ?><div class="k:flex k:flex-wrap k:gap-2.5"><?=$pageActions?></div><?php } ?>
			</div>
