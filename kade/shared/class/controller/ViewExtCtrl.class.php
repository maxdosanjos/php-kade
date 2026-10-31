<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
class ViewExtCtrl {
	CONST QUEM_SOMOS = "quem_somos";
	CONST CAD_VEICULO = "cad_veiculo";
	CONST BUSCA_VEICULO = "busca_veiculo";
	const CAD_CLIENTE = "cad_assinante";
	const CONF_CADCLI = "confirm_user";
	const NEW_PASSWRD = "request_new_password";
	const LOGIN = "login";
	const DATA_USER = "data_user";
	const CONFIRM_PWD = "confirm_new_pwd";
	const REG_CREATED = "reg_created";
	const REG_UTILIZED = "reg_utilized";
	const TERMS_USE = "terms_use";
	const VIEW_CUSTUMERS = "view_custumers";
	const ALT_CUSTUMER = "alter_custumer";
	const GNRT_PARC = "gnrt_parc";
	const VIEW_PARC = "view_parc";
	const ALT_PARC = "alt_parc";
	const LOAD_BANNER = "load_banner";
	const MC_CLIENT	  = "mc_client";
	const VIEW_LOG_MSG	  = "view_log_sms";
	
	private $indexPage   = null;
	private $manuten     = false;
	private $windowPopUp = false;
	private $pageInc     = null;
	
