<?php
// Shared header + hero partial for Kade views
// Uses $viewExtCtrl, $user, $msgPeriod from caller
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
    <div class="header-grid py-3 items-center">
      <!-- Logo (left) -->
      <div class="logo-wrap">
        <a href="/" title="voltar para página inicial" class="flex items-center gap-3">
          <img src="../shared/images/favicon.ico" alt="Kade" class="w-12 h-12 object-contain">
          <span class="text-2xl font-extrabold text-slate-800">Kade Caminhões</span>
        </a>
      </div>
      <!-- Center menu -->
      <nav class="menu-wrap">
        <ul>
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
      <div class="login-wrap">
        <?php if(!User::isLogged()){?>
          <div class="login-card rounded-lg border border-slate-200 px-3 py-2 shadow-sm bg-white flex items-center gap-2">
            <form name="FormLoginTopoEmpresa" id="FormLoginTopoEmpresa" method="POST" action="../LoginQueryCtrl.ctrlExt/onLogin" class="flex items-center gap-2">
              <input type="text" size="16" maxlength="20" class="mask.login input_clear input03b rounded-lg border border-slate-200 px-3 py-1.5" placeholder="Seu login" name="user_login" id="user_login" title="seu login" alt="seu login" style="width:120px;">
              <input type="password" value="" placeholder="Sua senha" size="13" maxlength="40" name="user_password" id="user_password" class="input03b cor-cinza5 input_clear rounded-lg border border-slate-200 px-3 py-1.5" title="sua senha" alt="sua senha" style="width:120px;">
              <input type="hidden" value="ok" name="user_rup" />
              <button type="submit" class="bg-kade-primary text-white rounded-lg px-3 py-1.5 text-sm font-medium btn_default submit_login">Entrar</button>
            </form>
          </div>
          <div class="text-right mt-1"><a href="/<?=ViewExtCtrl::NEW_PASSWRD;?>/" class="text-sm text-slate-600 hover:text-slate-800">Esqueceu seu login ou sua senha?</a></div>
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

<!-- Hero / Banner (uses Swiper if available) -->
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
      <div class="col-span-12 md:col-span-5">
        <!-- Swiper -->
        <div class="swiper mySwiper rounded-lg shadow-lg">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <img src="../shared/images/banner_slider1.jpg" alt="Banner" class="w-full h-64 object-cover rounded-lg">
            </div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </div>
  </div>
</section>
