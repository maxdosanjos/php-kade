<?php 	
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/MapsExtCtrl.class.php");
$totalReg  = $ctrlMapsExt->getTotalReg();
$msgPeriod = $viewExtCtrl->getMsgPeriod();
$user = User::getLogged(); 
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/maps_brazil.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/diapo.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/main_ext.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery-bxslider-v3.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.easing.1.3.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.hoverIntent.minified.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/diapo.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/MainExt.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){MainExt.getInstance().initQuery();});</script>
<div class="pix_background_diapo">
	<div class="pix_diapo">
		<div>
			<img src="../shared/images/banner_slider1.jpg" width="1100px" height="300px" >
			<div class="elemHover caption fromLeft caption_hdr" style="width: auto">
				<h2><?=$msgPeriod?><?=(User::isLogged())?", ".$user->getPerson()->getName():""?>!</h2>
				São <span id="span_tot_login"><?=$totalReg?></span> veículos ativos, <a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" title="Busca por veículos">clique aqui</a> para utilizar
			</div>
	
		</div>
		<div>
			<img src="../shared/images/banner_slider2.jpg" width="1100px" height="300px" usemap="#map_partner">
			<div class="elemHover caption fromLeft caption_hdr" style="width: auto">
				<h2><span id="span_tot_login">Seja nosso parceiro!</span></h2>
				Mais visibilidade e novos clientes para sua empresa!
			</div>
		</div>
		<div>
			<img src="../shared/images/banner_slider3.jpg" width="1100px" height="300px">
			<div class="elemHover caption fromLeft caption_hdr" style="width: auto">
				<h2><?=$msgPeriod?>!</h2>
				São <span id="span_tot_login"><?=$totalReg?></span> veículos ativos, <a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/" title="Busca por veículos">clique aqui</a> para utilizar
			</div>
		</div>
	</div>
</div>
<div id="pix_pag_back"></div>
<div id="div_back_maps">
	<div class="div_body">
		<div class="middle">
			<input type="hidden" id="maps_state" name="maps_state" value="" />
			<div class="container">
				<?    $ctrlMapsExt->onSearchMaps (); include_once("table_maps.php");?>
			</div>
			<!-- .container-->
			<article id="post-62" class="left-sidebar">
				<section id="map-sample">
					<div id="map-br" class="map">
						<ul id="brasil" lang="pt">
							<li id="br1"><a href="#AC">Acre</a></li>
							<li id="br2"><a href="#AL">Alagoas</a></li>
							<li id="br3"><a href="#AP">Amapá</a></li>
							<li id="br4"><a href="#AM">Amazonas</a></li>
							<li id="br5"><a href="#BA">Bahia</a></li>
							<li id="br6"><a href="#CE">Ceara</a></li>
							<li id="br7"><a href="#DF">Distrito Federal</a></li>
							<li id="br8"><a href="#ES">Espírito Santo</a></li>
							<li id="br9"><a href="#GO">Goiás</a></li>
							<li id="br10"><a href="#MA">Maranhão</a></li>
							<li id="br11"><a href="#MT">Mato Grosso</a></li>
							<li id="br12"><a href="#MS">Mato Grosso do Sul</a></li>
							<li id="br13"><a href="#MG">Minas Gerais</a></li>
							<li id="br14"><a href="#PA">Pará</a></li>
							<li id="br15"><a href="#PB">Paraíba</a></li>
							<li id="br16"><a href="#PR">Paraná</a></li>
							<li id="br17"><a href="#PE">Pernambuco</a></li>
							<li id="br18"><a href="#PI">Piauí</a></li>
							<li id="br19"><a href="#RJ">Rio de Janeiro</a></li>
							<li id="br20"><a href="#RN">Rio Grande do Norte</a></li>
							<li id="br21"><a href="#RS">Rio Grande do Sul</a></li>
							<li id="br22"><a href="#RO">Rondônia</a></li>
							<li id="br23"><a href="#RR">Roraima</a></li>
							<li id="br24"><a href="#SC">Santa Catarina</a></li>
							<li id="br25"><a href="#SP">São Paulo</a></li>
							<li id="br26"><a href="#SE">Sergipe</a></li>
							<li id="br27"><a href="#TO">Tocantins</a></li>
						</ul>
					</div>
				</section>
			</article>
		</div>
		<!-- .middle-->
	</div>
</div>

