<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleContactVO.class.php");
require_once (dirname ( __FILE__ ) .  "/PhoneDAO.class.php");
require_once (dirname ( __FILE__ ) .  "/PersonDAO.class.php");
require_once (dirname ( __FILE__ ) .  "/PersonEntityDAO.class.php");
require_once (dirname ( __FILE__ ) .  "/PersonIndividualDAO.class.php");
class VehicleContactDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function insert($dbConfig, VehicleContact $obj) {
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
			$db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
		
		$phone		    = $obj->getPhoneContact();
		$person 	    = $obj->getPerson ();
		$personDb		= null;
		
		try {			
			// Criar Telefone
			$phoneDAO = PhoneDAO::getInstance ();
			$phoneDAO->insert ( $db, $phone );
			
			// Criar Pessoa
			$personTypeDAO = null;
			$criteria = new Criteria();
			
			if ($person->getType () == Person::PJ && $person->getCnpj()!=""){
				$personTypeDAO = PersonEntityDAO::getInstance ();
				$criteria->eq("pj.cnpj",$person->getCnpj());
			}elseif ($person->getType () == Person::PF  && $person->getCpf()!=""){
				$personTypeDAO = PersonIndividualDAO::getInstance ();
				$criteria->eq("pf.cpf",$person->getCpf());
			}
			
			if($personTypeDAO!=null)
				$personDb = $personTypeDAO->getSingle ( $db, $criteria );
			
			if($personDb!=null && $personDb->getId() > 0)
				$person->setId($personDb->getId());
			else
			{
				$person 	    = $obj->getPerson ();
				$personDAO = PersonDAO::getInstance ();
				$personDAO->insert ( $db, $person );	
			}
			
			
			
			
			$obj->setPhoneContact($phone);
			$obj->setPerson($person);
			
			$sql = "INSERT INTO `vehicle_contact` 
					(   `person_id`
					  , `vehicle_traveling_id`
					  , `phone_contact_id`
					  , `name`
					 )
				VALUES 
					(
					   '".$obj->getPerson()->getId()."'					 
					 , '".$obj->getVehicleTraveling()->getId()."'
					 , '".$obj->getPhoneContact()->getId()."'
					 , '".$obj->getName()."'
					 )";			
			$out = $db->exec($sql);
			
			
			
								
			if($out == 0)
				throw new PDOException("Erro ao criar Pessoa de Contato!");
			
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
	public function getSingle($dbConfig, Criteria $criteria) {
	}
}
?>