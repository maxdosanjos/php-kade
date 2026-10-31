<?php
/**
 * Header do site. Incluído por main_ext.php, que define $viewExtCtrl, $user e $msgPeriod.
 */
$isLogged = User::isLogged();
$navItems = array(
	ViewExtCtrl::QUEM_SOMOS    => "Quem somos",
	ViewExtCtrl::CAD_VEICULO   => "Cadastre seu veículo",
	ViewExtCtrl::BUSCA_VEICULO => "Busca por veículos",
);
if ($isLogged)
	$navItems[ViewExtCtrl::DATA_USER] = "Meus dados";
else
	$navItems[ViewExtCtrl::CAD_CLIENTE] = "Cadastre-se";
?>
<header class="k-ui k:sticky k:top-0 k:z-50 k:border-b k:border-line k:bg-white">
	<div class="k:mx-auto k:flex k:max-w-[1200px] k:flex-wrap k:items-center k:justify-between k:gap-x-6 k:gap-y-3 k:px-4 k:py-3 k:md:px-6">
		<a href="/" class="k-focus k:flex k:items-center k:gap-2.5 k:rounded-lg k:text-kade-900" title="Página inicial">
			<span class="k:inline-flex k:h-10 k:w-10 k:items-center k:justify-center k:rounded-[10px] k:bg-kade-900">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff8a00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
			</span>
			<span class="k:font-display k:text-xl k:font-bold k:tracking-wide">KADE <span class="k:font-semibold k:text-muted">Caminhões</span></span>
		</a>

		<nav aria-label="Principal" class="k:order-last k:w-full k:overflow-x-auto k:lg:order-none k:lg:w-auto">
			<ul class="k:flex k:gap-6 k:whitespace-nowrap k:text-[15px] k:font-medium">
				<?php foreach ($navItems as $page => $label) {
					$active = $viewExtCtrl->defineClass($page) == "ativo"; ?>
				<li>
					<a href="/<?=$page?>/" class="k-focus k:inline-block k:rounded k:py-1.5 <?=$active ? "k:text-kade-900 k:font-semibold k:shadow-[inset_0_-2px_0_var(--k-color-accent)]" : "k:text-ink-soft k:hover:text-kade-900"?>"<?=$active ? ' aria-current="page"' : ""?>><?=$label?></a>
				</li>
				<?php } ?>
			</ul>
		</nav>

		<div class="k:flex k:items-center k:gap-3">
			<?php if (!$isLogged) { ?>
				<a href="/<?=ViewExtCtrl::NEW_PASSWRD?>/" class="k-focus k:hidden k:rounded k:text-sm k:text-muted k:hover:text-kade-900 k:sm:inline">Esqueci minha senha</a>
				<a href="/<?=ViewExtCtrl::LOGIN?>/" class="k-focus k:inline-flex k:min-h-11 k:items-center k:rounded-[10px] k:bg-kade-900 k:px-5 k:text-[15px] k:font-semibold k:text-white k:hover:bg-kade-800">Entrar</a>
			<?php } else { ?>
				<a href="/<?=ViewExtCtrl::DATA_USER?>/" class="k-focus k:hidden k:max-w-56 k:truncate k:rounded k:text-sm k:text-muted k:hover:text-kade-900 k:sm:inline" title="Meus dados"><?=$msgPeriod?>, <?=htmlspecialchars($user->getPerson()->getName(), ENT_QUOTES, "UTF-8")?></a>
				<form method="post" action="/LoginQueryCtrl.ctrlExt/onLogof" class="k:m-0">
					<button type="submit" class="k-focus k:inline-flex k:min-h-11 k:cursor-pointer k:items-center k:rounded-[10px] k:border k:border-line-strong k:bg-white k:px-[18px] k:font-sans k:text-[15px] k:font-semibold k:text-ink-soft k:hover:bg-surface">Sair</button>
				</form>
			<?php } ?>
		</div>
	</div>
</header>
