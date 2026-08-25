<?php 
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}	
$viewExtCtrl->getRequest();
$popUp	= $viewExtCtrl->getWindowPopUp();
$msgPeriod = $viewExtCtrl->getMsgPeriod();
$user = User::getLogged();

header("Content-Type: text/html; charset=UTF-8",true);
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
	<title>Kade Caminhões</title>
		<meta http-equiv="Content-Language" content="pt-br" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
		<meta name="title" content="Kade Caminhões"/>
		<meta name="description" content="Trazemos informações em tempo real de veículos disponíveis em todo o território nacional, agilizando seu processo de embarque."/>
		<meta name="robots" content="index, follow">
		<link rel="shortcut icon" href="../shared/images/favicon.ico"/>
		<!-- Tailwind CSS (Play CDN) -->
		<script src="https://cdn.tailwindcss.com"></script>
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
		<script>
		  // Tailwind config: small container max-width to match legacy layout and custom fonts/colors
		  tailwind.config = {
		    theme: {
		      container: {
		        center: true,
		        padding: '1rem',
		      },
		      extend: {
		        colors: {
		          // primary: deep navy similar to logistics themes
		          'kade-primary': '#0b3b6f',
		          // accent: energetic orange for CTAs
		          'kade-accent': '#ff8a00',
		          'kade-muted': '#6b7280'
		        },
		        fontFamily: {
		          sans: ['Poppins', 'ui-sans-serif', 'system-ui']
		        }
		      }
		    }
		  }
		</script>
		<!-- Project styles (kept after tailwind to allow overrides) -->
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/jquery-ui/jquery-ui.min.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/index_ext.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/bootstrap-helpers.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/tailwind-helpers.css?f=1"/>
		<link rel="alternate" hreflang="pt-BR" href="http://<?=$_SERVER["HTTP_HOST"];?>">
		<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.min.js?f=1"></script>
		<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery-migrate-1.2.1.js?f=1"></script>
		<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery-ui.min.js?f=1"></script>
		<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/notify.min.js?f=1"></script>
		<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/System.class.js?f=1"></script>
		<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewExt.class.js?f=1"></script>
		<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ ViewExt.getInstance().initQuery();});</script>
		<? include_once("analyticstracking.php");?>
	</head>
	<body>
		<?if($popUp){?>
		 <style type="text/css">
		 	body{ overflow-x:hidden;overflow-y:hidden; }
		 </style>
		<? $viewExtCtrl->includePage(); 
		}else{
		?>

		<!-- Top contact bar -->
		<div class="topbar bg-slate-900 text-white text-sm">
		  <div class="container mx-auto px-4 flex justify-between items-center py-1">
		    <div class="flex items-center space-x-4">
		      <div>📞 (41) 1234-5678</div>
		      <div>✉️ contato@kadecaminhoes.com.br</div>
		    </div>
		    <div class="flex items-center space-x-3">
		      <a href="#" class="text-white hover:underline">Entrar</a>
		      <a href="/<?=ViewExtCtrl::CAD_CLIENTE;?>/" class="text-white hover:underline">Cadastre-se</a>
		    </div>
		  </div>
		</div>
			
		<header class="site-header bg-white">
		  <div class="container mx-auto px-4">
		    <div class="header-grid py-2">
		      <!-- Logo (left) -->
		      <div class="logo-wrap">
		        <a href="/" title="voltar para página inicial" class="flex items-center gap-3">
		          <img src="../shared/images/favicon.ico" alt="Kade" class="w-10 h-10 object-contain">
		          <span class="text-2xl font-extrabold text-slate-800">Kade Caminhões</span>
		        </a>
		      </div>
		      <!-- Center menu -->
		      <nav class="menu-wrap">
		        <ul class="inline-flex space-x-8">
		          <li><a href="/<?=ViewExtCtrl::QUEM_SOMOS;?>/" id="a_como_func" class="menu-link text-slate-700 md:text-lg font-medium <?=$viewExtCtrl->defineClass(ViewExtCtrl::QUEM_SOMOS);?>">Quem somos?</a></li>
		          <li><a href="/<?=ViewExtCtrl::CAD_VEICULO;?>/" id="a_cad_vei" class="menu-link text-slate-700 md:text-lg font-medium <?=$viewExtCtrl->defineClass(ViewExtCtrl::CAD_VEICULO);?>">Cadastre seu veículo</a></li>
		          <li><a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" id="a_bus_frt" class="menu-link text-slate-700 md:text-lg font-medium <?=$viewExtCtrl->defineClass(ViewExtCtrl::BUSCA_VEICULO);?>">Busca por veículos</a></li>
		          <?php if(!User::isLogged()){?>
		            <li><a href="/<?=ViewExtCtrl::CAD_CLIENTE;?>/" id="a_cad_assi" class="menu-link text-slate-700 <?=$viewExtCtrl->defineClass(ViewExtCtrl::CAD_CLIENTE);?>">Cadastre-se</a></li>
		          <?php } else {?>
		            <li><a href="/<?=ViewExtCtrl::DATA_USER;?>/" id="a_dad_assi" class="menu-link text-slate-700 <?=$viewExtCtrl->defineClass(ViewExtCtrl::DATA_USER);?>">Meus Dados</a></li>
		          <?php } ?>
		        </ul>
		      </nav>
		      <!-- Login card (right) -->
		      <div class="login-wrap flex justify-end">
		        <?php if(!User::isLogged()){?>
		          <div class="login-card rounded-lg border border-slate-200 px-3 py-1.5 shadow-sm bg-white">
		            <form name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogin" class="flex items-center space-x-2">
		              <input type="text" size="16" maxlength="20" class="mask.login input_clear input03b rounded-md border border-slate-200 px-3 py-1.5" placeholder="Seu login" name="user_login" id="user_login" title="seu login" alt="seu login" style="width:140px;">
		              <input type="password" value="" placeholder="Sua senha" size="13" maxlength="40" name="user_password" id="user_password" class="input03b cor-cinza5 input_clear rounded-md border border-slate-200 px-3 py-1.5" title="sua senha" alt="sua senha" style="width:140px;">
		              <input type="hidden" value="ok" name="user_rup" />
		              <button type="submit" class="bg-kade-primary text-white rounded-lg px-3 py-1.5 text-sm font-medium btn_default submit_login">Entrar</button>
		            </form>
		            <div class="text-right mt-1"><a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="text-sm text-slate-600 hover:text-slate-800">Esqueceu seu login ou sua senha?</a></div>
		          </div>
		        <?php } else { ?>
		          <div class="rounded-lg border border-slate-200 px-3 py-1.5 bg-white">
		            <form name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogof" class="flex items-center space-x-3">
		              <a href="/<?=ViewExtCtrl::DATA_USER;?>/" class="text-sm text-slate-700"> <?=$msgPeriod?>, <?=$user->getPerson()->getName()?></a>
		              <button type="submit" class="text-sm text-slate-700">Sair</button>
		            </form>
		          </div>
		        <?php } ?>
		      </div>
		    </div>
		  </div>
		</header>
			
		<!-- Hero / Banner -->
		<section class="hero relative">
		  <div class="container mx-auto px-4">
		    <div class="grid grid-cols-12 gap-4 items-center py-12">
		      <div class="col-span-12 md:col-span-7">
		        <h1 class="text-4xl md:text-5xl font-bold text-slate-900 leading-tight">Conectando caminhões e cargas em todo o Brasil</h1>
		        <p class="mt-4 text-slate-600">Encontre fretes ou divulgue disponibilidade de veículos com rapidez e segurança.</p>
		        <div class="mt-6 flex flex-wrap gap-3">
		          <a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" class="inline-block bg-kade-primary hover:bg-kade-accent text-white font-medium rounded-lg px-5 py-3">Buscar veículos</a>
		          <a href="/<?=ViewExtCtrl::CAD_VEICULO;?>/" class="inline-block border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg px-4 py-3">Cadastre seu veículo</a>
		        </div>
		      </div>
		      <div class="col-span-12 md:col-span-5 hidden md:block">
		        <img src="../shared/images/banner_slider1.jpg" alt="Banner" class="rounded-lg shadow-lg object-cover w-full h-64">
		      </div>
		    </div>
		  </div>
		</section>
			
		<div id="div_middle">
			<?php $viewExtCtrl->includePage();?>
		</div>
		<footer>
			<div class="footer">
				<div class="div_body">
					<div id="div_esq_foot">
						<ul class="navigation">
							<li><a href="/<?=ViewExtCtrl::QUEM_SOMOS;?>/" 	 id="a_como_func_foot"  title="Quem somos?">Quem somos?</a></li>
							<li><a href="/<?=ViewExtCtrl::CAD_VEICULO;?>/"   id="a_cad_vei_foot"    title="Cadastre seu veículo">Cadastre seu veículo</a></li>
							<li><a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" id="a_bus_frt_foot"    title="Busca por veículos">Busca por veículos</a></li>
							<?php if(!User::isLogged()){?>
								<li><a href="/<?=ViewExtCtrl::CAD_CLIENTE;?>/" 	 id="a_cad_assi_foot"  title="Cadastre-se">Cadastre-se</a></li>
							<?php } else {?>
								<li><a href="/<?=ViewExtCtrl::DATA_USER;?>/" 	 id="a_dad_assi_foot"  title="Meus Dados">Meus Dados</a></li>
							<?php } ?>
						</ul>				
					</div>
					<div id="div_dir_foot">
						<span id="span_logo_fr">É barato, rápido e fácil!</span>
						<!-- <span id="span_pag_txt">Formas de Pagamento:</span>
						<span>
							<img src="../shared/images/logo_pagseguro.gif" width="180px" height="41px" />
						</span>
						 -->				
						<span id="span_cpy_ri">Copyright © 2012-<?=date("Y")?> Kade Caminhoes - Todos os direitos reservados.</span>
					</div>
					<span class="span_both"></span>
				</div>
			</div>
		</footer>
		<!-- .footer -->
		<div id="fix-facebook">
			<p class="bt-link bt-face">
				<a href="https://www.facebook.com/KadeCaminhoes" target="_blank" title="Acesse a Página Facebook da Kade"> 
					<img src="../shared/images/ico-facebook.jpg" alt="Acesse a Página Facebook da Kade" title="Acesse a Página Facebook da Kade" width="61px" height="61px">
				</a>
			</p>
		</div>
		<!-- Bootstrap bundle (CDN) -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
		<?php  } ?>
	</body>
</html>