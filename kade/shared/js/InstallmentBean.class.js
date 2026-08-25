var InstallmentBean = function() {
	this.initQuery = function() {
		formatGUIEventsQuery();
	};
	
	var formatGUIEventsQuery = function(){
		System.zebra("tbody_query");
		
		pagination = new Pagination();
		pagination.init($("#parc_query_open"));
		
		$("#parc_status").change(function(e){
			e.preventDefault();
			$("#parc_query_open").submit();
		});
	};
};
InstallmentBean.instance = null;
InstallmentBean.getInstance = function() {
	if (InstallmentBean.intance == null)
		InstallmentBean.instance = new InstallmentBean();
	return InstallmentBean.instance;
};