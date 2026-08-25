<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PaymentMethodVO.class.php");
class PaymentMethodDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	
	public function getArray($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  pay.id
					   	, pay.name
						, pay.enabled
				  FROM 	payment_method pay
				";
	
		//if ($criteria == null)
		//	$criteria = new Criteria ();
	
		//$criteria->eq ( "vtyp.enabled", "1" );
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
	
		$_arr = array ();
	
		while ( $objDAO = $result->fetchObject () ) {
			$paymentMethod = new PaymentMethod ();
			$paymentMethod->setId ( $objDAO->id );
			$paymentMethod->setName ( $objDAO->name );
			$paymentMethod->setEnabled ( $objDAO->enabled );
			$_arr [] = $paymentMethod;
		}
		return $_arr;
	}
	public function getArrayCache($dbConfig) {
		//$_SESSION ["list_vehicle_type"] = "";
		if($_SESSION ["list_payment_method"]!=""){
			$_result = unserialize ( $_SESSION ["list_payment_method"] );
		}
	
		if (sizeof ( $_result ) > 0) {
			return $_result;
		} else {
			$_result = array ();
				
			try {
				$_result = $this->getArray ( $dbConfig );
				$_SESSION["list_payment_method"] = serialize($_result);
			} catch ( PDOException $e ) {
				throw $e;
			}
		}
	
		if (sizeof ( $_result ) <= 0)
			$_result = array ();
	
		return $_result;
	}
}
?>