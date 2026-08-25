var ViewInt = function() {
	this.initQuery = function() {
		$( "#menu" ).menu();
	};

};
ViewInt.instance = null;
ViewInt.getInstance = function() {
	if (ViewInt.intance == null)
		ViewInt.instance = new ViewInt();
	return ViewInt.instance;
};