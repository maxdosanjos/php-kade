<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
if (!User::isUserSuper()){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };

// Espaços de imagem da home: a foto do topo e os 3 banners de "Destaques"
$imgDir = dirname(dirname(__FILE__))."/shared/images/";
$_slots = array(
	"hero" => array("Foto do topo da home", "hero.jpg", "Ideal: 1920 × 800 px. A foto fica atrás do texto, escurecida com o azul da marca. Sem foto, o topo fica só azul."),
	"1"    => array("Destaque 1", "banner_slider1.jpg", "Ideal: 1100 × 300 px."),
	"2"    => array("Destaque 2", "banner_slider2.jpg", "Ideal: 1100 × 300 px."),
	"3"    => array("Destaque 3", "banner_slider3.jpg", "Ideal: 1100 × 300 px."),
);
$_slotsView = array();
foreach ($_slots as $id => $slot) {
	$file = $imgDir.$slot[1];
	$_slotsView[$id] = array(
		"name" => $slot[0],
		"hint" => $slot[2],
		"src"  => "../shared/images/".$slot[1],
		"ver"  => file_exists($file) ? filemtime($file) : 0,
	);
}
$first = $_slotsView["1"];

$pageTitle = "Imagens da home";
$pageDesc  = "Troque a foto do topo e os banners da seção Destaques.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/MainExt.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/MainExt.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){MainExt.getInstance().initLoadBanner();});</script>
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>

	<form id="banner_form" method="post" target="banner_iframe" action="../MapsExtCtrl.ctrlExt/loadBanner" enctype="multipart/form-data" class="k:flex k:flex-col k:gap-6 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-8">
		<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2">
			<label class="k-label" for="banner_id">Imagem
				<select id="banner_id" name="banner_id" class="k-field required">
					<?php foreach ($_slotsView as $id => $slot) { ?>
					<option value="<?=$h($id)?>" data-src="<?=$h($slot["src"])?>" data-ver="<?=$h($slot["ver"])?>" data-hint="<?=$h($slot["hint"])?>"<?=(string) $id === "1" ? " selected" : ""?>><?=$h($slot["name"])?><?=$slot["ver"] ? "" : " (vazio)"?></option>
					<?php } ?>
				</select>
			</label>
			<label class="k-label" for="banner_file">Nova imagem (JPG, até 1 MB)
				<input type="file" id="banner_file" name="banner_file" accept=".jpg,.jpeg,image/jpeg" class="k-field input_clear required k:py-2"/>
			</label>
		</div>

		<figure class="k:m-0 k:flex k:flex-col k:gap-2">
			<div class="k:relative k:flex k:min-h-40 k:items-center k:justify-center k:overflow-hidden k:rounded-xl k:border k:border-line k:bg-surface">
				<img id="banner_img" src="<?=$first["ver"] ? $h($first["src"]."?v=".$first["ver"]) : ""?>" alt="Imagem atual" class="k:max-h-[340px] k:w-full k:object-contain"<?=$first["ver"] ? "" : ' style="display:none"'?>>
				<p id="banner_empty" class="k:m-0 k:p-10 k:text-center k:text-[15px] k:text-muted"<?=$first["ver"] ? ' style="display:none"' : ""?>>Nenhuma imagem enviada para este espaço.</p>
			</div>
			<figcaption class="k:flex k:flex-wrap k:items-center k:justify-between k:gap-2 k:text-[13px] k:text-muted">
				<span id="banner_hint"><?=$h($first["hint"])?></span>
				<span id="banner_state">Imagem atual</span>
			</figcaption>
		</figure>

		<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5 k:border-t k:border-line k:pt-6">
			<span id="span_loc_msg" class="k:mr-auto"></span>
			<button type="button" id="button_remove" class="k-btn k-btn-danger">Remover imagem</button>
			<button type="submit" id="button_save" class="k-btn k-btn-primary">Enviar imagem</button>
		</div>
	</form>

	<p class="k:text-[13px] k:text-muted">Use somente fotos próprias ou com licença de uso. Depois de enviar, a home já mostra a imagem nova (se não aparecer, atualize com Ctrl + F5).</p>

<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>

<iframe id="banner_iframe" name="banner_iframe" class="iframe_ctrl" src="" title="Envio da imagem" hidden></iframe>
<div id="dialog-result" title="Resultado" style="display:none;">
	<div>
		<div id="span_message_icon" class="" style="float:left; display:block;height:32px;width:32px;margin:7px 7px 0px 0;"></div>
		<div id="dialog-result.messageText"></div>
	</div>
</div>
<div id="dialog-working" title="Processando" style="display:none">
	<img id="img_dialog" src="../shared/images/dialog-working.gif" alt="" />
	<p id="dialog-working.message">Aguarde, enviando a imagem ...</p>
</div>
