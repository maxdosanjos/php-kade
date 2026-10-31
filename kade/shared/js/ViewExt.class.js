var ViewExt = function() {
	this.initQuery = function() {
		// Botões usam o visual novo (classes k-btn); nada a inicializar aqui
	};

};
ViewExt.instance = null;
ViewExt.getInstance = function() {
	if (ViewExt.intance == null)
		ViewExt.instance = new ViewExt();
	return ViewExt.instance;
};