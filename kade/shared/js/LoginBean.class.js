var LoginBean = function() {	
	this.initQuery = function(){
		//$("#submit_login").button();
		$("#tabs").tabs();
	};
};
LoginBean.instance = null;
LoginBean.getInstance = function() {
	if (LoginBean.intance == null)
		LoginBean.instance = new LoginBean();
	return LoginBean.instance;
};