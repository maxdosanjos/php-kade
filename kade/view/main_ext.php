<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$viewExtCtrl->getRequest();
$popUp	= $viewExtCtrl->getWindowPopUp();
$msgPeriod = $viewExtCtrl->getMsgPeriod();
$user = User::getLogged();
$appCssVersion = @filemtime(dirname(dirname(__FILE__))."/shared/css/app.css");

header("Content-Type: text/html; charset=UTF-8",true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>KADE Caminhões</title>
		<meta name="description" content="Trazemos informações em tempo real de veículos disponíveis em todo o território nacional, agilizando seu processo de embarque."/>
		<meta name="robots" content="index, follow">
		<link rel="shortcut icon" href="../shared/images/favicon.ico"/>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap">
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/jquery-ui/jquery-ui.min.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default.css?f=1"/>
		<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/index_ext.css?f=1"/>
		<!-- CSS novo (Tailwind) por último, para vencer o legado em empate -->
		<link type="text/css" rel="stylesheet" href="../shared/css/app.css?v=<?=$appCssVersion?>"/>
		<link rel="alternate" hreflang="pt-BR" href="http://<?=$_SERVER["HTTP_HOST"];?>">
		<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.min.js?f=1"></script>
		<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery-migrate-1.2.1.js?f=1"></script>
		<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery-ui.min.js?f=1"></script>
		<script type="text/javascript" charset="UTF-8" src="../shared/js/notify.min.js?f=1"></script>
		<script type="text/javascript" charset="UTF-8" src="../shared/js/System.class.js?f=1"></script>
		<script type="text/javascript" charset="UTF-8" src="../shared/js/ViewExt.class.js?f=1"></script>
		<script type="text/javascript" charset="UTF-8">$(document).ready(function(){ ViewExt.getInstance().initQuery();});</script>
		<? include_once("analyticstracking.php");?>
	</head>
	<body<?=$popUp ? "" : ' class="k:flex k:min-h-screen k:flex-col"'?>>
		<?if($popUp){?>
		 <style type="text/css">
		 	body{ overflow-x:hidden;overflow-y:hidden; }
		 </style>
		<? $viewExtCtrl->includePage();
		}else{
		?>
		<?php include(dirname(__FILE__)."/partials/header.php"); ?>
		<?php /* <main> e não <div>: o </main> fecha qualquer div que a página deixe aberta, então o rodapé nunca fica aninhado no conteúdo */ ?>
		<main id="div_middle" class="k:flex-1">
			<?php $viewExtCtrl->includePage();?>
		</main>
		<?php include(dirname(__FILE__)."/partials/footer.php"); ?>
		<?php  } ?>
	</body>
</html>
