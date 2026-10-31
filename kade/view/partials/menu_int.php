<?php
/**
 * Menu da área interna (páginas já migradas). As páginas antigas ainda usam view/menu_int.php.
 * No celular vira uma faixa horizontal com rolagem.
 */
$userSuper = User::isUserSuper();

$menuGroups = array();
if ($userSuper) {
	$menuGroups["Administrador"] = array(
		ViewExtCtrl::VIEW_CUSTUMERS => "Gerenciar clientes",
		ViewExtCtrl::LOAD_BANNER    => "Banners da home",
		ViewExtCtrl::GNRT_PARC      => "Contas e parcelas",
		ViewExtCtrl::VIEW_LOG_MSG   => "Log de SMS",
	);
}
$menuGroups["Minha conta"] = array(
	ViewExtCtrl::DATA_USER     => "Meus dados",
	ViewExtCtrl::CAD_VEICULO   => "Cadastre seu veículo",
	ViewExtCtrl::BUSCA_VEICULO => "Buscar veículos",
);
if (!$userSuper) {
	$menuGroups["Minha conta"][ViewExtCtrl::REG_CREATED]  = "Registros realizados";
	$menuGroups["Minha conta"][ViewExtCtrl::REG_UTILIZED] = "Registros utilizados";
	$menuGroups["Minha conta"][ViewExtCtrl::VIEW_PARC]    = "Minhas parcelas";
}
?>
<nav aria-label="Área do cliente" class="k:flex k:w-full k:gap-1 k:overflow-x-auto k:rounded-2xl k:border k:border-line k:bg-white k:p-2 k:lg:block k:lg:overflow-visible k:lg:p-3">
	<?php foreach ($menuGroups as $groupName => $items) { ?>
	<div class="k:flex k:items-center k:gap-1 k:lg:mb-3 k:lg:block k:lg:last:mb-0">
		<span class="k:hidden k:px-3 k:pb-1.5 k:pt-2 k:text-xs k:font-semibold k:uppercase k:tracking-wider k:text-muted k:lg:block"><?=$groupName?></span>
		<ul class="k:flex k:gap-1 k:whitespace-nowrap k:lg:flex-col k:lg:whitespace-normal">
			<?php foreach ($items as $page => $label) {
				$active = $viewExtCtrl->defineClass($page) == "ativo"; ?>
			<li>
				<a href="/<?=$page?>/" class="k-focus k:block k:rounded-[10px] k:px-3 k:py-2 k:text-[15px] <?=$active ? "k:bg-kade-50 k:font-semibold k:text-kade-900" : "k:text-ink-soft k:hover:bg-surface k:hover:text-kade-900"?>"<?=$active ? ' aria-current="page"' : ""?>><?=$label?></a>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
</nav>
