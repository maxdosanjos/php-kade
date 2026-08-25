var MsgLogBean = function() {
	this.initQuery = function() {
		formatGUIEventsQuery();
	};
	
	var formatGUIEventsQuery = function() {
		$("#msglog_dt_init").datepicker(System.getDatePickerOp());
		$("#msglog_dt_init").datepicker("option", "defaultDate", "+1w");
		//$("#account_dt_init").datepicker("option", "changeMonth", true);
		//$("#account_dt_init").datepicker("option", "numberOfMonths", 3);
		$("#msglog_dt_init").datepicker("option", "onClose", function( selectedDate ) {
				 $( "#msglog_dt_end" ).datepicker( "option", "minDate", selectedDate );
			}
		);
		
		$("#msglog_dt_end").datepicker(System.getDatePickerOp());
		$("#msglog_dt_end").datepicker("option", "defaultDate", "+1w");
		//$("#account_dt_end").datepicker("option", "changeMonth", true);
		//$("#account_dt_end").datepicker("option", "numberOfMonths", 3);
		$("#msglog_dt_end").datepicker("option", "onClose", function( selectedDate ) {
				 $( "#msglog_dt_init" ).datepicker( "option", "maxDate", selectedDate );
			}
		);
		
		System.zebra("tbody_query");

		pagination = new Pagination();
		pagination.init($("#msglog_query_open"));
		
		$("#button_search").button().click(function(e) {
			e.preventDefault();
			$("#pag").val("1");
			$("#msglog_query_open").submit();
		});
		
		$("#button_reset").button().click(function(e) {
			e.preventDefault();
			$("#msglog_query_open .input_clear").val("");
		});
		
	};
};
MsgLogBean.instance = null;
MsgLogBean.getInstance = function() {
	if (MsgLogBean.intance == null)
		MsgLogBean.instance = new MsgLogBean();
	return MsgLogBean.instance;
};