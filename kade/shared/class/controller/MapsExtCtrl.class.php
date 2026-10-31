<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/MapsExtVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTravelingVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/MapsExtDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTypeDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class MapsExtCtrl extends AbstractCtrl {
	private $mapsExtDAO = null;
	private $isGrpByUf = false;
	private $isAjax = false;
	private $_mapsExt = array ();
	private $limitReg = 14;
	private $selectedUf = "";
	public function __construct() {
		parent::__construct ();
		$this->setAction ( $_REQUEST ["action"] ?? null );
		
		/* Expirando msgs */
		$this->onExpiredRegisters ();
		
		switch ($this->getAction ()) {
			case "searchMapsByUf" :
				$this->onSearchMapsByUf ();
				break;
			case "searchMaps" :
				$this->onSearchMaps ();
				break;
			case "loadBanner" :
				$this->loadBanner ();
				break;
			/*
			 * case "expiredRegisters" : $this->expiredRegisters (); break;
			 */
			default :
		}
	}
	public function loadBanner() {
		$output = array (
				"result" => 0,
				"message" => "",
				"validationException" => array () 
		);
		
		
		$_validationException = array ();
		
		$fieldFile = "banner_file";
		$file = $_FILES [$fieldFile];
		try {
			if ($file ["error"] > 0) {
				throw new Exception ( "Erro no upload: " . $file ["error"] );
			}
			if (eregi ( ".jpg$", $file ["name"] ) && $file["size"] <= 1024000 && ($file["type"] == "image/gif" || $file["type"] == "image/jpeg" || $file["type"] == "image/pjpeg")) {
				
				$dir = dirname ( dirname ( dirname ( __FILE__ ) ) );
				if (move_uploaded_file ( $file ["tmp_name"], $dir . "/images/banner_slider" . $_POST ["banner_id"] . ".jpg" )) {
					$this->message->setType ( Message::SUCCESS );
					$this->message->setDesc ( "Upload realizado com sucesso!" );
				} else {
					throw new Exception ( "Erro no upload" );
				}
			} else {
				throw new Exception ( "Arquivo inválido! Somente imagem de extensão JPG e tamanho máximo 1 MB" );
			}
		} catch ( Exception $e ) {			
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
	public function onExpiredRegisters() {
		$prop = Properties::getGroup ( "job_msg_expired" );
		
		$file_cron_exp = dirname ( dirname ( dirname ( __FILE__ ) ) ) . "/" . $prop ["file_job_expired"];
		if (file_exists ( $file_cron_exp )) {
			$crontabExpired = @file_get_contents ( $file_cron_exp );
		}
		
		$dtNow = new DateTimeCustom ( "Now" );
		
		if ($crontabExpired != "")
			$dtInit = new DateTimeCustom ( $crontabExpired );
		else {
			$dtInit = new DateTimeCustom ( "Now" );
			$dtInit->modify ( "-" . $prop ["interval_job_expired"] . " hours" );
		}
		
		$dif = $dtNow->subtract ( $dtNow, $dtInit );
		
		if ($dif ["Hours"] >= $prop ["interval_job_expired"]) {
			$this->expiredRegisters ( $dtInit );
			@file_put_contents ( $file_cron_exp, $dtNow->format ( "Y/m/d H:i:s" ) );
		}
	}
	private function expiredRegisters(DateTimeCustom $dtInit) {
		$inTransaction = false;
		$db = null;
		try {
			$vehicleTravelingDAO = VehicleTravelingDAO::getInstance ();
			
			$criteria = new Criteria ();
			$criteria->lt ( "vt.date_hr_proc", $dtInit->format ( Criteria::$DEFAULT_DATETIMEFORMAT ), Criteria::FIELD_DATETIME );
			$criteria->in ( "vt.status", array (
					VehicleTraveling::ONLY_VIEW,
					VehicleTraveling::NONE 
			) );
			
			$vehiclesTraveling = $vehicleTravelingDAO->getArray ( $this->getDbConfig (), $criteria );
			if (sizeof ( $vehiclesTraveling ) <= 0) {
				$this->message->setType ( Message::INFO );
				$this->message->setDesc ( 'Nenhum resultado encontrado' );
			} else {
				
				$db = DataBase::getInstance ( $this->getDbConfig () );
				$inTransaction = $db->beginTransaction ();
				foreach ( $vehiclesTraveling as $obj ) {
					$obj->setStatus ( VehicleTraveling::EXPIRED );
					$vehicleTravelingDAO->setStatus ( $db, $obj );
				}
				$db->commit ();
				$db = null;
				
				$this->message->setType ( Message::SUCCESS );
				$this->message->setDesc ( 'Atualização realizada com sucesso' );
			}
		} catch ( PDOException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		} catch ( Exception $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $e->getMessage () );
		}
		
		/*
		 * $output = array(); $output ["result"] = $this->message->getType (); $output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>",$this->message->getDesc () )); header ( "Content-type: text/plain" ); echo json_encode ( $output ); exit ();
		 */
	}
	public static function includeTableMaps(MapsExtCtrl $ctrlMapsExt) {
		include_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . "/view/table_maps.php");
	}
	public function getTotalReg() {
		$total = 0;
		$this->mapsExtDAO = MapsExtDAO::getInstance ();
		try {
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$total = $this->mapsExtDAO->getTotalReg ( $db, true );
			$db = null;
		} catch ( PDOException $e ) {
			$db = null;
		}
		return $total;
	}
	/**
	 * Quantidade de veículos por UF, usada para colorir o mapa da home
	 * @return array UF => quantidade
	 */
	public function getQtyByUf() {
		$_qty = array ();
		try {
			$db = DataBase::getInstance ( $this->getDbConfig () );
			foreach ( MapsExtDAO::getInstance ()->getArrayGrpUf ( $db ) as $mapsExt )
				$_qty [$mapsExt->getAddress ()->getState ()] = ( int ) $mapsExt->getQtyReal ();
			$db = null;
		} catch ( PDOException $e ) {
			$db = null;
		}
		return $_qty;
	}
	public function getListUf() {
		return AddressDAO::getInstance ()->getListUf ();
	}
	public function getTypeVechicles() {
		$_result = array ();
		try {
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$_result = VehicleTypeDAO::getInstance ()->getArrayCache ( $db );
			$db = null;
		} catch ( PDOException $e ) {
			$db = null;
		}
		return $_result;
	}
	public function onSearchMapsByUf() {
		$this->_mapsExt = array ();
		$this->isGrpByUf = false;
		$this->mapsExtDAO = MapsExtDAO::getInstance ();
		$this->isAjax = $_POST ["is_ajax"] ?? false;
		
		try {
			$address = new Address ();
			$address->setState ( $_POST ["maps_state"] );
			$this->selectedUf = $address->getState ();

			$criteria = new Criteria ();
			$criteria->eq ( "idx.state", $address->getState () );
			
			$criteria->addOrder ( "idx.qty_real", Criteria::ORDER_DESC );
			
			$criteria->setLimit ( 0, $this->limitReg );
			
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$this->_mapsExt = $this->mapsExtDAO->getArray ( $db, $criteria );
			$db = null;
		} catch ( PDOException $e ) {
			$db = null;
		}
		MapsExtCtrl::includeTableMaps ( $this );
	}
	public function onSearchMaps() {
		$this->_mapsExt = array ();
		$this->isGrpByUf = true;
		$this->mapsExtDAO = MapsExtDAO::getInstance ();
		$this->isAjax = $_POST ["is_ajax"] ?? false;
		
		try {
			$db = DataBase::getInstance ( $this->getDbConfig () );
			
			$criteria = new Criteria ();
			// Alias da soma; "idx.qty_real" não é aceito com GROUP BY no MySQL 8 (ONLY_FULL_GROUP_BY)
			$criteria->addOrder ( "qty_real", Criteria::ORDER_DESC );

			$criteria->setLimit ( 0, $this->limitReg );
			$this->_mapsExt = $this->mapsExtDAO->getArrayGrpUf ( $db, $criteria );
			
			$db = null;
		} catch ( PDOException $e ) {
			$db = null;
		}
		MapsExtCtrl::includeTableMaps ( $this );
	}
	public function getListMapsExt() {
		return $this->_mapsExt;
	}
	public function getSelectedUfName() {
		$_ufs = $this->getListUf ();
		return $_ufs [$this->selectedUf] ?? $this->selectedUf;
	}
	public function getIsGrpByUf() {
		return $this->isGrpByUf;
	}
	public function getIsAjax() {
		return $this->isAjax;
	}
}
$ctrlMapsExt = new MapsExtCtrl ();
?>