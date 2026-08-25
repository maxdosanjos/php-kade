<?php 
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/VehicleRegCtrl.class.php");
$_vechicleTypes = $ctrlVehReg->getTypeVechicles();
$size = sizeof( $_vechicleTypes );
?>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/quem_somos_ext.css?f=1"/>
<div class="logo_back"></div>		
<div id="div_body_quem" class="div_body">
	<h1 id="sumario">Tópicos</h1>
	<ul id="ul_links">
		<li><a href="#quem_somos">Quem somos</a></li>
		<li><a href="#beneficios_clientes">Benefícios dos clientes Kade Caminhões</a></li>
		<li><a href="#beneficio_motoristas">Benefícios aos motoristas autônomos</a></li>
		<li><a href="#usuarios">Usuários da Kade Caminhões</a></li>
		<li><a href="#missao">Missão</a></li>
		<li><a href="#visao">Visão</a></li>
		<li><a href="#valores">Valores</a></li>
		<li><a href="#como_funciona">Como funciona</a></li>
		<li><a href="/<?=ViewExtCtrl::TERMS_USE;?>/" target="_blank">Termo de Uso</a></li>
	</ul>

	<h1 id="quem_somos">Quem somos:</h1>
	<p>
		O <span id="span_logo">KADE CAMINHÕES</span> é uma empresa fundada
		através de diversas pesquisas com os profissionais do setor de
		transporte rodoviário, ofertando de forma simples e eficiente,
		melhorias para o sistema logístico no Brasil. Seu software traz
		informações online de veículos de cargas disponíveis em todo o
		território nacional, esta informação é repassada em tempo real aos
		embarcadores, agilizando o processo de contratação nas mais diversas
		operações de transportes, além de ser uma forma inteligente para os
		motoristas autônomos aumentarem sua oferta de cargas.

	</p>
	<h1 id="beneficios_clientes">Benefícios dos clientes Kade Caminhões:</h1>
	<ul class="ul_text">
		<li>Maior opção de veículos a disposição das transportadoras;</li>
		<li>Contatos assertivos, feitos direto com motoristas à procura de
			fretes;</li>
		<li>Redução no tempo e custos com deslocamento em busca de caminhões
			vazios, aumentando a produtividade dos embarcadores;</li>
		<li>Redução de gastos com telefones, combustível e manutenção de
			frota;</li>
		<li>Agilidade e maior rapidez nos embarques;</li>
	</ul>

	<h1 id="beneficio_motoristas">Benefícios aos motoristas autônomos:</h1>
	<ul class="ul_text">
		<li>Aumento considerável na oferta de cargas:</li>
		<li>As cargas são oferecidas gratuitamente ao motorista;</li>
		<li>Redução do tempo com o caminhão parado vazio;</li>
		<li>Melhoria da eficiência do veículo e aumento na renda do autônomo;</li>
		<li>Segurança e praticidade em locais poucos conhecidos;</li>
	</ul>


	<h1 id="usuarios">Usuários da Kade Caminhões:</h1>
	<ul class="ul_text">
		<li>Transportadoras de cargas em geral;</li>
		<li>Indústrias;</li>
		<li>Comércio;</li>
		<li>Atacados;</li>
		<li>Agenciadores de cargas;</li>
		<li>Etc.</li>
	</ul>

	<h1 id="missao">Missão:</h1>
	<p>Ofertar em tempo real veículos disponíveis para fretes, as empresas
		do setor de transportes e todas as demais que necessitam contratar
		motoristas autônomos, facilitando no fechamento das cargas, com
		rapidez, segurança e tecnologia, atendendo em todo o território
		nacional.</p>
	<h1 id="visao">Visão:</h1>
	<p>Ser uma empresa de atuação nacional, reconhecida como facilitadora
		nos processos de contratação de cargas, contribuindo para o
		crescimento e profissionalização do setor de transportes no Brasil.</p>

	<h1 id="valores">Valores:</h1>
	<p>Ética, agilidade, comprometimento e segurança a todos os usuários da
		Kade Caminhões.</p>
	<h1 id="como_funciona">Como funciona:</h1>
	<p>
		Enviar um sms para o número <span class="number_tel">4396761700</span> com o Código que representa o tipo do seu caminhão, separado por * (Asterisco), Observe os exemplos abaixo:
	</p>
	<p>
		• <span class="number_tel">Exemplo:</span> Você está vazio na cidade de Paranaguá/PR com uma Carreta LS Graneleira (Código 6) envie um SMS com o código que representa o tipo do seu caminhão com o cep
		da cidade onde está, separado por *(asterisco), assim: <span class="number_tel">6*83203010</span>
	</p>
	<p>
		Pronto... agora nosso servidor envia a informação para todas as transportadoras e embarcadores do Brasil, que você está vazio e a procura de fretes, eles farão o contato contigo para
		lhe oferecer as suas cargas, ficando a seu critério escolher e fechar com as melhores.
	</p>
	<!-- <div id="div_logo_sms"></div> -->
	
	<table id="table_demonstr" class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th colspan="12">
					Abaixo a tabela com os códigos dos veículos que devem ser enviados com SMS
				</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$i = 0; 
				$idx = 4;
				while ($i < $size){
					$vehicleType = $_vechicleTypes[$i];					
				if($idx == 0 || $i == 0){
					if($idx == 0)
						$idx = 4;
			?>
				</tr>
				<tr>
			<?
				}
			?>
				<td class="td_descr"><?=$vehicleType->getId()?> - <?=$vehicleType->getDescr()?></td>			
			<? $idx--;$i++;} ?>
		</tbody>
		<tbody>
	</table>


</div>