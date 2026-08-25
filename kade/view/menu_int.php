<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}

$userSuper 	   = User::isUserSuper();
?>
<ul id="menu" class="ui-corner-all">
	<? if($userSuper){?>
	<li>
		<span class="ui-icon ui-icon-transferthick-e-w"></span>
		Administrador
		<ul>
			<li>
				<span class="ui-icon ui-icon-flag"></span>
				<a href="/<?=ViewExtCtrl::VIEW_CUSTUMERS;?>/">Gerenciar Clientes</a>
			</li>
			<li>
				<span class="ui-icon ui-icon-refresh"></span>
				<a href="/<?=ViewExtCtrl::LOAD_BANNER;?>/">Carregar Banner Principal</a>
			</li>
			<li>
				<span class="ui-icon ui-icon-calculator"></span>
				<a href="/<?=ViewExtCtrl::GNRT_PARC;?>/">Gerenciar Contas(Parcelas)</a>
			</li>
			<li>
				<span class="ui-icon ui-icon-refresh"></span>
				<a href="/<?=ViewExtCtrl::VIEW_LOG_MSG;?>/">Log de SMS</a>
			</li>			
		</ul>
	</li>
	<? } ?>
	<li>
		<span class="ui-icon ui-icon-person"></span>
		<a href="/<?=ViewExtCtrl::DATA_USER;?>/">Meus dados</a>
	</li>
	<li>
		<span class="ui-icon ui-icon-disk"></span>
		<a href="/<?=ViewExtCtrl::CAD_VEICULO;?>/">Cadastre seu veículo</a>
	</li>
	<li>
		<span class="ui-icon ui-icon-search"></span>
		<a href="/<?=ViewExtCtrl::BUSCA_VEICULO;?>/">Buscar veículos</a>
	</li>
	<? if(!$userSuper){?>
		<li>
			<span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
			<a href="/<?=ViewExtCtrl::REG_CREATED;?>/">Registros realizados</a>
		</li>
		<li>
			<span class="ui-icon ui-icon-refresh"></span>
			<a href="/<?=ViewExtCtrl::REG_UTILIZED;?>/">Registros utilizados</a>
		</li>
		
		<li>
			<span class="ui-icon ui-icon-calculator"></span>
			<a href="/<?=ViewExtCtrl::VIEW_PARC;?>/">Minhas parcelas</a>
		</li>
	<? } ?>
</ul>
