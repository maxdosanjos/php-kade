<?php
/**
 * Paginação das listas. Espera $pagination (Pagination). O Pagination.class.js
 * procura os links em div.nav_pag_left/div.nav_pag_right (atributo lang = página)
 * e os números em a.pag_number.
 */
if ($pagination->getTotalPag() > 1) { ?>
<nav aria-label="Paginação" class="k:flex k:flex-wrap k:items-center k:justify-center k:gap-2 k:border-t k:border-line k:px-5 k:py-4">
	<div class="nav_pag_left k:flex k:gap-2">
	<? if($pagination->getPagCurrent()!=1) { ?>
		<a href="#" class="nav_first k-btn k:min-w-10 k:px-3" title="Primeira página" aria-label="Primeira página" data-pag="<?=$pagination->getStartPag();?>">«</a>
		<a href="#" class="nav_prev k-btn k:min-w-10 k:px-3" title="Página anterior" aria-label="Página anterior" data-pag="<?=$pagination->getPreviousPag();?>">‹</a>
	<? } ?>
	</div>
	<div class="k-pager k:flex k:gap-2">
		<?=$pagination->getPagByNumber(2);?>
	</div>
	<div class="nav_pag_right k:flex k:gap-2">
	<? if($pagination->getPagCurrent()!= $pagination->getTotalPag() && $pagination->getTotalReg() > 0) { ?>
		<a href="#" class="nav_next k-btn k:min-w-10 k:px-3" title="Próxima página" aria-label="Próxima página" data-pag="<?=$pagination->getNextPag();?>">›</a>
		<a href="#" class="nav_end k-btn k:min-w-10 k:px-3" title="Última página" aria-label="Última página" data-pag="<?=$pagination->getTotalPag();?>">»</a>
	<?} ?>
	</div>
</nav>
<?php } ?>
<input type="hidden" id="param_regpag" name="param_regpag" value="<?=$pagination->getEndLimit();?>"/>
