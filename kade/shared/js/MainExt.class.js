var MainExt = function() {
	var timeCrono = null;
	var crono = 60;
	var saving = false;
	var initialFormHash = "";
	var timeoutSaving = 60; // em segundos 
	var errorFields = new Array();
	var checkChangesBeforeSave = false;
	
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

	var showBoxLoading = function() {
		box = $("#box_loading");

		if (box.length == 0) {
			wait_text = "Aguarde, carregando...";
			pag = $("body");
			// Instância
			box = $("<div/>").attr("id", "box_loading");
			elem2 = $("<div/>").css({
				fontWeight : "bold"
			});
			elem3 = $("<center/>");
			elem4 = $("<img/>").attr("src",
					System.CONTEXT + "shared/images/dialog-working.gif").css({
				width : "300px"
			});
			// pack
			elem3.append(elem4);
			elem2.html(wait_text);
			box.append(elem2);
			box.append(elem3);
			pag.append(box);
		}
		box.toggle(true);
	}
	var hideBoxLoading = function() {
		box = $("#box_loading");
		if (box.length != 0) {
			box.toggle(false);
		}
	}
	var loadTable = function() {
		
		var uf = $("#maps_state").val();
		
		var url = System.CONTEXT + "MapsExtCtrl.ctrlExt/searchMaps";

		uf.replace(/^\s+|\s+$/g, "");
		if (uf != "")
			url = System.CONTEXT + "MapsExtCtrl.ctrlExt/searchMapsByUf";

		$.ajax({
			type : "POST",
			dataType : "html",
			url : url,
			data : ({
				maps_state : uf,
				is_ajax : true
			}),
			beforeSend : function() {
				showBoxLoading();
				window.clearInterval(timeCrono);
				timeCrono = null;
			},
			error : function(xhr, ajaxOptions, thrownError) {
				hideBoxLoading();
				System.defaultErrorHandlerJQueryAjax(xhr, ajaxOptions,
						thrownError);
			},
			complete : function() {
				hideBoxLoading();
			},
			success : function(html) {
				if (html != "")
					$("div.container").html(html);
			}
		});

		timeCrono = window.setInterval(countCrono, 1000);
		crono = 60;
	};

	var countCrono = function() {
		if (crono == 0)
			loadTable();
		$("#crono").html(crono);
		crono = crono - 1;
	};

	this.initQuery = function() {
		var loadingText = "Carregando ...";
		var loadingErrorText = "Sem mapa!";
		var tooltipArrowHeight = 6;
		var visibleListId = '#map-visible-list';
		var agentsListId = '#addresses';
		var searchLink = 'search.php';
		var searchLinkVar = 'region';
		var searchName = 'Search';

		// Diapo
		$('.pix_diapo').diapo({
			fx : 'blindCurtainTopLeft',
			time : '5000',
			thumbs : 'false',
			gridDifference : '10'
		});
		
		$(document).on('click', 'a.a_city_uf_maps', function (e) {
			e.preventDefault();
			
			$("#vehicle_region").val($(this).attr("uf"));
			$("#vehicle_city").val($(this).attr("city"));
			
			$("#form_maps").submit();
			
		});

		timeCrono = setInterval(countCrono, 1000);

		$.multipleClickAction = function(e) {
			var clickedRegions = [];
			$('#brasil').find('.active-region').each(function() {
				var liUrl = $(this).children('a').attr('href');
				var slicedUrl = liUrl.slice(1);
				clickedRegions.push(slicedUrl);
			});
			$('#search-link').attr(
					'href',
					searchLink + '?' + searchLinkVar + '='
							+ clickedRegions.join('|'));
		};
		$.defaultClickAction = function(e) {
			var liUrl = $(e).children('a').attr('href');
			$(agentsListId).find('li').hide();
			$(liUrl + ',' + liUrl + ' li').show();
			var uf = liUrl.substring(1, 3);
			$("#maps_state").val(uf);
			loadTable();
			/*
			 * if ($(agentsListId).length > 0) { window.location.hash = liUrl; }
			 * else { window.location.href = liUrl; }
			 */
		};
		$.doubleClickedRegion = function(e) {
			$(e).removeClass('active-region');
			$(agentsListId).find('li').hide();
			$("#maps_state").val("");
			loadTable();
		};
		$('#map-br').prepend('<span id="loader">' + loadingText + '</span>')
				.addClass('script');
		$('#brasil').find('a').hide();
		$(agentsListId).find('li').hide();
		if ($('#map-br').hasClass('multiple-click')) {
			if (searchLink == '') {
				var searchLink = 'search.php';
			}
			if (searchLinkVar == '') {
				var searchLinkVar = 'region';
			}
			if (searchName == '') {
				var searchName = 'Search';
			}
			$(
					'<a href="' + searchLink + '" id="search-link">'
							+ searchName + '</a>').insertAfter('#brasil');
		}
		if ($('#map-br').hasClass('visible-list')) {
			$('#map-br').after(
					'<div id="' + visibleListId.slice(1) + '"><ul></ul></div>');
		}
		var mapUrl = $('#brasil').css('background-image').replace(/^url\("?([^\"\))]+)"?\)$/i, '$1');
		
		var mapImg = new Image();
		$(mapImg).load(
				function() {
					var clickedRegions = [];
					$('#loader').fadeOut();
					$('#brasil').find('li').each(
							function(c) {
								var liid = $(this).attr('id');
								var liUrl = $(this).children('a').attr('href');
								var code = null;
								var spans = 0;
								switch (liid) {
								case 'br8':
								case 'br19':
								case 'br26':
									spans = 9;
									break;
								case 'br4':
								case 'br13':
								case 'br14':
									spans = 34;
									break;
								case 'br5':
									spans = 28;
									break;
								case 'br7':
									spans = 2;
									break;
								case 'br10':
								case 'br12':
								case 'br18':
								case 'br21':
									spans = 19;
									break;
								case 'br9':
								case 'br11':
								case 'br25':
								case 'br27':
									spans = 23;
									break;
								default:
									spans = 14;
								}
								var tooltipLeft = $(this).children('a')
										.outerWidth()
										/ -2;
								var tooltipTop = $(this).children('a')
										.outerHeight()
										* -1 - tooltipArrowHeight;
								if ($('#map-br').hasClass('no-tooltip')) {
									var tooltipTop = 0;
								}
								$(this).prepend('<span class="map" />').append(
										'<span class="bg" />').attr('tabindex',
										c + 1);
								for (var i = 1; i < spans; i++) {
									$(this).find('.map').append(
											'<span class="s' + i + '" />');
								}
								$(this).children('a').css({
									'margin-left' : tooltipLeft,
									'margin-top' : tooltipTop
								});
								if ($('#map-br').hasClass('visible-list')) {
									var liHref = $(this).children('a').attr(
											'href');
									var liText = $(this).children('a').text();
									$(visibleListId + ' ul').append(
											'<li class="' + liid
													+ '"><a href="' + liHref
													+ '">' + liText
													+ '</a></li>');
								}
								if ($(this).children('a').hasClass(
										'active-region')
										|| liUrl == window.location.hash
										&& liUrl != "") {
									$(this).addClass('active-region focus');
									$(agentsListId).find('li').hide();
									$(liUrl + ',' + liUrl + ' li').show();
									$('.' + $(this).attr('id')).children('a')
											.addClass('active-region');
									$('#search-link').attr(
											'href',
											searchLink + '?' + searchLinkVar
													+ '=' + liUrl.slice(1));
								}
							}).hover(function() {
						$.MapHoveredRegion($(this));
					}, function() {
						$.MapUnHoveredRegion($(this));
					}).focus(function() {
						$.MapHoveredRegion($(this));
					}).blur(function() {
						$.MapUnHoveredRegion($(this));
					}).keypress(function(e) {
						code = (e.keyCode ? e.keyCode : e.which);
						if (code == 13)
							$.MapClickedRegion($(this));
					}).click(function(e) {
						$.MapClickedRegion($(this));
					});
					if ($('#map-br').hasClass('visible-list')) {
						$(visibleListId).find('a').each(function() {
							var itemId = '#' + $(this).parent().attr('class');
							$(this).hover(function() {
								$.MapHoveredRegion(itemId);
							}, function() {
								$.MapUnHoveredRegion(itemId);
							}).focus(function() {
								$.MapHoveredRegion(itemId);
							}).blur(function() {
								$.MapUnHoveredRegion(itemId);
							}).keypress(function(e) {
								code = (e.keyCode ? e.keyCode : e.which);
								if (code == 13)
									$.MapClickedRegion(itemId);
							}).click(function(e) {
								$.MapClickedRegion(itemId);
							});
						});
					}
				}).error(function() {
			$('#loader').text(loadingErrorText);
			$('#brasil').find('span').hide();
			$('#map-br,#brasil').css({
				'height' : 'auto',
				'left' : '0',
				'margin' : '0 auto'
			});
		}).attr('src', mapUrl);
		$.MapClickedRegion = function(e) {
			var listItemId = '.' + $(e).attr('id');
			var liUrl = $(e).children('a').attr('href');
			if (typeof liUrl != "undefined") {
				if ($('#map-br').hasClass('multiple-click')) {
					if ($(e).hasClass('active-region')) {
						$(e).removeClass('active-region');
						$(listItemId).children('a')
								.removeClass('active-region');
					} else {
						if (liUrl.length >= 2) {
							$(e).addClass('active-region');
							$(listItemId).children('a').addClass(
									'active-region');
						}
					}
					$.multipleClickAction(e);
				} else {
					if ($(e).hasClass('active-region')) {
						$.doubleClickedRegion(e);
						$(listItemId).children('a')
								.removeClass('active-region');
						$(e).attr('href', '');
					} else {
						$('#brasil,' + visibleListId).find('.active-region')
								.removeClass('active-region');
						$('#brasil').find('.focus').removeClass('focus');
						if ($(e).hasClass('active-region')) {
							$(e).removeClass('active-region focus');
							$(listItemId).children('a').removeClass(
									'active-region');
						} else {
							$(e).addClass('active-region focus').children('a')
									.show();
							$(listItemId).children('a').addClass(
									'active-region');
						}
						$.defaultClickAction(e);
						$(e).children('a').show();
					}
				}
			}
		};
		$.MapHoveredRegion = function(e) {
			var liUrl = $(e).children('a').attr('href');
			if (typeof liUrl != 'undefined' && liUrl != "") {
				$('#brasil').find('.active-region').children('a').hide();
				$(e).children('a').show();
				$(e).addClass('focus');
				$('.' + $(e).attr('id')).children('a').addClass('focus');
			} else {
				$(e).hide();
			}
		};
		$.MapUnHoveredRegion = function(e) {
			$(e).children('a').hide();
			if ($(e).hasClass('active-region') == false) {
				$(e).removeClass('focus');
			}
			$('.' + $(e).attr('id')).children('a').removeClass('focus');
		};
		var loaderLeft = $('#loader').outerWidth() / -2;
		var loaderTop = $('#loader').outerHeight() / -2;
		$('#loader').css({
			'margin-left' : loaderLeft,
			'margin-top' : loaderTop
		});

	};
	
	
	
	this.initLoadBanner = function(){
		formatEventsLoadBanner( );
	};

};
MainExt.instance = null;
MainExt.getInstance = function() {
	if (MainExt.intance == null)
		MainExt.instance = new MainExt();
	return MainExt.instance;
};