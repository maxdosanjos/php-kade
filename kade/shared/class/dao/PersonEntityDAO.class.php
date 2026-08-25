<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
class PersonEntityDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function update($dbConfig, PersonEntity $obj) {
		$sql = "UPDATE `person_entity` 
					SET `ie`	   = '" . $obj->getIe () . "'
					  , `contact`  = '" . $obj->getContact () . "'
				 WHERE `person_id` =  '" . $obj->getId () . "'";
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		$out = $db->exec ( $sql );
		if ($dbConfig instanceof DBConfig)
			$db = null;
	}
	
	public function insert($dbConfig, PersonEntity $obj) {
		$sql = "INSERT INTO `person_entity` 
					(   `person_id`
					  , `cnpj`
					  , `ie`
					  , `contact`
					 )
				VALUES 
					(
					   '" . $obj->getId () . "'
					 , '" . $obj->getCnpj () . "'
					 , '" . $obj->getIe () . "'
					 , '" . $obj->getContact () . "'
					 )";
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		$out = $db->exec ( $sql );
		if ($dbConfig instanceof DBConfig)
			$db = null;
		if($out == 0)
			throw new PDOException("Erro ao criar Pessoa Juridica!");
	}
	public function getSingle($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  p.id
					   	, p.name
						, p.email
						, p.type
						, p.address_id
						, p.phone_id
						, pj.cnpj
						, pj.ie
						, pj.contact
				  FROM 	person p
		    INNER JOIN	person_entity pj
					ON  pj.person_id = p.id
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
		
		$personEntity = null;
		$objDAO = $result->fetchObject ();
		if ($objDAO !== false && $objDAO !== null && $objDAO->id != null) {
			$personEntity = new PersonEntity ();
			$personEntity->setId ( $objDAO->id );
			$personEntity->setName ( $objDAO->name );
			$personEntity->setEmail ( $objDAO->email );
			$personEntity->setType ( $objDAO->type );
			$personEntity->setCnpj ( $objDAO->cnpj );
			$personEntity->setIe ( $objDAO->ie );
			$personEntity->setContact ( $objDAO->contact );
			
			if ($objDAO->address_id > 0) {
				$critAddr = new Criteria ();
				$critAddr->eq ( "addr.id", $objDAO->address_id );
				$addressDAO = AddressDAO::getInstance ();
				$address = $addressDAO->getSingle ( $dbConfig, $critAddr );
				if ($address != null) {
					$personEntity->setAddress ( $address );
				}
				$critAddr = null;
				$addressDAO = null;
			}
			if ($objDAO->phone_id != null) {
				$critPhone = new Criteria ();
				$critPhone->eq ( "pho.id", $objDAO->phone_id );
				$phoneDAO = PhoneDAO::getInstance ();
				$phone = $phoneDAO->getSingle ( $dbConfig, $critPhone );
				if ($phone != null) {
					$personEntity->setPhone ( $phone );
				}
				$phoneDAO = null;
				$critPhone = null;
			}
		}
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $personEntity;
	}
}
?>