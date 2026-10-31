<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTypeVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleContactVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTravelingVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTypeDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class VehicleRegCtrl extends AbstractCtrl {
	CONST HRS_EXPIRED = 16;
	
	private $vehicleTypeDAO = null;
	private $vehicleTravelingDAO = null;
	public function __construct() {
		parent::__construct ();
		$this->setAction ( $_REQUEST ["action"] ?? null );

		$this->guardAction(array(), AbstractCtrl::ACCESS_PUBLIC);
		
		
		switch ($this->getAction ()) {
			case "save" :
				$this->onSaveWeb ();
				break;
			case "saveSMS" :
				$this->onSaveSMS ();
				break;
			case "getCaptcha" :
				$this->getImageCaptcha ();
				break;
			default :
		}
	}
	public function onSaveSMS(){
		$_data		= array();
		$inTransaction = false;
		$db = null;
		
		
			
		try {
			
			$prop = Properties::getGroup("server_sms");		
			
			if(($_SERVER["HTTP_X_REAL_IP"] ?? "") != $prop["http_x_real_ip"])
				throw new Exception("IP inválido para tratamento de SMS");

			// Formato esperado: from=<55DDNNNNNNNNN> e body=<tipo>*<cep>
			$from = preg_replace('/\D/', "", (string) ($_GET["from"] ?? ""));
			$body = explode("*", (string) ($_GET["body"] ?? ""), 2);
			$_data["vehicle_phone"]   = "(".substr($from,1,2).") ".substr($from,3,11);
			$_data["vehicle_type"]    = $body[0];
			$_data["vehicle_zipcode"] = $body[1] ?? "";
			
			$vehicleTraveling = $this->getRequestBean ($_data);
			$vehicleTraveling->setSource(VehicleTraveling::SOURCE_SMS);
			
			$address     = $vehicleTraveling->getPersonContact()->getPerson()->getAddress();
			$vehicleType = $vehicleTraveling->getVehicleType ();
			
			$vehicleTraveling->getPersonContact()->getPerson()->setAddress($address);
			
			$this->validateBean ( $vehicleTraveling );


		
			$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
				
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$inTransaction = $db->beginTransaction ();
			
			

			$this->vehicleTravelingDAO->insert ( $db, $vehicleTraveling );
			$db->commit ();
			$db = null;
				
			$this->message->setType ( Message::SUCCESS );
			$this->message->setDesc ( "Registro " . $vehicleTraveling->getId () . " realizado com sucesso" );
		} catch ( ValidationException $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( json_encode($_validationException) );
			
		} catch ( PDOException $e ) {
				
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
				
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
		} catch ( Exception $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}	

	
		try {
			$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
			$this->vehicleTravelingDAO->registerLogSMS ( $this->getDbConfig (), $this->message, $_REQUEST,$_SERVER );
			exit();
		} catch ( Exception $e ) {
			error_log("registerLogSMS: " . $e->getMessage ());
			echo "Erro ao registrar log";
		}
	}
	public function getRequestBean(array $_data) {
		$person = null;
		$vehicleType = new VehicleType ();
		$phone = new Phone ();
		$address = new Address ();
		$vehicleTraveling = new VehicleTraveling ();
		$vehicleContact = new VehicleContact ();
		
		// Safely read incoming form keys to avoid undefined index warnings
		$type_person = isset($_data["type_person"]) ? ($_data["type_person"] ?? "") : '';
		switch ($type_person) {
			case Person::PF :
				$person = new PersonIndividual ();
				$person->setName ( ($_data["vehicle_name"] ?? "") );
				$person->setCpf ( ($_data["vehicle_cpf"] ?? "") );
				break;
			case Person::PJ :
				$person = new PersonEntity ();
				$person->setName ( ($_data["vehicle_rsoc"] ?? "") );
				$person->setContact ( ($_data["vehicle_contact"] ?? "") );
				$person->setCnpj ( ($_data["vehicle_cnpj"] ?? "") );
				
				break;
			default :
				$person = new Person ();
		}
		
		$vehicleType->setId ( isset($_data["vehicle_type"]) ? ($_data["vehicle_type"] ?? "") : null );
		
		$phone->setPhoneByMask ( isset($_data["vehicle_phone"]) ? ($_data["vehicle_phone"] ?? "") : '' );
		$person->setPhone ( $phone );
		
		$address->setCep ( isset($_data["vehicle_zipcode"]) ? ($_data["vehicle_zipcode"] ?? "") : '' );
		$address->setStreet ( isset($_data["vehicle_address"]) ? ($_data["vehicle_address"] ?? "") : '' );
		$address->setNeighborhood ( isset($_data["vehicle_neighborhood"]) ? ($_data["vehicle_neighborhood"] ?? "") : '' );
		$address->setNumber ( isset($_data["vehicle_address_number"]) ? ($_data["vehicle_address_number"] ?? "") : '' );
		$address->setComplement ( isset($_data["vehicle_complement"]) ? ($_data["vehicle_complement"] ?? "") : '' );
		$address->setCity ( isset($_data["vehicle_city"]) ? ($_data["vehicle_city"] ?? "") : '' );
		$address->setState ( isset($_data["vehicle_region"]) ? ($_data["vehicle_region"] ?? "") : '' );

		$_ufs = $this->getListUf();
		$regionKey = isset($_data["vehicle_region"]) ? ($_data["vehicle_region"] ?? "") : '';
		if ($regionKey !== '' && isset($_ufs[$regionKey])) {
			$address->setStateNm ( $_ufs[$regionKey] );
		} else {
			$address->setStateNm ( '' );
		}
		
		
		// Cidade e UF vêm do CEP, no servidor (o formulário só exibe e o SMS só traz o CEP).
		// Se a busca falhar, ficam as do formulário e a validação confere.
		if ($address->getCep () != "") {
			$resolved = new Address ();
			$resolved->setCep ( $address->getCep () );
			try {
				AddressDAO::getInstance ()->getAddressByZipCode ( $this->getDbConfig (), $resolved );
				$address->setCep ( $resolved->getCep () );
				$address->setCity ( $resolved->getCity () );
				$address->setState ( $resolved->getState () );
				$address->setStateNm ( $resolved->getStateNm () );
				if ($address->getStreet () == "")
					$address->setStreet ( $resolved->getStreet () );
				if ($address->getNeighborhood () == "")
					$address->setNeighborhood ( $resolved->getNeighborhood () );
			} catch ( Exception $e ) {
				error_log ( "VehicleRegCtrl CEP " . $address->getCep () . ": " . $e->getMessage () );
			}
		}
		
		$person->setAddress ( $address );
		
		$vehicleContact->setPerson ( $person );
		$vehicleContact->setPhoneContact ( $phone );
		$vehicleContact->setName ( $person->getName () );
		$vehicleContact->setVehicleTraveling ( $vehicleTraveling );
		
		$vehicleTraveling->setVehicleType ( $vehicleType );
		$vehicleTraveling->setAddress ( $address );
		$vehicleTraveling->setPersonContact ( $vehicleContact );
		$vehicleTraveling->setIp ( Util::getClientIP () );
		
		return $vehicleTraveling;
	}
	public function getListUf(){
		$addressDAO = AddressDAO::getinstance( );
		$_result = array();
	
	
		$_result = $addressDAO->getListUf( );
	
		return $_result;
	
	}
	public function getTypeVechicles() {
		$this->vehicleTypeDAO = VehicleTypeDAO::getInstance ();
		$_result = array ();
		
		try {
			$criteria = new Criteria();
			$criteria->eq ( "vtyp.enabled", "1" );
			$db = DataBase::getInstance ( $this->getDbConfig ());
			$_result = $this->vehicleTypeDAO->getArrayCache ( $db , $criteria);
			// $db->commit ();
			$db = null;
			
			return $_result;
		} catch ( PDOException $e ) {
			
			/*
			 * if ($db->inTransaction()) $db->rollback ();
			 */
			$db = null;
			
			/*
			 * $this->message->setType ( Message::ERR ); $this->message->setDesc ( $this->dbErrorMessage ( $e ) );
			 */
			
			return $_result;
		}
	}
	public function onSaveWeb() {
		// capture unexpected output (warnings/notices) so JSON response is not corrupted
		ob_start();
		$output = array (
				"result" => 0,
				"message" => "",
				"validationException" => array () 
		);
		$_validationException = array ();
		$db = null; // initialize to avoid undefined variable in catch blocks
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
			$vehicleTraveling = $this->getRequestBean ($_POST);
			$this->validateBean ( $vehicleTraveling );
			$excp = new ValidationException ( "Dados incompletos!" );
			
			//Validação Captcha (safe checks to avoid undefined index warnings)
			$vehicle_captcha_post = isset($_POST["vehicle_captcha"]) ? trim($_POST["vehicle_captcha"]) : '';
			$vehicle_captcha_sess = isset($_SESSION["vehicle_captcha"]) ? $_SESSION["vehicle_captcha"] : '';
			$vehicle_captcha_ts = isset($_SESSION["vehicle_captcha_ts"]) ? intval($_SESSION["vehicle_captcha_ts"]) : 0;
			$captcha_valid = false;
			if ($vehicle_captcha_post == "") {
				$excp->add ( "Código", "vehicle_captcha", "Código de segurança vazio!" );
			} else {
				// Check timestamp (5 minutes validity)
				if ($vehicle_captcha_ts <= 0 || (time() - $vehicle_captcha_ts) > 300) {
					$excp->add ( "Código", "vehicle_captcha", "Código expirado! Gere um novo código." );
				} else if (mb_strtoupper($vehicle_captcha_sess, 'UTF-8') != mb_strtoupper($vehicle_captcha_post, 'UTF-8')) {
					$excp->add ( "Código", "vehicle_captcha", "Código de segurança inválido!" );
				} else {
					$captcha_valid = true;
				}
			}
			
			// conclusão
			if ($excp->size () > 0) {
				throw $excp;
			}
		
			$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
			
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$inTransaction = $db->beginTransaction ();
			$this->vehicleTravelingDAO->insert ( $db, $vehicleTraveling );
			$db->commit ();
			$db = null;
			
			$this->message->setType ( Message::SUCCESS );
			$this->message->setDesc ( "Registro " . $vehicleTraveling->getId () . " realizado com sucesso" );
		} catch ( ValidationException $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		} catch ( PDOException $e ) {
			
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			if(stripos ( $e->getMessage(), 'cpf_UNIQUE' ) !== false)
			{
				$newItem = array(
	    			"name" => htmlentities("CPF"),
	    			"id" => htmlentities("vehicle_cpf"),
	    			"message" => htmlentities("CPF já existente! Informe um outro"),
	    		);
	    		$_validationException[] = $newItem;
				
			
				$this->message->setDesc ( "Dados inválidos!" );
			}else if(stripos ( $e->getMessage(), 'cnpj_UNIQUE' ) !== false)
			{
				$newItem = array(
	    			"name" => htmlentities("CNPJ"),
	    			"id" => htmlentities("vehicle__cnpj"),
	    			"message" => htmlentities("CNPJ já existente! Informe um outro"),
	    		);
	    		$_validationException[] = $newItem;
				
			
				$this->message->setDesc ( "Dados inválidos!" );
			}else{
				$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
			}
		} catch ( Exception $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
		
		$buffer = ob_get_clean();
		if (trim((string) $buffer) !== '') {
			// Unexpected output (warnings/notices) detected - log and return a safe JSON error
			error_log("Unexpected output in onSaveWeb(): " . $buffer);
			header ( "Content-type: application/json; charset=UTF-8" );
			echo json_encode (array("result"=>0,"message"=>"O sistema está indisponível no momento."));
			exit();
		}
		
		$output ["result"] = $this->message->getType ();
		$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>",$this->message->getDesc () ));
		$output ["validationException"] = $_validationException;
		
		header ( "Content-type: application/json; charset=UTF-8" );
		echo json_encode ( $output );
		exit ();
	}
	public function validateBean(VehicleTraveling $obj) {
		$e = new ValidationException ( "Dados incompletos!" );
		
		$vehicleContact = $obj->getPersonContact ();
		$address = $obj->getAddress ();
		$phone = $vehicleContact->getPhoneContact ();
		$person = $vehicleContact->getPerson ();
		$vehicleType = $obj->getVehicleType ();
		
		if ($person->getType () == Person::PJ) {
			if ($person->getCNPJ () == "") {
				$e->add ( "CNPJ", "vehicle_cnpj", "CNPJ vazio!" );
			} else if (! Validation::isCNPJ ( $person->getCNPJ () )) {
				$e->add ( "CNPJ", "vehicle_cnpj", "CNPJ inválido!" );
			}
			
			if ($person->getName () == "") {
				$e->add ( "Razão Social", "vehicle_rsoc", "Razão Social vazia!" );
			}
			
			if ($person->getContact () == "") {
				$e->add ( "Contato", "vehicle_contact", "Contato vazio!" );
			}
		} elseif ($person->getType () == Person::PF) {
			if ($person->getCPF () == "") {
				$e->add ( "CPF", "vehicle_cpf", "CPF vazio!" );
			} else if (! Validation::isCPF ( $person->getCPF () )) {
				$e->add ( "CPF", "vehicle_cpf", "CPF inválido" );
			}
			
			if ($person->getName () == "") {
				$e->add ( "Nome", "vehicle_name", "Nome vazio!" );
			}
		}
		
		if ($vehicleType->getId () <= 0) {
			$e->add ( "Tipo de Veículo", "vehicle_type", "Tipo de Veículo vazio!" );
		}
		
		$_vechicleTypes = $this->getTypeVechicles();
		$exist = false;
		foreach ($_vechicleTypes AS $vechicleTypesLoop){
			if($vehicleType->getId () == $vechicleTypesLoop->getId()){
				$exist = true;
				break;
			}
		}
		if(!$exist){
			$e->add ( "Tipo de Veículo", "vehicle_type", "Tipo de Veículo ".$vehicleType->getId ()." inválido!" );
		}
		
		if ($phone->getPhone () == "") {
			$e->add ( "Telefone", "vehicle_phone", "Telefone vazio!" );
		}
		
		if ($address->getCep () == "") {
			$e->add ( "CEP", "vehicle_zipcode", "CEP vazio!" );
		}
		
		$cepDigits = preg_replace('/\D/','', (string)$address->getCep());
		if(strlen($cepDigits) != 8){
			$e->add ( "CEP", "vehicle_zipcode", "CEP inválido");
		} else {
			// normalize stored cep to digits-only for downstream lookup
			$address->setCep($cepDigits);
		}
		
		// Sem cidade e UF o veículo não aparece no mapa: o SMS também é recusado (antes era
		// gravado sem localização quando o CEP não era encontrado)
		if ($obj->getSource() == VehicleTraveling::SOURCE_SMS) {
			if (strlen($cepDigits) == 8 && ($address->getCity () == "" || $address->getState () == ""))
				$e->add ( "CEP", "vehicle_zipcode", "CEP não encontrado!" );
		} else {
			if ($address->getCity () == "") {
				$e->add ( "Cidade", "vehicle_city", "Cidade inválida!" );
			}
			if ($address->getState () == "") {
				$e->add ( "UF", "vehicle_region", "UF inválida!" );
			}
		}
		// conclusão
		if ($e->size () > 0) {
			
			throw $e;
		}
	}
	public function getImageCaptcha() {
		$dir = dirname ( dirname ( dirname ( __FILE__ ) ) );
		// stronger captcha: 6 chars and timestamp for expiry
		$codigoCaptcha = substr(str_shuffle("AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789"),0,6);
		
		$_SESSION ["vehicle_captcha"] = $codigoCaptcha;
		$_SESSION ["vehicle_captcha_ts"] = time();
		
		$imagemCaptcha = imagecreatefrompng ( $dir . "/images/fundocaptch.png" );
		
		$fonteCaptcha = imageloadfont ( $dir . "/images/anonymous.gdf" );
		
		$corCaptcha = imagecolorallocate ( $imagemCaptcha, 205, 0, 0 );
		
		
		imagestring ( $imagemCaptcha, $fonteCaptcha, 15, 5, $_SESSION ["vehicle_captcha"], $corCaptcha );
		
		header ( "Content-type: image/png" );
		
		imagepng ( $imagemCaptcha );
		
		imagedestroy ( $imagemCaptcha );
	}
}

$ctrlVehReg = new VehicleRegCtrl ();
?>
