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
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class CustumerRegCtrl extends AbstractCtrl {
	private $clientDAO = null;
	private $clientEdit = null;
	public function __construct() {
		parent::__construct ();
		$this->setAction ( $_REQUEST ["action"] ?? null );

		$this->guardAction(array("save" => AbstractCtrl::ACCESS_PUBLIC, "getCaptcha" => AbstractCtrl::ACCESS_PUBLIC, "saveInter" => AbstractCtrl::ACCESS_USER), AbstractCtrl::ACCESS_ADMIN);
		
		switch ($this->getAction ()) {
			case "save" :
				$this->onSaveWeb ();
				break;
			case "saveInter" :
				$this->onSaveInter ();
				break;
			case "edit" :
				$this->onEdit ();
				break;
			case "getCaptcha" :
				$this->getImageCaptcha ();
				break;
			default :
		}
	}
	public function onEdit() {
		$this->clientEdit = null;
		
		$person = new Person();
		$person->setId ( $_GET ["custumer_person_id"] );
		try {
			if ($person==null || $person->getId() <= 0)
				throw new Exception ( "Cliente não encontrado!" );
			
			$criteria = new Criteria ();
			$criteria->eq ( "cli.user_person_id", $person->getId() );
			
			$this->clientDAO = ClientDAO::getInstance ();		
			$this->clientEdit = $this->clientDAO->getSingle ( $this->getDbConfig (), $criteria );
			if ($this->clientEdit == null || $this->clientEdit->getUser()->getPerson()->getId() <= 0)
				throw new Exception ( "Cliente não encontrado!" );
			
		} catch ( PDOException $e ) {
			$this->clientEdit = null;
		} catch ( Exception $e ) {
			$this->clientEdit = null;
		}
	}
	public function getClientEdit(){
		return $this->clientEdit;
	}
	public function getRequestBean(array $_data) {
		$client = new Client ();
		$person = null;
		$phone = new Phone ();
		$address = new Address ();
		
			$type_person = $_data["type_person"] ?? '';
			switch ($type_person) {
				case Person::PF :
					$person = new PersonIndividual ();
					$person->setName ( $_data["custumer_name"] ?? '' );
					$person->setCpf ( $_data["custumer_cpf"] ?? '' );
					break;
				case Person::PJ :
					$person = new PersonEntity ();
					$person->setName ( $_data["custumer_rsoc"] ?? '' );
					$person->setContact ( $_data["custumer_contact"] ?? '' );
					$person->setCnpj ( $_data["custumer_cnpj"] ?? '' );
				
					if (isset($_data["custumer_ie_isento"]) && $_data["custumer_ie_isento"] == "on")
						$_data["custumer_ie"] = PersonEntity::ISENTO;
				
					$person->setIe ( $_data["custumer_ie"] ?? '' );
				
					break;
				default :
					return $client;
			}
		
			$person->setEmail ( $_data["custumer_mail"] ?? '' );
		
			$phone->setPhoneByMask ( $_data["custumer_phone"] ?? '' );
		
			$phone->setId ( $_data["custumer_phone_id"] ?? null );
		
			$person->setPhone ( $phone );
		
			$address->setCep ( $_data["custumer_zipcode"] ?? '' );
			$address->setStreet ( $_data["custumer_address"] ?? '' );
			$address->setNeighborhood ( $_data["custumer_neighborhood"] ?? '' );
			$address->setNumber ( $_data["custumer_address_number"] ?? '' );
			$address->setComplement ( $_data["custumer_complement"] ?? '' );
			$address->setCity ( $_data["custumer_city"] ?? '' );
			$address->setState ( $_data["custumer_region"] ?? '' );
			$_ufs = $this->getListUf ();
			$selRegion = $_data["custumer_region"] ?? null;
			$address->setStateNm ( $selRegion !== null && isset($_ufs[$selRegion]) ? $_ufs[$selRegion] : '' );
		
			/*
			 * if ($address->getCep () != "" && ($address->getCity () == "" || $address->getState () == "")) { try { $addressDAO = AddressDAO::getInstance (); $db = DataBase::getInstance ( $addressDAO->loadDBConfigCep () ); $addressDAO->getAddressByZipCode ( $db, $address ); $db = null; } catch ( PDOException $e ) { $db = null; } }
			 */
		
			$address->setId ( $_data["custumer_address_id"] ?? null );
			$person->setAddress ( $address );
		
			$user = new User ();
		
			$person->setId ( $_data["custumer_person_id"] ?? null );
			$user->setPerson ( $person );
		
			$user->setLogin ( $_data["custumer_login"] ?? '' );
			$user->setPassword ( $_data["custumer_password"] ?? '' );
			$user->setStatus ( User::PEND );
		
			if(($_data["custumer_status"] ?? '')!=""){
				$user->setStatus ( $_data["custumer_status"] );
			}
		
			if(($_data["custumer_obs"] ?? '')!="")
				$user->setText ( $_data["custumer_obs"] );
		
			$client->setUser ( $user );
		
			return $client;
		}
	public function validateBean(Client $obj, $validateLogin = true) {
		$e = new ValidationException ( "Dados incompletos!" );
		
		$user = $obj->getUser ();
		
		if ($user != null)
			$person = $user->getPerson ();
		
		if ($person != null) {
			$address = $person->getAddress ();
			$phone = $person->getPhone ();
			
			if ($person->getType () != "") {
				if ($person->getType () == Person::PJ) {
					if ($person->getCNPJ () == "") {
						$e->add ( "CNPJ", "custumer_cnpj", "CNPJ vazio!" );
					} else if (! Validation::isCNPJ ( $person->getCNPJ () )) {
						$e->add ( "CNPJ", "custumer_cnpj", "CNPJ inválido!" );
					}
					
					if ($person->getName () == "") {
						$e->add ( "Razão Social", "custumer_rsoc", "Razão Social vazia!" );
					}
					
					if ($person->getContact () == "") {
						$e->add ( "Contato", "custumer_contact", "Contato vazio!" );
					}
					
					if ($person->getIe () == "") {
						$e->add ( "I.E.", "custumer_ie", "Inscrição Estadual vazio!" );
					}
				} elseif ($person->getType () == Person::PF) {
					if ($person->getCPF () == "") {
						$e->add ( "CPF", "custumer_cpf", "CPF vazio!" );
					} else if (! Validation::isCPF ( $person->getCPF () )) {
						$e->add ( "CPF", "custumer_cpf", "CPF inválido" );
					}
					
					if ($person->getName () == "") {
						$e->add ( "Nome", "custumer_name", "Nome vazio!" );
					}
				}
				
				if ($person->getEmail () == "") {
					$e->add ( "Email", "custumer_mail", "Email vazio!" );
				} elseif (! Validation::isEMail ( $person->getEmail () )) {
					$e->add ( "Email", "custumer_mail", "Email invalido!" );
				}
				
				if ($user != null) {
					if ($validateLogin) {
						if ($user->getLogin () == "") {
							$e->add ( "Login", "custumer_login", "Login vazio!" );
						} elseif (strlen ( $user->getLogin () ) < 5) {
							$e->add ( "Login", "custumer_login", "Login: Número de caracteres insuficiente! Mínimo = 5" );
						}
						
						if ($user->getPassword () == "") {
							$e->add ( "Senha", "custumer_password", "Senha vazia!" );
						} elseif (strlen ( $user->getPassword () ) < 5) {
							$e->add ( "Senha", "custumer_password", "Senha: Número de caracteres insuficiente! Mínimo = 5" );
						}
					}
					if ($phone != null) {
						if ($phone->getPhone () == "") {
							$e->add ( "Telefone", "custumer_phone", "Telefone vazio!" );
						}
						
						if ($address != null) {
							if ($address->getCep () == "" || strlen ( $address->getCep () ) < 8) {
								$e->add ( "CEP", "custumer_zipcode", "CEP vazio!" );
							}
							if ($address->getCity () == "") {
								$e->add ( "Cidade", "custumer_city", "Cidade inválida!" );
							}
							if ($address->getState () == "") {
								$e->add ( "UF", "custumer_region", "UF inválida!" );
							}
							
							if ($address->getNumber () == "") {
								$e->add ( "Número", "custumer_address_number", "Número vazio!" );
							}
						} else
							$e->add ( "CEP", "custumer_zipcode", "Necessário os dados obrigatórios" );
					} else
						$e->add ( "Telefone", "custumer_phone", "Necessário os dados obrigatórios" );
				} else
					$e->add ( "Login", "custumer_login", "Necessário os dados obrigatórios" );
			} else
				$e->add ( "Tipo de Pessoa", "type_person", "Necessário o tipo de Pessoa" );
		} else
			$e->add ( "Tipo de Pessoa", "type_person", "Necessário os dados obrigatórios" );
			
			// conclusão
		if ($e->size () > 0) {
			throw $e;
		}
	}
	private function sendMailConfirm($dbConfig, Client $custumer) {
		// Token aleatório (antes md5 da data, previsível) e URL base confiável (antes HTTP_HOST,
		// que permitia envenenar o link do e-mail com um cabeçalho Host falso)
		$md5 = $this->newToken ();
		$url = $this->getBaseUrl () . "/confirm_user/vua=" . $md5;
		
		$userDAO = UserDAO::getInstance ();
		$userDAO->registerLinkConfirm ( $dbConfig, $custumer->getUser (), $md5 );
		
		$mail = new Mail ();
		ob_start ();
		include_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . "/view/model_mail_cad_cli.php");
		$content = ob_get_contents ();
		ob_end_clean ();
		
		$person = $custumer->getUser ()->getPerson ();
		
		$subject = "Confirmação de usuário KADE Caminhões";
		$from = new EmailAddress ( 'contato@kadecaminhoes.com.br', 'Contato KADE Caminhões' );
		$to [] = new EmailAddress ( $person->getEmail (), $person->getName () );
		
		$mail->sendPHPMail ( $from, $to, $subject, $content, "text/html", array (), array (), true );
	}
	/**
	 * Define de quem é o cadastro a alterar sem confiar nos ids do formulário:
	 * usuário comum só altera o próprio (antes podia mandar o id de outra pessoa);
	 * endereço e telefone sempre vêm do cadastro salvo dessa pessoa.
	 */
	private function bindOwnedIds($db, Client $custumer) {
		$person = $custumer->getUser ()->getPerson ();
		$personId = User::isUserSuper () ? ( int ) $person->getId () : ( int ) User::getLogged ()->getPerson ()->getId ();

		$criteria = new Criteria ();
		$criteria->eq ( "cli.user_person_id", $personId );
		$current = ClientDAO::getInstance ()->getSingle ( $db, $criteria );
		if ($current == null || $current->getUser () == null)
			throw new Exception ( "Cadastro não encontrado." );

		$currentPerson = $current->getUser ()->getPerson ();
		$person->setId ( $personId );
		if ($person->getAddress () != null && $currentPerson->getAddress () != null)
			$person->getAddress ()->setId ( $currentPerson->getAddress ()->getId () );
		if ($person->getPhone () != null && $currentPerson->getPhone () != null)
			$person->getPhone ()->setId ( $currentPerson->getPhone ()->getId () );
	}
	public function onSaveInter() {
			ob_start();
			$output = array (
					"result" => 0,
					"message" => "",
					"validationException" => array () 
			);
		
			$_validationException = array ();
			$inTransaction = false;
		
			try {
			// Anti-bot: honeypot and minimum form fill time (optional fields in form)
			$hp = isset($_POST['hp_name']) ? trim($_POST['hp_name']) : '';
			$form_ts_post = isset($_POST['form_ts']) ? intval($_POST['form_ts']) : 0;
			if ($hp !== '') {
				throw new ValidationException('Suspeita de bot detectada');
			}
			if ($form_ts_post > 0 && (time() - $form_ts_post) < 3) {
				$excp = new ValidationException ( "Dados incompletos!" );
				$excp->add ( "Form", "form_ts", "Envio rápido demais. Tente novamente." );
				throw $excp;
			}
			$custumer = $this->getRequestBean ( $_POST );
		
			if(User::isUserSuper() && $custumer->getUser()->getPerson()->getId()!= User::getLogged()->getPerson()->getId()){
				if($_POST ["custumer_captcha"] == "")
					$_POST ["custumer_captcha"] = $_SESSION ["custumer_captcha"];
			}
		
			$this->validateBean ( $custumer, false );
			$excp = new ValidationException ( "Dados incompletos!" );
	
			// Validação Captcha (safe checks and expiry)
			$posted = isset($_POST["custumer_captcha"]) ? trim($_POST["custumer_captcha"]) : '';
			$sess = isset($_SESSION["custumer_captcha"]) ? $_SESSION["custumer_captcha"] : '';
			$sess_ts = isset($_SESSION["custumer_captcha_ts"]) ? intval($_SESSION["custumer_captcha_ts"]) : 0;
			if ($posted == '')
				$excp->add ( "Código", "custumer_captcha", "Código de segurança vazio!" );
			else {
				if ($sess_ts <= 0 || (time() - $sess_ts) > 300) {
					$excp->add ( "Código", "custumer_captcha", "Código expirado! Gere um novo código." );
				} else if (mb_strtoupper($sess, 'UTF-8') != mb_strtoupper($posted, 'UTF-8')) {
					$excp->add ( "Código", "custumer_captcha", "Código de segurança inválido!" );
				}
			}
				// conclusão
			if ($excp->size () > 0) {
				throw $excp;
			}
		
			$this->clientDAO = ClientDAO::getInstance ();

			$db = DataBase::getInstance ( $this->getDbConfig () );
			$inTransaction = $db->beginTransaction ();
			$this->bindOwnedIds ( $db, $custumer );
			$this->clientDAO->update ( $db, $custumer );

			$userLogged = User::getLogged ();

			if ($userLogged != null && $custumer->getUser ()->getPerson ()->getId () == $userLogged->getPerson ()->getId ()) {
				$criteria = new Criteria ();
				$criteria->eq ( "usr.person_id", $userLogged->getPerson ()->getId () );
				$userDAO = UserDAO::getInstance ();
				$userLogged = $userDAO->getSingle ( $db, $criteria );
				if ($userLogged == null || ($userLogged != null && $userLogged->getLogin () == ""))
					throw new Exception ( "Usuário inválido!!" );

				$userLogged->setPassword ( "" );
				$_SESSION ["user"] = serialize ( $userLogged );
			}
		
			$db->commit ();
			$db = null;
		
			$this->message->setType ( Message::SUCCESS );
			$this->message->setDesc ( "Cadastro atualizado com sucesso!" );
		} catch ( ValidationException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		} catch ( PDOException $e ) {
			
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			if (stripos ( $e->getMessage (), 'login_UNIQUE' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "Login" ),
						"id" => htmlentities ( "custumer_login" ),
						"message" => htmlentities ( "Login já existente! Informe um outro" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else if (stripos ( $e->getMessage (), 'cpf_UNIQUE' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "CPF" ),
						"id" => htmlentities ( "custumer_cpf" ),
						"message" => htmlentities ( "CPF já utilizado!" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else if (stripos ( $e->getMessage (), 'cnpj_UNIQUE' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "CNPJ" ),
						"id" => htmlentities ( "custumer_cnpj" ),
						"message" => htmlentities ( "CNPJ já utilizado!" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else {
				$this->message->setDesc ( $e->getMessage () );
			}
		} catch ( Exception $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		}
		
			$buffer = ob_get_clean();
			if (trim($buffer) !== '') {
				// Unexpected output (warnings/notices) detected - log and return a safe JSON error
				error_log("Unexpected output in onSaveInter(): " . $buffer);
				header ( "Content-type: application/json; charset=UTF-8" );
				echo json_encode (array("result"=>0,"message"=>"O sistema está indisponível no momento."));
				exit();
			}
		
			$output ["result"] = $this->message->getType ();
			$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );
			$output ["validationException"] = $_validationException;
		
			header ( "Content-type: application/json; charset=UTF-8" );
			echo json_encode ( $output );
			exit ();
		
		// $output ["result"] = $this->message->getType ();
		/*
		 * $output ["result"] = "error"; $output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>","Tente mais tarde" )); $output ["validationException"] = $_validationException; header ( "Content-type: text/plain" ); echo json_encode ( $output ); exit ();
		 */
	}
	public function getListUf() {
		$addressDAO = AddressDAO::getinstance ();
		$_result = array ();
		
		$_result = $addressDAO->getListUf ();
		
		return $_result;
	}
	public function onSaveWeb() {
		$output = array (
				"result" => 0,
				"message" => "",
				"validationException" => array () 
		);
		
		$_validationException = array ();
		$inTransaction = false;
		
		try {
			// Anti-bot: honeypot and minimum form fill time (optional hidden inputs in form)
			$hp = isset($_POST['hp_name']) ? trim($_POST['hp_name']) : '';
			$form_ts_post = isset($_POST['form_ts']) ? intval($_POST['form_ts']) : 0;
			if ($hp !== '') {
				throw new ValidationException('Suspeita de bot detectada');
			}
			if ($form_ts_post > 0 && (time() - $form_ts_post) < 3) {
				$excpTmp = new ValidationException ( "Dados incompletos!" );
				$excpTmp->add ( "Form", "form_ts", "Envio rápido demais. Tente novamente." );
				throw $excpTmp;
			}
			$custumer = $this->getRequestBean ( $_POST );
			$this->validateBean ( $custumer );
			$excp = new ValidationException ( "Dados incompletos!" );
			
			// Validação Captcha (safe checks and expiry)
			$posted = isset($_POST["custumer_captcha"]) ? trim($_POST["custumer_captcha"]) : '';
			$sess = isset($_SESSION["custumer_captcha"]) ? $_SESSION["custumer_captcha"] : '';
			$sess_ts = isset($_SESSION["custumer_captcha_ts"]) ? intval($_SESSION["custumer_captcha_ts"]) : 0;
			if ($posted == '')
				$excp->add ( "Código", "custumer_captcha", "Código de segurança vazio!" );
			else {
				if ($sess_ts <= 0 || (time() - $sess_ts) > 300) {
					$excp->add ( "Código", "custumer_captcha", "Código expirado! Gere um novo código." );
				} else if (mb_strtoupper($sess, 'UTF-8') != mb_strtoupper($posted, 'UTF-8')) {
					$excp->add ( "Código", "custumer_captcha", "Código de segurança inválido!" );
				}
			}
				// conclusão
			if ($excp->size () > 0) {
				throw $excp;
			}
			
			$this->clientDAO = ClientDAO::getInstance ();
			
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$inTransaction = $db->beginTransaction ();
			$this->clientDAO->insert ( $db, $custumer );
			
			$this->sendMailConfirm ( $db, $custumer );
			
			$db->commit ();
			$db = null;
			
			$person = $custumer->getUser ()->getPerson ();
			$this->message->setType ( Message::SUCCESS );
			$this->message->setDesc ( "Cadastro realizado com sucesso! Para confirmar o cadastro acesse o email " . $person->getEmail () . "  e clique no link solicitado  " );
		} catch ( ValidationException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		} catch ( PDOException $e ) {
			
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			if (stripos ( $e->getMessage (), 'login_UNIQUE' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "Login" ),
						"id" => htmlentities ( "custumer_login" ),
						"message" => htmlentities ( "Login já existente! Informe um outro" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else if (stripos ( $e->getMessage (), 'cpf_UNIQUE' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "CPF" ),
						"id" => htmlentities ( "custumer_cpf" ),
						"message" => htmlentities ( "CPF já utilizado!" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else if (stripos ( $e->getMessage (), 'cnpj_UNIQUE' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "CNPJ" ),
						"id" => htmlentities ( "custumer_cnpj" ),
						"message" => htmlentities ( "CNPJ já utilizado!" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else {
				$this->message->setDesc ( $e->getMessage () );
			}
		} catch ( Exception $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		}
		
		$buffer = ob_get_clean();
		if (trim($buffer) !== '') {
			// Unexpected output (warnings/notices) detected - log and return a safe JSON error
			error_log("Unexpected output in onSaveWeb(): " . $buffer);
			header ( "Content-type: application/json; charset=UTF-8" );
			echo json_encode (array("result"=>0,"message"=>"O sistema está indisponível no momento."));
			exit();
		}
		
		$output ["result"] = $this->message->getType ();
		$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );
		$output ["validationException"] = $_validationException;
		
		header ( "Content-type: application/json; charset=UTF-8" );
		echo json_encode ( $output );
		exit ();
		
		// $output ["result"] = $this->message->getType ();
		/*
		 * $output ["result"] = "error"; $output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>","Tente mais tarde" )); $output ["validationException"] = $_validationException; header ( "Content-type: text/plain" ); echo json_encode ( $output ); exit ();
		 */
	}
	public function getImageCaptcha() {
		$dir = dirname ( dirname ( dirname ( __FILE__ ) ) );
		// stronger captcha: 6 chars and timestamp for expiry
		$codigoCaptcha = substr ( str_shuffle ( "AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789" ), 0, 6 );
		
		$_SESSION ["custumer_captcha"] = $codigoCaptcha;
		$_SESSION ["custumer_captcha_ts"] = time();
		
		$imagemCaptcha = imagecreatefrompng ( $dir . "/images/fundocaptch.png" );
		
		$fonteCaptcha = imageloadfont ( $dir . "/images/anonymous.gdf" );
		
		$corCaptcha = imagecolorallocate ( $imagemCaptcha, 205, 0, 0 );
		
		imagestring ( $imagemCaptcha, $fonteCaptcha, 15, 5, $_SESSION ["custumer_captcha"], $corCaptcha );
		
		header ( "Content-type: image/png" );
		
		imagepng ( $imagemCaptcha );
		
		imagedestroy ( $imagemCaptcha );
	}
}

$ctrlCustReg = new CustumerRegCtrl ();
