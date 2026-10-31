<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PhoneDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonEntityDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonIndividualDAO.class.php");
class PersonDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function update($dbConfig, Person $obj) {	
		$personTypeDAO = null;
		$person = null;
		
		
		$address = $obj->getAddress ();
		$phone   = $obj->getPhone ();
		
		if ($obj->getType () == Person::PJ && $obj->getCnpj()!=""){
			$personTypeDAO = PersonEntityDAO::getInstance ();
		}elseif ($obj->getType () == Person::PF  && $obj->getCpf()!=""){
			$personTypeDAO = PersonIndividualDAO::getInstance ();
		}
		
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
			$db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
		
		try {			
			// Atualizar Telefone
			$phoneDAO = PhoneDAO::getInstance ();
			$phoneDAO->update ( $db, $phone );
			$obj->setPhone($phone);
			
			// Atualizar Endereço
			$addressDAO = AddressDAO::getInstance ();
			$addressDAO->update ( $db, $address );
			$obj->setAddress($address);
			
			// Atualizar pessoa
			$sql = "UPDATE `person` 
					   SET  `name`			= '" . Criteria::escapeString($obj->getName()) . "'
						  , `email`			= '" . Criteria::escapeString($obj->getEmail()) . "'
						  , `type`			= '" . Criteria::escapeString($obj->getType()) . "'
						  , `address_id`	= '" . Criteria::escapeString($obj->getAddress()->getId()) . "'
						  , `phone_id` 		= '" . Criteria::escapeString($obj->getPhone()->getId()) . "'
					 WHERE `id` 			= '" . Criteria::escapeString($obj->getId()) . "'";
				 
			$out = $db->exec($sql);			
			// Atualizar pessoa Fisica/Juridica				
			if($personTypeDAO!=null)
				$personTypeDAO->update( $db, $obj );
			
			if($dbConfig instanceof DBConfig){
				$db->commit ();
				$db		   = null;
			}
			
				
		} catch ( PDOException $e ) {
			if($dbConfig instanceof DBConfig){
				if ($db!=null && $db->inTransaction())
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	public function insert($dbConfig, Person $obj) {			
		$personTypeDAO = null;
		$person = null;
		$criteria = new Criteria ();
		
		
		$address = $obj->getAddress ();
		$phone   = $obj->getPhone ();
		
		if ($obj->getType () == Person::PJ && $obj->getCnpj()!=""){
			$personTypeDAO = PersonEntityDAO::getInstance ();
		}elseif ($obj->getType () == Person::PF  && $obj->getCpf()!=""){
			$personTypeDAO = PersonIndividualDAO::getInstance ();
		}
		
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
			$db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
		
		$criteria = null;
		
		try {			
			// Criar Telefone
			$phoneDAO = PhoneDAO::getInstance ();
			$phoneDAO->insert ( $db, $phone );
			$obj->setPhone($phone);
			
			// Criar Endereço
			$addressDAO = AddressDAO::getInstance ();
			$addressDAO->insert ( $db, $address );
			$obj->setAddress($address);
			
			// Criar pessoa
			$sql = "INSERT INTO `person` 
				(   `name`
				  , `email`
				  , `type`
				  , `address_id`
				  , `phone_id`
				 )
			VALUES 
				(
				   '" . Criteria::escapeString($obj->getName()) . "'
				 , '" . Criteria::escapeString($obj->getEmail()) . "'
				 , '" . Criteria::escapeString($obj->getType()) . "'
				 , '" . Criteria::escapeString($obj->getAddress()->getId()) . "'
				 , '" . Criteria::escapeString($obj->getPhone()->getId()) . "'
				 )";
				 
			$out = $db->exec($sql);
			$id = $db->lastInsertId();
			$obj->setId($id);
			
			// Criar pessoa Fisica/Juridica				
			if($personTypeDAO!=null)
				$personTypeDAO->insert( $db, $obj );
			
			if($dbConfig instanceof DBConfig){
				$db->commit ();
				$db		   = null;
			}
			
			if($out == 0)
				throw new PDOException("Erro ao criar Pessoa!");
			
				
		} catch ( PDOException $e ) {
			if($dbConfig instanceof DBConfig){
				if ($db!=null && $db->inTransaction())
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	
	public function getSingle($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  p.id
					   	, p.name
						, p.email
						, p.type
						, p.address_id
						, p.phone_id
				  FROM 	person p
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
	
		$person = null;
		$objDAO = $result->fetchObject ();
		if ($objDAO->id != null) {
			$person = new Person ();
			$person->setId ( $objDAO->id );
			$person->setName ( $objDAO->name );
			$person->setEmail ( $objDAO->email );
			$person->setType ( $objDAO->type );
				
			if ($objDAO->address_id > 0) {
				$critAddr = new Criteria ();
				$critAddr->eq ( "addr.id", $objDAO->address_id );
				$addressDAO = AddressDAO::getInstance ();
				$address = $addressDAO->getSingle ( $dbConfig, $critAddr );
				if ($address != null) {
					$person->setAddress ( $address );
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
					$person->setPhone ( $phone );
				}
				$phoneDAO = null;
				$critPhone = null;
			}
		}
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $person;
	}
}
?>