/**
 * Home: mapa de estados, tabela "Últimos veículos" e destaques.
 */
var HomeExt = function() {
	var REFRESH_SECONDS = 60;
	var selectedUf = "";
	var seconds = REFRESH_SECONDS;
	var timer = null;
	var request = null;

	var markSelected = function() {
		// classList em vez de toggleClass: o jQuery 1.x não altera classes de elementos SVG
		$("#k_map_br .k-uf, .k-uf-btn").each(function() {
			var on = this.getAttribute("data-uf") === selectedUf;
			this.classList.toggle("is-selected", on);
			this.setAttribute("aria-pressed", on ? "true" : "false");
		});
	};

	var loadTable = function() {
		var action = selectedUf !== "" ? "searchMapsByUf" : "searchMaps";
		if (request !== null)
			request.abort();
		request = $.ajax({
			type : "POST",
			dataType : "html",
			url : System.CONTEXT + "MapsExtCtrl.ctrlExt/" + action,
			data : {
				maps_state : selectedUf,
				is_ajax : true
			},
			success : function(html) {
				if (html !== "")
					$("#k_maps_table").html(html);
			},
			complete : function() {
				request = null;
				seconds = REFRESH_SECONDS;
			}
		});
	};

	var tick = function() {
		seconds = seconds - 1;
		if (seconds <= 0)
			loadTable();
		$("#crono").text(Math.max(seconds, 0));
	};

	var selectUf = function(uf) {
		// Clicar de novo no estado selecionado volta para a visão por estados
		selectedUf = (uf === selectedUf) ? "" : uf;
		markSelected();
		loadTable();
	};

	var initBanners = function() {
		var track = $(".k-banner-track");
		if (track.length === 0)
			return;
		var step = function(dir) {
			var el = track.get(0);
			var max = el.scrollWidth - el.clientWidth;
			var next = el.scrollLeft + dir * el.clientWidth;
			// Dá a volta nas pontas
			if (next > max + 1)
				next = 0;
			else if (next < -1)
				next = max;
			el.scrollTo({ left : next, behavior : "smooth" });
		};
		$(".k-banner-prev").on("click", function() { step(-1); });
		$(".k-banner-next").on("click", function() { step(1); });
	};

	this.initQuery = function() {
		$(document).on("click", "#k_map_br .k-uf, .k-uf-btn", function() {
			selectUf($(this).attr("data-uf"));
		});
		$(document).on("keydown", "#k_map_br .k-uf", function(e) {
			if (e.key === "Enter" || e.key === " ") {
				e.preventDefault();
				selectUf($(this).attr("data-uf"));
			}
		});

		// Cidade ou estado da tabela abre a busca já filtrada
		$(document).on("click", "a.a_city_uf_maps", function(e) {
			e.preventDefault();
			$("#vehicle_region").val($(this).attr("data-uf"));
			$("#vehicle_city").val($(this).attr("data-city"));
			$("#form_maps").submit();
		});

		initBanners();
		timer = window.setInterval(tick, 1000);
	};
};
HomeExt.instance = null;
HomeExt.getInstance = function() {
	if (HomeExt.instance == null)
		HomeExt.instance = new HomeExt();
	return HomeExt.instance;
};
