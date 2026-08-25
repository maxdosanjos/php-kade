<?
	require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
	require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
	require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ProfileVO.class.php");
	
	class ProfileDAO{
		private static $res = null;
		private function __construct() {
		}
		public static function getInstance() {
			if (self::$res == null)
				self::$res = new self ();
			return self::$res;
		}
		
		public function getSingle($dbConfig, Criteria $criteria = null) 
		{
			$sql = "
					SELECT 	  prof.id
							, prof.descr
							, prof.enabled
							, prof.super
							, prof.intern
					  FROM 	profile prof
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
			
			$objDAO = $result->fetchObject ();
			$profile = null;
			if ( $objDAO->id != null ) 
			{
				$profile = new Profile ();
				$profile->setId ( $objDAO->id );
				$profile->setDescr ( $objDAO->descr );
				$profile->setEnabled ( $objDAO->enabled );
				$profile->setSuper ( $objDAO->super );
				$profile->setIntern ( $objDAO->intern );
			}
			return $profile;
		}
	}
?>