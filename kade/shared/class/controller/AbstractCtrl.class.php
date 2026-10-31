<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/DBConfig.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Properties.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Message.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Session.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");

// Rotas *.ctrlExt chamam o controller direto, sem passar pelo index.php: erros
// não devem aparecer na resposta (expunham caminhos do servidor) e a sessão
// precisa ser iniciada aqui, senão o $_SESSION gravado no login se perde.
ini_set ( 'display_errors', '0' );
ini_set ( 'log_errors', '1' );
Session::start ();

abstract class AbstractCtrl {
	const ACCESS_PUBLIC = "public";
	const ACCESS_USER   = "user";
	const ACCESS_ADMIN  = "admin";

	private $action;
	private $dbConfig;
	protected $message = null;

	public function __construct() {
		$this->message 	      = new Message();
		$this->loadCurrentDBConfig ();
	}

	/**
	 * Confere o nível de acesso exigido pela ação atual antes de executá-la.
	 * $rules: ação => ACCESS_*; ações fora da lista usam $default.
	 * Sem permissão, responde 403 em JSON (formato que os iframes de envio já tratam) e encerra.
	 */
	protected function guardAction(array $rules, $default = self::ACCESS_PUBLIC) {
		$action = $this->getAction ();
		if ($action === "")
			return;
		$level = $rules [$action] ?? $default;
		$allowed = ($level === self::ACCESS_PUBLIC)
			|| ($level === self::ACCESS_USER && User::isLogged ())
			|| ($level === self::ACCESS_ADMIN && User::isUserSuper ());
		if ($allowed)
			return;

		http_response_code ( User::isLogged () ? 403 : 401 );
		header ( "Content-Type: text/plain; charset=UTF-8" );
		echo json_encode ( array (
			"result" => Message::ERR,
			"message" => User::isLogged () ? "Acesso negado." : "Sua sessão expirou. Entre novamente.",
			"validationException" => array ()
		) );
		exit ();
	}
	/**
	 * URL base para links enviados por e-mail. Usa APP_URL do .env; sem ela, só aceita
	 * o host da requisição se for um domínio conhecido (o Host vem do cliente e pode ser forjado).
	 */
	protected function getBaseUrl() {
		$appUrl = rtrim ( ( string ) Properties::get ( "app.url" ), "/" );
		if ($appUrl !== "")
			return $appUrl;
		$host = strtolower ( $_SERVER ["HTTP_HOST"] ?? "" );
		$knownHosts = array ( "kadecaminhoes.com.br", "www.kadecaminhoes.com.br", "kade.test", "localhost" );
		if (! in_array ( $host, $knownHosts, true ))
			$host = "www.kadecaminhoes.com.br";
		$https = ! empty ( $_SERVER ["HTTPS"] ) && $_SERVER ["HTTPS"] !== "off";
		return ($https ? "https://" : "http://") . $host;
	}

	/**
	 * Token de link (confirmação de cadastro, nova senha): 32 caracteres hexadecimais aleatórios.
	 */
	protected function newToken() {
		return bin2hex ( random_bytes ( 16 ) );
	}

	public function getAction() {
		return $this->action;
	}
	public function setAction($action) {
		$this->action = trim ( $action ?? "" );
	}
	public function getDbConfig() {
		return $this->dbConfig;
	}
	public function loadCurrentDBConfig() {
		if (Properties::get ( "INSTANCE" ) == "PRD") {
			$data = Properties::getGroup ( "dbkade_prd" );
		} else {
			$data = Properties::getGroup ( "dbkade_dev" );
		}
		
		if (! is_array ( $data ) || sizeof ( $data ) == 0) {
			return null;
		}
		
		$this->dbConfig = new DBConfig ();
		$this->dbConfig->setUser ( $data ["user"] );
		$this->dbConfig->setPassword ( $data ["password"] );
		$this->dbConfig->setHost ( $data ["host"] );
		$this->dbConfig->setPort ( $data ["port"] );
		$this->dbConfig->setDatabase ( $data ["database"] );
		
		switch ($data ["DBMS"]) {
			case "mssql" :
			case "sqlserver" :
				$this->dbConfig->setSGBD ( DBConfig::MSSQL );
				break;
			default :
				$this->dbConfig->setSGBD ( DBConfig::MYSQL );
				break;
		}
		
		return $this->dbConfig;
	}
	
	public function getMessage(){
		return $this->message;
	}
	
	public function resetSessionMsg(){
			$_SESSION["msg_type"] = "";
			$_SESSION["msg_txt"]  = "";
	}
}
?>