<?php
/*
 * Página de erro do Apache (ErrorDocument 403/404 no .htaccess).
 * Antes redirecionava para a home: o visitante não sabia que o link estava
 * errado e os buscadores recebiam redirect em vez de 404.
 * Caminhos sempre a partir da raiz: esta página responde em qualquer URL.
 */
$code = (int) ($_SERVER["REDIRECT_STATUS"] ?? 404);
if ($code != 403)
	$code = 404;
http_response_code($code);
header("Content-Type: text/html; charset=UTF-8");
$cssVer = @filemtime(__DIR__."/shared/css/app.css");
$title  = ($code == 403) ? "Acesso não permitido" : "Página não encontrada";
$text   = ($code == 403)
	? "Você não tem permissão para abrir este endereço."
	: "O endereço pode ter sido digitado errado ou a página não existe mais.";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title><?=$title?> · KADE Caminhões</title>
	<link rel="shortcut icon" href="/shared/images/favicon.ico"/>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap">
	<link rel="stylesheet" href="/shared/css/app.css?v=<?=$cssVer?>"/>
</head>
<body class="k:m-0 k:bg-surface">
	<main class="k-ui k:flex k:min-h-screen k:flex-col k:items-center k:justify-center k:gap-8 k:px-4 k:py-12 k:text-center k:text-ink">
		<a href="/" class="k-focus k:flex k:items-center k:gap-2.5 k:rounded-lg k:text-kade-900 k:no-underline" title="Página inicial">
			<span class="k:inline-flex k:h-10 k:w-10 k:items-center k:justify-center k:rounded-[10px] k:bg-kade-900">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff8a00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
			</span>
			<span class="k:font-display k:text-xl k:font-bold k:tracking-wide">KADE <span class="k:font-semibold k:text-muted">Caminhões</span></span>
		</a>
		<div class="k:flex k:w-full k:max-w-[520px] k:flex-col k:items-center k:gap-4 k:rounded-2xl k:border k:border-line k:bg-white k:p-8 k:md:p-10">
			<span class="k:font-display k:text-6xl k:font-bold k:text-accent"><?=$code?></span>
			<h1 class="k:font-display k:text-2xl k:font-bold k:text-kade-950"><?=$title?></h1>
			<p class="k:text-base k:leading-relaxed k:text-muted"><?=$text?></p>
			<div class="k:mt-2 k:flex k:flex-wrap k:justify-center k:gap-2.5">
				<a href="/" class="k-btn k-btn-primary k:no-underline">Ir para a página inicial</a>
				<a href="/busca_veiculo/" class="k-btn k:no-underline">Buscar veículos</a>
			</div>
		</div>
	</main>
</body>
</html>
