<?php
/**
 * Cartão de resultado (sucesso ou erro) com ícone, título, texto e ação.
 * Variáveis: $resultOk (bool), $resultTitle, $resultText (HTML), $resultAction (HTML opcional).
 */
?>
<div class="k-ui k:px-4 k:py-14 k:text-ink k:md:py-20">
	<div class="k:mx-auto k:flex k:w-full k:max-w-[520px] k:flex-col k:items-center k:gap-4 k:rounded-2xl k:border k:border-line k:bg-white k:p-8 k:text-center k:md:p-10">
		<span class="k:inline-flex k:h-14 k:w-14 k:items-center k:justify-center k:rounded-full <?=$resultOk ? "k:bg-[#e7f6ec] k:text-[#1a6b35]" : "k:bg-[#fdecec] k:text-[#a12a2a]"?>">
			<?php if ($resultOk) { ?>
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
			<?php } else { ?>
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
			<?php } ?>
		</span>
		<h1 class="k:font-display k:text-2xl k:font-bold k:text-kade-950"><?=$resultTitle?></h1>
		<p class="k:text-base k:leading-relaxed k:text-muted"><?=$resultText?></p>
		<?php if (!empty($resultAction)) { ?><div class="k:mt-2"><?=$resultAction?></div><?php } ?>
	</div>
</div>
