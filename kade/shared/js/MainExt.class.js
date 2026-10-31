/**
 * Tela "Imagens da home" (view/load_banner.php).
 * O código da home antiga (slider e tabela com cronômetro) saiu: a home nova usa o HomeExt.
 */
var MainExt = function() {
	var saving = false;
	var timeoutSaving = 60; // em segundos

	var formatEventsLoadBanner = function(){
		var previewUrl = null;
		var timer = null;

		// Mostra a imagem atual do espaço escolhido (ou o aviso de vazio)
		var showCurrent = function(){
			var opt = $("#banner_id option:selected");
			var ver = opt.attr("data-ver");
			var hasImg = (ver != undefined && ver != "0");
			$("#banner_hint").text(opt.attr("data-hint"));
			$("#banner_state").text(hasImg ? "Imagem atual" : "");
			if(hasImg){
				$("#banner_img").attr("src", opt.attr("data-src") + "?v=" + ver).show();
				$("#banner_empty").hide();
			}else{
				$("#banner_img").hide().attr("src", "");
				$("#banner_empty").show();
			}
			$("#button_remove").prop("disabled", !hasImg);
		};
		var clearPreview = function(){
			if(previewUrl){
				URL.revokeObjectURL(previewUrl);
				previewUrl = null;
			}
		};
		var setVersion = function(ver){
			var opt = $("#banner_id option:selected");
			opt.attr("data-ver", ver);
			opt.text(opt.text().replace(/ \(vazio\)$/, "") + (ver == "0" ? " (vazio)" : ""));
		};
		var showResult = function(type, text){
			System.setImgMessage(type);
			$("#dialog-result\.messageText").text(text);
			$("#dialog-result").dialog("open");
		};
		var endSaving = function(){
			saving = false;
			window.clearTimeout(timer);
			timer = null;
			$("#dialog-working").dialog("close");
		};

		$("#banner_id").change(function(){
			clearPreview();
			$("#banner_file").val("");
			showCurrent();
		});

		// Prévia local, antes de enviar
		$("#banner_file").change(function(){
			clearPreview();
			var file = this.files && this.files[0];
			if(!file){
				showCurrent();
				return;
			}
			previewUrl = URL.createObjectURL(file);
			$("#banner_img").attr("src", previewUrl).show();
			$("#banner_empty").hide();
			$("#banner_state").text("Prévia de " + file.name + " (ainda não enviada)");
		});

		$("#banner_form").submit(function(){
			if($("#banner_file").val() == ""){
				showResult("error", "Escolha a imagem que deseja enviar.");
				return false;
			}
			if(saving)
				return false;
			saving = true;
			$("#dialog-working").dialog("open");
			timer = window.setTimeout(function(){
				endSaving();
				showResult("error", "O envio demorou demais. Confira sua conexão e tente novamente.");
			}, timeoutSaving*1000);
			return true;
		});

		$("#banner_iframe").on("load", function(){
			if(!saving)
				return;
			endSaving();
			var json = null;
			try {
				json = $.parseJSON($(this).contents().text());
			} catch(e) {}
			if(json == null){
				showResult("error", "Não foi possível enviar a imagem agora. Tente novamente mais tarde.");
				return;
			}
			if(json.result != "error"){
				clearPreview();
				$("#banner_file").val("");
				setVersion(String(new Date().getTime()));
				showCurrent();
			}
			showResult(json.result, json.message);
		});

		$("#button_remove").click(function(){
			var opt = $("#banner_id option:selected");
			if(!window.confirm('Remover a imagem "' + opt.text() + '" da home?'))
				return;
			$.post("../MapsExtCtrl.ctrlExt/removeBanner", {banner_id: opt.val()}, null, "json")
				.done(function(json){
					if(json.result != "error"){
						clearPreview();
						$("#banner_file").val("");
						setVersion("0");
						showCurrent();
					}
					showResult(json.result, json.message);
				})
				.fail(function(){
					showResult("error", "Não foi possível remover a imagem agora. Tente novamente mais tarde.");
				});
		});

		$("#dialog-result").dialog({
			autoOpen: false,
			width: 460,
			modal: true,
			dialogClass: "k-dialog",
			buttons: {
				"OK": function(){
					$(this).dialog("close");
				}
			}
		});

		$("#dialog-working").dialog({
			width: 380,
			modal: true,
			autoOpen: false,
			draggable: false,
			resizable: false,
			closeOnEscape: false,
			dialogClass: "k-dialog",
			open: function(event, ui) {
				$(this).parent().find('.ui-dialog-titlebar-close').hide();
			}
		});

		showCurrent();
	};

	this.initLoadBanner = function(){
		formatEventsLoadBanner( );
	};
};
MainExt.instance = null;
MainExt.getInstance = function() {
	if (MainExt.instance == null)
		MainExt.instance = new MainExt();
	return MainExt.instance;
};
