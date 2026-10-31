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
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ClientVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/ClientDAO.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class CustumerQueryCtrl extends AbstractCtrl {
	private $clientDAO = null;
	/**
	 * * Parametros para busca **
	 */
	private $custumerParam = null;
	private $dtHrInit = null;
	private $dtHrEnd = null;
	/**
	 * * Parametros para busca **
	 */
	private $custumers = null;
	private $orderType = null; // tipo da ordenação
	private $orderColumn = null; // coluna a ser ordenada
	private $pagination = null;
	public function __construct() {
		parent::__construct ();
		$this->initCtrlParam ();
		$this->setAction ( $_REQUEST ["action"] ?? null );

		$this->guardAction(array(), AbstractCtrl::ACCESS_ADMIN);
		$this->setPagination ( new Pagination ( 0 ) ); // inicia com o numero total de registros
		
		switch ($this->getAction ()) {
			case "searchArray" :
				$this->onSearchArray ();
				break;
			case "exportCSV" :
				$this->exportCSV ();
				break;
			case "searchSingleCHK":
				$this->searchSingleCHK();
				break;
			default :
		}
	}
	public function searchSingleCHK(){
		$custumer = null;
		try {
			$this->clientDAO = ClientDAO::getInstance ();
			$this->custumerParam = $this->getRequestBean ( $_POST );
			if($this->custumerParam->getUser()->getPerson()->getId() <= 0){
				throw new Exception("Cliente não encontrado!!");
			}
			$this->custumerParam->getUser()->setStatus (User::CHEK);
			$criteria = $this->getCriteriaFilterDefault ();
				
			$custumer = $this->clientDAO->getSingle ( $this->getDbConfig (), $criteria );
			if($custumer == null){
				throw new Exception("Cliente não encontrado!!");
			}
		} catch ( PDOException $e ) {
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
		} catch ( Exception $e ) {
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
		
		$output ["error"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );			
		if($custumer!=null){
			$output ["name"] = iconv ( "UTF-8", "UTF-8",$custumer->getUser()->getPerson()->getName());	
		}
			
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Content-type: application/json; charset=UTF-8");
		echo json_encode ( $output );
	}
	private function getCriteriaFilterDefault() {
		
		$userParam = $this->custumerParam->getUser ();
		$personParam = $userParam->getPerson ();
		$address = $personParam->getAddress ();
		$phone = $personParam->getPhone ();
		
		$criteria = new Criteria ();
		
		if ($personParam->getId () > 0)
			$criteria->eq ( "cli.user_person_id", $personParam->getId () );
		
		if ($userParam->getStatus () != "")
			$criteria->eq ( "usr.status", $userParam->getStatus () );
		
		if ($personParam->getEmail () != "")
			$criteria->eq ( "per.email", $personParam->getEmail () );
		
		switch ($personParam->getType ()) {
			case Person::PF :
				if ($personParam->getCpf () != "")
					$criteria->eq ( "pf.cpf", $personParam->getCpf () );
				
				if ($personParam->getName () != "")
					$criteria->eq ( "pf.name", $personParam->getName () );
				
				$criteria->eq ( "per.type", $personParam->getType () );
				
				break;
			case Person::PJ :
				
				if ($personParam->getContact () != "")
					$criteria->eq ( "pj.contact", $personParam->getContact () );
				
				if ($personParam->getCnpj () != "")
					$criteria->eq ( "pj.cnpj", $personParam->getCnpj () );
				
				if ($personParam->getIe () != "")
					$criteria->eq ( "pj.ie", $personParam->getIe () );
				
				if ($personParam->getName () != "")
					$criteria->eq ( "pj.name", $personParam->getName () );
				
				$criteria->eq ( "per.type", $personParam->getType () );
				break;
		}
		
		if ($address != null) {
			if ($address->getCep () != "") {
				$criteria->eq ( "addr.cep", $address->getCep () );
			}
			if ($address->getState () != "") {
				$criteria->eq ( "addr.state", $address->getState () );
			}
			if ($address->getCity () != "") {
				$criteria->like ( "addr.city", $address->getCity () . '%' );
			}
		}
		
		if ($phone != null && $phone->getPhone () != "") {
			$criteria->eq ( "pho.ddi", $phone->getDdi () );
			$criteria->eq ( "pho.ddd", $phone->getDdd () );
			$criteria->eq ( "pho.phone", $phone->getPhone () );
		}
		
		return $criteria;
	}
	public function onSearchArray() {
		try {
			$this->clientDAO = ClientDAO::getInstance ();
			
			$this->custumerParam = $this->getRequestBean ( $_POST );
			$personType = $this->custumerParam->getUser ()->getPerson ()->getType ();
			
			$criteria = $this->getCriteriaFilterDefault ();
			
			$count = $this->clientDAO->count ( $this->getDbConfig (), $criteria, $personType );
			
			$this->setPagination ( new Pagination ( $count ) ); // inicia com o numero total de registros
			
			$criteria->setLimit ( $this->pagination->getStartLimit (), $this->pagination->getEndLimit () );
			
			if ($this->orderColumn == "")
				$this->orderColumn = "cli.user_person_id";
			if ($this->orderType == "")
				$this->orderType = "DESC";
			$criteria->addOrder ( $this->orderColumn, $this->orderType );
			
			$this->custumers = $this->clientDAO->getArray ( $this->getDbConfig (), $criteria, $personType );
			if (sizeof ( $this->custumers ) <= 0) {
				$this->message->setType ( Message::INFO );
				$this->message->setDesc ( 'Nenhum resultado encontrado' );
			}
		} catch ( PDOException $e ) {
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
		} catch ( Exception $e ) {
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
	}
	public function exportCSV() {
		try {
			$this->clientDAO = ClientDAO::getInstance ();
			$this->custumerParam = $this->getRequestBean ( $_POST );
			$personType = $this->custumerParam->getUser ()->getPerson ()->getType ();
			
			$criteria  = $this->getCriteriaFilterDefault( );
				
			$criteria->addOrder("cli.user_person_id","DESC");
			$this->custumers = $this->clientDAO->getArray ( $this->getDbConfig (), $criteria, $personType );
			
			$length 		  = count($this->custumers);
			if($length<=0){
				$this->message->setType ( Message::ERR );
				$this->message->setDesc ( 'Nenhum resultado encontrado' );
			}else{
		
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=resultado_busca_kade.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo "Registro;Tipo;Criado em;Nome/Razão Social;CPF/CNPJ;CEP;Cidade;UF;Telefone;Contato;Status\r";
				$i = 0;
				while($i < $length){
					$custumerList     = $this->custumers[$i];
					
					$userList		  = $custumerList->getUser();
					$personList 	  = $userList->getPerson();
					$addressList 	  = $personList->getAddress ( );
					
					$address 	 	= $personList->getAddress ( );
					$phone 			= $personList->getPhone( );
		
					echo $personList->getId().";";
					echo $personList->getDescType().";";
					echo $userList->getDateCad()->format("d/m/Y H:i").";";
					echo $personList->getName().";";
					
					switch ($personList->getType ()) {
						case Person::PF :
							echo Util::mask($personList->getCpf(),"###.###.###-##").";";
							break;
						case Person::PJ :
							echo Util::mask($personList->getCnpj(),"##.###.###/####-##").";";
							break;
					}
					
					
					echo $address->getCepMask().";";
					echo $address->getCity().";";
					echo $address->getState().";";
					echo $phone->getPhoneMask().";";
					
					switch ($personList->getType ()) {
						case Person::PF :
							echo $personList->getName().";";
							break;
						case Person::PJ :
							echo $personList->getContact().";";
							break;
					}
					
					echo $userList->getDescStatus().";";
					echo "\r";
					$i++;
				}
			}
		
		} catch ( PDOException $e ) {
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
		} catch ( Exception $e ) {
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
		
		if($this->message->getType ( ) == Message::ERR){
			echo "<h2>".$this->message->getDesc ()."</h2>";
		}
	}
	public function getCustumers() {
		return $this->custumers;
	}
	public function getCustumerParam() {
		return $this->custumerParam;
	}
	public function getListUf() {
		$addressDAO = AddressDAO::getinstance ();
		$_result = array ();
		
		$_result = $addressDAO->getListUf ();
		
		return $_result;
	}
	public function getPagination() {
		return $this->pagination;
	}
	/**
	 * Setter: pagination
	 *
	 * @param
	 *        	Pagination
	 * @return void
	 */
	public function setPagination(Pagination $pagination) {
		$this->pagination = $pagination;
	}
	
	/**
	 *
	 * @method String Retorna a imagem de acordo com a ordenação
	 */
	public function getImgOrder($columnBD) {
		if ($this->getOrderColumn () == trim ( $columnBD )) {
			if ($this->getOrderType () == "DESC")
				return "&#x25BC;";
			return "&#x25B2;";
		}
		return "";
	}
	/**
	 * Getter : orderColumn
	 *
	 * @return String
	 */
	public function getOrderColumn() {
		return $this->orderColumn;
	}
	/**
	 * Getter: orderType
	 *
	 * @return String
	 */
	public function getOrderType() {
		return $this->orderType;
	}
	/**
	 * Setter: orderColumn
	 *
	 * @param
	 *        	String
	 * @return void
	 */
	public function setOrderColumn($orderColumn = "cli.user_person_id") {
		$this->orderColumn = trim ( $orderColumn );
	}
	/**
	 * Setter: orderType
	 *
	 * @param
	 *        	String
	 * @return void
	 */
	public function setOrderType($orderType = "DESC") {
		$this->orderType = strtoupper ( trim ( $orderType ) );
	}
	/**
	 * * Parametros para busca **
	 */
	public function getDtHrInit() {
		return $this->dtHrInit;
	}
	public function setDtHrInit(DateTime $dtHrInit) {
		$this->dtHrInit = $dtHrInit;
	}
	public function getDtHrEnd() {
		return $this->dtHrEnd;
	}
	public function setDtHrEnd(DateTime $dtHrEnd) {
		$this->dtHrEnd = $dtHrEnd;
	}
	public function getRequestBean(array $_data) {
		$custumer = new Client ();
		$user = new User ();
		$address = new Address ();
		$phone = new Phone ();
		
		/*if ($_data ["type_person"] == "")
			$_data ["type_person"] = Person::PJ;*/
		
		switch ($_data ["type_person"]) {
			case Person::PF :
				$person = new PersonIndividual ();
				$person->setName ( $_data ["custumer_name"] );
				$person->setCpf ( $_data ["custumer_cpf"] );
				break;
			case Person::PJ :
				$person = new PersonEntity ();
				$person->setName ( $_data ["custumer_rsoc"] );
				$person->setContact ( $_data ["custumer_contact"] );
				$person->setCnpj ( $_data ["custumer_cnpj"] );
				
				if ($_data ["custumer_ie_isento"] == "on")
					$_data ["custumer_ie"] = PersonEntity::ISENTO;
				
				$person->setIe ( $_data ["custumer_ie"] );
				
				break;
			default :
				$person = new Person ();
		}
		$person->setId ( $_data ["custumer_id"] );
		$person->setEmail ( $_data ["custumer_mail"] );
		
		$phone->setPhoneByMask ( $_data ["custumer_phone"] );
		
		$_ufs = $this->getListUf ();
		$address->setState ( $_data ["custumer_region"] );
		$address->setStateNm ( $_ufs [$_data ["custumer_region"]] );
		$address->setCep ( $_data ["custumer_zipcode"] );
		$address->setCity ( $_data ["custumer_city"] );
		
		if ($_data ["custumer_status"] == "") {
			$_data ["custumer_status"] = User::CHEK;
		}
		$user->setStatus ( $_data ["custumer_status"] );
		
		$this->initCtrlParam ();
		if ($_data ["custumer_dt_init"] != "") {
			$dt = explode ( "/", $_data ["custumer_dt_init"] );
			$this->getDtHrInit ()->setDate ( $dt [2], $dt [1], $dt [0] );
		}
		if ($_data ["custumer_dt_end"] != "") {
			$dt = explode ( "/", $_data ["custumer_dt_end"] );
			$this->getDtHrEnd ()->setDate ( $dt [2], $dt [1], $dt [0] );
		}
		$this->setOrderColumn ( $_data ['order_column'] );
		$this->setOrderType ( $_data ['order_type'] );
		
		$this->message->setType ( $_data ["msg_type"] );
		$this->message->setDesc ( $_data ["msg_txt"] );
		
		$person->setAddress ( $address );
		$person->setPhone ( $phone );
		$user->setPerson ( $person );
		$custumer->setUser ( $user );
		
		return $custumer;
	}
	public function initCtrlParam() {
		$this->setDtHrInit ( new DateTimeCustom ( "Now" ) );
		$this->getDtHrInit ()->modify ( "-3 years" );
		$this->getDtHrInit ()->setTime ( 0, 0 );
		$this->setDtHrEnd ( new DateTimeCustom ( "Now" ) );
		$this->getDtHrEnd ()->setTime ( 23, 59 );
	}
}

$ctrlCustQry = new CustumerQueryCtrl ();
?>