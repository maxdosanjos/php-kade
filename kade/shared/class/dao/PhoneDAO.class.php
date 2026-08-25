<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
class PhoneDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function insert($dbConfig, Phone $obj) {
		$sql = "INSERT INTO `phone` 
					(   `phone`
					  , `ddd`
					  , `ddi`
					 )
				VALUES 
					(
					   '".$obj->getPhone()."'
					 , '".$obj->getDdd()."'
					 , '".$obj->getDdi()."'
					 )";
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");

		//echo $sql;
		$out = $db->exec($sql);
		$id = $db->lastInsertId();
		
		if($dbConfig instanceof DBConfig)
			$db		   = null;
		$obj->setId($id);		
		
		if($out == 0)
			throw new PDOException("Erro ao criar Telefone!");
		
	}
	public function update($dbConfig, Phone $obj) {
		$sql = "UPDATE 	`phone` 
				   SET  `phone` = '".$obj->getPhone()."'
					  , `ddd`	= '".$obj->getDdd()."'
					  , `ddi`	= '".$obj->getDdi()."'
				 WHERE `id`		= '".$obj->getId()."'";
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");

		//echo $sql;
		$out = $db->exec($sql);		
		if($dbConfig instanceof DBConfig)
			$db		   = null;
		
	}
	public function getSingle($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  pho.id
					   	, pho.phone
						, pho.ddd
						, pho.ddi
				  FROM 	phone pho
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
		
		$phone = null;		
		$objDAO = $result->fetchObject();
		if($objDAO->id!=null){
			$phone = new Phone();
			$phone->setId($objDAO->id);
			$phone->setPhone($objDAO->phone);
			$phone->setDdd($objDAO->ddd);
			$phone->setDdi($objDAO->ddi);
		}
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $phone;
	}
}
?>