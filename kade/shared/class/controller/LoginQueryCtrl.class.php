<?
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/mail/Mail.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ClientVO.class.php");	
	require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/ClientDAO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/UserDAO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
	require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
	class LoginQueryCtrl extends AbstractCtrl {
		private $userDAO      	 = null;
		private $userParam    	 = null;
		private $isRequestNewPwd = false;
		private $resetLastPage	 = false;
		
		public function __construct() {
			parent::__construct ();
			$this->setAction ( $_REQUEST ["action"] ?? null );
			
			$this->message->setType ( $_SESSION["msg_type"] ?? "" );
			$this->message->setDesc ( $_SESSION["msg_txt"] ?? "" );
			$this->resetSessionMsg();
			
			switch ($this->getAction ()) {
				case "onLogin" :
					$this->onLogin();
					break;
				case "onLogof" :
					$this->onLogof();
					break;
				case "requestNewPwd" :
					$this->onRequestNewPwd ();
					break;
				case "confirmNewPwd":
					$this->onConfirmNewPwd();
					break;
				default :
			}
		}
		public function onConfirmNewPwd(){
			$db = null;
			$inTransaction = false;
			$linkValid = false;
			// O usuário é identificado pelo hash do link, nunca por um id enviado pelo navegador
			$vua = isset($_POST["vua"]) ? trim($_POST["vua"]) : "";
			$password = $_POST["custumer_password"] ?? "";
			try
			{
				if(User::isLogged()){
					throw new Exception ( "Não pode ser realizado com o usuário logado! Clique em 'Sair' para redefinir a senha" );
				}

				$this->userDAO = UserDAO::getInstance ();
				$db = DataBase::getInstance ( $this->getDbConfig () );
				$this->userDAO->findLinkPersonId($db,$vua);
				$linkValid = true;

				if($password =="" || strlen ( $password ) < 5){
					throw new Exception("Senha: Número de caracteres insuficiente! Mínimo = 5");
				}

				$inTransaction = $db->beginTransaction ();
				$this->userDAO->resetPwdByLink($db,$vua,$password);
				$db->commit ();
				$db = null;

				$this->message->setType ( Message::SUCCESS );
				$this->message->setDesc ( "Senha redefinida com sucesso" );
			} catch ( PDOException $e ) {
				if ($db != null && $inTransaction)
					$db->rollback ();
				$db = null;
				$linkValid = false;
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( "A url informada já foi utilizada ou está inválida. Solicite uma nova senha novamente." );
			} catch ( Exception $e ) {
				$db = null;
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( $e->getMessage () );
			}

			$_SESSION["msg_type"] = $this->message->getType();
			$_SESSION["msg_txt"] = $this->message->getDesc();

			if($this->message->getType() != Message::ERR)
				$url = "Location:/login/";
			elseif($linkValid)
				$url = "Location:/confirm_new_pwd/vua=".$vua;
			else
				$url = "Location:/request_new_password/";
				
			header($url);			
			
		}
		public function onRequestNewPwd() {
			$person = null;
			$db     = null;
			$e = new ValidationException ( "Dados incompletos!" );
			$output = array (
					"result" => 0,
					"message" => "",
					"validationException" => array ()
			);
			$inTransaction = false;
			$_validationException = array();
			
			
			try {
				switch ($_REQUEST ["type_person"]) {
					case Person::PF :
						$person = new PersonIndividual ();
						$person->setCpf ( $_REQUEST ["newpwd_cpf"] );
						break;
					case Person::PJ :
						$person = new PersonEntity ();
						$person->setCnpj ( $_REQUEST ["newpwd_cnpj"] );
						break;
					default :
						throw new Exception("Necessária a definição de Pessoa Física ou Jurídica");
				}
				
				$person->setEmail ( $_REQUEST ["newpwd_mail"] );
				
				if ($person->getType () != "") {
					if ($person->getType () == Person::PJ) {
						if ($person->getCNPJ () == "") {
							$e->add ( "CNPJ", "custumer_cnpj", "CNPJ vazio!" );
						} else if (! Validation::isCNPJ ( $person->getCNPJ () )) {
							$e->add ( "CNPJ", "custumer_cnpj", "CNPJ inválido!" );
						}
					} elseif ($person->getType () == Person::PF) {
						if ($person->getCPF () == "") {
							$e->add ( "CPF", "custumer_cpf", "CPF vazio!" );
						} else if (! Validation::isCPF ( $person->getCPF () )) {
							$e->add ( "CPF", "custumer_cpf", "CPF inválido" );
						}
					}
					
					if ($person->getEmail () == "") {
						$e->add ( "Email", "custumer_mail", "Email vazio!" );
					} elseif (! Validation::isEMail ( $person->getEmail () )) {
						$e->add ( "Email", "custumer_mail", "Email invalido!" );
					}			
				}
				else
					throw new Exception("Necessária a definição de Pessoa Física ou Jurídica");
				
				if ($e->size () > 0) {
					throw $e;
				}
				
				$db = DataBase::getInstance ( $this->getDbConfig () );
				$inTransaction = $db->beginTransaction ();
				
				
				
				
				$personTypeDAO = null;
				$criteria = new Criteria();
				
				if ($person->getType () == Person::PJ && $person->getCnpj()!=""){
					$personTypeDAO = PersonEntityDAO::getInstance ();
					$criteria->eq("pj.cnpj",$person->getCnpj());
				}elseif ($person->getType () == Person::PF  && $person->getCpf()!=""){
					$personTypeDAO = PersonIndividualDAO::getInstance ();
					$criteria->eq("pf.cpf",$person->getCpf());
				}
				
				$user = null;
				if($personTypeDAO!=null)
				{
					$personDb = $personTypeDAO->getSingle ( $db, $criteria );
					$criteria = null;
					
					if($personDb!=null && $personDb->getId() > 0)
					{		
						$this->userDAO = UserDAO::getInstance ();				
						$criteria = new Criteria();
						$criteria->eq("usr.person_id",$personDb->getId());
						$user = $this->userDAO->getSingle($db,$criteria);
					}
				}		
				$criteria = null;
				
				
				if($user==null){					throw new Exception("Nenhum usuário encontrado!");
				} 		
				$user->getPerson()->setEmail($person->getEmail ());
				$this->sendMailConfirm ( $db, $user );
					
				$db->commit ();
				$db = null;
					
				$person = $user->getPerson ();
				$this->message->setType ( Message::SUCCESS );
				$this->message->setDesc ( "Enviado com sucesso ao email " . $person->getEmail () . "  o link para redefinição de sua senha  " );
				
				
			}catch(ValidationException $e){ 
				if ($db != null && $inTransaction)
					$db->rollback ();
				$db = null;
				
				$_validationException = $e->toArray ( true );
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( $e->getMessage () );		
			}catch ( PDOException $e ) {				
				if ($db != null && $inTransaction)
					$db->rollback ();
				$db = null;
				
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( $e->getMessage () );
			}catch ( Exception $e ) {
				if ($db != null && $inTransaction)
					$db->rollback ();
				$db = null;
				
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( $e->getMessage () );
			}
			
			
			$output ["result"] = $this->message->getType ();
			$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );
			$output ["validationException"] = $_validationException;
			
			header ( "Content-type: text/plain" );
			echo json_encode ( $output );
			exit ();
		}
		public function confirmUser() {
			$inTransaction = false;
			
			$vua = $_REQUEST ["vua"];
			if ($vua != "") {
				try {
					$userDAO = UserDAO::getInstance ();
					$db = DataBase::getInstance ( $this->getDbConfig () );
					$inTransaction = $db->beginTransaction ();
					$userDAO->confirmUserByLink ( $db, $vua );
					$db->commit ();
					$db = null;
				} catch ( PDOException $e ) {
					
					if ($db != null && $inTransaction)
						$db->rollback ();
					$db = null;
					
					throw new Exception ( "Confirmação não pode ser realizada!" );
				}
			} else {
				throw new Exception ( "Confirmação não pode ser realizada!" );
			}
		}
		public function confirmLinkPwd() {
			$inTransaction = false;
			
			$vua = $_REQUEST ["vua"];
			if ($vua != "") {
				if(User::isLogged()){
					throw new Exception ( "Não pode ser realizado com o usuário logado! Clique em 'Sair' para redefinir a senha" );
				}
			
				try {
					$userDAO = UserDAO::getInstance ();
					$db = DataBase::getInstance ( $this->getDbConfig () );
					$inTransaction = $db->beginTransaction ();
					$user = $userDAO->confirmLinkPwd ( $db, $vua );
					$db->commit ();
					$db = null;
					
					return $user;
				} catch ( PDOException $e ) {
					
					if ($db != null && $inTransaction)
						$db->rollback ();
					$db = null;
					
					throw new Exception ( "A url informada já foi utilizada ou está inválida. Será necessário <a href='".ViewExtCtrl::NEW_PASSWRD."'>clicar aqui</a> para solicitar novamente uma nova senha." );
				}
			} else {
				throw new Exception ( "A url informada já foi utilizada ou está inválida. Será necessário <a href='".ViewExtCtrl::NEW_PASSWRD."'>clicar aqui</a> para solicitar novamente uma nova senha." );
			}
		}
		public function onLogof(){
			$_SESSION ["user"] = null;
			header("Location:/");
		}
		public function onLogin(){			
			try 
			{
				$this->userParam = $this->getRequestBean($_REQUEST);
				if($this->userParam==null || $this->userParam->getLogin()=="" || $this->userParam->getPassword()=="")
					throw new Exception ( "Usuário ou senha inválida!" );
					
				$criteria = new Criteria();				
				$criteria->eq("usr.login",$this->userParam->getLogin());
				$criteria->eq("usr.password","MD5('".$this->userParam->getPassword()."')");
				$criteria->eq("usr.status",User::CHEK);
				
				$this->userDAO = UserDAO::getInstance ();
				
			
				$user = $this->userDAO->getSingle($this->getDbConfig (),$criteria);
				if($user==null || ( $user!=null && $user->getLogin() =="") )
					throw new Exception ( "Usuário ou senha inválida!!" );
					
				$_SESSION ["user"] = serialize($user);
				
				if($this->resetLastPage){
					$_SESSION["last_page"] = "";
				}
				
				
				if($_SESSION["last_page"]!=""){
					$url = "Location:/".$_SESSION["last_page"]."/";
					$_SESSION["last_page"] = "";
				}else
					$url = "Location:/";				
			} catch ( PDOException $e ) {			
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( $e->getMessage () );				
			} catch ( Exception $e ) {
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( $e->getMessage () );
			}
			
			if($this->message->getType() == Message::ERR)
			{
				$_SESSION["msg_type"] = $this->message->getType();
				$_SESSION["msg_txt"] = $this->message->getDesc();
				$url = "Location:/login/";
			}		
			else{
				$this->resetSessionMsg();
			}
			header($url);			
		}
		
		public function getRequestBean(array $_data){
			$user = new User();
			$user->setLogin($_data["user_login"]);
			$user->setPassword($_data["user_password"]);
			
			$this->resetLastPage = false;
			if($_data["user_rup"] == "ok")
				$this->resetLastPage = true;
			
			return $user;
		}
		public function isRequestNewPwd(){
			return $this->isRequestNewPwd;
		}
		private function sendMailConfirm($dbConfig, User $user) {
			$md5 = md5 ( date ( "dmYHis" . microtime () ) );
			
			$url = $_SERVER ["HTTP_HOST"] . "/confirm_new_pwd/vua=" . $md5;
			if (! eregi ( "http://", $url ))
				$url = "http://" . $url;
			
			$userDAO = UserDAO::getInstance ();
			$userDAO->registerLinkConfirm ( $dbConfig, $user, $md5 );
			
			$mail = new Mail ();
			ob_start ();
			include_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . "/view/model_mail_req_pwd.php");
			$content = ob_get_contents ();
			ob_end_clean ();
			
			$person = $user->getPerson ();
			
			$subject = "Requisição de nova senha KADE Caminhões";
			$from = new EmailAddress ( 'contato@kadecaminhoes.com.br', 'Contato KADE Caminhões' );
			$to [] = new EmailAddress ( $person->getEmail (), $person->getName () );
			
			$mail->sendPHPMail ( $from, $to, $subject, $content, "text/html" );
		}
		
	}
	
	$ctrlLogQry = new LoginQueryCtrl ();
?>