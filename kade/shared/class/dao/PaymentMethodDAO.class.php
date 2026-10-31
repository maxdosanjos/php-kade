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
		// Sem a chave na sessão $_result ficava null e o sizeof() era fatal no PHP 8
		$_result = array ();
		if (! empty ( $_SESSION ["list_payment_method"] )) {
			$_tmp = @unserialize ( $_SESSION ["list_payment_method"] );
			if (is_array ( $_tmp ))
				$_result = $_tmp;
		}
		if (count ( $_result ) > 0)
			return $_result;

		$_result = $this->getArray ( $dbConfig );
		if (! is_array ( $_result ))
			$_result = array ();
		// Lista vazia não vai para o cache, para aparecer assim que houver formas cadastradas
		if (count ( $_result ) > 0)
			$_SESSION ["list_payment_method"] = serialize ( $_result );
		return $_result;
	}
}
?>