<?
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ClientVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ProfileVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserProfileVO.class.php");
require_once (dirname ( __FILE__ ) . "/AddressDAO.class.php");
require_once (dirname ( __FILE__ ) . "/PhoneDAO.class.php");
require_once (dirname ( __FILE__ ) . "/PersonDAO.class.php");
require_once (dirname ( __FILE__ ) . "/PersonEntityDAO.class.php");
require_once (dirname ( __FILE__ ) . "/PersonIndividualDAO.class.php");
require_once (dirname ( __FILE__ ) . "/UserDAO.class.php");
require_once (dirname ( __FILE__ ) . "/ProfileDAO.class.php");
require_once (dirname ( __FILE__ ) . "/UserProfileDAO.class.php");
class ClientDAO{
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}	
	public function getArray($dbConfig, Criteria $criteria, $personType = Person::PJ ){
		
		$clients = array();
		
		$sql = "SELECT	cli.user_person_id		
      			  FROM 	client cli
			INNER JOIN  user   usr
					ON  usr.person_id = cli.user_person_id
			INNER JOIN  person per
					ON  per.id 		  = usr.person_id
			INNER JOIN	phone  pho
					ON	pho.id		  = per.phone_id
			INNER JOIN	address addr
					ON	addr.id		  = per.address_id
				 ";
		
		switch ($personType) {
			case Person::PF :
				$sql .= " INNER JOIN  person_individual pf
								ON  pf.person_id = cli.user_person_id
							";
				break;
			case Person::PJ :
				$sql .= " INNER JOIN  person_entity pj
								ON  pj.person_id = cli.user_person_id
							";
		
				break;
		}
		
		$sql 	 .= $criteria->toString();
	
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
	
		$result    =  $db->query($sql);
		
		
		while($objDAO = $result->fetchObject())
		{
			if($objDAO->user_person_id != null)
			{
				$critUser = new Criteria();
				$critUser->eq("usr.person_id",$objDAO->user_person_id);
				$userDAO = UserDAO::getInstance ();
				$user = $userDAO->getSingle ( $db, $critUser );
				if($user==null || $user->getLogin() == ""){
					throw new PDOException("Cliente não encontrado!! #Sem usuário");
				}
			
				$client = new Client();
				$client->setUser($user);
				
				$clients[] = $client;
			}
		}
		
		if($dbConfig instanceof DBConfig)
			$db		   = null;
		
	
		return $clients;
	
	}
	public function count($dbConfig, Criteria $criteria, $personType ){
	
		$sql = "SELECT count(cli.user_person_id) AS 'total'			
      			  FROM 	client cli
			INNER JOIN  user   usr
					ON  usr.person_id = cli.user_person_id
			INNER JOIN  person per
					ON  per.id 		  = usr.person_id
			INNER JOIN	phone  pho
					ON	pho.id		  = per.phone_id
			INNER JOIN	address addr
					ON	addr.id		  = per.address_id
				 ";
	
		switch ($personType) {
			case Person::PF :
				$sql .= " INNER JOIN  person_individual pf
								ON  pf.person_id = cli.user_person_id
							";
				break;
			case Person::PJ :
				$sql .= " INNER JOIN  person_entity pj
								ON  pj.person_id = cli.user_person_id
							";
	
				break;
		}
	
		$sql 	 .= $criteria->toString();
	
		if($dbConfig instanceof DBConfig)
			$db        =  DataBase::getInstance($dbConfig);
		else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
	
		$result    =  $db->query($sql);
	
		if($dbConfig instanceof DBConfig)
			$db		   = null;
		$objDAO   = $result->fetchObject();
	
		return $objDAO->total;
	
	}
	public function getSingle($dbConfig, Criteria $criteria) {
		$client = null;
		
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
			
		$sql = "
			    SELECT 	cli.user_person_id
				  FROM 	client cli 
		    INNER JOIN  user   usr
				    ON  usr.person_id = cli.user_person_id
				  
				";
		if ($criteria != null) {
			$sql .= $criteria->getWHERE ();
			$sql .= $criteria->getORDER ();
			$sql .= $criteria->getLIMIT ();
		}
		
		
		$result = $db->query ( $sql );		
		
		$objDAO = $result->fetchObject();
		if($objDAO && $objDAO->user_person_id != null)
		{		
			$critUser = new Criteria();
			$critUser->eq("usr.person_id",$objDAO->user_person_id);
			$userDAO = UserDAO::getInstance ();
			$user = $userDAO->getSingle ( $db, $critUser );	
			if($user==null || $user->getLogin() == ""){
				throw new PDOException("Cliente não encontrado!! #Sem usuário");
			}
		
			$client = new Client();	
			$client->setUser($user);
		}
		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		return $client;
		
	}
	
	public function verifyExist($dbConfig, Client $obj) {
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
		
		$sql = "
				SELECT 	COUNT(cli.user_person_id) AS total
				  FROM 	client cli
			INNER JOIN  user usr
					ON  usr.person_id = cli.user_person_id
			INNER JOIN  person per
					ON  per.id 		  = usr.person_id
				";
		switch ($obj->getUser()->getPerson()->getType()) {
			case Person::PF :
				$sql .= " INNER JOIN  person_individual pf
								ON  pf.person_id = cli.user_person_id
							 WHERE  pf.cpf 		 = '" . Criteria::escapeString($obj->getUser()->getPerson()->getCpf()) . "'
							";
				break;
			case Person::PJ :
				$sql .= " INNER JOIN  person_entity pj
								ON  pj.person_id = cli.user_person_id
							 WHERE  pj.cnpj 	 = '" . Criteria::escapeString($obj->getUser()->getPerson()->getCnpj()) . "'
							";
				
				break;
			default :
				throw new PDOException ( "Tipo de Pessoa inválida" );
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );
		
		$result = $db->query ( $sql );
		
		$address = null;
		$objDAO = $result->fetchObject ();
		$total  = $objDAO->total;
		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		return $total;		
	}
	
	public function update($dbConfig, Client $obj){
		$inTransaction = false;
		
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
			$inTransaction = $db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
			
		$user 	    	= $obj->getUser ();
		$person 	    = $user->getPerson ();
		
		try {						
			
			if(User::isUserSuper() && $user->getPerson()->getId() != User::getLogged()->getPerson()->getId()){
				$userDAO = UserDAO::getInstance ();
				$userDAO->updateStatus($db,$user);
			}
			// Atualiza Pessoa			
			$personDAO = PersonDAO::getInstance ();
			$personDAO->update ( $db, $person );									
		} catch ( PDOException $e ) {
			if($dbConfig instanceof DBConfig){
				if ($db!=null && $inTransaction)
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
	
	public function insert($dbConfig, Client $obj) {
		$inTransaction = false;
		
		if($dbConfig instanceof DBConfig){
			$db        =  DataBase::getInstance($dbConfig);
			$inTransaction = $db->beginTransaction();
		}else if($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException("Conexão Inválida");
		
		
		$user 	    	= $obj->getUser ();
		$person 	    = $user->getPerson ();
		
		try {						
			
			// Criar Pessoa
			$personTypeDAO = null;
			$criteria = new Criteria();
			
			if ($person->getType () == Person::PJ && $person->getCnpj()!="")
			{
				// Verifica se já existe cliente com esse CNPJ/CPF
				$qtde = $this->verifyExist($db,$obj);
				
				if($qtde > 0){
					throw new PDOException("CNPJ já está sendo utilizado por um cliente");
				}				
				
				$personTypeDAO = PersonEntityDAO::getInstance ();
				$criteria->eq("pj.cnpj",$person->getCnpj());
			}
			elseif ($person->getType () == Person::PF  && $person->getCpf()!="")
			{
			
				// Verifica se já existe cliente com esse CNPJ/CPF
				$qtde = $this->verifyExist($db,$obj);
				
				if($qtde > 0){
					throw new PDOException("CPF já está sendo utilizado por um cliente");
				}	
				
				$personTypeDAO = PersonIndividualDAO::getInstance ();
				$criteria->eq("pf.cpf",$person->getCpf());
			}
			
			
			if($personTypeDAO!=null)
				$personDb = $personTypeDAO->getSingle ( $db, $criteria );
			$criteria = null;
				
			
			if($personDb!=null && $personDb->getId() > 0)
			{
				$addressDb 		= $personDb->getAddress();
				$phoneDb 		= $personDb->getPhone();
				
				$person 	    = $user->getPerson ();
				$address 	    = $person->getAddress ();
				$phone  	    = $person->getPhone ();
				
				$person->setId($personDb->getId());
				
				$address->setId($addressDb->getId());
				$person->setAddress ($address);
				
				$phone->setId($phoneDb->getId());
				$person->setPhone ($phone);
				
				$personDAO = PersonDAO::getInstance ();
				$personDAO->update ( $db, $person );	
			}
			else
			{
				$person 	    = $user->getPerson ();
				
				$personDAO = PersonDAO::getInstance ();
				$personDAO->insert ( $db, $person );	
			}
			
			$user->setPerson($person);
			
			// Criar Usuário
			$userDAO = UserDAO::getInstance ();
			$userDAO->insert ( $db, $user );	
			
			$obj->setUser($user);
			
			// Get perfil Cliente
			$critProfile = new Criteria();
			$critProfile->eq("prof.id",Profile::CLIE);
			$critProfile->eq("prof.enabled","1");
			$profileDAO = ProfileDAO::getInstance ();			
			$profile    = $profileDAO->getSingle($db, $critProfile);
			$critProfile = null;
			
			if($profile==null)
				throw new PDOException("Perfil inválido");
			
			// Atrelar ao perfil cliente normal
			$userProfile = new UserProfile($user,$profile);			
			$userProfileDAO = UserProfileDAO::getInstance ();			
			$userProfileDAO->insert ( $db, $userProfile );	
			
			
			$_perfis = $user->getProfileList( );
			$_perfis[] = $userProfile;
			$user->setProfileList( $_perfis);
			
			$obj->setUser($user);
			
			
			
			//Criar Cliente
			$sql = "INSERT INTO `client` 
				(   `user_person_id`
				 )
			VALUES 
				(
				   '" . Criteria::escapeString($user->getPerson()->getId()) . "'
				 )";
				 
			$out = $db->exec($sql);			
			if($dbConfig instanceof DBConfig){
					$db->commit ();
					$db		   = null;
			}
			
			if($out == 0)
				throw new PDOException("Erro ao registrar Cliente!");		
			
		} catch ( PDOException $e ) {
			if($dbConfig instanceof DBConfig){
				if ($db!=null && $inTransaction)
					$db->rollback ();
				$db = null;
			}
			throw $e;
		}
	}
}
?>