<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTypeVO.class.php");
class VehicleTypeDAO {
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
			    SELECT 	  vtyp.id
					   	, vtyp.descr
						, vtyp.enabled
				  FROM 	vehicle_type vtyp
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
			$vechicleType = new VehicleType ();
			$vechicleType->setId ( $objDAO->id );
			$vechicleType->setDescr ( $objDAO->descr );
			$vechicleType->setEnabled ( $objDAO->enabled );
			$_arr [] = $vechicleType;
		}
		return $_arr;
	}
	public function count($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	COUNT(vtyp.id) AS total
				  FROM 	vehicle_type vtyp
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
		
		$objDAO = $result->fetchObject ();
		
		if($objDAO->total > 0)
			return $objDAO->total;
		return 0; 
	}
	public function getArrayCache($dbConfig) {
			// Use defensive checks for session cache
			$_result = array();
			if (isset($_SESSION) && isset($_SESSION["list_vehicle_type"]) && $_SESSION["list_vehicle_type"] != "") {
				try {
					$_tmp = @unserialize($_SESSION["list_vehicle_type"]);
					if (is_array($_tmp)) {
						$_result = $_tmp;
					}
				} catch ( Exception $e ) {
					// ignore and fallback to fetching from DB
				}
			}
		
			if (count($_result) > 0) {
				return $_result;
			} else {
				try {
					$_result = $this->getArray ( $dbConfig );
					if (is_array($_result)) {
						if (isset($_SESSION)) $_SESSION["list_vehicle_type"] = serialize($_result);
					}
				} catch ( PDOException $e ) {
					throw $e;
				}
			}
		
			if (!is_array($_result))
				$_result = array ();
		
			return $_result;
		}
}
?>