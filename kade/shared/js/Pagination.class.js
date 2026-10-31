var Pagination = function() {
	this.init = function(form) {
		initBtn();
		initEvent(form);
	};
	var initBtn = function() {
		$("a.nav_next").button({
			text : false,
			icons : {
				primary : "ui-icon-seek-next"
			}
		}).css({
			height : "18px"
		});

		$("a.nav_end").button({
			text : false,
			icons : {
				primary : "ui-icon-seek-end"
			}
		}).css({
			height : "18px"
		});

		$("a.nav_prev").button({
			text : false,
			icons : {
				primary : "ui-icon-seek-prev"
			}
		}).css({
			height : "18px"
		});

		$("a.nav_first").button({
			text : false,
			icons : {
				primary : "ui-icon-seek-first"
			}
		}).css({
			height : "18px"
		});
	};
	var initEvent = function(form) {
		$(document).on('click', 'a.pag_number', function (event) {
			event.preventDefault();
			$("#pag").val($(this).text());
			form.submit();
		});
		$(document).on('change', '#param_regpag', function () {
			$("#reg_per_pag").val($(this).val());
			form.submit();
		}).keypress(
				function(event) {
					keyPress = event.which;
					if (keyPress == '13' || keyPress == '0') {
						$("#reg_per_pag").val($(this).val());
						form.submit();
					}
					if (keyPress == 8 || keyPress == 9 || keyPress == 27
							|| keyPress == 46)
						return true;
					else if ((keyPress >= 35) && (keyPress <= 40))
						return true;
					else if ((keyPress >= 48) && (keyPress <= 57))
						return true;

					return false;
		});
		
		$(document).on('click', "th.column-order", function (event) {
			event.preventDefault();
			if ($("#order_type").val() == "ASC")
				$("#order_type").val("DESC");
			else
				$("#order_type").val("ASC");
			$("#order_column").val($(this).attr("data-col"));
			form.submit();
		});
		// Ordenação também pelo teclado (Enter ou espaço no cabeçalho)
		$(document).on('keydown', "th.column-order", function (event) {
			if (event.which == 13 || event.which == 32) {
				event.preventDefault();
				$(this).click();
			}
		});
		
		$(document).on('click',"div.nav_pag_right a, div.nav_pag_left a", function (event) {
			event.preventDefault();
			$("#pag").val($(this).attr("data-pag"));
			form.submit();
		});
		$("input:submit").click(function() {
			$("#pag").val(1);
		});
	};
};
Pagination.instance = null;
Pagination.getInstance = function() {
	if (Pagination.intance == null)
		Pagination.instance = new Pagination();
	return Pagination.instance;
};