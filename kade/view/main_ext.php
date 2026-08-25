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
		<!-- Bootstrap 5 (CDN) -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1FQ784/j6cY/iJTQUOhcWr7x9JvJmZQ5stwEULTy" crossorigin="anonymous">
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
		<div class="header">
			<div class="controle">
				<h1 class="logo">
					<a href="/" title="voltar para página inicial">Kade Caminhões</a>
				</h1>
				<ul class="navigation">
					<li><a href="/<?=ViewExtCtrl::QUEM_SOMOS;?>/" 	 id="a_como_func" title="Quem somos?" class="<?=$viewExtCtrl->defineClass(ViewExtCtrl::QUEM_SOMOS);?>">Quem somos?</a></li>
					<li><a href="/<?=ViewExtCtrl::CAD_VEICULO;?>/" 	 id="a_cad_vei"   title="Cadastre seu veículo" class="<?=$viewExtCtrl->defineClass(ViewExtCtrl::CAD_VEICULO);?>">Cadastre seu veículo</a></li>
					<li><a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" id="a_bus_frt"   title="Busca por veículos" class="<?=$viewExtCtrl->defineClass(ViewExtCtrl::BUSCA_VEICULO);?>">Busca por veículos</a></li>
					<?php if(!User::isLogged()){?>
					<li><a href="/<?=ViewExtCtrl::CAD_CLIENTE;?>/" 	 id="a_cad_assi"  title="Cadastre-se" class="<?=$viewExtCtrl->defineClass(ViewExtCtrl::CAD_CLIENTE);?>">Cadastre-se</a></li>
					<?php } else {?>
					<li><a href="/<?=ViewExtCtrl::DATA_USER;?>/" 	 id="a_dad_assi"  title="Meus Dados" class="<?=$viewExtCtrl->defineClass(ViewExtCtrl::DATA_USER);?>">Meus Dados</a></li>
					<?php } ?>
				</ul>
				<div id="glb-menu_div_login">
					<?php if(!User::isLogged()){?>
					<div id="div_form_login">					
						<form name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogin">
							<input type="text" size="16" maxlength="20" class="mask.login input_clear input03b" placeholder="Seu login" name="user_login" id="user_login" title="seu login" alt="seu login">
							<input type="password" value="" placeholder="Sua senha" size="13" maxlength="40" name="user_password" id="user_password" class="input03b cor-cinza5 input_clear" title="sua senha" alt="sua senha">			
							<input type="hidden" value="ok" name="user_rup" />
							<button id="" class="btn_default submit_login" title="digite o seu login e senha e clique para acessar sua conta" alt="digite o seu login e senha e clique para acessar sua conta">
								<div class="div_btn_with_image"></div>
								<span>Entrar</span>
							</button>
						</form>
					</div>
					<div class="glb-menu_div_login_msg">
						<a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="link-cinza4"> Esqueceu seu login ou sua senha?</a>
					</div>
					<?php } else { ?>
						<div id="div_form_login">					
							<form name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogof">				
								<div id="div_content_logof">								
									<div id="div_name_user_top">
										<a href="/<?=ViewExtCtrl::DATA_USER;?>/" title="Meus Dados"> <?=$msgPeriod?>, <?=$user->getPerson()->getName()?></a>
									</div>
									<button id="" class="btn_default submit_login" title="clique aqui para sair" alt="clique aqui para sair">
										<div class="div_btn_with_image"></div>
										<span>Sair</span>
									</button>
								</div>
							</form>
						</div>
					<?php } ?>
					</br style="clear:both">
				</div>
			</div>
		</div>		
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