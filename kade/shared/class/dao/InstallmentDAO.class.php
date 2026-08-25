<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AccountVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PaymentMethodVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/InstallmentVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/ClientDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PaymentMethodDAO.class.php");
class InstallmentDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	
	public function update($dbConfig, Installment $obj) {
		$inTransaction = false;
	
		if ($dbConfig instanceof DBConfig) {
			$db = DataBase::getInstance ( $dbConfig );
			$inTransaction = $db->beginTransaction ();
		} else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		try {
			$fieldsPayment = "";
			if($obj->getStatus() == Installment::PAID){
				$fieldsPayment = ",`payment_value` 	   = '".$obj->getPaymentValue()."'
								  ,`payment_date`  	   = '".$obj->getPaymentDate()->format("Y-m-d")."'
								  ,`payment_method_id` = '".$obj->getPaymentMethod()->getId()."'";
			}
			//Atualizar Parcelas
			$sql = "UPDATE `installment`
					   SET `status` 		=  '".$obj->getStatus()."'
					   	    ".$fieldsPayment."
				 	 WHERE `account_id`		=  '".$obj->getAccount()->getId()."'
				 	   AND `id`				=  '".$obj->getId()."'
				 ";
	
			$out = $db->exec($sql);	
			/*if($out == 0)
				throw new PDOException("Erro ao atualizar Parcela!");*/
			if ($dbConfig instanceof DBConfig) {
				$db->commit ();
				$db = null;
			}
		} catch ( PDOException $e ) {
			if ($dbConfig instanceof DBConfig) {
				if ($db != null && $inTransaction)
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	public function insert($dbConfig, Installment $obj) {
		$inTransaction = false;
	
		if ($dbConfig instanceof DBConfig) {
			$db = DataBase::getInstance ( $dbConfig );
			$inTransaction = $db->beginTransaction ();
		} else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		try {
				
						//Criar Parcelas
			$sql = "INSERT INTO `installment`
				(	 `account_id`
				   , `id`
				   , `value`
				   , `due_date`
				)
					VALUES
				(
				   '".$obj->getAccount()->getId()."'
				 , '".$obj->getId()."'
				 , '".$obj->getValue()."'
				 , '".$obj->getDueDate()->format("Y-m-d")."'
				 )";
			
			$out = $db->exec($sql);				
			if($out == 0)
				throw new PDOException("Erro ao registrar Parcela!");

				
			if ($dbConfig instanceof DBConfig) {
				$db->commit ();
				$db = null;
			}
		} catch ( PDOException $e ) {
			if ($dbConfig instanceof DBConfig) {
				if ($db != null && $inTransaction)
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	
	public function getArray($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	ins.account_id
					  , ins.id
					  , ins.value
					  , ins.payment_value
					  , ins.due_date
					  , ins.payment_date
					  , ins.status
				      , ins.payment_method_id
					  , pay.name
				  FROM 	installment ins
			INNER JOIN  account acc
					ON  acc.id = ins.account_id
			 LEFT JOIN  payment_method  pay
					ON  pay.id = ins.payment_method_id
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
	
		$_installs = array ();
		$result = $db->query ( $sql );
		while ( $objDAO = $result->fetchObject () ) {
			if ($objDAO->id == null)
				continue;
				
			$account     = new Account();
			$account->setId($objDAO->account_id);
			
			$installment = new Installment ();
			$installment->setId ( $objDAO->id );
			$installment->setValue ( $objDAO->value );
			$installment->setDueDate ( new DateTimeCustom ( $objDAO->due_date ) );
			$installment->setStatus ( $objDAO->status );			
			if($objDAO->payment_date != null)
				$installment->setPaymentDate ( new DateTimeCustom ( $objDAO->payment_date ) );
			$installment->setPaymentValue ( $objDAO->payment_value );
			
			if($objDAO->payment_method_id != null){
				$paymentMethod = new PaymentMethod();
				$paymentMethod->setId($objDAO->payment_method_id);
				$paymentMethod->setName($objDAO->name);
				$installment->setPaymentMethod($paymentMethod);
			}
			$installment->setAccount($account);
			
				
			$_installs [] = $installment;
		}
	
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $_installs;
	}
	public function count($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	COUNT(ins.id) AS total
				  FROM 	installment ins 
			INNER JOIN  account acc
					ON  acc.id = ins.account_id
			 LEFT JOIN  payment_method  pay
					ON  pay.id = ins.payment_method_id
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
	
		$objDAO = $result->fetchObject ();
	
		return $objDAO->total;
	}
}
?>