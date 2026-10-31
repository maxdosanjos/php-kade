<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Pagination.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/mail/Mail.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ClientVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PaymentMethodVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/InstallmentVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AccountVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/ClientDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AccountDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class AccountQueryCtrl extends AbstractCtrl {
	private $accountDAO = null;
	
	/**
	 * * Parametros para busca **
	 */
	private $accountParam = null;
	/**
	 * * Parametros para busca **
	 */
	private $accounts	 = null;
	private $orderType   = null; // tipo da ordenação
	private $orderColumn = null; // coluna a ser ordenada
	private $pagination  = null;
	
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
			default :
		}
	}
	
	public function onSearchArray() {
		try {
			$this->accountDAO = AccountDAO::getInstance ();
				
			$this->accountParam = $this->getRequestBean ( $_POST );
			$clientParam = $this->accountParam->getClient();
			$personParam = 	$clientParam->getUser()->getPerson();
			
			$criteria = new Criteria();
			if ($this->accountParam->getId () > 0)
				$criteria->eq ( "acc.id", $this->accountParam->getId () );
			
			$criteria->between("acc.date_hr_proc",$this->getDtHrInit(),$this->getDtHrEnd(),Criteria::FIELD_DATETIME);
			
			if ($personParam->getId () > 0)
				$criteria->eq ( "acc.client_user_person_id", $personParam->getId () );
				
			$count = $this->accountDAO->count ( $this->getDbConfig (), $criteria);
				
			$this->setPagination ( new Pagination ( $count ) ); // inicia com o numero total de registros
				
			$criteria->setLimit ( $this->pagination->getStartLimit (), $this->pagination->getEndLimit () );
				
			if ($this->orderColumn == "")
				$this->orderColumn = "acc.id";
			if ($this->orderType == "")
				$this->orderType = "DESC";
			$criteria->addOrder ( $this->orderColumn, $this->orderType );
				
			$this->accounts = $this->accountDAO->getArray ( $this->getDbConfig (), $criteria );
			if (sizeof ( $this->accounts ) <= 0) {
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
	public function getRequestBean(array $_data){
		$account = new Account();
		$client  = new Client();
		$user	 = new User();
		$person  = new Person();
		$account->setId ( $_data ["account_id"] );
		
		$person->setId($_data["account_client_id"]);
		$person->setName($_data["account_client_name"]);
		
		$user->setPerson($person);
		$client->setUser($user);
		$account->setClient($client);
		
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
		
		return $account;
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
	public function setOrderColumn($orderColumn = "acc.id") {
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
	public function getAccounts(){
		return $this->accounts;
	}
	public function getAccountParam(){
		return $this->accountParam;
	}
	public function initCtrlParam() {
		$this->setDtHrInit ( new DateTimeCustom ( "Now" ) );
		$this->getDtHrInit ()->modify ( "-3 years" );
		$this->getDtHrInit ()->setTime ( 0, 0 );
		$this->setDtHrEnd ( new DateTimeCustom ( "Now" ) );
		$this->getDtHrEnd ()->setTime ( 23, 59 );
	}
	

}
$ctrlAcctQry = new AccountQueryCtrl ();
?>