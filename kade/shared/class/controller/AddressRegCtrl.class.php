<?
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class AddressRegCtrl extends AbstractCtrl {	
	public function __construct() {
		parent::__construct ();
		$this->setAction ( $_REQUEST ["action"] ?? null );

		$this->guardAction(array(), AbstractCtrl::ACCESS_PUBLIC);
		
		switch ($this->getAction ()) {
			case "searchCEP" :
				$this->onSearchCEP ();
				break;
			default :
		}
	}
	
	public function onSearchCEP() {
		$output = array (
				"result" => 0,
				"message" => "",
				"data" => "" 
		);
		
		$address = new Address ();
		$address->setCep ( $_REQUEST ["zipcode"] );
		try {
			
			$addressDAO = AddressDAO::getInstance ();
			$dbConfigCep = $addressDAO->loadDBConfigCep ();
			$db = DataBase::getInstance ( $dbConfigCep );
			$addressDAO->getAddressByZipCode ( $db, $address );
			
			$_address ["address"] = iconv ( "UTF-8", "UTF-8", $address->getStreet () );
			$_address ["addressNumber"] = $address->getNumber ();
			$_address ["city"] = iconv ( "UTF-8", "UTF-8", $address->getCity () );
			$_address ["neighborhood"] = iconv ( "UTF-8", "UTF-8", $address->getNeighborhood () );
			$_address ["UF"] = $address->getState ();
			$_address ["UF_NM"] = iconv ( "UTF-8", "UTF-8", $address->getStateNm () );
			$_address ["zipcode"] = $address->getCep ();
			
			$output ["result"] = 1;
			$output ["data"] = $_address;
			// $db->commit ();
			$db = null;
			$dbConfigCep = null;
		} catch ( PDOException $e ) {
			$this->message->setType ( Message::WARN );
			$this->message->setDesc ( $e->getMessage () );
			
			/*
			 * if ($db->inTransaction()) $db->rollback ();
			 */
			$db = null;
			$dbConfigCep = null;
			$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace("\\n","<br/>",$this->message->getDesc () ));
		}
		
		header ( "Content-type: text/plain" );
		echo json_encode ( $output );
		exit ();
	}
}
$ctrlAddrReg = new AddressRegCtrl ();
?>