<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Pagination.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTravelingVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
	class MsgLogQueryCtrl extends AbstractCtrl {
		private $vehicleTravelingDAO = null;
		
		private $msgLogs	 = null;
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
		
		public function onSearchArray(){
			try {
				$this->vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
					
				$criteria = new Criteria();
				if ($_POST["type_log"]!="")
					$criteria->eq ( "log.type_log", $_POST["type_log"] );
					
				$criteria->between("log.date_hr",$this->getDtHrInit(),$this->getDtHrEnd(),Criteria::FIELD_DATETIME);
			
				$count = $this->vehicleTravelingDAO->countLogSMS ( $this->getDbConfig (), $criteria);
			
				$this->setPagination ( new Pagination ( $count ) ); // inicia com o numero total de registros
			
				$criteria->setLimit ( $this->pagination->getStartLimit (), $this->pagination->getEndLimit () );
			
				if ($this->orderColumn == "")
					$this->orderColumn = "log.date_hr";
				if ($this->orderType == "")
					$this->orderType = "DESC";
				$criteria->addOrder ( $this->orderColumn, $this->orderType );
			
				$this->msgLogs = $this->vehicleTravelingDAO->getArrayLogSMSgetArray ( $this->getDbConfig (), $criteria );
				if (sizeof ( $this->msgLogs ) <= 0) {
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
		public function setOrderColumn($orderColumn = "log.date_hr") {
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
		public function getMsgLogs(){
			return $this->msgLogs;
		}
		public function initCtrlParam() {
			$this->setDtHrInit ( new DateTimeCustom ( "Now" ) );
			$this->getDtHrInit ()->modify ( "-3 years" );
			$this->getDtHrInit ()->setTime ( 0, 0 );
			$this->setDtHrEnd ( new DateTimeCustom ( "Now" ) );
			$this->getDtHrEnd ()->setTime ( 23, 59 );
		}
		
	}
	
	$ctrlMsgLogQry = new MsgLogQueryCtrl ();
?>