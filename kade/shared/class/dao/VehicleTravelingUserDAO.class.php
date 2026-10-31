<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/VehicleTravelingUserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/VehicleTravelingDAO.class.php");
class VehicleTravelingUserDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function insert($dbConfig, VehicleTravelingUser $obj) {
		if ($dbConfig instanceof DBConfig) 
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
		
		// Criar Utilizador da informação
		$sql = "INSERT INTO `vehicle_traveling_user` 
				(   `vehicle_traveling_id`
				  , `user_person_id`
				  , `status`
				 )
			VALUES 
				(
				   '" . Criteria::escapeString($obj->getVehicleTraveling ()->getId ()) . "'
				 , '" . Criteria::escapeString($obj->getUser ()->getPerson ()->getId()) . "'
				 , '" . Criteria::escapeString($obj->getStatus ()) . "'
				 )";
		
		$out = $db->exec ( $sql );
		if ($out == 0)
			throw new PDOException ( "Erro ao registrar Utilizador da infomação!" );
		
		if ($dbConfig instanceof DBConfig) {
			$db = null;
		}
	}
}
?>