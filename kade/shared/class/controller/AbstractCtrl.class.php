<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/DBConfig.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Properties.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Message.class.php");
abstract class AbstractCtrl {
	private $action;
	private $dbConfig;
	protected $message = null;
	
	public function __construct() {
		$this->message 	      = new Message();
		$this->loadCurrentDBConfig ();
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