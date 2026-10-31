<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AddressDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PhoneDAO.class.php");
class PersonIndividualDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function update($dbConfig, PersonIndividual $obj) {
		
		$dateBirth = "NULL";
		if($obj->getDateBirth()!=null)
			$dateBirth = "'" . Criteria::escapeString($obj->getDateBirth()->format("Y-m-d")) . "'";
		
		$sql = "UPDATE `person_individual` 
				   SET `gender`		=  '" . Criteria::escapeString($obj->getGender()) . "'
					 , `dateBirth`	= ".$dateBirth."
			  WHERE `person_id`		= '" . Criteria::escapeString($obj->getId()) . "'";
			
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");		
			
		$out = $db->exec($sql);				
		if($dbConfig instanceof DBConfig)
			$db		   = null;
	}
	public function insert($dbConfig, PersonIndividual $obj) {
		
		$dateBirth = "NULL";
		if($obj->getDateBirth()!=null)
			$dateBirth = "'" . Criteria::escapeString($obj->getDateBirth()->format("Y-m-d")) . "'";
		
		$sql = "INSERT INTO `person_individual` 
					(   `person_id`
					  , `cpf`
					  , `gender`
					  , `dateBirth`
					 )
				VALUES 
					(
					   '" . Criteria::escapeString($obj->getId()) . "'
					 , '" . Criteria::escapeString($obj->getCpf()) . "'
					 , '" . Criteria::escapeString($obj->getGender()) . "'
					 , ".$dateBirth."
					 )";
			
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");		
			
		$out = $db->exec($sql);				
		if($dbConfig instanceof DBConfig)
			$db		   = null;
		if($out == 0)
			throw new PDOException("Erro ao criar Pessoa Fisica!");
	}
	public function getSingle($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  p.id
					   	, p.name
						, p.email
						, p.type
						, p.address_id
						, p.phone_id
						, pf.cpf
						, pf.gender
						, pf.dateBirth
				  FROM 	person p
		    INNER JOIN	person_individual pf
					ON  pf.person_id = p.id
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
		
		$personIndividual = null;		
		$objDAO = $result->fetchObject();
		if($objDAO !== false && $objDAO !== null && $objDAO->id != null){
			$personIndividual = new PersonIndividual( );
			$personIndividual->setId($objDAO->id);
			$personIndividual->setName($objDAO->name);
			$personIndividual->setEmail($objDAO->email);
			$personIndividual->setType($objDAO->type);
			$personIndividual->setCpf($objDAO->cpf);
			$personIndividual->setGender($objDAO->gender);
			$personIndividual->setDateBirth(new DateTimeCustom($objDAO->dateBirth));
			
			if($objDAO->address_id > 0){
				$critAddr = new Criteria();
				$critAddr->eq("addr.id",$objDAO->address_id);
				$addressDAO = AddressDAO::getInstance();
				$address 	= $addressDAO->getSingle($dbConfig, $critAddr);
				if($address!=null){
					$personIndividual->setAddress($address);
				}
				$critAddr   = null;
				$addressDAO = null;
			}
			if($objDAO->phone_id!=null){
				$critPhone = new Criteria();
				$critPhone->eq("pho.id",$objDAO->phone_id);
				$phoneDAO = PhoneDAO::getInstance();
				$phone 	= $phoneDAO->getSingle($dbConfig, $critPhone);
				if($phone!=null){
					$personIndividual->setPhone($phone);
				}
				$phoneDAO  = null;
				$critPhone = null;
			}
			
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $personIndividual;
	}
}
?>