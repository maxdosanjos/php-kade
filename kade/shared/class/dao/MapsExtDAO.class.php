<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/MapsExtVO.class.php");
class MapsExtDAO {
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
			    SELECT 	  idx.state
					   	, idx.city
						, idx.state_nm
						, qty_real
						, qty_illus
				  FROM 	idx_traveling_city idx
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
		
		$_arr = array ();
		
		while ( $objDAO = $result->fetchObject () ) {
			$address = new Address();
			$address->setState ( $objDAO->state );
			$address->setCity ( $objDAO->city );
			$address->setStateNm ( $objDAO->state_nm );
			
			$mapsExt = new MapsExt();
			$mapsExt->setAddress($address);
			$mapsExt->setQtyReal($objDAO->qty_real);
			$mapsExt->setQtyIllus($objDAO->qty_illus);
			
			$_arr [] = $mapsExt;
		}
		return $_arr;
	}
	public function getArrayGrpUf($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  idx.state
						, idx.state_nm
						, SUM(idx.qty_real) AS qty_real
						, SUM(idx.qty_illus) AS qty_illus
				  FROM 	idx_traveling_city idx
				";
		$sql .= " GROUP BY idx.state ASC";
		
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
			$address = new Address();
			$address->setState ( $objDAO->state );
			$address->setStateNm ( $objDAO->state_nm );
				
			$mapsExt = new MapsExt();
			$mapsExt->setAddress($address);
			$mapsExt->setQtyReal($objDAO->qty_real);
			$mapsExt->setQtyIllus($objDAO->qty_illus);
				
			$_arr [] = $mapsExt;
		}
		
		return $_arr;
	}
	
	public function getTotalReg($dbConfig, $qtyIllus = true) {

		if($qtyIllus)
			$sqlSum = "SUM(idx.qty_illus) AS total";
		else
			$sqlSum = "SUM(idx.qty_real) AS total";
		
		$sql = "
			    SELECT 	 ".$sqlSum."
				  FROM 	idx_traveling_city idx
				";	
		
		
	
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
}
?>