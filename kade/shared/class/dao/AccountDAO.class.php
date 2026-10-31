<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AccountVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/ClientDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/InstallmentDAO.class.php");
class AccountDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function getArray($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	acc.id
					  , acc.date_hr_proc
					  , acc.status
					  , acc.validate_month
					  , acc.client_user_person_id
				  FROM 	account acc
			INNER JOIN  client  cli
					ON  cli.user_person_id = acc.client_user_person_id
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
		
		$clientDAO = ClientDAO::getInstance ();
		$_accounts = array ();
		$result = $db->query ( $sql );
		while ( $objDAO = $result->fetchObject () ) {
			if ($objDAO->id == null)
				continue;
			
			$account = new Account ();
			$account->setId ( $objDAO->id );
			$account->setDtHrProc ( new DateTimeCustom ( $objDAO->date_hr_proc ) );
			$account->setStatus ( $objDAO->status );
			$account->setValidateMonth ( $objDAO->validate_month );
			
			$critClient = new Criteria ();
			$critClient->eq ( "cli.user_person_id", $objDAO->client_user_person_id );
			$client = $clientDAO->getSingle ( $db, $critClient );
			$account->setClient ( $client );
			
			$_accounts [] = $account;
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $_accounts;
	}
	public function count($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	COUNT(acc.id) AS total
				  FROM 	account acc
			INNER JOIN  client  cli
					ON  cli.user_person_id = acc.client_user_person_id
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
	public function countByInterval($dbConfig, Client $cliente, DateTimeCustom $dtInit, DateTimeCustom $dtEnd){
		$criteria = new Criteria();
		$criteria->between("ins.due_date",$dtInit,$dtEnd,Criteria::FIELD_DATETIME);
		$criteria->eq("acc.client_user_person_id",$cliente->getUser()->getPerson()->getId());
		$criteria->eq("acc.status",Account::UNPAID);
		
		$sql = "
			    SELECT 	COUNT(acc.id) AS total
				  FROM 	account acc
			INNER JOIN  installment ins
					ON  ins.account_id = acc.id
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
	public function update($dbConfig, Account $obj) {
		$inTransaction = false;
		
		$obj->reloadStatus();
		
		if ($dbConfig instanceof DBConfig) {
			$db = DataBase::getInstance ( $dbConfig );
			$inTransaction = $db->beginTransaction ();
		} else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		try {
			//Atualizar Conta
			$sql = "UPDATE `account`
					   SET `status` =  '" . Criteria::escapeString($obj->getStatus()) . "'
				 	 WHERE `id`		=  '" . Criteria::escapeString($obj->getId()) . "'
				 ";
				
			$out = $db->exec($sql);				
			/*if($out == 0)
				throw new PDOException("Erro ao atualizar Conta!");*/
			
			//Atualizar Parcelas
			$_installments = $obj->getInstallmentList();
			$installmentDAO = InstallmentDAO::getInstance();
			foreach ($_installments as $installment){
				$installmentDAO->update($db, $installment);
			}
			
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
	public function insert($dbConfig, Account $obj) {
		$inTransaction = false;
		
		if ($dbConfig instanceof DBConfig) {
			$db = DataBase::getInstance ( $dbConfig );
			$inTransaction = $db->beginTransaction ();
		} else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		try {
			
			//Criar Conta
			$sql = "INSERT INTO `account`
				(	 `validate_month`
					,`client_user_person_id`
				)
					VALUES
				(
				   '" . Criteria::escapeString($obj->getValidateMonth()) . "'
				 , '" . Criteria::escapeString($obj->getClient()->getUser()->getPerson()->getId()) . "'
				 )";
			
			$out = $db->exec($sql);
			$id = $db->lastInsertId();
			
			$obj->setId($id);
			
			if($out == 0)
				throw new PDOException("Erro ao registrar Veículo!");
				
			//Criar Parcelas
			$_installments = $obj->getInstallmentList();
			$installmentDAO = InstallmentDAO::getInstance();
			foreach ($_installments as $installment){
				$installmentDAO->insert($db, $installment);
			}
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
	public function getSingle($dbConfig, Criteria $criteria) {
		$sql = "
			    SELECT 	acc.id
					  , acc.date_hr_proc
					  , acc.status
					  , acc.validate_month
					  , acc.client_user_person_id
				  FROM 	account acc
			INNER JOIN  client  cli
					ON  cli.user_person_id = acc.client_user_person_id
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
		
		$clientDAO = ClientDAO::getInstance ();
		$_accounts = array ();
		$result = $db->query ( $sql );
		$objDAO = $result->fetchObject ();
		
		$account = new Account ();
		$account->setId ( $objDAO->id );
		$account->setDtHrProc ( new DateTimeCustom ( $objDAO->date_hr_proc ) );
		$account->setStatus ( $objDAO->status );
		$account->setValidateMonth ( $objDAO->validate_month );
		
		$critClient = new Criteria ();
		$critClient->eq ( "cli.user_person_id", $objDAO->client_user_person_id );
		$client = $clientDAO->getSingle ( $db, $critClient );
		$account->setClient ( $client );
		
		
		$installmentDAO = InstallmentDAO::getInstance();
		$criteria = new Criteria();
		$criteria->eq("ins.account_id",$account->getId());
		$_installment   = $installmentDAO->getArray($db,$criteria);
		
		$account->setInstallmentList($_installment);
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		return $account;
	}
}
?>