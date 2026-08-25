var ViewExt = function() {
	this.initQuery = function() {
		$("button.submit_login").button();
	};

};
ViewExt.instance = null;
ViewExt.getInstance = function() {
	if (ViewExt.intance == null)
		ViewExt.instance = new ViewExt();
	return ViewExt.instance;
};