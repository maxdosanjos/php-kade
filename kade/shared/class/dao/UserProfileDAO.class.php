<?
	require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
	require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
	require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ProfileVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserProfileVO.class.php");
	class UserProfileDAO{
		private static $res = null;
		private function __construct() {
		}
		public static function getInstance() {
			if (self::$res == null)
				self::$res = new self ();
			return self::$res;
		}
		
		public function insert($dbConfig, UserProfile $obj) {
			$user 	 = $obj->getUser();
			$profile = $obj->getProfile();
			
			$sql = "INSERT INTO `user_profile` 
						(   `user_person_id`
						  , `profile_id`
						 )
					VALUES 
						(
						   '" . $user->getPerson ()->getId() . "'
						 , '" . $profile->getId () . "'
						 )";
			if ($dbConfig instanceof DBConfig)
				$db = DataBase::getInstance ( $dbConfig );
			else if ($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException ( "Conexão Inválida" );
				
				// echo $sql;
			
			$out = $db->exec ( $sql );			
			if ($dbConfig instanceof DBConfig)
				$db = null;			
			if($out == 0)
				throw new PDOException("Erro ao relacionar cliente X perfil!");
		}
		
		public function getByUser($dbConfig, User $user){	
			$_perfis = array();
		
			if($dbConfig instanceof DBConfig){
				$db        =  DataBase::getInstance($dbConfig);
			}else if($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException("Conexão Inválida");
				
			$sql = "
					SELECT 	  prf.id
							, prf.descr
							, prf.enabled
							, prf.super
							, prf.intern
					  FROM 	user_profile uprf
				INNER JOIN  profile prf
					    ON  prf.id = uprf.profile_id
					 WHERE  uprf.user_person_id = '".$user->getPerson()->getId()."'
					   AND	prf.enabled 		= '1'
					";			
			$result = $db->query ( $sql );		
			
			while($objDAO = $result->fetchObject())
			{		
				$profile = new Profile();
				$profile->setId($objDAO->id);
				$profile->setDescr($objDAO->descr);
				$profile->setEnabled($objDAO->enabled);
				$profile->setSuper($objDAO->super);
				$profile->setIntern($objDAO->intern);
				
				$userProfile = new UserProfile($user,$profile);			
				$_perfis[]	 = $userProfile;
			}
			if ($dbConfig instanceof DBConfig)
				$db = null;			
			
			return $_perfis;
			
		}
	}
?>