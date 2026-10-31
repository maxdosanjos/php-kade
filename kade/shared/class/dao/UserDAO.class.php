<?
	require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
	require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
	require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserProfileVO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/UserProfileDAO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonEntityDAO.class.php");
	require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PersonIndividualDAO.class.php");

	class UserDAO{
	
		private static $res = null;
		private function __construct() {
		}
		public static function getInstance() {
			if (self::$res == null)
				self::$res = new self ();
			return self::$res;
		}
		
		public function registerLinkConfirm($dbConfig,User $user,$md5){
			$inTransaction = false;
			
			if($dbConfig instanceof DBConfig){
				$db        =  DataBase::getInstance($dbConfig);
				$inTransaction = $db->beginTransaction();
			}else if($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException("Conexão Inválida");
			
			try
			{
				$sql = "DELETE
						  FROM `link_confirm`
						 WHERE user_person_id = ?";
				$stmt = $db->prepare ( $sql );
				$stmt->execute ( array ( $user->getPerson()->getId() ) );

				$sql = "INSERT INTO `link_confirm`
							(   `user_person_id`
							  , `hash`
							 )
						VALUES
							( ?, ? )";

				$stmt = $db->prepare ( $sql );
				$stmt->execute ( array ( $user->getPerson()->getId(), $md5 ) );
				$out = $stmt->rowCount ();
				if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
				}
				
				if($out == 0)
					throw new PDOException("Erro ao criar link!");
				
			} catch ( PDOException $e ) {
				if($dbConfig instanceof DBConfig){
					if ($db!=null && $inTransaction)
						$db->rollback ();
					$db = null;
				}
				throw $e;
			}
			
		}
		
		public function updateStatus($dbConfig,User $user){
				
			if($dbConfig instanceof DBConfig){
				$db        =  DataBase::getInstance($dbConfig);
			}else if($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException("Conexão Inválida");
				
			try
			{
				if($user->getText()!=""){
					$sqlText = ", text = '".$user->getText()."'";
				}
				$sql = " UPDATE user
								SET status 	  = '".$user->getStatus()."'
								".$sqlText."
							  WHERE person_id = '".$user->getPerson()->getId()."'
						";
				$out = $db->exec ( $sql );
				if($dbConfig instanceof DBConfig){
					$db		   = null;
				}		
			} catch ( PDOException $e ) {
				if($dbConfig instanceof DBConfig){
					$db = null;
				}
				throw $e;
			}
				
		}
		
		public function confirmLink($dbConfig,$vua){
			try
			{
				$inTransaction = false;
				
				$sql = "
						SELECT 	    `user_person_id`
								  , `hash`
						  FROM 	link_confirm link
						 WHERE  hash = ?
						";


				if($dbConfig instanceof DBConfig){
					$db        =  DataBase::getInstance($dbConfig);
					$inTransaction = $db->beginTransaction();
				}else if($dbConfig instanceof DataBase)
					$db = $dbConfig;
				else
					throw new PDOException("Conexão Inválida");


				$result = $db->prepare ( $sql );
				$result->execute ( array ( $vua ) );

				$phone = null;
				$objDAO = $result->fetchObject();
				if($objDAO && $objDAO->user_person_id!=null)
				{
					$sql = " DELETE
							   FROM link_confirm
							  WHERE hash = ?
						";
					$stmt = $db->prepare ( $sql );
					$stmt->execute ( array ( $vua ) );
					$out = $stmt->rowCount ();
					if($out == 0)
						throw new PDOException("Erro ao confirmar usuário!");
					
					return $objDAO->user_person_id;
				}
				else
					throw new PDOException ( "URL inválida para confirmação" );
				
				if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
				}
				
			} catch ( PDOException $e ) {
				if($dbConfig instanceof DBConfig){
					if ($db!=null && $inTransaction)
						$db->rollback ();
					$db = null;
				}
				throw $e;
			}
		
		}
		
		public function confirmUserByLink($dbConfig,$vua){
			try
			{
				$inTransaction = false;
				if($dbConfig instanceof DBConfig){
					$db        =  DataBase::getInstance($dbConfig);
					$inTransaction = $db->beginTransaction();
				}else if($dbConfig instanceof DataBase)
					$db = $dbConfig;
				else
					throw new PDOException("Conexão Inválida");
				
				$user_person_id = $this->confirmLink($dbConfig,$vua);
				if($user_person_id > 0)
				{
						
					$sql = " UPDATE user
								SET status 	  = '".User::CHEK."'
							  WHERE person_id = '".$user_person_id."'
						";
					$out = $db->exec ( $sql );		
					if($out == 0)
						throw new PDOException("Erro ao confirmar usuário!");
				}
				else
					throw new PDOException ( "URL inválida para confirmação" );
				
				if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
				}
				
			} catch ( PDOException $e ) {
				if($dbConfig instanceof DBConfig){
					if ($db!=null && $inTransaction)
						$db->rollback ();
					$db = null;
				}
				throw $e;
			}
		
		}
		
		public function confirmLinkPwd($dbConfig,$vua){
			try
			{
				$inTransaction = false;
				
				// Apenas valida o link; o hash só é consumido em resetPwdByLink
				$user_person_id = $this->findLinkPersonId($dbConfig,$vua);
				if($user_person_id > 0)
				{
					$criteria = new Criteria();
					$criteria->eq("usr.person_id",$user_person_id);
					$user = $this->getSingle($dbConfig,$criteria);
					
					if($user == null)
						throw new PDOException("Erro ao confirmar usuário!");
						
					return $user;
				}
				else
					throw new PDOException ( "URL inválida para confirmação" );
				
				if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
				}
				
			} catch ( PDOException $e ) {
				if($dbConfig instanceof DBConfig){
					if ($db!=null && $inTransaction)
						$db->rollback ();
					$db = null;
				}
				throw $e;
			}
		
		}
	
		public function getSingle($dbConfig, Criteria $criteria) {
			$user = null;
			
			if($dbConfig instanceof DBConfig){
				$db        =  DataBase::getInstance($dbConfig);
			}else if($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException("Conexão Inválida");
			
			$sql = "
					SELECT 	usr.person_id
						  , usr.login
						  , usr.password
						  , usr.status
						  , usr.text
						  , usr.date_cad
						  , per.type 
					  FROM 	user usr
				INNER JOIN  person per
						ON  per.id 		  = usr.person_id
					";
			if ($criteria != null) {
				$sql .= $criteria->getWHERE ();
				$sql .= $criteria->getORDER ();
				$sql .= $criteria->getLIMIT ();
			}
			$result = $db->query ( $sql );		
			
			
			$phone = null;		
			$objDAO = $result->fetchObject();
			if($objDAO->person_id > 0)
			{		
				$user = new User ();
				$user->setLogin ( $objDAO->login );
				$user->setPassword ( $objDAO->password );
				$user->setStatus ( $objDAO->status );
				$user->setText ( $objDAO->text );
				$user->setDateCad ( new DateTimeCustom($objDAO->date_cad) );
				
				$critPers 	   = new Criteria();
				$personTypeDAO = null;
				
				if ($objDAO->type == Person::PJ){
					$personTypeDAO = PersonEntityDAO::getInstance ();
					$critPers->eq("pj.person_id",$objDAO->person_id);
				}elseif ($objDAO->type == Person::PF){
					$personTypeDAO = PersonIndividualDAO::getInstance ();
					$critPers->eq("pf.person_id",$objDAO->person_id);
				}
				
				if($personTypeDAO!=null)
					$person = $personTypeDAO->getSingle ( $db, $critPers );				
				
				if($person==null){
					throw new PDOException("Usuário não encontrado #sem_pessoa");
				}
				$user->setPerson($person);
				
				$critPers = null;
				
				$userProfileDAO = UserProfileDAO::getInstance ();			

				$_profiles = $userProfileDAO->getByUser($db,$user);				
				
				$user->setProfileList( $_profiles );
			}
			
			if ($dbConfig instanceof DBConfig)
				$db = null;			
			
			return $user;
		}
			
		public function insert($dbConfig, User $obj) {
			$sql = "INSERT INTO `user` 
						(   `person_id`
						  , `login`
						  , `password`
						  , `status`
						 )
					VALUES 
						(
						   '" . $obj->getPerson()->getId() . "'
						 , '" . $obj->getLogin () . "'
						 , MD5('" . $obj->getPassword () . "')
						 , '" . $obj->getStatus () . "'
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
				throw new PDOException("Erro ao criar Usuário!");
		}
		
		/**
		 * Retorna o person_id associado ao hash sem consumir o link
		 */
		public function findLinkPersonId($dbConfig, $vua) {
			if (! preg_match ( "/^[a-f0-9]{32}$/", ( string ) $vua ))
				throw new PDOException ( "URL inválida para confirmação" );

			if ($dbConfig instanceof DBConfig)
				$db = DataBase::getInstance ( $dbConfig );
			else if ($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException ( "Conexão Inválida" );

			$stmt = $db->prepare ( "SELECT `user_person_id` FROM `link_confirm` WHERE `hash` = ?" );
			$stmt->execute ( array ( $vua ) );
			$objDAO = $stmt->fetchObject ();
			if (! $objDAO || $objDAO->user_person_id <= 0)
				throw new PDOException ( "URL inválida para confirmação" );

			return ( int ) $objDAO->user_person_id;
		}

		/**
		 * Redefine a senha do usuário dono do hash e consome o link.
		 * O controle de transação fica a cargo de quem chama.
		 */
		public function resetPwdByLink(DataBase $db, $vua, $password) {
			$person = new Person ();
			$person->setId ( $this->findLinkPersonId ( $db, $vua ) );

			$user = new User ();
			$user->setPerson ( $person );
			$user->setPassword ( $password );
			$this->updatePwd ( $db, $user );

			$stmt = $db->prepare ( "DELETE FROM `link_confirm` WHERE `hash` = ?" );
			$stmt->execute ( array ( $vua ) );
			if ($stmt->rowCount () == 0)
				throw new PDOException ( "URL inválida para confirmação" );
		}

		public function updatePwd($dbConfig, User $obj) {
			$sql = "UPDATE `user`
					   SET  `password` = MD5(?)
					 WHERE  `person_id` = ?
					";
			if ($dbConfig instanceof DBConfig)
				$db = DataBase::getInstance ( $dbConfig );
			else if ($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException ( "Conexão Inválida" );
			$stmt = $db->prepare ( $sql );
			$stmt->execute ( array ( $obj->getPassword (), $obj->getPerson()->getId() ) );
			$out = $stmt->rowCount ();
			if ($dbConfig instanceof DBConfig)
				$db = null;			
			/*if($out == 0)
				throw new PDOException("Erro ao atualizar senha do Usuário!");*/
		}
		
		
		
	}
?>