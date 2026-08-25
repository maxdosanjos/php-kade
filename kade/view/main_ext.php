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
		<!-- Bootswatch Lux (Bootstrap 5 theme, CDN) -->
		<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/lux/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
		<!-- Project styles (kept after bootstrap to allow overrides) -->
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/jquery-ui/jquery-ui.min.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/index_ext.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/bootstrap-helpers.css?f=1"/>
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

		<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		  <div class="container-fluid">
			<a class="navbar-brand" href="/" title="voltar para página inicial">Kade Caminhões</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#kadeNavbar" aria-controls="kadeNavbar" aria-expanded="false" aria-label="Toggle navigation">
			  <span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="kadeNavbar">
			  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item"><a class="nav-link <?=$viewExtCtrl->defineClass(ViewExtCtrl::QUEM_SOMOS);?>" href="/<?=ViewExtCtrl::QUEM_SOMOS;?>/" id="a_como_func" title="Quem somos?">Quem somos?</a></li>
				<li class="nav-item"><a class="nav-link <?=$viewExtCtrl->defineClass(ViewExtCtrl::CAD_VEICULO);?>" href="/<?=ViewExtCtrl::CAD_VEICULO;?>/" id="a_cad_vei" title="Cadastre seu veículo">Cadastre seu veículo</a></li>
				<li class="nav-item"><a class="nav-link <?=$viewExtCtrl->defineClass(ViewExtCtrl::BUSCA_VEICULO);?>" href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" id="a_bus_frt" title="Busca por veículos">Busca por veículos</a></li>
				<?php if(!User::isLogged()){?>
				<li class="nav-item"><a class="nav-link <?=$viewExtCtrl->defineClass(ViewExtCtrl::CAD_CLIENTE);?>" href="/<?=ViewExtCtrl::CAD_CLIENTE;?>/" id="a_cad_assi" title="Cadastre-se">Cadastre-se</a></li>
				<?php } else {?>
				<li class="nav-item"><a class="nav-link <?=$viewExtCtrl->defineClass(ViewExtCtrl::DATA_USER);?>" href="/<?=ViewExtCtrl::DATA_USER;?>/" id="a_dad_assi" title="Meus Dados">Meus Dados</a></li>
				<?php } ?>
			  </ul>
			  <div class="d-flex align-items-center">
			  <?php if(!User::isLogged()){?>
				<form class="d-flex" name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogin">
					<input type="text" name="user_login" id="user_login" placeholder="Seu login" class="form-control form-control-sm me-2 mask.login input_clear input03b" size="16" maxlength="20" title="seu login" alt="seu login">
					<input type="password" name="user_password" id="user_password" placeholder="Sua senha" class="form-control form-control-sm me-2 input03b cor-cinza5 input_clear" size="13" maxlength="40" title="sua senha" alt="sua senha">
					<input type="hidden" value="ok" name="user_rup" />
					<button type="submit" class="btn btn-light btn-sm me-2 btn_default submit_login" title="digite o seu login e senha e clique para acessar sua conta"><i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-sm-inline">Entrar</span></button>
				</form>
				<a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="link-cinza4 text-light">Esqueceu seu login ou sua senha?</a>
			  <?php } else { ?>
				<form name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogof" class="d-flex align-items-center">
					<span class="me-3 text-light"><a href="/<?=ViewExtCtrl::DATA_USER;?>/" class="text-light" title="Meus Dados"><?=$msgPeriod?>, <?=$user->getPerson()->getName()?></a></span>
					<button type="submit" class="btn btn-light btn-sm btn_default submit_login" title="clique aqui para sair"><i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Sair</span></button>
				</form>
			  <?php } ?>
			  </div>
			</div>
		  </div>
		</nav>
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