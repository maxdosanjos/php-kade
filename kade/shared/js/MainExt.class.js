var MainExt = function() {
	var timeCrono = null;
	var crono = 60;
	var saving = false;
	var initialFormHash = "";
	var timeoutSaving = 60; // em segundos 
	var errorFields = new Array();
	var checkChangesBeforeSave = false;
	
	var formatEventsLoadBanner = function(){
		$("#button_save").click(function(e) {
			e.preventDefault();
			$("#banner_form").submit();
		});
		
		$("#banner_id").change(function(e){
			e.preventDefault();
			d = new Date();
			$("#banner_img").attr('src',"../shared/images/banner_slider"+$("#banner_id").val()+".jpg");
		});
		
		initialFormHash = $("#banner_form").serialize();
		$("#banner_form").submit(function(){
			// controle de alteração
			var formHash = $(this).serialize();
			if(checkChangesBeforeSave && initialFormHash == formHash){
				System.showMessageDialog("Você não alterou nenhuma informação para salvar!");
				return false;
			}
			initialFormHash = formHash;
			
			if(saving){
				return false;
			}
			saving = true;
			$("#dialog-working").dialog("open");
			
			intTimeOut = window.setTimeout(function(){
				onTimeoutSaving();
			},timeoutSaving*1000);
			
			return true;
		});
		
		$("#banner_iframe").load(function(){
			if(saving){
				saving = false;
				$("#dialog-working").dialog("close");
				
				window.clearInterval(intTimeOut);
				intTimeOut = null;
				
				// resultado
				var messageText = "";
				try {
					var content = $(this).contents().text();
					var json = $.parseJSON(content);
					System.setImgMessage(json.result);

					
					messageText = json.message;
					// independente se deu certo errado, verifica as mensagens
					var messageList = new Array();
					
					// limpando todos os erros anteriores
					for(var key in errorFields){
						var id = errorFields[key];
						$(id).css("background","#FFFFFF");
					}
					errorFields = new Array();
					
					if(json.result == 'error'){
						if(json.validationException.length > 0){
							messageText += "<br/></br>";
							for(var key in json.validationException){
								var obj = json.validationException[key];
								var isValidId = (obj.id != "");
								
								// trocando ponto e adaptando id
								obj.id = "#"+(obj.id.replace(/\./g,"\\."));
								if(isValidId){
									$(obj.id).css("background","#FFC1C1");
								}
								errorFields.push(obj.id);
								messageList.push(obj.message);
							}
							
							messageText += messageList.join("<br/>");
						}
					}else{
						$("#banner_form .input_clear").val("");
						d = new Date();
						$("#banner_img").attr('src', $('#banner_img').attr('src') +'?a='+ d.getMilliseconds());
						
					}
				}catch(e){
					System.setImgMessage('error');
					messageText = "Erro ao realizar registro<br/><br/>"; 
					messageText += "O sistema esta indisponível no momento, aguarde e tente novamente mais tarde. <br/><br/>"; 
					messageText += "Detalhes técnicos: "+e.message; 
				}
				$("#dialog-result\\.messageText").html(messageText);
				$("#dialog-result").dialog("open");
			}
		});
		
		$("#dialog-result").dialog({
			autoOpen: false,
			closeOnEscape: false,
			width: 640,
			height: 240,
			modal: true,
			open: function(event, ui) {
		    	 $(this).parent().children().children('.ui-dialog-titlebar-close').hide();
		    },
			buttons: {
				"OK": function(){
					$(this).dialog("close");
				}
			}
		});
		
		$("#dialog-working").dialog({
			width: 420,
			height: 220,
		    modal: true,
		    autoOpen: false,
		    draggable: false,
	        resizable: false,
		    closeOnEscape: false,
		    open: function(event, ui) {
		    	 $(this).parent().children().children('.ui-dialog-titlebar-close').hide();
		    }
		});
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