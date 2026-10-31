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
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/InstallmentDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class InstallmentQueryCtrl extends AbstractCtrl {
	private $installmentDAO = null;
	
	/**
	 * * Parametros para busca **
	 */
	private $installmentParam = null;
	/**
	 * * Parametros para busca **
	 */
	private $installments = null;
	private $orderType = null; // tipo da ordenação
	private $orderColumn = null; // coluna a ser ordenada
	private $pagination = null;
	public function __construct() {
		parent::__construct ();
		$this->setAction ( $_REQUEST ["action"] ?? null );

		$this->guardAction(array(), AbstractCtrl::ACCESS_USER);
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
			$this->installmentDAO = InstallmentDAO::getInstance ();
			
			$this->installmentParam = $this->getRequestBean ( $_POST );
			
			$criteria = new Criteria ();
			$criteria->eq ( "acc.client_user_person_id", User::getLogged ()->getPerson ()->getId () );
			$criteria->eq ( "ins.status", $this->installmentParam->getStatus() );

			
			if ($this->orderColumn == "")
				$this->orderColumn = "ins.id";
			if ($this->orderType == "")
				$this->orderType = "ASC";
			$criteria->addOrder ( $this->orderColumn, $this->orderType );
			
			$this->installments = $this->installmentDAO->getArray ( $this->getDbConfig (), $criteria );
			if (sizeof ( $this->installments ) <= 0) {
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
		if ($this->getOrderColumn () == trim((string) $columnBD)) {
			if ($this->getOrderType () == "ASC")
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
	public function setOrderColumn($orderColumn = "ins.id") {
		$this->orderColumn = trim((string) $orderColumn);
	}
	/**
	 * Setter: orderType
	 *
	 * @param
	 *        	String
	 * @return void
	 */
	public function setOrderType($orderType = "DESC") {
		$this->orderType = strtoupper ( trim((string) $orderType) );
	}
	public function getInstallments() {
		return $this->installments;
	}
	public function getInstallmentParam() {
		return $this->installmentParam;
	}
	public function getRequestBean($_data){
		$installment = new Installment();
		if($_data["parc_status"]!="")
			$installment->setStatus($_data["parc_status"]);
		else
			$installment->setStatus(Installment::UNPAID);
		
		$this->setOrderColumn($_data['order_column']);
		$this->setOrderType($_data['order_type']);
		 
		$this->message->setType ( $_data["msg_type"] );
		$this->message->setDesc ( $_data["msg_txt"] );
		
		return $installment;
	}
}
$ctrlInstQry = new InstallmentQueryCtrl ();
?>