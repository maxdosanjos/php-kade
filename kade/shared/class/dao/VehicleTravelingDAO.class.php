<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Message.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTypeVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleContactVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTravelingUserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleContactDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/UserDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTypeDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingUserDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonEntityDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonIndividualDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonDAO.class.php");

class VehicleTravelingDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}	
	public function countLogSMS($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	COUNT(log.id) AS total
				  FROM 	log_sms log
			";
		if ($criteria != null) {
			$sql .= $criteria->getWHERE ();
			$sql .= $criteria->getORDER ();
			$sql .= $criteria->getLIMIT ();
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );
		
		$result = $db->query ( $sql );
		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		$objDAO = $result->fetchObject ();
		
		return $objDAO->total;
	}
	public function getArrayLogSMSgetArray($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	log.id
					  , log.from
					  , log.body
					  , log.ip_trat
					  , log.url_request
					  , log.type_log
					  , log.log
					  , log.date_hr
				  FROM 	log_sms log
			";
		if ($criteria != null) {
			$sql .= $criteria->getWHERE ();
			$sql .= $criteria->getORDER ();
			$sql .= $criteria->getLIMIT ();
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );
		
		$_logs = array ();
		$result = $db->query ( $sql );
		while ( $objDAO = $result->fetchObject () ) {
			if ($objDAO->id == null)
				continue;			
			$_logs [] = $objDAO;
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $_logs;
	}
	public function registerLogSMS($dbConfig,Message $message,$request,$server){
		$json = json_decode($message->getDesc ());
		if($json[0]->message == ""){
			$json[0]->message = $message->getDesc ();			
		}else{
			$json[0]->message = html_entity_decode(iconv (  "UTF-8","UTF-8", $json[0]->message));
		}
		
		$sql = "INSERT INTO `log_sms`
					(   `from`
					  , `body`
					  , `ip_trat`
					  , `url_request`
					  , `type_log`
					  , `log`
					 )
				VALUES
					(
					   '" . $request["from"] . "'
					 , '" . addslashes($request["body"]) . "'
					 , '" . $server["HTTP_X_REAL_IP"] . "'
					 , '" . $server["SCRIPT_URI"]. "'
					 , '" . $message->getType () . "'
					 , '" . addslashes($json[0]->message) . "'
					 )";
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
			
		
		
		$out = $db->exec ( $sql );

		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		if($out == 0)
			throw new PDOException("Erro ao criar log!");
		
	}
	public function setStatus($dbConfig, VehicleTraveling $obj){		
		$user_ultilized = "";
		$inTransaction = false;
		
		if($dbConfig instanceof DBConfig){
			$db        	   =  DataBase::getInstance($dbConfig);
			$inTransaction = $db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
			
		if($obj->getStatus() == VehicleTraveling::UTILIZED){
			$user_ultilized = ", `user_person_id_ut` = '".User::getLogged()->getPerson()->getId()."'";
		}
		
		$sql = "UPDATE `vehicle_traveling` 
				   SET `status` = '".$obj->getStatus()."'
						".$user_ultilized."
				 WHERE `id` = '".$obj->getId()."'";
		
			
			

		//echo $sql;
		try {
		
			$out = $db->exec($sql);		

			if($obj->getStatus() == VehicleTraveling::UTILIZED || $obj->getStatus() == VehicleTraveling::CANCEL || $obj->getStatus() == VehicleTraveling::EXPIRED){
				$sql = 'call remove_idx_traveling_city('.$obj->getAddress()->getId().');';
				$out = $db->exec($sql);
			}
			if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
			}			
		} catch ( PDOException $e ) {
			if($dbConfig instanceof DBConfig){
				if ($db!=null && $inTransaction)
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	public function insert($dbConfig, VehicleTraveling $obj) {
		$inTransaction = false;
		
		if($dbConfig instanceof DBConfig){
			$db        	   =  DataBase::getInstance($dbConfig);
			$inTransaction = $db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
		
		$address		= $obj->getAddress();
		$vehicleContact = $obj->getPersonContact();
		
		try {			
			
			// Criar Endereço
			$addressDAO = AddressDAO::getInstance ();
			$addressDAO->insert ( $db, $address );
			
			$obj->setAddress($address);
			$obj->setPersonContact($vehicleContact);
			
			//Criar Viagem
			$sql = "INSERT INTO `vehicle_traveling` 
				(   `vehicle_type_id`
				  , `date_hr_proc`
				  , `source`
				  , `address_id`
				  , `ip`
				 )
			VALUES 
				(
				   '".$obj->getVehicleType()->getId()."'
				 , '".$obj->getDateHrProc()->format("Y-m-d H:i:s")."'
				 , '".$obj->getSource()."'				 
				 , '".$obj->getAddress()->getId()."'
				 , '".$obj->getIp()."'
				 )";
				 
			$out = $db->exec($sql);
			$id = $db->lastInsertId();
			
			$obj->setId($id);			
			
			if($id <= 0)
				throw new PDOException("Erro ao registrar Veículo!");			
				
			//Criar pessoa de contato
			$vehicleContact->setVehicleTraveling($obj);
			$vehicleContactDAO = VehicleContactDAO::getInstance ();
			$vehicleContactDAO->insert($db, $vehicleContact);
			
			//Indices
			$sql = 'call add_idx_traveling_city('.$obj->getAddress()->getId().');';
			$out = $db->exec($sql);
			if($out == 0)
				throw new PDOException("Erro ao registrar Veículo!");
			
			
			if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
			}
			
		} catch ( PDOException $e ) {
			if($dbConfig instanceof DBConfig){
				if ($db!=null && $inTransaction)
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	
	
	
	public function getSingle($dbConfig, Criteria $criteria){	
		$sql = "
			    SELECT 	  vt.id
					   	, vt.vehicle_type_id
						, vt.date_hr_proc
						, vt.source
						, vt.address_id
						, vt.ip
						, vt.status
						, vt.address_id
						, vt.user_person_id_ut
						, vtyp.descr AS vehicle_type_descr
						, ad.cep
						, ad.city
						, ad.state
						, vc.phone_contact_id
						, vc.name
						, vc.person_id
						, per.type
						, ph.ddi 
						, ph.ddd
						, ph.phone
						, vusr.date_hr_used
				  FROM  vehicle_traveling vt 
			INNER JOIN  vehicle_type vtyp 
					ON  vtyp.id = vt.vehicle_type_id 
			INNER JOIN  address ad 
					ON  ad.id = vt.address_id 
			INNER JOIN  vehicle_contact vc 
					ON  vc.vehicle_traveling_id = vt.id 
			INNER JOIN  phone ph 
					ON  ph.id = vc.phone_contact_id 
			INNER JOIN  person per
					ON  per.id 		  = vc.person_id
			LEFT  JOIN  vehicle_traveling_user vusr
				    ON  vusr.vehicle_traveling_id = vt.id
				   AND  vusr.user_person_id		  = vt.user_person_id_ut
				";		
		if ($criteria != null) {
			$sql .= $criteria->getWHERE ();
			$sql .= $criteria->getORDER ();
			$sql .= $criteria->getLIMIT ();
		}	

		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );		
		
		$result = $db->query ( $sql );
		$objDAO = $result->fetchObject();
		if($objDAO->id==null)
			throw new PDOException ( "Registro não encontrado" );
			
		$person = null;
		
		$vehicleType = new VehicleType ();
		$phone = new Phone ();
		$address = new Address ();
		$vehicleTraveling = new VehicleTraveling ();
		$vehicleContact = new VehicleContact ();
		
		
		$critPers 	   = new Criteria();
		$personTypeDAO = null;
		
		if ($objDAO->type == Person::PJ){
			$personTypeDAO = PersonEntityDAO::getInstance ();
			$critPers->eq("pj.person_id",$objDAO->person_id);
		}elseif ($objDAO->type == Person::PF){
			$personTypeDAO = PersonIndividualDAO::getInstance ();
			$critPers->eq("pf.person_id",$objDAO->person_id);
		}else{
			$personTypeDAO = PersonDAO::getInstance ();
			$critPers->eq("p.id",$objDAO->person_id);
		}
		
		if($personTypeDAO!=null)
			$person = $personTypeDAO->getSingle ( $db, $critPers );
		
		if($person==null){
			throw new PDOException("Contato não encontrado #sem_contato");
		}
		
		
		$vehicleType->setId ( $objDAO->vehicle_type_id );
		$vehicleType->setDescr ( $objDAO->vehicle_type_descr );
		
		$phone->setId ( $objDAO->phone_contact_id );
		$phone->setDdi ( $objDAO->ddi );
		$phone->setDdd( $objDAO->ddd );
		$phone->setPhone( $objDAO->phone );
		//$person->setPhone ( $phone );
		
		$address->setId ( $objDAO->address_id );
		$address->setCep ( $objDAO->cep );
		//$address->setStreet ( $_data ["vehicle_address"] );
		//$address->setNumber ( $_data ["vehicle_address_number"] );
		//$address->setComplement ( $_data ["vehicle_complement"] );
		$address->setCity ( $objDAO->city);
		$address->setState ( $objDAO->state );
		//$address->setStateNm ( $_data ["vehicle_region_nm"] );
		
		//$person->setAddress ( $address );
		
		$vehicleContact->setPerson ( $person );
		$vehicleContact->setPhoneContact ( $phone );
		$vehicleContact->setName ( $objDAO->name );
		$vehicleContact->setVehicleTraveling ( $vehicleTraveling );
		
		$vehicleTraveling->setVehicleType ( $vehicleType );
		$vehicleTraveling->setAddress ( $address );
		$vehicleTraveling->setPersonContact ( $vehicleContact );
		
		$vehicleTraveling->setId ( $objDAO->id );
		$vehicleTraveling->setIp ( $objDAO->ip);
		$vehicleTraveling->setDateHrProc ( new DateTimeCustom($objDAO->date_hr_proc) );
		$vehicleTraveling->setStatus ( $objDAO->status );
		$vehicleTraveling->setSource ( $objDAO->source );
		
		if($objDAO->user_person_id_ut!=null){
			$vehTravUser = new VehicleTravelingUser ();
			$vehTravUser->setVehicleTraveling( $vehicleTraveling );
			
			$critUser = new Criteria();
			$critUser->eq("usr.person_id",$objDAO->user_person_id_ut);
			$userDAO = UserDAO::getInstance ();
			$user = $userDAO->getSingle ( $db, $critUser );	
			if($user==null || $user->getLogin() == ""){
				throw new PDOException("Utilizador não encontrado!");
			}
			
			$vehTravUser->setUser( $user );		
			$vehTravUser->setDateHrUsed( new DateTimeCustom( $objDAO->date_hr_used ) );
			
			$vehicleTraveling->setUserUtilized( $vehTravUser );
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		return $vehicleTraveling;
	}
	
	public function count($dbConfig, Criteria $criteria){
		$sql = "SELECT count(vt.id) AS 'total'
      			  FROM 	vehicle_traveling vt
			INNER JOIN  address ad
					ON  ad.id = vt.address_id
			INNER JOIN  vehicle_contact vc
					ON  vc.vehicle_traveling_id = vt.id
			INNER JOIN	phone ph
					ON  ph.id = vc.phone_contact_id
				 ";
		if(User::isUserSuper()){
			$sql.= "LEFT  JOIN  vehicle_traveling_user vusr
					   		    ON  vusr.vehicle_traveling_id = vt.id
					   		   AND  vusr.user_person_id		  = vt.user_person_id_ut
					";
		}
		$sql 	 .= $criteria->toString();
	
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
	
		$result    =  $db->query($sql);
	
		if($dbConfig instanceof DBConfig)
			$db		   = null;
		$objDAO   = $result->fetchObject();
	
		return $objDAO->total;
	
	}
	public function getArray($dbConfig, Criteria $criteria){	
		$sql = "
			    SELECT 	  vt.id
					   	, vt.vehicle_type_id
						, vt.date_hr_proc
						, vt.source
						, vt.address_id
						, vt.ip
						, vt.address_id
						, vt.status
						, vt.user_person_id_ut
						, ad.cep
						, ad.city
						, ad.state
						, vc.phone_contact_id
						, vc.name
						, ph.ddi 
						, ph.ddd
						, ph.phone
			";
		if(User::isUserSuper()){
			$sql.="	, vusr.date_hr_used ";
		}
		
		$sql.="
				  FROM 	vehicle_traveling vt
			INNER JOIN  address ad
					ON  ad.id = vt.address_id
			INNER JOIN  vehicle_contact vc
					ON  vc.vehicle_traveling_id = vt.id
			INNER JOIN	phone ph
					ON  ph.id = vc.phone_contact_id
			 ";
			if(User::isUserSuper()){
				$sql.= "LEFT  JOIN  vehicle_traveling_user vusr
					   		    ON  vusr.vehicle_traveling_id = vt.id
					   		   AND  vusr.user_person_id		  = vt.user_person_id_ut
					";		
			}
		if ($criteria != null) {
			$sql .= $criteria->getWHERE ();
			$sql .= $criteria->getORDER ();
			$sql .= $criteria->getLIMIT ();
		}	
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );
		
		
		$_traveling = array();
		$_travTypeId = array();
		$result = $db->query ( $sql );
		while($objDAO = $result->fetchObject())
		{
			if($objDAO->id==null)
				continue;
				
			$person = null;
			
			$vehicleType = new VehicleType ();
			$phone = new Phone ();
			$address = new Address ();
			$vehicleTraveling = new VehicleTraveling ();
			$vehicleContact = new VehicleContact ();
			
			/*switch ($_data ["type_person"]) {
				case Person::PF :
					$person = new PersonIndividual ();
					$person->setName ( $_data ["vehicle_name"] );
					$person->setCpf ( $_data ["vehicle_cpf"] );
					break;
				case Person::PJ :
					$person = new PersonEntity ();
					$person->setName ( $_data ["vehicle_rsoc"] );
					$person->setContact ( $_data ["vehicle_contact"] );
					$person->setCnpj ( $_data ["vehicle_cnpj"] );
					
					break;
				default :
					$person = new Person ();
			}*/
			
			$vehicleType->setId ( $objDAO->vehicle_type_id );
			if(!in_array($objDAO->vehicle_type_id,$_travTypeId))
				$_travTypeId[] = $objDAO->vehicle_type_id;
			
			$phone->setId ( $objDAO->phone_contact_id );
			$phone->setDdi ( $objDAO->ddi );
			$phone->setDdd( $objDAO->ddd );
			$phone->setPhone( $objDAO->phone );
			//$person->setPhone ( $phone );
			
			$address->setId ( $objDAO->address_id );
			$address->setCep ( $objDAO->cep );
			//$address->setStreet ( $_data ["vehicle_address"] );
			//$address->setNumber ( $_data ["vehicle_address_number"] );
			//$address->setComplement ( $_data ["vehicle_complement"] );
			$address->setCity ( $objDAO->city);
			$address->setState ( $objDAO->state );
			//$address->setStateNm ( $_data ["vehicle_region_nm"] );
			
			//$person->setAddress ( $address );
			
			//$vehicleContact->setPerson ( $person );
			$vehicleContact->setPhoneContact ( $phone );
			$vehicleContact->setName ( $objDAO->name );
			$vehicleContact->setVehicleTraveling ( $vehicleTraveling );
			
			$vehicleTraveling->setVehicleType ( $vehicleType );
			$vehicleTraveling->setAddress ( $address );
			$vehicleTraveling->setPersonContact ( $vehicleContact );
			
			$vehicleTraveling->setId ( $objDAO->id );
			$vehicleTraveling->setIp ( $objDAO->ip);
			$vehicleTraveling->setDateHrProc ( new DateTimeCustom($objDAO->date_hr_proc) );
			$vehicleTraveling->setStatus ( $objDAO->status );
			$vehicleTraveling->setSource ( $objDAO->source );
			
			if($objDAO->user_person_id_ut!=null){
				$vehTravUser = new VehicleTravelingUser ();
				$vehTravUser->setVehicleTraveling( $vehicleTraveling );
					
				$critUser = new Criteria();
				$critUser->eq("usr.person_id",$objDAO->user_person_id_ut);
				$userDAO = UserDAO::getInstance ();
				$user = $userDAO->getSingle ( $db, $critUser );
				if($user==null || $user->getLogin() == ""){
					throw new PDOException("Utilizador não encontrado!");
				}
					
				$vehTravUser->setUser( $user );
				$vehTravUser->setDateHrUsed( new DateTimeCustom( $objDAO->date_hr_used ) );
					
				$vehicleTraveling->setUserUtilized( $vehTravUser );
			}
			
			$_traveling[] = $vehicleTraveling;
			
		}
		//Nome dos tipos
		$lenTrav = sizeof($_traveling);
		$lenTyp  = sizeof($_travTypeId);
		if( $lenTrav > 0 && $lenTyp > 0){
			$criteria = new Criteria();
			$criteria->in ( "vtyp.id", $_travTypeId );
			$vehicleTypeDAO = VehicleTypeDAO::getInstance ();
			$_types = $vehicleTypeDAO->getArray( $db , $criteria);
			
			$i = 0;
			while($i < $lenTyp){
				$vehType = $_types[$i];
				
				$j = 0;
				while($j < $lenTrav){
					$trav = $_traveling[$j];
					if($trav->getVehicleType()->getId() == $vehType->getId()){
						$trav->getVehicleType()->setDescr($vehType->getDescr());
					}
					$j++;
				}
				
				$i++;
			}
			
		}
		if ($dbConfig instanceof DBConfig)
			$db = null;
			
		return $_traveling;
	}
}
?>