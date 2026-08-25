<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Pagination.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTypeVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleContactVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTravelingVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTypeDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingUserDAO.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class VehicleQueryCtrl extends AbstractCtrl {
	const SEARCH_BY_ULT 		 = 'SEARCH_BY_ULT';
	const REG_UTILIZED			 = 'REG_UTILIZED';
	const REG_CREATED			 = 'REG_CREATED';
	
	private $vehicleTypeDAO      = null;
	private $vehicleTravelingDAO = null;
	private $vehiclesTraveling   = null;
	/*** Parametros para busca ***/
	private $dtHrInit			 = null;
	private $dtHrEnd			 = null; 
	private $vehTravParam  		 = null;
	/*** Parametros para busca ***/
	private $orderType  		 = null; //tipo da ordenação
	private $orderColumn 		 = null; //coluna a ser ordenada
	private $pagination			 = null;
	
	private $typeView 			 = null;
	
	public function __construct() {
		parent::__construct ();
		$this->initCtrlParam( );
		$this->setAction ( $_REQUEST ["action"] ?? null );
		
		if($this->typeView == ""){
			$this->typeView = VehicleQueryCtrl::SEARCH_BY_ULT;
		}
		
		$this->setPagination(new Pagination(0));//inicia com o numero total de registros
		
		switch ($this->getAction ()) {
			case "searchArray" :
				$this->onSearchArray();
				break;
			case "viewDetail":
				$this->onViewDetail();
				break;
			case "setStsUtilized":
				$this->setStsUtilized();
				break;
			case "setStsCancel":
				$this->setStsCancel();
				break;
			case "exportCSV":
				$this->exportCSV( );
				break;
			default :
		}
	}
	public function exportCSV(){
		try {
			$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();				
			$this->vehTravParam = $this->getRequestBean ($_POST);				
			$criteria  = $this->getCriteriaFilterDefault( );					
			
			if($this->vehTravParam->getStatus ( ) != "")
				$criteria->eq("vt.status",$this->vehTravParam->getStatus ( ));				
			
			$criteria->addOrder("vt.id","DESC");	 
			$this->vehiclesTraveling = $this->vehicleTravelingDAO->getArray($this->getDbConfig (),$criteria);
			$length 		  = count($this->vehiclesTraveling);
			if($length<=0){
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( 'Nenhum resultado encontrado' );
			}else{
				
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=resultado_busca_kade.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo "Registro;Criado em;Tipo Veículo;CEP;Cidade;UF;Telefone;Contato;Status;Origem;Utilizado em;Usuário Utilizador\r";
				$i = 0;
				while($i < $length){
					$vehicleTrav 	= $this->vehiclesTraveling[$i];
					$vehicleType 	= $vehicleTrav->getVehicleType ( );
					$address 	 	= $vehicleTrav->getAddress ( );
					$vehicleContact = $vehicleTrav->getPersonContact ( );
					$phone 			= $vehicleContact->getPhoneContact( );
					$vehTravUser    = $vehicleTrav->getUserUtilized();
										
					echo $vehicleTrav->getId().";";
					echo $vehicleTrav->getDateHrProc()->format("d/m/Y H:i").";";
					echo $vehicleType->getId()." - ".$vehicleType->getDescr().";";
					echo $address->getCepMask().";";
					echo $address->getCity().";";
					echo $address->getState().";";
					echo $phone->getPhoneMask().";";
					echo $vehicleContact->getName().";";					
					echo $vehicleTrav->getDescStatus().";";
					echo $vehicleTrav->getDescSource();
					if($vehTravUser!=null)
					{
						echo ";".$vehTravUser->getDateHrUsed()->format("d/m/Y H:i").";";
						echo $vehTravUser->getUser()->getLogin()." - ".$vehTravUser->getUser()->getPerson ()->getName().";";
					}
					echo "\r";		
					$i++;
				}
			}		
				
		} catch ( PDOException $e ) {
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		} catch ( Exception $e ) {
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		}
		
		if($this->message->getType ( ) == Message::ERR){
			echo "<h2>".$this->message->getDesc ()."</h2>";
		}
	}
	public function setTypeView($typeView){
		$this->typeView = $typeView;
	}
	public function getTypeView(){
		return $this->typeView;
	}
	public function getVehTravParam(){
		return $this->vehTravParam;
	}
	public function getVehiclesTraveling(){
		return $this->vehiclesTraveling;
	}
	public function getPagination()
    {
    	return $this->pagination;	
    }
    /**
     * Setter: pagination
     * @param Pagination
     * @return void
     */
    public function setPagination(Pagination $pagination){
    	$this->pagination = $pagination;
    }
    
    /**
     * @method String Retorna a imagem de acordo com a ordenação
     */
    public function getImgOrder($columnBD){
		if($this->getOrderColumn()==trim($columnBD))
		{
			if($this->getOrderType()== "DESC")
				return "&#x25BC;";  
			return "&#x25B2;"; 	
		}
		return "";
	}
	/**
	 * Getter : orderColumn
	 * @return String
	 */
	public function getOrderColumn()
	{
		return $this->orderColumn;
	}
	/**
	 * Getter: orderType
	 * @return String
	 */
	public function getOrderType()
	{
		return $this->orderType;
	}
	/**
	 * Setter: orderColumn
	 * @param String
	 * @return void
	 */
	public function setOrderColumn($orderColumn="vt.id"){
		$this->orderColumn = trim($orderColumn);
	}
	/**
	 * Setter: orderType
	 * @param String
	 * @return void
	 */
	public function setOrderType($orderType="DESC"){
		$this->orderType = strtoupper(trim($orderType));
	}
	/*** Parametros para busca ***/
    public function getDtHrInit(){
    	return $this->dtHrInit;
    }
    public function setDtHrInit(DateTime $dtHrInit){
    	$this->dtHrInit = $dtHrInit;
    }
    public function getDtHrEnd(){
    	return $this->dtHrEnd;
    }
    public function setDtHrEnd(DateTime $dtHrEnd){
    	$this->dtHrEnd = $dtHrEnd;
    }      
    public function setStsCancel(){
			$inTransaction = false;
    	try {
    		$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();    			
    		$this->vehTravParam = $this->getRequestBean ($_POST);
    		if($this->vehTravParam->getId() > 0){
				
				$db = DataBase::getInstance ( $this->getDbConfig () );
				$inTransaction = $db->beginTransaction ();
				
    			$this->vehTravParam->setStatus( VehicleTraveling::CANCEL );
    			$this->vehicleTravelingDAO->setStatus($db,$this->vehTravParam);
				
				$vehicleTravelingUser = new VehicleTravelingUser();
				$vehicleTravelingUser->setUser(User::getLogged());
				$vehicleTravelingUser->setVehicleTraveling($this->vehTravParam);
			
			
				// Criar Utilizador da informação
				$vehicleTravUserDAO = VehicleTravelingUserDAO::getInstance ();
				$vehicleTravUserDAO->insert ( $db, $vehicleTravelingUser );
				
				$db->commit ();
				$db = null;
    			
    			$this->message->setType ( Message::SUCCESS );
    			$this->message->setDesc ( "Status alterado com sucesso" );
    			
    		}else{
    			throw new Exception("Registro não informado");
    		}
    	} catch ( PDOException $e ) {
    		if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
    		$this->vehTravParam = null;
    		$this->message->setType ( Message::ERR );
    		$this->message->setDesc ( $e->getMessage () );
    	} catch ( Exception $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
    		$this->vehTravParam = null;
    		$this->message->setType ( Message::ERR );
    		$this->message->setDesc ( $e->getMessage () );
    	}
    	
    	$vehJsonArr = array();
    	$vehJsonArr ["result"]  = $this->message->getType();
    	$vehJsonArr ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>",$this->message->getDesc() ));    	
    	
    	echo json_encode($vehJsonArr);
	}
	public function setStsUtilized(){
		$inTransaction = false;
    	try {
    		$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();    			
    		$this->vehTravParam = $this->getRequestBean ($_POST);
    		if($this->vehTravParam->getId() > 0){
				
				$db = DataBase::getInstance ( $this->getDbConfig () );
				$inTransaction = $db->beginTransaction ();
				
				$vehicleTravelingUser = new VehicleTravelingUser();
				$vehicleTravelingUser->setUser(User::getLogged());
				$vehicleTravelingUser->setVehicleTraveling($this->vehTravParam);
					
				
				// Criar Utilizador da informação
				$vehicleTravUserDAO = VehicleTravelingUserDAO::getInstance ();
				$vehicleTravUserDAO->insert ( $db, $vehicleTravelingUser );
				
				$this->vehTravParam->setStatus( VehicleTraveling::UTILIZED );
    			$this->vehicleTravelingDAO->setStatus($db,$this->vehTravParam);
				
				$db->commit ();
				$db = null;
    			
    			$this->message->setType ( Message::SUCCESS );
    			$this->message->setDesc ( "Status alterado com sucesso" );
    			
    		}else{
    			throw new Exception("Registro não informado");
    		}
    	} catch ( PDOException $e ) {
    		if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
    		$this->vehTravParam = null;
    		$this->message->setType ( Message::ERR );
    		$this->message->setDesc ( $e->getMessage () );
    	} catch ( Exception $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
    		$this->vehTravParam = null;
    		$this->message->setType ( Message::ERR );
    		$this->message->setDesc ( $e->getMessage () );
    	}
    	
    	$vehJsonArr = array();
    	$vehJsonArr ["result"]  = $this->message->getType();
    	$vehJsonArr ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>",$this->message->getDesc() ));    	
    	
    	echo json_encode($vehJsonArr);
    }
	public function onViewDetail(){
		$vehicleTrav = null;
		$inTransaction = false;
		
		
		
		try {
		
			if(!User::isLogged()){
				throw new Exception("Necessário usuário estar logado!");
			}
			$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
			$criteria  = new Criteria();
			
			$this->vehTravParam = $this->getRequestBean ($_POST);
			$this->setTypeView($_POST["view_type"]);
			if($this->vehTravParam->getId() > 0){
				$db = DataBase::getInstance ( $this->getDbConfig () );
				$inTransaction = $db->beginTransaction ();
					
	    		$criteria->eq("vt.id",$this->vehTravParam->getId());
				$vehicleTrav = $this->vehicleTravelingDAO->getSingle($db,$criteria);				
				
				if( $this->getTypeView() == VehicleQueryCtrl::SEARCH_BY_ULT 
				 && $vehicleTrav!=null 
				 && User::getLogged()->getPerson()->getId() != $vehicleTrav->getPersonContact()->getPerson()->getId()
    			 && !User::isUserSuper())
				{
					$vehicleTrav->setStatus( VehicleTraveling::ONLY_VIEW );
					$this->vehicleTravelingDAO->setStatus($db,$vehicleTrav);				
					
					$vehicleTravelingUser = new VehicleTravelingUser();
					$vehicleTravelingUser->setUser(User::getLogged());
					$vehicleTravelingUser->setVehicleTraveling($vehicleTrav);
				
				
					// Criar Utilizador da informação
					$vehicleTravUserDAO = VehicleTravelingUserDAO::getInstance ();
					$vehicleTravUserDAO->insert ( $db, $vehicleTravelingUser );
					
					$db->commit ();
					$db = null;
					
				}
			}
			
			if($vehicleTrav==null){
				throw new Exception("Registro não informado");
			}				
		} catch ( PDOException $e ) {			
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			$vehicleTrav = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		} catch ( Exception $e ) {
			if ($db!=null && $inTransaction)
				$db->rollback ();
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		}	
		
		$vehJsonArr = array();
		if($vehicleTrav!=null){
			$vehicleType 	= $vehicleTrav->getVehicleType ( );
			$address 	 	= $vehicleTrav->getAddress ( );
			$vehicleContact = $vehicleTrav->getPersonContact ( );
			$phone 			= $vehicleContact->getPhoneContact( );
			$vehTravUser    = $vehicleTrav->getUserUtilized();
			
			$vehJsonArr["id"] 	   = $vehicleTrav->getId();
			$vehJsonArr["type_id"] = $vehicleType->getId();
			$vehJsonArr["type_nm"] = iconv ("UTF-8", "UTF-8", $vehicleType->getDescr());
			$vehJsonArr["dt_proc"] = $vehicleTrav->getDateHrProc()->format("d/m/Y H:i");
			
			$vehJsonArr["addr_id"] 	   = $address->getId();
			$vehJsonArr["cep"] 	   = $address->getCepMask();
			$vehJsonArr["city"]    = iconv ("UTF-8", "UTF-8", $address->getCity());
			$vehJsonArr["state"]   = $address->getState();
			
			$vehJsonArr["contact_phone"]    = iconv ("UTF-8", "UTF-8", $phone->getPhoneMask());
			$vehJsonArr["contact_name"]    = iconv ("UTF-8", "UTF-8", $vehicleContact->getName());
			
			$vehJsonArr["source"]   = $vehicleTrav->getDescSource();
			$vehJsonArr["sts_nm"]   = iconv ("UTF-8", "UTF-8",$vehicleTrav->getDescStatus());
			$vehJsonArr["sts"]   = $vehicleTrav->getStatus();
			
			$vehJsonArr["dt_used"] = "";
			if($vehTravUser!=null)
				$vehJsonArr["dt_used"]  = $vehTravUser->getDateHrUsed()->format("d/m/Y H:i");;	

			$this->message->setType ( Message::SUCCESS );
			
			
		}		
		$vehJsonArr ["result"]  = $this->message->getType();
		$vehJsonArr ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>",$this->message->getDesc() ));
		
	
	
		header ( "Content-type: text/plain" );
		echo json_encode ( $vehJsonArr );
		exit ();
	}
	private function getCriteriaFilterDefault(){
		$this->vehTravParam = $this->getRequestBean ($_POST);
			
		$criteria  = new Criteria();
			
		if($this->vehTravParam->getId() > 0)
			$criteria->eq("vt.id",$this->vehTravParam->getId());
		
		$vehicleType = $this->vehTravParam->getVehicleType ( );
		if($vehicleType!=null && $vehicleType->getId() > 0)
			$criteria->eq("vt.vehicle_type_id",$vehicleType->getId());
		
		$address = $this->vehTravParam->getAddress ( );
		if($address!=null){
			if($address->getCep()!=""){
				$criteria->eq("ad.cep",$address->getCep());
			}
			if($address->getState()!=""){
				$criteria->eq("ad.state",$address->getState());
			}
			if($address->getCity()!=""){
				$criteria->like("ad.city",$address->getCity().'%');
			}
		
		}
		
		$vehicleContact = $this->vehTravParam->getPersonContact ( );
		if($vehicleContact!=null)
		{
			$phone = $vehicleContact->getPhoneContact( );
			if($phone!=null && $phone->getPhone()!=""){
				$criteria->eq("ph.ddi",$phone->getDdi());
				$criteria->eq("ph.ddd",$phone->getDdd());
				$criteria->eq("ph.phone",$phone->getPhone());
			}
		
			if($vehicleContact->getName() != ""){
				$criteria->eq("vc.name",$vehicleContact->getName());
			}
		}
			
		$criteria->between("vt.date_hr_proc",$this->getDtHrInit(),$this->getDtHrEnd(),Criteria::FIELD_DATETIME);
		
		return $criteria;
	}
	public function onSearchArray(){
		
		try {
			$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
			
			$this->vehTravParam = $this->getRequestBean ($_POST);
			
			$criteria  = $this->getCriteriaFilterDefault( );
			
			$userLogged = User::getLogged( );
			
			if($this->typeView == VehicleQueryCtrl::SEARCH_BY_ULT){
				if( User::isUserSuper()){
					if($this->vehTravParam->getStatus ( ) != "")
						$criteria->eq("vt.status",$this->vehTravParam->getStatus ( ));
				}
				else
					$criteria->in("vt.status",array(VehicleTraveling::ONLY_VIEW,VehicleTraveling::NONE));
				
			}elseif($this->typeView == VehicleQueryCtrl::REG_CREATED)
				$criteria->eq("vc.person_id",$userLogged->getPerson()->getId());
			elseif($this->typeView == VehicleQueryCtrl::REG_UTILIZED)
				$criteria->eq("vt.user_person_id_ut",$userLogged->getPerson()->getId());
			
			$count 			= $this->vehicleTravelingDAO->count($this->getDbConfig (),$criteria);
			
			$this->setPagination(new Pagination($count));//inicia com o numero total de registros
			
			$criteria->setLimit($this->pagination->getStartLimit(),$this->pagination->getEndLimit());
			
			if($this->orderColumn == "")
				$this->orderColumn = "vt.id";
			if($this->orderType == "")
				$this->orderType   = "DESC";				
			$criteria->addOrder($this->orderColumn,$this->orderType);	
	    	 
	    	
			$this->vehiclesTraveling = $this->vehicleTravelingDAO->getArray($this->getDbConfig (),$criteria);
			if(sizeof($this->vehiclesTraveling)<=0){
				$this->message->setType ( Message::INFO );
				$this->message->setDesc ( 'Nenhum resultado encontrado' );
			}
			
		} catch ( PDOException $e ) {			
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		} catch ( Exception $e ) {
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		}		
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
			 * $this->message->setType ( Message::ERR ); $this->message->setDesc ( $e->getMessage () );
			 */
			
			return $_result;
		}
	}
	
	public function getListUf(){
		$addressDAO = AddressDAO::getinstance( );
		$_result = array();
		
		
		$_result = $addressDAO->getListUf( );
		
		return $_result;
		
	}
	
	public function getRequestBean(array $_data) {
		$person 		  = null;
		$vehicleType 	  = new VehicleType ();
		$phone 	     	  = new Phone ();
		$address 		  = new Address ();
		$vehicleTraveling = new VehicleTraveling ();
		$vehicleContact   = new VehicleContact ();
		
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
		
		/*$person = new Person ();
		$person->setName ( $_data ["vehicle_contact"] );*/
		
		$vehicleType->setId ( $_data ["vehicle_type"] );
		
		$phone->setPhoneByMask ( $_data ["vehicle_phone"] );
		//$person->setPhone ( $phone );
		
		$address->setCep ( $_data ["vehicle_zipcode"] );
		$address->setId ( $_data ["vehicle_address_id"] );
		$address->setStreet ( $_data ["vehicle_address"] );
		$address->setNumber ( $_data ["vehicle_address_number"] );
		$address->setComplement ( $_data ["vehicle_complement"] );
		$address->setCity ( $_data ["vehicle_city"] );
		$address->setState ( $_data ["vehicle_region"] );
		$_ufs = $this->getListUf();		
		$address->setStateNm ( $_ufs[$_data ["vehicle_region"]] );
		
		//$person->setAddress ( $address );
		
		//$vehicleContact->setPerson ( $person );
		$vehicleContact->setPhoneContact ( $phone );
		$vehicleContact->setName ( $_data ["vehicle_contact"] );
		$vehicleContact->setVehicleTraveling ( $vehicleTraveling );
		
		$vehicleTraveling->setVehicleType ( $vehicleType );
		$vehicleTraveling->setAddress ( $address );
		$vehicleTraveling->setPersonContact ( $vehicleContact );
		$vehicleTraveling->setIp ( Util::getClientIP () );
		$vehicleTraveling->setId ( $_data ["vehicle_id"] );
		
		if($_data["vehicle_status"]!="")
			$vehicleTraveling->setStatus ( $_data ["vehicle_status"] );
		
		$this->initCtrlParam( );
		
    	if($_data["vehicle_dt_init"]!=""){
    		$dt = explode("/",$_data["vehicle_dt_init"]);
    		$this->getDtHrInit()->setDate($dt[2],$dt[1],$dt[0]);	
    	}
    	if($_data["vehicle_dt_end"]!=""){
    		$dt = explode("/",$_data["vehicle_dt_end"]);
    		$this->getDtHrEnd()->setDate($dt[2],$dt[1],$dt[0]);	
    	}
    	$this->setOrderColumn($_data['order_column']);
    	$this->setOrderType($_data['order_type']);  	 
    	
    	$this->message->setType ( $_data["msg_type"] );
		$this->message->setDesc ( $_data["msg_txt"] );
		
		return $vehicleTraveling;
	}
	public function initCtrlParam(){
		$this->setDtHrInit(new DateTimeCustom("Now"));
    	$this->getDtHrInit()->modify("-30 day");
    	$this->getDtHrInit()->setTime(0,0);
    	$this->setDtHrEnd(new DateTimeCustom("Now"));
    	$this->getDtHrEnd()->setTime(23,59);
	}
	
}

$ctrlVehQry = new VehicleQueryCtrl ();
?>