	public function __construct() {
		if (! empty($_SESSION ["auth"]))
			$_GET ["auth"] = $_SESSION ["auth"];
		if ($this->manuten == true && $_SERVER ["HTTP_HOST"] == "kadecaminh.dominiotemporario.com" && $_GET ["auth"] == "MAX") {
			$_SESSION ["auth"] = $_GET ["auth"];
			$this->manuten = false;
		}
		
		if ($this->manuten == true) {
			header ( 'Location: /manutencao.html', true, 307 );
		}
	}
	public function getPageInc() {
		if ($this->pageInc == "")
			$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
		return $this->pageInc;
	}
	public static function verifyIsMobile() {
		$isMobile = preg_match ( "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER ['HTTP_USER_AGENT'] );
		
		return ($isMobile > 0 ? true : false);
	}
	public function getRequest() {
		$this->setIndexPage ( $_REQUEST ["page"] ?? null );
		$this->setWindowPopUp ( $_REQUEST ["windowPopUp"] ?? null );
		switch ($this->getIndexPage ()) {
			case ViewExtCtrl::QUEM_SOMOS :
				$this->pageInc = ViewExtCtrl::getDirView () . "quem_somos.php";
				break;
			case ViewExtCtrl::CAD_VEICULO :
				$this->pageInc = ViewExtCtrl::getDirView () . "cad_veiculo.php";
				break;
			case ViewExtCtrl::CAD_CLIENTE :
				$this->pageInc = ViewExtCtrl::getDirView () . "cad_cliente.php";
				break;
			case ViewExtCtrl::CONF_CADCLI :
				$this->pageInc = ViewExtCtrl::getDirView () . "confirm_user.php";
				break;
			case ViewExtCtrl::NEW_PASSWRD :
				$this->pageInc = ViewExtCtrl::getDirView () . "request_new_password.php";
				break;
			case ViewExtCtrl::CONFIRM_PWD :
				$this->pageInc = ViewExtCtrl::getDirView () . "confirm_new_pwd.php";
				break;
			case ViewExtCtrl::TERMS_USE :
				$this->pageInc = ViewExtCtrl::getDirView () . "terms_use.php";
				break;
			case ViewExtCtrl::LOGIN :
				if (User::isLogged ())
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "login.php";
				}
				break;
			case ViewExtCtrl::BUSCA_VEICULO :
				if (User::isLogged ())
					$this->pageInc = ViewExtCtrl::getDirView () . "busca_veiculo.php";
				else {
					$_SESSION ["last_page"] = $this->getIndexPage ();
					$this->pageInc = ViewExtCtrl::getDirView () . "login.php";
				}
				break;
			case ViewExtCtrl::REG_CREATED :
				if (User::isLogged ())
					$this->pageInc = ViewExtCtrl::getDirView () . "reg_criado.php";
				else {
					$_SESSION ["last_page"] = $this->getIndexPage ();
					$this->pageInc = ViewExtCtrl::getDirView () . "login.php";
				}
				break;
			case ViewExtCtrl::REG_UTILIZED :
				if (User::isLogged ())
					$this->pageInc = ViewExtCtrl::getDirView () . "reg_utilizado.php";
				else {
					$_SESSION ["last_page"] = $this->getIndexPage ();
					$this->pageInc = ViewExtCtrl::getDirView () . "login.php";
				}
				break;
			case ViewExtCtrl::DATA_USER :
				if (User::isLogged ())
					$this->pageInc = ViewExtCtrl::getDirView () . "data_user.php";
				else {
					$_SESSION ["last_page"] = $this->getIndexPage ();
					$this->pageInc = ViewExtCtrl::getDirView () . "login.php";
				}
				break;
			case ViewExtCtrl::VIEW_CUSTUMERS :
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "view_clientes.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::ALT_CUSTUMER :
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "alter_cliente.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::LOAD_BANNER :
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "load_banner.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::GNRT_PARC :
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "busca_conta.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::VIEW_PARC:
				if (User::isLogged ())
					$this->pageInc = ViewExtCtrl::getDirView () . "busca_parc.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::ALT_PARC :
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "alter_conta.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::MC_CLIENT :
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "mc_client.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case ViewExtCtrl::VIEW_LOG_MSG:
				if (User::isUserSuper ())
					$this->pageInc = ViewExtCtrl::getDirView () . "view_log_msg.php";
				else {
					$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				}
				break;
			case "" :
				$this->pageInc = ViewExtCtrl::getDirView () . "maps.php";
				break;
			default :
				// Endereço desconhecido: 404 em vez de mostrar a home (antes respondia 200 com a home)
				http_response_code ( 404 );
				$this->pageInc = ViewExtCtrl::getDirView () . "not_found.php";
		}
	}
	public function includePage() {
		ViewExtCtrl::requestIncludePage ( $this );
	}
	public static function includeMainPage() {
		$viewExtCtrl = new ViewExtCtrl ();
		include_once (ViewExtCtrl::getDirView () . "/main_ext.php");
		exit ();
	}
	public static function requestIncludePage(ViewExtCtrl $viewExtCtrl) {
		$isMobile = ViewExtCtrl::verifyIsMobile ();
		
		include_once ($viewExtCtrl->getPageInc ());
	}
	private static function getDirView() {
		// if(!ViewExtCtrl::verifyIsMobile())
		$dir_view = "/view/";
		return dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . $dir_view;
	}
	private static function getDirJS() {
		$dir_view = "/js/";
		return dirname ( dirname ( dirname ( __FILE__ ) ) ) . $dir_view;
	}
	private static function getDirCss() {
		$dir_view = "/css/";
		return dirname ( dirname ( dirname ( __FILE__ ) ) ) . $dir_view;
	}
	public function setIndexPage($indexPage) {
		$this->indexPage = trim ( $indexPage ?? "" );
	}
	public function setWindowPopUp($windowPopUp){
		$this->windowPopUp = false;
		if($windowPopUp==true){
			$this->windowPopUp = true;
		}
	}
	public function getWindowPopUp(){
		return $this->windowPopUp;
	}
	public function getIndexPage() {
		return $this->indexPage;
	}
	public function __destruct() {
		$this->indexPage = null;
	}
	public function defineClass($currentPage) {
		if ($currentPage == $this->indexPage)
			return "ativo";
	}
	public function getMsgPeriod() {
		$dateTime = new DateTimeCustom ( "Now" );
		if ($dateTime->format ( "H" ) > 18 || $dateTime->format ( "H" ) == 0)
			return "Boa noite";
		elseif ($dateTime->format ( "H" ) < 12 && $dateTime->format ( "H" ) > 0)
			return "Bom dia";
		else
			return "Boa tarde";
	}
}
?>